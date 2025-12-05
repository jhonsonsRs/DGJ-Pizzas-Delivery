<?php
session_start();
include 'conecta.php';

$email = $_POST['email'];
$senha = $_POST['senha'];
$tipo = $_POST['tipo'];

$tabela = $tipo; 
$pk = "id_" . $tipo;

$sql = "SELECT * FROM $tabela WHERE email = '$email' AND senha_hash = '$senha'";
$result = pg_query($conexao, $sql);

if (pg_num_rows($result) > 0) {
    $usuario = pg_fetch_assoc($result);
    
    $_SESSION['usuario_id'] = $usuario[$pk];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['tipo_usuario'] = $tipo;

    header("Location: painel.php");
} else {
    header("Location: login.php?erro=1");
}
?>