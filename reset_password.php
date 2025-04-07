<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password | CTRL+X</title>
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
        <form class="payment-form" action="update_password.php" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
            <div class="row">
                <div class="col">
                    <h3 class="title">Set New Password</h3>
                    
                    <div class="inputBox">
                        <label><i class="fas fa-lock"></i> New Password</label>
                        <input type="password" name="new_password" required>
                    </div>

                    <div class="inputBox">
                        <label><i class="fas fa-lock"></i> Confirm Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="submit_btn">Reset Password</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>