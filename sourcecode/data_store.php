<?php
	include('connection.php');
    
    if(!isset($_SESSION['user_type']))
    {
        header("Location: login.php");
    }

	if(isset($_GET['cadastro'])) { 
		if(base64url_decode($_GET['cadastro'])=="material") {
            $nome = strtolower(pg_escape_string(($_POST['name'])));
            $qtd = strtolower(pg_escape_string(($_POST['stockamount'])));
            
			if($_POST['flagEdit']==1) {
				$string_query="UPDATE material SET 
                                nome = '".$nome."', 
                                qtd_estoque = '".$qtd."' 
                                WHERE codigo = '".pg_escape_string(($_POST['codigo']))."';";
			} else {
				$string_query="INSERT INTO material (nome, qtd_estoque) 
								VALUES ('".$nome."', 
										'".$qtd."');";
			}
			$result = pg_query($conn, $string_query);
            if ($result) {
				header("location: ./materiais.php");
			} else {
				if(strpos(pg_last_error($conn), 'already exists') != false) {
                    // Verifica se já existe uma tupla com a chave primária no BD
                    $_SESSION['bd_error'] = 'Já existe um material cadastrado com o código gerado. Isso não deveria acontecer. Contate um administrador do sistema';
                } elseif (strpos(pg_last_error($conn), 'syntax error') != false) {
                    // Verifica se ocorreu erro de sintaxe
                    $_SESSION['bd_error'] = 'Problema de sintaxe de consulta. Contate um administrador do sistema';
                } else {
                    // Erro genérico
                    $_SESSION['bd_error'] = 'Contate um administrador do sistema se o problema persistir';
                }
				header("location: ./materiais_novo.php");
			}
		} elseif(base64url_decode($_GET['cadastro'])=="cliente") {
            $cpf = strtolower(pg_escape_string(($_POST['cpf'])));
            $nome = strtolower(pg_escape_string(($_POST['name'])));
            $email = strtolower(pg_escape_string(($_POST['email'])));
            $endereco = strtolower(pg_escape_string(($_POST['endereco'])));
            $telefone1 = strtolower(pg_escape_string(($_POST['telefone1'])));
            $telefone2 = strtolower(pg_escape_string(($_POST['telefone2'])));
            
			if($_POST['flagEdit']==1) {
				$string_query="UPDATE cliente SET 
								cpf = '".$cpf."',
                                nome = '".$nome."',
                                email ='".$email."',
                                endereco='".$endereco."',
                                telefone_1='".$telefone1."',
                                telefone_2='".$telefone2."'
                                WHERE cpf = '".pg_escape_string(($_POST['codigo']))."';";
			} else {
				$string_query="INSERT INTO cliente (cpf, nome, email, endereco, telefone_1, telefone_2) 
								VALUES ('".$cpf."',
										'".$nome."',
										'".$email."',
										'".$endereco."',
										'".$telefone1."', 
										'".$telefone2."');";
			}
			$result = pg_query($conn, $string_query);
            if ($result) {
				header("location: ./clientes.php");
			} else {
				if(strpos(pg_last_error($conn), 'already exists') != false) {
                    // Verifica se já existe uma tupla com a chave primária no BD
                    $_SESSION['bd_error'] = 'Já existe um cliente cadastrado com o CPF inserido';
                } elseif (strpos(pg_last_error($conn), 'syntax error') != false) {
                    // Verifica se ocorreu erro de sintaxe
                    $_SESSION['bd_error'] = 'Problema de sintaxe de consulta. Contate um administrador do sistema';
                } else {
                    // Erro genérico
                    $_SESSION['bd_error'] = 'Contate um administrador do sistema se o problema persistir';
                }
				header("location: ./clientes_novo.php");
			}
        } elseif(base64url_decode($_GET['cadastro'])=="veterinario") {
            $crmv = pg_escape_string(($_POST['crmv']));
            $nome = strtolower(pg_escape_string(($_POST['name'])));
            $email = strtolower(pg_escape_string(($_POST['email'])));
            $salario = pg_escape_string(($_POST['salary']));
            $telefone_1 = pg_escape_string(($_POST['tel1']));
            $telefone_2 = pg_escape_string(($_POST['tel2']));
            $especialidade = strtolower(pg_escape_string(($_POST['spec'])));
            
            $array_esp = explode(',', $especialidade);

			if($_POST['flagEdit']==1) {
				$string_query="UPDATE veterinario SET 
                                nome = '".$nome."', 
                                email = '".$email."',
                                salario = '".$salario."',
                                telefone_1 = '".$telefone_1."',
                                telefone_2 = '".$telefone_2."' 
                                WHERE crmv = '".pg_escape_string(($_POST['codigo']))."';";
                $string_query .= "DELETE FROM vet_especialidade WHERE crmv = '".pg_escape_string(($_POST['codigo']))."';";
			} else {
                if(isset($_POST['tel2']) && ($_POST['tel2'] != '')) {
				    $string_query="INSERT INTO veterinario (crmv, nome, email, salario, telefone_1, telefone_2) VALUES ('".$crmv."', '".$nome."', '".$email."', '".$salario."', '".$telefone_1."', '".$telefone_2."');";
                } else {
                    $string_query="INSERT INTO veterinario (crmv, nome, email, salario, telefone_1) VALUES ('".$crmv."', '".$nome."', '".$email."', '".$salario."', '".$telefone_1."');";
                }
            }
            foreach($array_esp as $esp){
                $string_query .= "INSERT INTO vet_especialidade (crmv, especialidade) VALUES ('".$_POST['crmv']."', '".$esp."');";
            }
			$result = pg_query($conn, $string_query);
            if ($result) {
				header("location: ./veterinarios.php");
			} else {
				if(strpos(pg_last_error($conn), 'already exists') != false) {
                    // Verifica se já existe uma tupla com a chave primária no BD
                    $_SESSION['bd_error'] = 'Já existe um veterinário cadastrado com o CRMV inserido';
                } elseif (strpos(pg_last_error($conn), 'syntax error') != false) {
                    // Verifica se ocorreu erro de sintaxe
                    $_SESSION['bd_error'] = 'Problema de sintaxe de consulta. Contate um administrador do sistema';
                } else {
                    // Erro genérico
                    $_SESSION['bd_error'] = 'Contate um administrador do sistema se o problema persistir';
                }
				header("location: ./veterinarios_novo.php");
			}
        } elseif(base64url_decode($_GET['cadastro'])=="paciente") {
            $cpf = strtolower(pg_escape_string(($_POST['cpf'])));
            $nome = strtolower(pg_escape_string(($_POST['name'])));
            $raca = strtolower(pg_escape_string(($_POST['raca'])));
            $especie = strtolower(pg_escape_string(($_POST['especie'])));
            $peso = strtolower(pg_escape_string(($_POST['weight'])));
            $idade = strtolower(pg_escape_string(($_POST['birthday'])));
            $especieorigin = strtolower(pg_escape_string(($_POST['especieorigin'])));

            if($_POST['nova_especie']==1){
            	$string_query="INSERT INTO paciente_especie (raca, especie)
            					VALUES ('".$raca."',
            							'".$especie."'
            					);";
			    $resultesp = pg_query($conn, $string_query);
            }else{
            	$string_query="UPDATE paciente_especie SET especie = '".$especie."' WHERE raca = '".$raca."';";
            	 $resultesp = pg_query($conn, $string_query);
            }
			if($_POST['flagEdit']==1) {
				$string_query="UPDATE paciente SET 
								cpf = '".$cpf."',
                                nome = '".$nome."',
                                raca ='".$raca."',
                                peso='".$peso."',
                                data_nascimento ='".$idade."'
                                WHERE cpf = '".$_POST['cpf']."' AND nome = '".strtolower(pg_escape_string(($_POST['nome'])))."';";
			} else {
				$string_query="INSERT INTO paciente (cpf, nome, raca, peso, data_nascimento) 
								VALUES ('".$cpf."',
										'".$nome."',
										'".$raca."',
										'".$peso."',
										'".$idade."');";
			}
			$result = pg_query($conn, $string_query);
            if ($result) {
				header("location: ./pacientes.php?cpf=".base64url_encode($cpf));
			} else {
				if(strpos(pg_last_error($conn), 'already exists') != false) {
                    // Verifica se já existe uma tupla com a chave primária no BD
                    $_SESSION['bd_error'] = 'Já existe um paciente cadastrado com o nome inserido';
                } elseif (strpos(pg_last_error($conn), 'syntax error') != false) {
                    // Verifica se ocorreu erro de sintaxe
                    $_SESSION['bd_error'] = 'Problema de sintaxe de consulta. Contate um administrador do sistema';
                } else {
                    // Erro genérico
                    $_SESSION['bd_error'] = 'Contate um administrador do sistema se o problema persistir';
                }
				header("location: ./pacientes_novo.php?cpf=".base64url_encode($cpf)."&editar=".base64url_encode($nome)."&error=1");
			}
            
        } elseif(base64url_decode($_GET['cadastro'])=="procedimento") {
            $data = pg_escape_string(($_POST['date'])) . " " . pg_escape_string(($_POST['time']));
            $sala = pg_escape_string(($_POST['room']));
            $tipo = pg_escape_string(($_POST['type']));
            $custo = pg_escape_string(($_POST['cost']));
            $descricao = pg_escape_string(utf8_encode($_POST['description']));
            $cpf = pg_escape_string(($_POST['cpf']));
            $nome = strtolower(pg_escape_string(($_POST['name'])));
            
            $lista_material = explode(';', pg_escape_string(($_POST['material'])));
            $lista_veterinario = explode(';', pg_escape_string(($_POST['veterinario'])));
            
            // Utilizado para testes
            //$str = 'tempo: '.$data.', sala: '.$sala.', custo: '.$custo.', tipo: '.$tipo.', cpf: '.$cpf.', nome: '.$nome.', materiais: '.implode(' ', $lista_material).', veterinarios: '.implode(' ', $lista_veterinario);
            
            //die($str);
            
            if($_POST['flagEdit']==1) {
                // Separa data e sala do procedimento
                $edit_code = explode('&', pg_escape_string(($_POST['codigo'])));
                
                // Atualiza o procedimento                
                $string_proc_query="UPDATE procedimento SET 
								data = '".$data."',
                                sala = '".$sala."',
                                tipo = '".$tipo."',
                                custo = '".$custo."',
                                descricao = '".$descricao."',
                                cpf_pac = '".$cpf."',
                                nome_pac = '".$nome."'
                                WHERE data = '".$edit_code[0]."' AND sala = '".$edit_code[1]."';";
                
                // Remove os antigos e insere os materiais utilizados
                $string_util_query="DELETE FROM utiliza WHERE data_proc='".$edit_code[0]."' AND sala_proc='".$edit_code[1]."';
                                    INSERT INTO utiliza (data_proc, sala_proc, codigo_mat, quantidade) VALUES ";
                foreach ($lista_material as $i => $value) {
                    // Separa código e quantidade utilizada
                    $material = explode(":", $value);
                    
                    if($material[0] != '' && $material[1] != '') {
                        if($i != 0) {
                            // Adiciona vírgula de separação de tuplas
                            $string_util_query = $string_util_query . ",";
                        }
                        // Adiciona a tupla
                        $string_util_query = $string_util_query . "('".$data."','".$sala."','".$material[0]."','".$material[1]."')";
                    }
                }
                $string_util_query = $string_util_query . ";";
                
                // Remove os antigos e insere os veterinários envolvidos
                $string_real_query="DELETE FROM realiza WHERE data_proc='".$edit_code[0]."' AND sala_proc='".$edit_code[1]."';
                                    INSERT INTO realiza (data_proc, sala_proc, crmv_vet) VALUES ";
                foreach ($lista_veterinario as $i => $value) {
                    if($value != '') {
                        if($i != 0) {
                            // Adiciona vírgula de separação de tuplas
                            $string_real_query = $string_real_query . ",";
                        }
                        // Adiciona a tupla
                        $string_real_query = $string_real_query . "('".$data."','".$sala."','".$value."')";
                    }
                }
                $string_real_query = $string_real_query . ";";
            } else {
                // Insere inicialmente o procedimento
                $string_proc_query="INSERT INTO procedimento (data, sala, tipo, custo, descricao, cpf_pac, nome_pac) 
								VALUES ('".$data."',
										'".$sala."',
                                        '".$tipo."',
										'".$custo."',
										'".$descricao."',
										'".$cpf."',
                                        '".$nome."');";
                
                // Insere os materiais utilizados
                $string_util_query="INSERT INTO utiliza (data_proc, sala_proc, codigo_mat, quantidade) VALUES ";
                foreach ($lista_material as $i => $value) {
                    // Separa código e quantidade utilizada
                    $material = explode(":", $value);
                    
                    if($material[0] != '' && $material[1] != '') {
                        if($i != 0) {
                            // Adiciona vírgula de separação de tuplas
                            $string_util_query = $string_util_query . ",";
                        }
                        // Adiciona a tupla
                        $string_util_query = $string_util_query . "('".$data."','".$sala."','".$material[0]."','".$material[1]."')";
                    }
                }
                $string_util_query = $string_util_query . ";";
                
                // Insere os veterinários envolvidos
                $string_real_query="INSERT INTO realiza (data_proc, sala_proc, crmv_vet) VALUES ";
                foreach ($lista_veterinario as $i => $value) {
                    if($value != '') {
                        if($i != 0) {
                            // Adiciona vírgula de separação de tuplas
                            $string_real_query = $string_real_query . ",";
                        }
                        // Adiciona a tupla
                        $string_real_query = $string_real_query . "('".$data."','".$sala."','".$value."')";
                    }
                }
                $string_real_query = $string_real_query . ";";
            }
                        
            // Insere verificando em caso de erro
            try {
                // Procedimento
                $result = pg_query($conn, $string_proc_query);
                if ($result) {
                    // Material utilizado
                    $result = pg_query($conn, $string_util_query);
                    if ($result) {
                        // Veterinário envolvido
                        $result = pg_query($conn, $string_real_query);
                        if ($result) {
                            header("location: ./procedimentos.php");
                        } else {
                            if(strpos(pg_last_error($conn), 'already exists') != false) {
                                // Verifica se já existe uma tupla com a chave primária no BD
                                $str_error = 'Já existe um veterinário cadastrado para o procedimento. Isso não deveria acontecer. Contate um administrador do sistema';
                            } elseif (strpos(pg_last_error($conn), 'syntax error') != false) {
                                // Verifica se ocorreu erro de sintaxe
                                $str_error = 'Problema de sintaxe de consulta. Contate um administrador do sistema';
                            } else {
                                // Erro genérico
                                $str_error = 'Contate um administrador do sistema se o problema persistir';
                            }
                            throw new Exception($str_error);
                        }
                    } else {
                        if(strpos(pg_last_error($conn), 'already exists') != false) {
                            // Verifica se já existe uma tupla com a chave primária no BD
                            $str_error = 'Já existe um material cadastrado para o procedimento. Isso não deveria acontecer. Contate um administrador do sistema';
                        } elseif (strpos(pg_last_error($conn), 'syntax error') != false) {
                            // Verifica se ocorreu erro de sintaxe
                            $str_error = 'Problema de sintaxe de consulta. Contate um administrador do sistema';
                        } else {
                            // Erro genérico
                            $str_error = 'Contate um administrador do sistema se o problema persistir';
                        }
                        throw new Exception($str_error);
                    }
                } else {
                    if(strpos(pg_last_error($conn), 'already exists') != false) {
                        // Verifica se já existe uma tupla com a chave primária no BD
                        $str_error = 'Já existe um procedimento cadastrado na sala, data e horário inseridos';
                    } elseif (strpos(pg_last_error($conn), 'syntax error') != false) {
                        // Verifica se ocorreu erro de sintaxe
                        $str_error = 'Problema de sintaxe de consulta. Contate um administrador do sistema';
                    } else {
                        // Erro genérico
                        $str_error = 'Contate um administrador do sistema se o problema persistir';
                    }
                    throw new Exception($str_error);
                }
            } catch (Exception $erro) {
                $_SESSION['bd_error'] = $erro->getMessage();
                // Para testes: $_SESSION['bd_error'] = pg_last_error($conn);
                
                // Remove tudo que foi inserido em caso de erro em uma das partes
                // Como utiliza e realiza estão configurados como CASCADE, é necessário remover apenas o procedimento
                $result = pg_query($conn, "DELETE FROM procedimento WHERE data='".$data."' AND sala='".$sala."'");
                die(pg_last_error($conn));
                // Retorna com erro
                header("location: ./procedimentos_novo.php");
            }
        } elseif(base64url_decode($_GET['cadastro'])=="usuario") {
            $usuario = strtolower(pg_escape_string(($_POST['username'])));
            $tipo = pg_escape_string(($_POST['type']));
            $senha = pg_escape_string(($_POST['password']));
            
            if($_POST['flagEdit']==1) {
                $string_query="UPDATE login SET
                                usuario = '".$usuario."',
                                tipo = '".$tipo."', 
                                senha = md5('".$senha."') 
                                WHERE usuario = '".pg_escape_string(($_POST['codigo']))."';";
                // Logout caso seja o usuário logado
                if ($_SESSION['user'] == pg_escape_string($_POST['codigo'])) {
                    session_unset();
                    session_destroy();
                }
            } else {
                $string_query="INSERT INTO login (usuario, tipo, senha) 
                                VALUES ('".$usuario."',
                                        '".$tipo."', 
                                        md5('".$senha."'));";
            }
            $result = pg_query($conn, $string_query);
            if ($result) {
                header("location: ./adm.php");
            } else {
                if(strpos(pg_last_error($conn), 'already exists') != false) {
                    // Verifica se já existe uma tupla com a chave primária no BD
                    $_SESSION['bd_error'] = 'Já existe um usuário cadastrado com o nome inserido';
                } elseif (strpos(pg_last_error($conn), 'syntax error') != false) {
                    // Verifica se ocorreu erro de sintaxe
                    $_SESSION['bd_error'] = 'Problema de sintaxe de consulta. Contate um administrador do sistema';
                } else {
                    // Erro genérico
                    $_SESSION['bd_error'] = 'Contate um administrador do sistema se o problema persistir';
                }
                header("location: ./adm_novo.php");
            }
        }else die("Erro: formulário não encontrado");
		
	}
	else header("Location: ./home.php");
?>
