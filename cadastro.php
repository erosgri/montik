<?php
session_start();
require_once 'banco_de_dados/conexao_pdo.php';

$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $logradouro = trim($_POST['logradouro'] ?? '');
    $bairro = trim($_POST['bairro'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $uf = trim($_POST['uf'] ?? '');
    $cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (!$nome || !$email || !$cep || !$logradouro || !$bairro || !$cidade || !$uf || !$cpf || !$senha) {
        $erros[] = 'Preencha todos os campos obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'E-mail inválido.';
    } elseif (strlen($cpf) !== 11) {
        $erros[] = 'CPF inválido.';
    } elseif (!preg_match('/^\d{5}-?\d{3}$/', $cep)) {
        $erros[] = 'CEP inválido.';
    }

    if (empty($erros)) {
        try {
            $conexao = novaConexao();

            $enderecoCompleto = "$logradouro, $bairro, $cidade - $uf, CEP: $cep";

            $sql = "INSERT INTO cadastro 
                (nome, email, cep, logradouro, bairro, cidade, uf, endereco, cpf, senha)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conexao->prepare($sql);
            $stmt->execute([
                $nome,
                $email,
                $cep,
                $logradouro,
                $bairro,
                $cidade,
                $uf,
                $enderecoCompleto,
                $cpf,
                password_hash($senha, PASSWORD_DEFAULT)
            ]);

            $_SESSION['mensagem'] = "Cadastro realizado com sucesso!";
            header('Location: index.php');
            exit;

        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                $erros[] = 'Usuário com este e-mail ou CPF já existe.';
            } else {
                $erros[] = "Erro ao cadastrar: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastro - Montink</title>
    <link rel="stylesheet" href="assets/css/cadastro.css">
</head>

<body class="cadastro">
    <header class="cabecalho">
        <img src="assets/arquivos/montink.png" alt="montink" width="400px" />
    </header>

    <div class="conteudo">
        <h3>Cadastro</h3>

        <?php if (!empty($erros)): ?>
            <div class="erro">
                <ul>
                    <?php foreach ($erros as $erro): ?>
                        <li><?= htmlspecialchars($erro) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="principal">
            <form method="post" id="form-cadastro" autocomplete="off">

                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" required>

                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>

                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required>

                <label for="cpf">CPF</label>
                <input type="text" id="cpf" name="cpf" maxlength="14" placeholder="000.000.000-00" required>

                <label for="cep">CEP</label>
                <input type="text" id="cep" name="cep" maxlength="9" placeholder="00000-000" required>

                <label for="logradouro">Logradouro</label>
                <input type="text" id="logradouro" name="logradouro" required>

                <label for="bairro">Bairro</label>
                <input type="text" id="bairro" name="bairro" required>

                <label for="cidade">Cidade</label>
                <input type="text" id="cidade" name="cidade" required>

                <label for="uf">UF</label>
                <input type="text" id="uf" name="uf" maxlength="2" required>

                <button type="submit">Cadastrar</button>
                <a href="login.php">Voltar</a>
            </form>
        </div>
    </div>

    <footer class="rodape">
        <h1>Eros Grigolli</h1>
    </footer>

    <script>
        document.getElementById('cep').addEventListener('blur', function () {
            const cep = this.value.replace(/\D/g, '');

            if (cep.length !== 8) {
                alert('CEP inválido!');
                return;
            }

            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(res => res.json())
                .then(data => {
                    if (data.erro) {
                        alert('CEP não encontrado!');
                        return;
                    }

                    document.getElementById('logradouro').value = data.logradouro || '';
                    document.getElementById('bairro').value = data.bairro || '';
                    document.getElementById('cidade').value = data.localidade || '';
                    document.getElementById('uf').value = data.uf || '';
                })
                .catch(() => alert('Erro ao buscar o CEP.'));
        });
    </script>
</body>

</html>