<?php
require_once("custom/php/common.php");
if(is_user_logged_in()){
    
    if($_REQUEST["estado"] == "editar"){

        //item
        if($_REQUEST["tabela"] == "item"){

            $_SESSION["id_item"] = $_REQUEST["id"];

            $buscarItem = "SELECT item.id, item.name, item.state, item.item_type_id
            FROM item, item_type 
            WHERE item.id = '".$_SESSION["id_item"]."' AND item.item_type_id = item_type.id";
            $resultItem = mysqli_query($link,$buscarItem);
            while ($Item = mysqli_fetch_assoc($resultItem)){

                echo "<h3>Edição da tabela item </h3>";

                echo "
                <body>
                <form action='' method='POST'>
                <label>Nome:<input type='text' name='item_nome' value='".$Item["name"]."'></label><br>";

                echo "<br><label>Tipo:</label><br>";

                $buscarTipo = "SELECT item_type.id, item_type.name FROM item_type";
                $resultTipo = mysqli_query($link,$buscarTipo);
                while($Tipo=mysqli_fetch_assoc($resultTipo)){
                    
                    if ($Tipo["id"] == $Item["item_type_id"]){
                        echo "
                        <input type='radio' name='item_tipo' checked value='" .$Tipo["id"]. "'>
                        <label>" .$Tipo["name"]. "</label><br>";
                    }else{
                        echo "
                        <input type='radio' name='item_tipo' value='" .$Tipo["id"]. "'>
                        <label>" .$Tipo["name"]. "</label><br>";
                    }
                }

                echo "
                <input type='hidden' name='estado' value='editarItem'>
                <br><input type='submit' value='Editar Item'>
                <br><br>
                </form>
                </body>";
            }
        //unidades
        }else if($_REQUEST["tabela"] == "unidades"){

            $_SESSION["id_unidade"] = $_REQUEST["id"];
            
            $buscarSubitens = "SELECT * FROM subitem_unit_type WHERE id ='".$_SESSION["id_unidade"]."' ";
            $resultSubitens = mysqli_query($link, $buscarSubitens);

            while($Subitens = mysqli_fetch_assoc($resultSubitens)){
                echo "<h3>Edição da tabela unidades </h3>
                    <form action='' method='POST'>
                    Nome da unidade:<input type='text' name=nome_unidade required value='".$Subitens["name"]."'><br>
                    <input type='hidden' name='estado' value='editarUnidade'>
                    <input type='submit' value='Editar unidade'>
                    <br><br>
                    </form>";
            }
        //subitens
        }else if($_REQUEST["tabela"] == "subitens"){

            $_SESSION["id_subitens"] = $_REQUEST["id"];

            $queryItems = "SELECT * FROM item ORDER BY item.name ASC";
            $resulItems = mysqli_query($link, $queryItems);

            while ($rowItem = mysqli_fetch_assoc($resulItems)) {

                $nomeItem = $rowItem['name'];
                $idItem = $rowItem['id'];

                $querySubitemItemId = "SELECT * FROM subitem WHERE item_id=$idItem AND id = '".$_SESSION["id_subitens"]."' ORDER BY subitem.name ASC";
                $resultSubitemItemId = mysqli_query($link, $querySubitemItemId);

                while($SubitemItemId = mysqli_fetch_assoc($resultSubitemItemId)){

                    echo "<h3>Edição da tabela subitens</h3>

                    <form action='' method='POST'>
                    <label>Nome do subitem: (Obrigatorio)</label>
                    <input type='text' name='nome_subitem' value ='".$SubitemItemId["name"]."' >
                    <br><br><label>Tipo de valor:</label><br>";

                    foreach (get_enum_values($link, 'subitem', 'value_type') as $tipoDeValor) {
                        
                        if($tipoDeValor == $SubitemItemId["value_type"]){
                            echo "<input type='radio' name='tipoValor' checked value=$tipoDeValor >
                            <label>$tipoDeValor</label><br>";
                        }else{
                            echo "<input type='radio' name='tipoValor' value=$tipoDeValor >
                            <label>$tipoDeValor</label><br>";
                        }
                    }

                    echo "<br><label>Item:</label>
                        <select name='idItems' id='idItems' >";

                    $queryItemName = "SELECT * FROM item ORDER BY item.name ASC";
                    $resultitemName = mysqli_query($link, $queryItemName);
                    while ($itemName = mysqli_fetch_assoc($resultitemName)) {
                        
                        if($itemName["id"] == $SubitemItemId["item_id"]){
                            echo "<option value=" . $itemName['id'] . ">" . $itemName['name'] . "</option>";
                        }else{
                            echo "<option value=" . $itemName['id'] . ">" . $itemName['name'] . "</option>";
                        }
                    }
                    echo "</select>";

                    echo "<br><br><label>Tipo do campo do formulário:</label><br>";
                    foreach (get_enum_values($link, 'subitem', 'form_field_type') as $tipoDeCampoDoFormulario) {
                        
                        if($tipoDeCampoDoFormulario == $SubitemItemId["form_field_type"]){
                            echo "<input type='radio' name='tipoDeCampoDoFormulario' checked value=$tipoDeCampoDoFormulario >
                            <label>$tipoDeCampoDoFormulario</label><br>";
                        }else{
                            echo "<input type='radio' name='tipoDeCampoDoFormulario' value=$tipoDeCampoDoFormulario >
                            <label>$tipoDeCampoDoFormulario</label><br>";
                        }
                    }

                    echo "<br><label>Tipo de unidade:</label><br>
                        <select name='tipoUnidade' id='tipoUnidade'>";

                    $querySubitemUnidade = "SELECT * FROM subitem_unit_type";
                    $resultSubitemUnidade = mysqli_query($link, $querySubitemUnidade);
                    while ($tipoDeUnidade = mysqli_fetch_assoc($resultSubitemUnidade)) {

                        if($tipoDeUnidade["id"] == $SubitemItemId["unit_type_id"]){
                            echo "<option value=" . $tipoDeUnidade["id"] . ">" . $tipoDeUnidade["name"] . "</option>";
                        }else{
                            echo "<option value=" . $tipoDeUnidade["id"] . ">" . $tipoDeUnidade["name"] . "</option>";
                        }
                    }
                    echo "</select>";

                    echo "<br><br><label>Ordem do campo no formulário</label>
                        <input type='text' name='ordemDoCampoDoForm' value='".$SubitemItemId["form_field_order"]."'>";

                    echo "<br><br><label>Obrigatório</label><br>";

                    if($SubitemItemId["mandatory"] == '1'){
                        
                        echo"
                        <input type='radio' name='obrigatorio' checked value='1' >
                        <label>sim</label><br>
                        <input type='radio' name='obrigatorio' value='0'>
                        <label>não</label>";
                    }
                    else if($SubitemItemId["mandatory"] == '0'){
                        
                        echo"
                        <input type='radio' name='obrigatorio' value='1' >
                        <label>sim</label><br>
                        <input type='radio' name='obrigatorio' checked value='0'>
                        <label>não</label>";
                    }

                    echo "<br>
                        <input type='hidden' name='estado' value='editarSubitens'>
                        <br><input type='submit' value='Inserir subitem'>";

                    "</form>";
                }
            }
        //valores permitidos
        }else if($_REQUEST["tabela"] == "valoresPermitidos"){

            $_SESSION["id_permitidos"] = $_REQUEST["id"];

            $queryPermitidos = "SELECT subitem_allowed_value.id, subitem_allowed_value.value, subitem_allowed_value.state 
            FROM subitem_allowed_value, subitem 
            WHERE subitem.id = subitem_allowed_value.subitem_id AND subitem_allowed_value.id = ".$_SESSION["id_permitidos"]."";
            $resultPermitidos = mysqli_query($link,$queryPermitidos);
            while($Permitidos = mysqli_fetch_assoc($resultPermitidos)){
                echo "<h3>Edição da tabela inserção de valores</h3>";

                echo"
                <form action='' method='POST'>
                Valores :<input type = 'text' name = 'insercao_value' value = '".$Permitidos["value"]."'><br><br>
                <input type = 'hidden' name = 'estado' value = 'editarValorPerm'>
                <input type = 'submit' value = 'Editar unidade'>
                <br><br>
                </form>";
            }
        //registos
        }else if($_REQUEST["tabela"] = "registos"){

            $_SESSION["item_id"] = $_REQUEST["item"];

            echo "<h3>Edição da coluna registos da gestão de registos</h3>";

            $_SESSION["crianca_id"] = $_REQUEST["crianca"];

            //obtem o item que tem o mesmo id da variavel de sessao
            $obterItem = "SELECT * FROM item WHERE item.id=".$_SESSION["item_id"]."";
            $resultadoItem = mysqli_query($link,$obterItem);
            $Item = mysqli_fetch_assoc($resultadoItem);

            //variavel de sessao que busca o nome do item que foi escolhido
            $_SESSION["item_name"] = $Item["name"];

            //variavel de sessao que busca o id do tipo de item do item que foi escolhido
            $_SESSION["item_type_id"] = $Item["item_type_id"];

            //obtem os subitens que o item id do subitem sejam iguais ao da variavel de sessao
            $obterSubitens = "SELECT * FROM subitem WHERE item_id= ".$_SESSION["item_id"]." AND state = 'active' ORDER BY form_field_order ASC";
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

                            echo "".$Subitens["name"]." (".$Unidade["name"]."):<input type = 'text' name = '".$Subitens['form_field_name']."' value = '' ><p>";
                        }
                        else{
                            echo "".$Subitens["name"].": <input type = 'text' name = '".$Subitens['form_field_name']."' value = '' ><p>";
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
        }
    }//editar item
    else if($_REQUEST["estado"] == "editarItem"){

        echo "<h3>Edição - validação</h3>";

        $nomeEditado = $_POST["item_nome"];
        $tipoEditado = $_POST["item_tipo"];
        $erro = 0;

        $queryVerificaItem = "SELECT * FROM item"; //Query da tabela subitem_allowed_value
        $resultVerificaItem = mysqli_query($link, $queryVerificaItem);
    
        while($rowVerificaItem = mysqli_fetch_assoc($resultVerificaItem)){
            if(strcmp($rowVerificaItem["name"],$nomeEditado)==0 && $erro== 0){ //Se o nome do item já exite na base de dados
                echo "\n <h1 style='color:Red;'>Já existe um item com este nome na base de dados</h1>";
                $erro ++;
            }
        }

        if(empty($nomeEditado)){
            $nomeErro = "<p> Nome é obrigatório </p>";
            echo $nomeErro;
            $erro++;
        }else{
            if (!preg_match("/^[a-zA-Z-'´`áéíóúàèìòùãõâêîôûÁÉÍÓÚÀÈÌÒÙÃÕÂÊÎÔÛÇç ]*$/",$nomeEditado)){
                $nomeErro= "<p> Nome: Apenas letras e espaços são permitidos </p>";
                echo $nomeErro;
                $erro++;
            }
        }

        if($erro>0){
            button_voltar();
        }else{
            echo "Estamos prestes a alterar estes itens. Confirma que os dados estão corretos e pretende alterar os mesmos?<br>
                <br>Nome: $nomeEditado<br>
                Tipo: $tipoEditado<br><br>";
            echo"
                <form action='' method='post' >
                <input type = 'hidden' name = 'nome_editado' value='".$nomeEditado."'>
                <input type = 'hidden' name = 'tipo_editado' value='".$tipoEditado."'>
                ".button_voltar()."ou <br>
                <input type = 'hidden' name = 'estado' value ='inserirItem'>
                <input type='submit' name='Submeter' value='Submeter'>
                </form> 
            ";
        }
    }//editar unidade
    else if($_REQUEST["estado"] == "editarUnidade"){

        echo "<h3>Edição - validação</h3>";

        $nomeEditado = $_POST["nome_unidade"];
        $erro = 0;

        $query = "SELECT * FROM subitem_unit_type";
        $result = mysqli_query($link, $query);

        while ($row = mysqli_fetch_assoc($result)) {
            //strcasecmp se as duas var forem iguais mesmo contando com as maisculas e minuscalas 
            if (strcasecmp($row['name'], $nomeEditado) == 0 && $erro == 0) {
                echo "<p style='color:red;'>Esta unidade já existe na base de dados</p>";
                $erro++;
            }
        }

        if(empty($nomeEditado)){
            $nomeErro = "<p> Nome é obrigatório </p>";
            echo $nomeErro;
            $erro++;
        }else{
            if (!preg_match("/^[a-zA-Z-'´`áéíóúàèìòùãõâêîôûÁÉÍÓÚÀÈÌÒÙÃÕÂÊÎÔÛÇç ]*$/",$nomeEditado)){
                $nomeErro= "<p> Nome: Apenas letras e espaços são permitidos </p>";
                echo $nomeErro;
                $erro++;
            }
        }
        if($erro>0){
            button_voltar();
        }else{
            echo "Estamos prestes a alterar estes itens. Confirma que os dados estão corretos e pretende alterar os mesmos?<br>
                <br>Nome: $nomeEditado<br><br>";
            echo"
                <form action='' method='post' >
                <input type = 'hidden' name = 'unidade_editada' value='".$nomeEditado."'>
                ".button_voltar()."ou <br>
                <input type = 'hidden' name = 'estado' value ='inserirUnidade'>
                <input type='submit' name='Submeter' value='Submeter'>
                </form> 
            ";
        }
    }//editar subitens
    else if($_REQUEST["estado"] == "editarSubitens"){

        echo "<h3>Edição - validação</h3>";
        
        $nomeEditado = $_POST["nome_subitem"];
        $valorEditado = $_POST["tipoValor"];
        $idEditado = $_POST["idItems"];
        $tipoDeCampoEditado = $_POST["tipoDeCampoDoFormulario"];
        $tipoUnidadeEditado = $_POST["tipoUnidade"];
        $ordemDoCampoEditado = $_POST["ordemDoCampoDoForm"];
        $obrigatorioEditado = $_POST["obrigatorio"];
        $erro = 0;

        if(empty($nomeEditado)){
            $nomeErro = "<p> Nome é obrigatório </p>";
            echo $nomeErro;
            $erro++;
        }else{
            if (!preg_match("/^[a-zA-Z-'´`áéíóúàèìòùãõâêîôûÁÉÍÓÚÀÈÌÒÙÃÕÂÊÎÔÛÇç ]*$/",$nomeEditado)){
                $nomeErro= "<p> Nome: Apenas letras e espaços são permitidos </p>";
                echo $nomeErro;
                $erro++;
            }
        }

        if (empty($valorEditado)) {
            echo "<p style='color:red;'>Escolher o tipo de valor é obrigatório</p>";
            $erro++ ;
        }

        if (empty($idEditado)) {
            echo "<p style='color:red;'>A seleção de um item é obrigatório</p>";
            $erro++;
        }

        if (empty($tipoDeCampoEditado)) {
            echo "<p style='color:red;'>É necessário escolher uma das opções do formulário</p>";
            $erro++;
        }

        if (empty($tipoUnidadeEditado)){
            echo "<p style='color:red;'>É necessário escolher uma das opções do formulário</p>";
            $erro++;
        }

        if (empty($ordemDoCampoEditado) || $ordemDoCampoEditado < 1) {
            echo "<p style='color:red;'>É necessário inserir um número maior que 0 na ordem do campo no formulário</p>";
            $erro++;
        }

        if (empty($obrigatorioEditado)) {
            echo "<p style='color:red;'>É necessário escolher uma das opções do campo 'Obrigatório'</p>";
            $erro++;
        }

        if($erro>0){
            button_voltar();
        }else{
            echo "Estamos prestes a alterar estes itens. Confirma que os dados estão corretos e pretende alterar os mesmos?<br>
            <br>Nome do subitem: $nomeEditado<br>
            Tipo de valor: $valorEditado<br>
            Item: $idEditado<br>
            Tipo do campo do formulário: $tipoDeCampoEditado<br>
            Tipo de unidade: $tipoUnidadeEditado<br>
            Ordem do campo no formulário: $ordemDoCampoEditado<br>
            Obrigatório: $obrigatorioEditado<br><br>
            ";

            echo"
                <form action='' method='post' >
                <input type = 'hidden' name = 'subitem_editado' value='".$nomeEditado."'>
                <input type = 'hidden' name = 'valor_editado' value='".$valorEditado."'>
                <input type = 'hidden' name = 'id_editado' value='".$idEditado."'>
                <input type = 'hidden' name = 'tipodocampo_editado' value='".$tipoDeCampoEditado."'>
                <input type = 'hidden' name = 'tipounidade_editado' value='".$tipoUnidadeEditado."'>
                <input type = 'hidden' name = 'ordemcampo_editado' value='".$ordemDoCampoEditado."'>
                <input type = 'hidden' name = 'obrigatorio_editado' value='".$obrigatorioEditado."'>
                ".button_voltar()."ou <br>
                <input type = 'hidden' name = 'estado' value ='inserirSubitem'>
                <input type='submit' name='Submeter' value='Submeter'>
                </form> 
            ";
        }
    }//editar valores permitidos
    else if($_REQUEST["estado"] == "editarValorPerm"){

        echo "<h3>Edição - validação</h3>";

        $valorPermitidoEditado = $_POST["insercao_value"];
        $erro = 0;

        if(empty($valorPermitidoEditado)){
            $nomeErro = "<p> Valor é obrigatório </p>";
            echo $nomeErro;
            $erro++;
        }else{
            if (!preg_match("/^[a-zA-Z-'´`áéíóúàèìòùãõâêîôûÁÉÍÓÚÀÈÌÒÙÃÕÂÊÎÔÛÇç ]*$/",$valorPermitidoEditado)){
                $nomeErro= "<p> Nome: Apenas letras e espaços são permitidos </p>";
                echo $nomeErro;
                $erro++;
            }
        }

        if($erro>0){
            button_voltar();
        }else{
            echo "Estamos prestes a alterar estes itens. Confirma que os dados estão corretos e pretende alterar os mesmos?<br>
            <br>Valor: $valorPermitidoEditado<br><br>";

            echo"
            <form action='' method='post' >
            <input type = 'hidden' name = 'valorPermitido_editado' value='".$valorPermitidoEditado."'>
            ".button_voltar()."ou <br>
            <input type = 'hidden' name = 'estado' value ='inserirValorPermitido'>
            <input type='submit' name='Submeter' value='Submeter'>
            </form> 
            ";
        }
    
    }//inserir item
    else if($_REQUEST["estado"] == "inserirItem"){

        echo "<h3>Edição - inserção</h3>";

        $nome = $_POST["nome_editado"];
        $tipo = $_POST["tipo_editado"];

        $updateNome = "UPDATE item SET name ='$nome', item_type_id = '$tipo' WHERE item.id ='".$_SESSION["id_item"]."' ";
        $resultNome = mysqli_query($link,$updateNome);

        if($resultNome){
            echo"
                <p style='color:green;'>Alterou os dados com sucesso.</p>
            ";
        }else{
            echo"<p style='color:red;'>Ocorreu um erro ao inserir os dados</p>";
            button_voltar();
        }
    }//inserir unidade
    else if($_REQUEST["estado"] == "inserirUnidade"){

        echo "<h3>Edição - inserção</h3>";

        $nome = $_POST["unidade_editada"];

        $updateUnidade = "UPDATE subitem_unit_type SET name ='$nome' WHERE subitem_unit_type.id ='".$_SESSION["id_unidade"]."' ";
        $resultUnidade = mysqli_query($link,$updateUnidade);

        if($resultUnidade){
            echo"
            <p style='color:green;'>Alterou os dados com sucesso.</p>
        ";
        }else{
            echo"<p style='color:red;'>Ocorreu um erro ao inserir os dados</p>";
            button_voltar();
        }
    }//inserir subitens
    else if($_REQUEST["estado"] == "inserirSubitem"){
        
        echo "<h3>Edição - inserção</h3>";

        $nome = $_POST["subitem_editado"];
        $valor = $_POST["valor_editado"];
        $id = $_POST["id_editado"];
        $tipoCampo = $_POST["tipodocampo_editado"];
        $unidade = $_POST["tipounidade_editado"];
        $ordemCampo = $_POST["ordemcampo_editado"];
        $obrigatorio = $_POST["obrigatorio_editado"];

        $updateSubitem = "UPDATE subitem SET `name` ='$nome', item_id = '$id', value_type = '$valor', form_field_name = '$tipoCampo', unit_type_id = '$unidade', form_field_order = '$ordemCampo', mandatory = '$obrigatorio' WHERE subitem.id ='".$_SESSION["id_subitens"]."' ";
        $resultSubitem = mysqli_query($link,$updateSubitem);

        if($resultSubitem){
            echo"
            <p style='color:green;'>Alterou os dados com sucesso.</p>
        ";
        }else{
            echo"<p style='color:red;'>Ocorreu um erro ao inserir os dados</p>";
            button_voltar();
        }
    }//inserir valores permitidos
    else if($_REQUEST["estado"] == "inserirValorPermitido"){
        
        echo "<h3>Edição - inserção</h3>";

        $valorPermitido = $_POST["valorPermitido_editado"];

        $updateValorPermitido = "UPDATE subitem_allowed_value SET `value` = '$valorPermitido' WHERE subitem_allowed_value.id = '".$_SESSION["id_permitidos"]."'";
        $resultValorPermitido = mysqli_query($link,$updateValorPermitido);

        if($resultValorPermitido){
            echo"
            <p style='color:green;'>Alterou os dados com sucesso.</p>
        ";
        }else{
            echo"<p style='color:red;'>Ocorreu um erro ao inserir os dados</p>";
            button_voltar();
        }
    }//apagar 
    else if($_REQUEST["estado"] == "apagar"){

        //apagar item
        if($_REQUEST["tabela"] == "item"){

            $_SESSION["apagar_item"] = $_REQUEST["id"];

            echo"<h3> Apagar dados da tabela item </h3>
                Tem a certeza que quer apagar o dado selecionado da tabela permanentemente?<br>
                <form action='' method='post'>
                <input type = 'hidden' name = 'estado' value ='apagarItem'>
                <input type='submit' name='Submeter' value='Apagar'>
                </form>
                ";
            
        }//apagar unidades
        else if($_REQUEST["tabela"] == "unidades"){

            $_SESSION["apagar_unidade"] = $_REQUEST["id"];

            echo"<h3> Apagar dados da tabela unidades </h3>
                Tem a certeza que quer apagar o dado selecionado da tabela permanentemente?<br>
                <form action='' method='post' >
                <input type = 'hidden' name = 'estado' value ='apagarUnidade'>
                <input type='submit' name='Submeter' value='Apagar'>
                </form>
                ";
        }//apagar subitens
        else if($_REQUEST["tabela"] == "subitens"){

            $_SESSION["apagar_subitem"]  = $_REQUEST["id"];

            echo"<h3> Apagar dados da tabela subitens </h3>
                Tem a certeza que quer apagar o dado selecionado da tabela permanentemente?<br>
                <form action='' method='post' >
                <input type = 'hidden' name = 'estado' value ='apagarSubitem'>
                <input type='submit' name='Submeter' value='Apagar'>
                </form>
                ";
        }//apagar valores permitidos
        else if($_REQUEST["tabela"] == "valorPermitido"){

            $_SESSION["apagar_valorPermitido"] = $_REQUEST["id"];

            echo"<h3> Apagar dados da tabela valores permitidos </h3>
                Tem a certeza que quer apagar o dado selecionado da tabela permanentemente?<br>
                <form action='' method='post'>
                <input type = 'hidden' name = 'estado' value ='apagarValorPermitido'>
                <input type='submit' name='Submeter' value='Apagar'>
                </form>
                ";
        }

    }//item
    else if($_REQUEST["estado"] == "apagarItem"){

        $apagarItem = "DELETE FROM item WHERE item.id = '".$_SESSION["apagar_item"]."'";
        $resultApagarItem = mysqli_query($link,$apagarItem);
        echo $resultApagarItem;

        if($resultApagarItem){
            echo "<p style='color:green;'>Apagou os dados com sucesso </p>";
        }else{
            echo"<p style='color:red;'>Ocorreu um erro ao eleminar os dados, pode estar a tentar apagar uma chave estrangeira de outra tabela</p>";
            button_voltar();
        }

    }//unidade
    else if($_REQUEST["estado"] == "apagarUnidade"){

        $apagarUnidade = "DELETE FROM unit_type WHERE unit_type.id = '".$_SESSION["apagar_unidade"]."'";
        $resultApagarUnidade = mysqli_query($link,$apagarUnidade);

        if($resultApagarUnidade){
            echo "<p style='color:green;'>Apagou os dados com sucesso </p>";
        }else{
            echo"<p style='color:red;'>Ocorreu um erro ao eleminar os dados, pode estar a tentar apagar uma chave estrangeira de outra tabela</p>";
            button_voltar();
        }

    }//subitem
    else if($_REQUEST["estado"] == "apagarSubitem"){

        $apagarSubitem = "DELETE FROM subitem WHERE subitem.id = '".$_SESSION["apagar_subitem"]."'";
        $resultApagarSubitem = mysqli_query($link,$apagarSubitem);

        if($resultApagarSubitem){
            echo "<p style='color:green;'>Apagou os dados com sucesso </p>";
        }else{
            echo"<p style='color:red;'>Ocorreu um erro ao eleminar os dados, pode estar a tentar apagar uma chave estrangeira de outra tabela</p>";
            button_voltar();
        }

    }//valor permitido
    else if($_REQUEST["estado"] == "apagarValorPermitido"){

        $apagarValorPermitido = "DELETE FROM subitem_allowed_value WHERE subitem_allowed_value.id = '".$_SESSION["apagar_valorPermitido"]."'";
        $resultApagarValorPermitido = mysqli_query($link,$apagarValorPermitido);

        if($resultApagarValorPermitido){
            echo "<p style='color:green;'>Apagou os dados com sucesso </p>";
        }else{
            echo"<p style='color:red;'>Ocorreu um erro ao eleminar os dados, pode estar a tentar apagar uma chave estrangeira de outra tabela</p>";
            button_voltar();
        }

    }// ativar
    else if($_REQUEST["estado"] == "ativar"){

        if($_REQUEST["tabela"] == "item"){

            $_SESSION["ativar_item"] = $_REQUEST["id"];

            echo "<h3> Ativar dados da tabela item</h3>
                Deseja ativar o seguinte item?<br>
                <form action='' method='post'>
                <input type = 'hidden' name = 'estado' value ='ativarItem'>
                <input type='submit' name='Submeter' value='Ativar'>
                </form>
            ";

        }
        else if($_REQUEST["tabela"] == "valoresPermitidos"){
            
            $_SESSION["ativar_valorPermitido"] = $_REQUEST["id"];

            echo "<h3> Ativar dados da tabela valores permitidos</h3>
                Deseja ativar o seguinte valor?<br>
                <form action='' method='post'>
                <input type = 'hidden' name = 'estado' value ='ativarValorPermitido'>
                <input type='submit' name='Submeter' value='Ativar'>
                </form>
            ";
        }
    }//item
    else if($_REQUEST["estado"] == "ativarItem"){

        $ativarItem = "UPDATE item SET `state` = 'active' WHERE item.id = '".$_SESSION["ativar_item"]."'";
        $resultAtivarItem = mysqli_query($link,$ativarItem);

        if($resultAtivarItem){
            echo "<p style='color:green;'>O item passou a estar ativo </p>";
        }else{
            echo"<p style='color:red;'>Ocorreu um erro ao tentar ativar o item</p>";
            button_voltar();
        }
    }//valores permitidos
    else if($_REQUEST["estado"] == "ativarValorPermitido"){
        
        $ativarValorPermitido = "UPDATE subitem_allowed_value SET `state` = 'active' WHERE subitem_allowed_value.id = '".$_SESSION["ativar_valorPermitido"]."'";
        $resultValorPermitido = mysqli_query($link,$ativarValorPermitido);

        if($resultValorPermitido){
            echo "<p style='color:green;'>O valor passou a estar ativo </p>";
        }else{
            echo"<p style='color:red;'>Ocorreu um erro ao tentar ativar o valor</p>";
            button_voltar();
        }
    }//desativar
    else if($_REQUEST["estado"] == "desativar"){

        //item
        if($_REQUEST["tabela"] == "item"){

            $_SESSION["desativar_item"] = $_REQUEST["id"];

            echo "<h3> Desativar dados da tabela item</h3>
                Deseja desativar o seguinte item?<br>
                <form action='' method='post'>
                <input type = 'hidden' name = 'estado' value ='desativarItem'>
                <input type='submit' name='Submeter' value='Desativar'>
                </form>
            ";

        }//subitem
        else if($_REQUEST["tabela"] == "subitem"){
            
            $_SESSION["desativar_subitem"] = $_REQUEST["id"];

            echo "<h3> Desativar dados da tabela subitem</h3>
                Deseja desativar o seguinte subitem?<br>
                <form action='' method='post'>
                <input type = 'hidden' name = 'estado' value ='desativarSubitem'>
                <input type='submit' name='Submeter' value='Desativar'>
                </form>
            ";
        }//valores permitidos
        else if($_REQUEST["tabela"] == "valoresPermitidos"){
            
            $_SESSION["desativar_valorPermitido"] = $_REQUEST["id"];

            echo "<h3> Desativar dados da tabela valores permitidos</h3>
                Deseja desativar o seguinte valor?<br>
                <form action='' method='post'>
                <input type = 'hidden' name = 'estado' value ='desativarValor'>
                <input type='submit' name='Submeter' value='Desativar'>
                </form>
            ";
        }
    }//desativar item
    else if($_REQUEST["estado"] == "desativarItem"){
        
        $desativarItem = "UPDATE item SET `state` = 'inactive' WHERE item.id = '".$_SESSION["desativar_item"]."'";
        $resultDesativarItem = mysqli_query($link,$desativarItem);

        if($resultDesativarItem){
            echo "<p style='color:green;'>O item passou a estar desativado </p>";
        }else{
            echo"<p style='color:red;'>Ocorreu um erro ao tentar desativar o item</p>";
            button_voltar();
        }
    }//desativar subitem
    else if($_REQUEST["estado"] == "desativarSubitem"){

        $desativarSubitem = "UPDATE subitem SET `state` = 'inactive' WHERE subitem.id = '".$_SESSION["desativar_subitem"]."'";
        $resultDesativarSubitem = mysqli_query($link,$desativarSubitem);

        if($resultDesativarSubitem){
            echo "<p style='color:green;'>O subitem passou a estar desativado </p>";
        }else{
            echo"<p style='color:red;'>Ocorreu um erro ao tentar desativar o subitem</p>";
            button_voltar();
        }
    }//desativar valor permitido
    else if($_REQUEST["estado"] == "desativarValor"){

        $desativarValor = "UPDATE subitem_allowed_value SET `state` = 'inactive' WHERE subitem_allowed_value.id = '".$_SESSION["desativar_valorPermitido"]."'";
        $resultDesativarValor = mysqli_query($link,$desativarValor);

        if($resultDesativarValor){
            echo "<p style='color:green;'>O valor passou a estar desativado </p>";
        }else{
            echo"<p style='color:red;'>Ocorreu um erro ao tentar desativar o valor</p>";
            button_voltar();
        }
    }
}else{
echo "<p>Não tem autorização para aceder a esta página</p>";
button_voltar();
}

?>