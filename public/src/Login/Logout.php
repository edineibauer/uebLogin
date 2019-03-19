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

        $this->setCookie("token", 0, -1);
        $this->setCookie("id", 0, -1);
        $this->setCookie("nome", 0, -1);
        $this->setCookie("imagem", 0, -1);
        $this->setCookie("setor", 0, -1);
    }

    /**
     * @param $name
     * @param $value
     * @param int $dias
     */
    private function setCookie($name, $value, int $dias = 60)
    {
        $tempo = $dias < 0 ? time() - 1 : time() + (86400 * $dias);
        setcookie($name, $value, $tempo, "/"); // 2 meses de cookie
    }
}
