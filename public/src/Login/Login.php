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
    private $system;
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

                if (!empty($data['system_id']))
                    $this->setSystem($data['system_id']);

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
     * @param string $system
     */
    public function setSystem(string $system)
    {
        $this->system = strip_tags(trim($system));
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
            $this->user = strip_tags(trim($user));
    }

    /**
     * @param string $nome
     * @return void
     */
    public function setNome(string $nome)
    {
        if (!empty($nome))
            $this->nome = strip_tags(trim($nome));
    }

    /**
     * @param string $email
     * @return void
     */
    public function setEmail(string $email)
    {
        if (!empty($email) && Check::email($email))
            $this->email = strip_tags(trim($email));
    }

    /**
     * @param string $cpf
     * @return void
     */
    public function setCpf(string $cpf)
    {
        if (!empty($cpf) && (Check::cpf($cpf) || Check::cnpj($cpf)))
            $this->cpf = (string)str_replace([".", "-", "/"], "", strip_tags(trim($cpf)));
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
            Config::setUser($this->token);
            $this->setResult($_SESSION['userlogin']);

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

            $parseData = ["pass" => $this->senha];

            if(!empty($this->system))
                $parseData['si'] = $this->system;

            $read = new Read();
            $read->setSelect(["id", "nome", "imagem", "status", "data", "setor", "system_id"]);
            $read->exeRead("usuarios", "WHERE password = :pass" . (!empty($this->system) ? " AND system_id = :si" : "") . (!empty($this->setor) ? " AND setor IN('" . implode("','", $this->setor) . "')" : ""), $parseData);
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
                                $this->createTokenUser($users['id']);
                                Config::setUser($this->token);
                                $this->setResult($_SESSION['userlogin']);

                                $create = new Create();
                                $create->exeCreate("login_attempt", ["system_entity" => "login_valido","ip" => filter_var(Helper::getIP(), FILTER_VALIDATE_IP), "data" => date("Y-m-d H:i:s"), "username" => $this->user]);

                            } else {
                                $this->setResult('Usuário Desativado!');
                            }
                            break;
                        }
                    }
                }
            }

            if(empty($this->token)) {
                $this->setResult('Login Inválido');

                $create = new Create();
                $create->exeCreate("login_attempt", ["system_entity" => "login_invalido","ip" => filter_var(Helper::getIP(), FILTER_VALIDATE_IP), "data" => date("Y-m-d H:i:s"), "username" => $this->user]);
            }
        }
    }

    /**
     * @param int $user
     * @return void
     */
    private function createTokenUser(int $user) {

        //Mantem apenas os últimos 4 tokens desse usuário + o que esta a ser criado
        $sql = new \Conn\SqlCommand();
        $sql->exeCommand("
            DELETE FROM usuarios_token
            WHERE usuario = " . $user . "
            AND id NOT IN (
                SELECT id FROM (
                    SELECT id 
                    FROM usuarios_token 
                    WHERE usuario = " . $user . " 
                    ORDER BY id DESC 
                    LIMIT 4
                ) as temp
            )
        ");

        $this->setToken($this->getToken());
        $create = new Create();
        $create->exeCreate("usuarios_token", ['token' => $this->token, "token_expira" => date("Y-m-d H:i:s"), "usuario" => $user]);
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
