<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['chat_with'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$chat_with = intval($_GET['chat_with']);

try {
    $stmt = $pdo->prepare("
        SELECT m.*, u.username AS sender_username
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (m.sender_id = :user_id AND m.receiver_id = :chat_with)
           OR (m.sender_id = :chat_with AND m.receiver_id = :user_id)
        ORDER BY m.created_at ASC
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':chat_with', $chat_with, PDO::PARAM_INT);
    $stmt->execute();

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'messages' => $messages]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
?>
