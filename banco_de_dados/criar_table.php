<?php
require_once "conexao_pdo.php";

try {
    $conexao = novaConexao();




    foreach ($dropTables as $drop) {
        $conexao->exec($drop);
    }

    // Criar tabelas
    $sqls = [
        "CREATE TABLE IF NOT EXISTS cadastro (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        endereco VARCHAR(255) NOT NULL,
        cpf VARCHAR(11) NOT NULL
    )",

        "CREATE TABLE IF NOT EXISTS produtos (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        preco DECIMAL(10,2) NOT NULL,
        estoque INT NOT NULL DEFAULT 0
    )",

        "CREATE TABLE IF NOT EXISTS pedidos (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        usuario INT NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        data TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

        "CREATE TABLE IF NOT EXISTS itens_pedido (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pedido_id INT(6) UNSIGNED NOT NULL,
        produto_id INT(6) UNSIGNED NOT NULL,
        quantidade INT NOT NULL,
        preco_unit DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
    )",

        "CREATE TABLE IF NOT EXISTS cupom (
        id INT AUTO_INCREMENT PRIMARY KEY,
        codigo VARCHAR(50) NOT NULL UNIQUE,
        tipo ENUM('porcentagem', 'valor') NOT NULL DEFAULT 'valor',
        valor DECIMAL(10,2) NOT NULL,
        validade DATE,
        uso_maximo INT DEFAULT 1,
        usado INT DEFAULT 0,
        ativo BOOLEAN DEFAULT TRUE,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
    ];


    foreach ($sqls as $sql) {
        $resultado = $conexao->exec($sql);
        if ($resultado === false) {
            $erro = $conexao->errorInfo();
            echo "Erro ao criar tabela: " . $erro[2] . "<br>";
        } else {
            echo "Tabela criada com sucesso.<br>";
        }
    }

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}

$conexao = null;
?>