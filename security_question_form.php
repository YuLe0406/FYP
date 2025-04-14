<?php
session_start();
require 'includes/config.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.html");
    exit();
}

$stmt = $conn->prepare("SELECT U_SecurityQuestion FROM USER WHERE U_Email = ?");
$stmt->execute([$_SESSION['reset_email']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Security Question | CTRL+X</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="logo">CTRL+X</div>
        <div class="icons">
            <a href="index.php" class="icon"><i class="fas fa-home"></i> Home</a>
        </div>
    </header>

    <div class="container" style="min-height: 80vh;">
        <form class="payment-form" action="verify_answer.php" method="POST">
            <div class="row">
                <div class="col">
                    <h3 class="title">Security Question</h3>
                    
                    <div class="inputBox">
                        <label><?= htmlspecialchars($user['U_SecurityQuestion']) ?></label>
                        <input type="text" name="security_answer" required>
                    </div>

                    <button type="submit" class="submit_btn">Verify Answer</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>