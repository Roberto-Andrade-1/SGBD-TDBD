<?php
require_once("custom/php/common.php");
if(is_user_logged_in()){
    
    

}
else{
    echo "<p>Não tem autorização para aceder a esta página</p>";
    button_voltar();
}
?>