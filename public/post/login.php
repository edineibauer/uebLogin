<?php

use Login\Login;
use Entity\Json;

$dados['user'] = strip_tags(trim(filter_input(INPUT_POST, "email")));
$dados['password'] = trim(filter_input(INPUT_POST, "pass"));
$dados['social'] = filter_input(INPUT_POST, "social");
$dados['socialToken'] = filter_input(INPUT_POST, "token");
$dados['recaptcha'] = filter_input(INPUT_POST, "recaptcha");

if(!empty($dados['user']) && !empty($dados['password'])) {
    $login = new Login($dados);
    $data['data'] = $login->getResult();
} else {
    $data['error'] = (!empty($dados['user']) ? "Informe o Usuário" : "Informe a Senha");
}