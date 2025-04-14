<?php
include 'db.php';
session_start();

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle banner text update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["banner_text"])) {
    $new_banner_text = mysqli_real_escape_string($conn, $_POST["banner_text"]);
    $status = isset($_POST["banner_status"]) ? 1 : 0;

    // Update the banner text and status
    $sql = "UPDATE BANNER SET B_Text='$new_banner_text', B_Status=$status WHERE B_ID = 1";
    if (mysqli_query($conn, $sql)) {
        $message = "Banner updated successfully!";
    } else {
        $message = "Error updating banner: " . mysqli_error($conn);
    }
}

// Fetch banner details
$sql = "SELECT * FROM BANNER WHERE B_ID = 1";
$result = mysqli_query($conn, $sql);
$banner = mysqli_fetch_assoc($result);

// If no banner exists, create a default one
if (!$banner) {
    $default_text = "Welcome to CTRL-X Clothing";
    $sql = "INSERT INTO BANNER (B_Text, B_Status) VALUES ('$default_text', 1)";
    mysqli_query($conn, $sql);
    $banner = ['B_Text' => $default_text, 'B_Status' => 1];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Banner - CTRL-X Admin</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
            text-align: center;
        }

        .banner-preview {
            background-color: #f1f1f1;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
            min-height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .banner-preview p {
            font-size: 24px;
            color: #333;
            margin: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
            font-size: 16px;
        }

        .status-toggle {
            display: flex;
            align-items: center;
            margin-top: 15px;
        }

        .status-toggle label {
            margin: 0 0 0 10px;
            font-weight: normal;
            cursor: pointer;
        }

        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #2980b9;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="container">
        <h1>Manage Banner Text</h1>
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="banner-preview">
            <p><?php echo htmlspecialchars($banner['B_Text']); ?></p>
        </div>

        <form method="POST" action="banner.php">
            <div class="form-group">
                <label for="banner_text">Banner Text:</label>
                <textarea id="banner_text" name="banner_text" required><?php echo htmlspecialchars($banner['B_Text']); ?></textarea>
            </div>

            <div class="status-toggle">
                <input type="checkbox" id="banner_status" name="banner_status" value="1" <?php echo ($banner['B_Status'] == 1) ? 'checked' : ''; ?>>
                <label for="banner_status">Active (Show on website)</label>
            </div>

            <button type="submit">Update Banner</button>
        </form>
    </div>
</div>

</body>
</html>