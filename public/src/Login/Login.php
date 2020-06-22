<?php

namespace Login;

use Conn\Read;
use Conn\TableCrud;
use Conn\Update;
use Conn\Create;
use Entity\Metadados;
use Entity\Entity;
use Helpers\Check;
use Helpers\Helper;
use ReCaptcha\ReCaptcha;

class Login
{
    private $user;
    private $senha;
    private $token;
    private $social;
    private $socialToken;
    private $recaptcha;
    private $attempts = 0;
    private $result;

    /**
     * Login constructor.
     * @param array $data
     * @param bool $passEncripty
     */
    public function __construct(array $data, bool $passEncripty = true)
    {
        if ($data) {
            if (!empty($data['recaptcha']))
                $this->setRecaptcha($data['recaptcha']);

            if(!empty($data['social']) && !empty($data['socialToken'])) {
                $this->social = $data['social'];
                $this->socialToken = $data['socialToken'];
            }

            if (!empty($data['token'])) {
                $this->setToken($data['token']);
            } else {
                if (!empty($data['user']))
                    $this->setUser($data['user']);
                if (!empty($data['password']))
                    $this->setSenha($data['password'], $passEncripty);
            }

            $this->start();
        }
    }

    /**
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token)
    {
        $this->token = $token;
    }

    /**
     * @param string $email
     */
    public function setUser($user)
    {
        if (!empty($user))
            $this->user = (string)strip_tags(trim($user));
    }

    /**
     * @param $senha
     * @param bool $passEncripty
     */
    public function setSenha($senha, bool $passEncripty = true)
    {
        if (!empty($senha))
            $this->senha = (string)($passEncripty ? Check::password(trim($senha)) : trim($senha));
        else
            $this->setResult('Informe a senha');
    }

