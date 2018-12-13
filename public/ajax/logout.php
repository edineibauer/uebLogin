<?php
if(!empty($_SESSION['userlogin'])) {
    $log = new \Login\Login();
    $log->logOut();
}

$data['data'] = 1;