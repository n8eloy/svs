<?php
    include('connection.php');
    
    if(!isset($_SESSION['user_type']))
    {
        header("Location: login.php");
    }

    $flagEdit=0;
    $edit = '';
    $data = '';
    $sala = '';
    $tipo = '';
    $custo = '';
    $cpf = '';
    $nome = '';
    $descricao = '';

    if(isset($_GET['view']) && $_GET['view']!='') {
        $edit = base64url_decode($_GET['view']);
        $edit_code = explode('&', $edit);
        
        $string_query="SELECT * FROM procedimento WHERE data='".$edit_code[0]."' AND sala='$edit_code[1]';";
        $result = pg_query($conn, $string_query);
        $obj = pg_fetch_object($result);
        $data = $obj->data;
        $sala = $obj->sala;
        $tipo = $obj->tipo;
        $custo = 'R$ '.number_format($obj->custo, 2, ',', '.');
        $cpf = $obj->cpf_pac;
        $nome = $obj->nome_pac;
        $descricao = utf8_decode($obj->descricao);
        $flagEdit=1;
        
        $mat_query="SELECT codigo_mat, quantidade FROM utiliza WHERE data_proc='".$edit_code[0]."' AND sala_proc='$edit_code[1]' ORDER BY codigo_mat;";
        $mat_result = pg_query($conn, $mat_query);
        
        $vet_query="SELECT crmv_vet FROM realiza WHERE data_proc='".$edit_code[0]."' AND sala_proc='$edit_code[1]' ORDER BY crmv_vet;";
        $vet_result = pg_query($conn, $vet_query);
    } else {
        header("Location: procedimentos.php");
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
                                <button class="cancel" onclick="warning_close()">Voltar</button>
                            </div>
                        </div>
                        <div class="row">
                            <h1 class="txt_green"><i class="material-icons">healing</i> Procedimentos Realizados — Detalhes</h1>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-lg-6">  
                                <label for="date">Data</label><br>
                                <?=date('Y-m-d', strtotime($data))?><br>

                                <label for="time">Hora</label><br>
                                <?=date('H:i', strtotime($data))?><br>

                                <label for="room">Sala</label><br>
                                <?=$sala?><br>

                                <label for="cost">Custo</label><br>
                                <?=$custo?><br>
                            </div>
                            <div class="col-lg-6">
                                <label for="type">Tipo</label><br>
                                <?=$tipo?><br>

                                <label for="cpf">CPF Dono</label><br>
                                <?=$cpf?><br>

                                <label for="name">Paciente</label><br>
                                <?=ucwords(strtolower($nome))?><br>
                            </div>
                        </div>
                        <label for="description">Descrição</label><br>
                        <?=$descricao?>
                            
                        <br><hr><br>

                        <div class="row">
                            <h1 class="txt_green col-lg-6"><i class="material-icons">reorder</i> Materiais Utilizados</h1>
                        </div>
                        <div class="table_frame add">
                            <table id="mat_add" class="table_query">
                                <thead>
                                    <tr>
                                        <th class="active no_anchor">Código</th>
                                        <th class="no_anchor">Nome</th>
                                        <th class="no_anchor">Quantidade Utilizada</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="expansible"></td>
                                        <td class="expansible"></td>
                                        <td class="expansible"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <br><hr><br>

                        <div class="row">
                            <h1 class="txt_green col-lg-6"><i class="material-icons">person</i> Veterinários Envolvidos</h1>
                        </div>
                        <div class="table_frame add">
                            <table id="vet_add" class="table_query">
                                <thead>
                                    <tr>
                                        <th class="no_anchor active">CRMV</th>
                                        <th class="no_anchor">Nome</th>
                                        <th class="no_anchor">E-mail</th>
                                        <th class="no_anchor">Telefones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="expansible"></td>
                                        <td class="expansible"></td>
                                        <td class="expansible"></td>
                                        <td class="expansible"> </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="button_footer">
                            <button type="button" class="cancel" onclick="history.back()"><i class="material-icons">arrow_back_ios</i>Retornar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>            
            var mat_add_qtd = 0;
            var vet_add_qtd = 0;
            
            var add_mat_id = 0;
            var add_mat_qtd = 0;
            var add_vet_id = 0;
                        
            function show_error() {
                $("#bd_msg").show();
            }
            
            function warning_close() {
                $(".warning").hide();
            }
                        
            function carregar_material(mat_cod, mat_qtd) {
                add_mat_id = mat_cod;
                add_mat_qtd = mat_qtd;
                
                ajax_call('adiciona_material');
            }
            
            function carregar_veterinario(crmv) {
                add_vet_id = crmv;
                
                ajax_call('adiciona_veterinario');
            }
                        
            <?php
                // Carrega os valores
                while($value = pg_fetch_assoc($mat_result)) {
            ?>
                // Chama a função quando o DOM estiver pronto
                $(carregar_material(<?=$value['codigo_mat']?>,<?=$value['quantidade']?>));
            <?php
                }
                while($value = pg_fetch_assoc($vet_result)) {
            ?>
                // Chama a função quando o DOM estiver pronto
                $(carregar_veterinario(<?='"'.rtrim($value['crmv_vet']).'"'?>));
            <?php
                }
            ?>
            
            function ajax_call(opt) {   
                var search_cat = '';
                var search_term = '';
                var order_cat = '';
                var addit_info = '';
                var result = null;
                var pagination_result = null;
                
                var page = 0;
                var total_pages = 0;
                
                if(opt == 'adiciona_material') {
                    search_cat = 'codigo';
                    search_term = add_mat_id;
                    order_cat = 'codigo';
                    addit_info = add_mat_qtd;
                    result = $('#mat_add tbody');
                    page = 1;
                } else if(opt == 'adiciona_veterinario') {
                    search_cat = 'crmv';
                    search_term = add_vet_id;
                    order_cat = 'crmv';
                    addit_info = '';
                    result = $('#vet_add tbody');
                    page = 1;
                }
                
                // jQuery AJAX
                $.get("ajax_result.php", {
                    flagBotao: 0,
                    opt: opt,
                    searchcategory: search_cat,
                    searchterm: search_term,
                    order: order_cat,
                    additional: addit_info,
                    page: page
                }, function(data) {
                    if(opt == 'adiciona_material') {
                        if(mat_add_qtd == 0) {
                            result.html(data);
                        } else {
                            result.append(data);
                        }
                        mat_add_qtd = mat_add_qtd + 1;
                    } else if(opt == 'adiciona_veterinario') {
                        if(vet_add_qtd == 0) {
                            result.html(data);
                        } else {
                            result.append(data);
                        }
                        vet_add_qtd = vet_add_qtd + 1;
                    }
                });
            }
            
        </script>
        <?php
            if((isset($_GET['view']) && !$result) || isset($_SESSION['bd_error'])) {
                echo "<script> show_error(); </script>";
                unset($_SESSION['bd_error']);
            }
        ?>
    </body>
</html>