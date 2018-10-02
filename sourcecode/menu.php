<div class="frame" style="height: 100%">
    <div class="menu_frame">
        <div class="menu_header">
            <table>
                <tr>
                    <td rowspan="3" style="width: 25%"><img style="width: 90%" src="../image/logo.png" alt="Logo"></td>
                    <td class="txt_green">Sistema Veterinário de Saúde</td>
                </tr>
                <tr>
                    <td class="txt_gray"><?=ucwords(str_replace('_',' ',$_SESSION['user'])) ?></td>
                </tr>
                <tr>
                    <td align="right"><a class="link" href="logout.php" style="font-size: small">Sair</a> </td>
                </tr>
            </table>
        </div>
        <nav id="nav_menu">
            <ul class="side_nav">
                <li class="bar" id="menu_home">
                    <a href="home.php">
                        <i class="material-icons">home</i>Página Inicial
                        <div class="active_block"></div>
                    </a>
                </li>
                <li class="bar" id="menu_proc">
                    <a href="procedimentos.php">
                        <i class="material-icons">healing</i>Procedimentos Realizados
                        <div class="active_block"></div>
                    </a>
                </li>
                <li class="bar" id="menu_vet">
                    <a href="veterinarios.php">
                        <i class="material-icons">person</i>Lista de Veterinários
                        <div class="active_block"></div>
                    </a>
                </li>
                <li class="bar" id="menu_cli">
                    <a href="clientes.php">
                        <i class="material-icons">group</i>Lista de Clientes
                        <div class="active_block"></div>
                    </a>
                </li>
                <li class="bar" id="menu_mat">
                    <a href="materiais.php">
                        <i class="material-icons">reorder</i>Estoque de Materiais
                        <div class="active_block"></div>
                    </a>
                </li>
                <?php 
                    if ($_SESSION['user_type'] == 1) {
                ?>
                <li class="bar" id="menu_adm">
                    <a href="adm.php">
                        <i class="material-icons">settings</i>Administração
                        <div class="active_block"></div>
                    </a>
                </li>
                <?php
                    }
                ?>
            </ul>
        </nav>
        <div class="footer">
            <p><?php echo $_SESSION['user'] ?></p>
            <p>Tipo: <?php 
                if ($_SESSION['user_type'] == 0)
                    echo "Funcionário";
                elseif ($_SESSION['user_type'] == 1)
                    echo "Administrador";
                ?>
            </p>
            
            <p>Acesso: <?php echo(date('d/m/Y, H:i:s', $_SESSION['date'])) ?></p>
        </div>
    </div>
</div>

<button class="top" id="top_button" onclick="scroll_top()" style="font-size: small">
    <div class="top_i"><i class="material-icons">keyboard_arrow_up</i></div>
    <div> Topo</div>
</button>

<script>
    $("#top_button").hide();
    
    window.onscroll = function() {
        if(document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            $("#top_button").fadeIn();
        } else {
            $("#top_button").fadeOut();
        }
    }
    
    function scroll_top() {
        window.scroll({
            top: 0,
            behavior: 'smooth'
        });
    }

</script>
