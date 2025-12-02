<?php
namespace App\classes;

// PHPMailer Abhängigkeiten wurden entfernt

class Auth {
    private $users; 
    private $db;
    
    // SMTP-Einstellungen wurden entfernt

    public function __construct() {
        // Beispiel-Benutzer (wird beibehalten, solange keine DB verwendet wird)
        // HINWEIS: Hier verwenden wir die E-Mail als 'username' (Key)
        $this->users = [
            'admin@htl.rennweg.at' => password_hash('admin', PASSWORD_DEFAULT),
            'maxim@htl.rennweg.at' => password_hash('maxim', PASSWORD_DEFAULT),
            'gabriel@htl.rennweg.at' => password_hash('gabriel', PASSWORD_DEFAULT),
            'timon@htl.rennweg.at' => password_hash('timon', PASSWORD_DEFAULT),
            'cemre@htl.rennweg.at' => password_hash('cemre', PASSWORD_DEFAULT),
            'thomas@htl.rennweg.at' => password_hash('thomas', PASSWORD_DEFAULT),
        ];
    }
    
    /**
     * Versendet den 2FA-Code per E-Mail mit der nativen PHP mail() Funktion.
     * @return bool True, wenn die E-Mail an den lokalen Mailserver übergeben wurde (nicht Zustellungsgarantie!).
     */
    private function _send2FACode(string $email, string $code): bool {
        
        // WICHTIG: Bitte ersetzen Sie 'noreply@ihre-domain.de' durch eine gültige Absender-Adresse.
        $from_email = 'noreply@schulmonitor.at';
        
        $subject = 'Ihr einmaliger Anmeldecode';
        $message = "Ihr 2FA-Code lautet: " . $code . "\n\nDieser Code ist 5 Minuten gültig.";
        
        // Header sind wichtig für die Zustellbarkeit (auch wenn sie gering ist)
        $headers = 'From: Schulmonitor <' . $from_email . '>' . "\r\n" .
                   'Reply-To: ' . $from_email . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();

        // Nutzt die native PHP mail() Funktion
        return mail($email, $subject, $message, $headers);
    }

    /**
     * Generiert, speichert den Code in der Session und versucht, die E-Mail zu versenden.
     */
    private function _generateAndSend2FACode(string $email, string $username): bool {
        $code = (string)random_int(100000, 999999);
        
        $_SESSION['2fa_pending_email'] = $email;
        $_SESSION['2fa_pending_username'] = $username;
        $_SESSION['2fa_code'] = $code;
        $_SESSION['2fa_expiry'] = time() + 300; // 5 Minuten Gültigkeit

        return $this->_send2FACode($email, $code);
    }

    public function login(string $username, string $password): bool {
        if (isset($this->users[$username]) && password_verify($password, $this->users[$username])) {
            $email = $username; 
            
            // Passwort korrekt: Starte den 2FA-Prozess
            return $this->_generateAndSend2FACode($email, $username);
        }
        return false;
    }

    public function verify2FA(string $code): bool {
        if (!isset($_SESSION['2fa_code']) || time() > $_SESSION['2fa_expiry']) {
            return false;
        }

        $expected_code = $_SESSION['2fa_code'];
        
        if ($code === $expected_code) {
            $final_username = $_SESSION['2fa_pending_username']; 
            
            // Bereinigung der 2FA-Session-Variablen
            unset($_SESSION['2fa_pending_email']);
            unset($_SESSION['2fa_pending_username']);
            unset($_SESSION['2fa_code']);
            unset($_SESSION['2fa_expiry']);

            session_regenerate_id(true);
            $_SESSION['username'] = $final_username; 
            return true;
        }

        return false;
    }

    public function logout(): void {
        $_SESSION = [];
        session_destroy();
    }
    
    // Die Registrierungsfunktion verwendet hier die JSON-Logik des ursprünglichen Codes.
    // Da sie nur mit einer Pfadangabe funktioniert und der Konstruktor keine mehr hat, 
    // wird die Registrierung logischerweise deaktiviert.
    public function register(string $username, string $email, string $password, string $type = 'user'): bool {
        return false; // Registrierung im aktuellen Setup deaktiviert.
    }
}