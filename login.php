<?php include 'header.php'; ?>

<main class="container page-content">
    <div class="form-container">
        <h1>Acesso ao Sistema</h1>
        <p style="text-align:center">Entre para pedir ou gerenciar.</p>

        <form action="login_proc.php" method="POST">
            <div class="form-group">
                <label>Email:</label>
                <input type="text" name="email" required>
            </div>
            <div class="form-group">
                <label>Senha:</label>
                <input type="password" name="senha" required>
            </div>
            <div class="form-group">
                <label>Sou:</label>
                <select name="tipo">
                    <option value="cliente">Cliente</option>
                    <option value="atendente">Atendente</option>
                    <option value="entregador">Entregador</option>
                    <option value="gerente">Gerente</option>
                </select>
            </div>
            
            <?php 
            if(isset($_GET['erro'])) {
                echo "<div class='form-message error'>Login ou senha incorretos!</div>";
            }
            ?>

            <button type="submit" class="btn-primary btn-large">Entrar</button>
        </form>
    </div>
</main>
</body></html>