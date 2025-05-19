<?php
require_once "conexao_pdo.php";

$nome = $_POST['nome'] ?? null;
$email = $_POST['email'] ?? null;
$logradouro = $_POST['logradouro'] ?? null;
$bairro = $_POST['bairro'] ?? null;
$cidade = $_POST['cidade'] ?? null;
$uf = $_POST['uf'] ?? null;
$cep = $_POST['cep'] ?? null;
$cpf = $_POST['cpf'] ?? null;
$senha = $_POST['senha'] ?? null;

if (!$nome || !$email || !$senha) {
    die("Preencha os campos nome, email e senha obrigatoriamente.");
}

try {
    $conexao = novaConexao();

    $enderecoCompleto = "$logradouro, $bairro, $cidade - $uf, CEP: $cep";

    $sql = "INSERT INTO cadastro (nome, email, endereco, cpf, senha) VALUES (?, ?, ?, ?, ?)";

    $stmt = $conexao->prepare($sql);
    $stmt->execute([
        $nome,
        $email,
        $enderecoCompleto,
        preg_replace('/\D/', '', $cpf),
        password_hash($senha, PASSWORD_DEFAULT)
    ]);

    echo "Cadastro realizado com sucesso.";

} catch (PDOException $e) {
    echo "Erro ao inserir cadastro: " . $e->getMessage();
}

$conexao = null;
?>