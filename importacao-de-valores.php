<?php 

require_once("custom/php/common.php");
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;

if (!$link) {
    echo "Error: Não é possível conectar á base de dados: " . mysqli_connect_error();
}
else if(is_user_logged_in() && current_user_can("values_import")){
    if(!isset($_REQUEST["estado"])){

        echo "<table class='tabelaS'>
            <tr>
                <th>Nome</th>
                <th>Data de Nascimento</th>
                <th>Enc. de educação</th>
                <th>Telefone do Enc.</th>
                <th>e-mail</th>
            </tr>
            ";

                $queryCrianças = "SELECT * 
                FROM child 
                ORDER BY child.name";
                $resultCrianças = mysqli_query($link, $queryCrianças);

                if ($resultCrianças === false) {
                    echo "Error: Não é possível executar a query: " . mysqli_error($link);
                }
                else if(mysqli_num_rows($resultCrianças)>0){
                    while($rowCrianças = mysqli_fetch_assoc($resultCrianças)){

                        $pagina="importacao-de-valores?estado=escolheritem&crianca=".$rowCrianças["id"]."";

                        echo "<tr><td><a href='$pagina'>" .$rowCrianças["name"]. "</a></td>";
                        echo "<td>" .$rowCrianças["birth_date"]. "</td>";
                        echo "<td>" .$rowCrianças["tutor_name"]. "</td>";
                        echo "<td>" .$rowCrianças["tutor_phone"]. "</td>";
                        echo "<td>" .$rowCrianças["tutor_email"]. "</td>";
                    }
                }
                else{
                    echo "<p> Não há crianças </p>";
                }

        echo "</table>";

        button_voltar();

    }
    else if ($_REQUEST["estado"] == "escolheritem"){

        echo "<ul>";

        $queryTipos = "SELECT item_type.id, item_type.name 
        FROM item_type"; //Query dos tipos de itens
        $resultTipos = mysqli_query($link, $queryTipos);

        if ($resultTipos === false) {
            echo "Error: Não é possível executar a query: " . mysqli_error($link);
        }
        else{
            while($rowTipos = mysqli_fetch_assoc($resultTipos)){

                echo "<li>".$rowTipos["name"]."</li><ul>";

                $queryItens = "SELECT item.id, item.name
                FROM item, item_type 
                WHERE item_type.id = item.item_type_id AND item.item_type_id =" .$rowTipos['id']. ""; //Query dos itens do tipo de item em questão
                $resultItens = mysqli_query($link, $queryItens);

                while($rowItens = mysqli_fetch_assoc($resultItens)){

                    $querySubitens = "SELECT * 
                    FROM subitem, item 
                    WHERE item.id = subitem.item_id AND subitem.item_id = " .$rowItens["id"]. ""; //Query de todos os subitens do item em questão
                    $resultSubitens = mysqli_query($link, $querySubitens);

                    $pagina = "importacao-de-valores?estado=introducao&crianca=".$_REQUEST["crianca"]."&item=".$rowItens["id"]."";

                    if(mysqli_num_rows($resultSubitens)){

                        echo "<li>[<a href='$pagina'>".$rowItens["name"]."</a>]</li>";

                    }

                }

                echo "</ul>";

            }

            echo "</ul>";
        }

        button_voltar();

    }
    else if ($_REQUEST["estado"] == "introducao"){

        $querySubitens = "SELECT subitem.form_field_name, subitem.id, subitem.value_type, subitem.mandatory
        FROM subitem, item 
        WHERE item.id = subitem.item_id AND subitem.item_id = " .$_REQUEST["item"]. ""; //Query de todos os subitens do item em questão
        $resultSubitens = mysqli_query($link, $querySubitens);
        $resultSubitens2 = mysqli_query($link, $querySubitens);
        $resultSubitens3 = mysqli_query($link, $querySubitens);
        $resultSubitens4 = mysqli_query($link, $querySubitens);

        if ($resultSubitens === false || $resultSubitens2 === false || $resultSubitens3 === false || $resultSubitens4 === false) {
            echo "Error: Não é possível executar a query: " . mysqli_error($link);
        }
        else{
            echo "<table><tr>";

            while($rowSubitens = mysqli_fetch_assoc($resultSubitens)){

                if($rowSubitens["value_type"] == "enum"){
                    
                    $queryPermitidos = "SELECT * 
                    FROM subitem_allowed_value, subitem 
                    WHERE subitem.id = " .$rowSubitens["id"]. " AND subitem_allowed_value.subitem_id = subitem.id"; // Querry dos dos valores "allowed" do subitem em questão
                    $resultPermitidos = mysqli_query($link, $queryPermitidos);

                    if ($resultPermitidos === false) {
                        echo "Error: Não é possível executar a query: " . mysqli_error($link);
                    }
                    else{
                        $x = mysqli_num_rows($resultPermitidos);

                        while($x > 0){

                            echo "<td>".$rowSubitens["form_field_name"]."";
                            $x--;

                        }
                    }

                }
                else{

                    echo "<td>".$rowSubitens["form_field_name"]."</td>";

                }

            }

            echo "</tr><tr>";

            while($rowSubitens2 = mysqli_fetch_assoc($resultSubitens2)){

                if($rowSubitens2["value_type"] == "enum"){
                    
                    $queryPermitidos2 = "SELECT * 
                    FROM subitem_allowed_value, subitem 
                    WHERE subitem.id = " .$rowSubitens2["id"]. " AND subitem_allowed_value.subitem_id = subitem.id"; // Querry dos dos valores "allowed" do subitem em questão
                    $resultPermitidos2 = mysqli_query($link, $queryPermitidos2);

                    if ($resultPermitidos2 === false) {
                        echo "Error: Não é possível executar a query: " . mysqli_error($link);
                    }
                    else{
                        $x = mysqli_num_rows($resultPermitidos2);

                        while($x > 0){

                            echo "<td>".$rowSubitens2["id"]."</td>";
                            $x--;

                        }
                    }

                }
                else{

                    echo "<td>".$rowSubitens2["id"]."</td>";

                }

            }

            echo "</tr><tr>";

            while($rowSubitens3 = mysqli_fetch_assoc($resultSubitens3)){

                if($rowSubitens3["value_type"] == "enum"){

                    $queryPermitidos3 = "SELECT subitem_allowed_value.value
                    FROM subitem_allowed_value, subitem 
                    WHERE subitem.id = " .$rowSubitens3["id"]. " AND subitem_allowed_value.subitem_id = subitem.id"; // Querry dos dos valores "allowed" do subitem em questão
                    $resultPermitidos3 = mysqli_query($link, $queryPermitidos3);

                    if ($resultPermitidos3 === false) {
                        echo "Error: Não é possível executar a query: " . mysqli_error($link);
                    }
                    else{
                        while($rowPermitidos3 = mysqli_fetch_assoc($resultPermitidos3)){

                            echo "<td>".$rowPermitidos3["value"]."</td>";

                        }
                    }
                }
                else{

                    echo "<td></td>";

                }
            }

            echo "</tr></table>";

            echo "Deverá copiar estas linhas para um ficheiro excel e introduzir os valores a importar <br>
            No caso dos subitens de 'value_type' igual a 'enum', deverá constar um 0 quando esse valor permitido não se aplique à instância em causa e um 1 quando esse valor se aplica";

            echo "
            <body>
            <form action='' method='POST' enctype='multipart/form-data'>
            <label>Upload:</label>
            <input type='file' name='exel'>
            <br>
            <input type='hidden' name='crianca' value=".$_REQUEST["crianca"].">
            <input type='hidden' name='estado' value='insercao'><br>
            <input type='submit' name='submeter_exel' value='Upload do ficheiro excel'>
            </form>
            </body><br>";
        }

        button_voltar();

    }
    else if ($_REQUEST["estado"] == "insercao"){

        $crianca = $_POST['crianca'];

        if(isset($_POST["submeter_exel"])){

            $file_name = $_FILES["exel"]["name"];
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

            $allowed_ext = ["xls","csv","xlsx"];

            if(in_array($file_ext,$allowed_ext)){

                $inputFileNamePath = $_FILES["exel"]["tmp_name"];;
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileNamePath);

                $worksheet = $spreadsheet->getActiveSheet();

                foreach ($worksheet->getRowIterator() as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(FALSE); 
                    $size = $row->getRowIndex();

                    if($size >= 4){

                        foreach ($cellIterator as $cell) {

                            $coluna = $cell->getColumn();
                            $idSub = $worksheet->getCell(''.$coluna.'2')->getValue();

                            if($cell->getValue() == 1){

                                $valor = $worksheet->getCell(''.$coluna.'3')->getValue();

                                $queryInserir1 = "INSERT INTO `value` (id,child_id,subitem_id,value,date,time,producer) 
                                VALUES (NULL,
                                '$crianca',
                                '$idSub',
                                '$valor',
                                '".date('Y-m-d')."',
                                '".date('H:i:s')."',
                                '".get_current_user()."')";
                                $resultInserir1 = mysqli_query($link,$queryInserir1);

                                if($resultInserir1){
                                    $inseriu = true;
                                    echo "<p style='color:green;'>Inseriu o valor ".$valor." na base de dados</p>";
                                }
                                else{
                                    $inseriu = false;
                                    echo "Error: Não é possível executar a query: " . mysqli_error($link);
                                }
                            }
                            else if ($cell->getValue()!=1 && $cell->getValue()!=0){

                                $queryInserir2 = "INSERT INTO `value` (id,child_id,subitem_id,value,date,time,producer) 
                                VALUES (NULL,
                                '$crianca',
                                '$idSub',
                                '".$cell->getValue()."', 
                                '".date('Y-m-d')."',
                                '".date('H:i:s')."',
                                '".get_current_user()."')";
                                $resultInserir2 = mysqli_query($link,$queryInserir2);

                                if($resultInserir2){
                                    $inseriu = true;
                                    echo "<p style='color:green;'>Inseriu o valor ".$cell->getValue()." na base de dados</p>";
                                }
                                else{
                                    $inseriu = false;
                                    echo "Error: Não é possível executar a query: " . mysqli_error($link);
                                }
                            }
                        }

                    }

                }
                if($inseriu){
                    echo "<p>Clique em <a href='$current_page'>Continuar</a> para avançar<br>";
                }
                else{
                    echo "<p style='color:red;'>Ocorreu um erro ao acessar os dados ou ficheiro exel incompleto ou mal construído</p>";
                }
            }
            else{
                echo "\n <h1>Ficheiro inválido</h1>";
            }
        }
        else{

            echo "<p style='color:red;'>Ocorreu um erro ao mudar de página</p>";

        }

        button_voltar();

    }
}
else{
    echo "\n <h1>Não tem autorização para aceder a esta página</h1>";
    button_voltar();
}

?>