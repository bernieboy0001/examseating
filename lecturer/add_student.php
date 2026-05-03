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
    $matric = $_POST['matric_no'];
    $name = $_POST['name'];
    $department = $_POST['department'];

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
            $matric = $row[0];
            $name = $row[1];
            $department = $row[2];

            $stmt = $db->prepare("INSERT INTO students (matric_no, name, department) VALUES (?, ?, ?)");
            $stmt->execute([$matric, $name, $department]);
        }

        fclose($file);
        $message = "Students uploaded successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Students</title>

<style>

body{
font-family:'Segoe UI',sans-serif;
background:#f1f4f9;
margin:0;
}

.container{
max-width:900px;
margin:50px auto;
background:white;
padding:35px;
border-radius:10px;
box-shadow:0 10px 25px rgba(0,0,0,0.08);
}

h2{
margin-top:0;
color:#2c3e50;
}

.card{
background:#fafafa;
border:1px solid #eee;
border-radius:8px;
padding:20px;
margin-bottom:25px;
}

input{
width:100%;
padding:12px;
margin-top:10px;
border:1px solid #ccc;
border-radius:6px;
font-size:14px;
}

button{
margin-top:15px;
padding:12px 18px;
border:none;
border-radius:6px;
cursor:pointer;
font-weight:500;
}

.btn-primary{
background:#2c7be5;
color:white;
}

.btn-upload{
background:#27ae60;
color:white;
}

.btn-back{
background:#6c757d;
color:white;
}

.message{
background:#e8f8f1;
color:#27ae60;
padding:12px;
border-radius:6px;
margin-bottom:20px;
font-weight:500;
}

.note{
font-size:14px;
color:#666;
margin-top:5px;
}

.divider{
height:1px;
background:#eee;
margin:30px 0;
}

.header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:20px;
}

.header h1{
font-size:22px;
margin:0;
}

</style>

</head>

<body>

<div class="container">

<div class="header">
<h1>Student Management</h1>
<a href="dashboard.php">
<button class="btn-back">← Back to Dashboard</button>
</a>
</div>

<?php if($message): ?>
<div class="message">
<?php echo $message; ?>
</div>
<?php endif; ?>

<!-- MANUAL ADD -->

<div class="card">

<h2>Add Student Manually</h2>

<form method="post">

<input type="text" name="matric_no" placeholder="Matric Number" required>

<input type="text" name="name" placeholder="Student Name" required>

<input type="text" name="department" placeholder="Department" required>

<button type="submit" name="add_student" class="btn-primary">
Add Student
</button>

</form>

</div>

<div class="divider"></div>

<!-- CSV UPLOAD -->

<div class="card">

<h2>Upload Students via CSV</h2>

<p class="note">
CSV Format: <b>matric_no, name, department</b>
</p>

<form method="post" enctype="multipart/form-data">

<input type="file" name="file" required>

<button type="submit" name="upload" class="btn-upload">
Upload CSV File
</button>

</form>

</div>

</div>

</body>
</html>