    /**
     * @param mixed $recaptcha
     */
    public function setRecaptcha($recaptcha)
    {
        $this->recaptcha = $recaptcha;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    private function start()
    {
        if (!empty($this->token)) {
            $this->setLogin($this->checkToken());
        } elseif ($this->user && $this->senha && !$this->attemptExceded()) {
            if ($this->isHuman())
                $this->checkUserInfo();

        } elseif ($this->user && $this->senha) {
            $cont = 10 - $this->attempts;
            $this->setResult($cont > 0 ? "{$cont} tentativas faltantes" : " bloqueado por 15 minutos");
        }
    }

    /**
     * Vetifica usuário e senha no banco de dados!
     */
    private function checkUserInfo()
    {
        if (!$this->getResult()) {

            $socialUser = (empty($this->social) ? "0 || login_social IS NULL" : ($this->social === "facebook" ? 2 : 1));
            $user = [];
            $read = new Read();
            $read->setSelect(["id", "nome", "imagem", "status", "data", "setor", "login_social", "system_id"]);
            $read->exeRead(PRE . "usuarios", "WHERE password = :pass AND (login_social = " . $socialUser . ")", "pass={$this->senha}", !0);
            if ($read->getResult()) {
                $usuarios = $read->getResult();

                if($socialUser === 1) {
                    /**
                     * Login social google
                     * validate info with the token
                     */
                    if(!empty($usuarios) && $this->senha === Check::password(Social::googleGetId($this->socialToken))) {
                        $user = $this->getUsuarioDataRelation($usuarios[0], "", Entity::dicionario($usuarios[0]['setor']), Entity::info($usuarios[0]['setor']));
                    } else {
                        $this->setResult('Token do google não condiz com o Usuário!');
                    }
                } elseif($socialUser === 2) {
                    /**
                     * Login social facebook
                     * validate info with the token
                     */
                    if(!empty($usuarios) && $this->senha === Check::password(Social::facebookGetId($this->socialToken))) {
                        $user = $this->getUsuarioDataRelation($usuarios[0], "", Entity::dicionario($usuarios[0]['setor']), Entity::info($usuarios[0]['setor']));
                    } else {
                        $this->setResult('Token do facebook não condiz com o Usuário!');
                    }

                } else {
                    /**
                     * Login normal
                     */
                    list($whereUser, $dicionarios, $info) = $this->getWhereUser($usuarios);

                    foreach ($usuarios as $users) {
                        if (strtolower($users['nome']) === strtolower($this->user)) {
                            if ($users['status'] === "1") {
                                $user = $this->getUsuarioDataRelation($users, "", $dicionarios, $info);
                            } else {
                                $this->setResult('Usuário Desativado!');
                            }

                            break;
                        } elseif (!empty($users['setor']) && !empty($whereUser[$users['setor']])) {
                            $usuarioAutenticado = $this->getUsuarioDataRelation($users, $whereUser[$users['setor']], $dicionarios, $info);

                            if (!empty($usuarioAutenticado['setorData'])) {
                                if ($usuarioAutenticado['status'] === "1") {
                                    $user = $usuarioAutenticado;
                                } else {
                                    $this->setResult('Usuário Desativado!');
                                }

                                break;
                            }

                        }
                    }
                }
            }

            $this->setLogin($user);
        }
    }

    /**
     * Return user data
     * @return array
     */
    private function checkToken(): array
    {
        $prazoTokenExpira = date('Y-m-d', strtotime("-12 months", strtotime(date("Y-m-d"))));
        $sql = new \Conn\SqlCommand();
        $sql->exeCommand("SELECT u.* FROM " . PRE . "usuarios as u JOIN " . PRE . "usuarios_token as t ON u.id = t.usuario WHERE t.token = '" . $this->token . "' AND u.status = 1 AND t.token_expira > " . $prazoTokenExpira);
        if ($sql->getResult()) {
            $user = $sql->getResult()[0];
            unset($user['password']);
            return $this->getUsuarioDataRelation($user);
        }

        return [];
    }

    /**
     * @param array $usuario
     * @param string $whereSetor
     * @param array $dicionarios
     * @param array $info
     * @return array
     */
    private function getUsuarioDataRelation(array $usuario, string $whereSetor = "", array $dicionarios = [], array $info = []): array
    {
        if (empty($usuario['setor']) || $usuario['setor'] === "admin") {
            $usuario['setor'] = "admin";
            $usuario['setorData'] = "";
            $usuario['system'] = "";
            $usuario['systemData'] = [];
            return $usuario;
        }

        /**
         * Obtém Setor Data
         */
        $read = new Read();
        $read->exeRead($usuario['setor'], "WHERE usuarios_id = {$usuario['id']}" . $whereSetor, null, !0);
        if ($read->getResult()) {
            $usuario['setorData'] = $read->getResult()[0];

            /**
             * Obtém System Data
             */
            if (empty($dicionarios[$usuario['setor']]))
                $dicionarios[$usuario['setor']] = Metadados::getDicionario($usuario['setor']);

            if (empty($info[$usuario['setor']]))
                $info[$usuario['setor']] = Metadados::getInfo($usuario['setor']);

            $usuario['system'] = (!empty($info[$usuario['setor']]['system']) ? $info[$usuario['setor']]['system'] : "");
            $usuario['systemData'] = [];

            if (!empty($usuario['system'])) {
                if (!empty($usuario['setorData']['system_id'])) {
                    $read->exeRead($usuario['system'], "WHERE id = :id", "id={$usuario['setorData']['system_id']}", !0);
                    $usuario['systemData'] = $read->getResult() ? $read->getResult()[0] : [];
                    $usuario['system_id'] = $usuario['systemData']['id'];
                } else {
                    foreach ($dicionarios[$usuario['setor']] as $dicionario) {
                        if ($dicionario['relation'] === $usuario['system']) {
                            $read->exeRead($usuario['system'], "WHERE id = :id", "id={$usuario['setorData'][$dicionario['column']]}", !0);
                            $usuario['systemData'] = $read->getResult() ? $read->getResult()[0] : [];
                            $usuario['system_id'] = $usuario['systemData']['id'];
                            $usuario['setorData']['system_id'] = $usuario['systemData']['id'];
                            break;
                        }
                    }
                }
            }
        }

        return $usuario;
    }

    /**
     * @param array $usuarios
     * @return array
     */
    private function getWhereUser(array $usuarios): array
    {
        $info = [];
        $dicionarios = [];
        $whereUser = [];
        foreach ($usuarios as $usuario) {
            if (!empty($usuario['setor'])) {

                if (empty($dicionarios[$usuario['setor']])) {
                    $dicionarios[$usuario['setor']] = Metadados::getDicionario($usuario['setor']);
                    $info[$usuario['setor']] = Metadados::getInfo($usuario['setor']);

                    $where = "";
                    if (!empty($info[$usuario['setor']]['unique'])) {
                        foreach ($info[$usuario['setor']]['unique'] as $id)
                            $where .= (empty($where) ? " && (" : " || ") . $dicionarios[$usuario['setor']][$id]['column'] . " = '{$this->user}'";
                    }

                    /**
                     * Mesmo que não seja informado como único, verifica campos de CPF, email e telefone
                     */
                    if (!empty($info[$usuario['setor']]['cpf']) && (empty($info[$usuario['setor']]['unique']) || !in_array($info[$usuario['setor']]['cpf'], $info[$usuario['setor']]['unique'])))
                        $where .= (empty($where) ? " && (" : " || ") . $dicionarios[$usuario['setor']][$info[$usuario['setor']]['cpf']]['column'] . " = '{$this->user}'";
                    if (!empty($info[$usuario['setor']]['email']) && (empty($info[$usuario['setor']]['unique']) || !in_array($info[$usuario['setor']]['email'], $info[$usuario['setor']]['unique'])))
                        $where .= (empty($where) ? " && (" : " || ") . $dicionarios[$usuario['setor']][$info[$usuario['setor']]['email']]['column'] . " = '{$this->user}'";
                    if (!empty($info[$usuario['setor']]['tel']) && (empty($info[$usuario['setor']]['unique']) || !in_array($info[$usuario['setor']]['tel'], $info[$usuario['setor']]['unique'])))
                        $where .= (empty($where) ? " && (" : " || ") . $dicionarios[$usuario['setor']][$info[$usuario['setor']]['tel']]['column'] . " = '{$this->user}'";

                    $whereUser[$usuario['setor']] = $where . (!empty($where) ? ")" : "");
                }
            }
        }

        return [$whereUser, $dicionarios, $info];
    }

    /**
     * Seta dados de um usuário como login de acesso
     * @param array $usuario
     */
    public function setLogin(array $usuario)
    {
        if ($usuario) {
            if (!empty($usuario['imagem'])) {
                $usuario['imagem'] = json_decode($usuario['imagem'], !0)[0];
                unset($usuario['imagem']['preview']);
            }

            if(empty($this->token)) {
                $this->token = $this->getToken();

                //atualiza banco com token
                $up = new Update();
                $up->exeUpdate("usuarios", ["token_recovery" => null], "WHERE id = :id", "id={$usuario['id']}");

                $create = new Create();
                $create->exeCreate("usuarios_token", ['token' => $this->token, "token_expira" => date("Y-m-d H:i:s"), "usuario" => $usuario['id']]);
            }

            $usuario['token'] = $this->token;

            $this->setResult($usuario);

        } elseif (empty($this->getResult())) {
            $this->setResult('Login Inválido!');

            $attempt = new TableCrud("login_attempt");
            $attempt->loadArray(array("ip" => filter_var(Helper::getIP(), FILTER_VALIDATE_IP), "data" => date("Y-m-d H:i:s"), "username" => $this->user));
            $attempt->save();
        }
    }

    private function attemptExceded()
    {
        $ip = filter_var(Helper::getIP(), FILTER_VALIDATE_IP);
        $read = new Read();
        $read->exeRead(PRE . "login_attempt", "WHERE data > DATE_SUB(NOW(), INTERVAL 15 MINUTE) && ip = '{$ip}' && email = '{$this->user}'", !0);
        $this->attempts = $read->getRowCount();

        return ($this->attempts > 10); // maximo de 10 tentativas por IP e email iguais em um intervalo de 15 minutos
    }

    private function isHuman()
    {
        if (defined("RECAPTCHA")) {
            if (empty($this->recaptcha))
                $this->setResult("resolva o captcha");

            $recaptcha = new ReCaptcha(RECAPTCHA);
            $resp = $recaptcha->verify($this->recaptcha, filter_var(Helper::getIP(), FILTER_VALIDATE_IP));
            if (!$resp->isSuccess())
                $this->setResult('<p>' . implode('</p><p>', $resp->getErrorCodes()) . '</p>');
        }

        return $this->getResult() ? false : true;
    }

    /**
     * @return string
     */
    private function getToken()
    {
        return md5("tokes" . rand(9999, 99999) . md5(base64_encode(date("Y-m-d H:i:s"))) . rand(0, 9999));
    }
}
