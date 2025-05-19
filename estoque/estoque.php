<?php

// LOGIN MESTRE PARA MEXER NA PARTE DE GERENCIAMENTO DE ESTOQUE!!!!!!!!!!

// mestre@login.com  senha: mestre

if (!isset($_SESSION['mestre']) || $_SESSION['mestre'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../banco_de_dados/conexao_pdo.php';

$conexao = novaConexao();
$msg = '';

// CADASTRAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar'])) {
    $nome = htmlspecialchars($_POST['nome']);
    $preco = floatval($_POST['preco']);
    $estoque = intval($_POST['estoque']);

    $sql = "INSERT INTO produtos (nome, preco, estoque) VALUES (?, ?, ?)";
    $stmt = $conexao->prepare($sql);

    $msg = $stmt->execute([$nome, $preco, $estoque])
        ? "✅ Produto cadastrado com sucesso!"
        : "❌ Erro ao cadastrar produto: " . $stmt->errorInfo()[2];
}

// EDITAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {
    $id = intval($_POST['id']);
    $novoPreco = floatval($_POST['novo_preco']);
    $novoEstoque = intval($_POST['novo_estoque']);

    $sql = "UPDATE produtos SET preco = ?, estoque = ? WHERE id = ?";
    $stmt = $conexao->prepare($sql);

    $msg = $stmt->execute([$novoPreco, $novoEstoque, $id])
        ? "✅ Produto atualizado com sucesso!"
        : "❌ Erro ao atualizar produto: " . $stmt->errorInfo()[2];
}

// EXCLUIR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir'])) {
    $id = intval($_POST['id']);

    $sql = "DELETE FROM produtos WHERE id = ?";
    $stmt = $conexao->prepare($sql);

    $msg = $stmt->execute([$id])
        ? "🗑️ Produto excluído com sucesso!"
        : "❌ Erro ao excluir produto: " . $stmt->errorInfo()[2];
}

// LISTAR
$sql = "SELECT * FROM produtos";
$produtos = $conexao->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <title>Gerenciamento de Estoque - Mestre</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body class="p-4">

    <div class="container">
        <h1 class="mb-4">Gerenciamento de Estoque (Apenas login: Mestre)</h1>

        <?php if ($msg): ?>
            <div class="alert alert-info"><?= $msg ?></div>
        <?php endif; ?>

        <!-- FORMULÁRIO DE CADASTRO -->
        <form method="post" class="mb-5">
            <h3>Cadastrar Produto</h3>

            <div class="mb-3">
                <label class="form-label">Nome:</label>
                <input type="text" name="nome" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Preço:</label>
                <input type="number" name="preco" step="0.01" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Estoque:</label>
                <input type="number" name="estoque" class="form-control" required>
            </div>

            <button type="submit" name="cadastrar" class="btn btn-primary">Cadastrar Produto</button>
        </form>

        <hr>

        <!-- LISTAGEM DE PRODUTOS -->
        <h3 class="mb-3">Produtos Cadastrados</h3>
        <table class="table table-striped table-bordered table-hover align-middle">
            <thead class="table-primary text-center">
                <tr>
                    <th>Nome</th>
                    <th>Preço</th>
                    <th>Estoque</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <td><?= htmlspecialchars($produto['nome']) ?></td>
                        <td>R$ <?= number_format($produto['preco'], 2, ',', '.') ?></td>
                        <td><?= $produto['estoque'] ?></td>
                        <td>
                            <form method="post" class="d-flex flex-wrap gap-2 justify-content-center align-items-center mb-2">
                                <input type="hidden" name="id" value="<?= $produto['id'] ?>">
                                <input type="number" name="novo_preco" class="form-control form-control-sm" placeholder="Novo preço" step="0.01" required style="width: 110px;">
                                <input type="number" name="novo_estoque" class="form-control form-control-sm" placeholder="Novo estoque" required style="width: 110px;">
                                <button type="submit" name="editar" class="btn btn-success btn-sm">Editar</button>
                            </form>

                            <form method="post" onsubmit="return confirm('Tem certeza que deseja excluir este produto?');" class="d-flex justify-content-center">
                                <input type="hidden" name="id" value="<?= $produto['id'] ?>">
                                <button type="submit" name="excluir" class="btn btn-danger btn-sm">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS Bundle (Popper + JS) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
