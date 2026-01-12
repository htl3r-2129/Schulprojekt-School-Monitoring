<?php
namespace Insi\Ssm;
require_once '../vendor/autoload.php';
use Insi\Ssm\User;
use Insi\Ssm\DB;

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new DB();
    }
    
    public function login(string $email, string $password) {
        $stmt = $this->db->getConn()->prepare("SELECT password FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($result);
        $stmt->fetch();

        $password = hash('sha256', $password);
        if ($result == $password){
            return true;
        }
        return false;
    }

    public function register(string $username, string $email, string $password) {
        $stmt = $this->db->getConn()->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if (!$stmt->fetch()){
            new User($username, $email, $password, $this->db);
            return true;
        }
        // TODO: show the error in a more pleasant way
        return false;
    }

    public function getUUID(string $email)
    {
        $stmt = $this->db->getConn()->prepare("SELECT PK_User_ID FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($result);
        $stmt->fetch();

        return $result;
    }

    public function getUsername(string $uuid)
    {
        $stmt = $this->db->getConn()->prepare("SELECT username FROM user WHERE PK_User_ID = ?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
        $stmt->bind_result($result);
        $stmt->fetch();

        return $result;
    }

    public function getAllMods()
    {
        $stmt = $this->db->getConn()->prepare("SELECT username, email, PK_User_ID FROM user WHERE role = 1");
        $stmt->execute();

        $result = $stmt->get_result();
        $mods = $result->fetch_all(MYSQLI_ASSOC);

        return $mods;
    }

    public function getAllUsers()
    {
        $stmt = $this->db->getConn()->prepare("SELECT username, email, PK_User_ID FROM user WHERE role = 0 AND isLocked = false");
        $stmt->execute();

        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);

        return $users;
    }

    public function getAllLocked()
    {
        $stmt = $this->db->getConn()->prepare("SELECT username, email, PK_User_ID FROM user WHERE isLocked = true");
        $stmt->execute();

        $result = $stmt->get_result();
        $locked = $result->fetch_all(MYSQLI_ASSOC);

        return $locked;
    }

    public function isModerator($uuid)
    {
        $stmt = $this->db->getConn()->prepare("SELECT role FROM user WHERE PK_User_ID = ?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
        $stmt->bind_result($result);
        $stmt->fetch();

        if ($result >= 1) {
            return true;
        }

        return false;
    }

    public function isAdmin($uuid)
    {
        $stmt = $this->db->getConn()->prepare("SELECT role FROM user WHERE PK_User_ID = ?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
        $stmt->bind_result($result);
        $stmt->fetch();

        if ($result === 2) {
            return true;
        }

        return false;
    }

    public function makeUser($uuid)
    {
        $stmt = $this->db->getConn()->prepare("update user set role = 0 where PK_User_ID like ?;");
        $stmt->bind_param("s", $uuid);

        if ($stmt->execute()){
            return true;
        }
        return false;
    }

    public function makeModerator($uuid)
    {
        $stmt = $this->db->getConn()->prepare("update user set role = 1 where PK_User_ID like ?;");
        $stmt->bind_param("s", $uuid);

        if ($stmt->execute()){
            return true;
        }
        return false;
    }

    public function makeAdmin($uuid)
    {
        $stmt = $this->db->getConn()->prepare("update user set role = 2 where PK_User_ID like ?;");
        $stmt->bind_param("s", $uuid);

        if ($stmt->execute()){
            return true;
        }
        return false;
    }

    public function lockUser($uuid)
    {
        $stmt = $this->db->getConn()->prepare("update user set isLocked = true where PK_User_ID like ?;");
        $stmt->bind_param("s", $uuid);

        if ($stmt->execute()){
            return true;
        }
        return false;
    }

    public function unlockUser($uuid)
    {
        $stmt = $this->db->getConn()->prepare("update user set isLocked = false where PK_User_ID like ?;");
        $stmt->bind_param("s", $uuid);

        if ($stmt->execute()){
            return true;
        }
        return false;
    }

    public function sendTwoFaEmail($uuid)
    {
//        TODO: smtp-Server is required to send emails
//        $msg = "This is a test email for 2-Factor-Authentication for the School Monitor Project.";
//        $msg = wordwrap($msg, 70);
//
//        mail($this->email, "2 Factor Code", $msg);
    }

    public function sendResetPasswordEmail($uuid)
    {

    }

    public function approve2Fa($uuid)
    {
        $stmt = $this->db->getConn()->prepare("update user set 2faSuccess = true where PK_User_ID like ?;");
        $stmt->bind_param("s", $uuid);

        if ($stmt->execute()){
            return true;
        }
        return false;
    }

    public function deleteUser($uuid)
    {
        $stmt = $this->db->getConn()->prepare("delete from user where PK_User_ID like ?;");
        $stmt->bind_param("s", $uuid);

        if ($stmt->execute()){
            return true;
        }
        return false;
    }
}