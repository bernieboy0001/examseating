<?php

require_once "../config/database.php";
$db = (new Database())->connect();

$query = $_GET['q'] ?? '';

if($query == ''){
exit;
}

$stmt = $db->prepare("
SELECT * FROM students
WHERE name LIKE ?
OR department LIKE ?
LIMIT 10
");

$search = "%$query%";

$stmt->execute([$search,$search]);

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

if($students){

foreach($students as $student){

echo "<div class='suggestion-item'>";
echo $student['name']." - ".$student['department'];
echo "</div>";

}

}else{

echo "<div class='suggestion-item'>No results found</div>";

}

?>