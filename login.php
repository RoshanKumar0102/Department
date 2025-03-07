<?php

$host="localhost";
$user="root";
$password="";
$db="user";

session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


$data=mysqli_connect($host,$user,$password,$db);

if($data===false)
{
	die("connection error");
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if username and password are set before using them
    $username = isset($_POST["username"]) ? $_POST["username"] : "";
    $password = isset($_POST["password"]) ? $_POST["password"] : "";

    if (!empty($username) && !empty($password)) { // Only proceed if both fields are filled
        // Use prepared statements to prevent SQL injection
        $stmt = $data->prepare("SELECT * FROM login WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $_SESSION["username"] = $username;

            if ($row['usertype'] == "user") {
                header("location:userhome.php");
                exit;
            } elseif ($row['usertype'] == "admin") {
                header("location:adminhome.php");
                exit;
            }
        } else {
            echo "<script>alert('Username or password is incorrect.'); window.location.href='login.php';</script>";
            exit;
        }

        $stmt->close(); // Close statement
    }
}

$data->close(); // Close database connection
?>







<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Split-Screen Login</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Arial', sans-serif;
    }

    body {
      display: flex;
      height: 100vh;
      overflow: hidden;
    }

    .left-section {
      flex: 1;
      background: url('Artificial.jpeg') no-repeat center center/cover;
      display: flex;
      justify-content: center;
      align-items: center;
      color: rgb(108, 71, 71);
      text-align: center;
    }

    .left-section h1 {
      font-size: 2.5rem;
      line-height: 1.5;
      text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.7);
    }

    .right-section {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      background: #8fa6d5;
      position: relative;
      overflow: hidden;
    }

    .shape {
      position: absolute;
      background: rgba(255, 255, 255, 0.3);
      animation: moveShapes 6s infinite alternate ease-in-out;
    }

    .shape.circle, .shape.square, .shape.triangle, .shape.hexagon {
      opacity: 0.6;
    }

    .shape.circle {
      width: 50px;
      height: 50px;
      border-radius: 50%;
    }

    .shape.square {
      width: 60px;
      height: 60px;
    }

    .shape.triangle {
      width: 0;
      height: 0;
      border-left: 40px solid transparent;
      border-right: 40px solid transparent;
      border-bottom: 70px solid rgba(255, 255, 255, 0.3);
    }

    .shape.hexagon {
      width: 70px;
      height: 70px;
      background: rgba(255, 255, 255, 0.3);
      clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
    }
    .shape.pentagon {
      width: 70px;
      height: 70px;
      background: rgba(196, 134, 134, 0.3);
      clip-path: polygon(50% 0%, 50% 25%, 100% 75%, 50% 100%, 0% 75%);
    }

    .top-left { top: 10%; left: 10%; }
    .top-right { top: 10%; right: 10%; }
    .bottom-left { bottom: 10%; left: 10%; }
    .bottom-right { bottom: 10%; right: 10%; }
    .center-left { left: 5%; top: 50%; }
    .center-right { right: 5%; top: 50%; }
    .middle-top { top: 25%; left: 50%; transform: translateX(-50%); }
    .middle-bottom { bottom: 25%; left: 50%; transform: translateX(-50%); }

    @keyframes moveShapes {
      0% { transform: translateY(0) rotate(0deg); }
      100% { transform: translateY(-40px) rotate(360deg); }
    }

    .login-container {
      width: 100%;
      max-width: 400px;
      padding: 2rem;
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      position: relative;
      z-index: 10;
    }

    .login-container h1 {
      text-align: center;
      margin-bottom: 1.5rem;
      font-size: 1.8rem;
      color: #333;
    }

    .login-container form {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .form-group {
      display: flex;
      flex-direction: column;
    }

    .form-group label {
      margin-bottom: 0.5rem;
      font-weight: bold;
      color: #555;
    }

    .form-group input {
      padding: 0.8rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 1rem;
      outline: none;
      transition: border-color 0.3s;
    }

    .form-group input:focus {
      border-color: #6366f1;
    }

    .login-container button {
      padding: 0.8rem;
      border: none;
      border-radius: 8px;
      background: #6366f1;
      color: white;
      font-size: 1rem;
      cursor: pointer;
      transition: background 0.3s;
    }
  </style>
</head>
<body>
  <div class="left-section">
    
  </div>

  <div class="right-section">
    <div class="shape circle top-left"></div>
    <div class="shape square top-right"></div>
    <div class="shape triangle bottom-left"></div>
    <div class="shape hexagon bottom-right"></div>
    <div class="shape circle center-left"></div>
    <div class="shape square center-right"></div>
    <div class="shape triangle middle-top"></div>
    <div class="shape hexagon middle-bottom"></div>
    <div class="shape pentagon middle-bottom"></div>

    <div class="login-container">
      <h1>Login</h1>
      <form action="#" method="post">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" placeholder="Enter your username" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>
        <button type="submit">Login</button>
      </form>
    </div>
  </div>
</body>
</html>