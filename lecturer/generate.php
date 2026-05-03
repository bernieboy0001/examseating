<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'lecturer'){
    header("Location: ../auth/login.php?role=lecturer");
    exit;
}

require_once "../config/database.php";
$db = (new Database())->connect();

$message = "";
$warning = "";

/* LOAD VENUES */
$venues = $db->query("SELECT * FROM venue ORDER BY venue_name ASC")
            ->fetchAll(PDO::FETCH_ASSOC);

/* LOAD DEPARTMENTS */
$departments = $db->query("SELECT DISTINCT department FROM students ORDER BY department ASC")
                ->fetchAll(PDO::FETCH_ASSOC);

/* ======================
   HANDLE ACTIONS
====================== */

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    // CLEAR ONLY LATEST
    if(isset($_POST['clear_seating'])){
        $db->exec("
            DELETE FROM seating 
            WHERE created_at = (
                SELECT latest FROM (
                    SELECT MAX(created_at) AS latest FROM seating
                ) temp
            )
        ");

        header("Location: generate.php?msg=cleared");
        exit;
    }

    // GENERATE
    if(isset($_POST['generate'])){

        $course = $_POST['course'];
        $selectedVenues = $_POST['venues'] ?? [];
        $selectedDepartments = $_POST['departments'] ?? [];

        if(empty($selectedVenues) || empty($selectedDepartments)){
            header("Location: generate.php?msg=error");
            exit;
        }

        /* COUNT STUDENTS */
        $placeholders = implode(',', array_fill(0, count($selectedDepartments), '?'));

        $countStmt = $db->prepare("
            SELECT COUNT(*) FROM students 
            WHERE department IN ($placeholders)
        ");
        $countStmt->execute($selectedDepartments);
        $totalStudents = $countStmt->fetchColumn();

        /* CALCULATE TOTAL CAPACITY */
        $totalCapacity = 0;

        foreach($venues as $v){
            if(in_array($v['venue_name'], $selectedVenues)){
                $totalCapacity += $v['capacity'];
            }
        }

        /* ⚠️ CAPACITY WARNING */
        if($totalStudents > $totalCapacity){
            header("Location: generate.php?msg=capacity_error");
            exit;
        }

        /* LOAD STUDENTS */
        $stmt = $db->prepare("
            SELECT * FROM students
            WHERE department IN ($placeholders)
            ORDER BY RAND()
        ");
        $stmt->execute($selectedDepartments);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $student_index = 0;

        function seatRow($i){
            $letters = range('A','Z');
            if($i < 26) return $letters[$i];
            return $letters[floor($i/26)-1] . $letters[$i%26];
        }

        foreach($venues as $venue){

            if(!in_array($venue['venue_name'], $selectedVenues)) continue;

            $rows = $venue['seat_rows'];
            $cols = $venue['columns_count'];
            $venue_name = $venue['venue_name'];

            for($r=0;$r<$rows;$r++){
                $rowLetter = seatRow($r);

                for($c=1;$c<=$cols;$c++){

                    if(!isset($students[$student_index])) break 3;

                    $student = $students[$student_index];
                    $seat = $rowLetter.$c;

                    $stmt = $db->prepare("
                        INSERT INTO seating
                        (matric_no, student_name, department, venue, seat_number, course)
                        VALUES (?,?,?,?,?,?)
                    ");

                    $stmt->execute([
                        $student['matric_no'],
                        $student['name'],
                        $student['department'],
                        $venue_name,
                        $seat,
                        $course
                    ]);

                    $student_index++;
                }
            }
        }

        header("Location: generate.php?msg=generated");
        exit;
    }
}

/* ======================
   MESSAGES
====================== */

if(isset($_GET['msg'])){
    if($_GET['msg'] === 'cleared'){
        $message = "✅ Latest seating cleared!";
    } elseif($_GET['msg'] === 'generated'){
        $message = "✅ Seating generated successfully!";
    } elseif($_GET['msg'] === 'error'){
        $message = "❌ Select at least one venue and department.";
    } elseif($_GET['msg'] === 'capacity_error'){
        $warning = "⚠️ Not enough seats! Selected venues cannot contain all students.";
    }
}

/* ======================
   STATISTICS
====================== */

$totalSeated = $db->query("SELECT COUNT(*) FROM seating")->fetchColumn();

$venueStats = $db->query("
    SELECT venue, COUNT(*) as total 
    FROM seating 
    GROUP BY venue
")->fetchAll(PDO::FETCH_ASSOC);

$deptStats = $db->query("
    SELECT department, COUNT(*) as total 
    FROM seating 
    GROUP BY department
")->fetchAll(PDO::FETCH_ASSOC);

/* LOAD SEATING */
$seating = $db->query("
    SELECT * FROM seating 
    ORDER BY created_at DESC, venue, seat_number
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
<title>Exam Seating Generator</title>

<style>
*{
margin:0;
padding:0;
box-sizing:border-box;
}

body{
font-family:'Segoe UI',sans-serif;
background:linear-gradient(135deg,#eef2f7,#dfe9f3);
}

/* CONTAINER */
.container{
max-width:1200px;
margin:40px auto;
background:white;
padding:30px;
border-radius:16px;
box-shadow:0 15px 40px rgba(0,0,0,0.08);
}

/* HEADER */
.header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:25px;
}

.header h2{
color:#2c3e50;
}

.back{
background:#6c757d;
color:white;
padding:10px 15px;
border-radius:8px;
text-decoration:none;
transition:0.3s;
}
.back:hover{background:#5a6268;}

/* FORM */
label{
font-weight:600;
margin-top:15px;
display:block;
color:#34495e;
}

input[type="text"]{
width:100%;
padding:10px;
margin-top:5px;
border-radius:8px;
border:1px solid #ccc;
}

/* GRID CHECKBOX */
.grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
gap:10px;
margin-top:10px;
}

.checkbox{
background:#f4f6f9;
padding:10px;
border-radius:8px;
border:1px solid #ddd;
cursor:pointer;
transition:0.2s;
}

.checkbox:hover{
background:#e9f2ff;
border-color:#2c7be5;
}

/* BUTTONS */
.buttons{
display:flex;
gap:10px;
margin-top:20px;
flex-wrap:wrap;
}

button{
padding:12px 18px;
border:none;
border-radius:8px;
cursor:pointer;
font-weight:600;
}

.generate{background:#2c7be5;color:white;}
.clear{background:#e74c3c;color:white;}
.print{background:#27ae60;color:white;}

button:hover{
opacity:0.9;
}

/* ALERTS */
.alert{
margin-top:15px;
padding:12px;
border-radius:8px;
font-weight:600;
}

.success{background:#e8f8f1;color:#1e8449;}
.error{background:#fdecea;color:#c0392b;}
.warning{background:#fff4e5;color:#d68910;}

/* STATS */
.stats{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:15px;
margin-top:25px;
}

.card{
background:#f8f9fa;
padding:20px;
border-radius:12px;
box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

.card h3{
margin-bottom:10px;
color:#2c3e50;
}

/* TABLE */
table{
width:100%;
margin-top:25px;
border-collapse:collapse;
border-radius:10px;
overflow:hidden;
}

th{
background:#2c7be5;
color:white;
padding:12px;
text-align:left;
}

td{
padding:10px;
border-bottom:1px solid #eee;
}

tr:hover{
background:#f7fbff;
}
</style>
</head>

<body>

<div class="container">

<!-- HEADER -->
<div class="header">
<h2>Exam Seating System</h2>
<a href="dashboard.php" class="back">← Back</a>
</div>

<!-- FORM -->
<form method="POST">

<label>Course</label>
<input type="text" name="course" required>

<label>Select Venues</label>
<div class="grid">
<?php foreach($venues as $v): ?>
<label class="checkbox">
<input type="checkbox" name="venues[]" value="<?php echo $v['venue_name']; ?>">
<?php echo $v['venue_name']; ?>
</label>
<?php endforeach; ?>
</div>

<label>Select Departments</label>
<div class="grid">
<?php foreach($departments as $d): ?>
<label class="checkbox">
<input type="checkbox" name="departments[]" value="<?php echo $d['department']; ?>">
<?php echo $d['department']; ?>
</label>
<?php endforeach; ?>
</div>

<div class="buttons">
<button name="generate" class="generate">Generate Seating</button>

<button name="clear_seating" class="clear"
onclick="return confirm('Clear latest seating?');">
Clear Latest
</button>

<a href="print_seating.php" target="_blank">
<button type="button" class="print">Export PDF</button>
</a>
</div>

</form>

<!-- ALERTS -->
<?php if($warning): ?>
<div class="alert warning"><?php echo $warning; ?></div>
<?php endif; ?>

<?php if($message): ?>
<div class="alert success"><?php echo $message; ?></div>
<?php endif; ?>

<!-- STATS -->
<div class="stats">

<div class="card">
<h3>Total Students</h3>
<p><?php echo $totalSeated; ?></p>
</div>

<div class="card">
<h3>By Venue</h3>
<?php foreach($venueStats as $v): ?>
<p><?php echo $v['venue']; ?>: <?php echo $v['total']; ?></p>
<?php endforeach; ?>
</div>

<div class="card">
<h3>By Department</h3>
<?php foreach($deptStats as $d): ?>
<p><?php echo $d['department']; ?>: <?php echo $d['total']; ?></p>
<?php endforeach; ?>
</div>

</div>

<!-- TABLE -->
<table>
<tr>
<th>Course</th>
<th>Seat</th>
<th>Name</th>
<th>Venue</th>
</tr>

<?php foreach($seating as $s): ?>
<tr>
<td><?php echo $s['course']; ?></td>
<td><?php echo $s['seat_number']; ?></td>
<td><?php echo $s['student_name']; ?></td>
<td><?php echo $s['venue']; ?></td>
</tr>
<?php endforeach; ?>

</table>

</div>

</body>
</html>