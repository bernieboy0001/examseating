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

/* --- GLOBAL --- */
body{
    font-family:'Roboto',sans-serif;
    background:#f4f6f9;
    margin:0;
}

/* --- NAVBAR --- */
.navbar{
    background:#2c3e50;
    color:#fff;
    padding:12px 20px;
    display:flex;
    align-items:center;
    justify-content:space-between;
}

.logo{
    display:flex;
    align-items:center;
    gap:10px;
}

.logo-box{
    width:40px;
    height:40px;
    background:#3498db;
    border-radius:8px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:bold;
}

.logo-text{
    font-weight:600;
    font-size:16px;
}

.user{
    font-size:14px;
}

/* --- MAIN CONTAINER --- */
.container{
    width:95%;
    max-width:1200px;
    margin:20px auto;
}

/* --- HEADINGS --- */
h1{
    margin:10px 0 5px;
}

p{
    color:#666;
}

/* --- ACTION CARDS --- */
.dashboard-buttons{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(150px,1fr));
    gap:15px;
    margin:25px 0;
}

.dashboard-buttons a{
    text-decoration:none;
}

.dashboard-buttons button{
    width:100%;
    padding:18px;
    border:none;
    border-radius:12px;
    color:white;
    font-weight:500;
    cursor:pointer;
    transition:0.3s;
    font-size:15px;
}

.dashboard-buttons button:hover{
    transform:translateY(-3px);
}

button.add{background:#3498db;}
button.view{background:#1abc9c;}
button.venue{background:#9b59b6;}
button.seating{background:#e67e22;}
button.logout{background:#e74c3c;}

/* --- SEARCH --- */
.search-container{
    margin-bottom:30px;
}

#search{
    width:100%;
    max-width:400px;
    padding:12px;
    border-radius:8px;
    border:1px solid #ccc;
}

#suggestions{
    background:white;
    border:1px solid #ccc;
    border-top:none;
    max-width:400px;
}

.suggestion-item{
    padding:10px;
    cursor:pointer;
}
.suggestion-item:hover{
    background:#f1f1f1;
}

/* --- TABLE --- */
.table-wrapper{
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
    background:white;
    border-radius:10px;
    overflow:hidden;
    margin-top:15px;
}

th,td{
    padding:12px;
    text-align:left;
}

th{
    background:#3498db;
    color:white;
}

tr:nth-child(even){
    background:#f9f9f9;
}

/* --- MOBILE --- */
@media(max-width:768px){

    .navbar{
        flex-direction:column;
        align-items:flex-start;
        gap:8px;
    }

    .logo-text{
        font-size:14px;
    }

    .dashboard-buttons{
        grid-template-columns:repeat(2,1fr);
    }

    .dashboard-buttons button{
        padding:14px;
        font-size:14px;
    }

    h1{
        font-size:22px;
    }

    p{
        font-size:14px;
    }

    table{
        font-size:13px;
    }
}

@media(max-width:480px){

    .dashboard-buttons{
        grid-template-columns:1fr;
    }

    #search{
        width:100%;
    }
}

</style>
</head>

<body>

<!-- NAVBAR -->
<div class="navbar">

    <div class="logo">
        <div class="logo-box">🎓</div>
        <div class="logo-text">Exam Seating System</div>
    </div>

    <div class="user">
        <?php echo $_SESSION['name'] ?? 'Lecturer'; ?>
    </div>

</div>

<div class="container">

<h1>Lecturer Dashboard</h1>
<p>Manage students, venues and seating efficiently.</p>

<!-- ACTIONS -->
<div class="dashboard-buttons">
<a href="add_student.php"><button class="add">Add Student</button></a>
<a href="view_students.php"><button class="view">View Students</button></a>
<a href="add_venue.php"><button class="venue">Add Venue</button></a>
<a href="generate.php"><button class="seating">Generate Seating</button></a>
<a href="../logout.php"><button class="logout">Logout</button></a>
</div>

<!-- SEARCH -->
<div class="search-container">
<h2>Search Students</h2>
<input type="text" id="search" placeholder="Search by name, matric no or department">
<div id="suggestions"></div>
</div>

<!-- DEPARTMENTS -->
<h2>Departments Overview</h2>

<div class="table-wrapper">
<table>
<tr>
<th>Department</th>
<th>Student Count</th>
</tr>

<?php foreach($departments as $dept): ?>
<tr>
<td><?php echo htmlspecialchars($dept['department']); ?></td>
<td><?php echo $dept['total']; ?></td>
</tr>
<?php endforeach; ?>

</table>
</div>

</div>

<script>
document.getElementById("search").addEventListener("keyup", function(){
    let query = this.value;

    if(query.length < 1){
        document.getElementById("suggestions").innerHTML = "";
        return;
    }

    fetch("search_students.php?q=" + encodeURIComponent(query))
    .then(res => res.text())
    .then(data => {
        document.getElementById("suggestions").innerHTML = data;
    });
});
</script>

</body>
</html>
