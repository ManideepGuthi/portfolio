<html>
<head>
<style>
        body {
            background: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
        }
        
        /* Top Navigation */
        .nav-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #2c3e50;
            color: white;
            padding: 15px 20px;
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 1000;
            box-sizing: border-box;
        }
        
        .left-nav {
            display: flex;
            align-items: center;
        }

        .toggle-btn {
            font-size: 24px;
            cursor: pointer;
            color: white;
            background: none;
            border: none;
            margin-right: 15px;
        }

        .company-name {
    font-size: 20px;
    font-weight: bold;
    white-space: nowrap;
    text-align: center;
    flex-grow: 1; /* Ensures it remains centered */
}

        
        .nav-links {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            padding: 8px 12px;
            white-space: nowrap;
        }
        
        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
        }

        /* Side Navigation */
        .side-nav {
            width: 250px;
            background: #34495e;
            color: white;
            position: fixed;
            top: 60px;
            left: -250px;
            height: 100vh;
            padding-top: 20px;
            transition: left 0.3s ease-in-out;
            box-shadow: 2px 0px 10px rgba(0, 0, 0, 0.2);
        }

        .side-nav a {
            display: block;
            padding: 15px 20px;
            text-decoration: none;
            color: white;
            font-size: 16px;
        }

        .side-nav a:hover {
            background: #2c3e50;
        }

        .side-nav.active {
            left: 0;
        }

        /* Content */
        .content {
            margin-top: 80px;
            padding: 20px;
            text-align: center;
            transition: margin-left 0.3s ease-in-out;
        }

        .content.active {
            margin-left: 250px;
        }


        /* Responsive Styles */
        @media (max-width: 768px) {
            .side-nav {
                width: 200px;
            }

            .side-nav.active {
                left: 0;
            }

            .content.active {
                margin-left: 200px;
            }

            .nav-container {
                flex-direction: row;
                justify-content: space-between;
                padding: 15px;
            }

            .nav-links {
                display: flex;
                gap: 10px;
            }

            .nav-links a {
                font-size: 14px;
                padding: 6px 10px;
            }

            /* Ensuring Logout Button Fits */
            .nav-links a:last-child {
                margin-left: auto;
                padding: 8px 10px;
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .nav-container {
                padding: 10px;
            }

            .toggle-btn {
                font-size: 20px;
            }

            .company-name {
                font-size: 18px;
            }

            .nav-links a {
                font-size: 12px;
                padding: 5px 8px;
            }

            .nav-links a:last-child {
                font-size: 12px;
                padding: 6px 8px;
            }
        }
    </style>
</head>

<body>

    <!-- Top Navigation -->
    <div class="nav-container">
    <div class="left-nav">
        <button class="toggle-btn" onclick="toggleNav()">☰</button>
    </div>
    <div class="company-name">My Portfolio</div>
    <div class="nav-links">
        <a href="dashboard.php">Home</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

    <!-- Side Navigation -->
    <div class="side-nav" id="sideNav">
        <a href="dashboard.php">User Dashboard</a>
        <a href="upload-skills.php">Manage Skills</a>
        <a href="preview.php">Portfolio Preview</a>
        <a href="#">Work Experience</a>
        <a href="ui-design.php">UI Design Principles</a>
        <a href="accessibility.php">Web Accessibility</a>
        <a href="usability-testing.php">Usability Testing</a>
        <a href="performance.php">Performance Optimization</a>
    </div>

    <script>
        function toggleNav() {
            var sideNav = document.getElementById('sideNav');
            var content = document.getElementById('mainContent');
            sideNav.classList.toggle('active');
            content.classList.toggle('active');
        }
    </script>
</script>
</body>
</html>