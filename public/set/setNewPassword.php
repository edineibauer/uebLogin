<?php
use \ConnCrud\TableCrud;
use Helpers\Check;

$senha = strip_tags(trim(filter_input(INPUT_POST, 'senha', FILTER_DEFAULT)));
$restoreCode = filter_input(INPUT_POST, 'code', FILTER_DEFAULT);

$d = new \Entity\Dicionario("usuarios");
$passColumn = $d->search($d->getInfo()['password'])->getColumn();

$banco = new TableCrud("usuarios");
$banco->load("token_recovery", $restoreCode);
if ($banco->exist()) {
    $banco->setDados([
        "token_recovery" => "",
        "token" => "",
        "token_expira" => "",
        $passColumn => Check::password($senha)
    ]);
    $banco->save();

    $data['data'] = "1";
}