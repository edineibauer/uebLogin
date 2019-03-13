<?php

namespace Login;

use Conn\Read;
use Conn\TableCrud;
use Conn\Update;
use Entity\Dicionario;
use Entity\Entity;
use Helpers\Check;
use Helpers\Helper;
use ReCaptcha\ReCaptcha;

class Login
{
    private $user;
    private $senha;
    private $recaptcha;
    private $attempts = 0;
    private $result;

    /**
     * @param mixed $data
     */
    public function __construct($data = null)
    {
        if ($data) {
            if (isset($data['recaptcha']) && !empty($data['recaptcha']))
                $this->setRecaptcha($data['recaptcha']);
            if (isset($data['user']) && !empty($data['user']))
                $this->setUser($data['user']);
            if (isset($data['password']) && !empty($data['password']))
                $this->setSenha($data['password']);
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
     * @param string $email
     */
    public function setUser($user)
    {
        if (!empty($user))
            $this->user = (string)strip_tags(trim($user));
        $this->start();
    }

    /**
     * @param string $senha
     */
    public function setSenha($senha)
    {
        if (!empty($senha) && strlen($senha) > 3)
            $this->senha = (string)Check::password(trim($senha));
        else
            $this->setResult('Senha muito Curta');

        $this->start();
    }

    /**
     * @param mixed $recaptcha
     */
    public function setRecaptcha($recaptcha)
    {
        $this->recaptcha = $recaptcha;
        $this->start();
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
        if ($this->user && $this->senha && !$this->attemptExceded()) {
            if (!empty($_SESSION['userlogin']))
                $this->setResult('Você já esta logado.');
            elseif ($this->isHuman())
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
            $dicionarios = Entity::dicionario(null, !0);

            $whereUser = [];
            foreach ($dicionarios as $entity => $dados) {
                if (empty($whereUser[$entity]) && !empty($dados['info']['user']) && $dados['info']['user'] === 1) {
                    $whereUser[$entity] = "WHERE usuarios_id = :id";
                    $where = "";
                    if (!empty($dados['info']['unique'])) {
                        foreach ($dados['info']['unique'] as $id)
                            $where .= (empty($where) ? " && (" : " || ") . $dados['dicionario'][$id]['column'] . " = '{$this->user}'";
                    }
                    $whereUser[$entity] .= $where . (!empty($where) ? ")" : "");
                }
            }

            $user = null;
            $read = new Read();
            $read->exeRead(PRE . "usuarios", "WHERE password = :pass", "pass={$this->senha}");
            if ($read->getResult()) {
                foreach ($read->getResult() as $users) {
                    if (strtolower($users['nome']) === strtolower($this->user)) {
                        if($users['status'] === "1") {
                            if (!empty($users['setor'])) {
                                $read->exeRead($users['setor'], "WHERE usuarios_id = :uid", "uid={$users['id']}");
                                $users['setorData'] = $read->getResult() ? $read->getResult()[0] : "";
                            } else {
                                $users['setor'] = "admin";
                                $users['setorData'] = "";
                            }
                            $user = $users;
                        } else {
                            $this->setResult('Usuário Desativado!');
                        }
                        break;
                    } elseif (!empty($users['setor']) && !empty($whereUser[$users['setor']])) {
                        $read->exeRead($users['setor'], $whereUser[$users['setor']], "id={$users['id']}");
                        if ($read->getResult()) {
                            if($users['status'] === "1") {
                                $users['setorData'] = $read->getResult()[0];
                                $user = $users;
                            } else {
                                $this->setResult('Usuário Desativado!');
                            }
                            break;
                        }
                    }
                }

                //busca informações do grupo de usuário pertencente
                if(!empty($user['setorData'])) {
                    foreach ($dicionarios[$user['setor']]['dicionario'] as $meta) {
                        if($meta['format'] === "list" && $dicionarios[$meta['relation']]['info']['user'] === 2 && !empty($user['setorData'][$meta['column']])) {
                            $read->exeRead($meta['relation'], "WHERE id = :rid", "rid={$user['setorData'][$meta['column']]}");
                            $user['groupData'] = ($read->getResult() ? $read->getResult()[0] : "");
                        }
                    }
                } else {
                    $user['groupData'] = "";
                }
            }

            if ($user) {
                $this->setLogin($user);
            } elseif(empty($this->getResult())) {
                $this->setResult('Login Inválido!');

                $attempt = new TableCrud("login_attempt");
                $attempt->loadArray(array("ip" => filter_var(Helper::getIP(), FILTER_VALIDATE_IP), "data" => date("Y-m-d H:i:s"), "username" => $this->user));
                $attempt->save();
            }
        }
    }

    public function logOut()
    {
        if (isset($_SESSION['userlogin'])) {
            if (!empty($_SESSION['userlogin']['token']) || (isset($_COOKIE['token']) && $_COOKIE['token'] !== "0")) {
                $t = !empty($_SESSION['userlogin']['token']) ? $_SESSION['userlogin']['token'] : $_COOKIE['token'];
                $token = new TableCrud("usuarios");
                $token->load("token", $t);
                if ($token->exist()) {
                    $token->setDados(["token" => null, "token_expira" => null]);
                    $token->save();
                }
            }
        }
        session_unset();

        $this->setCookie("token", 0, -1);
        $this->setCookie("id", 0, -1);
        $this->setCookie("nome", 0, -1);
        $this->setCookie("imagem", 0, -1);
        $this->setCookie("setor", 0, -1);
    }

    /**
     * Seta dados de um usuário como login de acesso
     * @param array $usuario
     */
    public function setLogin(array $usuario)
    {
        $_SESSION['userlogin'] = $usuario;
        $_SESSION['userlogin']['imagem'] = "";
        $_SESSION['userlogin']['token'] = $this->getToken();

        //atualiza banco com token
        $up = new Update();
        $up->exeUpdate("usuarios", ['token' => $_SESSION['userlogin']['token'], "token_expira" => date("Y-m-d H:i:s"), "token_recovery" => null], "WHERE id = :id", "id={$_SESSION['userlogin']['id']}");

        //Cookies
        $this->setCookie("token", 0, -1);
        $this->setCookie("id", 0, -1);
        $this->setCookie("nome", 0, -1);
        $this->setCookie("imagem", 0, -1);
        $this->setCookie("setor", 0, -1);
    }

    /**
     * @param string $imagem
     * @return string
     */
    /*private function getImagem(string $imagem): string
    {
        if (!empty($imagem) && Check::isJson($imagem)) {
            $imagem = json_decode($imagem, true);

            if (!empty($imagem[0]) && !empty($imagem[0]['image']))
                return $imagem[0]['image'];
        }

        return "";
    }*/

    private function attemptExceded()
    {
        $ip = filter_var(Helper::getIP(), FILTER_VALIDATE_IP);
        $read = new Read();
        $read->exeRead(PRE . "login_attempt", "WHERE data > DATE_SUB(NOW(), INTERVAL 15 MINUTE) && ip = '{$ip}' && email = '{$this->user}'");
        $this->attempts = $read->getRowCount();

        return ($this->attempts > 10); // maximo de 10 tentativas por IP e email iguais em um intervalo de 15 minutos
    }

    private function isHuman()
    {
        if (defined("RECAPTCHA") && $this->attempts < 6) {
            if (empty($this->recaptcha))
                $this->setResult("resolva o captcha");

            $recaptcha = new ReCaptcha(RECAPTCHA);
            $resp = $recaptcha->verify($this->recaptcha, filter_var(Helper::getIP(), FILTER_VALIDATE_IP));
            if (!$resp->isSuccess())
                $this->setResult('<p>' . implode('</p><p>', $resp->getErrorCodes()) . '</p>');
        }

        return $this->getResult() ? false : true;
    }

    private function setCookie($name, $value, int $dias = 60)
    {
        $tempo = $dias < 0 ? time() - 1 : time() + (86400 * $dias);
        setcookie($name, $value, $tempo, "/"); // 2 meses de cookie
    }

    /**
     * @return string
     */
    private function getToken()
    {
        return md5("tokes" . rand(9999, 99999) . md5(base64_encode(date("Y-m-d H:i:s"))) . rand(0, 9999));
    }
}
