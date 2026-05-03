<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'lecturer'){
    header("Location: ../auth/login.php?role=lecturer");
    exit;
}

require_once "../config/database.php";
$db = (new Database())->connect();

$message="";

$stmt = $db->query("SELECT * FROM venue ORDER BY venue_name ASC");
$venues = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(isset($_POST['save_venue'])){

$venue_name = $_POST['venue_name'];
$rows = $_POST['seat_rows'];
$columns = $_POST['columns_count'];
$capacity = $rows * $columns;

/* update venue metrics */

$stmt = $db->prepare("UPDATE venue 
SET seat_rows=?, columns_count=?, capacity=? 
WHERE venue_name=?");

$stmt->execute([$rows,$columns,$capacity,$venue_name]);

$message="Venue metrics updated successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<title>Venue Settings</title>

<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

<style>

body{
font-family:Roboto;
background:#f5f7fb;
margin:0;
}

.container{
max-width:650px;
margin:60px auto;
background:white;
padding:35px;
border-radius:12px;
box-shadow:0 6px 18px rgba(0,0,0,0.08);
}

.header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:25px;
}

h2{
margin:0;
}

.back-btn{
background:#6c757d;
color:white;
padding:8px 14px;
border-radius:6px;
text-decoration:none;
}

.back-btn:hover{
opacity:0.9;
}

label{
font-weight:500;
}

select,input{
width:100%;
padding:10px;
margin:8px 0 18px 0;
border:1px solid #ddd;
border-radius:6px;
font-size:14px;
}

.info-box{
background:#eef4ff;
padding:15px;
border-radius:8px;
margin-bottom:20px;
font-size:14px;
}

.capacity-box{
background:#f7f7f7;
padding:10px;
border-radius:6px;
margin-bottom:20px;
}

button{
background:#2c7be5;
color:white;
padding:12px 18px;
border:none;
border-radius:6px;
cursor:pointer;
font-weight:500;
}

button:hover{
opacity:0.9;
}

.message{
color:green;
font-weight:500;
margin-bottom:15px;
}

</style>

</head>

<body>

<div class="container">

<div class="header">
<h2>Venue Configuration</h2>
<a href="dashboard.php" class="back-btn">← Back</a>
</div>

<?php if($message) echo "<p class='message'>$message</p>"; ?>

<form method="POST">

<label>Select Venue</label>

<select id="venueSelect" name="venue_name" required>

<option value="">Select Venue</option>

<?php foreach($venues as $v): ?>

<option 
value="<?php echo $v['venue_name']; ?>"
data-rows="<?php echo $v['seat_rows']; ?>"
data-columns="<?php echo $v['columns_count']; ?>">

<?php echo $v['venue_name']; ?>

</option>

<?php endforeach; ?>

</select>

<div class="info-box">
You can adjust the seating layout if the exam arrangement changes.
</div>

<label>Rows</label>
<input type="number" id="rows" name="seat_rows" required>

<label>Seats Per Row</label>
<input type="number" id="columns" name="columns_count" required>

<div class="capacity-box">
Total Capacity: <strong id="capacity">0</strong> seats
</div>

<button type="submit" name="save_venue">
Save Venue Settings
</button>

</form>

</div>

<script>

let venueSelect = document.getElementById("venueSelect");
let rowsInput = document.getElementById("rows");
let columnsInput = document.getElementById("columns");
let capacityDisplay = document.getElementById("capacity");

function calculateCapacity(){

let rows = parseInt(rowsInput.value) || 0;
let columns = parseInt(columnsInput.value) || 0;

capacityDisplay.innerText = rows * columns;

}

venueSelect.addEventListener("change", function(){

let selected = this.options[this.selectedIndex];

rowsInput.value = selected.dataset.rows || "";
columnsInput.value = selected.dataset.columns || "";

calculateCapacity();

});

rowsInput.addEventListener("input", calculateCapacity);
columnsInput.addEventListener("input", calculateCapacity);

</script>

</body>
</html>