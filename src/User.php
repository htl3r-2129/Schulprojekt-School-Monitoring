<?php
namespace Insi\Ssm;
require_once '../vendor/autoload.php';
use Insi\Ssm\DB;

class User
{
    protected $uuid;
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

    public function makeUser()
    {
        $stmt = $this->db->getConn()->prepare("update user set role = 0 where PK_User_ID like ?;");
        $stmt->bind_param("s", $this->uuid);
        $stmt->execute();
    }

    public function makeModerator()
    {
        $stmt = $this->db->getConn()->prepare("update user set role = 1 where PK_User_ID like ?;");
        $stmt->bind_param("s", $this->uuid);
        $stmt->execute();
    }

    public function makeAdmin()
    {
        $stmt = $this->db->getConn()->prepare("update user set role = 2 where PK_User_ID like ?;");
        $stmt->bind_param("s", $this->uuid);
        $stmt->execute();
    }

    public function lockUser()
    {
        $stmt = $this->db->getConn()->prepare("update user set isLocked = true where PK_User_ID like ?;");
        $stmt->bind_param("s", $this->uuid);
        $stmt->execute();
    }

    public function unlockUser()
    {
        $stmt = $this->db->getConn()->prepare("update user set isLocked = false where PK_User_ID like ?;");
        $stmt->bind_param("s", $this->uuid);
        $stmt->execute();
    }

    public function sendTwoFaEmail()
    {

    }

    public function sendResetPasswordEmail()
    {

    }

    public function approve2Fa()
    {
        $stmt = $this->db->getConn()->prepare("update user set 2faSuccess = true where PK_User_ID like ?;");
        $stmt->bind_param("s", $this->uuid);
        $stmt->execute();
    }

    public function deleteUser()
    {
        $stmt = $this->db->getConn()->prepare("delete from user where PK_User_ID like ?;");
        $stmt->bind_param("s", $this->uuid);
        $stmt->execute();
    }
}