<?php
session_start();
include 'conecta.php';
if($_SESSION['tipo'] != 'gerente') { header("Location: index.php"); exit; }

$sql_faturamento = "SELECT forma_pagamento, COUNT(id_pedido) as qtd, SUM(valor_total) as total 
                    FROM pedido GROUP BY forma_pagamento";
$res_fat = pg_query($conexao, $sql_faturamento);

// Query de Estoque Crítico
$sql_estoque = "SELECT nome, quantidade, unidade_medida FROM estoque_insumos WHERE quantidade <= ponto_reposicao";
$res_estoque = pg_query($conexao, $sql_estoque);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="container">
        <div class="brand">Painel Gerencial</div>
        <nav><ul><li><a href="logout.php">Sair</a></li></ul></nav>
    </div>
</header>

<div class="container page-content">
    <h1>Visão Geral</h1>
    
    <div class="admin-section">
        <h2>Faturamento por Pagamento</h2>
        <div class="reasons-grid">
            <?php while($row = pg_fetch_assoc($res_fat)): ?>
            <div class="reason-item">
                <h3><?php echo ucfirst($row['forma_pagamento']); ?></h3>
                <p>Pedidos: <?php echo $row['qtd']; ?></p>
                <p class="highlight" style="font-size: 1.5rem; font-weight:bold">
                    R$ <?php echo number_format($row['total'], 2, ',', '.'); ?>
                </p>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="admin-section">
        <h2>Alerta de Estoque</h2>
        <?php if(pg_num_rows($res_estoque) == 0): ?>
            <p class="status-message" style="display:block; background:#e0f8e0; color:green;">Estoque Regular!</p>
        <?php else: ?>
            <table width="100%" style="margin-top:1rem;">
                <tr style="text-align:left"><th>Insumo</th><th>Qtd Atual</th></tr>
                <?php while($est = pg_fetch_assoc($res_estoque)): ?>
                <tr>
                    <td><?php echo $est['nome']; ?></td>
                    <td style="color:red; font-weight:bold"><?php echo $est['quantidade']." ".$est['unidade_medida']; ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>