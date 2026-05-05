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

<style>

/* ===== THEME ===== */
:root{
    --bg:#f5f7fa;
    --card:#ffffff;
    --text:#333;
    --primary:#3498db;
    --green:#2ecc71;
    --red:#e74c3c;
    --border:#ddd;
}

.dark-mode{
    --bg:#121212;
    --card:#1e1e1e;
    --text:#e0e0e0;
    --primary:#4da3ff;
    --green:#2ecc71;
    --red:#ff5c5c;
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
    width:95%;
    max-width:1200px;
    margin:20px auto;
}

/* ===== HEADER ===== */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:10px;
}

h1{
    margin:0;
}

/* ===== BUTTONS ===== */
.actions{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
}

button{
    padding:10px 14px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    color:white;
    font-size:14px;
}

.btn-back{background:#6c757d;}
.btn-add{background:var(--primary);}
.btn-seat{background:#9b59b6;}

.edit-btn{background:var(--green);}
.delete-btn{background:var(--red);}

/* ===== SEARCH ===== */
.search-box{
    margin-top:20px;
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

/* ===== TABLE ===== */
.table-wrapper{
    overflow-x:auto;
    margin-top:20px;
}

table{
    width:100%;
    border-collapse:collapse;
    background:var(--card);
    border-radius:10px;
    overflow:hidden;
}

th, td{
    padding:12px;
    text-align:left;
}

th{
    background:var(--primary);
    color:white;
}

tr:nth-child(even){
    background:rgba(0,0,0,0.03);
}

/* ===== MOBILE CARDS ===== */
@media(max-width:600px){

    table, thead, tbody, th, td, tr{
        display:block;
    }

    thead{
        display:none;
    }

    tr{
        margin-bottom:15px;
        background:var(--card);
        padding:10px;
        border-radius:10px;
    }

    td{
        display:flex;
        justify-content:space-between;
        padding:8px;
    }

    td::before{
        content:attr(data-label);
        font-weight:bold;
    }

    .actions{
        flex-direction:column;
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
    <div>
        <h1>All Students</h1>
        <p>Welcome, <strong><?php echo $_SESSION['name'] ?? 'Lecturer'; ?></strong> 👋</p>
    </div>

    <div class="actions">
        <a href="dashboard.php"><button class="btn-back">← Back</button></a>
        <a href="add_student.php"><button class="btn-add">+ Add</button></a>
        <a href="generate_students.php"><button class="btn-seat">⚙ Generate</button></a>
    </div>
</div>

<!-- SEARCH -->
<div class="search-box">
<input type="text" id="search" placeholder="Search by name, matric no or department">
<div id="suggestions"></div>
</div>

<!-- TABLE -->
<div class="table-wrapper">
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
<td data-label="Matric"><?php echo htmlspecialchars($student['matric_no']); ?></td>
<td data-label="Name"><?php echo htmlspecialchars($student['name']); ?></td>
<td data-label="Dept"><?php echo htmlspecialchars($student['department']); ?></td>
<td data-label="Action">
<a href="edit_student.php?id=<?php echo $student['id']; ?>">
<button class="edit-btn">Edit</button>
</a>
<a href="delete_student.php?id=<?php echo $student['id']; ?>" onclick="return confirm('Delete this student?');">
<button class="delete-btn">Delete</button>
</a>
</td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="4">No students found</td></tr>
<?php endif; ?>

</table>
</div>

</div>

<script>

// SEARCH
document.getElementById("search").addEventListener("keyup", function(){
    let query = this.value;

    if(query.length < 1){
        document.getElementById("suggestions").innerHTML="";
        return;
    }

    fetch("search_students.php?q="+encodeURIComponent(query))
    .then(res=>res.text())
    .then(data=>{
        document.getElementById("suggestions").innerHTML=data;
    });
});

// DARK MODE
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
