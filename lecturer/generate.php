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

/* HANDLE ACTIONS */
if($_SERVER['REQUEST_METHOD'] === 'POST'){

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

    if(isset($_POST['generate'])){

        $course = $_POST['course'];
        $selectedVenues = $_POST['venues'] ?? [];
        $selectedDepartments = $_POST['departments'] ?? [];

        if(empty($selectedVenues) || empty($selectedDepartments)){
            header("Location: generate.php?msg=error");
            exit;
        }

        $placeholders = implode(',', array_fill(0, count($selectedDepartments), '?'));

        $countStmt = $db->prepare("SELECT COUNT(*) FROM students WHERE department IN ($placeholders)");
        $countStmt->execute($selectedDepartments);
        $totalStudents = $countStmt->fetchColumn();

        $totalCapacity = 0;
        foreach($venues as $v){
            if(in_array($v['venue_name'], $selectedVenues)){
                $totalCapacity += $v['capacity'];
            }
        }

        if($totalStudents > $totalCapacity){
            header("Location: generate.php?msg=capacity_error");
            exit;
        }

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
            return $i < 26 ? $letters[$i] : $letters[floor($i/26)-1] . $letters[$i%26];
        }

        foreach($venues as $venue){
            if(!in_array($venue['venue_name'], $selectedVenues)) continue;

            for($r=0;$r<$venue['seat_rows'];$r++){
                for($c=1;$c<=$venue['columns_count'];$c++){

                    if(!isset($students[$student_index])) break 3;

                    $student = $students[$student_index];

                    $stmt = $db->prepare("
                        INSERT INTO seating
                        (matric_no, student_name, department, venue, seat_number, course)
                        VALUES (?,?,?,?,?,?)
                    ");

                    $stmt->execute([
                        $student['matric_no'],
                        $student['name'],
                        $student['department'],
                        $venue['venue_name'],
                        seatRow($r).$c,
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

/* MESSAGES */
if(isset($_GET['msg'])){
    if($_GET['msg'] === 'cleared') $message = "✅ Latest seating cleared!";
    elseif($_GET['msg'] === 'generated') $message = "✅ Seating generated successfully!";
    elseif($_GET['msg'] === 'error') $message = "❌ Select at least one venue and department.";
    elseif($_GET['msg'] === 'capacity_error') $warning = "⚠️ Not enough seats!";
}

/* STATS */
$totalSeated = $db->query("SELECT COUNT(*) FROM seating")->fetchColumn();
$venueStats = $db->query("SELECT venue, COUNT(*) as total FROM seating GROUP BY venue")->fetchAll(PDO::FETCH_ASSOC);
$deptStats = $db->query("SELECT department, COUNT(*) as total FROM seating GROUP BY department")->fetchAll(PDO::FETCH_ASSOC);
$seating = $db->query("SELECT * FROM seating ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seating Generator</title>

<style>
:root{
    --bg:#eef2f7;
    --card:#ffffff;
    --text:#333;
    --primary:#2c7be5;
}
.dark-mode{
    --bg:#121212;
    --card:#1e1e1e;
    --text:#e0e0e0;
}

body{
    font-family:'Segoe UI',sans-serif;
    background:var(--bg);
    color:var(--text);
    margin:0;
}

/* NAV */
.nav{
    display:flex;
    justify-content:space-between;
    padding:12px 20px;
    background:var(--card);
}
.toggle{
    cursor:pointer;
}

/* CONTAINER */
.container{
    max-width:1200px;
    margin:20px auto;
    padding:20px;
    background:var(--card);
    border-radius:12px;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(140px,1fr));
    gap:10px;
}

/* BUTTONS */
button{
    padding:10px;
    border:none;
    border-radius:6px;
    cursor:pointer;
}
.generate{background:#2c7be5;color:white;}
.clear{background:#e74c3c;color:white;}
.print{background:#27ae60;color:white;}

/* TABLE */
.table-wrap{
    overflow-x:auto;
}
table{
    width:100%;
    border-collapse:collapse;
}
th,td{
    padding:10px;
}
th{
    background:#2c7be5;
    color:white;
}

/* MOBILE FIX */
@media(max-width:600px){
    .container{
        margin:10px;
        padding:15px;
    }
    h2{
        font-size:18px;
    }
}
</style>
</head>

<body>

<div class="nav">
    <strong>Seating Generator</strong>
    <span id="toggle">🌙</span>
</div>

<div class="container">

<form method="POST">

<label>Course</label>
<input type="text" name="course" required>

<label>Venues</label>
<div class="grid">
<?php foreach($venues as $v): ?>
<label>
<input type="checkbox" name="venues[]" value="<?php echo htmlspecialchars($v['venue_name']); ?>">
<?php echo htmlspecialchars($v['venue_name']); ?>
</label>
<?php endforeach; ?>
</div>

<label>Departments</label>
<div class="grid">
<?php foreach($departments as $d): ?>
<label>
<input type="checkbox" name="departments[]" value="<?php echo htmlspecialchars($d['department']); ?>">
<?php echo htmlspecialchars($d['department']); ?>
</label>
<?php endforeach; ?>
</div>

<br>

<button name="generate" class="generate">Generate</button>
<button name="clear_seating" class="clear">Clear</button>

</form>

<?php if($message): ?><p><?php echo $message; ?></p><?php endif; ?>
<?php if($warning): ?><p><?php echo $warning; ?></p><?php endif; ?>

<div class="table-wrap">
<table>
<tr>
<th>Course</th>
<th>Seat</th>
<th>Name</th>
<th>Venue</th>
</tr>

<?php foreach($seating as $s): ?>
<tr>
<td><?php echo htmlspecialchars($s['course']); ?></td>
<td><?php echo htmlspecialchars($s['seat_number']); ?></td>
<td><?php echo htmlspecialchars($s['student_name']); ?></td>
<td><?php echo htmlspecialchars($s['venue']); ?></td>
</tr>
<?php endforeach; ?>

</table>
</div>

</div>

<script>
const toggle = document.getElementById("toggle");

if(localStorage.getItem("theme") === "dark"){
    document.body.classList.add("dark-mode");
    toggle.innerText="☀️";
}

toggle.onclick = ()=>{
    document.body.classList.toggle("dark-mode");

    if(document.body.classList.contains("dark-mode")){
        localStorage.setItem("theme","dark");
        toggle.innerText="☀️";
    }else{
        localStorage.setItem("theme","light");
        toggle.innerText="🌙";
    }
};
</script>

</body>
</html>
