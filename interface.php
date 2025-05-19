<?php
session_start();

// Define o usuário da sessão, se estiver salvo no cookie
if (isset($_COOKIE['usuario']) && !isset($_SESSION['usuario'])) {
    $_SESSION['usuario'] = $_COOKIE['usuario'];
}

// Redireciona se o usuário não estiver autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Montink</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/estilo.css" />
    <link rel="stylesheet" href="assets/css/interface.css" />
</head>

<body class="interface">

    <header class="cabecalho">
        <a href="index.php">
            <img src="assets/arquivos/montink.png" alt="montink" width="200px" style="cursor: pointer;">
        </a>
    </header>

    <nav class="navegacao">
        <span class="usuario">Usuário: <?= htmlspecialchars($_SESSION['usuario']) ?></span>
        <a href="index.php">Voltar</a>
        <a href="logout.php">Sair</a>
    </nav>

    <main class="principal">
        <div class="conteudo">
            <?php


            $dir = basename($_GET['dir']);
            $file = basename($_GET['file']);

            $pathPhp = __DIR__ . "/$dir/$file.php";
            $pathHtml = __DIR__ . "/$dir/$file.html";
            $pathJs = __DIR__ . "/$dir/$file.js";

            if (file_exists($pathPhp)) {
                include($pathPhp);
            }

            if (file_exists($pathHtml)) {
                include($pathHtml);
            }

            if (file_exists($pathJs)) {
                include($pathJs);
            }

            ?>
        </div>
    </main>

    <footer class="rodape">
        <h1>Eros Grigolli</h1>
    </footer>

</body>

</html>