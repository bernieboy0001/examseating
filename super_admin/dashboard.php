<?php
session_start();
require_once "../config/database.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin'){
    header("Location: ../auth/login.php");
    exit;
}

$db = (new Database())->connect();
$admin_id = $_SESSION['user_id'];

function redirect(){
    header("Location: dashboard.php");
    exit;
}

/* ======================
   ACTIONS
====================== */

if(isset($_GET['approve'])){
    $id = (int)$_GET['approve'];

    $db->prepare("UPDATE users 
        SET status='approved', approved_by=?, approved_at=NOW()
        WHERE id=?")->execute([$admin_id,$id]);

    $db->prepare("INSERT INTO audit_logs(action,user_id,performed_by)
        VALUES('Approved user',?,?)")->execute([$id,$admin_id]);

    redirect();
}

if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];

    $db->prepare("DELETE FROM users WHERE id=?")->execute([$id]);

    $db->prepare("INSERT INTO audit_logs(action,user_id,performed_by)
        VALUES('Deleted user',?,?)")->execute([$id,$admin_id]);

    redirect();
}

if(isset($_GET['disable'])){
    $id = (int)$_GET['disable'];

    $db->prepare("UPDATE users SET status='disabled' WHERE id=?")->execute([$id]);

    $db->prepare("INSERT INTO audit_logs(action,user_id,performed_by)
        VALUES('Disabled user',?,?)")->execute([$id,$admin_id]);

    redirect(); // 🔥 IMPORTANT FIX
}

if(isset($_GET['enable'])){
    $id = (int)$_GET['enable'];

    $db->prepare("UPDATE users SET status='approved' WHERE id=?")->execute([$id]);

    $db->prepare("INSERT INTO audit_logs(action,user_id,performed_by)
        VALUES('Enabled user',?,?)")->execute([$id,$admin_id]);

    redirect();
}

/* ======================
   PAGINATION + SEARCH
====================== */

$limit = 8;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? "";

$query = "
SELECT u.*, a.name AS admin_name
FROM users u
LEFT JOIN users a ON u.approved_by = a.id
WHERE u.role='lecturer'
";

$params = [];

if($search){
    $query .= " AND (u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY u.created_at DESC LIMIT $limit OFFSET $offset";

$stmt = $db->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* COUNT */
$totalRows = $db->query("SELECT COUNT(*) FROM users WHERE role='lecturer'")->fetchColumn();
$totalPages = ceil($totalRows / $limit);

/* STATS */
$total = $totalRows;
$approved = $db->query("SELECT COUNT(*) FROM users WHERE role='lecturer' AND status='approved'")->fetchColumn();
$pending = $db->query("SELECT COUNT(*) FROM users WHERE role='lecturer' AND status='pending'")->fetchColumn();
$disabled = $db->query("SELECT COUNT(*) FROM users WHERE role='lecturer' AND status='disabled'")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
<title>Super Admin Dashboard</title>

<style>
body{font-family:'Segoe UI';background:#eef2f7;margin:0;}

.container{
max-width:1200px;margin:40px auto;
background:white;padding:30px;border-radius:14px;
box-shadow:0 10px 35px rgba(0,0,0,0.1);
}

.header{
display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;
}

.actions-top{
display:flex;gap:10px;
}

.btn-top{
background:#2c7be5;color:white;padding:8px 14px;border-radius:6px;text-decoration:none;
}

.logout{background:#6c757d;}

.stats{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
gap:15px;margin-bottom:20px;
}

.card{
padding:15px;border-radius:10px;background:#f8f9fa;text-align:center;
}

.card h3{margin:0;color:#2c7be5;}

.search-box{
display:flex;gap:10px;margin-bottom:15px;
}

.search-box input{
flex:1;padding:10px;border-radius:6px;border:1px solid #ccc;
}

.search-box button{
padding:10px;background:#2c7be5;color:white;border:none;border-radius:6px;
}

table{width:100%;border-collapse:collapse;}
th{background:#2c7be5;color:white;padding:12px;}
td{padding:10px;border-bottom:1px solid #eee;}

.status{font-weight:bold;}
.pending{color:#e67e22;}
.approved{color:#27ae60;}
.disabled{color:#c0392b;}

.btn{
padding:5px 10px;border:none;border-radius:5px;cursor:pointer;font-size:12px;
}

.approve{background:#27ae60;color:white;}
.delete{background:#e74c3c;color:white;}
.disable{background:#f39c12;color:white;}
.enable{background:#2980b9;color:white;}

.actions{display:flex;gap:5px;flex-wrap:wrap;}

.pagination{text-align:center;margin-top:20px;}

.pagination a{
margin:0 5px;padding:8px 12px;
background:#2c7be5;color:white;border-radius:5px;text-decoration:none;
}
</style>
</head>

<body>

<div class="container">

<div class="header">
<h2>Super Admin Dashboard</h2>

<div class="actions-top">
<a href="logs.php" class="btn-top">📜 Activity Log</a>
<a href="../auth/login.php" class="btn-top logout">Logout</a>
</div>
</div>

<!-- STATS -->
<div class="stats">
<div class="card"><h3><?= $total ?></h3><p>Total</p></div>
<div class="card"><h3><?= $approved ?></h3><p>Approved</p></div>
<div class="card"><h3><?= $pending ?></h3><p>Pending</p></div>
<div class="card"><h3><?= $disabled ?></h3><p>Disabled</p></div>
</div>

<!-- SEARCH -->
<form method="GET" class="search-box">
<input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
<button>Search</button>
</form>

<table>
<tr>
<th>Name</th>
<th>Email</th>
<th>Status</th>
<th>Approved By</th>
<th>Date</th>
<th>Actions</th>
</tr>

<?php foreach($users as $u): 
$status = strtolower(trim($u['status']));
?>

<tr>
<td><?= htmlspecialchars($u['name']) ?></td>
<td><?= htmlspecialchars($u['email']) ?></td>

<td class="status <?= $status ?>">
<?= ucfirst($status) ?>
</td>

<td><?= $u['admin_name'] ?? '-' ?></td>

<td>
<?= !empty($u['approved_at']) ? date("d M Y", strtotime($u['approved_at'])) : '-' ?>
</td>

<td class="actions">

<?php if($status == 'pending'): ?>
<a href="?approve=<?= $u['id'] ?>"><button class="btn approve">Approve</button></a>
<?php endif; ?>

<?php if($status == 'approved'): ?>
<a href="?disable=<?= $u['id'] ?>"><button class="btn disable">Disable</button></a>
<?php endif; ?>

<?php if($status == 'disabled'): ?>
<a href="?enable=<?= $u['id'] ?>"><button class="btn enable">Enable</button></a>
<?php endif; ?>

<a href="?delete=<?= $u['id'] ?>" onclick="return confirm('Delete user?')">
<button class="btn delete">Delete</button>
</a>

</td>
</tr>

<?php endforeach; ?>
</table>

<!-- PAGINATION -->
<div class="pagination">
<?php for($i=1;$i<=$totalPages;$i++): ?>
<a href="?page=<?= $i ?>"><?= $i ?></a>
<?php endfor; ?>
</div>

</div>

</body>
</html>