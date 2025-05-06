<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendOrderConfirmationEmail($toEmail, $orderId, $orderTotal, $items, $paymentMethod, $fullName) {
    date_default_timezone_set('Asia/Kuala_Lumpur');
    $postimagesMap = [
        1 => 'https://i.postimg.cc/43f9x34Y/1Front.png',
        2 => 'https://i.postimg.cc/P5zDHDHG/2Front.png',
        3 => 'https://i.postimg.cc/P5kD04Bz/3Front.png',
        4 => 'https://i.postimg.cc/VNKbrc5D/4Front.png',
        5 => 'https://i.postimg.cc/76cG9M3z/5Front.png',
        6 => 'https://i.postimg.cc/XX6ZSjD7/6Front.png',
        7 => 'https://i.postimg.cc/9074nRbn/7Front.png',
        8 => 'https://i.postimg.cc/pTyqcHS6/1Front.jpg',
        9 => 'https://i.postimg.cc/SKggGsg9/2Front.jpg',
        10 => 'https://i.postimg.cc/qRYQ9FVM/3Front.jpg',
        11 => 'https://i.postimg.cc/wBqWJJQH/4Front.jpg',
        12 => 'https://i.postimg.cc/6qvzwhtV/5Front.jpg',
        13 => 'https://i.postimg.cc/sfQTp37B/6Front.jpg',
        14 => 'https://i.postimg.cc/ryBQj7Nf/7Front.jpg'
    ];
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'yltan0604@gmail.com';
        $mail->Password   = 'zzdj mlqe vvud kzyv';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        
        // Recipients
        $mail->setFrom('noreply@ctrlx.com', 'CTRL+X');
        $mail->addAddress($toEmail, $fullName);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "Your CTRL+X Order #" . $orderId . " is confirmed!";
        
        // Build email body
        $message = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Order Confirmation</title>
            <style>
                body {
                    font-family: "Helvetica Neue", Arial, sans-serif;
                    line-height: 1.6;
                    color: #333333;
                    margin: 0;
                    padding: 0;
                    background-color: #f7f7f7;
                }
                .email-container {
                    max-width: 600px;
                    margin: 0 auto;
                    background: #ffffff;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
                }
                .header {
                    background-color: #000000;
                    padding: 30px 20px;
                    text-align: center;
                }
                .header h1 {
                    color: #ffffff;
                    margin: 0;
                    font-size: 24px;
                }
                .content {
                    padding: 30px;
                }
                .greeting {
                    font-size: 18px;
                    color: #333333;
                    margin-bottom: 25px;
                }
                .order-card {
                    background: #ffffff;
                    border-radius: 8px;
                    border: 1px solid #eaeaea;
                    padding: 25px;
                    margin-bottom: 25px;
                }
                .order-title {
                    font-size: 20px;
                    font-weight: bold;
                    color: #000000;
                    margin-bottom: 15px;
                    padding-bottom: 15px;
                    border-bottom: 1px solid #f0f0f0;
                }
                .order-detail {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 10px;
                    font-size: 15px;
                }
                .order-detail-label {
                    color: #666666;
                }
                .order-detail-value {
                    font-weight: 500;
                }
                .items-title {
                    font-size: 18px;
                    font-weight: bold;
                    margin: 25px 0 15px 0;
                    color: #000000;
                }
                .item {
                    display: flex;
                    margin-bottom: 20px;
                    padding-bottom: 20px;
                    border-bottom: 1px solid #f0f0f0;
                }
                .item:last-child {
                    border-bottom: none;
                    margin-bottom: 0;
                    padding-bottom: 0;
                }
                .item-image {
                    width: 80px;
                    height: 80px;
                    border-radius: 4px;
                    overflow: hidden;
                    margin-right: 15px;
                    background: #f9f9f9;
                }
                .item-image img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }
                .item-details {
                    flex: 1;
                }
                .item-name {
                    font-weight: bold;
                    margin-bottom: 5px;
                    font-size: 15px;
                }
                .item-attribute {
                    font-size: 13px;
                    color: #666666;
                    margin-bottom: 3px;
                }
                .item-price {
                    font-weight: bold;
                    margin-top: 8px;
                    font-size: 15px;
                }
                .total-section {
                    background: #f9f9f9;
                    padding: 20px;
                    border-radius: 8px;
                    margin-top: 25px;
                    text-align: right;
                }
                .total-amount {
                    font-size: 18px;
                    font-weight: bold;
                }
                .shipping-info {
                    background: #f5f5f5;
                    padding: 20px;
                    border-radius: 8px;
                    margin-top: 25px;
                }
                .shipping-title {
                    font-weight: bold;
                    margin-bottom: 10px;
                }
                .footer {
                    text-align: center;
                    padding: 20px;
                    font-size: 12px;
                    color: #999999;
                    background: #f7f7f7;
                }
                .thank-you {
                    font-size: 16px;
                    margin-bottom: 20px;
                    line-height: 1.5;
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="header">
                    <h1>CTRL+X</h1>
                </div>
                
                <div class="content">
                    <div class="greeting">Hi ' . htmlspecialchars($fullName) . ',</div>
                    
                    <div class="thank-you">
                        Thank you for your order! We\'re getting it ready to be shipped. We\'ll notify you when it\'s on its way.
                    </div>
                    
                    <div class="order-card">
                        <div class="order-title">Order Summary</div>
                        
                        <div class="order-detail">
                            <span class="order-detail-label">Order Number: </span>
                            <span class="order-detail-value">#' . $orderId . '</span>
                        </div>
                        <div class="order-detail">
                            <span class="order-detail-label">Order Date: </span>
                            <span class="order-detail-value">' . date('d F Y H:i') . '</span>
                        </div>
                        <div class="order-detail">
                            <span class="order-detail-label">Payment Method: </span>
                            <span class="order-detail-value">' . htmlspecialchars($paymentMethod) . '</span>
                        </div>
                    </div>
                    
                    <div class="items-title">Your Items</div>';
        
                    foreach ($items as $item) {
                        $postimageUrl = $postimagesMap[$item['id']] ?? $item['image'];
                        
                        $message .= '
                        <div class="item">
                            <div class="item-image">
                                <img src="' . htmlspecialchars($postimageUrl) . '" 
                                     alt="' . htmlspecialchars($item['name']) . '">
                            </div>
                            <div class="item-details">
                                <div class="item-name">' . htmlspecialchars($item['name']) . '</div>
                                <div class="item-attribute">Size: ' . htmlspecialchars($item['size']) . '</div>
                                <div class="item-attribute">Quantity: ' . $item['quantity'] . '</div>
                                <div class="item-price">RM ' . number_format($item['price'], 2) . '</div>
                            </div>
                        </div>';
                    }
        
        $message .= '
                    <div class="total-section">
                        <div class="order-detail">
                            <span class="order-detail-label">Subtotal: </span>
                            <span class="order-detail-value">RM ' . number_format($orderTotal, 2) . '</span>
                        </div>
                        <div class="order-detail">
                            <span class="order-detail-label">Shipping: </span>
                            <span class="order-detail-value">FREE</span>
                        </div>
                        <div class="order-detail">
                            <span class="order-detail-label">Total:</span>
                            <span class="order-detail-value total-amount">RM ' . number_format($orderTotal, 2) . '</span>
                        </div>
                    </div>
                    
                    <div class="shipping-info">
                        <div class="shipping-title">Shipping Information</div>
                        <p>Your order will be shipped within 1-3 business days. You will receive another email with tracking information once your order has been dispatched.</p>
                    </div>
                    
                    <p style="margin-top: 30px;">If you have any questions about your order, please contact our customer service team at <a href="mailto:support@ctrlx.com" style="color: #000000; text-decoration: underline;">support@ctrlx.com</a> or call us at +603-1234 5678.</p>
                    
                    <p style="margin-top: 20px;">Thank you for shopping with CTRL+X!</p>
                </div>
                
                <div class="footer">
                    <p>© ' . date('Y') . ' CTRL+X. All rights reserved.</p>
                    <p>This is an automated message, please do not reply directly to this email.</p>
                </div>
            </div>
        </body>
        </html>';
        
        $mail->Body = $message;
        $mail->AltBody = strip_tags($message);
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>