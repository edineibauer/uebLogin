<?php

use Login\Login;

$dados['email'] = strip_tags(trim(filter_input(INPUT_POST, "email", FILTER_DEFAULT)));
$dados['password'] = trim(filter_input(INPUT_POST, "pass", FILTER_DEFAULT));
$dados['recaptcha'] = filter_input(INPUT_POST, "recaptcha", FILTER_DEFAULT);

$login = new Login($dados);
$data['data'] = $login->getResult();