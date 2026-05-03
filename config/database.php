<?php
class Database {
    public function connect(){
        try {
            return new PDO(
                "mysql:host=YOUR_HOST;dbname=YOUR_DB;charset=utf8mb4",
                "YOUR_USER",
                "YOUR_PASSWORD",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]
            );
        } catch(PDOException $e){
            die("Connection error: " . $e->getMessage());
        }
    }
}
