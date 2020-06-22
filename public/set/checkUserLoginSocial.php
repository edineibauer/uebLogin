<?php

if (empty($_SESSION['userlogin']['token']) && !empty($post['social'])) {
    $entity = constant(strtoupper($post['social']) . "ENTITY");

    /**
     * Search for the user
     */
    $read = new \Conn\Read();
    $read->exeRead("usuarios", "WHERE password = '" . \Helpers\Check::password($post['id']) . "' AND login_social = " . ($post['social'] === "facebook" ? 2 : 1) . " AND status = 1 AND setor = '{$entity}'");
    if (!$read->getResult()) {
        /**
         * User not exist, so create
         */
        $userData = [];
        $findImage = !1;

        /**
         * With the dictionary, search and set the fields values on entity from the social media
         */
        $dicionario = \Entity\Entity::dicionario($entity);
        foreach ($dicionario as $i => $meta) {
            switch ($meta['format']) {
                case "title":
                    $userData[$meta['column']] = $post['name'];
                    break;
                case "email":
                    $userData[$meta['column']] = $post['email'];
                    break;
                case "password":
                    $userData[$meta['column']] = $post['id'];
                    break;
                case "source_list":
                    if ($meta['size'] === 1 && !$findImage && in_array(["valor" => "png", "representacao" => "png"], $meta['allow']['options'])) {
                        $findImage = !0;
                        $userData[$meta['column']] = $post['image'];
                    }
            }
        }

        /**
         * Create new user with setor $entity
         */
        $d = new \Entity\Dicionario($entity);
        $d->setData($userData);
        $d->save();
        $data['error'] = $d->getError();
    }
}