<?php

namespace Login;

use Config\Config;
use Conn\Read;
use Conn\Update;
use Conn\Create;
use Entity\Metadados;
use Helpers\Check;
use Helpers\Helper;
use ReCaptcha\ReCaptcha;

class Login
{
    private $user;
    private $email;
    private $cpf;
    private $nome;
    private $senha;
    private $token;
    private $setor;
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

            if(!empty($data['social']) AND !empty($data['socialToken'])) {
                $this->social = $data['social'];
                $this->socialToken = $data['socialToken'];
            }

            if (!empty($data['token'])) {
                $this->setToken($data['token']);
            } else {

                if (!empty($data['user']))
                    $this->setUser($data['user']);

                if (!empty($data['nome']))
                    $this->setNome($data['nome']);

                if (!empty($data['email']))
                    $this->setEmail($data['email']);

                if (!empty($data['cpf']))
                    $this->setCpf($data['cpf']);

                if (!empty($data['setor'])) {
                    if(is_string($data['setor']))
                        $this->setSetor([$data['setor']]);
                    elseif(is_array($data['setor']))
                        $this->setSetor($data['setor']);
                }

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
     * @param string $user
     * @return void
     */
    public function setUser(string $user)
    {
        if (!empty($user))
            $this->user = (string)strip_tags(trim($user));
    }

    /**
     * @param string $nome
     * @return void
     */
    public function setNome(string $nome)
    {
        if (!empty($nome))
            $this->nome = (string)strip_tags(trim($nome));
    }

    /**
     * @param string $email
     * @return void
     */
    public function setEmail(string $email)
    {
        if (!empty($email) && Check::email($email))
            $this->email = (string)strip_tags(trim($email));
    }

    /**
     * @param string $cpf
     * @return void
     */
    public function setCpf(string $cpf)
    {
        if (!empty($cpf) && Check::cpf($cpf))
            $this->cpf = (string)str_replace([".", "-"], "", strip_tags(trim($cpf)));
    }

    /**
     * @param array $setores
     * @return void
     */
    public function setSetor(array $setores): void
    {
        if($this->isArrayofStrings($setores)) {
            $this->setor = array_map(function ($item) {
                return trim(strip_tags($item));
            }, $setores);
        }
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

    /**
     * @param $array
     * @return bool
     */
    private function isArrayofStrings($array): bool {
        if (!is_array($array))
            return false;

        foreach ($array as $item) {
            if (!is_string($item))
                return false;
        }

        return true;
    }

    private function start()
    {
        if (!empty($this->token)) {
            $this->setLogin($this->checkToken());

        } elseif (($this->user OR $this->email OR $this->cpf OR $this->nome) AND $this->senha AND !$this->attemptExceded()) {
            if ($this->isHuman())
                $this->checkUserInfo();

        } elseif (($this->user OR $this->email OR $this->cpf OR $this->nome) AND $this->senha) {
            $cont = 10 - $this->attempts;
            $this->setResult($cont > 0 ? "{$cont} tentativas faltantes" : " bloqueado por 15 minutos");
        } else {
            $this->setResult("Informações de login inválidas");
        }
    }

    /**
     * Vetifica usuário e senha no banco de dados!
     */
    private function checkUserInfo()
    {
        if (!$this->getResult()) {

            $socialUser = (empty($this->social) ? "0 OR login_social IS NULL" : ($this->social === "facebook" ? 2 : 1));
            $user = [];
            $read = new Read();
            $read->setSelect(["id", "nome", "imagem", "status", "data", "setor", "login_social", "system_id"]);
            $read->exeRead("usuarios", "WHERE password = :pass AND (login_social = :ss)" . (!empty($this->setor) ? " AND setor IN('" . implode("','", $this->setor) . "')" : ""), ["pass" => $this->senha, "ss" => $socialUser]);
            if ($read->getResult()) {
                $usuarios = $read->getResult();

                if($socialUser === 1) {
                    /**
                     * Login social google
                     * validate info with the token
                     */
                    if(!empty($usuarios) AND !empty($this->socialToken) AND $this->senha === Check::password(Social::googleGetId($this->socialToken))) {
                        $user = $this->getUsuarioDataRelation($usuarios[0]);
                    } else {
                        $this->setResult('Token do google não condiz com o Usuário!');
                    }
                } elseif($socialUser === 2) {
                    /**
                     * Login social facebook
                     * validate info with the token
                     */
                    if(!empty($usuarios) AND !empty($this->socialToken) AND $this->senha === Check::password(Social::facebookGetId($this->socialToken))) {
                        $user = $this->getUsuarioDataRelation($usuarios[0]);
                    } else {
                        $this->setResult('Token do facebook não condiz com o Usuário!');
                    }

                } else {
                    /**
                     * Login normal
                     */
                    $whereUser = $this->getWhereUser($usuarios);

                    foreach ($usuarios as $users) {

                        if (!empty($users['setor']) AND !empty($whereUser[$users['setor']])) {
                            /**
                             * Obtém Setor Data
                             */
                            $read->exeRead($users['setor'], "WHERE usuarios_id = :ui" . $whereUser[$users['setor']], ["ui" => $users['id']]);
                            if ($read->getResult()) {
                                $info = Metadados::getInfo($users['setor']);
                                $dicionario = Metadados::getDicionario($users['setor']);

                                if ($users['status'] == 1 && (empty($info['status']) || $read->getResult()[0][$dicionario[$info['status']]['column']] == 1)) {
                                    $user = $this->getUsuarioDataRelation($users);
                                } else {
                                    $this->setResult('Usuário Desativado!');
                                }
                                break;
                            }
                        }
                    }

                    if(empty($user) AND empty($this->result))
                        $this->setResult('Login Inválido');
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
        $sql->exeCommand("SELECT u.* FROM usuarios as u JOIN usuarios_token as t ON u.id = t.usuario WHERE t.token = '" . $this->token . "' AND u.status = 1 AND t.token_expira > " . $prazoTokenExpira);
        if ($sql->getResult()) {
            $user = $sql->getResult()[0];
            unset($user['password']);
            return $this->getUsuarioDataRelation($user);
        }

        return [];
    }

    /**
     * @param array $usuario
     * @return array
     */
    private function getUsuarioDataRelation(array $usuario): array
    {
        $this->token = $this->getToken();
        $create = new Create();
        $create->exeCreate("usuarios_token", ['token' => $this->token, "token_expira" => date("Y-m-d H:i:s"), "usuario" => $usuario['id']]);
        if($create->getResult())
            Config::setUser($this->token);

        return $create->getResult() ? $_SESSION['userlogin'] : [];
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

                    if($this->email && !empty($info[$usuario['setor']]['email'])) {
                        $where .= " AND " . $dicionarios[$usuario['setor']][$info[$usuario['setor']]['email']]['column'] . " = '{$this->email}'";

                    } elseif($this->cpf && !empty($info[$usuario['setor']]['cpf'])) {
                        $where .= " AND " . $dicionarios[$usuario['setor']][$info[$usuario['setor']]['cpf']]['column'] . " = '{$this->cpf}'";

                    } elseif($this->nome && !empty($info[$usuario['setor']]['title'])) {
                        $where .= " AND " . $dicionarios[$usuario['setor']][$info[$usuario['setor']]['title']]['column'] . " = '{$this->nome}'";

                    } elseif($this->user) {
                        if (!empty($info[$usuario['setor']]['unique'])) {
                            foreach ($info[$usuario['setor']]['unique'] as $id)
                                $where .= (empty($where) ? " AND (" : " OR ") . $dicionarios[$usuario['setor']][$id]['column'] . " = '{$this->user}'";
                        }

                        /**
                         * Mesmo que não seja informado como único, verifica campos de CPF, email e telefone
                         */
                        if (!empty($info[$usuario['setor']]['cpf']) AND (empty($info[$usuario['setor']]['unique']) OR !in_array($info[$usuario['setor']]['cpf'], $info[$usuario['setor']]['unique'])))
                            $where .= (empty($where) ? " AND (" : " OR ") . $dicionarios[$usuario['setor']][$info[$usuario['setor']]['cpf']]['column'] . " = '{$this->user}'";
                        if (!empty($info[$usuario['setor']]['email']) AND (empty($info[$usuario['setor']]['unique']) OR !in_array($info[$usuario['setor']]['email'], $info[$usuario['setor']]['unique'])))
                            $where .= (empty($where) ? " AND (" : " OR ") . $dicionarios[$usuario['setor']][$info[$usuario['setor']]['email']]['column'] . " = '{$this->user}'";
                        if (!empty($info[$usuario['setor']]['tel']) AND (empty($info[$usuario['setor']]['unique']) OR !in_array($info[$usuario['setor']]['tel'], $info[$usuario['setor']]['unique'])))
                            $where .= (empty($where) ? " AND (" : " OR ") . $dicionarios[$usuario['setor']][$info[$usuario['setor']]['tel']]['column'] . " = '{$this->user}'";

                        $where .= (!empty($where) ? ")" : "");
                    }

                    $whereUser[$usuario['setor']] = $where;
                }
            }
        }

        return $whereUser;
    }

    /**
     * Seta dados de um usuário como login de acesso
     * @param array $usuario
     */
    public function setLogin(array $usuario)
    {
        if ($usuario) {
            $usuario['token'] = $this->token;

            //disable usuario recovery password
            $up = new Update();
            $up->exeUpdate("usuarios", ["token_recovery" => null], "WHERE id = :id", "id={$usuario['id']}");

            $this->setResult($usuario);

        } elseif (empty($this->getResult())) {
            $this->setResult('Login Inválido');

            $create = new Create();
            $create->exeCreate("login_attempt", ["ip" => filter_var(Helper::getIP(), FILTER_VALIDATE_IP), "data" => date("Y-m-d H:i:s"), "username" => $this->user]);
        }
    }

    private function attemptExceded()
    {
        $ip = filter_var(Helper::getIP(), FILTER_VALIDATE_IP);
        $read = new Read();
        $read->exeRead("login_attempt", "WHERE data > DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND ip = :ip AND username = :un", ["ip" => $ip, "un" => $this->user]);
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
