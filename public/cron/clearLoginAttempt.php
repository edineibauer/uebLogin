<?php

/**
 * Primeiro dia do mês na primeira hora exclui todas as tentativas de login registradas
 */
if(date("d H:i") === "01 01:00") {
    $del = new \Conn\Delete();
    $del->exeDelete("login_attempt", "WHERE data < DATE_SUB(NOW(), INTERVAL 1 MONTH)");
}