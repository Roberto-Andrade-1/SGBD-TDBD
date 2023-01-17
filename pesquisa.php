<?php

require_once("custom/php/common.php");

if (is_user_logged_in() && current_user_can("search")) {

    if (!isset($_REQUEST['estado'])) {
        echo "<h3>Pesquisa - escolher item</h3>";

        $nomeTipoItem = "SELECT * FROM item_type";

        $res_nti = mysqli_query($link, $nomeTipoItem);

        if (mysqli_num_rows($res_nti) > 0) {
            while ($row2 = mysqli_fetch_assoc($res_nti)) {
                $nomeItem = "SELECT * FROM item WHERE item_type_id=" . $row2["id"] . "";
                $res_ni = mysqli_query($link, $nomeItem);
                echo "<ul> 
                    <li>" . preg_replace('/_/i', ' ', $row2["name"]) . "</li>
                    <ul>";
                while ($row3 = mysqli_fetch_assoc($res_ni)) {
                    $url3 = "?estado=escolha&item=" . $row3["id"] . "";
                    echo "
                            <li> [<a href='$current_page.$url3' >" . $row3["name"] . "</a>]</li>
                    ";
                }
                echo "</ul> </ul>";
            }
        }
    } elseif ($_REQUEST['estado'] == 'escolha') {

        $queryNomeItem = "SELECT name FROM item WHERE id=" . $_REQUEST['item'] . "";
        $resultNomeItem = mysqli_query($link, $queryNomeItem);
        $nomeDoItem = mysqli_fetch_assoc($resultNomeItem);

        $_SESSION['id'] = $_REQUEST['item'];
        $_SESSION['nome_do_item'] = $nomeDoItem['name'];

        echo "<form action='' method='POST'>
                <table class='tabelaEscolha'>
                    <thead>
                        <tr>
                            <th>Atributo</th>
                            <th>Obter</th>
                            <th>Filtro</th>
                        </tr>
                    </thead>
                    <tbody>";
        $queryAtrib = "SHOW COLUMNS FROM child";
        $resultAtrib = mysqli_query($link, $queryAtrib);
        while ($atributos = mysqli_fetch_assoc($resultAtrib)) {
            echo "<tr>
                    <td>" . $atributos['Field'] . "</td>
                    <td><input type='checkbox' name='obter_atributo[]' value='" . $atributos['Field'] . "'></td>
                    <td><input type='checkbox' name='filtro_atributo[]' value='" . $atributos['Field'] . "'></td>
                </tr>";
        }
        echo    "</tbody>
                </table>
                <table class='tabelaEscolha'>
                    <thead>
                        <tr>
                            <th>Subitem</th>
                            <th>Obter</th>
                            <th>Filtro</th>
                        </tr>
                    </thead>
                    <tbody>";
        $querySubitemId = "SELECT id,name FROM subitem WHERE item_id=" . $_SESSION['id'] . "";
        $resultSubitemId = mysqli_query($link, $querySubitemId);
        while ($subitem = mysqli_fetch_assoc($resultSubitemId)) {
            echo "<tr>
                    <td>" . $subitem['name'] . "</td>
                    <td><input type='checkbox' name='obter_subitem[]' value='" . $subitem['id'] . "'></td>
                    <td><input type='checkbox' name='filtro_subitem[]' value='" . $subitem['id'] . "'></td>
                </tr>";
        }
        echo "</tbody>
                </table>
                <input type='hidden' name='estado' value='escolher_filtros'>
                <input type='submit' value='Submeter'>
            </form>";
        button_voltar();
    } elseif ($_REQUEST['estado'] == 'escolher_filtros') {

        if (!isset($_REQUEST['obter_atributo']))
            $_SESSION['nome_atributo_obter'] = array();
        else
            $_SESSION['nome_atributo_obter'] = $_REQUEST['obter_atributo'];

        if (!isset($_REQUEST['filtro_atributo']))
            $_SESSION['nome_atributo_filtro'] = array();
        else
            $_SESSION['nome_atributo_filtro'] = $_REQUEST['filtro_atributo'];

        if (!isset($_REQUEST['obter_subitem']))
            $_SESSION['id_subitem_obter'] = array();
        else
            $_SESSION['id_subitem_obter'] = $_REQUEST['obter_subitem'];

        if (!isset($_REQUEST['filtro_subitem']))
            $_SESSION['id_subitem_filtro'] = array();
        else
            $_SESSION['id_subitem_filtro'] = $_REQUEST['filtro_subitem'];


        //ordena alfabeticamnete
        sort($_SESSION['nome_atributo_obter']);
        sort($_SESSION['nome_atributo_filtro']);

        $operadores = array('>', '>=', '<', '<=', '=', '!=', 'LIKE');
        //                   0,   1,    2,   3,    4,   5,     6

        //formulario 
        echo "<form action='' method='POST'>

            <p><strong>Obrigatório(<span class='obrigatorio'>*</span>)</strong></p>
            <p class='pesquisaP'>Irá ser realizada uma pesquisa que irá obter, como resultado, uma listagem de, para cada criança, dos seguintes dados pessoais escolhidos:</p>

            <table class='tabelaInvisivel'>";

        foreach ($_SESSION['nome_atributo_obter'] as $nomeAtributo) {
            echo "<tr><td><li>$nomeAtributo</td>";
            if (in_array($nomeAtributo, $_SESSION['nome_atributo_filtro'])) {
                echo "<td><strong>Operador<span class='obrigatorio'>*</span></strong>
                            <div>
                                <select class='selectOperador' name='atributo_operador[]' >
                                <option value=''></option>";

                switch ($nomeAtributo) {
                    case 'id':
                    case 'birth_date':
                    case 'tutor_phone':
                        for ($i = 0; $i < 6; $i++) {
                            echo "<option value='" . $operadores[$i] . "'>$operadores[$i]</option>";
                        }
                        break;
                    case 'name':
                    case 'tutor_email':
                    case 'tutor_name':
                        for ($i = 4; $i < count($operadores); $i++) {
                            echo "<option value=" . $operadores[$i] . ">$operadores[$i]</option>";
                        }
                        break;
                }
                echo "</div>
                        </td>
                        <td><strong>$nomeAtributo<span class='obrigatorio'>*</span></strong>
                            <div>
                                <input type='text' class='txtPesquisa' name='Pesquisa_atributos[]'>
                            </div>
                        </td>";
            }
            echo "</td></tr>";
        }
        echo "</table>
            <p class='pesquisaP'>e do item:* " . $_SESSION['nome_do_item'] . " * uma listagem dos valores dos subitens:</p>
                
                <table class='tabelaInvisivel'>";

        foreach ($_SESSION['id_subitem_obter'] as $idSubitem) {

            $query_subitem_nome = "SELECT name, value_type FROM subitem WHERE id=$idSubitem";
            $result_subitem_nome = mysqli_query($link, $query_subitem_nome);
            $subitem = mysqli_fetch_assoc($result_subitem_nome);

            echo "<tr><td><li>" . $subitem['name'] . "</td>";
            if (in_array($idSubitem, $_SESSION['id_subitem_filtro'])) {
                echo "<td><strong>Operador<span class='obrigatorio'>*</span></strong>
                        <div>
                            <select class='selectOperador' name='subitem_operador[]'>    
                                <option value=''></option>";

                switch ($subitem['value_type']) {
                    case 'text':
                        for ($i = 4; $i < count($operadores); $i++) {
                            echo "<option value='" . $operadores[$i] . "'>$operadores[$i]</option>";
                        }
                        break;

                    case 'int':
                    case 'double':
                        for ($i = 0; $i < 6; $i++) {
                            echo "<option value='" . $operadores[$i] . "'>$operadores[$i]</option>";
                        }
                        break;

                    case 'boolean':
                    case 'enum':
                        for ($i = 4; $i < 6; $i++) {
                            echo "<option value='" . $operadores[$i] . "'>$operadores[$i]</option>";
                        }
                        break;
                }
                echo "</div>
                        </td>
                        <td><strong>" . $subitem['name'] . "<span class='obrigatorio'>*</span></strong>
                            <div>
                                <input type='text' class='txtPesquisa' name='Pesquisa_subitem[]'>
                            </div>
                        </td>";
            }
            echo "</tr>";
        }
        echo "</table>
            <input type='hidden' name='estado' value='execucao'>
            <input type='submit' value='Procurar'>
            </form>";
        button_voltar();
    } elseif ($_REQUEST['estado'] == 'execucao') {
        $erro = 0;
        if (isset($_POST['atributo_operador']))
            $operadores_atributos = $_POST['atributo_operador'];
        else
            $operadores_atributos = array();

        if (isset($_POST['subitem_operador']))
            $operadores_subitems = $_POST['subitem_operador'];
        else
            $operadores_subitems = array();

        if (isset($_POST['Pesquisa_atributos']))
            $valores_operadores_atributos = $_POST['Pesquisa_atributos'];
        else
            $valores_operadores_atributos = array();

        if (isset($_POST['Pesquisa_subitem']))
            $valores_operadores_subitems = $_POST['Pesquisa_subitem'];
        else
            $valores_operadores_subitems = array();

        //verifica se selecionou os operadores dos atributos    
        foreach ($operadores_atributos as $operAtribVerificacao) {
            if ($operAtribVerificacao == "") {
                echo "<span class='obrigatorio'>Tem de escolher um operador para cada atributo<br></span>";
                $erro = 1;
                break;
            }
        }

        //verifica se os campos do formulário estão preenchidos
        foreach ($valores_operadores_atributos as $valor) {
            if ($valor == "") {
                echo "<span class='obrigatorio'>Tem de preencher todos os campos do formulário<br></span>";
                $erro = 1;
                break;
            }
        }

        //verifica se selecionou os operadores dos subitems
        foreach ($operadores_subitems as $operSubitVerificacao) {
            if ($operSubitVerificacao == "") {
                echo "<span class='obrigatorio'>Tem de escolher um operador para cada subitem<br></span>";
                $erro = 1;
                break;
            }
        }

        //caso os campos da partye dos atributos não forem preenchidos
        foreach ($valores_operadores_subitems as $valor) {
            if (($valor == "") && $erro == 0) {
                echo "<span class='obrigatorio'>Tem de preencher todos os campos </span>";
                $erro = 1;
                break;
            }
        }

        //verificar a data
        if (in_array('birth_date', $_SESSION['nome_atributo_filtro'])) {
            $data_errada = false;
            $date = $valores_operadores_atributos[0];
            $date_ver = explode("-", $date);
            if (count($date_ver) != 3) {
                $erro = 1;
                $data_errada = true;
            }
            foreach ($date_ver as $valor) {
                if (preg_match("/[^0-9]/", $valor) == 1 || strlen($valor) == 0) {
                    $erro = 1;
                    $data_errada = true;
                    break;
                }
            }
            if (strlen($date_ver[0]) != 4) {
                $erro = 1;
                $data_errada = true;
            }
            if ($data_errada)
                echo "<span class='obrigatorio'>Tem de inserir uma data correta</span>";
        }

        // ----QUERY----
        //se não houver erros irá ser feita a query pesquisa  
        if ($erro == 0) {

            $query_Pesquisa = "SELECT ";
            $query_descricao = "Seleciona-se ";
            $primeira_vez = true;
            //se o usuario quer obter algum atributo da criança
            if (count($_SESSION['nome_atributo_obter']) != 0) { //verifica se existe atributos para obter
                foreach ($_SESSION['nome_atributo_obter'] as $nomeAtributo) {
                    switch ($nomeAtributo) {
                        case 'id':
                            $query_Pesquisa_valor = "child.id as 'id_crianca'";
                            $query_descricao_valor = "o id";
                            break;
                        case 'name':
                            $query_Pesquisa_valor = "child.name as 'nome_crianca'";
                            $query_descricao_valor = "o nome";
                            break;
                        case 'birth_date':
                            $query_Pesquisa_valor = "child.birth_date as 'data_nascimento_crianca'";
                            $query_descricao_valor = "a data de nascimento";
                            break;
                        case 'tutor_name':
                            $query_Pesquisa_valor = "child.tutor_name as 'nome_encarregado_educacao'";
                            $query_descricao_valor = "o nome do encarregado de educação";
                            break;
                        case 'tutor_phone':
                            $query_Pesquisa_valor = "child.tutor_phone as 'telefone_encarregado_educacao'";
                            $query_descricao_valor = "o telefone do encarregado de educação";
                            break;
                        case 'tutor_email':
                            $query_Pesquisa_valor = "child.tutor_email as 'email_encarregado_educacao'";
                            $query_descricao_valor = "o email do encarregado de educação";
                            break;
                    }
                    if ($primeira_vez) {
                        $query_Pesquisa .= $query_Pesquisa_valor;
                        $query_descricao .= $query_descricao_valor;
                        $primeira_vez = false;
                    } else {
                        $query_Pesquisa .= ', ' . $query_Pesquisa_valor;
                        $query_descricao .= ', ' . $query_descricao_valor;
                    }
                }
                if (count($_SESSION['id_subitem_obter']) != 0) { //caso hajam subitems para obter
                    $query_Pesquisa .= ", subitem.name as 'nome_subitem', value.value as 'valor' FROM child INNER JOIN value on child.id=value.child_id INNER JOIN subitem on value.subitem_id=subitem.id";
                    $query_descricao .= ", o nome dos subitems e seus valores";
                } else if (count($_SESSION['id_subitem_obter']) == 0) { //caso não hajam subitems para obter
                    $query_Pesquisa .= " FROM child";
                }
            } else { //caso não seja preciso obter os atributos da criança
                if (count($_SESSION['id_subitem_obter']) != 0) {
                    $query_Pesquisa .= "subitem.name as 'nome_subitem', value.value as 'valor' FROM value INNER JOIN subitem on value.subitem_id=subitem.id";
                    $query_descricao .= "o nome dos subitems e seus valores";
                }
            }

            // ----Where----
            if ((count($_SESSION['nome_atributo_filtro']) || count($_SESSION['id_subitem_obter'])) != 0) { //caso haja filtros
                $query_Pesquisa .= " WHERE";
                $query_descricao .= ". Onde";

                $indice = 0;
                $primeira_vez_where = true;
                if ($_SESSION['nome_atributo_filtro'] != 0) {
                    foreach ($_SESSION['nome_atributo_filtro'] as $atributo_filtro) {
                        switch ($atributo_filtro) {
                            case 'id':
                                $query_Pesquisa_valor = " child.id";
                                $query_descricao_valor = " o id";
                                break;
                            case 'name':
                                $query_Pesquisa_valor = " child.name";
                                $query_descricao_valor = " o nome";
                                break;
                            case 'birth_date':
                                $query_Pesquisa_valor = " child.birth_date";
                                $query_descricao_valor = " a data de nascimento";
                                break;
                            case 'tutor_name':
                                $query_Pesquisa_valor = " child.tutor_name";
                                $query_descricao_valor = " o nome do encarregado de educação";
                                break;
                            case 'tutor_phone':
                                $query_Pesquisa_valor = " child.tutor_phone'";
                                $query_descricao_valor = " o telefone do encarregado de educação";
                                break;
                            case 'tutor_email':
                                $query_Pesquisa_valor =  "child.tutor_email'";
                                $query_descricao_valor = " o email do encarregado de educação";
                                break;
                        }
                        if ($primeira_vez_where) {
                            $primeira_vez_where = false;
                            $query_Pesquisa .= $query_Pesquisa_valor;
                            $query_descricao .= $query_descricao_valor;
                        } else {
                            $query_Pesquisa .= ' AND' . $query_Pesquisa_valor;
                            $query_descricao .= ',' . $query_descricao_valor;
                        }
                        switch ($operadores_atributos[$indice]) {
                            case '>':
                                $query_Pesquisa_valor = ">";
                                $query_descricao_valor = "é maior que";
                                break;
                            case '>=':
                                $query_Pesquisa_valor = ">=";
                                $query_descricao_valor = "é maior ou igual que";
                                break;
                            case '<':
                                $query_Pesquisa_valor = "<";
                                $query_descricao_valor = "é menor que";
                                break;
                            case '<=':
                                $query_Pesquisa_valor = "<=";
                                $query_descricao_valor = "é menor ou igual que";
                                break;
                            case '=':
                                $query_Pesquisa_valor = "=";
                                $query_descricao_valor = "é igual que";
                                break;
                            case '!=':
                                $query_Pesquisa_valor = "<>";
                                $query_descricao_valor = "é diferente de";
                                break;
                            case 'LIKE':
                                $query_Pesquisa_valor = " LIKE";
                                $query_descricao_valor = "contém";
                                break;
                        }
                        $query_Pesquisa .= $query_Pesquisa_valor . $valores_operadores_atributos[$indice];
                        $query_descricao .= " " . $query_descricao_valor . " " . $valores_operadores_atributos[$indice];

                        $indice++;
                    }
                }
                if (count($_SESSION['id_subitem_obter']) != 0) {
                    $indice = 0;
                    $abriu_paren = false;
                    $primeira_vez_or = true;
                    if ($primeira_vez_where) {
                        $primeira_vez_where = false;
                    } else {
                        $query_Pesquisa .= " AND (";
                        $abriu_paren = true;
                    }
                    foreach ($_SESSION['id_subitem_obter'] as $id_subitem) {
                        if ($primeira_vez_or) {
                            $primeira_vez_or = false;
                            $query_Pesquisa .= " subitem.id =";
                            $query_descricao .= " o id do subitem é ";
                        } else {
                            $query_Pesquisa .= " OR subitem.id = ";
                            $query_descricao .= " ou o id do subitem é ";
                        }
                        $query_Pesquisa .= $id_subitem;
                        $query_descricao .= $id_subitem;

                        if (in_array($id_subitem, $_SESSION['id_subitem_filtro'])) {
                            $query_Pesquisa .= " AND value";
                            $query_descricao .= " e o seu valor ";
                            switch ($operadores_subitems[$indice]) {
                                case '>':
                                    $query_Pesquisa_valor = ">";
                                    $query_descricao_valor = "é maior que";
                                    break;
                                case '>=':
                                    $query_Pesquisa_valor = ">=";
                                    $query_descricao_valor = "é maior ou igual que";
                                    break;
                                case '<':
                                    $query_Pesquisa_valor = "<";
                                    $query_descricao_valor = "é menor que";
                                    break;
                                case '<=':
                                    $query_Pesquisa_valor = "<=";
                                    $query_descricao_valor = "é menor ou igual que";
                                    break;
                                case '=':
                                    $query_Pesquisa_valor = "=";
                                    $query_descricao_valor = "é igual que";
                                    break;
                                case '!=':
                                    $query_Pesquisa_valor = "<>";
                                    $query_descricao_valor = "é diferente de";
                                    break;
                                case 'LIKE':
                                    $query_Pesquisa_valor = " LIKE";
                                    $query_descricao_valor = "contém";
                                    break;
                            }
                            $query_Pesquisa .= $query_Pesquisa_valor . " '" . $valores_operadores_subitems[$indice] . "' ";
                            $query_descricao .= $query_descricao_valor . " " . $valores_operadores_subitems[$indice];
                            $indice++;
                        }
                    }
                    if ($abriu_paren)
                        $query_Pesquisa .= ")";
                }
            }
            echo "<span class='queryDescrita'><strong>Descrição da query</strong>:<br>$query_descricao</span>";

            $result_query_pesquisa = mysqli_query($link, $query_Pesquisa);
            echo "<table class='tabelaPesquisa'>
                <tr>";
            $cabecalho = mysqli_fetch_fields($result_query_pesquisa);
            foreach ($cabecalho as $valor) {
                echo "<th>" . $valor->name . "</th>";
            }
            echo "</tr>";
            while ($rowPesquisa = mysqli_fetch_assoc($result_query_pesquisa)) {
                echo "<tr>";
                foreach ($cabecalho as $valor) {
                    echo "<td>" . $rowPesquisa[$valor->name] . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
        button_voltar();
    }
} else {
    echo "<p style='color:red;'>Não tem autorização para aceder a esta página</p>";
    button_voltar();
}
