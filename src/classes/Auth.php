<?php
namespace App\classes;

class Auth {
    private $users;

    public function __construct() {
        // Passwort-Hashes frisch erzeugt mit password_hash()
        $this->users = [
            'Admin' => password_hash('123456', PASSWORD_DEFAULT),
            'Maxim' => password_hash('test123', PASSWORD_DEFAULT)
        ];
    }

    public function login(string $username, string $password): bool {
        if (isset($this->users[$username]) && password_verify($password, $this->users[$username])) {
            session_regenerate_id(true);
            $_SESSION['username'] = $username;
            return true;
        }
        return false;
    }

    public function logout(): void {
        $_SESSION = [];
        session_destroy();
    }
}
