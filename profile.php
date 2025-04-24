<?php
session_start();
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM USER WHERE U_ID = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address = $_POST['address'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $dob = $_POST['dob'] ?? '';
    
    // Update user data
    $update_stmt = $conn->prepare("UPDATE USER SET U_Address = ?, U_Gender = ?, U_DOB = ? WHERE U_ID = ?");
    $update_stmt->bind_param("sssi", $address, $gender, $dob, $_SESSION['user_id']);
    
    if ($update_stmt->execute()) {
        $success = "Profile updated successfully!";
        // Refresh user data
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    } else {
        $error = "Failed to update profile: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | CTRL+X</title>
    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
            border: 3px solid var(--accent);
        }
        .profile-details {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .detail-group {
            margin-bottom: 1.5rem;
        }
        .detail-label {
            font-weight: 500;
            color: var(--secondary);
            margin-bottom: 0.5rem;
            display: block;
        }
        .detail-value {
            padding: 0.8rem;
            background: var(--light);
            border-radius: 6px;
            border: 1px solid var(--gray);
        }
        .edit-btn {
            background: var(--accent);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">CTRL+X</div>
        <nav class="nav-links">
            <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
            <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </header>

    <main class="auth-container">
        <div class="auth-form">
            <div class="profile-header">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['U_FName'].'+'.$user['U_LName']) ?>&background=4CAF50&color=fff" 
                     alt="Profile" class="profile-avatar">
                <h2 class="form-title"><?= htmlspecialchars($user['U_FName'] . ' ' . $user['U_LName']) ?></h2>
                <p class="form-subtitle">Member since <?= date('F Y', strtotime($user['U_AccountCreated'])) ?></p>
            </div>

            <form method="POST">
                <div class="profile-details">
                    <!-- Email (readonly) -->
                    <div class="detail-group">
                        <label class="detail-label"><i class="fas fa-envelope"></i> Email</label>
                        <div class="detail-value"><?= htmlspecialchars($user['U_Email']) ?></div>
                    </div>

                    <!-- Phone (readonly) -->
                    <div class="detail-group">
                        <label class="detail-label"><i class="fas fa-phone"></i> Phone</label>
                        <div class="detail-value"><?= htmlspecialchars($user['U_PNumber']) ?></div>
                    </div>

                    <!-- Address (editable) -->
                    <div class="input-field">
                        <label for="address"><i class="fas fa-map-marker-alt"></i> Address</label>
                        <input type="text" id="address" name="address" value="<?= htmlspecialchars($user['U_Address'] ?? '') ?>" 
                               placeholder="Enter your address">
                    </div>

                    <!-- Gender (editable) -->
                    <div class="input-field">
                        <label><i class="fas fa-venus-mars"></i> Gender</label>
                        <select name="gender" class="detail-value" style="width:100%;padding:0.8rem;">
                            <option value="male" <?= $user['U_Gender'] == 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= $user['U_Gender'] == 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= $user['U_Gender'] == 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <!-- Birthday (editable) -->
                    <div class="input-field">
                        <label for="dob"><i class="fas fa-birthday-cake"></i> Date of Birth</label>
                        <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($user['U_DOB']) ?>" 
                               max="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <button type="submit" class="auth-submit-btn">
                    <i class="fas fa-save"></i> Save Changes
                </button>

                <?php if (isset($success)): ?>
                    <script>
                    Swal.fire({
                        title: 'Success!',
                        text: '<?= $success ?>',
                        icon: 'success',
                        confirmButtonColor: '#4CAF50'
                    });
                    </script>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <script>
                    Swal.fire({
                        title: 'Error!',
                        text: '<?= $error ?>',
                        icon: 'error',
                        confirmButtonColor: '#4CAF50'
                    });
                    </script>
                <?php endif; ?>
            </form>
        </div>
    </main>
</body>
</html>