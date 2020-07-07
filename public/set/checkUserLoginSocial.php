<?php

$token = filter_input(INPUT_POST, 'token', FILTER_DEFAULT);
$social = filter_input(INPUT_POST, 'social', FILTER_DEFAULT);
$post = [
    "id" => trim(strip_tags(filter_input(INPUT_POST, 'id', FILTER_DEFAULT))),
    "name" => trim(strip_tags(filter_input(INPUT_POST, 'name', FILTER_DEFAULT))),
    "image" => trim(strip_tags(filter_input(INPUT_POST, 'image', FILTER_DEFAULT))),
    "email" => trim(strip_tags(filter_input(INPUT_POST, 'email', FILTER_DEFAULT)))
];

if (!empty($token) && !empty($social)) {
    $entity = constant(strtoupper($social) . "ENTITY");

    /**
     * Search for the user
     */
    $read = new \Conn\Read();
    $read->exeRead("usuarios", "WHERE password = '" . \Helpers\Check::password($post['id']) . "' AND login_social = " . ($social === "facebook" ? 2 : 1) . " AND setor = '{$entity}'");
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
         * Define session socialToken to create user social
         */
        $_SESSION['userlogin']['socialToken'] = $token;

        /**
         * Create new user with setor $entity
         */
        $d = new \Entity\Dicionario($entity);
        $d->setData($userData);
        $d->save();
        $data['error'] = $d->getError();
    }
}