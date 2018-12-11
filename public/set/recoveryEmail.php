<?php
use \ConnCrud\TableCrud;

$email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));

if ($email) {
    $user = new TableCrud(PRE . "usuarios");
    $user->load("email", $email);
    if ($user->exist()) {
        $code = md5(base64_encode(date('Y-m-d H:i:s') . "recovery-pass"));
        $user->setDados(['token' => null, 'token_recovery' => $code, "token_expira" => date('Y-m-d H:i:s')]);
        $user->save();

        //Prepara para enviar email
        $envio = new \EmailControl\EmailEnvio();
        $envio->setAssunto("Recuperação de Senha");
        $envio->setDestinatarioEmail($email);
        $envio->setBtnLink(HOME . "inserir-nova-senha/{$code}");
        $envio->setBtnText("<b style='font-size:25px;color:white'>Redefinir Senha</b>");
        $envio->setMensagem("Para redefinir sua senha, clique no link abaixo.");
        $envio->enviar();

        $data['data'] = true;
    }
}