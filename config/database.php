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
    "mysql:host=trolley.proxy.rlwy.net;port=40089;dbname=railway;charset=utf8mb4",
    "root",
    "gmiSweVyGWayDGErEcxZwLtZnMcHtZEj",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
        } catch(PDOException $e){
            die("Connection error: " . $e->getMessage());
        }
    }
}
?>
