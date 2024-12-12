<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';
include 'common.php';

// Fetch user information
$stmt = $pdo->prepare("SELECT first_name, last_name, role, username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($user) {
    // Combine and capitalize the first and last names
    $fullName = ucfirst(strtolower($user['first_name'])) . ' ' . ucfirst(strtolower($user['last_name']));
    $role = $user['role'];
    $_SESSION['name'] = $fullName;
    $_SESSION['role'] = $role;
    $_SESSION['username'] = $user['username'];
} else {
    // If user not found, redirect to login
    header("Location: login.php");
    exit();
}

// Fetch statistics for HR or Applicant
if ($role === 'HR') {
    $stmt = $pdo->prepare("
        SELECT 
            (SELECT COUNT(*) FROM applications WHERE status = 'Accepted' AND job_id IN (SELECT id FROM jobs WHERE created_by = ?)) AS accepted,
            (SELECT COUNT(*) FROM applications WHERE status = 'Rejected' AND job_id IN (SELECT id FROM jobs WHERE created_by = ?)) AS rejected,
            (SELECT COUNT(*) FROM applications WHERE status = 'Pending' AND job_id IN (SELECT id FROM jobs WHERE created_by = ?)) AS pending
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
    $stats = $stmt->fetch();

    // Fetch average salary of HR's job postings
    $stmt_avg_salary = $pdo->prepare("SELECT AVG(salary) AS average_salary FROM jobs WHERE created_by = ?");
    $stmt_avg_salary->execute([$_SESSION['user_id']]);
    $avg_salary = $stmt_avg_salary->fetch()['average_salary'];

    // Fetch job locations breakdown
    $stmt_locations = $pdo->prepare("SELECT location, COUNT(*) AS count FROM jobs WHERE created_by = ? GROUP BY location");
    $stmt_locations->execute([$_SESSION['user_id']]);
    $job_locations = $stmt_locations->fetchAll();
} else {
    // Fetch application statistics for the applicant
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) AS total,
            SUM(CASE WHEN status = 'Accepted' THEN 1 ELSE 0 END) AS accepted,
            SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS rejected,
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
            SUM(CASE WHEN status = 'Withdrawn' THEN 1 ELSE 0 END) AS withdrawn
        FROM applications 
        WHERE applicant_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $appStats = $stmt->fetch();

    // Fetch average salary of applied jobs
    $stmt_avg_salary_applicant = $pdo->prepare("
        SELECT AVG(j.salary) AS average_salary 
        FROM applications a 
        JOIN jobs j ON a.job_id = j.id 
        WHERE a.applicant_id = ?
    ");
    $stmt_avg_salary_applicant->execute([$_SESSION['user_id']]);
    $avg_salary_applicant = $stmt_avg_salary_applicant->fetch()['average_salary'];

    // Fetch application locations breakdown
    $stmt_locations_applicant = $pdo->prepare("
        SELECT j.location, COUNT(*) AS count 
        FROM applications a 
        JOIN jobs j ON a.job_id = j.id 
        WHERE a.applicant_id = ? 
        GROUP BY j.location
    ");
    $stmt_locations_applicant->execute([$_SESSION['user_id']]);
    $application_locations = $stmt_locations_applicant->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Head content -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FindHire</title>
    <!-- Include Tailwind CSS and Chart.js -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom styles -->
    <style>
        /* Gradient animation for the container */
        @keyframes gradient-x {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        /* Applying the animation with transparency */
        .animate-gradient-x {
            background: linear-gradient(90deg, #22c55e, #14b8a6);
            background-size: 200% 200%;
            animation: gradient-x 4s ease infinite;
            color: white;
        }

        /* Custom scrollbar for tables */
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(107, 114, 128, 0.5);
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(229, 231, 235, 0.5);
        }
    </style>
</head>
<body class="bg-gray-100 pt-24 pb-24"> <!-- Adjusted padding for header and footer -->
    <?php headerContent(); ?>

    <div class="container mx-auto px-6 py-8">
        <div class="bg-white shadow-lg p-8 rounded-lg hover:shadow-2xl transition duration-300">
            <h1 class="text-4xl font-bold text-gray-800 mb-6">
                Welcome, <?= htmlspecialchars($fullName) ?>!
            </h1>

            <?php if ($role === 'HR'): ?>
                <!-- HR Dashboard Content -->
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">HR Dashboard</h2>
                <p class="text-lg text-gray-600 mb-6">
                    Manage job postings, view applications, and track key metrics.
                </p>

                <!-- Statistics Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-green-500 text-white p-6 rounded-xl shadow-md hover:shadow-xl transition duration-300">
                        <h3 class="text-lg font-semibold">Accepted Applications</h3>
                        <p class="text-3xl font-bold mt-4"><?= $stats['accepted'] ?></p>
                    </div>
                    <div class="bg-red-500 text-white p-6 rounded-xl shadow-md hover:shadow-xl transition duration-300">
                        <h3 class="text-lg font-semibold">Rejected Applications</h3>
                        <p class="text-3xl font-bold mt-4"><?= $stats['rejected'] ?></p>
                    </div>
                    <div class="bg-yellow-500 text-white p-6 rounded-xl shadow-md hover:shadow-xl transition duration-300">
                        <h3 class="text-lg font-semibold">Pending Applications</h3>
                        <p class="text-3xl font-bold mt-4"><?= $stats['pending'] ?></p>
                    </div>
                </div>

                <!-- Chart Section -->
                <div class="p-6 rounded-xl shadow-md animate-gradient-x mb-8">
                    <h3 class="text-xl font-bold mb-4">Applications Overview</h3>
                    <div class="relative">
                        <canvas id="applicationsChart" class="mx-auto" style="max-width: 350px; height: 250px;"></canvas>
                    </div>
                </div>

                <script>
                    const ctx = document.getElementById('applicationsChart').getContext('2d');
                    const applicationsChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Accepted', 'Rejected', 'Pending'],
                            datasets: [{
                                label: 'Applications Overview',
                                data: [<?= $stats['accepted'] ?>, <?= $stats['rejected'] ?>, <?= $stats['pending'] ?>],
                                backgroundColor: ['#22c55e', '#ef4444', '#facc15'],
                                borderColor: ['#16a34a', '#dc2626', '#eab308'],
                                borderWidth: 1,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: false,
                                },
                            },
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Application Status',
                                        color: '#ffffff',
                                        font: {
                                            size: 14,
                                        },
                                    },
                                    ticks: {
                                        color: '#ffffff',
                                    },
                                },
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Number of Applications',
                                        color: '#ffffff',
                                        font: {
                                            size: 14,
                                        },
                                    },
                                    ticks: {
                                        color: '#ffffff',
                                    },
                                },
                            },
                        },
                    });
                </script>

                <!-- Your Job Posts Table -->
