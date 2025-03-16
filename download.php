<?php
include "db.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM resumes WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $filename = "Resume_{$row['name']}.txt";

    header('Content-Type: text/plain');
    header("Content-Disposition: attachment; filename=$filename");

    echo "Name: " . $row['name'] . "\n";
    echo "Email: " . $row['email'] . "\n";
    echo "Skills: " . $row['skills'] . "\n";
    echo "Experience: " . $row['experience'] . "\n";
    echo "Education: " . $row['education'] . "\n";
    echo "\nResume:\n" . $row['resume_text'];
} else {
    echo "Resume not found.";
}

$conn->close();
?>
