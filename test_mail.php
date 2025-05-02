<?php
require 'send_order_email.php';

// 测试数据 - 替换为你的真实信息
$testResult = sendOrderConfirmationEmail(
    'gamingsham0406@gmail.com', // 替换为你的接收邮箱
    12345,                      // 测试订单号
    299.99,                     // 测试总金额
    [
        [
            'id' => 1,
            'name' => '测试商品',
            'price' => 149.99,
            'quantity' => 2,
            'size' => 'L',
            'image' => 'https://example.com/product.jpg'
        ]
    ],
    'Credit Card',
    '测试用户'
);

echo $testResult ? '邮件发送成功！' : '邮件发送失败';
?>