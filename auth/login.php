<?php
session_start();
require_once "../config/database.php";

/* ======================
   DATABASE CONNECTION
====================== */
try {
    $db = (new Database())->connect();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

$error = "";
$message = "";

/* ======================
   LOGIN PROCESS
====================== */
if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {

        if (password_verify($password, $user['password'])) {

            if ($user['status'] !== 'approved') {
                $error = "⏳ Awaiting admin approval.";
            } else {

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];

                // ROLE REDIRECT
                if ($user['role'] === "super_admin") {
                    header("Location: ../super_admin/dashboard.php");
                } elseif ($user['role'] === "lecturer") {
                    header("Location: ../lecturer/dashboard.php");
                } else {
                    header("Location: ../student/index.php");
                }
                exit;
            }

        } else {
            $error = "Incorrect password";
        }

    } else {
        $error = "User not found";
    }
}

/* ======================
   REGISTER PROCESS
====================== */
if (isset($_POST['register'])) {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // CHECK EMAIL EXISTS
    $check = $db->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        $error = "Email already exists";
    } else {

        $stmt = $db->prepare("
            INSERT INTO users (name, email, password, role, status)
            VALUES (?, ?, ?, 'lecturer', 'pending')
        ");

        if ($stmt->execute([$name, $email, $password])) {
            $message = "Account created successfully. Awaiting approval.";
        } else {
            $error = "Registration failed. Try again.";
        }
    }
}
?> 
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../config/database.php";

$db = (new Database())->connect();

$error = "";
$message = "";
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Auth</title>

<style>
*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Segoe UI';
}

body{
background:linear-gradient(135deg,#1f2a6c,#2c7be5);
display:flex;
justify-content:center;
align-items:center;
height:100vh;
}

/* MAIN CONTAINER */
.container{
width:900px;
max-width:95%;
height:520px;
background:#fff;
border-radius:20px;
display:flex;
overflow:hidden;
box-shadow:0 20px 60px rgba(0,0,0,0.3);
position:relative;
}

/* LEFT SIDE (FORMS) */
.forms{
width:50%;
display:flex;
flex-direction:column;
justify-content:center;
padding:40px;
transition:0.5s;
}

/* RIGHT SIDE */
.side{
width:50%;
background:linear-gradient(135deg,#2c7be5,#1f2a6c);
color:white;
display:flex;
flex-direction:column;
justify-content:center;
align-items:center;
text-align:center;
padding:40px;
transition:0.5s;
}

/* TOGGLE EFFECT */
.container.active .forms{
transform:translateX(100%);
}

.container.active .side{
transform:translateX(-100%);
}

/* FORM */
form{
width:100%;
}

h2{
margin-bottom:15px;
color:#1f2a6c;
text-align:center;
}

/* INPUTS */
input{
width:100%;
padding:12px;
margin-top:10px;
border-radius:8px;
border:1px solid #ccc;
}

/* PASSWORD */
.input-group{
position:relative;
}
.toggle-pass{
position:absolute;
right:10px;
top:18px;
cursor:pointer;
}

/* BUTTON */
button{
width:100%;
margin-top:15px;
padding:12px;
border:none;
border-radius:8px;
background:#1f2a6c;
color:white;
font-weight:bold;
cursor:pointer;
}
button:hover{
background:#2c7be5;
}

/* REMEMBER */
.remember{
display:flex;
align-items:center;
gap:10px;
margin-top:12px;
font-size:14px;
}

.remember input{
appearance:none;
width:18px;
height:18px;
border:2px solid #1f2a6c;
border-radius:4px;
cursor:pointer;
position:relative;
}

.remember input:checked{
background:#1f2a6c;
}

.remember input:checked::after{
content:"✔";
color:white;
font-size:12px;
position:absolute;
top:50%;
left:50%;
transform:translate(-50%,-50%);
}

/* SIDE TEXT */
.side h2{
color:white;
}
.side button{
background:transparent;
border:1px solid white;
margin-top:15px;
}

/* ALERTS */
.error{
background:#ffe5e5;
color:red;
padding:10px;
border-radius:6px;
margin-bottom:10px;
}
.success{
background:#eafaf1;
color:green;
padding:10px;
border-radius:6px;
margin-bottom:10px;
}

/* LOGO */
.logo{
text-align:center;
margin-bottom:10px;
}
.logo img{
width:60px;
}

/* HIDE REGISTER BY DEFAULT */
#registerForm{
display:none;
}

/* MOBILE */
@media(max-width:768px){
.container{
flex-direction:column;
height:auto;
}
.forms,.side{
width:100%;
transform:none !important;
}
}
</style>
</head>

<body>

<div class="container" id="container">

<!-- LEFT SIDE (FORMS) -->
<div class="forms">

<div class="logo">
<img src="../assets/css/schoollogo2.GIF">
</div>

<!-- LOGIN -->
<!-- LOGIN -->
<form method="POST" id="loginForm">

<h2>Welcome Back</h2>

<?php if(!empty($error)): ?>
<div class="error"><?= $error ?></div>
<?php endif; ?>

<input type="email" name="email" placeholder="Email" required>

<div class="input-group">
<input type="password" name="password" id="loginPass" placeholder="Password" required>
<span class="toggle-pass" onclick="togglePass('loginPass')">👁</span>
</div>

<label class="remember">
<input type="checkbox" name="remember">
<span>Remember me</span>
</label>

<button type="submit" name="login">Login</button>

</form>

<!-- REGISTER -->
<form method="POST" id="registerForm">

<h2>Create Account</h2>

<?php if(!empty($message)): ?>
<div class="success"><?= $message ?></div>
<?php endif; ?>

<input type="text" name="name" placeholder="Full Name" required>
<input type="email" name="email" placeholder="Email" required>

<div class="input-group">
<input type="password" name="password" id="regPass" placeholder="Password" required>
<span class="toggle-pass" onclick="togglePass('regPass')">👁</span>
</div>

<button type="submit" name="register">Register</button>

</form>

</div>

<!-- RIGHT SIDE -->
<div class="side">
<h2 id="title">New here?</h2>
<p id="desc">Create your account to continue</p>
<button id="toggleBtn">Register</button>
</div>

</div>


<script>
let isLogin = true;

const btn = document.getElementById('toggleBtn');
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');
const title = document.getElementById('title');
const desc = document.getElementById('desc');
const container = document.getElementById('container');

btn.addEventListener('click', () => {

    container.classList.toggle('active');

    if(isLogin){
        loginForm.style.display = "none";
        registerForm.style.display = "block";

        btn.innerText = "Login";
        title.innerText = "Already have an account?";
        desc.innerText = "Login instead";

    } else {
        loginForm.style.display = "block";
        registerForm.style.display = "none";

        btn.innerText = "Register";
        title.innerText = "New here?";
        desc.innerText = "Create your account to continue";
    }

    isLogin = !isLogin;
});

/* PASSWORD TOGGLE */
function togglePass(id){
    let input = document.getElementById(id);
    input.type = input.type === "password" ? "text" : "password";
}
</script>

</body>
</html>
