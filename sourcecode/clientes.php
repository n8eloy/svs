<?php
    include('connection.php');
    
    if(!isset($_SESSION['user_type']))
    {
        header("Location: login.php");
    }

if(isset($_GET['excluir'])) {
        $result = pg_query($conn, "DELETE FROM cliente WHERE cpf='".base64url_decode($_GET['excluir'])."'");
        
        if($result) {
            // Tirar parâmetro 'excluir'
            $param = $_GET;
            unset($param['excluir']);
            header("Location: ?".http_build_query($param));
        }
    } else  {
        // Ordenação padrão
        $orderMethod = 'cpf';
        if(isset($_GET['order'])) {
            if($_GET['order'] == 'nome') {
                $orderMethod = 'nome';
            } elseif($_GET['order'] == 'email') {
                $orderMethod = 'email';
            } elseif($_GET['order'] == 'telefone') {
                $orderMethod = 'telefone_1,telefone_2';
            }
        }
        
        // Verificar se está sendo realizada busca
        if(isset($_GET['searchterm']) && isset($_GET['searchcategory']) && $_GET['searchterm']!="") {
            $search_column = pg_escape_string(($_GET['searchcategory']));
            $search_term = strtolower(pg_escape_string(($_GET['searchterm'])));

            if($_GET['searchcategory'] == "cpf") {
                $string_query="SELECT * FROM cliente WHERE cpf='".$search_term."'";
            } elseif($_GET['searchcategory'] == "nome") {
                $string_query="SELECT * FROM cliente WHERE nome LIKE '%".$search_term."%'";
            } elseif($_GET['searchcategory'] == "email") {
                $string_query="SELECT * FROM cliente WHERE email LIKE '%".$search_term."%'";
            } elseif($_GET['searchcategory'] == "telefone") {
                $string_query="SELECT * FROM cliente WHERE telefone_1 LIKE '%".$search_term."%'  or telefone_2 LIKE '%".$search_term."%'";
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
            $string_query="SELECT * FROM cliente";
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
                            <h1 class="txt_green col-lg-6"><i class="material-icons">reorder</i> Lista de Clientes</h1>
                            <div class="search_frame col-lg-6">
                                <form method="get">
                                    <select class="search_select" id="search_select" name="searchcategory" onchange="input_change(true)">
                                        <option value="cpf">CPF</option>
                                        <option value="nome">Nome</option>
                                        <option value="email">E-mail</option>
                                        <option value="telefone">Telefone</option>
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
                                    <th id="th_cpf" data-toggle="tooltip" title="Ordenar"><a href="?<?=http_build_query(array_merge($_GET, array("order"=> "cpf")))?>">CPF</a></th>
                                    <th id="th_nome" data-toggle="tooltip" title="Ordenar"><a href="?<?=http_build_query(array_merge($_GET, array("order" => "nome")))?>">Nome</a></th>
                                    <th id="th_email" data-toggle="tooltip" title="Ordenar"><a href="?<?=http_build_query(array_merge($_GET, array("order" => "email")))?>">E-mail</a></th>
                                    <th id="th_telefone" data-toggle="tooltip" title="Ordenar"><a href="?<?=http_build_query(array_merge($_GET, array("order" => "telefone")))?>">Telefone</a></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                    while($value = pg_fetch_assoc($result)) {
                                ?>
                                <tr>
                                    <td class="expansible center">  <?=ucwords($value['cpf'])?></td>
                                    <td class="expansible">         <?=ucwords($value['nome'])?></td>
                                    <td class="expansible center">  <?=$value['email']?></td>
                                    <td class="expansible">         <?=$value['telefone_1']?><?=($value['telefone_2']!='' ? ', <br />'.$value['telefone_2'] : '')?></td>
                                    <td class="button_column">
                                        <a class="query" href="pacientes.php?cpf=<?=base64url_encode($value['cpf']);?>">Ver Pacientes</a>
                                        <div class="drop_menu">
                                            <button class="drop"><i class="material-icons">expand_more</i></button>
                                            <div class="drop_content">
                                                <a href="clientes_detalhes.php?view=<?=base64url_encode($value['cpf']);?>">Ver Detalhes</a>
                                                <a href="clientes_novo.php?edit=<?=base64url_encode($value['cpf']);?>">Editar</a>
                                                <button onclick="msg_remover('<?php
                                                    $params = array_merge($_GET, array("excluir" => base64url_encode($value['cpf'])));
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
                            <a class="adv_search" href="clientes_busca.php"><i class="material-icons">search</i> Busca avançada</a>
                            <a class="confirm" href="clientes_novo.php"><i class="material-icons">person_add</i> Novo</a>
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
            
                if (cat == "cpf") {
                    sInput.attr("pattern", ".{12}");
                    sInput.attr("type", "text");
                    sInput.mask("000000000-00");
                    sInput.attr("placeholder", "11 dígitos");
                    sInput.attr("autocomplete", "on");
                } else if (cat == "nome") {
                    sInput.removeAttr("pattern");
                    sInput.removeAttr("maxlength");
                    sInput.attr("type", "text");
                    sInput.mask("A", {'translation': {A: {pattern: /[A-Za-z0-9\s]/, recursive: true}}});
                    sInput.attr("placeholder", "Texto");
                    sInput.attr("autocomplete", "on");
                } else if (cat == "email") {
                    sInput.removeAttr("pattern");
                    sInput.removeAttr("maxlength");
                    sInput.unmask();
                    sInput.attr("type", "email");
                    sInput.attr("placeholder", "E-mail");
                    sInput.attr("autocomplete", "on");
                } else if (cat == "telefone") {
                    sInput.attr("pattern", ".{14,15}");
                    sInput.attr("type", "tel");
                    sInput.mask('(00) 00000-0000', telOptions);
                    sInput.attr("placeholder", "Apenas números");
                    sInput.attr("autocomplete", "on");
                }
                
                if (reset) {
                    sInput.val('');
                }
            }
            input_change(false);
            
            var telOptions = {
                onKeyPress: function(val, e, field, options) {
                    var masks = ['(00) 0000-00009', '(00) 00000-0000'];
                    var mask = (val.replace(/\D/g, '').length==11) ? masks[1] : masks[0];
                    $(field).mask(mask, telOptions);
                }
            };
            
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