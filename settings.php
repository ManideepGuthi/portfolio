<?php
session_start();
require_once 'db.php';
include 'navbar.php';

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'];
$message = "";

// Fetch user details
$query = $conn->prepare("SELECT * FROM user_profiles WHERE username = ?");
$query->bind_param("s", $username);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

// If user doesn't exist, create a new entry
if (!$user) {
    $insertQuery = $conn->prepare("INSERT INTO user_profiles (username) VALUES (?)");
    $insertQuery->bind_param("s", $username);
    if ($insertQuery->execute()) {
        $message = "New profile created successfully!";
    }

    $user = [
        'full_name' => '', 'email' => '', 'phone' => '',
        'bio' => '', 'profile_pic' => 'default.jpg',
        'location' => '', 'linkedin' => '', 'github' => '', 'website' => ''
    ];
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $bio = $_POST['bio'];
    $location = $_POST['location'];
    $linkedin = $_POST['linkedin'];
    $github = $_POST['github'];
    $website = $_POST['website'];
    $profile_pic = $user['profile_pic'];

    // Handle profile picture upload
    if (!empty($_FILES['profile_pic']['name'])) {
        $targetDir = "uploads/";
        $profile_pic = $username . "_" . basename($_FILES["profile_pic"]["name"]);
        $targetFilePath = $targetDir . $profile_pic;
        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFilePath);
    }

    // Update user profile
    $updateQuery = $conn->prepare("UPDATE user_profiles SET full_name=?, email=?, phone=?, bio=?, location=?, linkedin=?, github=?, website=?, profile_pic=? WHERE username=?");
    $updateQuery->bind_param("ssssssssss", $full_name, $email, $phone, $bio, $location, $linkedin, $github, $website, $profile_pic, $username);

    if ($updateQuery->execute()) {
        $message = "Profile updated successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 500px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.1);
            margin: 50px auto;
        }
        h2 {
            text-align: center;
        }
        .profile-pic {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-pic img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #3498db;
        }
        label {
            font-weight: bold;
            display: block;
            margin: 10px 0 5px;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        button {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        button:hover {
            background: #2980b9;
        }
        .success {
            background: #2ecc71;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Update Profile</h2>

    <?php if (!empty($message)): ?>
        <div class="success"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="profile-pic">
        <img src="uploads/<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile Picture">
    </div>

    <form method="POST" enctype="multipart/form-data">
        <label>Full Name</label>
        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label>Phone</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">

        <label>Bio</label>
        <textarea name="bio"><?php echo htmlspecialchars($user['bio']); ?></textarea>

        <label>Location</label>
        <input type="text" name="location" value="<?php echo htmlspecialchars($user['location']); ?>">

        <label>LinkedIn Profile</label>
        <input type="text" name="linkedin" value="<?php echo htmlspecialchars($user['linkedin']); ?>">

        <label>GitHub Profile</label>
        <input type="text" name="github" value="<?php echo htmlspecialchars($user['github']); ?>">

        <label>Website</label>
        <input type="text" name="website" value="<?php echo htmlspecialchars($user['website']); ?>">

        <label>Profile Picture</label>
        <input type="file" name="profile_pic">

        <button type="submit">Save Changes</button>
    </form>
</div>

</body>
</html>
