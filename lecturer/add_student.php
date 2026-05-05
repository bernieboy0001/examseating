<?php
session_start();
require_once "../config/database.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'lecturer'){
    header("Location: ../auth/login.php?role=lecturer");
    exit;
}

$db = (new Database())->connect();
$message = "";

/* ======================
   MANUAL ADD
====================== */
if(isset($_POST['add_student'])){
    $matric = trim($_POST['matric_no']);
    $name = trim($_POST['name']);
    $department = trim($_POST['department']);

    $stmt = $db->prepare("INSERT INTO students (matric_no, name, department) VALUES (?, ?, ?)");
    $stmt->execute([$matric, $name, $department]);

    $message = "Student added successfully!";
}

/* ======================
   CSV UPLOAD
====================== */
if(isset($_POST['upload'])){
    if(isset($_FILES['file']['tmp_name'])){
        $file = fopen($_FILES['file']['tmp_name'], "r");

        while(($row = fgetcsv($file)) !== FALSE){
            if(empty($row[0])) continue;

            $stmt = $db->prepare("INSERT INTO students (matric_no, name, department) VALUES (?, ?, ?)");
            $stmt->execute([$row[0], $row[1], $row[2]]);
        }

        fclose($file);
        $message = "Students uploaded successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Students</title>

<style>

/* ===== THEME ===== */
:root{
    --bg:#f4f6f9;
    --card:#ffffff;
    --text:#333;
    --primary:#2c7be5;
    --green:#27ae60;
    --border:#ddd;
}

.dark-mode{
    --bg:#121212;
    --card:#1e1e1e;
    --text:#e0e0e0;
    --primary:#4da3ff;
    --green:#2ecc71;
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

/* ===== NAV ===== */
.nav{
    background:var(--card);
    padding:12px 18px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:1px solid var(--border);
    position:sticky;
    top:0;
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
    max-width:900px;
    margin:30px auto;
    padding:20px;
}

/* ===== HEADER ===== */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

h1{
    margin:0;
}

/* ===== CARD ===== */
.card{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:12px;
    padding:20px;
    margin-bottom:25px;
}

/* ===== INPUT ===== */
input{
    width:100%;
    padding:12px;
    margin-top:10px;
    border-radius:8px;
    border:1px solid var(--border);
    background:transparent;
    color:var(--text);
}

input:focus{
    outline:none;
    border-color:var(--primary);
}

/* ===== BUTTONS ===== */
button{
    margin-top:15px;
    padding:12px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-weight:500;
    transition:0.2s;
}

button:active{
    transform:scale(0.97);
}

.btn-primary{
    background:var(--primary);
    color:white;
}

.btn-upload{
    background:var(--green);
    color:white;
}

.btn-back{
    background:#6c757d;
    color:white;
}

/* ===== MESSAGE ===== */
.message{
    background:#eafaf1;
    color:var(--green);
    padding:12px;
    border-radius:8px;
    margin-bottom:20px;
}

/* ===== NOTE ===== */
.note{
    font-size:13px;
    opacity:0.8;
}

/* ===== MOBILE ===== */
@media(max-width:600px){

    .header{
        flex-direction:column;
        align-items:flex-start;
        gap:10px;
    }

    .container{
        margin:10px;
    }

    h1{
        font-size:20px;
    }
}

</style>
</head>

<body>

<!-- NAV -->
<div class="nav">
    <div class="logo">
        <div class="logo-box">🎓</div>
        <strong>Exam System</strong>
    </div>

    <div>
        <span class="toggle" id="darkToggle">🌙</span>
    </div>
</div>

<div class="container">

<div class="header">
<h1>Student Management</h1>
<a href="dashboard.php">
<button class="btn-back">← Back</button>
</a>
</div>

<?php if($message): ?>
<div class="message">
<?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<!-- MANUAL -->
<div class="card">

<h2>Add Student</h2>

<form method="post">

<input type="text" name="matric_no" placeholder="Matric Number" required>
<input type="text" name="name" placeholder="Student Name" required>
<input type="text" name="department" placeholder="Department" required>

<button type="submit" name="add_student" class="btn-primary">
Add Student
</button>

</form>

</div>

<!-- CSV -->
<div class="card">

<h2>Upload CSV</h2>

<p class="note">
Format: matric_no, name, department
</p>

<form method="post" enctype="multipart/form-data">

<input type="file" name="file" required>

<button type="submit" name="upload" class="btn-upload">
Upload CSV
</button>

</form>

</div>

</div>

<script>

// DARK MODE GLOBAL
const toggle = document.getElementById("darkToggle");

if(localStorage.getItem("theme") === "dark"){
    document.body.classList.add("dark-mode");
    toggle.innerText = "☀️";
}

toggle.addEventListener("click", () => {
    document.body.classList.toggle("dark-mode");

    if(document.body.classList.contains("dark-mode")){
        localStorage.setItem("theme", "dark");
        toggle.innerText = "☀️";
    } else {
        localStorage.setItem("theme", "light");
        toggle.innerText = "🌙";
    }
});

</script>

</body>
</html>
