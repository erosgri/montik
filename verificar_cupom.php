<?php
session_start();
require_once __DIR__ . '/banco_de_dados/conexao_pdo.php';
$conexao = novaConexao();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['codigo'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Requisição inválida']);
    exit;
}

$codigoCupom = trim($_POST['codigo']);
if ($codigoCupom === '') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Digite um código de cupom.']);
    exit;
}

$sqlCupom = "SELECT * FROM cupom WHERE codigo = ? AND ativo = TRUE AND (validade IS NULL OR validade >= CURDATE())";
$stmtCupom = $conexao->prepare($sqlCupom);
$stmtCupom->execute([$codigoCupom]);
$cupom = $stmtCupom->fetch(PDO::FETCH_ASSOC);

if (!$cupom) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Cupom inválido, expirado ou inativo.']);
    exit;
}

if ($cupom['usado'] >= $cupom['uso_maximo']) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Este cupom já atingiu o limite máximo de uso.']);
    exit;
}

// Enviar os dados do cupom para o JS calcular desconto depois
echo json_encode([
    'status' => 'ok',
    'mensagem' => 'Cupom válido!',
    'tipo' => $cupom['tipo'],
    'valor' => (float) $cupom['valor'],
    'codigo' => $cupom['codigo']
]);
