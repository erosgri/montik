<?php
session_start();

// LOGIN MESTRE PARA MEXER NA PARTE DE GERENcIAMENTO DE ESTOQUE!!!!!!!!!!

// mestre@login.com  senha: mestre

require_once('banco_de_dados/conexao_pdo.php');

$_SESSION['usuario_id'] = $usuario['id'];
$_SESSION['usuario_nome'] = $usuario['nome'];

$email = trim($_POST['email'] ?? '');
$senha = trim($_POST['senha'] ?? '');
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$email || !$senha) {
        $erros[] = 'Informe e-mail e senha.';
    } else {
        try {
            $conexao = novaConexao();

            $sql = "SELECT * FROM cadastro WHERE email = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha, $usuario['senha'])) {
                // Salva o ID e nome do usuário na sessão para controle de acesso
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];

                // Definir usuário mestre (ajuste o email conforme seu cadastro)
                if (strtolower(trim($usuario['email'])) === 'mestre@login.com') {
                    $_SESSION['mestre'] = true;
                } else {
                    $_SESSION['mestre'] = false;
                }

                $_SESSION['erros'] = null;
                setcookie('usuario', $usuario['nome'], time() + 60 * 60 * 24 * 30);
                header('Location: index.php');
                exit;
            } else {
                $erros[] = 'Usuário não cadastrado ou senha incorreta.';
            }
        } catch (PDOException $e) {
            $erros[] = 'Erro no banco de dados: ' . $e->getMessage();
        }
    }

    $_SESSION['erros'] = $erros;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <title>Montink - Login</title>

    <!-- Fontes Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="assets/css/login.css" />
</head>

<body class="login">
    <header class="cabecalho">
        <img src="assets/arquivos/montink.png" alt="montink" width="400px" />
    </header>

    <main class="principal">
        <div class="conteudo">
            <h3>Bem-vindo</h3>

            <?php if (isset($_SESSION['erros'])): ?>
                <div class="erro">
                    <?php foreach ($_SESSION['erros'] as $erro): ?>
                        <p><?= htmlspecialchars($erro) ?></p>
                    <?php endforeach ?>
                </div>
            <?php endif ?>

            <form action="#" method="post">
                <div>
                    <label for="email">E-mail</label>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" required />
                </div>
                <div>
                    <label for="senha">Senha</label>
                    <input type="password" name="senha" id="senha" required />
                </div>
                <div class="botoes">
                    <button type="submit">Login</button>
                    <a href="cadastro.php" class="botao-cadastrar">Cadastrar</a>
                </div>
            </form>
        </div>
    </main>

    <footer class="rodape">
        <h1>Eros Grigolli</h1>
    </footer>
</body>

</html>

<style>
    button>a {
        color: white;
    }
</style>
