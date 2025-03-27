<?php
// Check if a new banner is uploaded
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["banner"])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . "banner.jpg"; // Overwrite previous banner
    $imageFileType = strtolower(pathinfo($_FILES["banner"]["name"], PATHINFO_EXTENSION));

    // Allow only image files
    if (in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
        if (move_uploaded_file($_FILES["banner"]["tmp_name"], $target_file)) {
            $upload_message = "Banner updated successfully!";
        } else {
            $upload_message = "Error uploading the banner.";
        }
    } else {
        $upload_message = "Only JPG, JPEG, PNG, and GIF files are allowed.";
    }
}

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

        .upload-form {
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

<!-- Sidebar -->
<nav class="sidebar">
    <h2>CTRL-X Admin</h2>
    <ul>
        <li><a href="dashboard.html">Dashboard</a></li>
        <li><a href="admin.html">Admin</a></li>
        <li><a href="category.html">Category</a></li>
        <li><a href="product.html">Product</a></li>
        <li><a href="customer.html">Customer List</a></li>
        <li><a href="orderlist.html">Order List</a></li>
        <li><a href="report.html">Generate Report</a></li>
        <li><a href="banner.php"><b>Banner</b></a></li>
    </ul>
    
    <div class="sidebar-icon">
        <a href="admin_profile.php">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
            </svg>
        </a>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <div class="container">
        <h1>Manage Banner</h1>
        <h3>Current Banner</h3>

        <!-- Display Current Banner -->
        <img id="current-banner" src="<?php echo $banner_image; ?>" alt="Current Banner">
        
        <!-- Upload Status Message -->
        <?php if (isset($upload_message)) echo "<p class='message'>$upload_message</p>"; ?>

        <!-- Upload New Banner -->
        <form class="upload-form" action="banner.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="banner" accept="image/*" required>
            <br><br>
            <button type="submit">Upload New Banner</button>
        </form>
    </div>
</div>

</body>
</html>
