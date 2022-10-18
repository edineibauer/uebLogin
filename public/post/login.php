<?php

use Login\Login;
use Entity\Json;

$dados['email'] = strip_tags(trim(filter_input(INPUT_POST, "email")));
$dados['cpf'] = str_replace([".", "-"], "", strip_tags(trim(filter_input(INPUT_POST, "cpf"))));
$dados['nome'] = strip_tags(trim(filter_input(INPUT_POST, "nome")));
$dados['user'] = strip_tags(trim(filter_input(INPUT_POST, "user")));

$dados['password'] = trim(filter_input(INPUT_POST, "pass"));

$dados['token'] = filter_input(INPUT_POST, "token");

$dados['social'] = filter_input(INPUT_POST, "social");
$dados['socialToken'] = $dados['token'];
$dados['recaptcha'] = filter_input(INPUT_POST, "recaptcha");

if(((!empty($dados['email']) || !empty($dados['cpf']) || !empty($dados['nome']) || !empty($dados['user'])) && !empty($dados['password'])) || !empty($dados['token'])) {
    $login = new Login($dados);
    $data['data'] = $login->getResult();
} else {
    $data['error'] = (!empty($dados['user']) ? "Informe o Usuário" : "Informe a Senha");
}