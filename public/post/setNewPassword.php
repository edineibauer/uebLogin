<?php

use Conn\Delete;
use \Conn\TableCrud;
use Helpers\Check;

$senha = strip_tags(trim(filter_input(INPUT_POST, 'senha')));
$restoreCode = filter_input(INPUT_POST, 'code');

$d = new \Entity\Dicionario("usuarios");

$dadosUpdate = [
    "token_recovery" => "",
    $d->search($d->getInfo()['password'])->getColumn() => Check::password($senha)
];

if(!empty($d->getInfo()['status']))
    $dadosUpdate[$d->search($d->getInfo()['status'])->getColumn()] = 1;

$banco = new TableCrud("usuarios");
$banco->load("token_recovery", $restoreCode);
if ($banco->exist()) {
    $id = $banco->getDados()['id'];
    $banco->setDados($dadosUpdate);
    $banco->save();

    $del = new Delete();
    $del->exeDelete("usuarios_token", "WHERE usuario = :u", "u={$id}");

    $data['data'] = "1";
}