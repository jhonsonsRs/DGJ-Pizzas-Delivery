<?php if(!isset($_SESSION)) { session_start(); } ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>DGJ Pizzas Delivery</title>
    <link href="https://fonts.googleapis.com/css2?family=Lobster&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo-container">
                <img src="img/logo.png" alt="Logo" class="logo-img" onerror="this.src='https://via.placeholder.com/64'">
                <div>
                    <div class="brand">DGJ Pizzaria</div>
                    <div class="tag">Sabor que chega rápido</div>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Início</a></li>
                    
                    <?php if(isset($_SESSION['usuario_id'])): ?>
                        <li><a href="painel.php" class="active">Painel <?php echo ucfirst($_SESSION['tipo_usuario']); ?></a></li>
                        <li><a href="logout.php" class="btn-primary" style="background-color: var(--dark);">Sair</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="cadastro.php" class="btn-primary">Cadastre-se</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>