<?php
namespace Insi\Ssm;
use Joshcam\MysqliDb;

class DB
{
    protected $servername = 'localhost';
    protected $username = 'mariadb';
    protected $password = 'mariadb';
    protected $dbname = 'monitor';
    protected $conn;

    public function __construct()
    {
        $this->conn = new MysqliDb($this->servername, $this->username, $this->password, $this->dbname);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        echo "Connected successfully";
    }

    public function getConn()
    {
        return $this->conn;
    }
}
