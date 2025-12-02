<?php
namespace Insi\Ssm;
require_once '../vendor/autoload.php';
use mysqli;

class DB
{
    protected $servername = '127.0.0.1';
    protected $username = 'mariadb';
    protected $password = 'mariadb';
    protected $dbname = 'monitor';
    protected $conn;

    public function __construct()
    {
        try {
            $this->conn = new mysqli(
                $this->servername,
                $this->username,
                $this->password,
                $this->dbname
            );
        } catch (\mysqli_sql_exception $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function getConn()
    {
        return $this->conn;
    }
}
