<?php
session_start();
include 'common.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - FindHire</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 pt-16 pb-16">
    <?php headerContent(); ?>

    <div class="container mx-auto px-6 py-8">
        <div class="bg-white shadow-lg p-8 rounded-lg hover:shadow-2xl transition duration-300">
            <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">About FindHire</h1>
            <p class="text-lg text-gray-600 leading-relaxed mb-6">
                FindHire is an innovative job portal designed to bridge the gap between employers and applicants. With a user-friendly interface and features tailored for Human Resources (HR) and Applicants, we aim to simplify job applications and communication processes.
            </p>

            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Features</h2>
            <ul class="list-disc list-inside text-lg text-gray-600 space-y-2">
                <li><strong>Post Jobs:</strong> HR professionals can post job openings effortlessly.</li>
                <li><strong>Manage Applications:</strong> HR can track and manage applications in real-time.</li>
                <li><strong>Apply for Jobs:</strong> Applicants can find and apply for jobs easily.</li>
                <li><strong>Real-time Messaging:</strong> Communication between HR and Applicants is smooth and efficient.</li>
                <li><strong>Responsive Design:</strong> Enjoy a modern and user-friendly experience on all devices.</li>
            </ul>
        </div>
    </div>

    <?php footerContent(); ?>
</body>
</html>
