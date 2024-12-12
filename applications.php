<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'HR') {
    header("Location: login.php");
    exit();
}
include 'config.php';

$job_id = $_GET['job_id'] ?? null;
if (!$job_id) {
    echo "<p>Invalid Job ID.</p>";
    exit();
}

// Fetch job details
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? AND created_by = ?");
$stmt->execute([$job_id, $_SESSION['user_id']]);
$job = $stmt->fetch();

if (!$job) {
    echo "<p>Job not found or you do not have permission to view this job.</p>";
    exit();
}

// Fetch applications for the job
$stmt = $pdo->prepare("SELECT a.id AS application_id, u.first_name, u.last_name, u.email, u.phone, a.resume_path, a.status 
    FROM applications a
    JOIN users u ON a.applicant_id = u.id
    WHERE a.job_id = ?");
$stmt->execute([$job_id]);
$applications = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update application status
    $application_id = $_POST['application_id'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $stmt->execute([$status, $application_id]);

    echo "<p>Application status updated successfully.</p>";
    header("Location: applications.php?job_id=$job_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications for <?= htmlspecialchars($job['title']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
       /* Status Animation: Accepted (Green Gradients) */
@keyframes statusAccepted {
    0% { background-color: rgba(40, 167, 69, 1); }
    50% { background-color: rgba(50, 180, 80, 0.8); }
    100% { background-color: rgba(30, 150, 90, 1); }
}

/* Status Animation: Rejected (Red Gradients) */
@keyframes statusRejected {
    0% { background-color: rgba(220, 53, 69, 1); }
    50% { background-color: rgba(255, 87, 94, 0.8); }
    100% { background-color: rgba(200, 30, 60, 1); }
}

/* Status Classes */
.status-accepted {
    animation: statusAccepted 2s infinite alternate ease-in-out;
}

.status-rejected {
    animation: statusRejected 2s infinite alternate ease-in-out;
}

/* Smooth Wave Gradient Background */
body {
    background: linear-gradient(
        90deg,
        rgba(40, 167, 69, 0.9) 0%,
        rgba(50, 180, 80, 0.8) 50%,
        rgba(0, 123, 255, 0.2) 100%
    );
    background-size: 200% 200%;
    animation: waveEffect 8s infinite linear;
}

@keyframes waveEffect {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Rounded Tables */
table {
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 10px;
    overflow: hidden;
    border: none; /* Remove the outer border */
    box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1); /* Optional subtle shadow for separation */
}

th:first-child, td:first-child {
    border-top-left-radius: 10px;
    border-bottom-left-radius: 10px;
}

th:last-child, td:last-child {
    border-top-right-radius: 10px;
    border-bottom-right-radius: 10px;
}

    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

<?php include 'common.php'; headerContent(); ?>

<div class="max-w-6xl w-full bg-white shadow-lg rounded-lg overflow-hidden flex flex-col my-auto p-6 bg-opacity-50">
    <!-- Job Title -->
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Applications for: <?= htmlspecialchars($job['title']) ?></h1>

    <!-- Search Bar -->
    <div class="search-bar mb-6">
        <input type="text" id="searchInput" class="px-4 py-2 border rounded w-full" placeholder="Search by Applicant Name, Email, or Phone Number" oninput="filterApplications()">
    </div>

    <!-- Applications Table -->
    <?php if (count($applications) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto bg-white rounded-lg shadow-md">
                <thead>
                    <tr class="bg-green-500 text-white">
                        <th class="px-4 py-2 border">Applicant Name</th>
                        <th class="px-4 py-2 border">Email</th>
                        <th class="px-4 py-2 border">Phone</th>
                        <th class="px-4 py-2 border text-center">Resume</th>
                        <th class="px-4 py-2 border">Status</th>
                        <th class="px-4 py-2 border">Actions</th>
                    </tr>
                </thead>
                <tbody id="applicationsTable">
                    <?php foreach ($applications as $application): ?>
                        <tr class="border-b">
                            <td class="px-4 py-2"><?= htmlspecialchars($application['first_name'] . " " . $application['last_name']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($application['email']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($application['phone']) ?></td>
                            <td class="px-4 py-2 text-center"><a href="<?= htmlspecialchars($application['resume_path']) ?>" target="_blank" class="text-blue-500 hover:underline">View Resume</a></td>
                            <td class="px-4 py-2 text-center">
                                <span class="px-4 py-2 rounded-lg font-semibold 
                                    <?= $application['status'] == 'Accepted' ? 'bg-green-500 text-white' : ($application['status'] == 'Rejected' ? 'bg-red-500 text-white' : 'bg-yellow-500 text-white') ?> 
                                    status-animation">
                                    <?= htmlspecialchars($application['status']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <form action="applications.php?job_id=<?= $job_id ?>" method="POST" class="flex justify-center space-x-2">
                                    <input type="hidden" name="application_id" value="<?= $application['application_id'] ?>">
                                    <button type="submit" name="status" value="Accepted" class="bg-green-500 text-white py-1 px-3 rounded-lg hover:bg-green-600 transition-all">Accept</button>
                                    <button type="submit" name="status" value="Rejected" class="bg-red-500 text-white py-1 px-3 rounded-lg hover:bg-red-600 transition-all">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No applications found for this job.</p>
    <?php endif; ?>
</div>

<script>
    // Filter applications based on search input
    function filterApplications() {
        const searchQuery = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#applicationsTable tr');

        rows.forEach(row => {
            const name = row.cells[0].innerText.toLowerCase();
            const email = row.cells[1].innerText.toLowerCase();
            const phone = row.cells[2].innerText.toLowerCase();

            if (name.includes(searchQuery) || email.includes(searchQuery) || phone.includes(searchQuery)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>

<?php footerContent(); ?>

</body>
</html>
