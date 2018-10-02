<?php
    include('connection.php');
    
    if(!isset($_SESSION['user_type']))
    {
        header("Location: login.php");
    }
    
    $especialidade='';
    $salario='';

    if(isset($_GET['spec']) && isset($_GET['salary']) && ($_GET['spec'] != '') && ($_GET['salary']!= '') && is_numeric($_GET['salary'])) {
        $especialidade = pg_escape_string(strtolower($_GET['spec']));
        $salario = pg_escape_string(($_GET['salary']));
        
        $string_query = "SELECT * FROM consulta_avancada_veterinario('".$especialidade."', '".$salario."')";
        
        $result = pg_query($conn, "SELECT COUNT(*) FROM (".$string_query.") AS SUBQ");
        $numRows = pg_fetch_row($result)[0];
        $rowsPerPage = 10;
        $totalPages = ceil($numRows / $rowsPerPage);

        if(isset($_GET['page']) && is_numeric($_GET['page'])) {
            $page = (int) $_GET['page'];

            if ($page < 1) {
                $page = 1;
            } elseif ($page > $totalPages) {
                $page = $totalPages;
            }
        } else {
            $page = 1;
        }
        
        $offset = ($page - 1) * $rowsPerPage;
            
        $result = pg_query($conn, $string_query." LIMIT ".$rowsPerPage." OFFSET ".$offset);
    } elseif(isset($_GET['spec']) || isset($_GET['salary'])){
        // Tira os parâmetros inúteis
        $param = $_GET;
        unset($param['spec']);
        unset($param['salary']);
        header("Location: ?".http_build_query($param));
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
                                <button class="cancel" onclick="history.back()">Voltar</button>
                            </div>
                        </div>
                        <div class="row">
                            <h1 class="txt_green"><i class="material-icons">person</i> Lista de Veterinários — Busca Avançada</h1>
                        </div>
                        <hr>
                        <form method="get">
                            <div class="row">
                                <div class="col-lg-6">
                                    <label for="spec">Especialidade</label><br>
                                    <p class="description">*Retorna todos os veterinários com especialidade igual</p>
                                    <input class="flex" type="text" name="spec" id="spec" placeholder="Texto" value="<?=ucwords($especialidade)?>" required autofocus><br>
                                    
                                    <label for="salary">Salário</label><br>
                                    <p class="description">*Retorna todos os veterinários com salário a partir de</p>
                                    <input class="flex" type="text" name="salary" id="salary" placeholder="Apenas números" value="<?=$salario?>" required><br>
                                </div>
                            </div>
                            <div class="button_footer">
                                <button type="button" class="cancel" onclick="history.back()"><i class="material-icons">arrow_back_ios</i>Retornar</button>
                                <button type="submit" class="adv_search" ><i class="material-icons">search</i>Buscar</button>
                            </div>
                            <?php if(isset($_GET['spec']) && isset($_GET['salary']) && ($_GET['spec'] != '') && ($_GET['salary']!= '') && is_numeric($_GET['salary'])) { ?>
                            <div class="row">
                                <h1 class="txt_green"><i class="material-icons">search</i> Resultados</h1>
                            </div>
                            <hr>
                            <div class="table_frame">
                            <table class="table_query">
                                <thead>
                                <tr>
                                    <th class="no_anchor">CRMV</th>
                                    <th class="no_anchor">Nome</th>
                                    <th class="no_anchor">E-mail</th>
                                    <th class="no_anchor">1º Telefone</th>
                                    <th class="no_anchor">Salário</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                    while($value = pg_fetch_assoc($result)) {
                                ?>
                                <tr>
                                    <td class="expansible center">  <?=$value['crmv']?></td>
                                    <td class="expansible">         <?=ucwords(strtolower($value['nome']))?></td>
                                    <td class="expansible center">  <?=$value['email']?></td>
                                    <td class="expansible center">  <?=$value['telefone_1']?></td>
                                    <td class="expansible center">  <?='R$ '.number_format($value['salario'], 2, ',', '.')?></td>
                                </tr>
                                <?php
                                    }
                                ?>
                                </tbody>
                            </table>
                        </div>
                        <?php include 'pagination.php'; } ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>        
        <script>            
            function show_error() {
                $("#bd_msg").show();
            }
            
            $("#spec").mask("A", {'translation': {
                A: {pattern: /[A-Za-z0-9\s]/, recursive: true},
            }});
            
            $("#salary").mask("#0,00", {reverse: true});
        </script>
        <?php
            if(isset($_GET['spec']) && isset($_GET['salary']) && ($_GET['spec'] != '') && ($_GET['salary']!= '')  && is_numeric($_GET['salary']) && !$result) {
                echo "<script> show_error(); </script>";
            }
        ?>
    </body>
</html>