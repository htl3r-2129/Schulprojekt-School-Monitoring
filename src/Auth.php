<?php
namespace Insi\Ssm;
require_once '../vendor/autoload.php';
use Insi\Ssm\User;
use Insi\Ssm\DB;

class Auth {
    private $users;
    private $db;
    
    public function __construct() {
        $this->db = new DB();
//        $this->users = [
//            // Beispiel-Benutzer TODO: user aus db auslesen
//            // should contain array with user data as string, not user classes (maybe)
//            new User("Max Musterman", "musterman@htl.rennweg.at", "password", $this->db)
//        ];
    }
    
    public function login(string $email, string $password) {
        $stmt = $this->db->getConn()->prepare("SELECT password FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($result);
        $stmt->fetch();

        $password = hash('sha256', $password);
//        echo "Set password: " . $result . "<br>";
//        echo "Given password: " . $password . "<br>";
        if ($result == $password){
            return true;
        }
        return false;
    }

    public function logout() {
        $_SESSION = [];
        session_destroy();
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
}