<?php

use Login\Login;
use Entity\Json;

$dados['user'] = strip_tags(trim(filter_input(INPUT_POST, "email", FILTER_DEFAULT)));
$dados['password'] = trim(filter_input(INPUT_POST, "pass", FILTER_DEFAULT));
$dados['social'] = filter_input(INPUT_POST, "social", FILTER_DEFAULT);
$dados['socialToken'] = filter_input(INPUT_POST, "token", FILTER_DEFAULT);
$dados['recaptcha'] = filter_input(INPUT_POST, "recaptcha", FILTER_DEFAULT);

$store = new Json("login");
$store->setVersionamento(!1);
$now = DateTime::createFromFormat('U.u', microtime(true));
$store->save($now->format("Y-m-d H:i:s.u"), $dados);

if(!empty($dados['user']) && !empty($dados['password'])) {
    $login = new Login($dados);
    $data['data'] = $login->getResult();
} else {
    $data['error'] = (!empty($dados['user']) ? "Informe o Usuário" : "Informe a Senha");
}