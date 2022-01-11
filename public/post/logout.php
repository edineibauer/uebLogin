<?php

/**
 * Read table token and remove toke access
 */
$del = new \Conn\Delete();
$del->exeDelete("usuarios_token", "WHERE token = :t", "t={$_SESSION['userlogin']['token']}");
$data['data'] = "1";
