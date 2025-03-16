<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'];

// Fetch user profile
$sql_profile = "SELECT full_name, email, profile_pic FROM user_profiles WHERE username = ?";
$stmt_profile = $conn->prepare($sql_profile);
$stmt_profile->bind_param("s", $username);
$stmt_profile->execute();
$result_profile = $stmt_profile->get_result();
$profile = $result_profile->fetch_assoc();

// Fetch skills
$sql_skills = "SELECT skill_name, proficiency, experience_years, certifications FROM skills WHERE username = ?";
$stmt_skills = $conn->prepare($sql_skills);
$stmt_skills->bind_param("s", $username);
$stmt_skills->execute();
$result_skills = $stmt_skills->get_result();

// Fetch achievements
$sql_achievements = "SELECT title, description FROM achievements WHERE username = ?";
$stmt_achievements = $conn->prepare($sql_achievements);
$stmt_achievements->bind_param("s", $username);
$stmt_achievements->execute();
$result_achievements = $stmt_achievements->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Preview</title>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <nav class="bg-gray-800 p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold text-purple-400">Portfolio</h1>
            <div>
                <a href="dashboard.php" class="text-white px-4 py-2 hover:text-purple-400">Home</a>
                <a href="logout.php" class="ml-4 bg-purple-500 text-white px-4 py-2 rounded-lg shadow-md hover:bg-purple-600">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="max-w-4xl mx-auto mt-10 bg-gray-800 p-6 rounded-lg shadow-lg">
        <div class="text-center">
            <?php if (!empty($profile['profile_pic'])) { ?>
                <img src="<?php echo htmlspecialchars($profile['profile_pic']); ?>" class="w-32 h-32 mx-auto rounded-full border-4 border-purple-500 shadow-lg">
            <?php } else { ?>
                <img src="default-profile.png" class="w-32 h-32 mx-auto rounded-full border-4 border-gray-500 shadow-lg">
            <?php } ?>
            <h2 class="text-2xl font-bold mt-3"><?php echo htmlspecialchars($profile['full_name'] ?? $username); ?></h2>
            <p class="text-gray-400"><?php echo htmlspecialchars($profile['email'] ?? 'No Email Provided'); ?></p>
        </div>

        <?php if ($result_skills->num_rows > 0) { ?>
            <h3 class="text-xl font-semibold mt-6 border-b-2 border-purple-500 pb-2">Skills</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                <?php while ($row = $result_skills->fetch_assoc()) { 
                    $proficiency_percentage = 0;
                    switch (strtolower($row['proficiency'])) {
                        case 'beginner': $proficiency_percentage = 30; break;
                        case 'intermediate': $proficiency_percentage = 60; break;
                        case 'advanced': $proficiency_percentage = 90; break;
                    }
                ?>
                <div class="bg-gray-700 p-4 rounded-lg shadow-md">
                    <h4 class="font-bold text-lg"><i class="fas fa-code"></i> <?php echo htmlspecialchars($row['skill_name']); ?></h4>
                    <div class="relative h-4 bg-gray-600 rounded-full mt-2">
                        <div class="absolute top-0 left-0 h-4 bg-purple-500 rounded-full" style="width: <?php echo $proficiency_percentage; ?>%;"></div>
                    </div>
                    <p class="text-sm mt-2">Experience: <?php echo htmlspecialchars($row['experience_years']); ?> years</p>
                    <p class="text-sm">Certifications: <?php echo htmlspecialchars($row['certifications'] ?: 'None'); ?></p>
                </div>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if ($result_achievements->num_rows > 0) { ?>
            <h3 class="text-xl font-semibold mt-6 border-b-2 border-purple-500 pb-2">Achievements</h3>
            <ul class="mt-3">
                <?php while ($row = $result_achievements->fetch_assoc()) { ?>
                    <li class="bg-gray-700 p-4 rounded-lg shadow-md mt-2">
                        <h4 class="font-bold text-lg"><?php echo htmlspecialchars($row['title']); ?></h4>
                        <p class="text-sm text-gray-300"><?php echo htmlspecialchars($row['description']); ?></p>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>

        <div class="mt-6 text-center">
            <a href="dashboard.php" class="bg-purple-500 text-white px-6 py-2 rounded-lg shadow-md hover:bg-purple-600">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
