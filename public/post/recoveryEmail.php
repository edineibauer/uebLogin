<?php

$data['data'] = !1;
$email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
$setor = trim(filter_input(INPUT_POST, 'setor'));
$home = trim(filter_input(INPUT_POST, 'home')) ?? HOME;

if (!empty($email) && !empty($setor)) {

    /**
     * @param array $setorData
     * @return string
     */
    function setRecoveryCode(array $setorData): string
    {
        $code = md5(base64_encode(date('Y-m-d H:i:s') . "recovery-pass"));
        $up = new \Conn\Update();
        $up->exeUpdate("usuarios", ['token_recovery' => $code], "WHERE id = :id", "id={$setorData['usuarios_id']}");

        return $code;
    }

    /**
     * @param array $setorData
     * @param string $email
     * @param string $home
     * @return bool
     */
    function sendEmailRecovery(array $setorData, string $email, string $home)
    {
        $code = setRecoveryCode($setorData);
        $result = !0;
        if (defined("EMAIL")) {
            try {

                $emailSend = new \Email\Email();
                $emailSend->setDestinatarioEmail($email);
                $emailSend->setAssunto("Recuperação de Senha");
                $emailSend->setMensagem("Para redefinir sua senha, clique no link abaixo.");
                $emailSend->setDestinatarioNome("");
                $emailSend->setVariables([
                    'id' => "",
                    'image' => "",
                    'background' => "",
                    'btn' => "Criar nova senha",
                    'link' => $home . "index.html?url=inserir-nova-senha/{$code}",
                ]);
                $emailSend->enviar();

            } catch (Exception $e) {
                return !1;
            }

            $result = empty($emailSend->getError());
        }

        return $result;
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

    $emailColumn = getEmailColumn($setor);
    if (!empty($emailColumn)) {
        $read = new \Conn\Read();
        $read->exeRead($setor, "WHERE {$emailColumn} = '{$email}'", !0, !0, !0);
        if ($read->getResult())
            $data['data'] = sendEmailRecovery($read->getResult()[0], $read->getResult()[0][$emailColumn], $home);
    }
}