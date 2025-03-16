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

// Fetch user data
$profilePicData = null;
$profileImageType = null;
$profileImageSrc = 'default-profile.png';
$firstName = "";
$lastName = "";
$email = "";

try {
    $stmt = $conn->prepare("SELECT first_name, last_name, email, profile_pic, profile_pic_type FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $userData = $result->fetch_assoc();
            $firstName = $userData['first_name'];
            $lastName = $userData['last_name'];
            $email = $userData['email'];
            $profilePicData = $userData['profile_pic'];
            $profileImageType = $userData['profile_pic_type'];
            if ($profilePicData && $profileImageType) {
                $base64Image = base64_encode($profilePicData);
                $profileImageSrc = 'data:' . $profileImageType . ';base64,' . $base64Image;
            }
        }
        $stmt->close();
        $result->free();
    }
} catch (Exception $e) {
    error_log("Error fetching user data: " . $e->getMessage());
}

// Fetch counts for overview
$educationCount = 0;
$skillsCount = 0;
$experienceCount = 0;
$projectsCount = 0;
try {
    $educationCount = fetchCount($conn, "education", $user_id);
    $skillsCount = fetchCount($conn, "skills", $user_id);
    $experienceCount = fetchCount($conn, "experience", $user_id);
    $projectsCount = fetchCount($conn, "projects", $user_id);
} catch (Exception $e) {
    error_log("Error fetching overview counts: " . $e->getMessage());
}

// Function to fetch counts
function fetchCount($conn, $table, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM $table WHERE user_id = ?"); // Line 68
    if (!$stmt) {
        // Handle prepare error
        error_log("Database prepare error in fetchCount for table '$table': " . $conn->error);
        return 0; // Or throw an exception
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_row()[0];
    $stmt->close();
    return $count;
}

// Fetch counts for overview
$educationCount = 0;
$skillsCount = 0;
$experienceCount = 0;
$projectsCount = 0;
try {
    $educationCount = fetchCount($conn, "education", $user_id); // This is where the error likely originates
    $skillsCount = fetchCount($conn, "skills", $user_id);
    $experienceCount = fetchCount($conn, "experience", $user_id);
    $projectsCount = fetchCount($conn, "projects", $user_id);
} catch (Exception $e) {
    error_log("Error fetching overview counts: " . $e->getMessage());
}

// Fetch recent projects (example)
$recentProjects = [];
try {
    $stmt = $conn->prepare("SELECT title, description FROM projects WHERE user_id = ? ORDER BY created_at DESC LIMIT 3"); // Line 99
    if (!$stmt) {
        error_log("Database prepare error fetching recent projects: " . $conn->error);
        // Decide how to handle the error, perhaps an empty array or a message
        $recentProjects = [];
    } else {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $recentProjects[] = $row;
        }
        $stmt->close();
        $result->free();
    }
} catch (Exception $e) {
    error_log("Error fetching recent projects: " . $e->getMessage());
}

