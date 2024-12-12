<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['unreadMessages' => 0]);
    exit();
}

include 'config.php'; // Include database connection

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = :user_id AND is_read = 0");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$unreadMessages = $stmt->fetchColumn();

echo json_encode(['unreadMessages' => $unreadMessages]);
?>
