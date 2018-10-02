<?php
    include('connection.php');
    
    if(!isset($_SESSION['user_type']))
    {
        header("Location: login.php");
    }

    $flagEdit=0;
    $edit='';
    $cpf='';
    $nome='';
    $email='';
    $endereco='';
    $telefone1='';
    $telefone2='';

    if(isset($_GET['edit'])) {
        $edit = base64url_decode($_GET['edit']);
        $string_query="SELECT * FROM cliente WHERE cpf='".$edit."';";
        $result = pg_query($conn, $string_query);
        $obj = pg_fetch_object($result);
        $cpf=$obj->cpf;
        $nome=$obj->nome;
        $email=$obj->email;
        $endereco=$obj->endereco;
        $telefone1=$obj->telefone_1;
        $telefone2=$obj->telefone_2;
        $flagEdit=1;
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
                            <h1 class="txt_green"><i class="material-icons">reorder</i> Lista de Clientes — <?php
                                if(isset($_GET['editar'])) {
                                    echo "Editar ".$nome;
                                } else {
                                    echo "Novo";
                                }?>
                            </h1>
                        </div>
                        <hr>
                        <form action="data_store.php?cadastro=<?php echo base64url_encode("cliente");?>" method="post">
                            <input type="hidden" name="flagEdit" value="<?php echo $flagEdit; ?>">
                            <input type="hidden" name="codigo" value="<?php echo $edit; ?>">
                            
                            <div class="row">
                                <div class="col-lg-6">                                
                                    <label for="cpf">CPF</label><br>
                                    <input class="flex" type="text" name="cpf" id="cpf" placeholder="11 dígitos" value="<?=$cpf?>" required autofocus pattern=".{12}"><br>
                                    
                                    <label for="nome">Nome</label><br>
                                    <input class="flex" type="text" name="name" id="name" placeholder="Nome" value="<?=ucwords($nome)?>" required maxlength="64"><br>
                                    
                                    <label for="email">E-mail</label><br>
                                    <input class="flex" type="text" name="email" id="email" placeholder="E-mail" value="<?=$email?>" required maxlength="64"><br>
                                    
                                    <label for="endereco">Endereço</label><br>
                                    <input class="flex" type="text" name="endereco" id="endereco" placeholder="Endereço" value="<?=ucwords($endereco)?>" required maxlength="64"><br>

                                    <label for="telefone1">Telefone 1</label><br>
                                    <input class="flex" type="tel" name="telefone1" id="telefone1" placeholder="Apenas números" value="<?=$telefone1?>" required pattern=".{14,15}"><br>

                                    <label for="telefone2">Telefone 2</label><br>
                                    <input class="flex" type="tel" name="telefone2" id="telefone2" placeholder="Apenas números" value="<?=$telefone2?>" pattern=".{14,15}"><br>

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
            function show_error() {
                $("#bd_msg").show();
            }
            
            $("#cpf").mask("000000000-00");
            
            $("#name").mask("A", {'translation': {
                A: {pattern: /[A-Za-z0-9\s]/, recursive: true},
            }});
                        
            var telOptions = {
                onKeyPress: function(val, e, field, options) {
                    var masks = ['(00) 0000-00009', '(00) 00000-0000'];
                    var mask = (val.replace(/\D/g, '').length==12) ? masks[1] : masks[0];
                    $(field).mask(mask, telOptions);
                }
            };
            
            $("input[type=tel]").each(function(pagination) {
                var masks = ['(00) 00009-0000', '(00) 00000-0000'];
                var val = $(this).val();
                $(this).mask(((val.replace(/\D/g, '').length==11) ? masks[1] : masks[0]), telOptions);
            }); 
        </script>
        <?php
            if((isset($_GET['edit']) && !$result) || isset($_SESSION['bd_error'])) {
                echo "<script> show_error(); </script>";
                unset($_SESSION['bd_error']);
            }
        ?>
    </body>
</html>