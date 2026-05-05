<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'lecturer'){
    header("Location: ../auth/login.php?role=lecturer");
    exit;
}

require_once "../config/database.php";
$db = (new Database())->connect();

$message="";

// Fetch venues
$stmt = $db->query("SELECT * FROM venue ORDER BY venue_name ASC");
$venues = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ======================
   UPDATE VENUE
====================== */
if(isset($_POST['save_venue'])){

    $venue_id = $_POST['venue_id'];
    $rows = (int)$_POST['seat_rows'];
    $columns = (int)$_POST['columns_count'];
    $capacity = $rows * $columns;

    $stmt = $db->prepare("UPDATE venue 
        SET seat_rows=?, columns_count=?, capacity=? 
        WHERE id=?");

    if($stmt->execute([$rows,$columns,$capacity,$venue_id])){
        $message = "Venue updated successfully!";
    } else {
        $message = "Error updating venue.";
    }
}

/* ======================
   ADD NEW VENUE
====================== */
if(isset($_POST['add_venue'])){

    $name = trim($_POST['new_venue_name']);
    $rows = (int)$_POST['new_rows'];
    $columns = (int)$_POST['new_columns'];
    $capacity = $rows * $columns;

    if($name){
        $stmt = $db->prepare("INSERT INTO venue (venue_name, seat_rows, columns_count, capacity) VALUES (?, ?, ?, ?)");
        if($stmt->execute([$name,$rows,$columns,$capacity])){
            $message = "New venue created successfully!";
        } else {
            $message = "Error creating venue.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Venue Settings</title>

<style>

/* ===== THEME ===== */
:root{
    --bg:#f5f7fb;
    --card:#ffffff;
    --text:#333;
    --primary:#2c7be5;
    --border:#ddd;
}

.dark-mode{
    --bg:#121212;
    --card:#1e1e1e;
    --text:#e0e0e0;
    --primary:#4da3ff;
    --border:#333;
}

/* ===== GLOBAL ===== */
body{
    font-family:'Segoe UI',sans-serif;
    background:var(--bg);
    margin:0;
    color:var(--text);
    transition:0.3s;
}

/* ===== NAVBAR ===== */
.nav{
    background:var(--card);
    padding:12px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:1px solid var(--border);
}

.logo{
    display:flex;
    align-items:center;
    gap:10px;
}

.logo-box{
    width:38px;
    height:38px;
    background:var(--primary);
    border-radius:8px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:white;
}

.toggle{
    cursor:pointer;
    padding:6px 10px;
    border-radius:6px;
    border:1px solid var(--border);
}

/* ===== CONTAINER ===== */
.container{
    max-width:650px;
    margin:30px auto;
    background:var(--card);
    padding:30px;
    border-radius:12px;
    box-shadow:0 6px 18px rgba(0,0,0,0.08);
}

/* ===== HEADER ===== */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:10px;
}

.back-btn{
    background:#6c757d;
    color:white;
    padding:8px 14px;
    border-radius:6px;
    text-decoration:none;
}

/* ===== FORM ===== */
label{
    font-weight:500;
}

select,input{
    width:100%;
    padding:12px;
    margin:8px 0 18px;
    border:1px solid var(--border);
    border-radius:8px;
    background:var(--card);
    color:var(--text);
}

input:focus, select:focus{
    outline:none;
    border-color:var(--primary);
}

/* ===== INFO BOXES ===== */
.info-box{
    background:#eef4ff;
    padding:15px;
    border-radius:8px;
    margin-bottom:20px;
}

.dark-mode .info-box{
    background:#1c2a40;
}

.capacity-box{
    background:#f7f7f7;
    padding:12px;
    border-radius:8px;
    margin-bottom:20px;
    text-align:center;
}

.dark-mode .capacity-box{
    background:#2a2a2a;
}

/* ===== BUTTON ===== */
button{
    width:100%;
    background:var(--primary);
    color:white;
    padding:14px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-weight:500;
}

button:hover{
    opacity:0.9;
}

/* ===== MESSAGE ===== */
.message{
    background:#eafaf1;
    color:#27ae60;
    padding:12px;
    border-radius:6px;
    margin-bottom:15px;
}

/* ===== MOBILE ===== */
@media(max-width:600px){
    .container{
        margin:15px;
        padding:20px;
    }

    .header{
        flex-direction:column;
        align-items:flex-start;
    }
}

</style>

</head>

<body>

<!-- NAV -->
<div class="nav">
    <div class="logo">
        <div class="logo-box">🏫</div>
        <strong>Venue Setup</strong>
    </div>

    <span class="toggle" id="darkToggle">🌙</span>
</div>

<div class="container">

<div class="header">
<h2>Venue Configuration</h2>
<a href="dashboard.php" class="back-btn">← Back</a>
</div>

<?php if($message): ?>
<div class="message"><?php echo $message; ?></div>
<?php endif; ?>

<!-- ================= UPDATE ================= -->
<form method="POST">

<label>Select Venue</label>

<select id="venueSelect" name="venue_id" required>
<option value="">Select Venue</option>

<?php foreach($venues as $v): ?>
<option 
value="<?php echo $v['id']; ?>"
data-rows="<?php echo $v['seat_rows']; ?>"
data-columns="<?php echo $v['columns_count']; ?>">

<?php echo htmlspecialchars($v['venue_name']); ?>

</option>
<?php endforeach; ?>

</select>

<div class="info-box">
You can adjust seating layout if exam arrangement changes.
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

<hr style="margin:30px 0;">

<!-- ================= ADD NEW ================= -->
<h3>Add New Venue</h3>

<form method="POST">

<input type="text" name="new_venue_name" placeholder="Venue Name" required>
<input type="number" name="new_rows" placeholder="Rows" required>
<input type="number" name="new_columns" placeholder="Seats per Row" required>

<button type="submit" name="add_venue">
Create Venue
</button>

</form>

</div>

<script>

// CAPACITY
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

// DARK MODE (PERSISTENT)
const toggle = document.getElementById("darkToggle");

if(localStorage.getItem("theme") === "dark"){
    document.body.classList.add("dark-mode");
    toggle.innerText = "☀️";
}

toggle.addEventListener("click", ()=>{
    document.body.classList.toggle("dark-mode");

    if(document.body.classList.contains("dark-mode")){
        localStorage.setItem("theme","dark");
        toggle.innerText="☀️";
    }else{
        localStorage.setItem("theme","light");
        toggle.innerText="🌙";
    }
});

</script>

</body>
</html>
