<?php

if(!empty($_SESSION['userlogin'])) {
    $log = new \SessionControl\Login();
    $log->logOut();
}

$data['response'] = 3;
$data['data'] = HOME . "login";