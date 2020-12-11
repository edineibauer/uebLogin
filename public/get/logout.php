<?php

/**
 * Exclui todo o cache
 */
if(file_exists(PATH_HOME . "_cdn/userActivity/" . $_SESSION['userlogin']['id']))
    \Helpers\Helper::recurseDelete(PATH_HOME . "_cdn/userActivity/" . $_SESSION['userlogin']['id']);

if(file_exists(PATH_HOME . "_cdn/userSSE/" . $_SESSION['userlogin']['id']))
    \Helpers\Helper::recurseDelete(PATH_HOME . "_cdn/userSSE/" . $_SESSION['userlogin']['id']);

/**
 * Read table token and remove toke access
 */
$del = new \Conn\Delete();
$del->exeDelete("usuarios_token", "WHERE token = :t", "t={$_SESSION['userlogin']['token']}");
$data['data'] = "1";
