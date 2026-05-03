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
$students = $db->query("SELECT * FROM students ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Get department counts
$departments = $db->query("SELECT department, COUNT(*) as total FROM students GROUP BY department ORDER BY department ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lecturer Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

<style>
/* --- Global Styles --- */
body {
    font-family: 'Roboto', sans-serif;
    background: #f0f2f5;
    margin: 0;
    padding: 0;
    color: #333;
}

.container {
    width: 95%;
    max-width: 1200px;
    margin: 30px auto;
    padding: 30px;
}

/* --- Header --- */
h1 {
    font-size: 32px;
    color: #2c3e50;
    margin-bottom: 5px;
}
p {
    margin-bottom: 25px;
    font-size: 16px;
    color: #555;
}

/* --- Cards for Quick Actions --- */
.dashboard-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.dashboard-buttons a {
    text-decoration: none;
}

.dashboard-buttons button {
    width: 100%;
    padding: 20px;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 500;
    color: #fff;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.dashboard-buttons button:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
}

/* Color coding */
button.add { background: #3498db; }
button.view { background: #1abc9c; }
button.venue { background: #9b59b6; }
button.seating { background: #e67e22; }
button.logout { background: #e74c3c; }

/* --- Search Box --- */
.search-container {
    position: relative;
    margin-bottom: 30px;
}

#search {
    width: 100%;
    max-width: 400px;
    padding: 12px 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
}

#suggestions {
    position: absolute;
    top: 50px;
    width: 100%;
    max-width: 400px;
    border: 1px solid #ccc;
    border-top: none;
    border-radius: 0 0 8px 8px;
    background: #fff;
    z-index: 100;
}

.suggestion-item {
    padding: 10px;
    cursor: pointer;
    transition: 0.2s;
}
.suggestion-item:hover { background: #f1f1f1; }

/* --- Tables --- */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

th, td {
    padding: 15px 12px;
    text-align: left;
}

th {
    background: #3498db;
    color: #fff;
    font-weight: 500;
    font-size: 14px;
}

tr:nth-child(even) { background: #f9f9f9; }

tr:hover { background: #f1f1f1; }

button.edit-btn {
    background: #2ecc71;
    color: #fff;
    padding: 5px 12px;
    border-radius: 5px;
}

button.delete-btn {
    background: #e74c3c;
    color: #fff;
    padding: 5px 12px;
    border-radius: 5px;
}

/* --- Responsive --- */
@media(max-width:768px){
    .dashboard-buttons {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 15px;
    }
    table th, table td {
        padding: 10px;
        font-size: 13px;
    }
}
</style>
</head>

<body>

<div class="container">

<h1>Lecturer Dashboard</h1>
<p>Welcome, <strong><?php echo $_SESSION['name'] ?? 'Lecturer'; ?></strong> 👋</p>
<!-- Quick Action Cards -->
<div class="dashboard-buttons">
<a href="add_student.php"><button class="add">Add Student</button></a>
<a href="view_students.php"><button class="view">View Students</button></a>
<a href="add_venue.php"><button class="venue">Add Venue</button></a>
<a href="generate.php"><button class="seating">Generate Seating</button></a>
<a href="../logout.php"><button class="logout">Logout</button></a>
</div>

<!-- Search Students -->
<div class="search-container">
<h2>Search Students</h2>
<input type="text" id="search" placeholder="Search by name, matric no or department">
<div id="suggestions"></div>
</div>

<!-- Departments Overview -->
<h2>Departments Overview</h2>
<table style="width:50%;">
<tr>
<th>Department</th>
<th>Student Count</th>
</tr>
<?php foreach($departments as $dept): ?>
<tr>
<td><?php echo $dept['department']; ?></td>
<td><?php echo $dept['total']; ?></td>
</tr>
<?php endforeach; ?>
</table>

</div>

<script>
document.getElementById("search").addEventListener("keyup", function(){
    let query = this.value;
    if(query.length < 1){
        document.getElementById("suggestions").innerHTML = "";
        return;
    }
    fetch("search_students.php?q=" + encodeURIComponent(query))
    .then(response => response.text())
    .then(data => {
        document.getElementById("suggestions").innerHTML = data;
    });
});
</script>

</body>
</html>