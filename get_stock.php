<?php
header('Content-Type: application/json');
require 'db.php';

if (!isset($_GET['pv_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing pv_id']);
    exit;
}

$pvId = intval($_GET['pv_id']);

try {
    $stmt = $conn->prepare("SELECT P_Quantity FROM PRODUCT_VARIANTS WHERE PV_ID = ?");
    $stmt->bind_param("i", $pvId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'stock' => intval($row['P_Quantity']),
            'pv_id' => $pvId // 返回用于调试
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Variant not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    $stmt->close();
    $conn->close();
}