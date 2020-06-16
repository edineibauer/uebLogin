<?php

namespace Login;

class Social
{
    public static function facebookLogin()
    {
        include_once PATH_HOME . VENDOR . "login/public/view/login/inc/facebookLogin.php";
    }

    public static function googleLogin()
    {
        include_once PATH_HOME . VENDOR . "login/public/view/login/inc/googleLogin.php";
    }
}