<?php
class Database {

    private $host = "trolley.proxy.rlwy.net";
    private $port = "40089";
    private $db_name = "railway";
    private $username = "root";
    private $password = "gmiSweVyGWayDGErEcxZwLtZnMcHtZEj";

    public function connect(){
        try {
            return new PDO(
                "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch(PDOException $e){
            die("Connection error: " . $e->getMessage());
        }
    }
}
?>
