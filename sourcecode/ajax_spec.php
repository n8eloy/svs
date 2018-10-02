<?php
    include('connection.php');
    
    if(!isset($_SESSION['user_type']))
    {
        header("Location: login.php");
    }

    $crmv=$_GET['crmv'];

    $string_query="SELECT especialidade FROM vet_especialidade WHERE crmv='".$crmv."'";

    $result = pg_query($conn, $string_query);
    
    $value = pg_fetch_assoc($result);        
        echo ucwords(strtolower($value['especialidade']));
    
    while($value = pg_fetch_assoc($result)) {      
        echo ', '.ucwords(strtolower($value['especialidade']));
    }
?>