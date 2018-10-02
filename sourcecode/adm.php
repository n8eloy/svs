<?php
    include('connection.php');
    
    if(!isset($_SESSION['user_type']))
    {
        header("Location: login.php?erro_login=1");
    } elseif($_SESSION['user_type'] != 1) {
        header("Location: home.php");
    }
    
    $excluir;
    
    if(isset($_GET['excluir'])) {
        if($_SESSION['user'] == base64url_decode($_GET['excluir'])) {
            $_SESSION['bd_error'] = 'Não é possível excluir você mesmo';
            // Tirar parâmetro 'excluir'
            $param = $_GET;
            unset($param['excluir']);
            header("Location: ?".http_build_query($param));
        } else {
            $result = pg_query($conn, "DELETE FROM login WHERE usuario='".base64url_decode($_GET['excluir'])."'");

            if($result) {
                // Tirar parâmetro 'excluir'
                $param = $_GET;
                unset($param['excluir']);
                header("Location: ?".http_build_query($param));
            }
        }
    } else  {
        // Ordenação padrão
        $orderMethod = 'usuario';
        if(isset($_GET['order'])) {
            if($_GET['order'] == 'tipo') {
                $orderMethod = 'tipo, usuario';
            }
        }
        
        $string_query="SELECT * FROM login";
        
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

        $result = pg_query($conn, $string_query." ORDER BY ".$orderMethod." LIMIT ".$rowsPerPage." OFFSET ".$offset);
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
                            <h1 class="txt_green col-lg-12"><i class="material-icons">settings</i> Administração</h1>
                        </div>
                        <hr>
                        <div class="table_frame">
                            <table class="table_query">
                                <thead>
                                <tr>
                                    <th id="th_usuario" data-toggle="tooltip" title="Ordenar"><a href="?<?=http_build_query(array_merge($_GET, array("order"=> "usuario")))?>">Usuário</a></th>
                                    <th id="th_tipo" data-toggle="tooltip" title="Ordenar"><a href="?<?=http_build_query(array_merge($_GET, array("order" => "tipo")))?>">Tipo</a></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                    while($value = pg_fetch_assoc($result)) {
                                ?>
                                <tr>
                                    <td class="expansible center">  <?=($value['usuario'])?></td>
                                    <td class="expansible center">                                          
                                    <?php 
                                        if($value['tipo'] == 1) 
                                            echo "Administrador"; 
                                        else
                                            echo "Funcionário";
                                    ?>                                
                                    </td>
                                    <td class="button_column">
                                        <div class="drop_menu">
                                            <button class="drop"><i class="material-icons">expand_more</i></button>
                                            <div class="drop_content">
                                                <a href="adm_novo.php?edit=<?=base64url_encode($value['usuario']);?>">Editar</a>
                                                <button onclick="msg_remover('<?php
                                                    $params = array_merge($_GET, array("excluir" => base64url_encode($value['usuario'])));
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
                            <a class="confirm" href="adm_novo.php"><i class="material-icons">add</i> Novo</a>
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
