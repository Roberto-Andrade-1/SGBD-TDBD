<?php 

require_once("custom/php/common.php");

if(is_user_logged_in() && current_user_can("manage_records")){
    if(!isset($_REQUEST["estado"])){
        button_voltar();
        #echo "ta";
        echo "<table>
            <tr>
                <th>Nome</th>
                <th>Data de Nascimento</th>
                <th>Enc. de educação</th>
                <th>Telefone do Enc.</th>
                <th>e-mail</th>
                <th>Registos</th>
            </tr>
            ";

                $dados = "SELECT * FROM child ORDER BY child.name";
                $resultDados = mysqli_query($link, $dados);

                if(mysqli_num_rows($resultDados)>0){
                    while($Crianca = mysqli_fetch_assoc($resultDados)){

                        echo "<tr><td>" .$Crianca["name"]. "</td>";
                        echo "<td>" .$Crianca["birth_date"]. "</td>";
                        echo "<td>" .$Crianca["tutor_name"]. "</td>";    
                        echo "<td>" .$Crianca["tutor_phone"]. "</td>";
                        echo "<td>" .$Crianca["tutor_email"]. "</td>";
                        echo "<td>"; 
                        
                        $cadaidcriança = $Crianca["id"];

                        $buscarItem = "SELECT `name`,`id` FROM item WHERE item.id <= 3 ORDER BY item.name ASC";
                        $resultItem = mysqli_query($link,$buscarItem);
                        
                        while($Item = mysqli_fetch_assoc($resultItem)){
                            
                            $cadaiditem = $Item["id"];
                            
                            $query = "SELECT distinct item.name,item.id,subitem.name AS s_nome ,value.id AS v_id,value.child_id,value.subitem_id,value.value,value.date,value.time,value.producer 
                            FROM item 
                            INNER JOIN subitem ON item.id = subitem.item_id 
                            INNER JOIN value ON subitem.id = value.subitem_id 
                            WHERE child_id = $cadaidcriança AND item.id = $cadaiditem AND value.producer != '' AND value.value != '' 
                            ORDER BY item.name ASC, date DESC, time DESC";
                            
                            $resultQuery = mysqli_query($link,$query);

                            //utilizar um if para ver se tem informação em cada criança se não nao imprime isto
                            //talvez usar distict 
                            echo"".strtoupper($Item["name"]).": <p>";
                            while($row2 = mysqli_fetch_assoc($resultQuery)){

                                // usar um if para se o nome for igual ao primeiro fazer paragrafo caso contrario continuar na mesma linha
                                echo" </p>[ed][ap]- <strong>".$row2["date"]."</strong> (".$row2["producer"].") - <strong>".$row2["s_nome"]."</strong> (".$row2["value"].");<p>";
                            }
                        }echo"</td></tr>";   
                    } 
                }
                else{
                    echo "<p> Não há crianças </p>";
                }
        echo "
            </table>";

        echo "<h3>Dados de registo - introdução</h3>";
        echo "Introduza os dados pessoais básicos da criança: <br>";

        echo '
        <style>
        .error {color: #FF0012;}
        </style>

        <p><span class="error">* Campo obrigatório</span></p> 
        <form method="post" action="">  
        <span class="error">* </span>
        Nome completo: <input type="text" name="person_name">
        <br><br>
        <span class="error">* </span>
        Data de nascimento: (formato AAAA-MM-DD) <input type="text" name="birth_date" >
        <br><br>
        <span class="error">* </span>
        Nome completo do encarregado de educação: <input type="text" name="tutor_name" >
        <br><br>
        <span class="error">* </span>
        Telefone do encarregado de educação: (contem 9 dígitos) <input type="text" name="tutor_phone" >
        <br><br>
        Endereço de e-mail do tutor: (opcional)<input type="text" name="tutor_email" >
        <br><br>
        <input type = "hidden" name = "estado" value ="validar">
        <input type="submit" name="Submeter" value="Submeter">
        </form> ';
        #echo "ta";
    }
    elseif($_REQUEST["estado"] == "validar"){
        
        echo "<h3> Dados de registo - validação </h3>";

        $erro = 0;

        $nome = $_POST["person_name"];
        $data_nascimento = $_POST["birth_date"];
        $nome_tutor = $_POST["tutor_name"];
        $numero_tutor = $_POST["tutor_phone"];
        $email_tutor = $_POST["tutor_email"];

        if(empty($nome)){
            $nomeErro = "<p> Nome é obrigatório </p>";
            echo $nomeErro;
            $erro++;
        }else{
            if (!preg_match("/^[a-zA-Z-'´`áéíóúàèìòùãõâêîôûÁÉÍÓÚÀÈÌÒÙÃÕÂÊÎÔÛÇç ]*$/",$nome)){
                $nomeErro= "<p> Nome: Apenas letras e espaços são permitidos </p>";
                echo $nomeErro;
                $erro++;
            }
        }

        if(empty($data_nascimento)){
            $data_nascimentoErro = "<p> Data obrigatória </p>";
            echo $data_nascimentoErro;
            $erro++;
        }else{
            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$data_nascimento)){
                $data_nascimentoErro = "<p> Data: Formato de data AAAA-MM-DD </p>";
                echo $data_nascimentoErro;
                $erro++;
            }
        }
        
        if(empty($nome_tutor)){
            $nome_tutorErro = "<p> Nome do encarregado é obrigatório </p>";
            echo $nome_tutorErro;
            $erro++;
        }else{
            if (!preg_match("/^[A-Za-z-'´`áéíóúàèìòùãõâêîôûÁÉÍÓÚÀÈÌÒÙÃÕÂÊÎÔÛÇç ]*$/",$nome_tutor)){
                $nome_tutorErro= "<p> Nome Encarregado: Apenas letras e espaços são permitidos </p>";
                echo $nome_tutorErro;
                $erro++;
            }
        }

        if(empty($numero_tutor)){
            $numero_tutorErro = "<p> Telemóvel é obrigatório </p>";
            echo $numero_tutorErro;
            $erro++;
        }else{
            if(!preg_match("/^9[1236][0-9]{7}$/",$numero_tutor)){
                $numero_tutorErro = "<p> Número: Introduza um número com 9 algarismos </p>";
                echo $numero_tutorErro;
                $erro++;
            }

        }
        
        if(!empty($email_tutor)){
            if (!filter_var($email_tutor, FILTER_VALIDATE_EMAIL)) {
                $email_tutorErro = "<p>Formato de e-mail inválido</p>";
                echo $email_tutorErro;
                $erro++;
            }
        }

        if($erro > 0){
            echo "<p></p>";
            button_voltar();
        }else{

            echo 
            '
            <p>Estamos prestes a inserir os dados abaixo na base de dados.</p> 
            <p>Nome : '.$_POST["person_name"].' </p>
            <p>Data de Nascimento: '.$_POST["birth_date"].'</p>
            <p>Nome do encarregado de educação: '.$_POST["tutor_name"].'</p>
            <p>Telefone do encarregado de educação: '.$_POST["tutor_phone"].'</p>
            <p>E-mail do encarregado de educação: '.$_POST["tutor_email"].'</p>
            <p>Confirma que os dados estão correctos e pretende submeter os mesmos?</p>
            ';

            button_voltar();
            
            echo 
            '
            <form action="" method="post" >
            <input type = "hidden" name = "person_name" value="'.$_POST["person_name"].'">
            <input type = "hidden" name = "birth_date" value="'.$_POST["birth_date"].'">
            <input type = "hidden" name = "tutor_name" value="'.$_POST["tutor_name"].'">
            <input type = "hidden" name = "tutor_phone" value="'.$_POST["tutor_phone"].'">
            <input type = "hidden" name = "tutor_email" value="'.$_POST["tutor_email"].'">
            <input type = "hidden" name = "estado" value ="inserir">
            <input type="submit" name="Submeter" value="Submeter">
            </form> 
            ';
        }
    }
    elseif($_REQUEST["estado"] == "inserir"){
        
        echo "<h3>Dados de registo - inserção </h3>";

        $nome = $_POST["person_name"];
        $data_nascimento = $_POST["birth_date"];
        $nome_tutor = $_POST["tutor_name"];
        $numero_tutor = $_POST["tutor_phone"];
        $email_tutor = $_POST["tutor_email"];
        
        $inserirNaTabela = "INSERT INTO child (id, name, birth_date, tutor_name, tutor_phone, tutor_email)
                            VALUES (NULL, '$nome','$data_nascimento','$nome_tutor','$numero_tutor','$email_tutor')";
        $result2 = mysqli_query($link,$inserirNaTabela);
        
        if($result2){
            echo"
                <p style='color:green;'>Inseriu os dados de registo com sucesso.</p>
                <p>Clique em <a href='$current_page'>Continuar</a> para avançar<br>";
            }
        else{
            echo"<p style='color:red;'>Ocorreu um erro ao inserir os dados</p>";
            button_voltar();
        }
    }
}
else{
    echo "Não tem autorização para aceder a esta página";
    button_voltar();
}

?>