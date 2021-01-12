<?php
    error_reporting(0);
    $data = date_create();
    $data = date_format($data,'d');
    session_name(md5($data.'segurança_php'.$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR']));
    session_start();

    if(isset($_POST['nm_login'])){
        $_SESSION['server']   = 'localhost';
        $_SESSION['login']    = strtolower($_POST['nm_login']);
        $_SESSION['senha']    = $_POST['nm_senha'];
        $_SESSION['db']       = 'db_financeiro';
        
        $conexao = mysqli_connect($_SESSION['server'],$_SESSION['login'],$_SESSION['senha']);
        if(mysqli_connect_error()){
            $retuns['error'] = true;
            $retuns['msg']   = 'erro ao tentar connectar ao sistema-COD:001BE';
            $retuns['cod']   = mysqli_connect_error();
            echo(json_encode($retuns));
        }
        else{
           $start_role = "set default role 'rl_operadores';";
           $start = mysqli_query($conexao,$start_role);
           if(!$start){
                $retuns['error'] = true;
                $retuns['msg']   = 'Permição do sistema não foi iniciada-COD:002BE';
                $retuns['cod']   = mysqli_error($conexao);
                echo(json_encode($retuns));
           }
           else{
               $select = "select * from ".$_SESSION['db'].".tb_login where login= '".$_SESSION['login']."' and status = 'A'";
//               echo($select);
               $consultar = mysqli_query($conexao,$select);
               if(!$consultar or $consultar->num_rows == 0){
                    $retuns['error'] = true;
                    $retuns['msg']   = 'permição ao sistema não foi iniciada como esperada-COD:003BE';
                    $retuns['cod']   = mysqli_error($conexao);
                    echo(json_encode($retuns));
               }
               else{
                   $retorno = mysqli_fetch_object($consultar);
                   $_SESSION['nivel_fin'] = $retorno->nivel;
                   $_SESSION['login_fin'] = $retorno->login;
                   $retuns['error'] = false;
                   $retuns['msg']   = 'Acesso ok-COD:004BE';
                   $retuns['cod']   = '1995BE';
                   echo(json_encode($retuns));
               }

           }
           mysqli_close($conexao);
        }
    }
?>