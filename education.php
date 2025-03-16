<?php
session_start();

$conn = include 'db.php';

if (!$conn) {
    die("Fatal Error: Database connection failed. Please check your db.php file.");
}

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Handle delete education record
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM education WHERE id = ? AND user_id = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $delete_id, $user_id);
            if ($stmt->execute()) {
                header("Location: education.php?delete_success=1");
                exit;
            } else {
                error_log("Error deleting education record: " . $stmt->error);
                $deleteError = "Failed to delete education record. Please try again.";
            }
            $stmt->close();
        } else {
            error_log("Database prepare error deleting education record: " . $conn->error);
            $deleteError = "Database error. Please try again later.";
        }
    } catch (Exception $e) {
        error_log("Error deleting education record: " . $e->getMessage());
        $deleteError = "An error occurred while deleting the record.";
    }
}

// Handle form submission for updating education
$updateEducationError = '';
$editMode = false;
$editRecord = null;

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editMode = true;
    $edit_id = $_GET['edit'];
    try {
        $stmt = $conn->prepare("SELECT id, institution, degree, major, start_date, end_date, description FROM education WHERE id = ? AND user_id = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $edit_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $editRecord = $result->fetch_assoc();
            $stmt->close();
            $result->free();
        } else {
            error_log("Database prepare error fetching record for edit: " . $conn->error);
            $updateEducationError = "Error fetching record for edit. Please try again.";
        }
    } catch (Exception $e) {
        error_log("Error fetching record for edit: " . $e->getMessage());
        $updateEducationError = "An error occurred while fetching the record for edit.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_education'])) {
    $update_id = $_POST['update_id'];
    $institution = $_POST['institution'];
    $degree = $_POST['degree'];
    $major = $_POST['major'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $description = $_POST['description'];

    if (empty($institution) || empty($degree) || empty($start_date)) {
        $updateEducationError = "Institution, Degree, and Start Date are required.";
    } else {
        try {
            $stmt = $conn->prepare("UPDATE education SET institution = ?, degree = ?, major = ?, start_date = ?, end_date = ?, description = ? WHERE id = ? AND user_id = ?");
            if ($stmt) {
                $stmt->bind_param("ssssssii", $institution, $degree, $major, $start_date, $end_date, $description, $update_id, $user_id);
                if ($stmt->execute()) {
                    header("Location: education.php?update_success=1");
                    exit;
                } else {
                    error_log("Error updating education record: " . $stmt->error);
                    $updateEducationError = "Failed to update education record. Please try again.";
                }
                $stmt->close();
            } else {
                error_log("Database prepare error updating education record: " . $conn->error);
                $updateEducationError = "Database error. Please try again later.";
            }
        } catch (Exception $e) {
            error_log("Error updating education record: " . $e->getMessage());
            $updateEducationError = "An error occurred while updating the record.";
        }
    }
}

// Handle form submission for adding education
$addEducationError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_education'])) {
    $institution = $_POST['institution'];
    $degree = $_POST['degree'];
    $major = $_POST['major'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $description = $_POST['description'];

    if (empty($institution) || empty($degree) || empty($start_date)) {
        $addEducationError = "Institution, Degree, and Start Date are required.";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO education (user_id, institution, degree, major, start_date, end_date, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("issssss", $user_id, $institution, $degree, $major, $start_date, $end_date, $description);
                if ($stmt->execute()) {
                    header("Location: education.php?add_success=1");
                    exit;
                } else {
                    error_log("Error adding education record: " . $stmt->error);
                    $addEducationError = "Failed to add education record. Please try again.";
                }
                $stmt->close();
            } else {
                error_log("Database prepare error adding education record: " . $conn->error);
                $addEducationError = "Database error. Please try again later.";
            }
        } catch (Exception $e) {
            error_log("Error adding education record: " . $e->getMessage());
            $addEducationError = "An error occurred while adding the record.";
        }
    }
}

