<?php
namespace Insi\Ssm;
require_once '../vendor/autoload.php';
use Insi\Ssm\DB;

class User
{
    protected $uuid;

    public function getUuid(): string
    {
        return $this->uuid;
    }
    protected $username;
    protected $email;
    protected $password;
    protected $role = 0;
    protected $isLocked = false;
    protected $twoFaSuccess = false;
    protected $db;

    public function __construct($username, $email, $password, $database)
    {
        $this->db = $database; //It is absolutely vital that only one db connection is created
        $this->uuid = uniqid("user-");
        $this->username=$username;
        $this->email=$email;
        $this->password=hash('sha256', $password);

        $stmt = $this->db->getConn()->prepare("insert into user (PK_User_ID, username, email, password, role, isLocked, 2faSuccess) values (?, ?, ?, ?, 0, false, false)");
        $stmt->bind_param("ssss", $this->uuid, $username, $email, $this->password);
        $stmt->execute();
    }

    public function toString()
    {
        return 'UUID: ' . $this->uuid . '; Username: ' . $this->username . '; E-Mail: ' . $this->email . '; Password(gehasht): ' . $this->password;
    }
}