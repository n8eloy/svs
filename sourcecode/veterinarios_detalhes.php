<?php
    include('connection.php');
    
    if(!isset($_SESSION['user_type']))
    {
        header("Location: login.php");
    }

    $edit='';
    $crmv = '';
    $nome = '';
    $email = '';
    $salario = '';
    $telefone_1 = '';
    $telefone_2 = '';
    $especialidade = '';

    if(isset($_GET['view'])) {
        $view = base64url_decode($_GET['view']);
        $string_query= "SELECT * FROM veterinario WHERE crmv='".$view."';";
        $result = pg_query($conn, $string_query);
        $obj = pg_fetch_object($result);
        $crmv=$obj->crmv;
        $nome=ucwords(strtolower($obj->nome));
        $email=$obj->email;
        $salario='R$ '.number_format($obj->salario, 2, ',', '.');
        $telefone_1=$obj->telefone_1;
        $telefone_2=$obj->telefone_2;
        
        $string_query="SELECT * FROM vet_especialidade WHERE crmv='".$view."';";
        $result = pg_query($conn, $string_query);
        
        $especialidade = '';
        while($value = pg_fetch_assoc($result)) {
            if($especialidade != '') {
                $especialidade .= ', ';
            }
            $especialidade .= ucwords(strtolower($value['especialidade']));
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>SVS - Veterinários</title>
        <?php include 'common_meta.php'; ?>
    </head>
    <!-- Fonte declarada inline para compatibilidade com Chrome -->
    <body class="container-fluid" style="font-family: 'Lato', sans-serif" >
        <div class="row">
            <div class="col-lg-3 grid_container">
                <?php include 'menu.php'; ?>
                <script> document.getElementById("menu_vet").className += " active"; </script>
            </div>
            <div class="col-lg-9 grid_container">
                <div class="frame">
                    <div class="content">
                        <div id="bd_msg" class="warning">
                            <hr>
                            <h1 class="txt_red">Erro de Banco de Dados</h1>
                            <hr>
                            <div class="warning_content">
                                <?php
                                if (isset($_SESSION['bd_error'])) {
                                    echo $_SESSION['bd_error'];
                                } else {
                                    echo 'Contate um administrador do sistema se o problema persistir';
                                }
                                ?>
                            </div>
                            <div class="warning_buttons">
                                <button class="cancel" onclick="warning_close()">Voltar</button>
                            </div>
                        </div>
                        <div class="row">
                            <h1 class="txt_green"><i class="material-icons">person</i> Lista de Veterinários — Detalhes <?=$nome?></h1>
                        </div>
                        <hr>                            
                        <div class="row">
                            <div class="col-lg-6">

                                <label for="crmv">CRMV</label><br>
                                <?=$crmv?><br>

                                <label for="name">Nome</label><br>
                                <?=$nome?><br>

                                <label for="email">E-mail</label><br>
                                <?=$email?><br>

                                <label for="spec">Especialidade</label><br>
                                <?=$especialidade?><br>
                            </div>
                            <div class="col-lg-6">
                                <label for="salario">Salário</label><br>
                                <?=$salario?><br>

                                <label for="tel1">Telefone 1</label><br>
                                <?=$telefone_1?><br>

                                <label for="tel2">Telefone 2</label><br>
                                <?=$telefone_2?><br>
                            </div>
                        </div>
                        <div class="button_footer">
                            <button type="button" class="cancel" onclick="history.back()"><i class="material-icons">arrow_back_ios</i>Retornar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            function show_error() {
                $("#bd_msg").show();
            }
            
            function warning_close() {
                $(".warning").hide();
            }         
        </script>
        <?php
            if((isset($_GET['view']) && !$result) || isset($_SESSION['bd_error'])) {
                echo "<script> show_error(); </script>";
                unset($_SESSION['bd_error']);
            }
        ?>
    </body>
</html>