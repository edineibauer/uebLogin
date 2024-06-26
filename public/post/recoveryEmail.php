<?php

$data['data'] = !1;
$email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
$setor = trim(filter_input(INPUT_POST, 'setor'));

if (!empty($email)) {

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
     * @return string
     */
    function sendEmailRecovery(array $setorData, string $email): string
    {
        $code = setRecoveryCode($setorData);

        try {

            $emailSend = new \Email\Email();
            $emailSend->setDestinatarioEmail($email);
            $emailSend->setAssunto("Recuperação de Senha");
            $emailSend->setMensagem("Para redefinir sua senha, clique no link abaixo.");
            $emailSend->setDestinatarioNome("");
            $emailSend->setIpPoolPrivado(true);
            $emailSend->setVariables([
                'id' => "",
                'image' => "",
                'background' => "",
                'btn' => "Criar nova senha",
                'link' => HOME_PRODUCTION . "index.html?url=inserir-nova-senha/{$code}",
            ]);
            $emailSend->enviar();

        } catch (Exception $e) {
            return $e;
        }

        return empty($emailSend->getError()) ? "1" : $emailSend->getError();
    }

    $read = new \Conn\Read();
    if(empty($setor)) {
        foreach (\Helpers\Helper::listFolder(PATH_HOME . "entity/cache/info") as $entity) {
            if(empty($entity) || pathinfo($entity, PATHINFO_EXTENSION) !== "json")
                continue;

            $infoEntity = json_decode(file_get_contents(PATH_HOME . "entity/cache/info/" . $entity), true);
            if($infoEntity['user'] == 1 && !empty($infoEntity['email'])) {
                $cacheEntity = json_decode(file_get_contents(PATH_HOME . "entity/cache/" . $entity), true);
                $emailColumn = $cacheEntity[$infoEntity['email']]['column'];

                if(empty($emailColumn))
                    continue;

                $read->exeRead(str_replace(".json", "", $entity), "WHERE {$emailColumn} = :ee", ["ee" => $email]);
                if($read->getResult()) {
                    $data['data'] = sendEmailRecovery($read->getResult()[0], $read->getResult()[0][$emailColumn]);
                    if($data['data'] !== "1")
                        $data['error'] = $data['data'];
                }
            }
        }
    } else {
        $dic = new \Entity\Dicionario($setor);
        $metaEmail = $dic->search("format", "email");
        $emailColumn = $metaEmail ? $metaEmail->getColumn() : "";

        if (!empty($emailColumn)) {
            $read->exeRead($setor, "WHERE {$emailColumn} = :ee", ["ee" => $email]);
            if ($read->getResult()) {
                $data['data'] = sendEmailRecovery($read->getResult()[0], $read->getResult()[0][$emailColumn]);
                if ($data['data'] !== "1")
                    $data['error'] = $data['data'];
            } else {
                $data['error'] = "Email não encontrado";
            }
        }
    }
}