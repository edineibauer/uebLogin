<?php

namespace Login;

use Config\Config;
use Conn\Read;
use Conn\Update;
use Conn\Create;
use Entity\Metadados;
use Helpers\Check;
use Helpers\Helper;

class Login
{
    private $user;
    private $email;
    private $cpf;
    private $nome;
    private $senha;
    private $token;
    private $setor;
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
                return explode(" ", trim(strip_tags($item)))[0];
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

            $user = [];
            $read = new Read();
            $read->setSelect(["id", "nome", "imagem", "status", "data", "setor", "system_id"]);
            $read->exeRead("usuarios", "WHERE password = :pass" . (!empty($this->setor) ? " AND setor IN('" . implode("','", $this->setor) . "')" : ""), ["pass" => $this->senha]);
            if ($read->getResult()) {
                $usuarios = $read->getResult();

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
        if(strpos($this->token, "T!") === 0) {
            return [
                "id" => str_replace("T!", "", $this->token),
                "nome" => "Anônimo " . substr($this->token, - 6),
                "imagem" => null,
                "status" => 1,
                "setor" => 0,
                "setorData" => []
            ];
        } else {
            $prazoTokenExpira = date('Y-m-d', strtotime("-12 months", strtotime(date("Y-m-d"))));
            $sql = new \Conn\SqlCommand();
            $sql->exeCommand("SELECT u.* FROM usuarios as u JOIN usuarios_token as t ON u.id = t.usuario WHERE t.token = '" . $this->token . "' AND u.status = 1 AND t.token_expira > " . $prazoTokenExpira);
            if ($sql->getResult()) {
                $user = $sql->getResult()[0];
                unset($user['password']);
                return $this->getUsuarioDataRelation($user);
            }
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

        /**
         * Mantem apenas os últimos 2 tokens desse usuário + o que esta a ser criado
         */
        $sql = new \Conn\SqlCommand();
        $sql->exeCommand("
            DELETE FROM usuarios_token
            WHERE usuario = " . $usuario['id'] . "
            AND id NOT IN (
                SELECT id FROM (
                    SELECT id 
                    FROM usuarios_token 
                    WHERE usuario = " . $usuario['id'] . " 
                    ORDER BY id DESC 
                    LIMIT 2
                ) as temp
            )
        ");

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
            if(strpos($this->token, "T!") === 0) {
                $up = new Update();
                $up->exeUpdate("usuarios", ["token_recovery" => null], "WHERE id = :id", ["id" => $usuario['id']]);
            }

            $this->setResult($usuario);

            $create = new Create();
            $create->exeCreate("login_attempt", ["system_entity" => "login_valido","ip" => filter_var(Helper::getIP(), FILTER_VALIDATE_IP), "data" => date("Y-m-d H:i:s"), "username" => $this->user]);

        } elseif (empty($this->getResult())) {
            $this->setResult('Login Inválido');

            $create = new Create();
            $create->exeCreate("login_attempt", ["system_entity" => "login_invalido","ip" => filter_var(Helper::getIP(), FILTER_VALIDATE_IP), "data" => date("Y-m-d H:i:s"), "username" => $this->user]);
        }
    }

    private function attemptExceded()
    {
        $read = new Read();
        $read->exeRead("login_attempt", "WHERE system_entity = 'login_invalido' AND data > DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND ip = :ip AND username = :un", ["ip" => filter_var(Helper::getIP(), FILTER_VALIDATE_IP), "un" => $this->user]);
        $this->attempts = $read->getRowCount();

        return ($this->attempts > 10); // maximo de 10 tentativas por IP e email iguais em um intervalo de 15 minutos
    }

    private function getToken() {
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_';
        $sequencia = '';
        $max = strlen($caracteres) - 1;

        for ($i = 0; $i < 96; $i++)
            $sequencia .= $caracteres[random_int(0, $max)];

        return $sequencia;
    }
}
