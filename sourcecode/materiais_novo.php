<?php
    include('connection.php');
    
    if(!isset($_SESSION['user_type']))
    {
        header("Location: login.php");
    }

    $flagEdit=0;
    $edit='';
    $nome='';
    $qtd='';

    if(isset($_GET['edit'])) {
        $edit = base64url_decode($_GET['edit']);
        $string_query="SELECT * FROM material WHERE codigo='".$edit."';";
        $result = pg_query($conn, $string_query);
        $obj = pg_fetch_object($result);
        $nome=ucwords(strtolower($obj->nome));
        $qtd=$obj->qtd_estoque;
        $flagEdit=1;
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>SVS - Materiais</title>
        <?php include 'common_meta.php'; ?>
    </head>
    <!-- Fonte declarada inline para compatibilidade com Chrome -->
    <body class="container-fluid" style="font-family: 'Lato', sans-serif" >
        <div class="row">
            <div class="col-lg-3 grid_container">
                <?php include 'menu.php'; ?>
                <script> $("#menu_mat").addClass("active"); </script>
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
                            <h1 class="txt_green"><i class="material-icons">reorder</i> Estoque de Materiais — <?php
                                if(isset($_GET['edit'])) {
                                    echo "Editar ".$nome;
                                } else {
                                    echo "Novo";
                                }?></h1>
                        </div>
                        <hr>
                        <form action="data_store.php?cadastro=<?php echo base64url_encode("material");?>" method="post">
                            <input type="hidden" name="flagEdit" value="<?php echo $flagEdit; ?>">
                            <input type="hidden" name="codigo" value="<?php echo $edit; ?>">
                            
                            <div class="row">
                                <div class="col-lg-6">
                                    <label for="name">Nome</label><br>
                                    <input class="flex" type="text" name="name" id="name" placeholder="Texto" value="<?=$nome?>" required autofocus maxlength="64"><br>

                                    <label for="stockamount">Quantidade em Estoque</label><br>
                                    <input class="flex" type="text" name="stockamount" id="stockamount" placeholder="Apenas números" value="<?=$qtd?>" required><br>
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
            
            $("#name").mask("A", {'translation': {
                A: {pattern: /[A-Za-z0-9\s]/, recursive: true},
            }});
            
            $("#stockamount").mask("0#");
        </script>
        <?php
            if((isset($_GET['edit']) && !$result) || isset($_SESSION['bd_error'])) {
                echo "<script> show_error(); </script>";
                unset($_SESSION['bd_error']);
            }
        ?>
    </body>
</html>