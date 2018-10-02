<?php
    $servername = "localhost";
    $port = 5432;
    $database = "SVS";
    //$username = "postgres";
    //$password = "admin";
    
    session_start();
    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 1) {
        $username = 'administrador';
        $password = '@login;admpX0';
    } elseif (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 0) {
        $username = 'funcionario';
        $password = '@login;funcM9z';
    } else {
        $username = 'comum';
        $password = '@login;comum2Kh';
    }
    
    // Create connection
    $conn = pg_connect("host=$servername port=$port dbname=$database user=$username password=$password ");

    // Check connection
    if (!$conn) {
        die("Erro: falha ao conectar com o Banco de Dados");
    }

    function base64url_encode( $data ){
        return rtrim( strtr( base64_encode( $data ), '+/', '-_'), '=');
    }
    
    function base64url_decode( $data ){
        return base64_decode( strtr( $data, '-_', '+/') . str_repeat('=', 3 - ( 3 + strlen( $data )) % 4 ));
    }   
?>