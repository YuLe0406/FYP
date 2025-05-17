<?php
include 'db.php';
session_start();

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = '';
$uploadError = '';

// Set upload directory
$targetDir = __DIR__ . "/uploads/banners/";

// Create directory if it doesn't exist
if (!file_exists($targetDir)) {
    if (!mkdir($targetDir, 0755, true)) {
        die("Failed to create upload directory");
    }
}

// Handle banner text and primary picture update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["banner_text"])) {
    $new_banner_text = mysqli_real_escape_string($conn, $_POST["banner_text"]);
    $status = isset($_POST["banner_status"]) ? 1 : 0;
    
    // Handle primary picture upload if provided
    $primaryPicture = null;
    if (!empty($_FILES["primary_picture"]["name"])) {
        $fileName = preg_replace("/[^a-zA-Z0-9\.]/", "_", basename($_FILES["primary_picture"]["name"]));
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        
        $check = getimagesize($_FILES["primary_picture"]["tmp_name"]);
        if($check !== false) {
            $allowTypes = array('jpg','png','jpeg','gif');
            if(in_array($fileType, $allowTypes)) {
                if ($_FILES["primary_picture"]["size"] <= 2000000) {
                    $counter = 1;
                    while (file_exists($targetFilePath)) {
                        $fileInfo = pathinfo($fileName);
                        $fileName = $fileInfo['filename'] . '_' . $counter . '.' . $fileInfo['extension'];
                        $targetFilePath = $targetDir . $fileName;
                        $counter++;
                    }
                    
                    if (move_uploaded_file($_FILES["primary_picture"]["tmp_name"], $targetFilePath)) {
                        $primaryPicture = $fileName;
                    } else {
                        $uploadError = "Error uploading primary picture. Please try again.";
                    }
                } else {
                    $uploadError = "Primary picture is too large (max 2MB)";
                }
            } else {
                $uploadError = "Only JPG, JPEG, PNG & GIF files are allowed for primary picture";
            }
        } else {
            $uploadError = "Primary picture file is not an image";
        }
    }

    if ($uploadError === '') {
        if ($primaryPicture) {
            $sql = "UPDATE BANNER SET B_Text='$new_banner_text', B_Picture='$primaryPicture', B_Status=$status WHERE B_ID = 1";
        } else {
            $sql = "UPDATE BANNER SET B_Text='$new_banner_text', B_Status=$status WHERE B_ID = 1";
        }
        
        if (mysqli_query($conn, $sql)) {
            $message = "Banner updated successfully!";
        } else {
            $message = "Error updating banner: " . mysqli_error($conn);
        }
    }
}

// Handle additional picture upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["additional_picture"])) {
    $countResult = $conn->query("SELECT COUNT(*) as count FROM BANNER_PICTURE WHERE B_ID = 1");
    $countRow = $countResult->fetch_assoc();
    
    if ($countRow['count'] >= 5) {
        $uploadError = "Maximum of 5 additional pictures reached. Please delete some before uploading new ones.";
    } else {
        $fileName = preg_replace("/[^a-zA-Z0-9\.]/", "_", basename($_FILES["additional_picture"]["name"]));
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        
        $check = getimagesize($_FILES["additional_picture"]["tmp_name"]);
        if($check !== false) {
            $allowTypes = array('jpg','png','jpeg','gif');
            if(in_array($fileType, $allowTypes)) {
                if ($_FILES["additional_picture"]["size"] <= 2000000) {
                    $counter = 1;
                    while (file_exists($targetFilePath)) {
                        $fileInfo = pathinfo($fileName);
                        $fileName = $fileInfo['filename'] . '_' . $counter . '.' . $fileInfo['extension'];
                        $targetFilePath = $targetDir . $fileName;
                        $counter++;
                    }
                    
                    if (move_uploaded_file($_FILES["additional_picture"]["tmp_name"], $targetFilePath)) {
                        $insert = $conn->query("INSERT INTO BANNER_PICTURE (B_ID, B_Picture) VALUES (1, '$fileName')");
                        if($insert){
                            $message = $message ? $message."<br>Additional picture uploaded!" : "Additional picture uploaded!";
                        } else {
                            $uploadError = "Error saving picture to database";
                        } 
                    } else {
                        $uploadError = "Error uploading additional picture. Please try again.";
                    }
                } else {
                    $uploadError = "Additional picture is too large (max 2MB)";
                }
            } else {
                $uploadError = "Only JPG, JPEG, PNG & GIF files are allowed for additional pictures";
            }
        } else {
            $uploadError = "Additional picture file is not an image";
        }
    }
}

// Handle picture deletion
if (isset($_GET['delete_picture'])) {
    $pic_id = (int)$_GET['delete_picture'];
    
    $result = $conn->query("SELECT B_Picture FROM BANNER_PICTURE WHERE BP_ID = $pic_id");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $filePath = $targetDir . $row['B_Picture'];
        
        $delete = $conn->query("DELETE FROM BANNER_PICTURE WHERE BP_ID = $pic_id");
        if($delete) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $message = "Picture deleted successfully!";
        } else {
            $message = "Error deleting picture: " . mysqli_error($conn);
        }
    }
}

