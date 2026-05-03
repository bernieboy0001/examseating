<?php ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Seating Portal</title>

<style>

/* RESET */
*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Segoe UI',sans-serif;
}

/* BACKGROUND */
body{
height:100vh;
display:flex;
justify-content:center;
align-items:center;
background:linear-gradient(1000deg,#8c7be5,#9ea8fe,#2facfe);
background-size:300% 300%;
animation:gradientMove 8s ease infinite;
}

/* ANIMATION */
@keyframes gradientMove{
0%{background-position:0% 50%;}
50%{background-position:100% 50%;}
100%{background-position:0% 50%;}
}

/* BACK BUTTON */
.back{
position:absolute;
top:20px;
left:20px;
text-decoration:none;
background:rgba(255,255,255,0.2);
color:white;
padding:10px 14px;
border-radius:8px;
backdrop-filter:blur(10px);
transition:0.3s;
}

.back:hover{
background:rgba(255,255,255,0.4);
}

/* MAIN CARD */
.container{
width:100%;
max-width:420px;
padding:40px;
border-radius:18px;
background:rgba(255,255,255,0.15);
backdrop-filter:blur(20px);
box-shadow:0 20px 50px rgba(0,0,0,0.25);
text-align:center;
animation:fadeIn 0.8s ease;
}

/* FADE */
@keyframes fadeIn{
from{opacity:0; transform:translateY(20px);}
to{opacity:1; transform:translateY(0);}
}

/* TITLE */
h2{
color:white;
margin-bottom:10px;
font-size:26px;
}

.subtitle{
color:#eaeaea;
font-size:14px;
margin-bottom:25px;
}

/* INPUT */
input{
width:100%;
padding:14px;
border:none;
border-radius:10px;
margin-top:10px;
font-size:15px;
outline:none;
transition:0.3s;
}

input:focus{
box-shadow:0 0 0 3px rgba(44,123,229,0.4);
}

/* BUTTON */
button{
width:100%;
margin-top:15px;
padding:14px;
border:none;
border-radius:10px;
background:#ffffff;
color:#2c7be5;
font-weight:bold;
font-size:16px;
cursor:pointer;
transition:0.3s;
}

button:hover{
transform:translateY(-2px);
box-shadow:0 10px 20px rgba(0,0,0,0.2);
}

/* NOTE */
.note{
margin-top:12px;
font-size:13px;
color:#ddd;
}

/* FOOTER */
.footer{
margin-top:20px;
font-size:12px;
color:#ccc;
}

</style>
</head>

<body>

<a href="../index.html" class="back">← Home</a>

<div class="container">

<h2>🎓 Exam Seating Portal</h2>
<p class="subtitle">Enter your matric number to view your seat</p>

<form action="dashboard.php" method="GET">
<input type="text" name="matric" placeholder="e.g. CSC/2023/001" required>
<button type="submit">View My Seating</button>
</form>

<p class="note">Make sure your matric number is correct</p>

<div class="footer">
© <?php echo date("Y"); ?> Exam Seating System
</div>

</div>

</body>
</html>