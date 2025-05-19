<?php
require_once('finalizar1.php');
require_once('finalizarAjax.php');

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Finalizar Pedido</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/estilo.css" />
    <!-- <link rel="stylesheet" href="/assets/css/interface.css" /> -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet" />
</head>

<body>
    <header class="cabecalho">
        <img src="assets/arquivos/montink.png" alt="montink" width="200px">
    </header>

    <nav class="navegacao">
        <span class="usuario">Usuário: <?= $_SESSION['usuario'] ?></span>
        <a href="pedidos.php">Voltar</a>
        <a href="logout.php">Sair</a>
    </nav>

    <main class="principal">
        <div class="conteudo">
            <h2 class="mb-4">Finalizar Pedido</h2>
            <?php if (empty($msg)): ?>
                <form method="post">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Preço Unitário</th>
                                <th>Subtotal</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtosDb as $prod): ?>
                                <?php
                                $id = $prod['id'];
                                $qtd = $_SESSION['carrinho'][$id];
                                $sub = $prod['preco'] * $qtd;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($prod['nome']) ?></td>
                                    <td><?= $qtd ?></td>
                                    <td>R$ <?= number_format($prod['preco'], 2, ',', '.') ?></td>
                                    <td>R$ <?= number_format($sub, 2, ',', '.') ?></td>
                                    <td>
                                        <a href="finalizar_compra.php?remover=<?= $id ?>"
                                            class="btn btn-sm btn-danger">Remover</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <tr>
                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                <td colspan="2" id="subtotal"><strong>R$
                                        <?= number_format($subtotal, 2, ',', '.') ?></strong></td>
                            </tr>
                            <?php if ($desconto > 0): ?>
                                <tr id="linha-desconto">
                                    <td colspan="3" class="text-end text-success"><strong>Desconto (Cupom):</strong></td>
                                    <td colspan="2" class="text-success" id="valor-desconto">- R$
                                        <?= number_format($desconto, 2, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <tr id="linha-desconto" style="display:none;">
                                    <td colspan="3" class="text-end text-success"><strong>Desconto (Cupom):</strong></td>
                                    <td colspan="2" class="text-success" id="valor-desconto"></td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Frete:</strong></td>
                                <td colspan="2" id="frete">R$ <?= number_format($frete, 2, ',', '.') ?></td>
                            </tr>
                            <tr class="table-secondary">
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td colspan="2" id="total"><strong>R$ <?= number_format($total, 2, ',', '.') ?></strong>
                                </td>
                            </tr>

                        </tbody>
                    </table>

                    <div class="mb-3">
                        <label for="codigo_cupom" class="form-label">Cupom de Desconto</label>
                        <input type="text" class="form-control" id="codigo_cupom" name="codigo_cupom"
                            placeholder="Digite o código do cupom" />
                        <button type="button" class="btn btn-primary mt-2" onclick="aplicarCupom()">Aplicar
                            Cupom</button>

                        <div id="mensagem-cupom" class="mt-2"></div>
                    </div>

                    <?php if (!empty($msgCupom)): ?>
                        <div class="alert alert-<?= $tipoMsgCupom ?>"><?= $msgCupom ?></div>
                    <?php endif; ?>

                    <button type="submit" name="finalizar_pedido" class="btn btn-success">Confirmar Pedido</button>
                    <a href="pedidos.php" class="btn btn-secondary ms-2">Voltar ao Carrinho</a>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <footer class="rodape">
        <h1>Eros Grigolli</h1>
    </footer>

      
</body>

</html>