// Fetch banner data
$banner = $conn->query("SELECT * FROM BANNER WHERE B_ID = 1")->fetch_assoc();
if (!$banner) {
    $default_text = "Welcome to CTRL-X Clothing";
    $default_picture = "default-banner.jpg";
    
    $conn->query("INSERT INTO BANNER (B_Text, B_Picture, B_Status) VALUES ('$default_text', '$default_picture', 1)");
    $banner = ['B_Text' => $default_text, 'B_Picture' => $default_picture, 'B_Status' => 1];
    
    $defaultImagePath = $targetDir . $default_picture;
    if (!file_exists($defaultImagePath)) {
        copy(__DIR__ . "/images/default-banner.jpg", $defaultImagePath);
    }
}

// Fetch additional pictures
$additional_pictures = $conn->query("SELECT * FROM BANNER_PICTURE WHERE B_ID = 1 ORDER BY BP_ID")->fetch_all(MYSQLI_ASSOC);
$picture_count = count($additional_pictures);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Banner - CTRL-X Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4a6fa5;
            --secondary: #166088;
            --accent: #4fc3dc;
            --light: #f8f9fa;
            --dark: #343a40;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1;
            padding: 2rem;
            margin-left: 250px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 1.8rem;
            color: var(--dark);
            font-weight: 600;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .card-title {
            font-size: 1.2rem;
            color: var(--secondary);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .banner-preview {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
            border: 1px dashed #ddd;
        }
        
        .banner-preview img {
            max-width: 100%;
            max-height: 300px;
            object-fit: contain;
            border-radius: 6px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .banner-text {
            font-size: 1.1rem;
            color: var(--dark);
            padding: 0.5rem;
            background: white;
            border-radius: 4px;
            display: inline-block;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 195, 220, 0.2);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .file-upload-btn {
            background: var(--light);
            color: var(--dark);
            padding: 0.75rem 1rem;
            border: 1px dashed #ccc;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-upload-btn:hover {
            background: #e9ecef;
            border-color: var(--accent);
        }
        
        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .current-file {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #666;
        }
        
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
            margin-right: 10px;
        }
        
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: var(--success);
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        .switch-label {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            color: var(--dark);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-size: 1rem;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #3a5a8f;
        }
        
        .btn-secondary {
            background-color: var(--secondary);
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #0d4b6e;
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
        }
        
        .gallery-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
        }
        
        .gallery-img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        
        .gallery-actions {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
        }
        
        .delete-btn {
            background-color: rgba(220, 53, 69, 0.8);
            color: white;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .delete-btn:hover {
            background-color: var(--danger);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .alert i {
            margin-right: 0.75rem;
            font-size: 1.2rem;
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .alert-error {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        .picture-counter {
            background-color: var(--light);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .picture-counter i {
            margin-right: 0.5rem;
            color: var(--primary);
        }
        
        .picture-counter span {
            font-weight: 600;
            color: var(--dark);
        }
        
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .gallery {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .gallery {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
        }
    </style>
</head>
<body>

<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Banner Management</h1>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($uploadError): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($uploadError) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2 class="card-title">Primary Banner</h2>
            
            <div class="banner-preview">
                <img src="uploads/banners/<?= htmlspecialchars($banner['B_Picture']) ?>" alt="Primary Banner">
                <div class="banner-text"><?= htmlspecialchars($banner['B_Text']) ?></div>
            </div>

            <form method="POST" action="banner.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">Banner Text</label>
                    <textarea class="form-control" name="banner_text" required><?= htmlspecialchars($banner['B_Text']) ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Primary Banner Image</label>
                    <div class="file-upload">
                        <label class="file-upload-btn">
                            <i class="fas fa-cloud-upload-alt"></i> Choose Image (Max 2MB)
                            <input type="file" class="file-upload-input" name="primary_picture" accept="image/*">
                        </label>
                    </div>
                    <div class="current-file">Current: <?= htmlspecialchars($banner['B_Picture']) ?></div>
                </div>

                <div class="form-group">
                    <label class="switch-label">
                        <label class="switch">
                            <input type="checkbox" name="banner_status" value="1" <?= ($banner['B_Status'] == 1) ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                        Show banner on website
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Banner
                </button>
            </form>
        </div>

        <div class="card">
            <h2 class="card-title">Additional Banner Images</h2>
            
            <div class="picture-counter">
                <i class="fas fa-images"></i>
                <span><?= $picture_count ?>/5</span> images uploaded
            </div>

            <form method="POST" action="banner.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">Upload Additional Image</label>
                    <div class="file-upload">
                        <label class="file-upload-btn <?= ($picture_count >= 5) ? 'disabled' : '' ?>">
                            <i class="fas fa-plus-circle"></i> Choose Image (Max 2MB)
                            <input type="file" class="file-upload-input" name="additional_picture" accept="image/*" <?= ($picture_count >= 5) ? 'disabled' : 'required' ?>>
                        </label>
                    </div>
                    <?php if ($picture_count >= 5): ?>
                        <div class="alert alert-error" style="margin-top: 1rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                            Maximum of 5 additional images reached. Delete some to upload new ones.
                        </div>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn btn-secondary" <?= ($picture_count >= 5) ? 'disabled' : '' ?>>
                    <i class="fas fa-upload"></i> Upload Image
                </button>
            </form>

            <?php if ($additional_pictures): ?>
            <div class="gallery">
                <?php foreach ($additional_pictures as $picture): ?>
                <div class="gallery-item">
                    <img src="uploads/banners/<?= htmlspecialchars($picture['B_Picture']) ?>" alt="Banner Image" class="gallery-img">
                    <div class="gallery-actions">
                        <a href="banner.php?delete_picture=<?= $picture['BP_ID'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this image?')">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>