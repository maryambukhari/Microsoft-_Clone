<?php
// Dedicated handler for chat actions like save/delete (called via AJAX from JS)
// Include DB
include 'db.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$id = (int)($_POST['id'] ?? 0);

switch ($action) {
    case 'toggle_save':
        $stmt = $pdo->prepare("UPDATE conversations SET is_saved = NOT is_saved WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'saved' => $pdo->lastInsertId() ? 1 : 0]); // Simplified
        break;
    case 'delete':
        $stmt = $pdo->prepare("DELETE FROM conversations WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>
