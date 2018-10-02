<?php
include('connection.php');
    
    if(!isset($_SESSION['user_type']))
    {
        header("Location: login.php");
    }

    if(isset($_GET['cpf']) && $_GET['cpf']!='') {
        $cpf = base64url_decode($_GET['cpf']);
        
        if(isset($_GET['excluir'])) {
            $result = pg_query($conn, "DELETE FROM paciente WHERE cpf='".$cpf."' and nome='".base64url_decode($_GET['excluir'])."'");

            if($result) {
                // Tirar parâmetro 'excluir'
                $param = $_GET;
                unset($param['excluir']);
                header("Location: ?".http_build_query($param));
            }
        } else  {
            // Ordenação padrão
            $orderMethod = 'nome';
            if(isset($_GET['order'])) {
                if($_GET['order'] == 'raca') {
                    $orderMethod = 'P.raca';
                } elseif($_GET['order'] == 'especie'){
                    $orderMethod = 'Pe.especie';
                } elseif($_GET['order'] == 'peso') {
                    $orderMethod = 'peso';
                } elseif($_GET['order'] == 'idade'){
                    $orderMethod = 'data_nascimento DESC';
                }
            }

            // Verificar se está sendo realizada busca
            if(isset($_GET['searchterm']) && isset($_GET['searchcategory']) && $_GET['searchterm']!="") {
                $search_column = pg_escape_string(($_GET['searchcategory']));
                $search_term = strtolower(pg_escape_string(($_GET['searchterm'])));

                if($_GET['searchcategory'] == "nome") {
                    $string_query="and cpf='".$cpf."' and nome LIKE '%".$search_term."%'";
                } elseif($_GET['searchcategory'] == "raca") {
                        $string_query="and cpf='".$cpf."' and P.raca LIKE '%".$search_term."%'";
                }  elseif($_GET['searchcategory'] == "especie") {
                    $string_query="and cpf='".$cpf."' and Pe.especie like '%".$search_term."%'";
                }   elseif($_GET['searchcategory'] == "peso") {
                    $string_query="and cpf='".$cpf."' and peso = '".$search_term."'";
                }   elseif($_GET['searchcategory'] == "idade") {
                    $string_query="and cpf='".$cpf."' and (Select Extract(Year from AGE(P.data_nascimento ))) = '".$search_term."'";
                } else {
                    // Utilizando categoria (coluna) que não existe
                    $param = $_GET;
                    unset($param['searchterm']);
                    unset($param['searchcategory']);
                    header("Location: ?".http_build_query($param));
                }
            } elseif(isset($_GET['searchterm']) || isset($_GET['searchcategory'])){
                // Tira os parâmetros inúteis
                $param = $_GET;
                unset($param['searchterm']);
                unset($param['searchcategory']);
                header("Location: ?".http_build_query($param));
            } else {        
                $string_query="and cpf = '".$cpf."'";
            }

            $result = pg_query($conn, "SELECT COUNT(*) FROM (select * FROM paciente as P,paciente_especie as Pe WHERE P.raca = Pe.raca ".$string_query.") AS SUBQ");
            $numRows = pg_fetch_row($result)[0];
            $rowsPerPage = 10;
            $totalPages = ceil($numRows / $rowsPerPage);

            if(isset($_GET['page']) && is_numeric($_GET['page'])) {
                $page = (int) $_GET['page'];

                if ($page < 1 || $totalPages == 0) {
                    $page = 1;
                } elseif ($page > $totalPages) {
                    $page = $totalPages;
                }
            } else {
                $page = 1;
            }

            $offset = ($page - 1) * $rowsPerPage;

            $result = pg_query($conn,"select * FROM paciente as P,paciente_especie as Pe WHERE P.raca = Pe.raca $string_query order by $orderMethod limit $rowsPerPage offset $offset");
        }
    } else {
        header("Location: home.php");
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
                        <div id="del_msg" class="warning">
                            <hr>
                            <h1 class="txt_green">Remover</h1>
                            <hr>
                            <div class="warning_content">
                                Tem certeza que deseja remover?
                            </div>
                            <div class="warning_buttons">
                                <button class="cancel" onclick="warning_close()"><i class="material-icons">cancel</i> Cancelar</button>
                                <a class="remove" id="excluir_link" href=""><i class="material-icons">remove_circle</i> Remover</a>
                            </div>
                        </div>
                        <div class="row">
                            <h1 class="txt_green col-lg-6"><i class="material-icons">group</i> Lista de Clientes — Pacientes</h1>
                            <div class="search_frame col-lg-6">
                                <form method="get">
                                    <select class=" " id="search_select" name="searchcategory" onchange="input_change(true)">
                                        <option value="nome">Nome</option>
                                        <option value="raca">Raça</option>
                                        <option value="especie">Espécie</option>
                                        <option value="peso">Peso</option>
                                        <option value="idade">Idade</option>
                                        <option selected disabled hidden>Selecionar Categoria</option>
                                    </select>
                                    
                                    <script>
                                        var sel = "<?=(isset($_GET['searchcategory']) ? $_GET['searchcategory'] : '')?>";
                                        
                                        if(sel != '') {
                                            $('#search_select').val(sel);
                                        }
                                    </script>
                                    
                                    <input class="search_input" id="search_input" type="text" name="searchterm" placeholder="Termo para busca" <?=(isset($_GET['searchterm']) ? 'value="'.$_GET['searchterm'].'"' : '')?>>
                                    <input type="hidden" id="cpf" name="cpf" value=<?=$_GET['cpf'] ?> >
                                    <button type="submit" class="search"><i class="material-icons">location_searching</i></button>
                                </form>
                            </div>
                        </div>
                        <hr>
                        <div class="table_frame">
                            <table class="table_query">
                                <thead>
                                <tr>
                                    <th id="th_nome" data-toggle="tooltip" title="Ordenar"><a href="?<?=http_build_query(array_merge($_GET, array("order"=> "nome")))?>">Nome</a></th>
                                    <th id="th_raca" data-toggle="tooltip" title="Ordenar"><a href="?<?=http_build_query(array_merge($_GET, array("order" => "raca")))?>">Raça</a></th>
                                    <th id="th_especie" data-toggle="tooltip" title="Ordenar"><a href="?<?=http_build_query(array_merge($_GET, array("order" => "especie")))?>">Espécie</a></th>
                                    <th id="th_peso" data-toggle="tooltip" title="Ordenar"><a href="?<?=http_build_query(array_merge($_GET, array("order" => "peso")))?>">Peso</a></th>
                                    <th id="th_idade" data-toggle="tooltip" title="Ordenar"><a href="?<?=http_build_query(array_merge($_GET, array("order" => "idade")))?>">Idade</a></th>                                    
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                    while($value = pg_fetch_assoc($result)) {
                                        $result_age = pg_query($conn,"Select Extract(Year from AGE(timestamp '".$value['data_nascimento']."' ))");
                                        $value_age = pg_fetch_assoc($result_age);
                                ?>
                                <tr>
                                    <td class="expansible center">  <?=ucwords($value['nome'])?></td>
                                    <td class="expansible">         <?=ucwords($value['raca'])?></td>
                                    <td class="expansible center">  <?=ucwords($value['especie'])?></td>
                                    <td class="expansible center">  <?=$value['peso']?></td>
                                    <td class="expansible center">  <?=$value_age['date_part']?> anos </td> 
                                    <td class="button_column">
                                        <div class="drop_menu">
                                            <button class="drop"><i class="material-icons">expand_more</i></button>
                                            <div class="drop_content">
                                                <a href="pacientes_novo.php?cpf=<?=base64url_encode($value['cpf']);?>&edit=<?=base64url_encode($value['nome']);?>">Editar</a>
                                                <button onclick="msg_remover('<?php
                                                    $params = array_merge($_GET, array("excluir" => base64url_encode($value['nome']),"cpf" => $_GET["cpf"]));
                                                    echo '?'.http_build_query($params);
                                                ?>')">Remover</button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                                    }
                                ?>
                                </tbody>
                            </table>
                        </div>
                        <?php include 'pagination.php'; ?>
                        <div class="button_footer">
                            <a class="cancel" href="clientes.php"><i class="material-icons">arrow_back_ios</i> Retornar</a>
                            <a class="confirm" href="pacientes_novo.php?cpf=<?=$_GET['cpf']?>"><i class="material-icons">add</i> Novo</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            function msg_remover(encodedURL) {
                var link = encodedURL;
                $("#excluir_link").attr("href", link);
                $("#del_msg").show();
            }

            function show_error() {
                $("#bd_msg").show();
            }

            function warning_close() {
                $(".warning").hide();
            }
            
            function input_change(reset){
                var cat = $("#search_select").val();
                var sInput = $("#search_input");
                
                if (cat == "nome" || cat == "especie" || cat == "raca") {
                    sInput.removeAttr("pattern");
                    sInput.mask("A", {'translation': {A: {pattern: /[A-Za-z0-9\s]/, recursive: true}}});
                    sInput.attr("placeholder", "Texto");
                    sInput.attr("autocomplete", "on");
                } else if (cat == "peso" || cat == "idade") {
                    sInput.removeAttr("pattern");
                    sInput.mask("0#");
                    sInput.attr("placeholder", "Apenas números");
                    sInput.attr("autocomplete", "on");
                }
                
                if (reset) {
                    sInput.val('');
                }
            }
            input_change(false);
            
            $("#th_<?=(isset($_GET['order'])) ? $_GET['order'] : $orderMethod?>").addClass('active');
        </script>
        <?php
            if(!isset($_GET['excluir']) && (!$result || isset($_SESSION['bd_error']))) {
                echo "<script> show_error(); </script>";
                unset($_SESSION['bd_error']);
            }
        ?>
    </body>
</html>