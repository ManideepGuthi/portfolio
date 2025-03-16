<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'];
$sql = "SELECT title, description, date_awarded, certificate_link FROM achievements WHERE username = ?";
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
    <title>View Achievements</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <h2>Your Achievements</h2>

    <div class="achievements-container">
        <?php while ($row = $result->fetch_assoc()) { ?>
        <div class="achievement-card">
            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
            <p><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
            <p><strong>Date:</strong> <?php echo htmlspecialchars($row['date_awarded']); ?></p>
            <?php if ($row['certificate_link']) { ?>
                <a href="<?php echo htmlspecialchars($row['certificate_link']); ?>" class="cert-link" target="_blank">View Certificate</a>
            <?php } ?>
        </div>
        <?php } ?>
    </div>

    <a href="dashboard.php" class="dashboard-btn">Back to Dashboard</a>
</div>

</body>
</html>
