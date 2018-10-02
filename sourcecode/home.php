<?php
    session_start();
    if(!isset($_SESSION['user_type']))
    {
        header("Location: login.php");
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>SVS - Página Inicial</title>
        <?php include 'common_meta.php'; ?>
        <link rel="stylesheet" href="../stylesheet/home.css">
    </head>
    <!-- Fonte declarada inline para compatibilidade com Chrome -->
    <body class="container-fluid" style="font-family: 'Lato', sans-serif" >
        <div class="row">
            <div class="col-lg-3 grid_container">
                <?php include 'menu.php'; ?>
                <script> document.getElementById("menu_home").className += " active"; </script>
            </div>
            <div class="col-lg-9 grid_container">
                <div class="frame">
                    <div class="content">
                        <div class="content_header">
                            <h1 class="txt_green">Bem-vindo <?=ucwords(str_replace('_',' ',$_SESSION['user']))?>!</h1>
                            <h1 class="txt_green">SVS - Clínica Veterinária <i>Sahudy & Saúde</i></h1>
                        </div>
                        <hr>
                        
                        <h1 class="txt_green">Atalhos</h1>
                        <a class="shortcut" href="clientes_novo.php">Cadastrar novo cliente</a><br>
                        <a class="shortcut" href="veterinarios_novo.php">Cadastrar novo veterinário</a><br>
                        <a class="shortcut" href="procedimentos_novo.php">Cadastrar novo procedimento</a><br>
                        <a class="shortcut" href="clientes_busca.php">Buscar dados de procedimentos realizados para um cliente</a><br>
                        <a class="shortcut" href="veterinarios_busca.php">Buscar veterinários por faixa salarial e especialidade</a><br>
                        
                        <!--UTILIZADO PARA TESTES-->
                        <!--<p> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut at varius quam. Vivamus commodo, lectus id iaculis ultrices, sapien felis hendrerit augue, eget laoreet dui lacus vitae turpis. Maecenas feugiat, dui vel ultricies tincidunt, turpis enim cursus ex, in blandit leo eros in metus. Morbi luctus nisi eget nibh malesuada, non interdum erat placerat. Mauris vitae venenatis ante. Nulla lacus ipsum, porttitor ac augue vitae, luctus maximus nisi. Suspendisse dolor diam, dignissim nec mi id, luctus elementum felis. Nam sed dolor sed mauris tincidunt gravida. Quisque sed finibus lacus, tincidunt malesuada odio. Nullam mi nulla, semper ac faucibus non, placerat vehicula sapien. Aenean a nunc enim. Pellentesque sed tempor magna, ac vulputate velit. Vivamus facilisis vel tellus sit amet tincidunt.</p>
                        <p> In hac habitasse platea dictumst. Donec fermentum, sem a eleifend euismod, lorem nibh ultricies tortor, eget fermentum tortor risus ac ex. Vestibulum posuere tincidunt ultrices. Ut faucibus dapibus lacus, id euismod ex facilisis at. Vestibulum rhoncus felis eu vehicula vehicula. Donec malesuada justo vitae vehicula commodo. Sed sem mi, maximus eget mattis a, molestie a ligula. Ut a turpis aliquet, pellentesque ex eget, pellentesque lorem. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae;</p>
                        <p>Pellentesque gravida urna sed dui placerat sagittis. Maecenas varius ex quis lacus condimentum elementum. Donec dignissim quam ut magna tristique, nec vestibulum mi tincidunt. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer turpis lacus, pretium eu neque a, fringilla efficitur turpis. Maecenas eget diam suscipit, tempor sapien in, posuere nisi. Vestibulum imperdiet ut orci eget finibus. Mauris iaculis, leo eu tempor rutrum, tellus ex blandit dolor, ut interdum lorem enim quis purus. Vivamus facilisis maximus pellentesque. Vestibulum commodo magna magna, at varius diam dictum ac. Nullam aliquet condimentum mi, quis elementum justo porttitor a. Nulla facilisi. Integer non ligula sit amet turpis posuere tempor dapibus eget enim.</p>
                        <p>Suspendisse volutpat tellus nibh, vitae venenatis augue vehicula at. Fusce euismod tempor libero, eu placerat nisl pulvinar commodo. Ut pellentesque hendrerit neque, ac tempus turpis porta et. Nulla sed interdum nibh. Nullam aliquet libero non molestie tincidunt. Nam in finibus quam, ac consequat dui. Vestibulum vel turpis in lorem eleifend lobortis eget quis libero. Sed sit amet eros malesuada, facilisis sapien suscipit, condimentum lorem. Vivamus ac ultricies nibh, vel vehicula sapien. Vivamus vitae dignissim neque. Proin molestie ornare sapien ac maximus. Nunc in felis tempus, iaculis lorem sit amet, suscipit ante.</p>
                        <p>Etiam egestas dapibus elit, ut accumsan libero gravida laoreet. Donec dui massa, dictum vitae odio placerat, elementum pretium lectus. Nulla faucibus quis mauris non elementum. Nam sapien neque, pretium ac ligula quis, varius vestibulum elit. Nunc non mi quis nisi gravida gravida. Suspendisse vehicula velit sed sem sollicitudin congue. Curabitur quis nunc orci. Proin bibendum lorem id nulla rhoncus convallis ut a tellus. Integer suscipit erat ut leo faucibus, quis lacinia lorem pharetra. Donec nisl magna, malesuada et pretium eget, semper id leo. Duis cursus efficitur posuere. Vivamus non enim magna.</p>
                        <p>Nam tincidunt, nisl eu scelerisque interdum, lacus ante rhoncus mauris, vitae feugiat urna elit vitae ante. In scelerisque, libero sit amet rutrum luctus, sem metus commodo leo, vitae rutrum metus nulla et magna. Praesent consequat congue lectus, eu molestie purus elementum et. In condimentum commodo placerat. Donec lacinia mauris vel tempor malesuada. Aliquam tincidunt viverra nisl, eget fringilla nisi dictum at. Donec sit amet suscipit ex. Nulla at finibus odio, a tincidunt sem.</p>-->
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>