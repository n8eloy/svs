<?php
    include('connection.php');
    
    // variável de controle do popup do usuário inválido
    $erro_login=0;  

    if(isset($_SESSION['user_type']))
    {
        header("Location: home.php");
    }

    if(isset($_POST['username'])){
        // pego os valores do post
        $user=strtolower(pg_escape_string(($_POST['username'])));
        $senha=md5(pg_escape_string(($_POST['password'])));

        // script SQL para a consulta
        $string_query="SELECT usuario, tipo FROM login WHERE usuario='".$user."' AND senha='".$senha."' limit 1";

        /* a query retorna algum valor caso haja sucesso na consulta */
        $result = pg_query($conn, $string_query);
        if ($result) {
            $rows = pg_num_rows($result);
            if ($rows>0) { // aqui verifica-se se teve resultado
                /* Aqui, como há somente um resultado, pega-se o resultado
                   e se coloca em um objeto */
                $row = pg_fetch_row($result);
                /* cria-se uma sessão, para a gestão do conteúdo logado */            
                
                $_SESSION['logged']=1;
                $_SESSION['user']=$row[0];
                $_SESSION['user_type']=$row[1];
                $_SESSION['date']=time();
                /* libera o objeto do resultado */
                header("Location: home.php");
            } else { //caso o email e/ou a senha nao foram encontrados no banco de dados
                $erro_login=1;
            }

            /* libera o objeto da conexão */
            pg_close($conn);
        }
    }
?>

<!DOCTYPE html>
<html>    
    <head>
        <title>SVS - Login</title>
        <?php include 'common_meta.php' ?>
        <link rel="stylesheet" href="../stylesheet/login.css">
    </head>
    <!-- Fonte declarada inline para compatibilidade com Chrome -->
    <body class="login_body" style="font-family: 'Lato', sans-serif" >
        <div class="login_container col-lg-4" >
            <img class="login_logo" src="../image/logo.png" alt="Logo">
            <div class="login_outter_frame">
                <div class="login_frame">
                    <h1 class="txt_green" style="text-align: center"> LOG IN </h1>
                    <div class="login_form">
                        <form method="post" action="login.php">
                            <p class="error">
                            <?php
                                if($erro_login==1) {
                                    echo("Usuário ou senha incorreto(s)");
                                }
                            ?>
                            </p>
                            <div class="row mx-auto login_input">
                                <i class="material-icons login_icon">sentiment_neutral</i>
                                <input type="text" id="username" placeholder="Usuário" name="username" required autofocus>
                            </div>
                            <div class="row mx-auto login_input">
                                <i class="material-icons login_icon">lock</i>
                                <input type="password" id="password" placeholder="Senha" name="password" required>
                            </div>
                            <hr>
                            <p style="text-align: center">
                                <button type="submit" class="confirm">Entrar</button>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