// Fetch education records for the user
$educationRecords = [];
try {
    $stmt = $conn->prepare("SELECT id, institution, degree, major, start_date, end_date, description FROM education WHERE user_id = ? ORDER BY end_date DESC");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $educationRecords[] = $row;
        }
        $stmt->close();
        $result->free();
    } else {
        error_log("Database prepare error fetching education records: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Error fetching education records: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Education - My Portfolio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="education.css">
    <style>
        .dark-mode-toggle {
            background: none;
            border: none;
            color: #333;
            cursor: pointer;
            font-size: 20px;
            padding: 5px;
            transition: color 0.3s ease;
        }

        .dark-mode .dark-mode-toggle {
            color: #f0f0f0;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar" id="mySidebar">
            <div class="sidebar-header">
                <button class="close-btn" onclick="closeSidebar()">
                    <i class="fa-solid fa-times"></i>
                </button>
                <a href="dashboard.php" class="logo-container">
                    <span style="font-weight: bold; font-size: 1.3em;">MyPortfolio</span>
                </a>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <i class="fa-solid fa-tachometer-alt"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="profile.php" class="nav-item">
                    <i class="fa-solid fa-user"></i>
                    <span class="nav-text">Profile</span>
                </a>
                <a href="skills.php" class="nav-item">
                    <i class="fa-solid fa-code"></i>
                    <span class="nav-text">Skills</span>
                </a>
                <a href="experience.php" class="nav-item">
                    <i class="fa-solid fa-briefcase"></i>
                    <span class="nav-text">Experience</span>
                </a>
                <a href="education.php" class="nav-item active">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <span class="nav-text">Education</span>
                </a>
                <a href="projects.php" class="nav-item">
                    <i class="fa-solid fa-project-diagram"></i>
                    <span class="nav-text">Projects</span>
                </a>
                <a href="awards.php" class="nav-item">
                    <i class="fa-solid fa-award"></i>
                    <span class="nav-text">Awards</span>
                </a>
                <a href="social.php" class="nav-item">
                    <i class="fa-solid fa-share-nodes"></i>
                    <span class="nav-text">Social</span>
                </a>
                <a href="settings.php" class="nav-item">
                    <i class="fa-solid fa-cog"></i>
                    <span class="nav-text">Settings</span>
                </a>
                <a href="preview.php" target="_blank" class="nav-item">
                    <i class="fa-solid fa-eye"></i>
                    <span class="nav-text">Preview</span>
                </a>
                <a href="logout.php" class="nav-item logout">
                    <i class="fa-solid fa-sign-out-alt"></i>
                    <span class="nav-text">Logout</span>
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <button class="toggle-btn-header" onclick="toggleSidebar()">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="header-right">
                    <button class="dark-mode-toggle" onclick="toggleDarkMode()">
                        <i class="fa-solid fa-moon"></i>
                    </button>
                    <div class="profile-dropdown">
                        <span class="username"><?php echo htmlspecialchars($username); ?></span>
                        <img src="default-profile.png" alt="Profile Picture" class="profile-icon" style="width: 30px; height: 30px; border-radius: 50%;">
                        <div class="profile-dropdown-content">
                            <a href="settings.php"><i class="fa-solid fa-cog"></i> Settings</a>
                            <a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </header>

            <section class="education-section">
                <h2 class="section-title">My Education</h2>

                <?php if (isset($_GET['add_success'])): ?>
                    <div class="success-message">Education record added successfully.</div>
                <?php endif; ?>
                <?php if (isset($_GET['update_success'])): ?>
                    <div class="success-message">Education record updated successfully.</div>
                <?php endif; ?>
                <?php if (isset($_GET['delete_success'])): ?>
                    <div class="success-message">Education record deleted successfully.</div>
                <?php endif; ?>

                <?php if (isset($addEducationError)): ?>
                    <div class="error-message"><?php echo $addEducationError; ?></div>
                <?php endif; ?>
                <?php if (isset($deleteError)): ?>
                    <div class="error-message"><?php echo $deleteError; ?></div>
                <?php endif; ?>
                <?php if (isset($updateEducationError)): ?>
                    <div class="error-message"><?php echo $updateEducationError; ?></div>
                <?php endif; ?>

                <?php if ($editMode && $editRecord): ?>
                    <div class="add-education-form">
                        <h3>Edit Education</h3>
                        <form method="post">
                            <input type="hidden" name="update_id" value="<?php echo htmlspecialchars($editRecord['id']); ?>">
                            <div class="form-group">
                                <label for="institution">Institution</label>
                                <input type="text" id="institution" name="institution" value="<?php echo htmlspecialchars($editRecord['institution']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="degree">Degree</label>
                                <input type="text" id="degree" name="degree" value="<?php echo htmlspecialchars($editRecord['degree']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="major">Major</label>
                                <input type="text" id="major" name="major" value="<?php echo htmlspecialchars($editRecord['major']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($editRecord['start_date']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="end_date">End Date (Leave blank if ongoing)</label>
                                <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($editRecord['end_date']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description"><?php echo htmlspecialchars($editRecord['description']); ?></textarea>
                            </div>
                            <button type="submit" name="update_education" class="save-btn">Update Education</button>
                            <a href="education.php" class="cancel-btn">Cancel</a>
                        </form>
                    </div>
                    <hr>
                <?php endif; ?>

                <div class="education-list">
                    <h3>History</h3>
                    <?php if (empty($educationRecords)): ?>
                        <p>No education records added yet.</p>
                    <?php else: ?>
                        <?php foreach ($educationRecords as $education): ?>
                            <div class="education-item">
                                <h3><?php echo htmlspecialchars($education['institution']); ?></h3>
                                <p class="degree"><?php echo htmlspecialchars($education['degree']); ?> (<?php echo htmlspecialchars($education['major']); ?>)</p>
                                <p class="date"><?php echo date('M Y', strtotime($education['start_date'])); ?> - <?php echo ($education['end_date'] ? date('M Y', strtotime($education['end_date'])) : 'Present'); ?></p>
                                <?php if (!empty($education['description'])): ?>
                                    <p class="description"><?php echo nl2br(htmlspecialchars($education['description'])); ?></p>
                                <?php endif; ?>
                                <div class="education-actions">
                                    <a href="education.php?edit=<?php echo $education['id']; ?>" class="edit-btn">Edit</a>
                                    <a href="education.php?delete=<?php echo $education['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if (!$editMode): ?>
                    <div class="add-education-form">
                        <h3>Add Education</h3>
                        <form method="post">
                            <div class="form-group">
                                <label for="institution">Institution</label>
                                <input type="text" id="institution" name="institution" required>
                            </div>
                            <div class="form-group">
                                <label for="degree">Degree</label>
                                <input type="text" id="degree" name="degree" required>
                            </div>
                            <div class="form-group">
                                <label for="major">Major</label>
                                <input type="text" id="major" name="major">
                            </div>
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" id="start_date" name="start_date" required>
                            </div>
                            <div class="form-group">
                                <label for="end_date">End Date (Leave blank if ongoing)</label>
                                <input type="date" id="end_date" name="end_date">
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description"></textarea>
                            </div>
                            <button type="submit" name="add_education" class="save-btn">Add Education</button>
                        </form>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script>
        const body = document.body;
        const sidebar = document.getElementById('mySidebar');
        const mainContent = document.querySelector('.main-content');
        let isSidebarOpen = false;

        function toggleSidebar() {
            if (isSidebarOpen) {
                closeSidebar();
            } else {
                openSidebar();
            }
        }

        function openSidebar() {
            sidebar.style.transform = 'translateX(0)';
            isSidebarOpen = true;
        }

        function closeSidebar() {
            sidebar.style.transform = 'translateX(-100%)';
            isSidebarOpen = false;
        }

        function toggleDarkMode() {
            body.classList.toggle('dark-mode');
        }

        window.addEventListener('resize', handleResize);
        handleResize();

        function handleResize() {
            const isMobile = window.innerWidth < 768;
            if (isMobile && isSidebarOpen) {
                closeSidebar();
            }
        }
    </script>
</body>

</html>