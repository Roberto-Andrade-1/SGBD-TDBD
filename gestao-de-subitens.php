<?php

require_once("custom/php/common.php");

if (is_user_logged_in() && current_user_can("manage_subitems")) {

    if (!isset($_REQUEST["estado"])) {

        $queryAllSubitem = "SELECT * FROM subitem";
        $resultAllSubitem = mysqli_query($link, $queryAllSubitem);
        $numRows = mysqli_num_rows($resultAllSubitem);

        if ($numRows == 0) {
            echo "<p style='color:red;'>Não há subitens especificados</p>";
        } else {
            echo
            "<table>
                    <thead>
                        <tr>
                            <th>item</th>
                            <th>id</th>
                            <th>subitem</th>
                            <th>tipo de valor</th>
                            <th>nome do campo no formulário</th>
                            <th>tipo do campo no formulário</th>
                            <th>tipo de unidade</th>
                            <th>ordem do campo no formulário</th>
                            <th>obrigatório</th>
                            <th>estado</th>
                            <th>ação</th>
                        </tr>
                    </thead>
                    <tbody>";

            $queryItems = "SELECT * FROM item ORDER BY item.name ASC";
            $resulItems = mysqli_query($link, $queryItems);

            while ($rowItem = mysqli_fetch_assoc($resulItems)) {

                $nomeItem = $rowItem['name'];
                $idItem = $rowItem['id'];

                $querySubitemItemId = "SELECT * FROM subitem WHERE item_id=$idItem ORDER BY subitem.name ASC";
                $resultSubitemItemId = mysqli_query($link, $querySubitemItemId);
                $numRowSpan = mysqli_num_rows($resultSubitemItemId);


                if ($numRowSpan > 0) {

                    $first = true;
                    echo "<tr>
                            <td rowspan=$numRowSpan>$nomeItem</td>";

                    while ($rowSubitemItemId = mysqli_fetch_assoc($resultSubitemItemId)) {

                        if ($rowSubitemItemId["unit_type_id"] != NULL) {
                            $queryTipoUnidade = "SELECT * FROM subitem_unit_type WHERE id=" . $rowSubitemItemId["unit_type_id"] . "";
                            $resultTipoUnidade = mysqli_query($link, $queryTipoUnidade);
                            $rowTipoUnidade = mysqli_fetch_assoc($resultTipoUnidade);
                            $unidade = $rowTipoUnidade["name"];
                        } else {
                            $unidade = "-";
                        }

                        if ($rowSubitemItemId["mandatory"] == 1) {
                            $obrigatorio = "sim";
                        } else {
                            $obrigatorio = "não";
                        }

                        if ($rowSubitemItemId["state"] == "active") {
                            $ativ = "ativo";
                        } else {
                            $ativ = "inativo";
                        }

                        $imprimeDadosTabela = "<td>" . $rowSubitemItemId["id"] . "</td>
                                                    <td>" . $rowSubitemItemId["name"] . "</td>
                                                    <td>" . $rowSubitemItemId["value_type"] . "</td>
                                                    <td>" . $rowSubitemItemId["form_field_name"] . "</td>
                                                    <td>" . $rowSubitemItemId["form_field_type"] . "</td>
                                                    <td>" . $unidade . "</td>
                                                    <td>" . $rowSubitemItemId["form_field_order"] . "</td>
                                                    <td>" . $obrigatorio . "</td>
                                                    <td>" . $ativ . "</td>
                                                    <td>[<a href='edicao-de-dados?estado=editar&id=".$rowSubitemItemId["id"]."&tabela=subitens' >editar</a>]<br>[<a href='edicao-de-dados?estado=desativar&id=".$rowSubitemItemId["id"]."&tabela=subitens' >desativar</a>]<br>[<a href='edicao-de-dados?estado=apagar&id=".$rowSubitemItemId["id"]."&tabela=subitens' >apagar</a>]</td>";

                        if ($first) {
                            echo $imprimeDadosTabela;
                            $first = false;
                        } else {
                            echo "<tr>" . $imprimeDadosTabela;
                        }
                        echo "</tr>";
                    }
                } else {
                    echo "<tr>
                            <td>$nomeItem</td>
                            <td colspan=10>
                                <p style='text-align:center'>este item não tem subitems </p>
                            </td>
                        </tr>";
                }
            }
        }
        echo "</tbody>
        </table>";

        echo "<h3>Gestão de subitems - introdução</h3>
            <form action='' method='POST'>
                <label>Nome do subitem: (Obrigatorio)</label>
                    <input type='text' name='nomeNovoSubitem' >
                <br><br><label>Tipo de valor:</label><br>";

        foreach (get_enum_values($link, 'subitem', 'value_type') as $tipoDeValor) {
            echo "<input type='radio' name='tipoValor' value=$tipoDeValor >
                        <label>$tipoDeValor</label><br>";
        }

        echo "<br><label>Item:</label>
                        <select name='idItems' id='idItems' >
                            <option value=''></option>";
        $queryItemName = "SELECT * FROM item ORDER BY item.name ASC";
        $resultitemName = mysqli_query($link, $queryItemName);
        while ($itemName = mysqli_fetch_assoc($resultitemName)) {
            echo "<option value=" . $itemName['id'] . ">" . $itemName['name'] . "</option>";
        }
        echo "</select>";

        echo "<br><br><label>Tipo do campo do formulário:</label><br>";
        foreach (get_enum_values($link, 'subitem', 'form_field_type') as $tipoDeCampoDoFormulario) {
            echo "<input type='radio' name='tipoDeCampoDoFormulario' value=$tipoDeCampoDoFormulario >
                        <label>$tipoDeCampoDoFormulario</label><br>";
        }

        echo "<br><label>Tipo de unidade:</label><br>
                        <select name='tipoUnidade' id='tipoUnidade'>
                            <option value=''></option>";

        $querySubitemUnidade = "SELECT * FROM subitem_unit_type";
        $resultSubitemUnidade = mysqli_query($link, $querySubitemUnidade);
        while ($tipoDeUnidade = mysqli_fetch_assoc($resultSubitemUnidade)) {
            echo "<option value=" . $tipoDeUnidade["id"] . ">" . $tipoDeUnidade["name"] . "</option>";
        }

        echo "</select>";

        echo "<br><br><label>Ordem do campo no formulário</label>
                        <input type='text' name='ordemDoCampoDoForm'>";

        echo "<br><br><label>Obrigatório</label><br>
                        <input type='radio' name='obrigatorio' value='1' >
                            <label>sim</label><br>
                        <input type='radio' name='obrigatorio' value='0'>
                            <label>não</label>";

        echo "<br>
                    <input type='hidden' name='estado' value='inserir'>
                    <br><input type='submit' value='Inserir subitem'>";

        "</form>";
    } elseif ($_REQUEST["estado"] == "inserir") {

        echo "<h3>Gestão de subitens - inserção</h3>";

        $erro = false;

        if (preg_match('/[^a-z]/', $_POST['nomeNovoSubitem']) || is_numeric($_POST['nomeNovoSubitem'])) {
            echo "<p style='color:red;'>Nome do subitem ' " . $_POST['nomeNovoSubitem'] . " ' não é valido</p>";
            $erro = true;
        }

        if (empty($_POST['nomeNovoSubitem'])) {
            echo "<p style='color:red;'>É obrigatório inserir um nome para o subitem</p>";
        }

        if (empty($_POST['tipoValor'])) {
            echo "<p style='color:red;'>Escolher o tipo de valor é obrigatório</p>";
            $erro = true;
        }

        if (empty($_POST['idItems'])) {
            echo "<p style='color:red;'>A seleção de um item é obrigatório</p>";
            $erro = true;
        }

        if (empty($_POST['tipoDeCampoDoFormulario'])) {
            echo "<p style='color:red;'>É necessário escolher uma das opções do formulário</p>";
            $erro = true;
        }

        if (empty($_POST['ordemDoCampoDoForm']) || $_POST['ordemDoCampoDoForm'] < 1 || !is_numeric($_POST['ordemDoCampoDoForm'])) {
            echo "<p style='color:red;'>É necessário inserir um número maior que 0 na ordem do campo no formulário</p>";
            $erro = true;
        }

        if (empty($_POST['obrigatorio'])) {
            echo "<p style='color:red;'>É necessário escolher uma das opções do campo 'Obrigatório'</p>";
            $erro = true;
        }

        switch ($erro) {
            case true:
                button_voltar();
                break;
            default:
                if ($_POST['tipoUnidade'] === "") $tipoDeUnidade = 'NULL';
                else $tipoDeUnidade = "'" . $_POST['tipoUnidade'] . "'";

                $queryInserir = "INSERT INTO subitem(id, name, item_id, value_type, form_field_name, form_field_type, unit_type_id, form_field_order, mandatory, state) 
                                VALUES(
                                    NULL,
                                    '" . $_POST['nomeNovoSubitem'] . "',
                                    '" . $_POST['idItems'] . "',
                                    '" . $_POST['tipoValor'] . "',
                                    '',
                                    '" . $_POST['tipoDeCampoDoFormulario'] . "',
                                    " . $tipoDeUnidade . ",
                                    '" . $_POST['ordemDoCampoDoForm'] . "',
                                    '" . $_POST['obrigatorio'] . "',
                                    'active'
                                    )";
                $resultInserir = mysqli_query($link, $queryInserir);
                $idAtual = mysqli_insert_id($link);

                $queryItemConcat = "SELECT name FROM item WHERE id=" . $_POST['idItems'] . "";
                $resultItemConcat = mysqli_query($link, $queryItemConcat);
                $nomeItemConcat = mysqli_fetch_assoc($resultItemConcat);

                $nomeSemEspacos = preg_replace('/ /', '_', $_POST['nomeNovoSubitem']);
                $verificaNome = preg_replace('/[^a-z0-9_ ]/i', '', $nomeSemEspacos);
                $formFieldName = substr($nomeItemConcat['name'], 0, 3) . '-' . $idAtual . '-' . $verificaNome;

                $queryUpdate = "UPDATE subitem SET form_field_name='$formFieldName' WHERE id='$idAtual'";
                $resultUpdate = mysqli_query($link, $queryUpdate);

                if ($resultUpdate) {
                    echo "
                        <p style='color:green;'>Inseriu os dados de novo subitem com sucesso</p>
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
