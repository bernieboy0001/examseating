<?php
session_start();

// Restrict access
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'lecturer'){
    header("Location: ../auth/login.php?role=lecturer");
    exit;
}

require_once "../config/database.php";
$db = (new Database())->connect();

// Get department counts
$departments = $db->query("
    SELECT department, COUNT(*) as total 
    FROM students 
    GROUP BY department 
    ORDER BY department ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lecturer Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

<style>

/* --- THEME VARIABLES --- */
:root{
    --bg:#f4f6f9;
    --text:#333;
    --card:#ffffff;
    --primary:#3498db;
    --navbar:#2c3e50;
    --border:#ddd;
}

.dark-mode{
    --bg:#121212;
    --text:#e0e0e0;
    --card:#1e1e1e;
    --primary:#4da3ff;
    --navbar:#1a1a1a;
    --border:#333;
}

/* --- GLOBAL --- */
body{
    font-family:'Roboto',sans-serif;
    background:var(--bg);
    color:var(--text);
    margin:0;
    transition:0.3s;
}

/* --- NAVBAR --- */
.navbar{
    background:var(--navbar);
    color:#fff;
    padding:12px 20px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    flex-wrap:wrap;
}

.logo{
    display:flex;
    align-items:center;
    gap:10px;
}

.logo-box{
    width:42px;
    height:42px;
    background:var(--primary);
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:20px;
}

.logo-text{
    font-weight:600;
    font-size:16px;
}

.user{
    font-size:14px;
}

/* DARK BUTTON */
.toggle-btn{
    background:transparent;
    border:1px solid rgba(255,255,255,0.4);
    color:white;
    padding:6px 10px;
    border-radius:6px;
    cursor:pointer;
}

/* --- MAIN --- */
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
    opacity:0.8;
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
    transition:0.25s;
    font-size:15px;
}

.dashboard-buttons button:hover{
    transform:translateY(-4px);
    box-shadow:0 8px 20px rgba(0,0,0,0.15);
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
    border:1px solid var(--border);
    background:var(--card);
    color:var(--text);
}

#suggestions{
    background:var(--card);
    border:1px solid var(--border);
    border-top:none;
    max-width:400px;
}

.suggestion-item{
    padding:10px;
    cursor:pointer;
}
.suggestion-item:hover{
    background:rgba(0,0,0,0.05);
}

/* --- TABLE --- */
.table-wrapper{
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
    background:var(--card);
    border-radius:10px;
    overflow:hidden;
    margin-top:15px;
}

th,td{
    padding:12px;
    text-align:left;
}

th{
    background:var(--primary);
    color:white;
}

tr:nth-child(even){
    background:rgba(0,0,0,0.04);
}

/* --- MOBILE --- */
@media(max-width:768px){

    .navbar{
        flex-direction:column;
        align-items:flex-start;
        gap:10px;
    }

    .dashboard-buttons{
        grid-template-columns:repeat(2,1fr);
    }

    h1{
        font-size:22px;
    }
}

@media(max-width:480px){

    .dashboard-buttons{
        grid-template-columns:1fr;
    }

    #search{
        max-width:100%;
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

    <div style="display:flex; align-items:center; gap:10px;">
        <button id="darkToggle" class="toggle-btn">🌙</button>
        <div class="user">
            <?php echo htmlspecialchars($_SESSION['name'] ?? 'Lecturer'); ?>
        </div>
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

// SEARCH
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

// DARK MODE
const toggleBtn = document.getElementById("darkToggle");

// Load saved theme
if(localStorage.getItem("theme") === "dark"){
    document.body.classList.add("dark-mode");
    toggleBtn.innerText = "☀️";
}

toggleBtn.addEventListener("click", () => {
    document.body.classList.toggle("dark-mode");

    if(document.body.classList.contains("dark-mode")){
        localStorage.setItem("theme", "dark");
        toggleBtn.innerText = "☀️";
    } else {
        localStorage.setItem("theme", "light");
        toggleBtn.innerText = "🌙";
    }
});

</script>

</body>
</html>
