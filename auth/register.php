<?php
session_start();
require_once "../config/database.php";

$db = (new Database())->connect();

$message = "";
$error = "";

if(isset($_POST['register'])){

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $role = "lecturer";
    $status = "pending";

    $check = $db->prepare("SELECT id FROM users WHERE email=?");
    $check->execute([$email]);

    if($check->rowCount()){
        $error = "Email already exists";
    } else {
        $stmt = $db->prepare("
        INSERT INTO users(name,email,password,role,status)
        VALUES(?,?,?,?,?)
        ");
        $stmt->execute([$name,$email,$password,$role,$status]);

        $message = "Account created. Awaiting approval.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body{font-family:'Segoe UI';background:#f5f7fb;margin:0;}

.header{
background:#1f2a6c;
padding:40px;
text-align:center;
color:white;
}

.card{
background:white;
margin:-30px 15px;
padding:25px;
border-radius:20px;
}

input{
width:100%;
padding:12px;
margin-top:10px;
border-radius:10px;
border:1px solid #ddd;
}

button{
width:100%;
margin-top:15px;
padding:12px;
background:#1f2a6c;
color:white;
border:none;
border-radius:10px;
}

.success{color:green;}
.error{color:red;}
</style>
</head>

<body>

<div class="header">Create Account</div>

<div class="card">

<?php if($message): ?><p class="success"><?= $message ?></p><?php endif; ?>
<?php if($error): ?><p class="error"><?= $error ?></p><?php endif; ?>

<form method="POST">
<input type="text" name="name" placeholder="Full Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>

<button name="register">Register</button>
</form>

</div>

</body>
</html>