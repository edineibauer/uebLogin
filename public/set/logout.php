<?php
$login = new \Login\Login();
$login->logOut();

if($login->getResult() && !$login->getError()) {
    echo json_encode(array("status" => 1, "mensagem" => $login->getResult()));
} else if($login->getError()) {
    echo json_encode(array("status" => 2, "mensagem" => $login->getError()));
}
