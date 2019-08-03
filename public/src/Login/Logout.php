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
                $token = new TableCrud("usuarios_token");
                $token->load("token", $_SESSION['userlogin']['token']);
                if ($token->exist())
                    $token->delete();
            }
            unset($_SESSION['userlogin']);
        }
    }
}
