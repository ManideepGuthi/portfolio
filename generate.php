<?php
include "db.php";

// Example user data (replace with form input or AI API)
$user_id = 1;
$name = "John Doe";
$email = "johndoe@example.com";
$skills = "PHP, MySQL, JavaScript, HTML, CSS";
$experience = "3+ years as a Web Developer";
$education = "B.Tech in Computer Science";

// AI Resume Template
$resume_text = "
**Resume: $name**\n
**Email:** $email\n
**Skills:** $skills\n
**Experience:** $experience\n
**Education:** $education\n
";

// Save resume to database
$stmt = $conn->prepare("INSERT INTO resumes (user_id, name, email, skills, experience, education, resume_text) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssss", $user_id, $name, $email, $skills, $experience, $education, $resume_text);
$stmt->execute();

$resume_id = $stmt->insert_id;
$stmt->close();
$conn->close();

// Redirect back to main page
header("Location: resume.php");
exit();
?>
