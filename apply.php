
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Applicant') {
    header("Location: login.php");
    exit();
}
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = $_POST['job_id'];
    $applicant_id = $_SESSION['user_id'];

    $resume = $_FILES['resume'];
    $upload_dir = 'uploads/';
    $resume_name = time() . '_' . basename($resume['name']);
    $resume_path = $upload_dir . $resume_name;

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (move_uploaded_file($resume['tmp_name'], $resume_path)) {
        $cover_letter = $_POST['cover_letter'] ?? null;
        $stmt = $pdo->prepare("INSERT INTO applications (job_id, applicant_id, resume_path, cover_letter, status) VALUES (?, ?, ?, ?, 'Pending')");
        $stmt->execute([$job_id, $applicant_id, $resume_path, $cover_letter]);

        echo "<p>Application submitted successfully!</p>";
        header("Location: dashboard.php");
        exit();
    } else {
        echo "<p>Failed to upload resume. Please try again.</p>";
    }
} else {
    header("Location: jobs.php?action=view");
    exit();
}
?>
