<?php
use Conn\TableCrud;

function setCookiePre($name, $value, int $dias = 60)
{
    $tempo = $dias < 0 ? time() - 1 : time() + (86400 * $dias);
    setcookie($name, $value, $tempo, "/"); // 2 meses de cookie
}

if (isset($_SESSION['userlogin'])) {
    if (!empty($_SESSION['userlogin']['token']) || (isset($_COOKIE['token']) && $_COOKIE['token'] !== "0")) {
        $t = !empty($_SESSION['userlogin']['token']) ? $_SESSION['userlogin']['token'] : $_COOKIE['token'];
        $token = new TableCrud("usuarios");
        $token->load("token", $t);
        if ($token->exist()) {
            $token->setDados(["token" => null, "token_expira" => null]);
            $token->save();
        }
    }
}
session_unset();

setCookiePre("token", 0, -1);
setCookiePre("id", 0, -1);
setCookiePre("nome", 0, -1);
setCookiePre("imagem", 0, -1);
setCookiePre("setor", 0, -1);