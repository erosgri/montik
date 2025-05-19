

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    if (isset($_COOKIE['usuario'])) {
        $_SESSION['usuario'] = $_COOKIE['usuario'];
    } else {
        header('Location: login.php');
        exit;
    }
}

require_once __DIR__ . '/banco_de_dados/conexao_pdo.php';
$conexao = novaConexao();

$usuarioId = $_SESSION['usuario_id'] ?? null;
if (!$usuarioId) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

$msg = '';

// Processar pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_carrinho'])) {
    $produtosSelecionados = $_POST['produtos'] ?? [];
    $produtosPedidos = array_filter($produtosSelecionados, fn($qtd) => intval($qtd) > 0);

    if (empty($produtosPedidos)) {
        $msg = '<div class="alert alert-warning">Selecione pelo menos um produto com quantidade maior que zero.</div>';
    } else {
        $ids = array_keys($produtosPedidos);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT id, nome, preco, estoque FROM produtos WHERE id IN ($placeholders)";
        $stmt = $conexao->prepare($sql);
        $stmt->execute($ids);
        $produtosDb = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $erroEstoque = false;
        foreach ($produtosDb as $prod) {
            $qtdSelecionada = $produtosPedidos[$prod['id']];
            $qtdNoCarrinho = $_SESSION['carrinho'][$prod['id']] ?? 0;
            $qtdTotal = $qtdNoCarrinho + $qtdSelecionada;

            if ($qtdTotal > $prod['estoque']) {
                $msg = '<div class="alert alert-danger">Quantidade solicitada do produto "' . htmlspecialchars($prod['nome']) . '" excede o estoque disponível.</div>';
                $erroEstoque = true;
                break;
            }
        }

        if (!$erroEstoque) {
            foreach ($produtosDb as $prod) {
                $id = $prod['id'];
                $qtd = $produtosPedidos[$id];
                $_SESSION['carrinho'][$id] = ($_SESSION['carrinho'][$id] ?? 0) + $qtd;
            }
            $msg = '<div class="alert alert-success">Produtos adicionados ao carrinho com sucesso!</div>';
        }
    }
}

// Buscar produtos disponíveis
$sql = "SELECT * FROM produtos WHERE estoque > 0";
$produtos = $conexao->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <title>Pedidos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/estilo.css" />
    <link rel="stylesheet" href="/assets/css/interface.css" />
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet" />
</head>

<body>
    <header class="cabecalho">
        <img src="assets/arquivos/montink.png" alt="montink" width="200px">
    </header>

    <nav class="navegacao">
        <span class="usuario">Usuário: <?= $_SESSION['usuario'] ?></span>
        <a href="index.php">Voltar</a>
        <a href="logout.php">Sair</a>
    </nav>

    <main class="principal">
        <div class="conteudo">
            <h2 class="mb-4">Pedidos</h2>
            <?= $msg ?>

            <form method="post">
                <h4>Escolha a quantidade dos produtos</h4>
                <?php foreach ($produtos as $p): ?>
                    <div class="row align-items-center mb-3">
                        <div class="col-md-6">
                            <label for="produto-<?= $p['id'] ?>" class="form-label">
                                <?= htmlspecialchars($p['nome']) ?>
                                <small class="text-muted">(Estoque: <?= $p['estoque'] ?>)</small>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <input type="number" class="form-control" id="produto-<?= $p['id'] ?>"
                                name="produtos[<?= $p['id'] ?>]" min="0" max="<?= $p['estoque'] ?>" value="0" />
                        </div>
                        <div class="col-md-2">
                            <span class="badge bg-secondary">
                                R$ <?= number_format($p['preco'], 2, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>

                <button type="submit" name="adicionar_carrinho" class="btn btn-primary">Adicionar no carrinho</button>
                <a href="finalizar_compra.php" class="btn btn-success ms-2">Finalizar Compra</a>
            </form>
        </div>
    </main>

    <footer class="rodape">
        <h1>Eros Grigolli</h1>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>