<h3 class="text-xl font-semibold text-gray-800 mt-8 mb-4">Your Job Posts</h3>
<div class="overflow-x-auto custom-scrollbar">
    <div class="max-h-96 overflow-y-auto custom-scrollbar">
        <table class="min-w-full border-collapse rounded-xl overflow-hidden">
            <thead>
                <tr class="bg-green-500 text-white">
                    <th class="p-4 border text-left">Job Title</th>
                    <th class="p-4 border text-left">Posted Date</th>
                    <th class="p-4 border text-center">Applications</th>
                    <th class="p-4 border text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->prepare("SELECT * FROM jobs WHERE created_by = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $jobs = $stmt->fetchAll();

                if ($jobs):
                    foreach ($jobs as $job):
                        $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM applications WHERE job_id = ?");
                        $stmt->execute([$job['id']]);
                        $app_count = $stmt->fetch()['count'];
                        // Format the date
                        $postedDate = date("F d, Y", strtotime($job['created_at']));
                ?>
                    <tr class="hover:bg-gray-100">
                        <td class="p-4 border"><?= htmlspecialchars($job['title']) ?></td>
                        <td class="p-4 border"><?= htmlspecialchars($postedDate) ?></td>
                        <td class="p-4 border text-center"><?= $app_count ?></td>
                        <td class="p-4 border text-center">
                            <a href="applications.php?job_id=<?= $job['id'] ?>" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 whitespace-nowrap">View Applications</a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr>
                        <td colspan="4" class="p-4 border text-center text-gray-500">You have not posted any jobs yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


                <!-- Recent Applications -->
                <h3 class="text-xl font-semibold text-gray-800 mt-8 mb-4">Recent Applications to Your Jobs</h3>
                <div class="p-6 rounded-xl shadow-md animate-gradient-x mb-8">
                    <?php
                    // Fetch recent applications to the HR's jobs
                    $stmt = $pdo->prepare("
                        SELECT a.status, a.applied_at, j.title, u.first_name, u.last_name
                        FROM applications a
                        JOIN jobs j ON a.job_id = j.id
                        JOIN users u ON a.applicant_id = u.id
                        WHERE j.created_by = ?
                        ORDER BY a.applied_at DESC
                        LIMIT 5
                    ");
                    $stmt->execute([$_SESSION['user_id']]);
                    $recentApps = $stmt->fetchAll();
                    ?>

                    <?php if ($recentApps): ?>
                        <ul>
                            <?php foreach ($recentApps as $app): ?>
                                <li class="mb-4">
                                    <p class="text-white"><strong>Applicant:</strong> <?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?></p>
                                    <p class="text-white"><strong>Job:</strong> <?= htmlspecialchars($app['title']) ?></p>
                                    <p class="text-white"><strong>Status:</strong> 
                                        <?php
                                        $status = htmlspecialchars($app['status']);
                                        $status_class = '';
                                        if ($status == 'Pending') {
                                            $status_class = 'bg-yellow-100 text-yellow-800';
                                        } elseif ($status == 'Accepted') {
                                            $status_class = 'bg-green-100 text-green-800';
                                        } elseif ($status == 'Rejected') {
                                            $status_class = 'bg-red-100 text-red-800';
                                        } elseif ($status == 'Withdrawn') {
                                            $status_class = 'bg-gray-100 text-gray-800';
                                        }
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_class ?>">
                                            <?= $status ?>
                                        </span>
                                    </p>
                                    <p class="text-white text-sm"><?= htmlspecialchars(date("F d, Y h:i A", strtotime($app['applied_at']))) ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-white">No recent applications.</p>
                    <?php endif; ?>
                </div>

                <!-- Average Salary of Your Job Postings -->
                <h3 class="text-xl font-semibold text-gray-800 mt-8 mb-4">Average Salary of Your Job Postings</h3>
                <div class="bg-gradient-to-r from-green-400 to-blue-500 p-6 rounded-xl shadow-md mb-8 text-white">
                    <p class="text-lg">
                        The average salary for your job postings is 
                        <span class="font-bold">₱<?= number_format($avg_salary, 2) ?></span>.
                    </p>
                </div>

                <!-- Job Locations Overview -->
                <h3 class="text-xl font-semibold text-gray-800 mt-8 mb-4">Job Locations Overview</h3>
                <div class="bg-white p-6 rounded-xl shadow-md mb-8">
                    <?php if ($job_locations): ?>
                        <ul>
                            <?php foreach ($job_locations as $location): ?>
                                <li class="flex justify-between py-2 border-b last:border-b-0">
                                    <span><?= htmlspecialchars($location['location']) ?></span>
                                    <span class="font-semibold"><?= $location['count'] ?> Job(s)</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-gray-500">No job locations available.</p>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- Applicant Dashboard Content -->
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Applicant Dashboard</h2>
                <p class="text-lg text-gray-600 mb-6">
                    Track your applications and gain insights into your job search.
                </p>

                <!-- Display Success and Error Messages -->
                <?php if (isset($_GET['withdraw_success'])): ?>
                    <div class="bg-green-100 text-green-700 p-4 rounded mb-4">
                        Your application has been successfully withdrawn.
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
                        An error occurred: <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <!-- Application Status Filters -->
                <div class="mb-6">
                    <form method="GET" class="flex items-center space-x-4">
                        <label for="status" class="text-gray-700">Filter by Status:</label>
                        <select name="status" id="status" class="border border-gray-300 rounded px-3 py-2">
                            <option value="">All</option>
                            <option value="Accepted" <?= (isset($_GET['status']) && $_GET['status'] === 'Accepted') ? 'selected' : '' ?>>Accepted</option>
                            <option value="Rejected" <?= (isset($_GET['status']) && $_GET['status'] === 'Rejected') ? 'selected' : '' ?>>Rejected</option>
                            <option value="Pending" <?= (isset($_GET['status']) && $_GET['status'] === 'Pending') ? 'selected' : '' ?>>Pending</option>
                            <option value="Withdrawn" <?= (isset($_GET['status']) && $_GET['status'] === 'Withdrawn') ? 'selected' : '' ?>>Withdrawn</option>
                        </select>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Filter</button>
                    </form>
                </div>

               <!-- Your Applications Table without Actions Column -->
<h3 class="text-xl font-semibold text-gray-800 mb-4">Your Applications</h3>
<div class="overflow-x-auto custom-scrollbar">
    <div class="max-h-96 overflow-y-auto custom-scrollbar">
        <table class="table-auto w-full border-collapse rounded-xl overflow-hidden">
            <thead>
                <tr class="bg-green-500 text-white">
                    <th class="p-4 border">Job Title</th>
                    <th class="p-4 border">Job Posted Date</th>
                    <th class="p-4 border">Status</th>
                    <th class="p-4 border">Applied Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Handle status filtering
                $statusFilter = '';
                $params = [$_SESSION['user_id']];
                if (isset($_GET['status']) && in_array($_GET['status'], ['Accepted', 'Rejected', 'Pending', 'Withdrawn'])) {
                    $statusFilter = " AND a.status = ?";
                    $params[] = $_GET['status'];
                }

                $stmt = $pdo->prepare("
                    SELECT a.id, a.status, a.applied_at, j.title, j.created_at as job_posted_date
                    FROM applications a
                    JOIN jobs j ON a.job_id = j.id
                    WHERE a.applicant_id = ?" . $statusFilter . "
                    ORDER BY a.applied_at DESC
                ");
                $stmt->execute($params);
                $applications = $stmt->fetchAll();

                if ($applications):
                    foreach ($applications as $application):
                        $appliedDate = date("F d, Y", strtotime($application['applied_at']));
                        $jobPostedDate = date("F d, Y", strtotime($application['job_posted_date']));
                ?>
                    <tr class="hover:bg-gray-100">
                        <td class="p-4 border"><?= htmlspecialchars($application['title']) ?></td>
                        <td class="p-4 border"><?= htmlspecialchars($jobPostedDate) ?></td>
                        <td class="p-4 border text-center">
                            <?php
                            $status = htmlspecialchars($application['status']);
                            $status_class = '';
                            if ($status == 'Pending') {
                                $status_class = 'bg-yellow-100 text-yellow-800';
                            } elseif ($status == 'Accepted') {
                                $status_class = 'bg-green-100 text-green-800';
                            } elseif ($status == 'Rejected') {
                                $status_class = 'bg-red-100 text-red-800';
                            } elseif ($status == 'Withdrawn') {
                                $status_class = 'bg-gray-100 text-gray-800';
                            }
                            ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_class ?>">
                                <?= $status ?>
                            </span>
                        </td>
                        <td class="p-4 border text-center"><?= htmlspecialchars($appliedDate) ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr>
                        <td colspan="4" class="p-4 border text-center text-gray-500">You have not applied to any jobs yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


                <!-- Application Statistics -->
                <h3 class="text-xl font-semibold text-gray-800 mt-8 mb-4">Your Application Statistics</h3>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
                    <div class="bg-blue-500 text-white p-4 rounded-xl shadow-md">
                        <h4 class="text-lg">Total Applications</h4>
                        <p class="text-2xl"><?= $appStats['total'] ?></p>
                    </div>
                    <div class="bg-green-500 text-white p-4 rounded-xl shadow-md">
                        <h4 class="text-lg">Accepted</h4>
                        <p class="text-2xl"><?= $appStats['accepted'] ?></p>
                    </div>
                    <div class="bg-red-500 text-white p-4 rounded-xl shadow-md">
                        <h4 class="text-lg">Rejected</h4>
                        <p class="text-2xl"><?= $appStats['rejected'] ?></p>
                    </div>
                    <div class="bg-yellow-500 text-white p-4 rounded-xl shadow-md">
                        <h4 class="text-lg">Pending</h4>
                        <p class="text-2xl"><?= $appStats['pending'] ?></p>
                    </div>
                    <div class="bg-gray-500 text-white p-4 rounded-xl shadow-md">
                        <h4 class="text-lg">Withdrawn</h4>
                        <p class="text-2xl"><?= $appStats['withdrawn'] ?></p>
                    </div>
                </div>

                <!-- Recent Activity -->
                <h3 class="text-xl font-semibold text-gray-800 mt-8 mb-4">Recent Activity</h3>
                <div class="p-6 rounded-xl shadow-md animate-gradient-x mb-8">
                    <?php
                    // Fetch recent applications
                    $stmt = $pdo->prepare("
                        SELECT a.status, a.applied_at, j.title 
                        FROM applications a
                        JOIN jobs j ON a.job_id = j.id
                        WHERE a.applicant_id = ?
                        ORDER BY a.applied_at DESC
                        LIMIT 5
                    ");
                    $stmt->execute([$_SESSION['user_id']]);
                    $recentApps = $stmt->fetchAll();
                    ?>

                    <?php if ($recentApps): ?>
                        <ul>
                            <?php foreach ($recentApps as $app): ?>
                                <li class="mb-4">
                                    <p class="text-white"><strong>Job:</strong> <?= htmlspecialchars($app['title']) ?></p>
                                    <p class="text-white"><strong>Status:</strong> 
                                        <?php
                                        $status = htmlspecialchars($app['status']);
                                        $status_class = '';
                                        if ($status == 'Pending') {
                                            $status_class = 'bg-yellow-100 text-yellow-800';
                                        } elseif ($status == 'Accepted') {
                                            $status_class = 'bg-green-100 text-green-800';
                                        } elseif ($status == 'Rejected') {
                                            $status_class = 'bg-red-100 text-red-800';
                                        } elseif ($status == 'Withdrawn') {
                                            $status_class = 'bg-gray-100 text-gray-800';
                                        }
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_class ?>">
                                            <?= $status ?>
                                        </span>
                                    </p>
                                    <p class="text-white text-sm"><?= htmlspecialchars(date("F d, Y h:i A", strtotime($app['applied_at']))) ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-white">No recent activity.</p>
                    <?php endif; ?>
                </div>

                <!-- Average Salary of Applied Jobs -->
                <h3 class="text-xl font-semibold text-gray-800 mt-8 mb-4">Average Salary of Applied Jobs</h3>
                <div class="bg-gradient-to-r from-green-400 to-blue-500 p-6 rounded-xl shadow-md mb-8 text-white">
                    <?php if ($avg_salary_applicant): ?>
                        <p class="text-lg">
                            The average salary of the jobs you've applied to is 
                            <span class="font-bold">₱<?= number_format($avg_salary_applicant, 2) ?></span>.
                        </p>
                    <?php else: ?>
                        <p class="text-lg">You haven't applied to any jobs yet.</p>
                    <?php endif; ?>
                </div>

                <!-- Application Locations Overview -->
                <h3 class="text-xl font-semibold text-gray-800 mt-8 mb-4">Application Locations Overview</h3>
                <div class="bg-white p-6 rounded-xl shadow-md mb-8">
                    <?php if ($application_locations): ?>
                        <ul>
                            <?php foreach ($application_locations as $location): ?>
                                <li class="flex justify-between py-2 border-b last:border-b-0">
                                    <span><?= htmlspecialchars($location['location']) ?></span>
                                    <span class="font-semibold"><?= $location['count'] ?> Application(s)</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-gray-500">No application locations available.</p>
                    <?php endif; ?>
                </div>

               <!-- Your Application Trends -->
<h3 class="text-xl font-semibold text-gray-800 mt-8 mb-4">Your Application Trends</h3>
<div class="bg-white p-6 rounded-xl shadow-md">
    <div class="w-full md:w-3/4 lg:w-1/2 xl:w-2/3 mx-auto overflow-x-auto">
        <canvas id="applicationTrendsChart" class="w-full"></canvas>
    </div>
</div>

<script>
    <?php
    // Fetch application trends over the past 6 months
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(applied_at, '%Y-%m') AS month,
            COUNT(*) AS count
        FROM applications
        WHERE applicant_id = ?
        AND applied_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(applied_at, '%Y-%m')
        ORDER BY DATE_FORMAT(applied_at, '%Y-%m') ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $trendData = $stmt->fetchAll();

    // Prepare data for Chart.js
    $months = [];
    $counts = [];
    foreach ($trendData as $data) {
        $months[] = date("M Y", strtotime($data['month'] . "-01"));
        $counts[] = $data['count'];
    }

    // Ensure all 6 months are represented
    $allMonths = [];
    $allCounts = array_fill(0, 6, 0);
    for ($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $formattedMonth = date("M Y", strtotime($month . "-01"));
        $allMonths[] = $formattedMonth;
        $key = array_search($month, array_column($trendData, 'month'));
        if ($key !== false) {
            $allCounts[5 - $i] = (int)$trendData[$key]['count'];
        }
    }
    ?>

    const ctxTrends = document.getElementById('applicationTrendsChart').getContext('2d');
    const applicationTrendsChart = new Chart(ctxTrends, {
        type: 'line',
        data: {
            labels: <?= json_encode($allMonths) ?>,
            datasets: [{
                label: 'Number of Applications',
                data: <?= json_encode($allCounts) ?>,
                backgroundColor: 'rgba(34, 197, 94, 0.2)',
                borderColor: '#22c55e',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Month',
                        color: '#374151',
                        font: {
                            size: 14,
                        },
                    },
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Applications',
                        color: '#374151',
                        font: {
                            size: 14,
                        },
                    },
                    ticks: {
                        stepSize: 1,
                    },
                },
            },
        },
    });
</script>

            <?php endif; ?>
        </div>
    </div>

    <?php footerContent(); ?>
</body>
</html>
