<?php
    session_start();
    //unset($_SESSION['logado']);
    //unset($_SESSION['usuario']);
    //unset($_SESSION['tipo']);
    //unset($_SESSION['data']);
    session_unset();
    session_destroy();
    header("Location: login.php");
?>