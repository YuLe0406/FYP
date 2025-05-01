<?php
header('Content-Type: application/json');
require 'db.php';

if (!isset($_GET['pv_id'])) {
    echo json_encode(['error' => 'Missing pv_id']);
    exit;
}

$pvId = intval($_GET['pv_id']);

$stmt = $conn->prepare("SELECT P_Quantity FROM PRODUCT_VARIANTS WHERE PV_ID = ?");
$stmt->bind_param("i", $pvId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(['stock' => intval($row['P_Quantity'])]);
} else {
    echo json_encode(['stock' => 0]);
}

$stmt->close();
$conn->close();
