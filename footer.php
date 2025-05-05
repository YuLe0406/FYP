<?php
echo '
<footer class="footer">
    <div class="footer-container">
        <div class="footer-column">
            <h3>Delivery</h3>
            <ul>
                <li><a href="order.php">My Order</a></li>
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

<style>
    .footer {
        background-color: #f5f5f5;
        color: #333;
        padding: 40px 0 20px;
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
';
?>