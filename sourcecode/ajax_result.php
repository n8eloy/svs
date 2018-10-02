<?php
    include('connection.php');
    
    if(!isset($_SESSION['user_type']))
    {
        header("Location: login.php");
    }
    
    if(isset($_GET['flagBotao']) && $_GET['flagBotao'] == 0) {
        $flagBotao = false;
    } else {
        $flagBotao = true;
    }

    if(isset($_GET['opt'])) {
        if($_GET['opt']=='busca_material' || $_GET['opt']=='adiciona_material') {
            // Ordenação padrão
            $orderMethod = 'codigo';
            if(isset($_GET['order'])) {
                if($_GET['order'] == 'nome') {
                    $orderMethod = 'nome';
                } elseif($_GET['order'] == 'qtd_estoque') {
                    $orderMethod = 'qtd_estoque';
                }
            }
            // Verificar se está sendo realizada busca
            if(isset($_GET['searchterm']) && isset($_GET['searchcategory']) && $_GET['searchterm']!="" && $_GET['searchcategory']!="") {
                $search_column = pg_escape_string(($_GET['searchcategory']));
                $search_term = strtolower(pg_escape_string(($_GET['searchterm'])));

                if($_GET['searchcategory'] == "codigo") {
                    $string_query="SELECT * FROM material WHERE codigo='".$search_term."'";
                } elseif($_GET['searchcategory'] == "nome") {
                    $string_query="SELECT * FROM material WHERE nome LIKE '%".$search_term."%'";
                } else {
                    $string_query="SELECT * FROM material";
                }
            } else {
                $string_query="SELECT * FROM material";
            }

            if($string_query) {
                $result = pg_query($conn, "SELECT COUNT(*) FROM (".$string_query.") AS SUBQ");
                $numRows = pg_fetch_row($result)[0];
                $rowsPerPage = 10;
                $totalPages = ceil($numRows / $rowsPerPage);

                if($_GET['opt'] == 'busca_material') {
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
                } else {
                    $result = pg_query($conn, $string_query." ORDER BY ".$orderMethod);
                }
                
                if($result) {
                    if($_GET['opt']=='busca_material') {
                        if(pg_num_rows($result) > 0) {
                            ?>
                            <table class="table_query">
                            <thead>
                            <tr>
                                <th class="active" id="th_mat_codigo" data-toggle="tooltip" title="Ordenar"><button onclick="order('mat','codigo')">Código</button></th>
                                <th id="th_mat_nome" data-toggle="tooltip" title="Ordenar"><button onclick="order('mat','nome')")>Nome</button></th>
                                <th id="th_mat_qtd_estoque" data-toggle="tooltip" title="Ordenar"><button onclick="order('mat','qtd_estoque')")>Quantidade em Estoque</button></th>
                                <?php if($flagBotao) { ?><th></th><?php } ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            while($value = pg_fetch_assoc($result)) {
                            ?>
                            <tr>
                                <td class="expansible center">  <?=$value['codigo']?></td>
                                <td class="expansible">         <?=ucwords(strtolower($value['nome']))?></td>
                                <td class="expansible center">  <?=$value['qtd_estoque']?></td>
                                <?php if($flagBotao) { ?>
                                <td class="button_column">
                                    <button class="search" onclick="adicionar_material('<?=$value['codigo']?>', '<?=ucwords(strtolower($value['nome']))?>', '<?=$value['qtd_estoque']?>')"><i class="material-icons">add</i></button>
                                </td>
                                <?php } ?>
                            </tr>
                            <?php
                            }
                            ?>
                            </tbody>
                            </table>
                            <input id="mat_total_pages" type="hidden" value="<?=$totalPages?>">
                            <?php
                        } else {
                            ?>
                            <table class="table_query">
                            <thead>
                            <tr>
                                <th class="active" id="th_mat_codigo" data-toggle="tooltip" title="Ordenar"><button onclick="order('mat','codigo')">Código</button></th>
                                <th id="th_mat_nome" data-toggle="tooltip" title="Ordenar"><button onclick="order('mat','nome')")>Nome</button></th>
                                <th id="th_mat_qtd_estoque" data-toggle="tooltip" title="Ordenar"><button onclick="order('mat','qtd_estoque')")>Quantidade em Estoque</button></th>
                                <?php if($flagBotao) { ?><th></th><?php } ?>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td class="expansible"></td>
                                <td class="expansible"></td>
                                <td class="expansible"></td>
                                <?php if($flagBotao) { ?>
                                <td class="button_column">
                                    <button class="search" onclick=""></button>
                                </td>
                                <?php } ?>
                            </tr>
                            </tbody>
                            </table>
                            <input id="mat_total_pages" type="hidden" value="<?=$totalPages?>">
                            <?php
                        }
                    } else {
                        if(pg_num_rows($result) > 0) {
                            $value = pg_fetch_assoc($result);
                            ?>
                            <tr id="mat_<?=$value['codigo']?>">
                                <td class="expansible center">  <?=$value['codigo']?></td>
                                <td class="expansible">         <?=ucwords(strtolower($value['nome']))?></td>
                                <td class="expansible center">  <?=$_GET['additional']?></td>
                                <?php if($flagBotao) { ?>
                                <td class="button_column">
                                    <button class="search" onclick="remover_material('<?=$value['codigo']?>')"><i class="material-icons">remove</i></button>
                                </td>
                                <?php } ?>
                            </tr>
                            <?php
                        } else {
                            ?>
                            <tr>
                                <td class="expansible"></td>
                                <td class="expansible"></td>
                                <td class="expansible"></td>
                                <?php if($flagBotao) { ?>
                                <td class="button_column">
                                    <button class="search" onclick=""></button>
                                </td>
                                <?php } ?>
                            </tr>
                            <?php
                        }
                    }
                } else {
                    die("Erro: ".pg_last_error($conn));
                }
            } else {
                die("Erro: operação inválida");
            }
        } elseif($_GET['opt']=='busca_veterinario' || $_GET['opt']=='adiciona_veterinario') {
            // Ordenação padrão
            $orderMethod = 'crmv';
            if(isset($_GET['order'])) {
                if($_GET['order'] == 'nome') {
                    $orderMethod = 'nome';
                } elseif($_GET['order'] == 'email') {
                    $orderMethod = 'email';
                } elseif($_GET['order'] == 'telefones') {
                    $orderMethod = 'telefone_1,telefone_2';
                }
            }
            // Verificar se está sendo realizada busca
            if(isset($_GET['searchterm']) && isset($_GET['searchcategory']) && $_GET['searchterm']!="" && $_GET['searchcategory']!="") {
                $search_column = pg_escape_string(($_GET['searchcategory']));
                $search_term = strtolower(pg_escape_string(($_GET['searchterm'])));

                if($_GET['searchcategory'] == "crmv") {
                    $string_query="SELECT * FROM veterinario WHERE crmv='".$search_term."'";
                } elseif($_GET['searchcategory'] == "nome") {
                    $string_query="SELECT * FROM veterinario WHERE nome LIKE '%".$search_term."%'";
                } elseif($_GET['searchcategory'] == "email") {
                    $string_query="SELECT * FROM veterinario WHERE email LIKE '%".$search_term."%'";
                } elseif($_GET['searchcategory'] == "telefone") {
                    $string_query="SELECT * FROM veterinario WHERE telefone_1 LIKE '%".$search_term."%' OR telefone_2 LIKE '%".$search_term."%'";
                } else {
                    $string_query="SELECT * FROM veterinario";
                }
            } else {
                $string_query="SELECT * FROM veterinario";
            }

            if($string_query) {
                $result = pg_query($conn, "SELECT COUNT(*) FROM (".$string_query.") AS SUBQ");
                $numRows = pg_fetch_row($result)[0];
                $rowsPerPage = 10;
                $totalPages = ceil($numRows / $rowsPerPage);

                if($_GET['opt'] == 'busca_veterinario') {
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
                } else {
                    $result = pg_query($conn, $string_query." ORDER BY ".$orderMethod);
                }
                if($result) {
                    if($_GET['opt']=='busca_veterinario') {
                        if(pg_num_rows($result) > 0) {
                            ?>                                
                            <table class="table_query">
                            <thead>
                            <tr>
                                <th class="active" id="th_vet_crmv" data-toggle="tooltip" title="Ordenar"><button onclick="order('vet', 'crmv')">CRMV</button></th>
                                <th id="th_vet_nome" data-toggle="tooltip" title="Ordenar"><button onclick="order('vet', 'nome')">Nome</button></th>
                                <th id="th_vet_email" data-toggle="tooltip" title="Ordenar"><button onclick="order('vet', 'email')">E-mail</button></th>
                                <th id="th_vet_telefones" data-toggle="tooltip" title="Ordenar"><button onclick="order('vet', 'telefones')">Telefones</button></th>
                                <?php if($flagBotao) { ?><th></th><?php } ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            while($value = pg_fetch_assoc($result)) {
                            ?>
                            <tr>
                                <td class="expansible center">  <?=$value['crmv']?></td>
                                <td class="expansible">         <?=ucwords(strtolower($value['nome']))?></td>
                                <td class="expansible center">  <?=ucwords(strtolower($value['email']))?></td>
                                <td class="expansible center">  <?=$value['telefone_1']?> <?=($value['telefone_2']!='' ? ','.$value['telefone_2'] : '')?></td>
                                <?php if($flagBotao) { ?>
                                <td class="button_column">
                                    <button class="search" onclick="adicionar_veterinario('<?=$value['crmv']?>')"><i class="material-icons">add</i></button>
                                </td>
                                <?php } ?>
                            </tr>
                            <?php
                            }
                            ?>
                            </tbody>
                            </table>
                            <input id="vet_total_pages" type="hidden" value="<?=$totalPages?>">
                            <?php
                        } else {
                            ?>                                
                            <table class="table_query">
                            <thead>
                            <tr>
                                <th class="active" id="th_vet_crmv" data-toggle="tooltip" title="Ordenar"><button onclick="order('vet', 'crmv')">CRMV</button></th>
                                <th id="th_vet_nome" data-toggle="tooltip" title="Ordenar"><button onclick="order('vet', 'nome')">Nome</button></th>
                                <th id="th_vet_email" data-toggle="tooltip" title="Ordenar"><button onclick="order('vet', 'email')">E-mail</button></th>
                                <th id="th_vet_telefones" data-toggle="tooltip" title="Ordenar"><button onclick="order('vet', 'telefones')">Telefones</button></th>
                                <?php if($flagBotao) { ?><th></th><?php } ?>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td class="expansible center"></td>
                                <td class="expansible"></td>
                                <td class="expansible center"></td>
                                <td class="expansible center"></td>
                                <?php if($flagBotao) { ?>
                                <td class="button_column">
                                    <button class="search" onclick=""></button>
                                </td>
                                <?php } ?>
                            </tr>
                            </tbody>
                            </table>
                            <input id="vet_total_pages" type="hidden" value="<?=$totalPages?>">
                            <?php
                        }
                    } else {
                        if(pg_num_rows($result) > 0) {
                            $value = pg_fetch_assoc($result);
                            ?>
                            <tr id="vet_<?=$value['crmv']?>">
                                <td class="expansible center">  <?=$value['crmv']?></td>
                                <td class="expansible">         <?=ucwords(strtolower($value['nome']))?></td>
                                <td class="expansible center">  <?=ucwords(strtolower($value['email']))?></td>
                                <td class="expansible center">  <?=$value['telefone_1']?> <?=($value['telefone_2']!='' ? ','.$value['telefone_2'] : '')?></td>
                                <?php if($flagBotao) { ?>
                                <td class="button_column">
                                    <button class="search" onclick="remover_veterinario('<?=$value['crmv']?>')"><i class="material-icons">remove</i></button>
                                </td>
                                <?php } ?>
                            </tr>
                            <?php
                        } else {
                            ?>
                            <tr>
                                <td class="expansible center"></td>
                                <td class="expansible"></td>
                                <td class="expansible center"></td>
                                <td class="expansible center"></td>
                                <?php if($flagBotao) { ?>
                                <td class="button_column">
                                    <button class="search" onclick=""></button>
                                </td>
                                <?php } ?>
                            </tr>
                            <?php
                        }
                    }
                } else {
                    die("Erro: ".pg_last_error($conn));
                }
            } else {
                die ("Erro: operação inválida");
            }
        }
    }
?>
