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

// Set upload directory (using absolute path for Windows)
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
        // Sanitize filename
        $fileName = preg_replace("/[^a-zA-Z0-9\.]/", "_", basename($_FILES["primary_picture"]["name"]));
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        
        // Check if file is an actual image
        $check = getimagesize($_FILES["primary_picture"]["tmp_name"]);
        if($check !== false) {
            // Allow certain file formats
            $allowTypes = array('jpg','png','jpeg','gif');
            if(in_array($fileType, $allowTypes)) {
                // Check file size (max 2MB)
                if ($_FILES["primary_picture"]["size"] <= 2000000) {
                    // Generate unique filename if file exists
                    $counter = 1;
                    while (file_exists($targetFilePath)) {
                        $fileInfo = pathinfo($fileName);
                        $fileName = $fileInfo['filename'] . '_' . $counter . '.' . $fileInfo['extension'];
                        $targetFilePath = $targetDir . $fileName;
                        $counter++;
                    }
                    
                    // Try to move uploaded file
                    if (move_uploaded_file($_FILES["primary_picture"]["tmp_name"], $targetFilePath)) {
                        $primaryPicture = $fileName;
                    } else {
                        $uploadError = "Error uploading primary picture. Please try again.";
                        error_log("Upload error: " . $_FILES["primary_picture"]["error"]);
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

    // Update banner (either with or without new primary picture)
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
    // First check if we've reached the maximum of 5 pictures
    $countResult = $conn->query("SELECT COUNT(*) as count FROM BANNER_PICTURE WHERE B_ID = 1");
    $countRow = $countResult->fetch_assoc();
    
    if ($countRow['count'] >= 5) {
        $uploadError = "Maximum of 5 additional pictures reached. Please delete some before uploading new ones.";
    } else {
        // Sanitize filename
        $fileName = preg_replace("/[^a-zA-Z0-9\.]/", "_", basename($_FILES["additional_picture"]["name"]));
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        
        $check = getimagesize($_FILES["additional_picture"]["tmp_name"]);
        if($check !== false) {
            $allowTypes = array('jpg','png','jpeg','gif');
            if(in_array($fileType, $allowTypes)) {
                // Check file size (max 2MB)
                if ($_FILES["additional_picture"]["size"] <= 2000000) {
                    // Generate unique filename if file exists
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
                        error_log("Upload error: " . $_FILES["additional_picture"]["error"]);
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
    
    // Get filename first so we can delete the file
    $result = $conn->query("SELECT B_Picture FROM BANNER_PICTURE WHERE BP_ID = $pic_id");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $filePath = $targetDir . $row['B_Picture'];
        
        // Delete from database
        $delete = $conn->query("DELETE FROM BANNER_PICTURE WHERE BP_ID = $pic_id");
        if($delete) {
            // Delete the actual file
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
    
    // Create default banner if none exists
    $conn->query("INSERT INTO BANNER (B_Text, B_Picture, B_Status) VALUES ('$default_text', '$default_picture', 1)");
    $banner = ['B_Text' => $default_text, 'B_Picture' => $default_picture, 'B_Status' => 1];
    
    // Copy default image to uploads directory if it doesn't exist
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
    <link rel="stylesheet" href="admin.css">
    <style>
        .banner-management {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .banner-preview {
            background-color: #f1f1f1;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .primary-banner {
            margin-bottom: 40px;
        }
        .picture-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .picture-item {
            position: relative;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }
        .picture-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .picture-actions {
            position: absolute;
            top: 5px;
            right: 5px;
        }
        .delete-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
        }
        .status-toggle {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        textarea {
            width: 100%;
            min-height: 100px;
            padding: 10px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .picture-limit {
            color: #666;
            font-style: italic;
            margin-bottom: 15px;
        }
        @media (max-width: 768px) {
            .banner-management {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="container">
        <h1>Manage Banner</h1>
        
        <?php if ($message): ?>
            <div class="alert success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($uploadError): ?>
            <div class="alert error"><?= htmlspecialchars($uploadError) ?></div>
        <?php endif; ?>

        <div class="banner-management">
            <!-- Primary Banner Section -->
            <div class="primary-banner">
                <h2>Primary Banner</h2>
                <div class="banner-preview">
                    <img src="uploads/banners/<?= htmlspecialchars($banner['B_Picture']) ?>" alt="Primary Banner" style="max-width:100%; margin-bottom:15px;">
                    <p><?= htmlspecialchars($banner['B_Text']) ?></p>
                </div>

                <form method="POST" action="banner.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Banner Text:</label>
                        <textarea name="banner_text" required><?= htmlspecialchars($banner['B_Text']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Primary Picture (2MB max):</label>
                        <input type="file" name="primary_picture" accept="image/*">
                        <small>Current: <?= htmlspecialchars($banner['B_Picture']) ?></small>
                    </div>

                    <div class="status-toggle">
                        <input type="checkbox" id="banner_status" name="banner_status" value="1" <?= ($banner['B_Status'] == 1) ? 'checked' : '' ?>>
                        <label for="banner_status">Active (Show on website)</label>
                    </div>

                    <button type="submit" class="btn-primary">Update Primary Banner</button>
                </form>
            </div>

            <!-- Additional Pictures Section -->
            <div class="additional-pictures">
                <h2>Additional Pictures</h2>
                
                <p class="picture-limit">You have <?= $picture_count ?> of 5 additional pictures</p>
                
                <form method="POST" action="banner.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Upload Additional Picture (2MB max):</label>
                        <input type="file" name="additional_picture" accept="image/*" <?= ($picture_count >= 5) ? 'disabled' : 'required' ?>>
                        <?php if ($picture_count >= 5): ?>
                            <p class="error">Maximum of 5 additional pictures reached. Delete some to upload new ones.</p>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn-secondary" <?= ($picture_count >= 5) ? 'disabled' : '' ?>>Upload Picture</button>
                </form>

                <?php if ($additional_pictures): ?>
                <h3>Current Additional Pictures</h3>
                <div class="picture-gallery">
                    <?php foreach ($additional_pictures as $picture): ?>
                    <div class="picture-item">
                        <img src="uploads/banners/<?= htmlspecialchars($picture['B_Picture']) ?>" alt="Banner Image">
                        <div class="picture-actions">
                            <a href="banner.php?delete_picture=<?= $picture['BP_ID'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this picture?')">×</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>