<?php
    error_reporting(0);
    $data = date_create();
    $data = date_format($data,'d');
    session_name(md5($data.'segurança_php'.$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR']));
    session_start();
    session_destroy();
    header('location: ../');
?>