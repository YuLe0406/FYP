<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Admin</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="addadmin.css">
</head>
<body>
    <div class="container">

        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <section class="add-admin">
                <h2>Add Admin</h2>
                <form class="admin-form" id="adminForm">
                    <div class="form-group">
                        <label for="adminName">Admin Name:</label>
                        <input type="text" id="adminName" name="adminName" required>
                    </div>
                    <div class="form-group">
                        <label for="adminEmail">Admin Email:</label>
                        <input type="email" id="adminEmail" name="adminEmail" required>
                    </div>
                    <div class="form-group">
                        <label for="adminContact">Contact Number:</label>
                        <input type="tel" id="adminContact" name="adminContact" pattern="[0-9]{10,12}" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Role:</label>
                        <select id="role" name="role">
                            <option value="admin">Admin</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="adminPassword">Password:</label>
                        <input type="password" id="adminPassword" name="adminPassword" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password:</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    <button type="submit" class="submit-btn">Add Admin</button>
                </form>
            </section>
            
            <section class="recent-admins">
                <h2>Recent Admins</h2>
                <ul class="admin-list">
                    <li class="admin-item">
                        <span class="admin-name">YuLe</span>
                        <span class="admin-email">yule@example.com</span>
                        <span class="admin-contact">+60123456789</span>
                        <button class="delete-btn">Delete</button>
                    </li>
                    <li class="admin-item">
                        <span class="admin-name">ShiHao</span>
                        <span class="admin-email">shihao@example.com</span>
                        <span class="admin-contact">+60987654321</span>
                        <button class="delete-btn">Delete</button>
                    </li>
                </ul>
            </section>
        </main>
    </div>

    <script>
        document.getElementById("adminForm").addEventListener("submit", function(e) {
            let password = document.getElementById("adminPassword").value;
            let confirmPassword = document.getElementById("confirmPassword").value;
            if (password !== confirmPassword) {
                alert("Passwords do not match!");
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
