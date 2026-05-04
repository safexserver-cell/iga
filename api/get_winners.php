<?php
// api/get_winners.php
header('Content-Type: application/json');
require_once 'db.php';

try {
    $stmt = $pdo->query("SELECT * FROM laureates ORDER BY year DESC, id ASC");
    $laureates = $stmt->fetchAll();
    
    echo json_encode([
        'status' => 'success',
        'data' => $laureates
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal Server Error'
    ]);
}
?>
