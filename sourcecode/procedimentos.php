<?php
    include('connection.php');
    
    if(!isset($_SESSION['user_type']))
    {
        header("Location: login.php");
    }
    
    $excluir;

    if(isset($_GET['excluir'])) {
        // Separa data e sala do procedimento
        $del_code = explode('&', base64url_decode($_GET['excluir']));
        
        // Como utiliza e realiza estão configurados como CASCADE, é necessário remover apenas o procedimento
        $result = pg_query($conn, "DELETE FROM procedimento WHERE data='".$del_code[0]."' AND sala='$del_code[1]';");
        
        if($result) {
            // Tirar parâmetro 'excluir'
            $param = $_GET;
            unset($param['excluir']);
            header("Location: ?".http_build_query($param));
        }
    } else  {
        // Ordenação padrão
        $orderMethod = 'data';
        if(isset($_GET['order'])) {
            if($_GET['order'] == 'sala') {
                $orderMethod = 'sala';
            } elseif($_GET['order'] == 'tipo') {
                $orderMethod = 'tipo';
            } elseif($_GET['order'] == 'custo') {
                $orderMethod = 'custo';
            } elseif($_GET['order'] == 'cpf') {
                $orderMethod = 'cpf_pac';
            } elseif($_GET['order'] == 'nome') {
                $orderMethod = 'nome_pac';
            }
        }        
        // Verificar se está sendo realizada busca
        if(isset($_GET['searchterm']) && isset($_GET['searchcategory']) && $_GET['searchterm']!="") {
            $search_column = pg_escape_string(($_GET['searchcategory']));
            $search_term = strtolower(pg_escape_string(($_GET['searchterm'])));

            if($_GET['searchcategory'] == "data") {
                $string_query="SELECT * FROM procedimento WHERE data::date = date'".$search_term."'";
            } elseif($_GET['searchcategory'] == "sala") {
                $string_query="SELECT * FROM procedimento WHERE sala='".$search_term."'";
            } elseif($_GET['searchcategory'] == "tipo") {
                $string_query="SELECT * FROM procedimento WHERE tipo ILIKE '%".$search_term."%'";
            } elseif($_GET['searchcategory'] == "custo") {
                $string_query="SELECT * FROM procedimento WHERE custo='".$search_term."'";
            } elseif($_GET['searchcategory'] == "cpf") {
                $string_query="SELECT * FROM procedimento WHERE cpf_pac='".$search_term."'";
            } elseif($_GET['searchcategory'] == "nome") {
                $string_query="SELECT * FROM procedimento WHERE nome_pac LIKE '%".$search_term."%'";
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
            $string_query="SELECT * FROM procedimento";
        }
        
        $result = pg_query($conn, "SELECT COUNT(*) FROM (".$string_query.") AS SUBQ");
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

        $result = pg_query($conn, $string_query." ORDER BY ".$orderMethod." LIMIT ".$rowsPerPage." OFFSET ".$offset);
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>SVS - Procedimentos</title>
        <?php include 'common_meta.php'; ?>
    </head>
    <!-- Fonte declarada inline para compatibilidade com Chrome -->
    <body class="container-fluid" style="font-family: 'Lato', sans-serif" >
        <div class="row">
            <div class="col-lg-3 grid_container">
                <?php include 'menu.php'; ?>
                <script> $("#menu_proc").addClass("active"); </script>
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
                            <h1 class="txt_green col-lg-6"><i class="material-icons">healing</i> Procedimentos Realizados</h1>
                            <div class="search_frame col-lg-6">
                                <form method="get">
                                    <select class="search_select" id="search_select" name="searchcategory" onchange="input_change(true)">
                                        <option value="data">Data</option>
                                        <option value="sala">Sala</option>
                                        <option value="tipo">Tipo</option>
                                        <option value="custo">Custo</option>
                                        <option value="cpf">CPF Dono</option>
                                        <option value="nome">Nome Paciente</option>
                                        <option selected disabled hidden>Selecionar Categoria</option>
                                    </select>
                                    
                                    <script>
                                        var sel = "<?=(isset($_GET['searchcategory']) ? $_GET['searchcategory'] : '')?>";
                                        
                                        if(sel != '') {
                                            $('#search_select').val(sel);
                                        }
                                    </script>
                                    
                                    <input class="search_input" id="search_input" type="text" name="searchterm" placeholder="Termo para busca" <?=(isset($_GET['searchterm']) ? 'value="'.$_GET['searchterm'].'"' : '')?>>
                                    <button type="submit" class="search"><i class="material-icons">location_searching</i></button>
                                </form>
                            </div>
                        </div>
                        <hr>
                        <div class="table_frame">
                            <table class="table_query">
                                <thead>
                                <tr>                                    
                                    <th id="th_data" data-toggle="tooltip" title="Ordenar"><a href="?<?=http_build_query(array_merge($_GET, array("order"=> "data")))?>">Data</a></th>
                                    <th id="th_sala" data-toggle="tooltip" title="Ordenar"><a href="?<?=http_build_query(array_merge($_GET, array("order" => "sala")))?>">Sala</a></th>
                                    <th id="th_tipo" data-toggle="tooltip" title="Ordenar"><a href="?<?=http_build_query(array_merge($_GET, array("order" => "tipo")))?>">Tipo</a></th>
                                    <th id="th_custo" data-toggle="tooltip" title="Ordenar"><a href="?<?=http_build_query(array_merge($_GET, array("order" => "custo")))?>">Custo</a></th>
                                    <th id="th_cpf" data-toggle="tooltip" title="Ordenar"><a href="?<?=http_build_query(array_merge($_GET, array("order" => "cpf")))?>">CPF Dono</a></th>
                                    <th id="th_nome" data-toggle="tooltip" title="Ordenar"><a href="?<?=http_build_query(array_merge($_GET, array("order" => "nome")))?>">Nome Paciente</a></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                    while($value = pg_fetch_assoc($result)) {
                                ?>
                                <tr>
                                    <td class="expansible center"><?=$value['data']?></td>
                                    <td class="expansible center"><?=$value['sala']?></td>
                                    <td class="expansible center"><?=$value['tipo']?></td>
                                    <td class="expansible"><?='R$ '.number_format($value['custo'], 2, ',', '.')?></td>
                                    <td class="expansible center"><?=$value['cpf_pac']?></td>
                                    <td class="expansible"><?=ucwords(strtolower($value['nome_pac']))?></td>
                                    <td class="button_column">
                                        <div class="drop_menu">
                                            <button class="drop"><i class="material-icons">expand_more</i></button>
                                            <div class="drop_content">
                                                <a href="procedimentos_detalhes.php?view=<?=base64url_encode($value['data'] . '&' . $value['sala']);?>">Ver Detalhes</a>
                                                <a href="procedimentos_novo.php?edit=<?=base64url_encode($value['data'] . '&' . $value['sala']);?>">Editar</a>
                                                <button onclick="msg_remover('<?php
                                                    $params = array_merge($_GET, array("excluir" => ''.base64url_encode($value['data'].'&'.$value['sala'].'')));
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
                            <a class="confirm" href="procedimentos_novo.php"><i class="material-icons">add</i> Novo</a>
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
            
                if (cat == "data") {
                    //sInput.attr("pattern", ".{10}");
                    //sInput.mask("0000-00-00");
                    sInput.attr("type", "date");
                    //sInput.attr("placeholder", "YYYY-MM-DD");
                } else if (cat == "sala") {
                    sInput.removeAttr("pattern");
                    sInput.removeAttr("maxlength");
                    sInput.mask("0#");
                    sInput.attr("type", "text");
                    sInput.attr("placeholder", "Apenas números");
                    sInput.attr("autocomplete", "on");
                } else if (cat == "tipo") {
                    sInput.removeAttr("pattern");
                    sInput.removeAttr("maxlength");
                    sInput.mask("A", {'translation': {A: {pattern: /[A-Za-z0-9\s]/, recursive: true}}});
                    sInput.attr("type", "text");
                    sInput.attr("placeholder", "Texto");
                    sInput.attr("autocomplete", "on");
                } else if (cat == "custo") {
                    sInput.removeAttr("pattern");
                    sInput.removeAttr("maxlength");
                    sInput.mask("#0.00", {reverse: true});
                    sInput.attr("type", "text");
                    sInput.attr("placeholder", "Apenas números");
                    sInput.attr("autocomplete", "on");
                } else if (cat == "cpf") {
                    sInput.attr("pattern", ".{12}");
                    sInput.mask("000000000-00");
                    sInput.attr("type", "text");
                    sInput.attr("placeholder", "Apenas números");
                    sInput.attr("autocomplete", "on");
                } else if (cat == "nome") {
                    sInput.removeAttr("pattern");
                    sInput.removeAttr("maxlength");
                    sInput.mask("A", {'translation': {A: {pattern: /[A-Za-z0-9\s]/, recursive: true}}});
                    sInput.attr("type", "text");
                    sInput.attr("placeholder", "Texto");
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