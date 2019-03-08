<?php

use Login\Login;

$dados['user'] = strip_tags(trim(filter_input(INPUT_POST, "email", FILTER_DEFAULT)));
$dados['password'] = trim(filter_input(INPUT_POST, "pass", FILTER_DEFAULT));
$dados['recaptcha'] = filter_input(INPUT_POST, "recaptcha", FILTER_DEFAULT);

if(!empty($dados['user']) && !empty($dados['password'])) {
    $login = new Login($dados);
    $data['data'] = $login->getResult();
} else {
    if(!empty($dados['user']))
        $data['data'] = "Informe o Usuário";
    else
        $data['data'] = "Informe a Senha";
}