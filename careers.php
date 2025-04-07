<?php include 'header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Careers | CTRL+X</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://kit.fontawesome.com/b5e0bce514.js" crossorigin="anonymous"></script>
</head>
<body>

<section class="careers-section">
    <div class="container">
        <h1 class="page-title">Join Our Team</h1>
        
        <div class="culture-section">
            <h2>Work Culture</h2>
            <div class="culture-grid">
                <div class="culture-item">
                    <img src="images/office-space.jpg" alt="Office Environment">
                    <h4>Modern Workspace</h4>
                </div>
                <!-- Add more culture items -->
            </div>
        </div>

        <div class="open-positions">
            <h2>Current Openings</h2>
            <div class="job-listings">
                <div class="job-card">
                    <h3>Senior PHP Developer</h3>
                    <div class="job-meta">
                        <span class="location">Remote</span>
                        <span class="type">Full-time</span>
                    </div>
                    <a href="apply.php?position=php-dev" class="btn">Apply Now</a>
                </div>
                <!-- Add more job listings -->
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

</body>
</html>