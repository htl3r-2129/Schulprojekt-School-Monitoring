<?php
namespace Insi\Ssm;
require_once '../vendor/autoload.php';
use mysqli;

class DB
{
    protected $servername = 'mariadb'; // Do not use localhost. It will try to connect via socket that does not exist.
    protected $username = 'mariadb';
    protected $password = 'mariadb';
    protected $dbname = 'monitor';
    protected $port = 3306;
    protected $conn;

    public function __construct()
    {
        try {
            $this->conn = new mysqli(
                $this->servername,
                $this->username,
                $this->password,
                $this->dbname,
                $this->port
            );
        } catch (\mysqli_sql_exception $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function getConn()
    {
        return $this->conn;
    }

    public function status()
    {
        return $this->conn->ping();
    }
}
