<?php
    include('connection.php');
    
    if(!isset($_SESSION['user_type']))
    {
        header("Location: login.php");
    }

    $flagEdit=0;
    $edit='';
    $crmv = '';
    $nome = '';
    $email = '';
    $salario = '';
    $telefone_1 = '';
    $telefone_2 = '';
    $especialidade = '';

    if(isset($_GET['edit'])) {
        $edit = base64url_decode($_GET['edit']);
        $string_query= "SELECT * FROM veterinario WHERE crmv='".$edit."';";
        $result = pg_query($conn, $string_query);
        $obj = pg_fetch_object($result);
        $crmv=$obj->crmv;
        $nome=ucwords(strtolower($obj->nome));
        $email=$obj->email;
        $salario=number_format($obj->salario, 2, ',', '.');
        $telefone_1=$obj->telefone_1;
        $telefone_2=$obj->telefone_2;
        $string_query="SELECT especialidade FROM vet_especialidade WHERE crmv='".$edit."';";
        $result = pg_query($conn, $string_query);
        $obj = pg_fetch_assoc($result);
        $especialidade = ucwords($obj['especialidade']);
        while($obj = pg_fetch_assoc($result))
                $especialidade .= ','.ucwords($obj['especialidade']);
        $flagEdit=1;
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
                            <h1 class="txt_green"><i class="material-icons">person</i> Lista de Veterinários — <?php
                                if(isset($_GET['edit'])) {
                                    echo "Editar ".$nome;
                                } else {
                                    echo "Novo";
                                }?></h1>
                        </div>
                        <hr>
                        <form action="data_store.php?cadastro=<?php echo base64url_encode("veterinario");?>" method="post">
                            <input type="hidden" name="flagEdit" value="<?php echo $flagEdit; ?>">
                            <input type="hidden" name="codigo" value="<?php echo $edit; ?>">    
                            
                            <div class="row">
                                <div class="col-lg-6">
                                    
                                    <label for="crmv">CRMV</label><br>
                                    <input pattern=".{6}" class="flex" type="text" name="crmv" id="crmv" placeholder="6 dígitos" value="<?=$crmv?>" required autofocus><br>
                                
                                    <label for="name">Nome</label><br>
                                    <input class="flex" type="text" name="name" id="name" placeholder="Texto" value="<?=$nome?>" required maxlength="64"><br>

                                    <label for="email">E-mail</label><br>
                                    <input class="flex" type="email" name="email" id="email" placeholder="E-mail" value="<?=$email?>" required maxlength="64"><br>
                                    
                                    <label for="spec">Especialidade</label><br>
                                    <p class="description">Insira as especialidades seperadas por vírgula. Ex.: Patologia,Oncologia,Acupuntura</p>
                                    <input class="flex" type="text" name="spec" id="spec" placeholder="Texto" value="<?=$especialidade?>"><br>
                                </div>
                                <div class="col-lg-6">
                                    <label for="salario">Salário</label><br>
                                    <input class="flex" type="text" name="salary" id="salary" placeholder="Apenas números" value="<?=$salario?>" required><br>
                                    
                                    <label for="tel1">Telefone 1</label><br>
                                    <input pattern=".{13,14}" class="flex" type="tel" name="tel1" id="tel1" placeholder="Apenas números" value="<?=$telefone_1?>" required><br>

                                    <label for="tel2">Telefone 2</label><br>
                                    <input pattern=".{13,14}" class="flex" type="tel" name="tel2" id="tel2" placeholder="Apenas números" value="<?=$telefone_2?>">
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
            
            function warning_close() {
                $(".warning").hide();
            }
            
            $("#crmv").mask("000000");
            
            $("#name").mask("A", {'translation': {
                A: {pattern: /[A-Za-z0-9\s]/, recursive: true},
            }});
            
            $("#salary").mask("#0.00", {reverse: true});
            
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