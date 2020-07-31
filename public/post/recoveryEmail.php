<?php

$data['data'] = 0;
$email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
$setor = trim(filter_input(INPUT_POST, 'setor', FILTER_DEFAULT));

if ($email) {

    /**
     * @param int $id
     * @param string $setor
     * @return string
     */
    function setRecoveryCode(int $id, string $setor): string
    {
        $code = md5(base64_encode(date('Y-m-d H:i:s') . "recovery-pass"));
        $up = new \Conn\Update();
        $up->exeUpdate($setor, ['token_recovery' => $code], "WHERE id = :id", "id={$id}");

        return $code;
    }

    /**
     * @param int $id
     * @param string $email
     * @param string $setor
     */
    function sendEmailRecovery(int $id, string $email, string $setor)
    {
        $code = setRecoveryCode($id, $setor);
        $envio = new \Email\EmailEnvio();
        $envio->setAssunto("Recuperação de Senha");
        $envio->setDestinatarioEmail($email);
        $envio->setBtnLink(HOME . "inserir-nova-senha/{$code}");
        $envio->setBtnText("<b style='font-size:25px;color:white'>Redefinir Senha</b>");
        $envio->setMensagem("Para redefinir sua senha, clique no link abaixo.");
        $envio->enviar();

        return !0;
    }

    /**
     * @param string $setor
     * @return string
     */
    function getEmailColumn(string $setor): string
    {
        $dic = new \Entity\Dicionario($setor);
        $metaEmail = $dic->search("format", "email");
        return $metaEmail ? $metaEmail->getColumn() : "";
    }

    $read = new \Conn\Read();
    if (empty($setor)) {
        foreach (\Config\Config::getSetorSystem() as $setor) {
            $emailColumn = getEmailColumn($setor);
            if (!empty($emailColumn)) {
                $read->exeRead($setor, "WHERE {$emailColumn} = '{$email}'");
                if ($read->getResult())
                    $data['data'] = sendEmailRecovery($read->getResult()[0]['id'], $read->getResult()[0][$emailColumn], $setor);
            }
        }
    } else {
        $emailColumn = getEmailColumn($setor);
        if (!empty($emailColumn)) {
            $read->exeRead($setor, "WHERE {$emailColumn} = '{$email}'");
            if ($read->getResult())
                $data['data'] = sendEmailRecovery($read->getResult()[0]['id'], $read->getResult()[0][$emailColumn], $setor);
        }
    }
}