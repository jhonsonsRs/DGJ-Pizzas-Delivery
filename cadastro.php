<?php include 'header.php'; ?>
<main class="container page-content">
    <div class="form-container">
        <h1>Cadastro de Cliente</h1>
        <p style="text-align:center">Crie sua conta para agilizar seus próximos pedidos.</p>
        <form action="cadastro_proc.php" method="POST">
            <div class="form-group">
                <label for="nome">Nome Completo</label>
                <input type="text" name="nome" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="tel" name="telefone" maxlength="11" required>
            </div>
            <div class="form-group">
                <label for="idade">Idade</label>
                <input type="number" name="idade" min="1">
            </div>
            <div class="form-group">
                <label for="senha">Crie uma Senha</label>
                <input type="password" name="senha" required>
            </div>
            
            <input type="hidden" name="tipo" value="cliente"> 
            
            <button type="submit" class="btn-primary btn-large">Criar Conta</button>
        </form>
        <div class="form-toggle-link">
            <p>Já tem uma conta? <a href="login.php">Faça login</a></p>
        </div>
    </div>
</main>
</body></html>