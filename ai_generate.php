<?php
require 'vendor/autoload.php'; // Load dotenv for API security

use Dotenv\Dotenv;

// Load API Key from .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$api_key = $_ENV['OPENAI_API_KEY'];

include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $skills = $_POST["skills"];
    $experience = $_POST["experience"];
    $education = $_POST["education"];

    // OpenAI API Request
    $prompt = "Generate a professional resume for the following person:
    Name: $name
    Email: $email
    Skills: $skills
    Experience: $experience
    Education: $education";

    $data = [
        "model" => "gpt-4",
        "messages" => [["role" => "system", "content" => $prompt]],
        "temperature" => 0.7,
        "max_tokens" => 500
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $api_key
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    curl_close($ch);

    $decodedResponse = json_decode($response, true);
    $resume_text = $decodedResponse["choices"][0]["message"]["content"];

    // Save to database
    $stmt = $conn->prepare("INSERT INTO resumes (user_id, name, email, skills, experience, education, resume_text) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $user_id = 1; // Change this dynamically if needed
    $stmt->bind_param("issssss", $user_id, $name, $email, $skills, $experience, $education, $resume_text);
    $stmt->execute();
    $resume_id = $stmt->insert_id;
    $stmt->close();
    $conn->close();

    // Redirect to display resume
    header("Location: ai_generate.php?id=$resume_id");
    exit();
}

// Fetch resume if ID is provided
$resume_text = "";
if (isset($_GET["id"])) {
    $id = intval($_GET["id"]);
    $sql = "SELECT * FROM resumes WHERE id = $id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $resume_text = nl2br($row["resume_text"]);
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>AI Resume Generator</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
        input, textarea { width: 80%; padding: 10px; margin: 5px; border-radius: 5px; border: 1px solid #ccc; }
        button { padding: 10px; font-size: 18px; cursor: pointer; margin: 10px; border: none; border-radius: 5px; background-color: #28a745; color: white; }
    </style>
</head>
<body>

    <h2>AI Resume Generator</h2>

    <?php if (!$resume_text): ?>
    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <textarea name="skills" placeholder="Skills (comma separated)" required></textarea><br>
        <textarea name="experience" placeholder="Experience" required></textarea><br>
        <textarea name="education" placeholder="Education" required></textarea><br>
        <button type="submit">Generate AI Resume</button>
    </form>
    <?php else: ?>
        <h3>Generated Resume</h3>
        <p><?= $resume_text ?></p>
        <a href="download.php?id=<?= $id ?>">Download Resume</a>
    <?php endif; ?>

</body>
</html>
