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

    public function logout() {
        setcookie("user", "", time() - 3600);
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

    public function isModerator($uuid)
    {
        $stmt = $this->db->getConn()->prepare("SELECT role FROM user WHERE PK_User_ID = ?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
        $stmt->bind_result($result);
        $stmt->fetch();

        if ($result === 1) {
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
}