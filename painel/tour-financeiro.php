<?php
    error_reporting(0);
    $data = date_create();
    $data = date_format($data,'d');
    session_name(md5($data.'segurança_php'.$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR']));
    session_start();

    if(!isset($_SESSION['login_fin'])){
        session_destroy();
        header('location: ../');
    }

?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <title>Painel</title>
        <link rel="stylesheet" href="../css/bootstrap.min.css">
        <link rel="stylesheet" href="css/tour-financeiro.css">
        <link rel="stylesheet" href="../alerta/css/alerta.css">
        <link rel="stylesheet" href="../menu/css/navbar.css">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    </head>
    <body>
        <?php require_once('../menu/navbar.php'); require_once('../alerta/alerta.php'); ?>
        <section id="id_sectionlogin">
            <div>
                <p>Despesas</p>
            </div>
            <div>
                <p>Saldos</p>
            </div>
            <div>
                <p>Metas</p>
            </div>
            <div>
                <p>Lista do Mercado</p>
            </div>
            <div>
                <p>Projeções</p>
            </div>
            <div>
                <p>Usuários</p>
            </div>
        </section>
    </body>
    <script src="../js/jQuery_v3.5.1.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="js/tour-financeiro.js"></script>
    <script src="../alerta/js/alerta.js"></script>
    <script src="../menu/js/navbar.js"></script>
</html>