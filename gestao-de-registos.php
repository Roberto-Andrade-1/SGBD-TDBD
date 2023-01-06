<?php 

require_once("custom/php/common.php");

if(is_user_logged_in() && current_user_can("manage_records")){
    if(!isset($_REQUEST["estado"])){
        
        $buscarCrianca = "SELECT * FROM child ORDER BY child.name";
        $resultCrianca = mysqli_query($link,$buscarCrianca);

        if(mysqli_num_rows($resultCrianca)>0){
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

            while($Crianca = mysqli_fetch_assoc($resultCrianca)){

                echo "<tr><td>" .$Crianca["name"]. "</td>";
                echo "<td>" .$Crianca["birth_date"]. "</td>";
                echo "<td>" .$Crianca["tutor_name"]. "</td>";    
                echo "<td>" .$Crianca["tutor_phone"]. "</td>";
                echo "<td>" .$Crianca["tutor_email"]. "</td>";
                echo "<td>"; 

                $buscarItem = "SELECT `name`,`id` FROM item WHERE item.id <= 3 ORDER BY item.name ASC";
                $resultItem = mysqli_query($link,$buscarItem);
                        
                while($Item = mysqli_fetch_assoc($resultItem)){

                    if($Item["name"] == 'autismo'){

                        echo strtoupper($Item["name"]).": ";

                    }else{

                        echo"<br>".strtoupper($Item["name"]).": ";
                    }
                    
                    $cadaidcriança = $Crianca["id"];        
                    $cadaiditem = $Item["id"];
                    
                    $buscarDataProducer = "SELECT DISTINCT value.time, value.date, value.producer 
                    FROM item 
                    INNER JOIN subitem ON item.id = subitem.item_id 
                    INNER JOIN value ON subitem.id = value.subitem_id 
                    WHERE child_id = $cadaidcriança AND item_id = $cadaiditem AND value.producer != '' AND value.value != '' 
                    ORDER BY item.name ASC, date ASC, time ASC";
                    $resultDataProducer = mysqli_query($link,$buscarDataProducer);  
                    
                    while($DataProducer = mysqli_fetch_assoc($resultDataProducer)){

                        echo "<br>[editar][apagar]- <strong>".$DataProducer["date"]."</strong> (".$DataProducer["producer"].") - ";

                        $cadaData = $DataProducer["date"];
                        $cadaTime = $DataProducer["time"];
                        
                        $buscarQuery = "SELECT item.name, item.id, subitem.name AS s_nome, subitem.unit_type_id, value.id AS v_id, value.child_id, value.subitem_id, value.value, value.date, value.time, value.producer 
                        FROM item 
                        INNER JOIN subitem ON item.id = subitem.item_id 
                        INNER JOIN value ON subitem.id = value.subitem_id  
                        WHERE child_id = $cadaidcriança AND item_id = $cadaiditem AND value.time = '$cadaTime' AND value.date = '$cadaData' AND value.producer != '' AND value.value != ''
                        ORDER BY item.name ASC, date ASC, time ASC";      
                        $resultQuery = mysqli_query($link,$buscarQuery);
                        
                        while($query = mysqli_fetch_assoc($resultQuery)){

                            if($query["unit_type_id"] != ""){
                        
                                $obterUnidade = "SELECT * FROM subitem_unit_type WHERE id ='". $query["unit_type_id"]."'";
                                $resultUnidade = mysqli_query($link,$obterUnidade);
                                $Unidade = mysqli_fetch_assoc($resultUnidade);

                                echo "<strong>".$query["s_nome"]."</strong> (".$query["value"]." ".$Unidade["name"]."); ";
                            }else{
                                echo "<strong>".$query["s_nome"]."</strong> (".$query["value"]."); ";
                            }
                        }
                    }
                }echo"</td></tr>";   
            } 
        }else{
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
            button_voltar();
        }else{

            echo 
            '
            <p>Estamos prestes a inserir os dados abaixo na base de dados. Confirma que os dados estão correctos e pretende submeter os mesmos?</p> 
            <p>Nome : '.$nome.' </p>
            <p>Data de Nascimento: '.$data_nascimento.'</p>
            <p>Nome do encarregado de educação: '.$nome_tutor.'</p>
            <p>Telefone do encarregado de educação: '.$numero_tutor.'</p>
            <p>E-mail do encarregado de educação: '.$email_tutor.'</p>
            ';
            
            echo 
            '
            <form action="" method="post" >
            <input type = "hidden" name = "person_nm" value="'.$nome.'">
            <input type = "hidden" name = "birth_dt" value="'.$data_nascimento.'">
            <input type = "hidden" name = "tutor_nm" value="'.$nome_tutor.'">
            <input type = "hidden" name = "tutor_phne" value="'.$numero_tutor.'">
            <input type = "hidden" name = "tutor_mail" value="'.$email_tutor.'">
            '.button_voltar().'ou <br>
            <input type = "hidden" name = "estado" value ="inserir">
            <input type="submit" name="Submeter" value="Submeter">
            </form> 
            ';

        }
    }
    elseif($_REQUEST["estado"] == "inserir"){
        
        echo "<h3>Dados de registo - inserção </h3>";

        $nome_pequeno = $_POST["person_nm"];
        $data_nscmt = $_POST["birth_dt"];
        $nome_ttr = $_POST["tutor_nm"];
        $numero_ttr = $_POST["tutor_phne"];
        $email_ttr = $_POST["tutor_mail"];
        
        $inserirNaTabela = "INSERT INTO child (id, name, birth_date, tutor_name, tutor_phone, tutor_email)
                            VALUES (NULL, '$nome_pequeno','$data_nscmt','$nome_ttr','$numero_ttr','$email_ttr')";
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