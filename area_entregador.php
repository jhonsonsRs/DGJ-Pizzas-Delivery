<?php
session_start();
include 'conecta.php';
if($_SESSION['tipo'] != 'entregador') { header("Location: index.php"); exit; }

$id_entregador = $_SESSION['usuario_id'];

$sql = "SELECT p.id_pedido, c.nome, p.endereco_entrega, p.status_pedido 
        FROM pedido p 
        JOIN cliente c ON p.id_cliente = c.id_cliente
        WHERE p.status_pedido IN ('em_preparo', 'saiu_entrega')
        ORDER BY p.id_pedido DESC";
$result = pg_query($conexao, $sql);
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="style.css"></head>
<body>
<header>
    <div class="container"><div class="brand">Área do Entregador</div>
    <nav><ul><li><a href="logout.php">Sair</a></li></ul></nav></div>
</header>
<div class="container page-content">
    <h2>Fila de Entregas</h2>
    <div class="menu-grid">
        <?php while($ped = pg_fetch_assoc($result)): ?>
        <div class="menu-card" style="height:auto; padding: 1rem;">
            <h3>Pedido #<?php echo $ped['id_pedido']; ?></h3>
            <p><strong>Cliente:</strong> <?php echo $ped['nome']; ?></p>
            <p><strong>Endereço:</strong> <?php echo $ped['endereco_entrega']; ?></p>
            <p class="highlight"><?php echo strtoupper($ped['status_pedido']); ?></p>
            
            <?php if($ped['status_pedido'] == 'em_preparo'): ?>
                <button class="btn-primary" style="background:var(--secondary); color:black;">Pegar Entrega</button>
            <?php else: ?>
                <button class="btn-primary" style="background:green;">Finalizar Entrega</button>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>