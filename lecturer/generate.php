<?php
// KEEP YOUR ORIGINAL PHP CODE EXACTLY AS IS ABOVE
?>

<!DOCTYPE html>
<html>
<head>
<title>Exam Seating Generator</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>

/* ===== MODERN DARK THEME ===== */
:root{
    --bg:#0f172a;
    --card:#1e293b;
    --card-soft:#273449;
    --text:#e2e8f0;
    --muted:#94a3b8;
    --primary:#3b82f6;
    --danger:#ef4444;
    --success:#22c55e;
    --warning:#f59e0b;
    --border:#334155;
}

/* ===== GLOBAL ===== */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Segoe UI',sans-serif;
    background:var(--bg);
    color:var(--text);
}

/* ===== CONTAINER ===== */
.container{
    max-width:1100px;
    margin:25px auto;
    padding:20px;
}

/* ===== HEADER ===== */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.header h2{
    font-size:22px;
}

.back{
    background:var(--card-soft);
    color:white;
    padding:8px 14px;
    border-radius:8px;
    text-decoration:none;
    font-size:14px;
}

/* ===== CARD ===== */
.card{
    background:var(--card);
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;
    border:1px solid var(--border);
}

/* ===== FORM ===== */
label{
    font-weight:500;
    margin-top:15px;
    display:block;
}

input[type="text"]{
    width:100%;
    padding:12px;
    border-radius:8px;
    border:1px solid var(--border);
    background:var(--card-soft);
    color:white;
    margin-top:6px;
}

/* ===== GRID CHECKBOX ===== */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(150px,1fr));
    gap:10px;
    margin-top:10px;
}

.checkbox{
    background:var(--card-soft);
    padding:10px;
    border-radius:8px;
    border:1px solid var(--border);
    font-size:14px;
}

/* ===== BUTTONS ===== */
.buttons{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    margin-top:20px;
}

button{
    padding:12px 18px;
    border:none;
    border-radius:8px;
    font-weight:500;
    cursor:pointer;
    color:white;
}

.generate{background:var(--primary);}
.clear{background:var(--danger);}
.print{background:var(--success);}

button:hover{
    opacity:0.9;
}

/* ===== ALERTS ===== */
.alert{
    margin-top:15px;
    padding:12px;
    border-radius:8px;
    font-weight:500;
}

.success{background:#064e3b;color:#6ee7b7;}
.warning{background:#78350f;color:#fcd34d;}
.error{background:#7f1d1d;color:#fca5a5;}

/* ===== STATS ===== */
.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:15px;
}

.stat-card{
    background:var(--card);
    padding:18px;
    border-radius:12px;
    border:1px solid var(--border);
}

.stat-card h3{
    font-size:14px;
    color:var(--muted);
}

.stat-card p{
    font-size:20px;
    margin-top:5px;
}

/* ===== TABLE ===== */
.table-wrapper{
    margin-top:20px;
    overflow-x:auto;
    border-radius:10px;
}

table{
    width:100%;
    border-collapse:collapse;
    min-width:600px;
}

th{
    background:var(--primary);
    color:white;
    padding:12px;
    text-align:left;
}

td{
    padding:10px;
    border-bottom:1px solid var(--border);
}

tr:hover{
    background:var(--card-soft);
}

/* ===== MOBILE ===== */
@media(max-width:600px){

    .header{
        flex-direction:column;
        align-items:flex-start;
        gap:10px;
    }

    .buttons{
        flex-direction:column;
    }

    button{
        width:100%;
    }

    input{
        font-size:14px;
    }
}

</style>
</head>

<body>

<div class="container">

<div class="header">
<h2>Exam Seating Generator</h2>
<a href="dashboard.php" class="back">← Back</a>
</div>

<!-- FORM -->
<div class="card">
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
<button name="generate" class="generate">Generate</button>

<button name="clear_seating" class="clear"
onclick="return confirm('Clear latest seating?');">
Clear
</button>

<a href="print_seating.php" target="_blank">
<button type="button" class="print">Export</button>
</a>
</div>

</form>
</div>

<!-- ALERTS -->
<?php if($warning): ?>
<div class="alert warning"><?php echo $warning; ?></div>
<?php endif; ?>

<?php if($message): ?>
<div class="alert success"><?php echo $message; ?></div>
<?php endif; ?>

<!-- STATS -->
<div class="stats">

<div class="stat-card">
<h3>Total Students</h3>
<p><?php echo $totalSeated; ?></p>
</div>

<div class="stat-card">
<h3>By Venue</h3>
<?php foreach($venueStats as $v): ?>
<p><?php echo $v['venue']; ?>: <?php echo $v['total']; ?></p>
<?php endforeach; ?>
</div>

<div class="stat-card">
<h3>By Department</h3>
<?php foreach($deptStats as $d): ?>
<p><?php echo $d['department']; ?>: <?php echo $d['total']; ?></p>
<?php endforeach; ?>
</div>

</div>

<!-- TABLE -->
<div class="table-wrapper">
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

</div>

</body>
</html>
