<?php
session_start();

// Restrict access
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'lecturer'){
    header("Location: ../auth/login.php?role=lecturer");
    exit;
}

require_once "../config/database.php";
$db = (new Database())->connect();

// Get all students
$stmt = $db->query("SELECT * FROM students ORDER BY name ASC");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Students</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<style>
body{font-family:'Roboto',sans-serif;background:#f5f7fa;margin:0;padding:0;color:#333;}
.container{width:95%;max-width:1200px;margin:20px auto;padding:20px;background:#fff;border-radius:10px;box-shadow:0 4px 15px rgba(0,0,0,0.1);}
h1,h2{color:#2c3e50;}
.dashboard-buttons{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:20px;}
.dashboard-buttons a button{flex:1;padding:15px 20px;background:#3498db;color:white;border:none;border-radius:5px;cursor:pointer;transition:0.3s;}
.dashboard-buttons a button:hover{opacity:0.9;}
#search{width:100%;max-width:400px;padding:10px 15px;border:1px solid #ccc;border-radius:5px;margin-bottom:10px;}
#suggestions{width:100%;max-width:400px;border:1px solid #ccc;border-top:none;background:white;position:absolute;z-index:100;border-radius:0 0 5px 5px;}
.suggestion-item{padding:10px;border-bottom:1px solid #eee;cursor:pointer;}
.suggestion-item:hover{background:#f1f1f1;}
table{border-collapse:collapse;width:100%;margin-top:10px;}
table th,table td{padding:12px 15px;border-bottom:1px solid #eee;}
table th{background:#3498db;color:white;text-align:left;}
table tr:hover{background:#f9f9f9;}
button.edit-btn{background:#2ecc71;color:white;padding:5px 10px;margin-right:5px;}
button.delete-btn{background:#e74c3c;color:white;padding:5px 10px;}
@media(max-width:768px){.dashboard-buttons{flex-direction:column;}table th,table td{font-size:14px;padding:8px;}}
</style>
</head>
<body>
<div class="container">
<h1>All Students</h1>
<p>Welcome, <strong><?php echo $_SESSION['username'] ?? 'Lecturer'; ?></strong> 👋</p>

<div class="dashboard-buttons">
<a href="dashboard.php"><button>Back to Dashboard</button></a>
<a href="add_student.php"><button>Add Student</button></a>
<a href="generate_students.php"><button>Randomize Seats</button></a>
</div>

<hr>

<h2>Search Students</h2>
<input type="text" id="search" placeholder="Search by name, matric no or department">
<div id="suggestions"></div>

<hr>

<h2>Students List</h2>
<table>
<tr>
<th>Matric No</th>
<th>Name</th>
<th>Department</th>
<th>Action</th>
</tr>
<?php if(count($students) > 0): ?>
<?php foreach($students as $student): ?>
<tr>
<td><?php echo $student['matric_no']; ?></td>
<td><?php echo $student['name']; ?></td>
<td><?php echo $student['department']; ?></td>
<td>
<a href="edit_student.php?id=<?php echo $student['id']; ?>"><button class="edit-btn">Edit</button></a>
<a href="delete_student.php?id=<?php echo $student['id']; ?>" onclick="return confirm('Are you sure you want to delete this student?');"><button class="delete-btn">Delete</button></a>
</td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="4">No students found</td></tr>
<?php endif; ?>
</table>
</div>

<script>
document.getElementById("search").addEventListener("keyup", function(){
    let query = this.value;
    if(query.length < 1){document.getElementById("suggestions").innerHTML=""; return;}
    fetch("search_students.php?q="+encodeURIComponent(query))
    .then(response=>response.text())
    .then(data=>{document.getElementById("suggestions").innerHTML=data;});
});
</script>
</body>
</html>