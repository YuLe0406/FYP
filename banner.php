<?php
include 'db.php';

// Handle banner image upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["banner"])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . "banner.jpg"; // Overwrite previous banner
    $imageFileType = strtolower(pathinfo($_FILES["banner"]["name"], PATHINFO_EXTENSION));

    // Allow only image files
    if (in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
        if (move_uploaded_file($_FILES["banner"]["tmp_name"], $target_file)) {
            $upload_message = "Banner image updated successfully!";
        } else {
            $upload_message = "Error uploading the banner image.";
        }
    } else {
        $upload_message = "Only JPG, JPEG, PNG, and GIF files are allowed.";
    }
}

// Handle banner text update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["banner_text"])) {
    $new_banner_text = mysqli_real_escape_string($conn, $_POST["banner_text"]);
    $status = isset($_POST["banner_status"]) ? 1 : 0;

    $sql = "UPDATE BANNER SET BANNER_TEXT='$new_banner_text', BANNER_STATUS=$status WHERE BANNER_ID = 1";
    if (mysqli_query($conn, $sql)) {
        $text_message = "Banner text updated successfully!";
    } else {
        $text_message = "Error updating banner text: " . mysqli_error($conn);
    }
}

// Fetch banner details
$sql = "SELECT * FROM BANNER WHERE BANNER_ID = 1";
$result = mysqli_query($conn, $sql);
$banner = mysqli_fetch_assoc($result);

// Set default banner image if no upload exists
$banner_image = file_exists("uploads/banner.jpg") ? "uploads/banner.jpg" : "default-banner.jpg";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Banner - CTRL-X Admin</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
        }

        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 600px;
            margin: auto;
            text-align: center;
        }

        .banner-container img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        .upload-form, .text-form {
            margin-top: 20px;
        }

        .message {
            color: green;
            font-weight: bold;
            margin-top: 10px;
        }

    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <div class="container">
        <h1>Manage Banner</h1>
        
        <h3>Current Banner</h3>
        <div class="banner-container">
            <img id="current-banner" src="<?php echo $banner_image; ?>" alt="Current Banner">
        </div>

        <!-- Upload Status Messages -->
        <?php if (isset($upload_message)) echo "<p class='message'>$upload_message</p>"; ?>
        <?php if (isset($text_message)) echo "<p class='message'>$text_message</p>"; ?>

        <!-- Upload New Banner Image -->
        <form class="upload-form" action="banner.php" method="POST" enctype="multipart/form-data">
            <label><b>Upload New Banner Image:</b></label><br>
            <input type="file" name="banner" accept="image/*" required>
            <br><br>
            <button type="submit">Upload Banner</button>
        </form>

        <!-- Update Banner Text -->
        <form class="text-form" action="banner.php" method="POST">
            <label><b>Update Banner Text:</b></label><br>
            <textarea name="banner_text" rows="3" cols="50"><?php echo htmlspecialchars($banner['BANNER_TEXT']); ?></textarea><br>
            <label>Active:</label>
            <input type="checkbox" name="banner_status" value="1" <?php echo ($banner['BANNER_STATUS'] == 1) ? 'checked' : ''; ?>><br><br>
            <button type="submit">Update Text</button>
        </form>
    </div>
</div>

</body>
</html>
