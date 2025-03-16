<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        $success = "Signup successful! You can now <a href='login.php'>Login</a>.";
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        
        .navbar {
            width: 100%;
            background: #2c3e50;
            padding: 15px 0;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            justify-content: center;
            gap: 30px;
            z-index: 1100;
        }


        .navbar a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
            padding: 10px 15px;
            transition: 0.3s;
        }

        .navbar a:hover {
            background: #1a252f;
            border-radius: 5px;
        }


        .container {
            margin-top: 80px;
        }

    </style>
</head>
<body>
    <div class="container">
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact Us</a>
    </div>
        <div class="box">
            <h2 class="title">Signup</h2>
            <?php 
                if (isset($error)) echo "<p class='error'>$error</p>";
                if (isset($success)) echo "<p class='success'>$success</p>";
            ?>
            <form method="post">
                <input type="text" name="username" placeholder="Username" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Signup</button>
            </form>
            <p>Already have an account? <a href="login.php"><button class="switch-btn">Login</button></a></p>
        </div>
    </div>
</body>
</html>
