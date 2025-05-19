<?php
require_once 'banco_de_dados/conexao_pdo.php';

try {
    $conexao = novaConexao();

    // Inserir cupom 20% OFF
    $sqlInsert = "INSERT INTO cupom (codigo, tipo, valor, validade, uso_maximo, usado, ativo)
                  VALUES ('20OFF', 'porcentagem', 20.00, '2025-12-31', 100, 0, TRUE)";

    $conexao->exec($sqlInsert);

    echo "Cupom inserido com sucesso!";
} catch (PDOException $e) {
    echo "Erro ao inserir cupom: " . $e->getMessage();
}
?>