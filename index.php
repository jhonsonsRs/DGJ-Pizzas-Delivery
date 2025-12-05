<?php include 'header.php'; ?>

<main>
    <section class="hero-new">
        <div class="container hero-content">
            <div class="hero-text">
                <h1>A pizza que você <span class="highlight">realmente</span> merece.</h1>
                <p>Feita com paixão, entregue com agilidade. Peça a sua DGJ Pizza hoje!</p>
                
                <?php if(isset($_SESSION['usuario_id'])): ?>
                    <a href="painel.php" class="btn-primary btn-large">Ir para o Painel</a>
                <?php else: ?>
                    <a href="login.php" class="btn-primary btn-large">Fazer Login</a>
                <?php endif; ?>
            </div>
            <div class="hero-image">
                <img src="img/hero-pizza.png" alt="Pizza" class="hero-pizza" onerror="this.style.display='none'">
            </div>
        </div>
    </section>

    <section class="info-bar container">
        <div><strong>Horário:</strong><p>18h às 23h30</p></div>
        <div><strong>Pagamento:</strong><p>Dinheiro, Cartão e Pix</p></div>
    </section>
</main>

<footer><div class="container"><p>&copy; 2025 DGJ Pizzas.</p></div></footer>
</body></html>