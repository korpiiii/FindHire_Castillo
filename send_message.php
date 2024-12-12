<?php
session_start();
include 'config.php'; // Database connection using PDO

if (!isset($_SESSION['user_id']) || !isset($_POST['receiver_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$user_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'];
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$attachment = isset($_FILES['attachment']) ? $_FILES['attachment'] : null;
$voice_message = isset($_FILES['voice_message']) ? $_FILES['voice_message'] : null;

try {
    // Handle file attachment upload
    $attachment_path = null;
    if ($attachment && $attachment['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!in_array($attachment['type'], $allowed_types)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid file type']);
            exit();
        }

        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($attachment['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($attachment['tmp_name'], $target_file)) {
            $attachment_path = $target_file;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File upload failed']);
            exit();
        }
    }

    // Handle voice message upload
    $voice_message_path = null;
    if ($voice_message && $voice_message['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['audio/mpeg', 'audio/wav'];
        if (!in_array($voice_message['type'], $allowed_types)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid voice message type']);
            exit();
        }

        $upload_dir = 'uploads/voice_messages/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($voice_message['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($voice_message['tmp_name'], $target_file)) {
            $voice_message_path = $target_file;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Voice message upload failed']);
            exit();
        }
    }

    // Insert message into database
    $stmt = $pdo->prepare(
        "INSERT INTO messages (sender_id, receiver_id, message, attachment, voice_message, created_at, seen_at) VALUES (:sender_id, :receiver_id, :message, :attachment, :voice_message, NOW(), NULL)"
    );
    $stmt->execute([
        'sender_id' => $user_id,
        'receiver_id' => $receiver_id,
        'message' => $message,
        'attachment' => $attachment_path,
        'voice_message' => $voice_message_path
    ]);

    // Return message details for real-time update
    $message_id = $pdo->lastInsertId();
    echo json_encode([
        'status' => 'success',
        'message' => 'Message sent successfully',
        'message_details' => [
            'id' => $message_id,
            'sender_id' => $user_id,
            'receiver_id' => $receiver_id,
            'message' => $message,
            'attachment' => $attachment_path,
            'voice_message' => $voice_message_path,
            'created_at' => date('Y-m-d H:i:s'),
            'seen_at' => null
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
