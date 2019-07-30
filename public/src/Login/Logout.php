<?php

namespace Login;

use Conn\TableCrud;

class Logout
{
    /**
     * Logout constructor.
     */
    public function __construct()
    {
        if (isset($_SESSION['userlogin'])) {
            if (!empty($_SESSION['userlogin']['token'])) {
                $t = !empty($_SESSION['userlogin']['token']) ? $_SESSION['userlogin']['token'] : $_COOKIE['token'];
                $token = new TableCrud("usuarios");
                $token->load("token", $t);
                if ($token->exist()) {
                    $token->setDados(["token" => null, "token_expira" => null]);
                    $token->save();
                }
            }
            unset($_SESSION['userlogin']);
        }
    }
}
