<?php

require_once("custom/php/common.php");

if (is_user_logged_in() && current_user_can("manage_unit_types")) {

    if (!isset($_REQUEST["estado"])) {

        $query = "SELECT * FROM subitem_unit_type";
        $result = mysqli_query($link, $query);
        $numRow = mysqli_num_rows($result);

        if ($numRow == 0) {
            echo "Não há tipos de unidades<br>";
        } else {
            echo "<table>
                    <thead>
                        <tr>
                            <th> Id </th>
                            <th> Unidade </th>
                            <th> Subitem </th>
                            <th> ação </th>
                        </tr>
                    </thead>
                <tbody>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>" . $row["id"] . "</td>
                        <td>" . $row["name"] . "</td>
                        <td>";

                $query1 = "SELECT name , item_id FROM subitem WHERE unit_type_id= " . $row["id"] . "";
                $result1 = mysqli_query($link, $query1);

                if (mysqli_num_rows($result1) == 0) {
                    echo "Não há subitems para estas unidades";
                } else {
                    $i = 0;
                    while ($rowSubitem = mysqli_fetch_assoc($result1)) {
                        echo $rowSubitem["name"] . " (";

                        $query2 = "SELECT name FROM item WHERE id=" . $rowSubitem["item_id"] . "";
                        $result2 = mysqli_query($link, $query2);

                        $rowSubitemItem = mysqli_fetch_assoc($result2);
                        echo $rowSubitemItem["name"] . ")";
                        $i++;
                        if ($i < mysqli_num_rows($result1))
                            echo ", ";
                    }
                }
                echo "</td>
                        <td>[<a href='edicao-de-dados?estado=editar&id=".$row["id"]."&tabela=unidades' >editar</a>][<a href='edicao-de-dados?estado=apagar&id=".$row["id"]."&tabela=unidades' >apagar</a>]</td>  
                </tr>";
            }
        }
        echo "</tbody>
        </table>";

        echo
        "<h3>Gestão de unidades - introdução</h3>
            <form action='' method='POST'>
                Nome da unidade:<input type='text' name=nome_unidade required><br>
                <input type='hidden' name='estado' value='inserir'>
                <input type='submit' value='Inserir tipo de unidade'>
            </form>";
    } elseif ($_REQUEST["estado"] == "inserir") {

        echo
        "<h3>Gestão de unidades - inserção</h3>";
        //trim retira os espaços no final e começo
        $novaUnidade = trim($_POST["nome_unidade"]);


        $query = "SELECT * FROM subitem_unit_type";
        $result = mysqli_query($link, $query);

        $erro = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            //strcasecmp se as duas var forem iguais mesmo contando com as maisculas e minuscalas 
            if (strcasecmp($row['name'], $novaUnidade) == 0 && $erro == 0) {
                echo "<p style='color:red;'>Esta unidade já existe na base de dados</p>";
                $erro = 1;
                button_voltar();
            }
        }
        if (preg_match('/[^a-z]/i', $novaUnidade) || empty($novaUnidade)) {
            echo "<p style='color:red;'>Nome da unidade inválido</p>";
            $erro = 1;
            button_voltar();
        } elseif ($erro == 0) {

            $queryInsert = "INSERT INTO subitem_unit_type(id,name) VALUES (NULL,'$novaUnidade')";
            $resultInsert = mysqli_query($link, $queryInsert);

            if ($resultInsert) {
                echo "
                    <p style='color:green;'>Inseriu os dados de novo tipo de unidade com sucesso</p>
                    <p>Clique em <a href='$current_page'>Continuar</a> para avançar<br>";
            } else {
                echo "<p style='color:red;'>Ocorreu um erro ao acessar os dados</p>";
                button_voltar();
            }
        }
    }
} else {
    echo "<p style='color:red;'>Não tem autorização para aceder a esta página</p>";
    button_voltar();
}
