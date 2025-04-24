<?php
session_start();
require __DIR__ . '/db.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

// Fetch security question for the user
$stmt = $conn->prepare("SELECT U_SecurityQuestion FROM USER WHERE U_Email = ?");
$stmt->bind_param("s", $_SESSION['reset_email']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found");
}

$user = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security Question | CTRL+X</title>
    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="logo">CTRL+X</div>
        <nav class="nav-links">
            <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
        </nav>
    </header>

    <main class="auth-container">
        <form class="auth-form" action="verify_answer.php" method="POST">
            <div class="form-header">
                <h2 class="form-title">Security Question</h2>
                <p class="form-subtitle">Answer your security question</p>
            </div>

            <div class="input-group">
                <div class="input-field">
                    <label><i class="fas fa-question-circle"></i> <?= htmlspecialchars($user['U_SecurityQuestion']) ?></label>
                    <input type="text" name="security_answer" placeholder="Your answer" required>
                </div>
            </div>

            <button type="submit" class="auth-submit-btn">
                <i class="fas fa-check"></i> Verify Answer
            </button>
        </form>
    </main>
</body>
</html>