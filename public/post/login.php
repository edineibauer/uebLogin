<?php

use Login\Login;
use Entity\Json;

$dados['email'] = strip_tags(trim(filter_input(INPUT_POST, "email") ?? ''));
$dados['cpf'] = str_replace([".", "-"], "", strip_tags(trim(filter_input(INPUT_POST, "cpf") ?? '')));
$dados['nome'] = strip_tags(trim(filter_input(INPUT_POST, "nome") ?? ''));
$dados['user'] = strip_tags(trim(filter_input(INPUT_POST, "user") ?? ''));
$dados['setor'] = strip_tags(trim(filter_input(INPUT_POST, "setor") ?? ''));
$dados['system_id'] = filter_input(INPUT_POST, "system_id", FILTER_VALIDATE_INT);
$userCache = filter_input(INPUT_POST, "userCache", FILTER_VALIDATE_BOOLEAN);

if(empty($dados['setor']))
    $dados['setor'] = filter_input(INPUT_POST, "setor", FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
elseif(is_string($dados['setor']))
    $dados['setor'] = [$dados['setor']];
else
    $dados['setor'] = null;

$dados['password'] = trim(filter_input(INPUT_POST, "pass") ?? '');

$dados['token'] = filter_input(INPUT_POST, "token");

if(((!empty($dados['email']) || !empty($dados['cpf']) || !empty($dados['nome']) || !empty($dados['user'])) && !empty($dados['password'])) || !empty($dados['token'])) {
    $login = new Login($dados);
    $data['data'] = $login->getResult();

    if(is_array($data['data']) && $userCache) {
        $loginData = $data['data'];
        include_once PATH_HOME . VENDOR . "config/public/get/userCache.php";
        $userCacheContent = $data['data'];
        $data['data'] = $loginData;
        $data['data']['userCache'] = $userCacheContent;
        unset($userCacheContent, $loginData);
    }

} else {
    $data['error'] = (!empty($dados['user']) ? "Informe o Usuário" : "Informe a Senha");
}