<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$conn = include 'db.php'; // Capture the returned connection

if (!$conn) {
    die("Fatal Error: Database connection failed. Please check your db.php file.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $sql = "SELECT id, username, password FROM users WHERE username=?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close(); // Close the statement

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION["logged_in"] = true;
                $_SESSION["user_id"] = $user["id"]; // Store user ID in session
                $_SESSION["username"] = $user["username"];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "User does not exist! Please sign up.";
        }
        $result->free(); // Free the result set
    } else {
        // Handle error if prepare fails
        $error = "Database error: Could not prepare statement.";
        error_log("Database prepare error in login.php: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Navbar */
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

        /* Container */
        .container {
            margin-top: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 80vh;
        }

        .box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 300px;
        }

        .title {
            margin-bottom: 20px;
        }

        .error {
            color: red;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background: #3498db;
            color: white;
            padding: 10px;
            border: none;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #2980b9;
        }

        .switch-btn {
            background: #2ecc71;
            margin-top: 10px;
        }

        .switch-btn:hover {
            background: #27ae60;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact Us</a>
    </div>

    <div class="container">
        <div class="box">
            <h2 class="title">Login</h2>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="post">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <p>New user? <a href="signup.php"><button class="switch-btn">Signup</button></a></p>
        </div>
    </div>
</body>
</html>