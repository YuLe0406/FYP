<?php
require 'db.php';

header('Content-Type: application/json');

// Make sure it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $P_ID = $data['P_ID'] ?? null;  // Use capital letters to match JS
    $P_Size = $data['P_Size'] ?? null;

    if ($P_ID && $P_Size) {
        $stmt = $conn->prepare("SELECT PV_ID FROM PRODUCT_VARIANTS WHERE P_ID = ? AND P_Size = ?");
        $stmt->bind_param("is", $P_ID, $P_Size);
        $stmt->execute();
        $stmt->bind_result($PV_ID);
        if ($stmt->fetch()) {
            echo json_encode(['PV_ID' => $PV_ID]);
        } else {
            echo json_encode(['PV_ID' => null]);
        }
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Missing P_ID or P_Size']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
}
?>
