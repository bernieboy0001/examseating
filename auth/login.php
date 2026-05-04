<?php
session_start();
require_once "../config/database.php";

/* ======================
   DB CONNECTION
====================== */
$db = (new Database())->connect();
if (!$db) {
    die("Database connection failed");
}

/* ======================
   DEFAULT STATES
====================== */
$error = "";
$message = "";

/* ======================
   LOGIN
====================== */
if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {

        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {

            if (!password_verify($password, $user['password'])) {
                $error = "Incorrect password";
            } else {

                // HANDLE STATUS
                if ($user['status'] === 'pending') {
                    $error = "⏳ Awaiting admin approval.";
                } elseif ($user['status'] === 'disabled') {
                    $error = "❌ Your account has been disabled.";
                } else {

                    // LOGIN SUCCESS
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['name'] = $user['name'];

                    // REMEMBER ME
                    if (isset($_POST['remember'])) {
                        setcookie("user_email", $email, time() + (86400 * 30), "/"); // 30 days
                    } else {
                        setcookie("user_email", "", time() - 3600, "/");
                    }

                    // REDIRECT
                    switch ($user['role']) {
                        case "super_admin":
                            header("Location: ../super_admin/dashboard.php");
                            break;

                        case "lecturer":
                            header("Location: ../lecturer/dashboard.php");
                            break;

                        case "student":
                            header("Location: ../student/index.php");
                            break;

                        default:
                            $error = "Unknown role";
                    }
                    exit;
                }
            }

        } else {
            $error = "User not found";
        }

    } else {
        $error = "All fields are required";
    }
}

/* ======================
   REGISTER
====================== */
if (isset($_POST['register'])) {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    if (!empty($name) && !empty($email) && !empty($_POST['password'])) {

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
                $message = "✅ Account created. Awaiting approval.";
            } else {
                $error = "Registration failed";
            }
        }

    } else {
        $error = "All fields are required";
    }
}

/* PREFILL EMAIL */
$rememberedEmail = $_COOKIE['user_email'] ?? "";
?>
