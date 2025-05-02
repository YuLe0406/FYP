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
        $mail->Username   = 'yltan0604@gmail.com'; // 你的Gmail邮箱
        $mail->Password   = 'zzdj mlqe vvud kzyv'; // 你的应用专用密码
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        
        // Recipients
        $mail->setFrom('noreply@ctrlx.com', 'CTRL+X');
        $mail->addAddress($toEmail, $fullName);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "CTRL+X Order Confirmation #" . $orderId;
        
        // Build email body
        $message = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Order Confirmation</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #000; padding: 20px; text-align: center; }
                .header h1 { color: #fff; margin: 0; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .order-info { background-color: #fff; padding: 15px; margin-bottom: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
                .item { display: flex; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
                .item-image { width: 80px; margin-right: 15px; }
                .item-image img { max-width: 100%; height: auto; }
                .item-details { flex: 1; }
                .total { font-weight: bold; font-size: 18px; margin-top: 20px; text-align: right; }
                .footer { margin-top: 20px; font-size: 12px; color: #777; text-align: center; }
                .thank-you { font-size: 16px; margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>CTRL+X</h1>
                </div>
                
                <div class="content">
                    <div class="thank-you">Dear ' . htmlspecialchars($fullName) . ',</div>
                    <p>Thank you for your order! Your purchase has been confirmed and will be processed shortly.</p>
                    
                    <div class="order-info">
                        <h3>Order #' . $orderId . '</h3>
                        <p>Order Date: ' . date('d F Y H:i') . '</p>
                        
                        <h4>Order Items:</h4>';
        
                        foreach ($items as $item) {
                            // 获取对应的Postimages URL
                            $postimageUrl = $postimagesMap[$item['id']] ?? $item['image'];
                            
                            $message .= '
                            <div class="item">
                                <div class="item-image">
                                    <img src="' . htmlspecialchars($postimageUrl) . '" 
                                         alt="' . htmlspecialchars($item['name']) . '"
                                         style="max-width:80px;">
                                </div>
                                <div class="item-details">
                                    <h4>' . htmlspecialchars($item['name']) . '</h4>
                                    <p>Size: ' . htmlspecialchars($item['size']) . '</p>
                                    <p>Quantity: ' . $item['quantity'] . '</p>
                                    <p>Price: RM ' . number_format($item['price'], 2) . '</p>
                                </div>
                            </div>';
                        }
        
        $message .= '
                        <div class="total">
                            <p>Total Amount: RM ' . number_format($orderTotal, 2) . '</p>
                        </div>
                        
                        <h4>Payment Method:</h4>
                        <p>' . htmlspecialchars($paymentMethod) . '</p>
                    </div>
                    
                    <p>Your order will be shipped within 1-3 business days. You will receive another email with tracking information once your order has been dispatched.</p>
                    
                    <p>If you have any questions about your order, please contact our customer service team at <a href="mailto:support@ctrlx.com">support@ctrlx.com</a> or call us at +603-1234 5678.</p>
                    
                    <p>Thank you for shopping with CTRL+X!</p>
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