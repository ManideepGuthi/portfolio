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

// Fetch user profile data
$firstName = "";
$lastName = "";
$email = "";
$bio = "";
$profilePicData = null;
$profileImageType = null;
$profileImageSrc = 'default-profile.png'; // Default image

try {
    $stmt = $conn->prepare("SELECT first_name, last_name, email, bio, profile_pic, profile_pic_type FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $userData = $result->fetch_assoc();
            $firstName = $userData['first_name'];
            $lastName = $userData['last_name'];
            $email = $userData['email'];
            $bio = $userData['bio'];
            $profilePicData = $userData['profile_pic'];
            $profileImageType = $userData['profile_pic_type'];

            if ($profilePicData && $profileImageType) {
                $base64Image = base64_encode($profilePicData);
                $profileImageSrc = 'data:' . $profileImageType . ';base64,' . $base64Image;
            }
        }
        $stmt->close();
        $result->free();
    } else {
        error_log("Database prepare error fetching profile data: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Error fetching profile data: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - My Portfolio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="profile.css"> <style>
        /* Dark mode toggle (you can move this to CSS later) */
        .dark-mode-toggle {
            background: none;
            border: none;
            color: #333; /* Default light mode color */
            cursor: pointer;
            font-size: 20px;
            padding: 5px;
            transition: color 0.3s ease;
        }

        .dark-mode .dark-mode-toggle {
            color: #f0f0f0; /* Dark mode color */
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
                <a href="profile.php" class="nav-item active">
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
                <a href="education.php" class="nav-item">
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
                        <img src="<?php echo $profileImageSrc; ?>" alt="Profile Picture" class="profile-icon" style="width: 30px; height: 30px; border-radius: 50%;">
                        <div class="profile-dropdown-content">
                            <a href="settings.php"><i class="fa-solid fa-cog"></i> Settings</a>
                            <a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </header>

            <section class="profile-section">
                <h2 class="section-title">Your Profile</h2>
                <div class="profile-grid">
                    <div class="profile-card">
                        <div class="profile-picture-container">
                            <img src="<?php echo $profileImageSrc; ?>" alt="Profile Picture" class="profile-picture">
                            </div>
                        <div class="profile-info">
                            <div class="form-group">
                                <label for="firstName">First Name</label>
                                <input type="text" id="firstName" value="<?php echo htmlspecialchars($firstName); ?>">
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name</label>
                                <input type="text" id="lastName" value="<?php echo htmlspecialchars($lastName); ?>">
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" value="<?php echo htmlspecialchars($email); ?>">
                            </div>
                            <div class="form-group">
                                <label for="bio">Bio</label>
                                <textarea id="bio"><?php echo htmlspecialchars($bio); ?></textarea>
                            </div>
                            <button class="save-btn">Save Changes</button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        // JavaScript (same as dashboard.php)
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

        // Initial state and resize handling
        window.addEventListener('resize', handleResize);
        handleResize(); // Call on initial load

        function handleResize() {
            const isMobile = window.innerWidth < 768;
            if (isMobile && isSidebarOpen) {
                closeSidebar(); // Collapse sidebar on mobile if open
            }
        }
    </script>
</body>

</html>