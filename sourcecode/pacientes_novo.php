<?php
    include('connection.php');
    
    if(!isset($_SESSION['user_type']))
    {
        header("Location: login.php");
    }

    $flagEdit=0;
    $edit_cpf='';
    $edit_nome='';
    $nome='';
    $raca='';
    $especie='';
    $nascimento='';
    $peso='';

    if(isset($_GET['edit']) && isset($_GET['cpf'])) {
        $edit_cpf = base64url_decode($_GET['cpf']);
        $edit_nome = base64url_decode($_GET['edit']);
        
        $string_query="SELECT * FROM paciente WHERE cpf='".$edit_cpf."' and nome = '".$edit_nome."';";
        $result = pg_query($conn, $string_query);
        $obj = pg_fetch_object($result);
        $nome=$obj->nome;
        $raca=$obj->raca;
        $nascimento=$obj->data_nascimento;
        $peso=$obj->peso;
        $flagEdit=1;

        $string_query="SELECT * FROM paciente_especie WHERE raca = '".$raca."';";
        $result = pg_query($conn, $string_query); 
        $obj_esp = pg_fetch_object($result);
        $especie=$obj_esp->especie;
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>SVS - Pacientes</title>
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
                            <h1 class="txt_green"><i class="material-icons">reorder</i> Lista de Clientes — Pacientes <?php
                                if(isset($_GET['editar'])) {
                                    echo "Editar ".$nome;
                                } else {
                                    echo "Novo";
                                }?></h1>
                        </div>
                        <hr>

                        <form action="data_store.php?cadastro=<?php echo base64url_encode("paciente");?>" method="post">
                            <input type="hidden" name="flagEdit" value="<?php echo $flagEdit; ?>">
                            <input type="hidden" name="cpf" value="<?php echo $edit_cpf; ?>">
                            <input type="hidden" name="nome" value="<?php echo $edit_nome; ?>">
                            <input type="hidden" id="nova_especie" name="nova_especie" value="0" >
                            <input type="hidden" name="especieorigin" id="especieorigin" value="<?=$especie?>">

                            <div class="row">
                                <div class="col-lg-6">                                
                                    <label for="name">Nome</label><br>
                                    <input class="flex" type="text" name="name" id="name" placeholder="Nome" value="<?=ucwords($nome)?>" required autofocus maxlength="64"><br>

                                    <label for="raca">Raça</label><br>
                                    <input class="flex" type="text" name="raca" id="raca" placeholder="Raça" value="<?=ucwords($raca)?>" required maxlength="32" onkeyup="ver_raca()" onblur="ver_raca()"><br>

                                    <label for="especie">Especie</label><br> 
                                    <input class="flex" type="text" name="especie" id="especie" placeholder="Especie" value="<?=ucwords($especie)?>" required maxlength="32">
                                    
                                    <label for="birthday">Data Nascimento</label><br>
                                    <input class="flex" type="date" name="birthday" id="birthday" value="<?=$nascimento?>" required><br>
                                    
                                    <label for="weight">Peso</label><br>
                                    <input class="flex" type="text" name="weight" id="weight" placeholder="Apenas números" value="<?=$peso?>" required><br>
                                </div>
                            </div>
                            <div class="button_footer">
                                <button type="button" class="cancel" onclick="history.back()"><i class="material-icons">arrow_back_ios</i>Retornar</button>
                                <button type="submit" class="confirm"><i class="material-icons">check</i>Confirmar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script>
            function ver_raca() {
                var ajax = new XMLHttpRequest();
                var url = "ajax_raca.php?raca="+ document.getElementById("raca").value;
                // Seta tipo de requisição e URL com os parâmetros
                ajax.open("GET", url, true);

                // Envia a requisição
                ajax.send();

                // Cria um evento para receber o retorno.
                ajax.onreadystatechange = function() {
                // Caso o state seja 4 e o http.status for 200, é porque a requisiçõe deu certo.
                    if (ajax.readyState == 4 && ajax.status == 200) {
                        var data = JSON.parse(ajax.response);
                        // Retorno do Ajax
                        if(data[0] == "0"){
                            document.getElementById("especie").disabled = false;
                            document.getElementById("especie").value = "";
                            document.getElementById("nova_especie").value = "1";
                        }else{
                            document.getElementById("especie").disabled = false;                            
                            document.getElementById("especie").value = data[1];
                            document.getElementById("especieorigin").vale = data[1];
                            document.getElementById("nova_especie").value = "0";
                        }
                        console.log(data);
                    }

                }

            }

            function show_error() {
                $("#bd_msg").show();
            }
            
            $("#name, #raca, #especie").mask("A", {'translation': {
                A: {pattern: /[A-Za-z0-9\s]/, recursive: true},
            }});
            
             $("#weight").mask("#0.00", {reverse: true});
        </script>
        <?php
            if((isset($_GET['edit']) && !$result) || isset($_SESSION['bd_error'])) {
                echo "<script> show_error(); </script>";
                unset($_SESSION['bd_error']);
            }
        ?>
    </body>
</html>