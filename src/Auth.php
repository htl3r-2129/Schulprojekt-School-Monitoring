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
    
    public function login(string $username, string $password) {
        // TODO: Passwort korrekt
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
            // TODO: notify frontend so user is forwarded to dashboard
            return true;
        }
        echo "Error: User with that email already exists.";
        // TODO: show the error in a more pleasant way
        return false;
    }
}