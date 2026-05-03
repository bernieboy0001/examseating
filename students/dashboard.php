<?php
require_once "../config/database.php";
$db = (new Database())->connect();

$matric = $_GET['matric'] ?? "";

if(!$matric){
    header("Location: index.php");
    exit;
}

$stmt = $db->prepare("
    SELECT * FROM seating 
    WHERE matric_no = ?
    ORDER BY course, venue, seat_number
");
$stmt->execute([$matric]);



$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<title>Your Seating</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body{
    font-family:'Segoe UI',sans-serif;
    background:linear-gradient(135deg,#eef2f7,#dfe9f3);
    margin:0;
}

.container{
    max-width:900px;
    margin:40px auto;
    background:white;
    padding:30px;
    border-radius:16px;
    box-shadow:0 15px 40px rgba(0,0,0,0.1);
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

h2{
    margin:0;
    color:#2c3e50;
}

.back{
    text-decoration:none;
    background:#6c757d;
    color:white;
    padding:8px 14px;
    border-radius:8px;
    font-size:14px;
}

.back:hover{
    background:#5a6268;
}

/* Student badge */
.badge{
    display:inline-block;
    background:#2c7be5;
    color:white;
    padding:6px 12px;
    border-radius:20px;
    font-size:13px;
    margin-top:10px;
}

/* Cards */
.card{
    background:#f8f9fa;
    padding:20px;
    margin-bottom:15px;
    border-radius:12px;
    border-left:5px solid #2c7be5;
    transition:0.2s;
}

.card:hover{
    transform:translateY(-2px);
    box-shadow:0 6px 15px rgba(0,0,0,0.08);
}

.course{
    font-size:18px;
    font-weight:bold;
    color:#2c7be5;
    margin-bottom:10px;
}

.detail{
    margin-bottom:6px;
    color:#333;
}

/* Print button */
.print{
    text-align:center;
    margin-top:25px;
}

button{
    padding:12px 25px;
    background:#27ae60;
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-size:15px;
}

button:hover{
    background:#1e8449;
}

/* Empty state */
.empty{
    text-align:center;
    padding:40px;
}

.empty-icon{
    font-size:50px;
    margin-bottom:10px;
}

.empty p{
    color:#777;
}

/* Print styling */
@media print{
    body{
        background:white;
    }

    .back, .print{
        display:none;
    }

    .container{
        box-shadow:none;
        margin:0;
        padding:10px;
    }
}


/* Mobile */
@media(max-width:600px){
    .container{
        margin:10px;
        padding:20px;
    }
}
</style>
</head>

<body>

<div class="container">

<div class="header">
    <h2>Your Exam Seating</h2>
    <a href="index.php" class="back">← Back</a>
</div>

<div class="badge">
    Matric No: <?php echo htmlspecialchars($matric); ?>
</div>

<br>
<?php
$nameStmt = $db->prepare("SELECT name FROM students WHERE matric_no = ?");
$nameStmt->execute([$matric]);
$student = $nameStmt->fetch();
?>

<h3>Welcome, <?php echo $student['name']; ?></h3>
<?php if(count($results) > 0): ?>

<?php foreach($results as $r): ?>
<div class="card">

<div class="course">
    <?php echo htmlspecialchars($r['course']); ?>
</div>

<div class="detail">
    <strong>Seat:</strong> <?php echo htmlspecialchars($r['seat_number']); ?>
</div>

<div class="detail">
    <strong>Venue:</strong> <?php echo htmlspecialchars($r['venue']); ?>
</div>

<div class="detail">
    <strong>Date:</strong> 
    <?php echo date("d M Y • h:i A", strtotime($r['created_at'])); ?>
</div>

</div>
<?php endforeach; ?>

<div class="print">
    <button onclick="window.print()">🖨 Print Slip</button>
</div>

<?php else: ?>

<div class="empty">
    <div class="empty-icon">📭</div>
    <h3>No Seating Found</h3>
    <p>No exam seating has been assigned to this matric number yet. Check Matric Number or contact your lecturer. </p>
</div>

<?php endif; ?>

</div>

</body>
</html>