<?php
require_once("custom/php/common.php");
if(is_user_logged_in() && current_user_can("insert_values")){
    
    if(!isset($_REQUEST["estado"])){  
        
        echo "
        <h3>Inserção de valores - criança - procurar</h3>
        Introduza um dos nomes da criança a encontrar e/ou a data de nascimento dela<p>";
        
        $nome = $data_nascimento = "";
        $nomeErro = $data_nascimentoErro = "";
        
        echo'
            <form method="post" action = "">
            Nome: <input type="text" name="person_name" >
            <br><br>
            Data de nascimento: (formato AAAA-MM-DD)<input type="text" name="birth_date"> 
            <input type = "hidden" name = "estado" value ="escolher_crianca">
            <br><br>
            <input type="submit" name="Submeter" value="Submeter">
            </form> 
        ';

    }elseif($_REQUEST["estado"] == "escolher_crianca"){

        echo "<h3>Inserção de valores - criança - escolher</h3>";

        $nomeCrianca = $_POST["person_name"];
        $dataCrianca = $_POST["birth_date"];

        //vai buscar à tabela child a criança que tem o mesmo nome ou que tenha esse nome no nome
        $escolhaNome = "SELECT id,name,birth_date FROM child WHERE name LIKE '$nomeCrianca%'";
        //vai buscar à tabela child a criança que nasceu na mesma data 
        $escolhaData = "SELECT id,name,birth_date FROM child WHERE birth_date LIKE '$dataCrianca%'";

        $resultEscolhaNome = mysqli_query($link,$escolhaNome);
        $resultEscolhaData = mysqli_query($link,$escolhaData);

        if(empty($nomeCrianca)){
            while($row1 = mysqli_fetch_assoc($resultEscolhaData)){
                
                $url = "?estado=escolher_item&crianca=".$row1["id"]."";
                echo "
                <p> [<a href ='$current_page.$url'>".$row1["name"]."</a>]
                (".$row1["birth_date"].")</p>";
            }
        }else{
            while($row0 = mysqli_fetch_assoc($resultEscolhaNome)){
                
                $url2 = "?estado=escolher_item&crianca=".$row0["id"]."";
                echo "
                <p>[<a href ='$current_page.$url2'>" .$row0["name"]. "</a>]
                (" .$row0["birth_date"]. ")</p>";
            }
        }
        button_voltar();   
    
    }elseif($_REQUEST["estado"] == "escolher_item"){

        echo "<h3>Inserção de valores - criança - escolher item</h3>";

        //criada variavel de sessao que busca ao url o id da crianca
        $_SESSION["child_id"] = $_REQUEST["crianca"];

        $nomeTipoItem = "SELECT * FROM item_type";
        
        $resultNomeTipoItem = mysqli_query($link,$nomeTipoItem);

        if(mysqli_num_rows($resultNomeTipoItem)>0){
            while($TipoItem = mysqli_fetch_assoc($resultNomeTipoItem)){
                
                //obtem o item que tem o mesmo item_type_id que o tipo.id
                $obterItem = "SELECT DISTINCT item.id, item.name FROM item INNER JOIN subitem ON subitem.item_id = item.id WHERE item.state = 'active' and item_type_id = ".$TipoItem["id"]." ";
                // $obterItem = "SELECT * FROM item WHERE item_type_id=".$TipoItem["id"]."";
                $resultItem = mysqli_query($link,$obterItem);
                
                echo "<ul> 
                    <li>".preg_replace('/_/i',' ',$TipoItem["name"])."</li>
                    <ul>";
                    while ($Item = mysqli_fetch_assoc($resultItem)){
                        
                        $url3 = "?estado=introducao&item=".$Item["id"]."";
                        echo"
                            <li> [<a href='$current_page.$url3' >".$Item["name"]."</a>]</li>
                            ";
                    } echo "</ul> </ul>";
            }
        }
    }elseif($_REQUEST["estado"] == "introducao"){

        //vai buscar ao url o id do item
        $_SESSION["item_id"] = $_REQUEST["item"];

        //obtem o item que tem o mesmo id da variavel de sessao
        $obterItem = "SELECT * FROM item WHERE item.id=".$_SESSION["item_id"]."";
        $resultadoItem = mysqli_query($link,$obterItem);
        $Item = mysqli_fetch_assoc($resultadoItem);

        //variavel de sessao que busca o nome do item que foi escolhido
        $_SESSION["item_name"] = $Item["name"];

        //variavel de sessao que busca o id do tipo de item do item que foi escolhido
        $_SESSION["item_type_id"] = $Item["item_type_id"];

        echo "<h3> Inserção de valores - ".$_SESSION["item_name"]."</h3>";

        //obtem os subitens que o item id do subitem sejam iguais ao da variavel de sessao
        $obterSubitens = "SELECT * FROM subitem WHERE item_id= ".$_SESSION["item_id"]." AND state = 'active' AND mandatory = 1 ORDER BY form_field_order ASC";
        $resultObterSubitens = mysqli_query($link,$obterSubitens);
        
        echo'
            <style>
            .error {color: #FF0012;}
            </style>
            <p><span class="error">* Campo obrigatório</span></p>
            <form nome = "item_type_"'.$_SESSION["item_type_id"].'"_item_"'.$_SESSION["item_id"].'" method = "post" action ="?estado=validar&item="'.$_SESSION["item_id"].'">
            ';
        
        while($Subitens = mysqli_fetch_assoc($resultObterSubitens)){

            switch($Subitens["value_type"]){
                
                case 'double':
                case 'int':
                    echo"<p><span class='error'>* </span>";
                    if($Subitens["unit_type_id"] != ""){

                        //obtem o tipo de unidade do subitem em que o id seja igual ao da coluna unit_type_id da tabela subitem
                        $obterUnidade = "SELECT * FROM subitem_unit_type WHERE id ='". $Subitens["unit_type_id"]."'";
                        $resultUnidade = mysqli_query($link,$obterUnidade);
                        $Unidade = mysqli_fetch_assoc($resultUnidade);

                        echo "".$Subitens["name"]." (".$Unidade["name"]."):<input type = 'text' name = '".$Subitens['form_field_name']."' ><p>";
                    }
                    else{
                        echo "".$Subitens["name"].": <input type = 'text' name = '".$Subitens['form_field_name']."' ><p>";
                    }
                break;
                
                case 'text':
                    
                    echo"<p><span class='error'>* </span>";
                    
                    if($Subitens["unit_type_id"] != ""){
                        if($Subitens["form_field_type"] == "text"){

                            $obterUnidade = "SELECT * FROM subitem_unit_type WHERE id ='". $Subitens["unit_type_id"]."'";
                            $resultUnidade = mysqli_query($link,$obterUnidade);
                            $Unidade = mysqli_fetch_assoc($resultUnidade);

                            echo "".$Subitens["name"]." (".$Unidade["name"]."):<input type = 'text' name = '".$Subitens['form_field_name']."'><p>";
                        }
                        else{

                            $obterUnidade = "SELECT * FROM subitem_unit_type WHERE id ='". $Subitens["unit_type_id"]."'";
                            $resultUnidade = mysqli_query($link,$obterUnidade);
                            $Unidade = mysqli_fetch_assoc($resultUnidade);

                            echo "".$Subitens["name"]." (".$Unidade["name"]."):<textarea id='".$Subitens['form_field_name']."' name = '".$Subitens['form_field_name']."' rows = '4' cols = '50'> </textarea> <p>";
                        }
                    }else{
                        if($Subitens["form_field_type"] == "text"){

                            echo "".$Subitens["name"].":<input type = 'text' name = '".$Subitens['form_field_name']."'><p>";
                        }
                        else{
                            echo "".$Subitens["name"].":<textarea id='".$Subitens['form_field_name']."' name = '".$Subitens['form_field_name']."' rows = '4' cols = '50'> </textarea> <p>";
                        }
                    }
                break;
                
                case 'enum':

                    if($Subitens["unit_type_id"] != ""){

                        $obterUnidade = "SELECT * FROM subitem_unit_type WHERE id ='". $Subitens["unit_type_id"]."'";
                        $resultUnidade = mysqli_query($link,$obterUnidade);
                        $Unidade = mysqli_fetch_assoc($resultUnidade);

                        if($Subitens["form_field_type"] == "checkbox"){
                            
                            echo"<p><span class='error'>* </span>";
                            echo "".$Subitens["name"]." (".$Unidade["name"]."):<p>";

                            $obterValorPermitido ="SELECT * FROM subitem_allowed_value WHERE subitem_id ='".$Subitens["id"]."'";
                            $resultObterescolhas = mysqli_query($link,$obterValorPermitido);
                            
                            while($ValorPermitido = mysqli_fetch_assoc($resultObterescolhas)){                            
                                echo"
                                <input type = 'checkbox' name = '".$Subitens['form_field_name']."[]' value='".$ValorPermitido["value"]."'>".$ValorPermitido['value']."</p>";  
                            }
                        }
                        elseif($Subitens["form_field_type"] == "selectbox"){
                            
                            echo "<p>".$Subitens["name"]." (".$Unidade["name"]."): <select name = '".$Subitens['form_field_name']."' id='".$Subitens['form_field_name']."' >
                            
                            <option value=''></option>";
                            
                            $obterValorPermitido ="SELECT * FROM subitem_allowed_value WHERE subitem_id ='".$Subitens["id"]."'";
                            $resultObterescolhas = mysqli_query($link,$obterValorPermitido);
                            
                            while($ValorPermitido = mysqli_fetch_assoc($resultObterescolhas)){
                                echo"
                                <option value='".$ValorPermitido["value"]."'> ".$ValorPermitido["value"]." </option>";
                            }
                            echo"</select>";
                        }
                        elseif($Subitens["form_field_type"] == "radio"){
                            
                            echo"<p><span class='error'>* </span>";
                            echo "".$Subitens["name"]." (".$Unidade["name"]."): <p>";
                            
                            $obterValorPermitido ="SELECT * FROM subitem_allowed_value WHERE subitem_id ='".$Subitens["id"]."'";
                            $resultObterescolhas = mysqli_query($link,$obterValorPermitido);
                            
                            while($ValorPermitido = mysqli_fetch_assoc($resultObterescolhas)){
                                echo"<input type = 'radio' name = '".$Subitens['form_field_name']."' value='".$ValorPermitido["value"]."'>".$ValorPermitido["value"]."
                                ";
                            }
                        }
                    }else{
                        if($Subitens["form_field_type"] == "checkbox"){
                            
                            echo"<p><span class='error'>* </span>";
                            echo "".$Subitens["name"].": <p>";
                            
                            $obterValorPermitido ="SELECT * FROM subitem_allowed_value WHERE subitem_id ='".$Subitens["id"]."'";
                            $resultObterescolhas = mysqli_query($link,$obterValorPermitido);
                            while($ValorPermitido = mysqli_fetch_assoc($resultObterescolhas)){
                                echo"
                                <input type = 'checkbox' name = '".$Subitens['form_field_name']."[]' value='".$ValorPermitido["value"]."'>".$ValorPermitido['value']."</p>";  
                            }
                        }
                        elseif($Subitens["form_field_type"] == "selectbox"){
                            
                            echo "<p>".$Subitens["name"]." : <select name = '".$Subitens['form_field_name']."' id='".$Subitens['form_field_name']."' >
                            <option value=''></option>";
                            
                            $obterValorPermitido ="SELECT * FROM subitem_allowed_value WHERE subitem_id ='".$Subitens["id"]."'";
                            $resultObterescolhas = mysqli_query($link,$obterValorPermitido);
                            
                            while($ValorPermitido = mysqli_fetch_assoc($resultObterescolhas)){
                                echo"
                                <option value='".$ValorPermitido["value"]."'> ".$ValorPermitido["value"]." </option>";
                            }
                            echo"</select>";
                        }
                        elseif($Subitens["form_field_type"] == "radio"){
                            
                            echo"<p><span class='error'>* </span>";
                            echo "".$Subitens["name"].": <p>";
                            
                            $obterValorPermitido ="SELECT * FROM subitem_allowed_value WHERE subitem_id ='".$Subitens["id"]."'";
                            $resultObterescolhas = mysqli_query($link,$obterValorPermitido);
                            
                            while($ValorPermitido = mysqli_fetch_assoc($resultObterescolhas)){
                                echo"
                                <input type = 'radio' name = '".$Subitens['form_field_name']."' value='".$ValorPermitido["value"]."' >".$ValorPermitido["value"]."";
                            }
                        }
                    }
                
                break;

                case 'bool':
                    echo"<p><span class='error'>* </span>";
                    
                    if($Subitens["unit_type_id"] != ""){
                        
                        $obterUnidade = "SELECT * FROM subitem_unit_type WHERE id ='". $Subitens["unit_type_id"]."'";
                        $resultUnidade = mysqli_query($link,$obterUnidade);
                        $Unidade = mysqli_fetch_assoc($resultUnidade);

                        echo "<p>".$Subitens["name"]." (".$Unidade["name"]."):<input type = 'radio' name = '".$Subitens['form_field_name']."' ></p>";
                    }else{
                        echo "<p>".$Subitens["name"].":<input type = 'radio' name = '".$Subitens['form_field_name']."' ></p>";
                    }
                break;

            }
        }
        echo '
        <input type = "hidden" name = "estado" value = "validar">
        <p><input type="submit" name="Submeter" value="Submeter"></p> 
        </form>
        ';
    }elseif($_REQUEST["estado"] == "validar") {

        echo "<h3> Inserção de valores - ".$_SESSION["item_name"]." - validar</h3>";

        $erro = 0;

        $obterSubitens = "SELECT * FROM subitem WHERE item_id= ".$_SESSION["item_id"]." AND state = 'active'";
        $resultObterSubitens = mysqli_query($link,$obterSubitens);
        
        while($Subitens = mysqli_fetch_assoc($resultObterSubitens)){

            switch($Subitens["form_field_type"]){

                case'text':
                    $campo = $_REQUEST[$Subitens['form_field_name']];
                    if(empty($campo)){
                        $textoErro= "não preencheu o campo ";
                        echo $textoErro.$Subitens["name"]."<p>";
                        $erro++;
                    }
                break;

                case 'textbox':
                    $campo = $_REQUEST[$Subitens['form_field_name']];
                    if(empty($campo)){
                        $textoErro= "não preencheu o campo ";
                        echo $textoErro.$Subitens["name"]."<p>";
                        $erro++;
                    }
                break;

                case 'radio':
                    $campo = $_REQUEST[$Subitens['form_field_name']];
                    if(empty($campo)){
                        $textoErro= "não preencheu o campo ";
                        echo $textoErro.$Subitens["name"]."<p>";
                        $erro++;
                    }
                break;

                case 'checkbox':

                    if(empty($_REQUEST[$Subitens['form_field_name']])){
                        $textoErro= "não preencheu o campo ";
                        echo $textoErro.$Subitens["name"]."<p>";
                        $erro++;
                    }
                break;

                case 'selectbox':
                    if($_REQUEST[$Subitens['form_field_name']] == ''){
                        $textoErro= "não preencheu o campo ";
                        echo $textoErro.$Subitens["name"]."<p>";
                        $erro++;
                    }
                break;

            }
        }
        if($erro > 0){
            echo "<p></p>";
            button_voltar();
        }else{

            echo"
            <p>Estamos prestes a inserir os dados abaixo na base de dados. Confirma que os dados estão correctos e pretende submeter os mesmos?</p>
            ";
            
            $obterSubitens = "SELECT * FROM subitem WHERE item_id= ".$_SESSION["item_id"]." AND state = 'active' ORDER BY form_field_order ASC";
            $resultObterSubitens = mysqli_query($link,$obterSubitens);
            
            
            while ($Subitens = mysqli_fetch_assoc($resultObterSubitens)){
                
                echo "<form method='post' action='?estado=inserir&item='".$_SESSION["item_id"]."' > ";

                if($Subitens["form_field_type"] == 'checkbox'){
                    
                    echo $Subitens["name"].": ";

                    $valores = $_POST[$Subitens["form_field_name"]];

                    for($i = 0;$i<sizeof($valores);$i++){
                        echo $valores[$i]." ";
                        
                        echo "<input type='hidden' name='checkbox[]' value=".$valores[$i]." > ";
                    }

                }else{
                    
                    $valores = $_POST[$Subitens["form_field_name"]];
                    
                    echo"
                    <p>".$Subitens["name"].": ".$valores."</p>";
    
                    echo "<input type='hidden' name='".$Subitens['name']."' value='".$valores."' > "; 
                }
            
            }
            echo"
            <input type='hidden' name='estado' value='inserir'>
            <input type='submit' name='Submeter' value='Submeter'>
            </form>
            ";
        }

    }elseif($_REQUEST["estado"] == "inserir"){
        echo"<h3> Inserção de valores - ".$_SESSION["item_name"]." - inserção </h3>";

        $obterSubitens = "SELECT * FROM subitem WHERE item_id= ".$_SESSION["item_id"]." AND state = 'active' ORDER BY form_field_order ASC";
        $resultObterSubitens = mysqli_query($link,$obterSubitens);
        while ($Subitens = mysqli_fetch_assoc($resultObterSubitens)){

            if($Subitens["form_field_type"] == 'checkbox'){
                $nome = $_POST["checkbox"];

                for($i = 0; $i < sizeof($nome); $i++){
                
                    $inserirNaTabela = "INSERT INTO `value` (`id`, `child_id`, `subitem_id`, `value`, `date`, `time`, `producer`)
                    VALUES (NULL, '".$_SESSION["child_id"]."', '".$Subitens ["id"]."', '$nome[$i]', '".date('Y-m-d')."', '".date('H:i:s')."', '".get_current_user()."')";
                    $resultInserirNaTabela = mysqli_query($link,$inserirNaTabela);
                }
            }else{  

                $inserirNaTabela = "INSERT INTO `value` (`id`, `child_id`, `subitem_id`, `value`, `date`, `time`, `producer`)
                VALUES (NULL, '".$_SESSION["child_id"]."', '".$Subitens ["id"]."', '".$_POST[$Subitens["name"]]."', '".date('Y-m-d')."', '".date('H:i:s')."', '".get_current_user()."')";
                $resultInserirNaTabela = mysqli_query($link,$inserirNaTabela);
            }
        }
        if($resultInserirNaTabela){
            $pagina = "?estado=escolher_item&crianca=".$_SESSION["child_id"]."";
            echo"
                <p style='color:green;'>Inseriu os dados de registo com sucesso.</p>
                <p>Clique em <a href='$current_page'>Voltar</a> para voltar ao início da inserção de valores ou em <a href='$current_page.$pagina'>Escolher </a>item se quiser continuar a inserir valores associados a esta criança<br>";   
        }else{
            echo"<p style='color:red;'>Ocorreu um erro ao inserir os dados</p>";
            button_voltar();
        }
    }
}
else{
    echo "<p>Não tem autorização para aceder a esta página</p>";
    button_voltar();
}
?>