<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'])) {
    include 'config.php';
    $application_id = $_POST['application_id'];
    
    // Verify that the application belongs to the user and is pending
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ? AND applicant_id = ? AND status = 'Pending'");
    $stmt->execute([$application_id, $_SESSION['user_id']]);
    $application = $stmt->fetch();
    
    if ($application) {
        // Update the application status to 'Withdrawn'
        $stmt = $pdo->prepare("UPDATE applications SET status = 'Withdrawn' WHERE id = ?");
        $stmt->execute([$application_id]);
        
        // Redirect back with success message
        header("Location: dashboard.php?withdraw_success=1");
        exit();
    } else {
        // Invalid request
        header("Location: dashboard.php?error=invalid_withdraw");
        exit();
    }
} else {
    // Invalid request method
    header("Location: dashboard.php");
    exit();
}
?>
