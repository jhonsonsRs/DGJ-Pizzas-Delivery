<?php
session_start();
include 'conecta.php';

$nome = $_POST['nome'];
$email = $_POST['email'];
$telefone = $_POST['telefone'];
$idade = $_POST['idade'] ? $_POST['idade'] : 'NULL';
$senha = $_POST['senha']; 
$tipo = $_POST['tipo']; 

$sql = "INSERT INTO cliente (nome, email, telefone, idade, senha_hash) 
        VALUES ('$nome', '$email', '$telefone', $idade, '$senha')";

$resultado = pg_query($conexao, $sql);

if ($resultado) {
    header("Location: login.php?cadastro=sucesso");
} else {
    header("Location: cadastro.php?erro=1");
}
?>