<?php

use Login\Login;

$dados['email'] = strip_tags(trim(filter_input(INPUT_POST, "email", FILTER_DEFAULT)));
$dados['password'] = trim(filter_input(INPUT_POST, "pass", FILTER_DEFAULT));
$dados['recaptcha'] = filter_input(INPUT_POST, "recaptcha", FILTER_DEFAULT);

if(!empty($dados['email']) && !empty($dados['password'])) {
    $login = new Login($dados);
    $data['data'] = $login->getResult();
} else {
    if(!empty($dados['email']))
        $data['data'] = "Informe o Email";
    else
        $data['data'] = "Informe a Senha";
}