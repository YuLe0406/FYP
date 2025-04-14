<?php
session_start();
// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Handle login errors
$error = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case '1':
            $error = "Invalid email or password";
            break;
        case '2':
            $error = "Account is blocked";
            break;
        case '3':
            $error = "Please login first";
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | CTRL+X</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
        }

        header {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px 5%;
            background-color: #2c3e50;
            color: white;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
            padding: 20px;
        }

        .login-form {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 500px;
        }

        .title {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }

        .inputBox {
            margin-bottom: 20px;
        }

        .inputBox label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #34495e;
        }

        .inputBox input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .inputBox i {
            margin-right: 10px;
            color: #3498db;
        }

        .submit_btn {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submit_btn:hover {
            background-color: #2980b9;
        }

        .error-message {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 20px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">CTRL+X Admin</div>
    </header>

    <div class="container">
        <form class="login-form" action="admin_login_process.php" method="POST">
            <h3 class="title">Admin Login</h3>

            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="inputBox">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="A_Email" required>
            </div>

            <div class="inputBox">
                <label><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="A_Password" required>
            </div>

            <button type="submit" class="submit_btn">Login</button>
        </form>
    </div>
</body>
</html>
