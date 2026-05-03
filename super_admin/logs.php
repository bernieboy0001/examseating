<?php
session_start();
require_once "../config/database.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin'){
    header("Location: ../auth/login.php");
    exit;
}

$db = (new Database())->connect();

/* ======================
   CLEAR LOGS
====================== */
if(isset($_GET['clear'])){
    $db->exec("TRUNCATE TABLE audit_logs");
    header("Location: activity.php?msg=cleared");
    exit;
}

/* ======================
   LOAD LOGS
====================== */
$logs = $db->query("
SELECT l.*, u.name as user_name, a.name as admin_name
FROM audit_logs l
LEFT JOIN users u ON l.user_id = u.id
LEFT JOIN users a ON l.performed_by = a.id
ORDER BY l.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

$message = "";
if(isset($_GET['msg']) && $_GET['msg'] === 'cleared'){
    $message = "✅ All logs cleared successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Activity Logs</title>

<style>
body{
font-family:'Segoe UI';
background:#eef2f7;
margin:0;
}

/* CONTAINER */
.container{
max-width:1100px;
margin:40px auto;
background:white;
padding:30px;
border-radius:14px;
box-shadow:0 10px 30px rgba(0,0,0,0.1);
}

/* HEADER */
.header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:20px;
}

.buttons{
display:flex;
gap:10px;
}

.btn{
padding:8px 14px;
border-radius:6px;
text-decoration:none;
color:white;
font-size:14px;
}

.back{background:#2c7be5;}
.clear{background:#e74c3c;}
.logout{background:#6c757d;}

/* MESSAGE */
.message{
background:#e8f8f5;
color:#27ae60;
padding:10px;
border-radius:6px;
margin-bottom:15px;
}

/* TABLE */
table{
width:100%;
border-collapse:collapse;
margin-top:10px;
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
background:#f9fbff;
}

/* BADGES */
.badge{
padding:4px 8px;
border-radius:6px;
font-size:12px;
font-weight:bold;
}

.approve{background:#d4edda;color:#155724;}
.delete{background:#f8d7da;color:#721c24;}
.disable{background:#fff3cd;color:#856404;}
.enable{background:#d1ecf1;color:#0c5460;}

/* EMPTY */
.empty{
text-align:center;
padding:20px;
color:#888;
}
</style>
</head>

<body>

<div class="container">

<div class="header">
<h2>📜 Activity Logs</h2>

<div class="buttons">
<a href="dashboard.php" class="btn back">← Dashboard</a>

<a href="" class="btn clear"
onclick="return confirm('Clear ALL logs? This cannot be undone!')">
Clear Logs
</a>

<a href="../auth/logout.php" class="btn logout">Logout</a>
</div>
</div>

<?php if($message): ?>
<div class="message"><?= $message ?></div>
<?php endif; ?>

<table>
<tr>
<th>Action</th>
<th>User</th>
<th>Performed By</th>
<th>Date</th>
</tr>

<?php if(count($logs) > 0): ?>

<?php foreach($logs as $log): 

$action = strtolower($log['action']);
$badgeClass = '';

if(strpos($action, 'approved') !== false) $badgeClass = 'approve';
elseif(strpos($action, 'deleted') !== false) $badgeClass = 'delete';
elseif(strpos($action, 'disabled') !== false) $badgeClass = 'disable';
elseif(strpos($action, 'enabled') !== false) $badgeClass = 'enable';

?>

<tr>

<td>
<span class="badge <?= $badgeClass ?>">
<?= htmlspecialchars($log['action']) ?>
</span>
</td>

<td><?= htmlspecialchars($log['user_name'] ?? 'Unknown') ?></td>

<td><?= htmlspecialchars($log['admin_name'] ?? 'System') ?></td>

<td><?= date("d M Y, H:i", strtotime($log['created_at'])) ?></td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>
<td colspan="4" class="empty">No activity recorded yet</td>
</tr>

<?php endif; ?>

</table>

</div>

</body>
</html>
