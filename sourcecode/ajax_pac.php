<?php
    include('connection.php');
    
    if(!isset($_SESSION['user_type']))
    {
        header("Location: login.php");
    }

    $cpf=$_GET['cpf'];

    $string_query="SELECT nome FROM paciente WHERE cpf='".$cpf."'";

    $result = pg_query($conn, $string_query);

    // Verifica se houve retorno
    if($result && pg_num_rows($result) > 0) { ?>
    <select class="type_select" name="name" id="name" required>
        <?php
            while($value = pg_fetch_assoc($result)) {?>
                <option value="<?=$value['nome']?>"><?=ucwords($value['nome'])?></option>
            <?php }
        ?>
        <option selected disabled hidden>Selecionar Paciente</option>
    </select><br>
    <?php } else { ?> 
    <select class="type_select" name="name" id="name" disabled required>
        <option selected disabled hidden>Insira um CPF existente</option>
    </select><br>
    <?php } ?>