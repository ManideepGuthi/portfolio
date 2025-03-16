<?php
session_start();
require_once 'db.php';
include 'navbar.php';

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'];
$success_msg = $error_msg = "";
$edit_skill_id = "";
$edit_skill_name = "";
$edit_proficiency = "";
$edit_experience_years = "";
$edit_certifications = "";
$edit_certificate_file = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_skill'])) {
        $skill_name = $_POST['skill_name'];
        $proficiency = $_POST['proficiency'];
        $experience_years = isset($_POST['experience_years']) ? $_POST['experience_years'] : 0;
        $certifications = isset($_POST['certifications']) ? $_POST['certifications'] : "";

        // Handle file upload
        $certificate_file = "";
        if (!empty($_FILES['certificate']['name'])) {
            $target_dir = "uploads/";
            $certificate_file = $target_dir . basename($_FILES["certificate"]["name"]);
            move_uploaded_file($_FILES["certificate"]["tmp_name"], $certificate_file);
        }

        if (!empty($_POST['edit_skill_id'])) {
            // Update existing skill
            $skill_id = $_POST['edit_skill_id'];
            $sql = "UPDATE skills SET skill_name=?, proficiency=?, experience_years=?, certifications=?, certificate_file=? WHERE id=? AND username=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssissis", $skill_name, $proficiency, $experience_years, $certifications, $certificate_file, $skill_id, $username);
        } else {
            // Insert new skill
            $sql = "INSERT INTO skills (username, skill_name, proficiency, experience_years, certifications, certificate_file) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssiss", $username, $skill_name, $proficiency, $experience_years, $certifications, $certificate_file);
        }

        if ($stmt->execute()) {
            $success_msg = !empty($_POST['edit_skill_id']) ? "Skill updated successfully!" : "Skill added successfully!";
        } else {
            $error_msg = "Error saving skill!";
        }
    } elseif (isset($_POST['delete_skill'])) {
        $skill_id = $_POST['skill_id'];

        // Delete certificate file if exists
        $sql = "SELECT certificate_file FROM skills WHERE id=? AND username=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $skill_id, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (!empty($row['certificate_file']) && file_exists($row['certificate_file'])) {
                unlink($row['certificate_file']); // Delete file
            }
        }

        // Delete skill from database
        $sql = "DELETE FROM skills WHERE id = ? AND username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $skill_id, $username);
        if ($stmt->execute()) {
            $success_msg = "Skill deleted successfully!";
        } else {
            $error_msg = "Error deleting skill!";
        }
    } elseif (isset($_POST['edit_skill'])) {
        // Load skill data for editing
        $skill_id = $_POST['skill_id'];
        $sql = "SELECT * FROM skills WHERE id = ? AND username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $skill_id, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $edit_skill_id = $row['id'];
            $edit_skill_name = $row['skill_name'];
            $edit_proficiency = $row['proficiency'];
            $edit_experience_years = $row['experience_years'];
            $edit_certifications = $row['certifications'];
            $edit_certificate_file = $row['certificate_file'];
        }
    }
}

// Fetch existing skills
$sql = "SELECT * FROM skills WHERE username = ?";
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
    <title>Manage Skills</title>
    <link rel="stylesheet" href="USstyles.css">
</head>
<body>
<div class="container" style="margin-top:50px;">
    <h2>Manage Your Skills</h2>

    <?php if ($success_msg) echo "<div class='message success'>$success_msg</div>"; ?>
    <?php if ($error_msg) echo "<div class='message error'>$error_msg</div>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="edit_skill_id" value="<?php echo $edit_skill_id; ?>">

        <div class="form-group">
            <label for="skill_name">Skill Name:</label>
            <input type="text" id="skill_name" name="skill_name" value="<?php echo $edit_skill_name; ?>" required>
        </div>

        <div class="form-group">
            <label for="proficiency">Proficiency Level:</label>
            <select id="proficiency" name="proficiency" required>
                <option value="Beginner" <?php if ($edit_proficiency == "Beginner") echo "selected"; ?>>Beginner</option>
                <option value="Intermediate" <?php if ($edit_proficiency == "Intermediate") echo "selected"; ?>>Intermediate</option>
                <option value="Advanced" <?php if ($edit_proficiency == "Advanced") echo "selected"; ?>>Advanced</option>
                <option value="Expert" <?php if ($edit_proficiency == "Expert") echo "selected"; ?>>Expert</option>
            </select>
        </div>

        <div class="form-group">
            <label for="experience_years">Years of Experience:</label>
            <input type="number" id="experience_years" name="experience_years" min="0" value="<?php echo $edit_experience_years; ?>" required>
        </div>

        <div class="form-group">
            <label for="certifications">Certifications (if any):</label>
            <textarea id="certifications" name="certifications" rows="3"><?php echo $edit_certifications; ?></textarea>
        </div>

        <label for="certificate">Upload Certificate:</label>
        <input type="file" id="certificate" name="certificate">
        <br>
        <?php if ($edit_certificate_file): ?>
            <p>Current Certificate: <a href="<?= $edit_certificate_file ?>" target="_blank">View</a></p>
        <?php endif; ?>

        <br><br>
        <button type="submit" name="add_skill"><?php echo empty($edit_skill_id) ? "Submit Skill" : "Update Skill"; ?></button>
    </form>

    <div class="skills-container">
        <h3>Your Skills</h3>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="skill-card">
                <div class="skill-details">
                    <strong><?= $row['skill_name'] ?></strong> (<?= $row['proficiency'] ?>, <?= $row['experience_years'] ?> years)
                </div>
                <div class="skill-actions">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="skill_id" value="<?= $row['id'] ?>">
                        <button type="submit" name="edit_skill" class="edit-btn">Edit</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="skill_id" value="<?= $row['id'] ?>">
                        <button type="submit" name="delete_skill" class="delete-btn">Delete</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>
