<?php
    include('connection.php');
    
    if(!isset($_SESSION['user_type']))
    {
        header("Location: login.php");
    }

    $edit='';
    $cpf='';
    $nome='';
    $email='';
    $endereco='';
    $telefone1='';
    $telefone2='';

    if(isset($_GET['view']) && $_GET['view']!='') {
        $view = base64url_decode($_GET['view']);
        $string_query="SELECT * FROM cliente WHERE cpf='".$view."';";
        $result = pg_query($conn, $string_query);
        $obj = pg_fetch_object($result);
        $cpf=$obj->cpf;
        $nome=ucwords(strtolower($obj->nome));
        $email=$obj->email;
        $endereco=ucwords(strtolower($obj->endereco));
        $telefone1=$obj->telefone_1;
        $telefone2=$obj->telefone_2;
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>SVS - Clientes</title>
        <?php include 'common_meta.php'; ?>
    </head>
    <!-- Fonte declarada inline para compatibilidade com Chrome -->
    <body class="container-fluid" style="font-family: 'Lato', sans-serif" >
        <div class="row">
            <div class="col-lg-3 grid_container">
                <?php include 'menu.php'; ?>
                <script> document.getElementById("menu_cli").className += " active"; </script>
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
                                <button class="cancel" onclick="history.back()">Voltar</button>
                            </div>
                        </div>
                        <div class="row">
                            <h1 class="txt_green"><i class="material-icons">reorder</i> Lista de Clientes — Detalhes <?=$nome?>
                            </h1>
                        </div>
                        <hr>    
                        <div class="row">
                            <div class="col-lg-6">                                
                                <label for="cpf">CPF</label><br>
                                <?=$cpf?><br>

                                <label for="nome">Nome</label><br>
                                <?=$nome?><br>

                                <label for="email">E-mail</label><br>
                                <?=$email?><br>
                            </div>
                            <div class="col-lg-6">
                                <label for="endereco">Endereço</label><br>
                                <?=$endereco?><br>

                                <label for="telefone1">Telefone 1</label><br>
                                <?=$telefone1?><br>

                                <label for="telefone2">Telefone 2</label><br>
                                <?=$telefone2?><br>

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
        </script>
        <?php
            if((isset($_GET['view']) && !$result) || isset($_SESSION['bd_error'])) {
                echo "<script> show_error(); </script>";
                unset($_SESSION['bd_error']);
            }
        ?>
    </body>
</html>