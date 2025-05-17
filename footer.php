<?php
// Start session (if not already started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in (replace with your actual session variable)
$isLoggedIn = isset($_SESSION['user_id']); // Change 'user_id' to your actual session variable

echo '
<div class="page-wrapper">
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-column">
                <h3>Delivery</h3>
                <ul>
                    <li><a href="order.php" id="myOrderLink">My Order</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3>About Us</h3>
                <ul>
                    <li><a href="#">Our Story</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3>Customer Service</h3>
                <p>📞 +6010 828 0026</p>
                <p>📧 support@ctrlx.com</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-github"></i></a>
                </div>
            </div>

            <div class="footer-column">
                <h3>Operation Time</h3>
                <p>Mon - Fri: 10:00am - 10:00pm</p>
            </div>

            <div class="footer-column feedback">
                <h3>Feedback</h3>
                <form>
                    <input type="email" placeholder="Enter your email">
                    <textarea placeholder="Your feedback"></textarea>
                    <button type="submit">Submit</button>
                </form>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© 2025 CTRL+X. All Rights Reserved.</p>
        </div>
    </footer>
</div>

<style>
    /* Your existing CSS styles */
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }
    
    .page-wrapper {
        min-height: 45%;
        display: flex;
        flex-direction: column;
    }
    
    main {
        flex: 1;
    }
    
    .footer {
        background-color: #f5f5f5;
        color: #333;
        padding: 40px 0 20px;
        margin-top: auto;
    }
    
    .footer-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .footer-column {
        flex: 1;
        min-width: 200px;
        margin-bottom: 20px;
        padding: 0 15px;
    }
    
    .footer-column h3 {
        margin-bottom: 15px;
        font-size: 18px;
    }
    
    .footer-column ul {
        list-style: none;
        padding: 0;
    }
    
    .footer-column ul li {
        margin-bottom: 8px;
    }
    
    .footer-column ul li a {
        color: #555;
        text-decoration: none;
    }
    
    .footer-column ul li a:hover {
        color: #000;
        text-decoration: underline;
    }
    
    .footer-column p {
        margin: 8px 0;
        color: #555;
    }
    
    .social-icons {
        margin-top: 15px;
    }
    
    .social-icons a {
        color: #555;
        font-size: 20px;
        margin-right: 15px;
    }
    
    .social-icons a:hover {
        color: #000;
    }
    
    .feedback form {
        display: flex;
        flex-direction: column;
    }
    
    .feedback input,
    .feedback textarea {
        margin-bottom: 10px;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .feedback textarea {
        height: 80px;
        resize: vertical;
    }
    
    .feedback button {
        padding: 8px 15px;
        background-color: #333;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    
    .feedback button:hover {
        background-color: #000;
    }
    
    .footer-bottom {
        text-align: center;
        padding: 20px 0 0;
        border-top: 1px solid #ddd;
        margin-top: 20px;
    }
    
    .footer-bottom p {
        color: #555;
        margin: 0;
    }
</style>

<!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const myOrderLink = document.getElementById("myOrderLink");
        
        if (myOrderLink) {
            myOrderLink.addEventListener("click", function(e) {
                // Check login status from PHP variable
                const isLoggedIn = ' . ($isLoggedIn ? 'true' : 'false') . ';
                
                if (!isLoggedIn) {
                    e.preventDefault();
                    
                    Swal.fire({
                        title: "Login Required",
                        text: "You need to login to view your orders",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Go to Login",
                        cancelButtonText: "Cancel",
                        customClass: {
                            confirmButton: "swal-confirm-button",
                            cancelButton: "swal-cancel-button"
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "login.html";
                        }
                    });
                }
            });
        }
    });
</script>
';
?>