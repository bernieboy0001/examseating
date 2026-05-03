<?php
session_start();

// Restrict access
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'lecturer'){
    header("Location: ../auth/login.php?role=lecturer");
    exit;
}

require_once "../config/database.php";
$db = (new Database())->connect();

// Get student ID from URL
$id = $_GET['id'] ?? 0;

// Fetch student details
$stmt = $db->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$student){
    die("Student not found");
}

// Handle form submission
if(isset($_POST['update'])){
    $name = trim($_POST['name']);
    $matric_no = trim($_POST['matric_no']);
    $department = trim($_POST['department']);

    // Prevent duplicate matric_no
    $check = $db->prepare("SELECT * FROM students WHERE matric_no = ? AND id != ?");
    $check->execute([$matric_no, $id]);
    if($check->rowCount() > 0){
        $error = "Matric number already exists!";
    } else {
        $update = $db->prepare("UPDATE students SET name=?, matric_no=?, department=? WHERE id=?");
        $update->execute([$name, $matric_no, $department, $id]);
        header("Location: dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Edit Student</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">
<h1>Edit Student</h1>

<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST">
    <label>Matric No:</label><br>
    <input type="text" name="matric_no" value="<?php echo htmlspecialchars($student['matric_no']); ?>" required><br><br>

    <label>Name:</label><br>
    <input type="text" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required><br><br>

    <label>Department:</label><br>
    <input type="text" name="department" value="<?php echo htmlspecialchars($student['department']); ?>" required><br><br>

    <button type="submit" name="update">Update Student</button>
</form>

<br>
<a href="dashboard.php"><button>Back to Dashboard</button></a>
</div>

</body>
</html>