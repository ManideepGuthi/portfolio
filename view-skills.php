<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'];
$sql = "SELECT skill_name, proficiency, experience_years, certifications FROM skills WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Skills - Portfolio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }

        .container {
            background-color: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 800px;
            text-align: center;
        }

        h2 {
            color: #007bff;
            margin-bottom: 30px;
            font-size: 2.5em;
            font-weight: 600;
        }

        .username {
            font-size: 1.2em;
            color: #555;
            margin-bottom: 25px;
        }

        .skills-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .skill-card {
            background-color: #e8f5e9; /* Light green for skills */
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            text-align: left;
            transition: transform 0.2s ease-in-out;
        }

        .skill-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
        }

        .skill-name {
            font-size: 1.4em;
            font-weight: 500;
            color: #28a745; /* Green for skill name */
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .skill-name i {
            font-size: 1.2em;
        }

        .progress-bar {
            background-color: #d1e7dd;
            border-radius: 4px;
            height: 10px;
            margin-bottom: 15px;
            overflow: hidden;
        }

        .progress {
            background-color: #28a745;
            height: 10px;
            border-radius: 4px;
            width: 0%; /* Initial width */
            transition: width 0.8s ease-in-out;
        }

        .experience,
        .certifications {
            font-size: 1em;
            color: #6c757d;
            margin-bottom: 8px;
        }

        .certifications::before {
            content: 'Certifications: ';
            font-weight: 500;
            color: #555;
        }

        .dashboard-btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 1.1em;
            transition: background-color 0.3s ease;
        }

        .dashboard-btn:hover {
            background-color: #0056b3;
        }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .container {
                padding: 30px;
            }
            h2 {
                font-size: 2em;
            }
            .skills-container {
                grid-template-columns: 1fr;
            }
            .skill-card {
                padding: 20px;
            }
            .skill-name {
                font-size: 1.3em;
            }
            .experience,
            .certifications {
                font-size: 0.9em;
            }
            .dashboard-btn {
                padding: 10px 20px;
                font-size: 1em;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Your Skills & Expertise</h2>
    <div class="username">Hello, <?php echo htmlspecialchars($username); ?></div>

    <div class="skills-container">
        <?php while ($row = $result->fetch_assoc()) {
            $proficiency_percentage = 0;
            switch (strtolower($row['proficiency'])) {
                case 'beginner': $proficiency_percentage = 30; break;
                case 'intermediate': $proficiency_percentage = 60; break;
                case 'advanced': $proficiency_percentage = 90; break;
            }
        ?>
        <div class="skill-card">
            <div class="skill-name"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($row['skill_name']); ?></div>
            <div class="progress-bar">
                <div class="progress" data-progress="<?php echo $proficiency_percentage; ?>"></div>
            </div>
            <div class="experience"><i class="fas fa-briefcase"></i> Experience: <?php echo htmlspecialchars($row['experience_years']); ?> years</div>
            <div class="certifications"><i class="fas fa-certificate"></i> <?php echo htmlspecialchars($row['certifications'] ?: 'None'); ?></div>
        </div>
        <?php } ?>
    </div>

    <a href="dashboard.php" class="dashboard-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
</div>

<script>
    window.onload = function() {
        document.querySelectorAll('.progress').forEach(bar => {
            const progressValue = bar.dataset.progress;
            bar.style.width = progressValue + '%';
        });
    };
</script>

</body>
</html>