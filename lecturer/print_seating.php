<?php
require_once "../config/database.php";
$db = (new Database())->connect();

/* load seating */

$seating = $db->query("
SELECT * FROM seating
ORDER BY venue, seat_number
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>

<title>Exam Seating Arrangement</title>

<style>

body{
font-family:Arial;
padding:40px;
}

h1{
text-align:center;
}

table{
width:100%;
border-collapse:collapse;
margin-top:30px;
}

th,td{
border:1px solid black;
padding:8px;
text-align:left;
}

th{
background:#eee;
}

.print-btn{
margin-top:20px;
padding:10px 15px;
background:#2c7be5;
color:white;
border:none;
cursor:pointer;
}

@media print{

.print-btn{
display:none;
}

}

</style>

</head>

<body>

<h1>Exam Seating Arrangement</h1>

<button class="print-btn" onclick="window.print()">Print</button>

<table>

<tr>
<th>Seat</th>
<th>Matric No</th>
<th>Name</th>
<th>Department</th>
<th>Venue</th>
</tr>

<?php foreach($seating as $s): ?>

<tr>

<td><?php echo $s['seat_number']; ?></td>
<td><?php echo $s['matric_no']; ?></td>
<td><?php echo $s['student_name']; ?></td>
<td><?php echo $s['department']; ?></td>
<td><?php echo $s['venue']; ?></td>

</tr>

<?php endforeach; ?>

</table>

</body>
</html>
