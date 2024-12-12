<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$search_query = $_GET['q'] ?? '';
$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT id, username, profile_image FROM users WHERE username LIKE :search AND id != :user_id");
    $search_term = '%' . $search_query . '%';
    $stmt->bindParam(':search', $search_term);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'users' => $users]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
?>