// Fetch recent skills (example)
$recentSkills = [];
try {
    $stmt = $conn->prepare("SELECT skill_name, proficiency FROM skills WHERE user_id = ? ORDER BY created_at DESC LIMIT 5"); // Line 121
    if (!$stmt) {
        error_log("Database prepare error fetching recent skills: " . $conn->error);
        $recentSkills = [];
    } else {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $recentSkills[] = $row;
        }
        $stmt->close();
        $result->free();
    }
} catch (Exception $e) {
    error_log("Error fetching recent skills: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - My Portfolio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="profile.css">
    <style>
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

        .dashboard-widgets {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .widget {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            border: 1px solid #eee;
        }

        .dark-mode .widget {
            background-color: #222738;
            border-color: #333;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            color: #f0f0f0;
        }

        .widget-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 15px;
            color: #555;
        }

        .dark-mode .widget-title {
            color: #ccc;
        }

        .project-item {
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }

        .dark-mode .project-item {
            border-bottom-color: #444;
        }

        .project-item h4 {
            font-size: 1em;
            margin-bottom: 5px;
        }

        .skill-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 3px;
            border-bottom: 1px dashed #eee;
        }

        .dark-mode .skill-item {
            border-bottom-color: #444;
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
                <a href="dashboard.php" class="nav-item active">
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

            <section class="dashboard-overview">
                <h2 class="section-title">Overview</h2>
                <div class="overview-grid">
                    <div class="overview-card education-card">
                        <div class="card-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                        <div class="card-content">
                            <h3>Education</h3>
                            <p>Manage your academic details.</p>
                            <span class="count"><?php echo $educationCount; ?> Entries</span>
                            <a href="education.php" class="card-link">View & Edit <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="overview-card skills-card">
                        <div class="card-icon"><i class="fa-solid fa-code"></i></div>
                        <div class="card-content">
                            <h3>Skills</h3>
                            <p>Showcase your technical proficiencies.</p>
                            <span class="count"><?php echo $skillsCount; ?> Skills</span>
                            <a href="upload-skills.php" class="card-link">Manage Skills <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="overview-card experience-card">
                        <div class="card-icon"><i class="fa-solid fa-briefcase"></i></div>
                        <div class="card-content">
                            <h3>Experience</h3>
                            <p>Detail your professional history.</p>
                            <span class="count"><?php echo $experienceCount; ?> Records</span>
                            <a href="experience.php" class="card-link">Update Experience <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="overview-card projects-card">
                        <div class="card-icon"><i class="fa-solid fa-project-diagram"></i></div>
                        <div class="card-content">
                            <h3>Projects</h3>
                            <p>Highlight your key projects.</p>
                            <span class="count"><?php echo $projectsCount; ?> Projects</span>
                            <a href="projects.php" class="card-link">Explore Projects <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="quick-actions">
                <h2 class="section-title">Quick Actions</h2>
                <div class="actions-grid">
                    <a href="profile.php" class="action-item">
                        <i class="fa-solid fa-user-edit"></i>
                        <span>Edit Profile</span>
                    </a>
                    <a href="projects_add.php" class="action-item">
                        <i class="fa-solid fa-plus"></i>
                        <span>Add Project</span>
                    </a>
                    <button class="action-item">
                        <i class="fa-solid fa-upload"></i>
                        <span>Upload Resume</span>
                    </button>
                    <button class="action-item">
                        <i class="fa-solid fa-envelope"></i>
                        <span>Send Message</span>
                    </button>
                    <a href="settings.php" class="action-item">
                        <i class="fa-solid fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </div>
            </section>

            <section class="dashboard-widgets">
                <div class="widget">
                    <h3 class="widget-title">Recent Projects</h3>
                    <?php if (!empty($recentProjects)): ?>
                        <?php foreach ($recentProjects as $project): ?>
                            <div class="project-item">
                                <h4><?php echo htmlspecialchars($project['title']); ?></h4>
                                <p><?php echo substr(htmlspecialchars($project['description']), 0, 50); ?>...</p>
                            </div>
                        <?php endforeach; ?>
                        <a href="projects.php" style="display:block; margin-top: 10px; font-size: 0.9em;">View All Projects</a>
                    <?php else: ?>
                        <p>No recent projects added.</p>
                    <?php endif; ?>
                </div>

                <div class="widget">
                    <h3 class="widget-title">Recent Skills</h3>
                    <?php if (!empty($recentSkills)): ?>
                        <?php foreach ($recentSkills as $skill): ?>
                            <div class="skill-item">
                                <span><?php echo htmlspecialchars($skill['skill_name']); ?></span>
                                <small><?php echo htmlspecialchars($skill['proficiency']); ?></small>
                            </div>
                        <?php endforeach; ?>
                        <a href="skills.php" style="display:block; margin-top: 10px; font-size: 0.9em;">View All Skills</a>
                    <?php else: ?>
                        <p>No recent skills added.</p>
                    <?php endif; ?>
                </div>

                <div class="widget">
                    <h3 class="widget-title">Profile Information</h3>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                    <a href="profile.php" style="margin-top: 10px; font-size: 0.9em;">Edit Profile</a>
                </div>

                <div class="widget">
                    <h3 class="widget-title">Resume Status</h3>
                    <p>Your resume is up to date. <a href="#">View Resume</a></p>
                    <button style="margin-top: 10px;">Update Resume</button>
                </div>
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