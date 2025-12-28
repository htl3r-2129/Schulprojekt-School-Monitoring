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
        $this->users = [
            // Beispiel-Benutzer TODO: user aus db auslesen
            new User("Max Musterman", "musterman@htl.rennweg.at", "password", $this->db)
        ];
    }
    
    public function login(string $username, string $password) {
        if (isset($this->users[$username]) && password_verify($password, $this->users[$username])) {
            $email = $username; 
            
            // TODO: Passwort korrekt

        }
        return false;
    }

    public function logout() {
        $_SESSION = [];
        session_destroy();
    }
    
    public function register(string $username, string $email, string $password) {
        new User($username, $email, $password, $this->db);
        // TODO: notify frontend so user is forwarded to dashboard
        return true;
    }
}