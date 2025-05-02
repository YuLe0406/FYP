<?php
function sendOrderConfirmationEmail($toEmail, $orderId, $orderTotal, $items, $paymentMethod) {
    // Email subject
    $subject = "CTRL+X Order Confirmation #" . $orderId;
    
    // Email headers
    $headers = "From: CTRL+X <noreply@ctrlx.com>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // Build the email body
    $message = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #000; padding: 20px; text-align: center; }
            .header img { max-width: 150px; }
            .content { padding: 20px; }
            .order-details { margin: 20px 0; }
            .item { display: flex; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
            .item-image { width: 80px; margin-right: 15px; }
            .item-info { flex: 1; }
            .footer { margin-top: 20px; font-size: 12px; color: #777; text-align: center; }
            .total { font-weight: bold; font-size: 18px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1 style="color: #fff;">CTRL+X</h1>
            </div>
            
            <div class="content">
                <h2>Thank you for your order!</h2>
                <p>Your order has been received and is being processed. Here are the details:</p>
                
                <div class="order-details">
                    <h3>Order #' . $orderId . '</h3>
                    
                    <h4>Items Ordered:</h4>';
    
    foreach ($items as $item) {
        $message .= '
                    <div class="item">
                        <div class="item-image">
                            <img src="' . $item['image'] . '" alt="' . htmlspecialchars($item['name']) . '" width="80">
                        </div>
                        <div class="item-info">
                            <h4>' . htmlspecialchars($item['name']) . '</h4>
                            <p>Size: ' . htmlspecialchars($item['size']) . '</p>
                            <p>Quantity: ' . $item['quantity'] . '</p>
                            <p>Price: RM ' . number_format($item['price'], 2) . '</p>
                        </div>
                    </div>';
    }
    
    $message .= '
                    <div class="total">
                        <p>Total: RM ' . number_format($orderTotal, 2) . '</p>
                    </div>
                    
                    <h4>Payment Method:</h4>
                    <p>' . htmlspecialchars($paymentMethod) . '</p>
                    
                    <p>We will notify you once your order has been shipped. You can check the status of your order by logging into your account.</p>
                    
                    <p>If you have any questions about your order, please contact our customer service at support@ctrlx.com or call us at +603-1234 5678.</p>
                </div>
            </div>
            
            <div class="footer">
                <p>© ' . date('Y') . ' CTRL+X. All rights reserved.</p>
                <p>This email was sent automatically. Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>';
    
    // Send the email
    return mail($toEmail, $subject, $message, $headers);
}
?>