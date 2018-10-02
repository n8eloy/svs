<?php
    include('connection.php');
    
    if(!isset($_SESSION['user_type']))
    {
        header("Location: login.php");
    }
        
    $cpf='';

    if(isset($_GET['cpf']) && ($_GET['cpf'] != '')) {
        $cpf = pg_escape_string(($_GET['cpf']));
        
        $string_query = "SELECT * FROM consulta_avancada_cliente('".$cpf."')";
                    
        $result = pg_query($conn, $string_query);
    } elseif(isset($_GET['cpf'])) {
        // Tira os parâmetros inúteis
        $param = $_GET;
        unset($param['cpf']);
        header("Location: ?".http_build_query($param));
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
                            <h1 class="txt_green"><i class="material-icons">group</i> Lista de Clientes — Busca Avançada</h1>
                        </div>
                        <hr>
                        <form method="get">
                            <div class="row">
                                <div class="col-lg-6">
                                    <label for="cpf">CPF</label><br>
                                    <p class="description">*Retorna detalhes dos procedimentos para o CPF inserido</p>
                                    <input class="flex" type="text" name="cpf" id="cpf" placeholder="CPF" value="<?=$cpf?>" required autofocus><br>
                                </div>
                            </div>
                            <div class="button_footer">
                                <button type="button" class="cancel" onclick="history.back()"><i class="material-icons">arrow_back_ios</i>Retornar</button>
                                <button type="submit" class="adv_search" ><i class="material-icons">search</i>Buscar</button>
                            </div>
                            <?php if(isset($_GET['cpf']) && ($_GET['cpf']!= '')) { ?>
                            <div class="row">
                                <h1 class="txt_green"><i class="material-icons">search</i> Resultados</h1>
                            </div>
                            <hr>
                            <div class="table_frame">
                            <table class="table_query">
                                <thead>
                                <tr>
                                    <th class="no_anchor">CPF</th>
                                    <th class="no_anchor">Nome</th>
                                    <th class="no_anchor">Endereço</th>
                                    <th class="no_anchor">Tipo</th>
                                    <th class="no_anchor">Custo Total</th>
                                    <th class="no_anchor">Quantidade Realizada</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                    while($value = pg_fetch_assoc($result)) {
                                ?>
                                <tr>
                                    <td class="expansible center">  <?=$value['cpf']?></td>
                                    <td class="expansible">         <?=ucwords(strtolower($value['nome']))?></td>
                                    <td class="expansible center">  <?=ucwords($value['endereco'])?></td>                                    
                                    <td class="expansible center">  <?=ucwords($value['tipo'])?></td>
                                    <td class="expansible center">  <?='R$ '.number_format($value['custo_total'], 2, ',', '.')?></td>
                                    <td class="expansible center">  <?=$value['qtd_realizada']?></td>
                                </tr>
                                <?php
                                    }
                                ?>
                                </tbody>
                            </table>
                        </div>
                        <?php } ?>
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
            
            $("#spec").mask("A", {'translation': {
                A: {pattern: /[A-Za-z0-9\s]/, recursive: true},
            }});
            
            $("#salary").mask("#0.00", {reverse: true});
        </script>
        <?php
            if(isset($_GET['cpf']) && ($_GET['cpf'] != '') && !$result) {
                echo "<script> show_error(); </script>";
            }
        ?>
    </body>
</html>