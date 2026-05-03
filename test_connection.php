<?php
require "config/database.php";

$db = (new Database())->connect();

if($db){
    echo "Database connected successfully!";
}
?>