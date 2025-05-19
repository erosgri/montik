<?php

require_once "conexao_pdo.php";

try {
    $conexao = novaConexao(null); 

    $sql = 'CREATE DATABASE IF NOT EXISTS montink';
    $resultado = $conexao->exec($sql); 

    if ($resultado !== false) {
        echo "Sucesso";
    } else {
        echo "Erro ao criar banco de dados.";
    }

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}

$conexao = null;

?>