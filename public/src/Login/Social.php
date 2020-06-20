<?php

namespace Login;

use Helpers\Check;

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

    /**
     * @return string
     */
    public static function facebookGetId():string
    {
        if(!empty($_SESSION['facebookToken']) && defined('FACEBOOKAPLICATIONID') && !empty(FACEBOOKAPLICATIONID) && defined('FACEBOOKVERSION') && !empty(FACEBOOKVERSION) && defined('FACEBOOKENTITY')) {
            $data = file_get_contents("https://graph.facebook.com/" . FACEBOOKVERSION . "/me?fields=id&access_token=" . $_SESSION['facebookToken']);
            if(!empty($data) && Check::isJson($data)) {
                $idFacebook = json_decode($data, !0);
                return !empty($idFacebook['id']) ? $idFacebook['id'] : "";
            }
        }
    }

    /**
     * @return string
     */
    public static function googleGetId():string
    {
        if(!empty($_SESSION['googleToken']) && defined('GOOGLELOGINCLIENTID') && !empty(GOOGLELOGINCLIENTID) && defined('GOOGLEENTITY')) {
            $data = file_get_contents("https://oauth2.googleapis.com/tokeninfo?id_token=" . $_SESSION['googleToken']);
            if(!empty($data) && Check::isJson($data)) {
                $idGoogle = json_decode($data, !0);
                return !empty($idGoogle['sub']) ? $idGoogle['sub'] : "";
            }
        }
    }
}