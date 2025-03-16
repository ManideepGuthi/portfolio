<?php
session_start();
require_once 'db.php';
include 'navbar.php';

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $description = $_POST["description"];
    $date_awarded = $_POST["date_awarded"];
    $certificate_link = $_POST["certificate_link"] ?? null;

    $sql = "INSERT INTO achievements (username, title, description, date_awarded, certificate_link) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $_SESSION['username'], $title, $description, $date_awarded, $certificate_link);

    if ($stmt->execute()) {
        header("Location: view-achievements.php");
        exit;
    } else {
        $error = "Error saving achievement. Please try again!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Achievement</title>
    <style>
/* Page Background */
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(to bottom, #e3f2fd, #ffffff);
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* Form Container */
.container {
    background: #ffffff;
    width: 50%;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    text-align: center;
}

/* Heading */
h2 {
    color: #0d47a1;
    font-size: 24px;
    margin-bottom: 20px;
    font-weight: 600;
}

/* Input Fields */
input, textarea {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: 1px solid #90caf9;
    border-radius: 6px;
    font-size: 16px;
    background: #f1f8ff;
    transition: border 0.3s ease-in-out;
}

input:focus, textarea:focus {
    border: 2px solid #0d47a1;
    outline: none;
}

/* Submit Button */
button {
    width: 100%;
    padding: 12px;
    background: #0d47a1;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s ease-in-out;
}

button:hover {
    background: #1565c0;
}

/* Back to Dashboard Button */
.dashboard-btn {
    display: inline-block;
    margin-top: 15px;
    padding: 10px 15px;
    background: #64b5f6;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
    transition: background 0.3s ease-in-out;
}

.dashboard-btn:hover {
    background: #42a5f5;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        width: 80%;
    }
}


    </style>
</head>
<body>

<div class="container">
    <h2>Upload Your Achievements</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    
    <form method="post">
        <input type="text" name="title" placeholder="Achievement Title" required>
        <textarea name="description" placeholder="Describe your achievement" required></textarea>
        <input type="date" name="date_awarded" required>
        <input type="url" name="certificate_link" placeholder="Certificate URL (optional)">
        <button type="submit">Submit Achievement</button>
    </form>

    <a href="dashboard.php" class="dashboard-btn">Back to Dashboard</a>
</div>

</body>
</html>
