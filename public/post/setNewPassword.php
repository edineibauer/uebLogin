<?php

use Conn\Delete;
use \Conn\TableCrud;
use Helpers\Check;

$senha = strip_tags(trim(filter_input(INPUT_POST, 'senha', FILTER_DEFAULT)));
$restoreCode = filter_input(INPUT_POST, 'code', FILTER_DEFAULT);

$d = new \Entity\Dicionario("usuarios");
$passColumn = $d->search($d->getInfo()['password'])->getColumn();

$banco = new TableCrud("usuarios");
$banco->load("token_recovery", $restoreCode);
if ($banco->exist()) {
    $id = $banco->getDados()['id'];
    $banco->setDados([
        "token_recovery" => "",
        $passColumn => Check::password($senha)
    ]);
    $banco->save();

    $del = new Delete();
    $del->exeDelete("usuarios_token", "WHERE usuario = :u", "u={$id}");

    $data['data'] = "1";
}