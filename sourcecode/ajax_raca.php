<?php 
    include('connection.php');
    
    if(!isset($_SESSION['user_type']))
    {
        header("Location: login.php");
    }

    $string_query="SELECT * FROM paciente_especie WHERE raca = '".strtolower($_GET['raca'])."';";
    $result = pg_query($conn, $string_query);  
    if(pg_num_rows ($result) == 0){
        $return = array("0","");
        echo json_encode($return);
    }else{
        $obj_pac_esp = pg_fetch_object($result);
        $especie = $obj_pac_esp->especie;
        $return = array("1" , ucwords($especie) );
        echo json_encode($return);
    }
?>   