<!-- parte do PHP -->

<?php
session_start();
require_once __DIR__ . '/banco_de_dados/conexao_pdo.php';
$conexao = novaConexao();

$usuarioId = $_SESSION['usuario_id'] ?? null;
if (!$usuarioId) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['remover'])) {
    $idRemover = (int)$_GET['remover'];

    unset($_SESSION['carrinho'][$idRemover]);
    unset($_SESSION['desconto'], $_SESSION['cupom_aplicado']); // Limpa o cupom se carrinho mudar

    if (empty($_SESSION['carrinho'])) {
        // Se o carrinho ficou vazio após remover, redireciona para pedidos
        header('Location: pedidos.php');
    } else {
        // Senão, apenas recarrega a página
        header('Location: finalizar_compra.php');
    }
    exit;
}

// Buscar carrinho e produtos
$carrinho = $_SESSION['carrinho'] ?? [];

if (empty($carrinho)) {
    echo "Carrinho vazio.";
    exit;
}

$ids = array_keys($carrinho);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $conexao->prepare("SELECT id, nome, preco, estoque FROM produtos WHERE id IN ($placeholders)");
$stmt->execute($ids);
$produtosDb = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular subtotal
$subtotal = 0;
foreach ($produtosDb as $prod) {
    $subtotal += $prod['preco'] * $carrinho[$prod['id']];
}




// TRATAR AJAX DE CUPOM

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codigo'])) {
    header('Content-Type: application/json');

    $codigo = trim($_POST['codigo']);
    if ($codigo === '') {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Informe um código de cupom.']);
        exit;
    }

    // Buscar cupom no banco que está ativo e não ultrapassou uso máximo
    $stmtCupom = $conexao->prepare("SELECT * FROM cupom WHERE codigo = ? AND ativo = 1 AND (uso_maximo IS NULL OR usado < uso_maximo) AND (validade IS NULL OR validade >= CURDATE())");
    $stmtCupom->execute([$codigo]);
    $cupom = $stmtCupom->fetch(PDO::FETCH_ASSOC);

    if (!$cupom) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Cupom inválido, expirado ou atingiu o limite de uso.']);
        exit;
    }

    // Calcular desconto
    $desconto = 0;
    if ($cupom['tipo'] === 'porcentagem') {
        $desconto = $subtotal * ($cupom['valor'] / 100);
    } else { // valor fixo
        $desconto = $cupom['valor'];
    }

    // Garantir que desconto não ultrapasse subtotal
    if ($desconto > $subtotal) {
        $desconto = $subtotal;
    }

    // Salvar desconto e cupom na sessão
    $_SESSION['desconto'] = $desconto;
    $_SESSION['cupom_aplicado'] = $codigo;

    echo json_encode([
        'status' => 'ok',
        'mensagem' => "Cupom aplicado! Desconto de R$ " . number_format($desconto, 2, ',', '.'),
        'valor' => $desconto,
    ]);
    exit;
}

// Pegar desconto e frete da sessão para mostrar
$desconto = $_SESSION['desconto'] ?? 0.0;
$frete = $_SESSION['frete'] ?? 0.0;
$total = ($subtotal - $desconto) + $frete;


// TRATAR FINALIZAÇÃO DO PEDIDO

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_pedido'])) {
    try {
        $conexao->beginTransaction();

        // Inserir pedido
        $sqlPedido = "INSERT INTO pedidos (usuario, total) VALUES (?, ?)";
        $stmtPedido = $conexao->prepare($sqlPedido);
        $stmtPedido->execute([$usuarioId, $total]);
        $pedidoId = $conexao->lastInsertId();

        // Inserir itens do pedido e atualizar estoque
        $sqlItem = "INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unit) VALUES (?, ?, ?, ?)";
        $stmtItem = $conexao->prepare($sqlItem);
        $sqlAtualizaEstoque = "UPDATE produtos SET estoque = estoque - ? WHERE id = ?";
        $stmtEstoque = $conexao->prepare($sqlAtualizaEstoque);

        foreach ($produtosDb as $prod) {
            $produtoId = $prod['id'];
            $quantidade = $carrinho[$produtoId];
            $precoUnit = $prod['preco'];

            $stmtItem->execute([$pedidoId, $produtoId, $quantidade, $precoUnit]);
            $stmtEstoque->execute([$quantidade, $produtoId]);
        }

        // Atualizar cupom usado
        if (!empty($_SESSION['cupom_aplicado'])) {
            $stmtCupomUso = $conexao->prepare("UPDATE cupom SET usado = usado + 1 WHERE codigo = ?");
            $stmtCupomUso->execute([$_SESSION['cupom_aplicado']]);
        }

        $conexao->commit();

        // Limpar sessão
        unset($_SESSION['carrinho'], $_SESSION['desconto'], $_SESSION['frete'], $_SESSION['cupom_aplicado']);

        $msg = '<div class="alert alert-success">Pedido finalizado com sucesso!</div>';
    } catch (Exception $e) {
        $conexao->rollBack();
        $msg = '<div class="alert alert-danger">Erro ao finalizar pedido: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

?>






