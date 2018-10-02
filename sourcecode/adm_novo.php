<?php
    include('connection.php');
    
    if(!isset($_SESSION['user_type']))
    {
        header("Location: login.php?erro_login=1");
    } elseif($_SESSION['user_type'] != 1) {
        header("Location: home.php");
    }

    $flagEdit=0;
    $edit='';
    $usuario = '';
    $tipo = '';

    if(isset($_GET['edit'])) {
        $edit = base64url_decode($_GET['edit']);
        $string_query="SELECT * FROM login WHERE usuario='".$edit."';";
        $result = pg_query($conn, $string_query);
        $obj = pg_fetch_object($result);
        $usuario=$obj->usuario;
        $tipo=$obj->tipo;
        //$senha=$obj->senha;
        $flagEdit=1;
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>SVS - Administração</title>
        <?php include 'common_meta.php'; ?>
    </head>
    <!-- Fonte declarada inline para compatibilidade com Chrome -->
    <body class="container-fluid" style="font-family: 'Lato', sans-serif" >
        <div class="row">
            <div class="col-lg-3 grid_container">
                <?php include 'menu.php'; ?>
                <script> $("#menu_adm").addClass("active"); </script>
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
                            <h1 class="txt_green col-lg-12"><i class="material-icons">settings</i> Administração — <?php
                                if(isset($_GET['edit'])) {
                                    echo "Editar ".$usuario;
                                } else {
                                    echo "Novo";
                                }?></h1>
                        </div>
                        <hr>
                        <form action="data_store.php?cadastro=<?php echo base64url_encode("usuario");?>" method="post">
                            <input type="hidden" name="flagEdit" value="<?php echo $flagEdit; ?>">
                            <input type="hidden" name="codigo" value="<?php echo $edit; ?>">
                            
                            <div class="row">
                                <div class="col-lg-6">                                
                                    <label for="username">Usuário</label><br>
                                    <input class="flex" type="text" name="username" id="username" placeholder="Usuário" value="<?=$usuario?>" required autofocus><br>

                                    <label for="type">Tipo</label><br>
                                    <select class="type_select" id="type" name="type" required>
                                        <option value="0">Funcionário</option>
                                        <option value="1">Administrador</option>
                                        <option selected disabled hidden>Selecionar Tipo</option>
                                    </select><br>
                                    
                                    <script>
                                        var sel = "<?=$tipo?>";
                                        
                                        if(sel != '') {
                                            $('#type').val(sel);
                                        }
                                    </script>
                                </div>
                                <div class="col-lg-6"> 
                                    <label for="password">Senha</label><br>
                                    <input class="flex" type="password" name="password" id="password" placeholder="Senha" required><br>
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
            
            $("#username").mask("A", {'translation': {
                A: {pattern: /[a-z0-9_]/, recursive: true},
            }});

        </script>
        <?php
            if((isset($_GET['edit']) && !$result) || isset($_SESSION['bd_error'])) {
                echo "<script> show_error(); </script>";
                unset($_SESSION['bd_error']);
            }
        ?>
    </body>
</html>