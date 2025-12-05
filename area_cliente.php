<?php
session_start();
include 'conecta.php';
if($_SESSION['tipo'] != 'cliente') { header("Location: index.php"); exit; }

$sql = "SELECT * FROM produtos WHERE disponivel = true";
$result = pg_query($conexao, $sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Cardápio - DGJ</title>
</head>
<body>
<header>
    <div class="container">
        <div class="brand">Olá, <?php echo $_SESSION['usuario_nome']; ?></div>
        <nav>
            <ul>
                <li><a href="#" class="active">Cardápio</a></li>
                <li><a href="meus_pedidos.php">Meus Pedidos</a></li>
                <li><a href="logout.php" class="btn-primary">Sair</a></li>
            </ul>
        </nav>
    </div>
</header>

<div class="container page-content">
    <h2>Nosso Cardápio</h2>
    <div class="menu-grid">
        <?php while($prod = pg_fetch_assoc($result)): ?>
        <div class="menu-card">
            <img src="img/<?php echo $prod['imagem_url'] ? $prod['imagem_url'] : 'pizza_padrao.png'; ?>" alt="Pizza">
            <h3><?php echo $prod['nome']; ?></h3>
            <p><?php echo $prod['descricao']; ?></p>
            <div class="price">R$ <?php echo number_format($prod['preco_base'], 2, ',', '.'); ?></div>
            <button class="add-to-cart">Adicionar</button>
        </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>