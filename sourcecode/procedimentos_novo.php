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

    if(isset($_GET['edit'])) {
        $edit = base64url_decode($_GET['edit']);
        $edit_code = explode('&', $edit);
        
        $string_query="SELECT * FROM procedimento WHERE data='".$edit_code[0]."' AND sala='$edit_code[1]';";
        $result = pg_query($conn, $string_query);
        $obj = pg_fetch_object($result);
        $data = $obj->data;
        $sala = $obj->sala;
        $tipo = $obj->tipo;
        $custo = number_format($obj->custo, 2, ',', '.');
        $cpf = $obj->cpf_pac;
        $nome = $obj->nome_pac;
        $descricao = utf8_decode($obj->descricao);
        $flagEdit=1;
        
        $mat_query="SELECT codigo_mat, quantidade FROM utiliza WHERE data_proc='".$edit_code[0]."' AND sala_proc='$edit_code[1]' ORDER BY codigo_mat;";
        $mat_result = pg_query($conn, $mat_query);
        
        $vet_query="SELECT crmv_vet FROM realiza WHERE data_proc='".$edit_code[0]."' AND sala_proc='$edit_code[1]' ORDER BY crmv_vet;";
        $vet_result = pg_query($conn, $vet_query);
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
                        <input id="add_vet_id" type=hidden val=''>
                        <div id="add_material" class="warning">
                            <input id="add_mat_id" type=hidden val=''>
                            <input id="add_mat_qtd_estoque" type=hidden val=''>
                            <hr>
                            <h1 class="txt_green">Adicionar Material</h1>
                            <hr>
                            <div class="warning_content">
                                <div id="add_mat_name"></div>
                                <label for="add_mat_qtd">Quantidade Utilizada</label><br>
                                <input class="flex" type="text" name="add_mat_qtd" id="add_mat_qtd" placeholder="Quantidade" value="1" required>
                            </div>
                            <div class="warning_buttons">
                                <button class="cancel" onclick="warning_close()" type="reset"><i class="material-icons">cancel</i> Cancelar</button>
                                <button class="confirm" onclick="confirmar_material($('#add_mat_id').val(), $('#add_mat_qtd_estoque').val(), $('#add_mat_qtd').val())" type="submit"><i class="material-icons">check</i> Confirmar</button>
                            </div>
                        </div>
                        <div class="row">
                            <h1 class="txt_green"><i class="material-icons">healing</i> Procedimentos Realizados — Novo</h1>
                        </div>
                        <hr>
                        <form id="data_form" action="data_store.php?cadastro=<?php echo base64url_encode("procedimento");?>" method="post">
                            <input type="hidden" name="flagEdit" value="<?php echo $flagEdit; ?>">
                            <input type="hidden" name="codigo" value="<?php echo $edit; ?>">
                            <input type="hidden" name="material" id="input_material" val="">
                            <input type="hidden" name="veterinario" id="input_veterinario" val="">
                            
                            <div class="row">
                                <div class="col-lg-6">  
                                    <label for="date">Data</label><br>
                                    <input class="flex" type="date" name="date" id="date" required 
                                           <?=(isset($_GET['edit']) ? 'value="'.date('Y-m-d', strtotime($data)).'"' : '')?>><br>
                                    
                                    <label for="time">Hora</label><br>
                                    <p class="description">*Formato de 24 horas</p>
                                    <input class="flex" type="time" name="time" id="time" required 
                                           <?=(isset($_GET['edit']) ? 'value="'.date('H:i', strtotime($data)).'"' : '')?>><br>
                                    
                                    <label for="room">Sala</label><br>
                                    <input class="flex" type="text" name="room" id="room" placeholder="Apenas números" value="<?=$sala?>" required autofocus><br>

                                    <label for="cost">Custo</label><br>
                                    <input class="flex" type="text" name="cost" id="cost" placeholder="Apenas números" value="<?=$custo?>" required><br>
                                </div>
                                <div class="col-lg-6">
                                    <label for="type">Tipo</label><br>
                                    <select class="type_select" id="type" name="type" required>
                                        <option value="Cirurgia">Cirurgia</option>
                                        <option value="Consulta">Consulta</option>
                                        <option value="Internacao">Internação</option>
                                        <option value="Vacinacao">Vacinação</option>
                                        <option selected disabled hidden>Selecionar Tipo</option>
                                    </select><br>
                                    
                                    <script>
                                        var sel = "<?=$tipo?>";
                                        
                                        if(sel != '') {
                                            $('#type').val(sel);
                                        }
                                    </script>
                                    
                                    <label for="cpf">CPF Dono</label><br>
                                    <p class="description">*Consultar tabela de clientes</p>
                                    <input pattern=".{12}" class="flex" type="text" name="cpf" id="cpf" placeholder="Apenas números" value='<?=$cpf?>' required onkeyup="carregar_pacientes(false)"><br>
                                    
                                    <label for="name">Paciente</label><br>
                                    <div id="name_sel">
                                        <select class="type_select" name="name" id="name" required disabled>
                                            <option selected disabled hidden>Insira um CPF existente</option>
                                        </select><br>
                                    </div>
                                </div>
                            </div>
                            <label for="description">Descrição</label><br>
                            <p class="description">*Opcional</p>
                            <textarea rows=4 class="flex" name="description" id="description" placeholder="Texto"><?=$descricao?></textarea>
                        </form>
                            
                        <br><hr><br>

                        <div class="row">
                            <h1 class="txt_green col-lg-6"><i class="material-icons">reorder</i> Materiais Utilizados</h1>
                            <div class="search_frame col-lg-6">
                                <select id="mat_sel" class="search_select" name="searchcategory" onchange="input_change('material')">
                                    <option value="codigo">Código</option>
                                    <option value="nome">Nome</option>
                                    <option selected disabled hidden>Selecionar Categoria</option>
                                </select>
                                <input id="mat_input" class="search_input" type="text" name="searchterm" placeholder="Termo para busca">
                                <button type="submit" class="search" onclick="ajax_call('busca_material')"><i class="material-icons">location_searching</i></button>
                            </div>
                        </div>
                        <div class="table_frame add">
                            <div id="mat_result">
                                <table class="table_query">
                                    <thead>
                                        <tr>
                                            <th class="active" id="th_mat_codigo" data-toggle="tooltip" title="Ordenar"><button onclick="order('mat','codigo')">Código</button></th>
                                            <th id="th_mat_nome" data-toggle="tooltip" title="Ordenar"><button onclick="order('mat','nome')")>Nome</button></th>
                                            <th id="th_mat_qtd_estoque" data-toggle="tooltip" title="Ordenar"><button onclick="order('mat','qtd_estoque')")>Quantidade em Estoque</button></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="expansible"></td>
                                            <td class="expansible"></td>
                                            <td class="expansible"></td>
                                            <td class="button_column">
                                                <button class="search" onclick=""></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div id="mat_pagination" class="pagination_footer"></div>
                            <br>
                            <table id="mat_add" class="table_query">
                                <thead>
                                    <tr>
                                        <th class="active no_anchor">Código</th>
                                        <th class="no_anchor">Nome</th>
                                        <th class="no_anchor">Quantidade Utilizada</th>
                                        <th class="no_anchor"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="expansible"></td>
                                        <td class="expansible"></td>
                                        <td class="expansible"></td>
                                        <td class="button_column">
                                            <button class="search" onclick=""></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <br><hr><br>

                        <div class="row">
                            <h1 class="txt_green col-lg-6"><i class="material-icons">person</i> Veterinários Envolvidos</h1>
                            <div class="search_frame col-lg-6">
                                <select id="vet_sel" class="search_select" name="searchcategory" onchange="input_change('veterinario')">
                                    <option value="crmv">CRMV</option>
                                    <option value="nome">Nome</option>
                                    <option value="email">E-mail</option>
                                    <option value="telefone">Telefone</option>
                                    <option selected disabled hidden>Selecionar Categoria</option>
                                </select>
                                <input id="vet_input" class="search_input" type="text" name="searchterm" placeholder="Termo para busca">
                                <button type="submit" class="search" onclick="ajax_call('busca_veterinario')"><i class="material-icons">location_searching</i></button>
                            </div>
                        </div>
                        <div class="table_frame add">
                            <div id="vet_result">
                                <table class="table_query">
                                    <thead>
                                        <tr>
                                            <th class="active" id="th_vet_crmv" data-toggle="tooltip" title="Ordenar"><button onclick="order('vet', 'crmv')">CRMV</button></th>
                                            <th id="th_vet_nome" data-toggle="tooltip" title="Ordenar"><button onclick="order('vet', 'nome')">Nome</button></th>
                                            <th id="th_vet_email" data-toggle="tooltip" title="Ordenar"><button onclick="order('vet', 'email')">E-mail</button></th>
                                            <th id="th_vet_telefones" data-toggle="tooltip" title="Ordenar"><button onclick="order('vet', 'telefones')">Telefones</button></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="expansible"></td>
                                            <td class="expansible"></td>
                                            <td class="expansible"></td>
                                            <td class="expansible"> </td>
                                            <td class="button_column">
                                                <button class="search" onclick=""></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div id="vet_pagination" class="pagination_footer"></div>
                            <br>
                            <table id="vet_add" class="table_query">
                                <thead>
                                    <tr>
                                        <th class="no_anchor active">CRMV</th>
                                        <th class="no_anchor">Nome</th>
                                        <th class="no_anchor">E-mail</th>
                                        <th class="no_anchor">Telefones</th>
                                        <th class="no_anchor"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="expansible"></td>
                                        <td class="expansible"></td>
                                        <td class="expansible"></td>
                                        <td class="expansible"> </td>
                                        <td class="button_column">
                                            <button class="search" onclick=""></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="button_footer">
                            <button type="button" class="cancel" onclick="history.back()"><i class="material-icons">arrow_back_ios</i>Retornar</button>
                            <button form="data_form" type="submit" class="confirm"><i class="material-icons">check</i>Confirmar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>            
            var order_mat = 'codigo';  
            var order_vet = 'crmv';
            var mat_add_qtd = 0;
            var vet_add_qtd = 0;
            var mat_page = 1;
            var vet_page = 1;
                        
            function show_error() {
                $("#bd_msg").show();
            }
            
            function warning_close() {
                $(".warning").hide();
            }
            
            function adicionar_material(mat_cod, mat_nome, mat_qtd_estoque) {
                // Procura se o material já foi adicionado
                if($("#input_material").val().search((mat_cod + ":")) != -1) {
                    alert("Erro: Material já selecionado");
                } else {
                    $("#add_mat_name").text(mat_nome);
                    $("#add_mat_id").val(mat_cod);
                    $("#add_mat_qtd_estoque").val(mat_qtd_estoque);
                    $("#add_material").show();
                }
            }
                        
            function confirmar_material(mat_cod, mat_qtd_estoque, mat_qtd_utilizada) {
                if(Number(mat_qtd_utilizada) <= 0) {
                    alert("Erro: Impossível utilizar menos que um");
                } else if(Number(mat_qtd_utilizada) > Number(mat_qtd_estoque)) {
                    alert("Erro: Quantidade inserida maior do que a presente em estoque");
                } else {
                    warning_close();
                    $("#input_material").val($("#input_material").val() + mat_cod + ':' + mat_qtd_utilizada + ';');
                    ajax_call('adiciona_material');
                }
            }
                                    
            function adicionar_veterinario(crmv) {
                // Procura se o veterinário já foi adicionado
                if($("#input_veterinario").val().search((crmv + ";")) != -1) {
                    alert("Erro: Veterinário já selecionado");
                } else {
                    $("#add_vet_id").val(crmv);
                    $("#input_veterinario").val($("#input_veterinario").val() + crmv + ';');
                    ajax_call('adiciona_veterinario');
                }
            }
            
            function carregar_material(mat_cod, mat_qtd) {
                $("#add_mat_id").val(mat_cod);
                $("#add_mat_qtd").val(mat_qtd);
                
                $("#input_material").val($("#input_material").val() + mat_cod + ':' + mat_qtd + ';');
                ajax_call('adiciona_material');
            }
            
            function carregar_veterinario(crmv) {
                $("#add_vet_id").val(crmv);
                
                $("#input_veterinario").val($("#input_veterinario").val() + crmv + ';');
                ajax_call('adiciona_veterinario');
            }
            
            function carregar_pacientes(edit_load) {
                var cpf = $("#cpf").val();
                var result = $("#name_sel");
                                
                if(cpf.length >= 12) {
                    $.get("ajax_pac.php", {
                        cpf: cpf
                    }, function(data) {
                        result.html(data);
                        
                        // Altera caso esteja carregando a página para edição
                        if(edit_load) {
                            var sel = "<?=$nome?>";

                            if(sel != '') {
                                $('#name').val(sel);
                            }
                        }
                    });
                } else {
                    $("#name").prop('disabled', true);
                }
            }
            
            <?php
            // Carrega os valores no caso de edição
            if(isset($_GET['edit'])) {
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
                    $(carregar_pacientes(true));
                <?php
            }
            ?>
            
            function remover_material(mat_cod) {
                // Procura se o material já foi adicionado
                if($("#input_material").val().search((mat_cod + ":")) != -1) {
                    $("#mat_" + mat_cod).remove();
                    mat_add_qtd = mat_add_qtd - 1;
                    
                    $("#input_material").val($("#input_material").val().replace(new RegExp(mat_cod + ":[^;]+;", "g"), ''))
                }
                
                // Caso não existam mais materiais
                if(mat_add_qtd <= 0) {
                    mat_add_qtd = 0;
                    
                    // Safe-switch para resetar o valor
                    $("#input_material").val('');
                    
                    $("#mat_add").append('<tr><td class="expansible"></td><td class="expansible"></td><td class="expansible"></td><td class="button_column"><button class="search" onclick=""></button></td></tr>');
                }
            }
            
            function remover_veterinario(crmv) {
                // Procura se o veterinário já foi adicionado
                if($("#input_veterinario").val().search((crmv + ";")) != -1) {
                    $("#vet_" + crmv).remove();
                    vet_add_qtd = vet_add_qtd - 1;
                    
                    $("#input_veterinario").val($("#input_veterinario").val().replace(new RegExp(crmv + ";", "g"), ''))
                }
                
                // Caso não existam mais veterinários
                if(vet_add_qtd <= 0) {
                    vet_add_qtd = 0;
                    
                    // Safe-switch para resetar o valor
                    $("#input_veterinario").val('');
                    
                    $("#vet_add").append('<tr><td class="expansible"></td><td class="expansible"></td><td class="expansible"></td><td class="expansible"></td><td class="button_column"><button class="search" onclick=""></button></td></tr>');
                }
            }         
            
            function ajax_call(opt) {   
                var search_cat = '';
                var search_term = '';
                var order_cat = '';
                var addit_info = '';
                var result = null;
                var pagination_result = null;
                
                var page = 0;
                var total_pages = 0;
                
                if(opt == 'busca_material') {
                    search_cat = $('#mat_sel').val();
                    search_term = $('#mat_input').val();
                    order_cat = order_mat;
                    addit_info = '';
                    result = $('#mat_result');
                    pagination_result = $('#mat_pagination');
                    page = mat_page;
                } else if(opt == 'adiciona_material') {
                    search_cat = 'codigo';
                    search_term = $('#add_mat_id').val();
                    order_cat = 'codigo';
                    addit_info = $('#add_mat_qtd').val();
                    result = $('#mat_add tbody');
                    page = 1;
                } else if(opt == 'busca_veterinario') {
                    search_cat = $('#vet_sel').val();
                    search_term = $('#vet_input').val();
                    order_cat = order_vet;
                    addit_info = '';
                    result = $('#vet_result');
                    pagination_result = $('#vet_pagination');
                    page = vet_page;
                } else if(opt == 'adiciona_veterinario') {
                    search_cat = 'crmv';
                    search_term = $('#add_vet_id').val();
                    order_cat = 'crmv';
                    addit_info = '';
                    result = $('#vet_add tbody');
                    page = 1;
                }
                
                // jQuery AJAX
                $.get("ajax_result.php", {
                    opt: opt,
                    searchcategory: search_cat,
                    searchterm: search_term,
                    order: order_cat,
                    additional: addit_info,
                    page: page
                }, function(data) {
                    if(opt == 'busca_material' || opt == 'busca_veterinario') {
                        result.html(data);
                        
                        if(opt == 'busca_material') {
                            total_pages = $('#mat_total_pages').val();
                            $("*[id^='th_mat']").removeClass('active');
                            $("#th_mat_" + order_cat).addClass('active');
                        } else if(opt == 'busca_veterinario') {
                            total_pages = $('#vet_total_pages').val();
                            $("*[id^='th_vet']").removeClass('active');
                            $("#th_vet_" + order_cat).addClass('active'); 
                        }
                        // paginationação
                        $.get("ajax_pagination.php", {
                            opt: opt,
                            totalPages: total_pages,
                            page: page
                        }, function(pagination_data) {
                            pagination_result.html(pagination_data);
                        });
                    } else if(opt == 'adiciona_material') {
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
            
            // Verifica submissão do form em caso de erro
            $('#data_form').submit(function(event) {
                // Verifica quantidade mínima de veterinários e materiais
                if(vet_add_qtd < 1 || mat_add_qtd < 1) {
                    alert('Selecione ao menos um material e um veterinário!');
                    event.preventDefault();
                } else {
                    $('#data_form').submit();
                }
            })
               
            function page_change(opt, page) {
                if(opt == 'busca_material') {
                    mat_page = Number(page);
                } else if (opt == 'busca_veterinario') {
                    vet_page = Number(page);
                }
                ajax_call(opt);
            }
            
            function order(opt, ord) {
                if(opt == 'mat') {
                    order_mat = ord;
                    ajax_call('busca_material');                    
                } else if(opt == 'vet') {
                    order_vet = ord;
                    ajax_call('busca_veterinario');
                }
            }
            
            $("#room").mask("0#");
            
            $("#cost").mask("#0.00", {reverse: true});
            
            $("#name").mask("A", {'translation': {
                A: {pattern: /[A-Za-z0-9\s]/, recursive: true},
            }});
            
            $("#cpf").mask("000000000-00");
            
            function input_change(opt){
                if(opt == 'material') {
                    var cat = $("#mat_sel").val();
                    var sInput = $("#mat_input");

                    if (cat == "codigo") {
                        sInput.removeAttr("pattern");
                        sInput.mask("0#");
                        sInput.attr("placeholder", "Apenas números");
                    } else if (cat == "nome") {
                        sInput.removeAttr("pattern");
                        sInput.mask("A", {'translation': {A: {pattern: /[A-Za-z0-9\s]/, recursive: true}}});
                        sInput.attr("placeholder", "Texto");
                    }

                    sInput.val('');
                } else if(opt == 'veterinario') {
                    var cat = $("#vet_sel").val();
                    var sInput = $("#vet_input");
                    
                    if (cat == "crmv") {
                        sInput.attr("pattern", ".{6}");
                        sInput.attr("type", "text");
                        sInput.mask("000000");
                        sInput.attr("placeholder", "6 dígitos");
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
                    
                    sInput.val('');
                }
            }
            
            var telOptions = {
                onKeyPress: function(val, e, field, options) {
                    var masks = ['(00) 0000-00009', '(00) 00000-0000'];
                    var mask = (val.replace(/\D/g, '').length==11) ? masks[1] : masks[0];
                    $(field).mask(mask, telOptions);
                }
            };
        </script>
        <?php
            if((isset($_GET['edit']) && !$result) || isset($_SESSION['bd_error'])) {
                echo "<script> show_error(); </script>";
                unset($_SESSION['bd_error']);
            }
        ?>
    </body>
</html>