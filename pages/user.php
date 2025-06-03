<?php
session_start();
include("../includes/session.php");
include("../includes/db.php");

if (!isset($_SESSION['user'], $_SESSION['role'])) {
    header("Location: ../landing.php");
    exit;
}

$user = $_SESSION['user'];
$role = $_SESSION['role'];
$upload_error = '';
$upload_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    $filename = basename($file['name']);
    $target_dir = "../assets/uploads/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . time() . "_" . $filename;

    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($imageFileType, $allowed) && $file['size'] < 2 * 1024 * 1024) {
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $table = $role; // 'admin', 'doctor', or 'patient'
            $stmt = $conn->prepare("UPDATE $table SET profile_picture = ? WHERE id = ?");
            $stmt->bind_param("si", $target_file, $user['id']);
            
            if ($stmt->execute()) {
                $_SESSION['user']['profile_picture'] = $target_file;
                $upload_success = "Profile picture updated successfully!";
            } else {
                unlink($target_file);
                $upload_error = "Failed to update database.";
            }
            $stmt->close();
        } else {
            $upload_error = "Failed to upload image.";
        }
    } else {
        $upload_error = "Only JPG, JPEG, PNG, GIF files under 2MB are allowed.";
    }
}

$profile_picture = !empty($_SESSION['user']['profile_picture']) ? $_SESSION['user']['profile_picture'] : '../assets/img/default-user.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= ucfirst($role) ?> Profile</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary: #2ecc71;
            --primary-dark: #27ae60;
            --light: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--light);
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .profile-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        
        .profile-card {
            background: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: <?= $role === 'admin' ? '420px' : '700px' ?>;
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }
        
        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        }
        
        .profile-pic-container {
            position: relative;
            width: 180px;
            height: 180px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 6px;
        }
        
        .profile-pic {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid var(--white);
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        
        .upload-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: var(--primary);
            color: white;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            box-shadow: 0 3px 10px rgba(46, 204, 113, 0.3);
        }
        
        .upload-btn:hover {
            background: var(--primary-dark);
            transform: scale(1.1);
        }
        
        .upload-btn svg {
            width: 20px;
            height: 20px;
        }
        
        .hidden-upload {
            display: none;
        }
        
        .dashboard-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        
        .dashboard-btn a {
            background-color: var(--primary);
            padding: 0.6rem 1.2rem;
            color: white;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            box-shadow: 0 2px 5px rgba(46, 204, 113, 0.2);
        }
        
        .dashboard-btn a:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(46, 204, 113, 0.3);
        }
        
        .status-message {
            margin: 1.5rem 0;
            padding: 0.8rem;
            border-radius: 8px;
            font-weight: 500;
            animation: fadeIn 0.4s ease-out;
            text-align: center;
        }
        
        .success-message { 
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--primary-dark);
            border: 1px solid rgba(46, 204, 113, 0.2);
        }
        
        .error-message { 
            background-color: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.2);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .profile-title {
            color: var(--primary);
            margin: 0.5rem 0 1.5rem;
            font-size: 1.5rem;
            font-weight: 600;
            text-align: center;
        }
        
        /* Admin-specific styles */
        <?php if ($role === 'admin'): ?>
        .admin-profile {
            text-align: center;
        }
        <?php else: ?>
        /* Doctor/Patient profile info styles */
        .profile-info {
            margin-top: 2rem;
        }
        
        .profile-info h3 {
            color: #555;
            border-bottom: 1px solid #eee;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .profile-field {
            display: flex;
            margin-bottom: 1rem;
            font-size: 1rem;
        }
        
        .profile-field strong {
            color: #555;
            min-width: 150px;
            font-weight: 600;
        }
        
        .profile-field span {
            color: #333;
        }
        
        <?php if ($role === 'doctor'): ?>
        .doctor-badge {
            background-color: rgba(52, 152, 219, 0.1);
            color: #3498db;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            margin-left: 0.5rem;
        }
        <?php endif; ?>
        <?php endif; ?>
    </style>
</head>
<body>
    <?php include("../includes/header.php"); ?>
    
    <main class="profile-container">
        <div class="profile-card">
            <div class="dashboard-btn">
                <a href="../<?= $role ?>/dashboard.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    Dashboard
                </a>
            </div>

            <h1 class="profile-title"><?= ucfirst($role) ?> Profile</h1>
            
            <div class="profile-pic-container">
                <img class="profile-pic" src="<?= htmlspecialchars($profile_picture) ?>" 
                     alt="Profile Picture"
                     onerror="this.src='../assets/img/default-user.png'">
                
                <button class="upload-btn" onclick="document.getElementById('profile-upload').click()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                        <circle cx="12" cy="13" r="4"></circle>
                    </svg>
                </button>
                
                <form method="POST" enctype="multipart/form-data" class="hidden-upload">
                    <input type="file" id="profile-upload" name="profile_picture" accept="image/*" onchange="this.form.submit()">
                </form>
            </div>

            <?php if ($upload_success): ?>
                <div class="status-message success-message">
                    <?= $upload_success ?>
                </div>
            <?php elseif ($upload_error): ?>
                <div class="status-message error-message">
                    <?= $upload_error ?>
                </div>
            <?php endif; ?>

            <?php if ($role !== 'admin'): ?>
                <div class="profile-info">
                    <h3>Personal Information</h3>
                    
                    <div class="profile-field">
                        <strong>Full Name:</strong>
                        <span><?= htmlspecialchars($user['fullname']) ?></span>
                    </div>
                    
                    <div class="profile-field">
                        <strong>Username:</strong>
                        <span><?= htmlspecialchars($user['username']) ?></span>
                    </div>
                    
                    <div class="profile-field">
                        <strong>Email:</strong>
                        <span><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    
                    <div class="profile-field">
                        <strong>Phone:</strong>
                        <span><?= htmlspecialchars($user['phone']) ?></span>
                    </div>

                    <?php if ($role === 'patient'): ?>
                        <div class="profile-field">
                            <strong>Date of Birth:</strong>
                            <span><?= htmlspecialchars($user['dob']) ?></span>
                        </div>
                    <?php elseif ($role === 'doctor'): ?>
                        <div class="profile-field">
                            <strong>Specialization:</strong>
                            <span>
                                <?= htmlspecialchars($user['specialization']) ?>
                                <span class="doctor-badge">MD</span>
                            </span>
                        </div>
                        <div class="profile-field">
                            <strong>Experience:</strong>
                            <span><?= htmlspecialchars($user['experience']) ?> years</span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include("../includes/footer.php"); ?>
</body>
</html>