<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
include 'config.php';
include 'common.php';
// Removed 'job_card_applicant.php' inclusion from the top
// It should be included within loops where $job is defined

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$role = $_SESSION['role'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'view';

// Define pagination variables
$limit = 6; // Number of jobs per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Handle job posting by HR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'HR' && $action === 'post') {
    // Handle job posting
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $requirements = trim($_POST['requirements']);
    $location = trim($_POST['location']);
    $salary_input = trim($_POST['salary']);
    $expiration_date = $_POST['expiration_date'];
    $created_by = $_SESSION['user_id'];

    // Input validation
    $errors = [];
    
    if (empty($title)) {
        $errors[] = "Job title is required.";
    }

    if (empty($description)) {
        $errors[] = "Job description is required.";
    } elseif (strlen($description) > 1500) { // Changed from word count to character count
        $errors[] = "Job description cannot exceed 1500 characters.";
    }

    if (empty($requirements)) {
        $errors[] = "Job requirements are required.";
    } elseif (strlen($requirements) > 1500) { // Changed from word count to character count
        $errors[] = "Job requirements cannot exceed 1500 characters.";
    }

    if (empty($location)) {
        $errors[] = "Job location is required.";
    }

    if (empty($salary_input)) {
        $errors[] = "Job salary is required.";
    } elseif (!preg_match('/^[₱\d\.,\s]+$/', $salary_input)) { // Updated validation to allow Peso sign
        $errors[] = "Job salary can only contain numbers, dots, commas, spaces, and the Peso sign (₱).";
    }

    if (empty($expiration_date)) {
        $errors[] = "Expiration date is required.";
    }

    if (empty($errors)) {
        // Process salary: Remove Peso sign and commas, then convert to float
        $salary_numeric = floatval(str_replace([',', '₱'], '', $salary_input));

        $stmt = $pdo->prepare("INSERT INTO jobs (title, description, requirements, location, salary, expiration_date, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$title, $description, $requirements, $location, $salary_numeric, $expiration_date, $created_by]);
        // Set flash message and redirect
        $_SESSION['flash_message'] = "Job posted successfully!";
        header("Location: jobs.php?action=manage&page=1");
        exit();
    } else {
        // Set flash message with errors and redirect
        $_SESSION['flash_message'] = implode("<br>", $errors);
        header("Location: jobs.php?action=post&page=1");
        exit();
    }
}

// Handle job editing by HR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'HR' && $action === 'edit' && isset($_POST['job_id'])) {
    $job_id = (int)$_POST['job_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $requirements = trim($_POST['requirements']);
    $location = trim($_POST['location']);
    $salary_input = trim($_POST['salary']);
    $expiration_date = $_POST['expiration_date'];

    // Input validation
    $errors = [];

    if (empty($title)) {
        $errors[] = "Job title is required.";
    }

    if (empty($description)) {
        $errors[] = "Job description is required.";
    } elseif (strlen($description) > 1500) { // Changed from word count to character count
        $errors[] = "Job description cannot exceed 1500 characters.";
    }

    if (empty($requirements)) {
        $errors[] = "Job requirements are required.";
    } elseif (strlen($requirements) > 1500) { // Changed from word count to character count
        $errors[] = "Job requirements cannot exceed 1500 characters.";
    }

    if (empty($location)) {
        $errors[] = "Job location is required.";
    }

    if (empty($salary_input)) {
        $errors[] = "Job salary is required.";
    } elseif (!preg_match('/^[₱\d\.,\s]+$/', $salary_input)) { // Updated validation to allow Peso sign
        $errors[] = "Job salary can only contain numbers, dots, commas, spaces, and the Peso sign (₱).";
    }

    if (empty($expiration_date)) {
        $errors[] = "Expiration date is required.";
    }

    if (empty($errors)) {
        // Process salary: Remove Peso sign and commas, then convert to float
        $salary_numeric = floatval(str_replace([',', '₱'], '', $salary_input));

        $stmt = $pdo->prepare("UPDATE jobs SET title = ?, description = ?, requirements = ?, location = ?, salary = ?, expiration_date = ? WHERE id = ? AND created_by = ?");
        $stmt->execute([$title, $description, $requirements, $location, $salary_numeric, $expiration_date, $job_id, $_SESSION['user_id']]);
        // Set flash message and redirect
        $_SESSION['flash_message'] = "Job updated successfully!";
        header("Location: jobs.php?action=manage&page=1");
        exit();
    } else {
        // Set flash message with errors and redirect
        $_SESSION['flash_message'] = implode("<br>", $errors);
        header("Location: jobs.php?action=edit&job_id=$job_id&page=1");
        exit();
    }
}

// Handle job deletion by HR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'HR' && $action === 'delete' && isset($_POST['job_id'])) {
    $job_id = (int)$_POST['job_id'];

    // Delete the job record
    $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ? AND created_by = ?");
    $stmt->execute([$job_id, $_SESSION['user_id']]);

    // Set flash message and redirect
    $_SESSION['flash_message'] = "Job deleted successfully!";
    header("Location: jobs.php?action=manage&page=1");
    exit();
}

// Handle application cancellation
if (isset($_GET['cancel_application']) && isset($_GET['job_id'])) {
    $job_id = (int)$_GET['job_id'];
    $applicant_id = $_SESSION['user_id'];
    
    // Delete the application record
    $stmt = $pdo->prepare("DELETE FROM applications WHERE job_id = ? AND applicant_id = ?");
    $stmt->execute([$job_id, $applicant_id]);

    // Optionally, set a flash message
    $_SESSION['flash_message'] = "Application cancelled successfully!";
    header("Location: jobs.php?action=view&page=1");
    exit();
}

// Handle AJAX search requests for real-time search
if ($action === 'search' && $role === 'Applicant') {
    $search_query = '';
    $search_params = [];
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search_query = " AND jobs.title LIKE ?";
        $search_params[] = '%' . $_GET['search'] . '%';
    }

    // Handle 'applied' filter
    $applied_filter = '';
    if (isset($_GET['applied_filter'])) {
        if ($_GET['applied_filter'] == 'applied') {
            $applied_filter = " AND applications.applicant_id = ?";
            $search_params[] = $_SESSION['user_id'];
        } elseif ($_GET['applied_filter'] == 'not_applied') {
            $applied_filter = " AND jobs.id NOT IN (SELECT job_id FROM applications WHERE applicant_id = ?)";
            $search_params[] = $_SESSION['user_id'];
        }
    }

    // Fetch jobs
    $sql = "
        SELECT jobs.*, users.first_name, users.last_name, users.profile_image 
        FROM jobs 
        INNER JOIN users ON jobs.created_by = users.id 
        LEFT JOIN applications ON jobs.id = applications.job_id AND applications.applicant_id = ?
        WHERE jobs.expiration_date >= CURDATE() $search_query $applied_filter
        GROUP BY jobs.id
        ORDER BY jobs.created_at DESC 
        LIMIT $limit OFFSET $offset
    ";
    $stmt = $pdo->prepare($sql);
    $params = array_merge([$_SESSION['user_id']], $search_params);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll();

    // Generate HTML for jobs
    if (count($jobs) > 0) {
        ob_start();
        foreach ($jobs as $job):
            // Include the job card template within the loop where $job is defined
            include 'job_card_applicant.php';
        endforeach;
        $output = ob_get_clean();
    } else {
        $output = "<p>No jobs available at the moment.</p>";
    }

    echo $output;
    exit();
}

// Handle job search and filtering
$search_query = '';
$search_params = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = " AND jobs.title LIKE ?";
    $search_params[] = '%' . $_GET['search'] . '%';
}

// Handle 'applied' filter for Applicant
$applied_filter = '';
if ($role === 'Applicant' && isset($_GET['applied_filter'])) {
    if ($_GET['applied_filter'] == 'applied') {
        $applied_filter = " AND applications.applicant_id = ?";
        $search_params[] = $_SESSION['user_id'];
    } elseif ($_GET['applied_filter'] == 'not_applied') {
        $applied_filter = " AND jobs.id NOT IN (SELECT job_id FROM applications WHERE applicant_id = ?)";
        $search_params[] = $_SESSION['user_id'];
    }
}

// Handle 'applications' filter for HR
$applications_filter = '';
if ($role === 'HR' && isset($_GET['applications_filter'])) {
    if ($_GET['applications_filter'] == 'with_applications') {
        $applications_filter = " AND jobs.id IN (SELECT job_id FROM applications)";
    } elseif ($_GET['applications_filter'] == 'without_applications') {
        $applications_filter = " AND jobs.id NOT IN (SELECT job_id FROM applications)";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Head content -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jobs - FindHire</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js for interactivity -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        /* Animations */
        .button-hover:hover {
            transform: scale(1.05);
            transition: transform 0.3s ease;
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        .button-sm {
            padding: 8px 16px; /* Adjusted for smaller size */
            font-size: 14px;
        }

        /* Justify text */
        .text-justify {
            text-align: justify;
        }

        /* Modal Styles */
        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.5);
        }

        /* Custom scrollbar for modal */
        .modal-content::-webkit-scrollbar {
            width: 8px;
        }
        .modal-content::-webkit-scrollbar-thumb {
            background-color: rgba(107, 114, 128, 0.5);
            border-radius: 4px;
        }

        /* Character Count Styles */
        .text-green-500 {
            color: #10B981;
        }
        .text-red-500 {
            color: #EF4444;
        }
    </style>
</head>
<body class="bg-gray-100 pt-24 pb-24"> <!-- Adjusted padding for header and footer -->

    <?php headerContent(); ?>

    <div class="container mx-auto px-6 py-8">
        <div class="bg-white shadow-lg p-8 rounded-lg hover:shadow-2xl transition duration-300">

            <!-- Display Flash Messages -->
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($_SESSION['flash_message']) ?></span>
                    <?php unset($_SESSION['flash_message']); ?>
                </div>
            <?php endif; ?>

            <?php if ($role === 'HR' && $action === 'post'): ?>
                <!-- HR Post a Job -->
                <!-- Include your job posting form code here -->
                <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Post a Job</h1>
                <form action="jobs.php" method="POST" class="space-y-6" onsubmit="return validateCharacterCount();">
                    <input type="hidden" name="action" value="post">
                    <div>
                        <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Job Title</label>
                        <input type="text" name="title" id="title" placeholder="Enter the job title" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" pattern="[A-Za-z\s]{1,100}" title="Only letters and spaces allowed, max 100 characters">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Job Description</label>
                        <textarea name="description" id="description" placeholder="Provide a detailed job description" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-justify" rows="6" oninput="countCharacters(this, 'descriptionCount');" maxlength="1500"></textarea>
                        <p class="text-sm text-gray-500 mt-1">Character Count: <span id="descriptionCount">0</span>/1500</p>
                    </div>

                    <div>
                        <label for="requirements" class="block text-sm font-semibold text-gray-700 mb-2">Job Requirements</label>
                        <textarea name="requirements" id="requirements" placeholder="List the job requirements" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-justify" rows="4" oninput="countCharacters(this, 'requirementsCount');" maxlength="1500"></textarea>
                        <p class="text-sm text-gray-500 mt-1">Character Count: <span id="requirementsCount">0</span>/1500</p>
                    </div>

                    <div>
                        <label for="location" class="block text-sm font-semibold text-gray-700 mb-2">Job Location</label>
                        <input type="text" name="location" id="location" placeholder="Enter the job location" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" pattern="[A-Za-z\s]{1,100}" title="Only letters and spaces allowed, max 100 characters">
                    </div>

                    <div>
                        <label for="salary" class="block text-sm font-semibold text-gray-700 mb-2">Job Salary</label>
                        <input type="text" name="salary" id="salary" placeholder="Enter the job salary" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" pattern="^[₱\d\.,\s]+$" title="Only numbers, dots, commas, spaces, and the Peso sign (₱) are allowed">
                    </div>

                    <div>
                        <label for="expiration_date" class="block text-sm font-semibold text-gray-700 mb-2">Expiration Date</label>
                        <input type="date" name="expiration_date" id="expiration_date" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <!-- Centered and smaller Post Job Button -->
                    <div class="flex justify-center">
                        <button type="submit" class="bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-green-600 transition duration-300 button-hover">
                            Post Job
                        </button>
                    </div>
                </form>

                <!-- JavaScript for Character Count Validation -->
                <script>
                    function countCharacters(textarea, countElementId) {
                        var count = textarea.value.length;
                        var countElement = document.getElementById(countElementId);
                        countElement.innerText = count;

                        if (count === 1500) {
                            countElement.classList.add('text-red-500');
                        } else {
                            countElement.classList.remove('text-red-500');
                        }

                        if (count > 1500) {
                            textarea.value = textarea.value.substring(0, 1500);
                            countElement.innerText = 1500;
                            alert("Maximum character limit of 1500 has been reached.");
                        }
                    }

                    function validateCharacterCount() {
                        var description = document.getElementById('description').value;
                        var requirements = document.getElementById('requirements').value;
                        if (description.length > 1500) {
                            alert("Job Description cannot exceed 1500 characters.");
                            return false;
                        }
                        if (requirements.length > 1500) {
                            alert("Job Requirements cannot exceed 1500 characters.");
                            return false;
                        }
                        return true;
                    }
                </script>

            <?php elseif ($role === 'HR' && $action === 'manage'): ?>
                <!-- HR Manage Jobs -->
                <h1 class="text-3xl font-bold text-gray-800 mb-6">Manage Posted Jobs</h1>

                <!-- Applications Filter -->
                <div class="mb-6">
                    <form method="GET" action="jobs.php" class="flex items-center">
                        <input type="hidden" name="action" value="manage">
                        <label for="applications_filter" class="mr-2 text-gray-700 font-semibold">Filter Jobs:</label>
                        <select name="applications_filter" id="applications_filter" class="border border-gray-300 rounded px-3 py-2">
                            <option value="">All Jobs</option>
                            <option value="with_applications" <?= (isset($_GET['applications_filter']) && $_GET['applications_filter'] == 'with_applications') ? 'selected' : '' ?>>With Applications</option>
                            <option value="without_applications" <?= (isset($_GET['applications_filter']) && $_GET['applications_filter'] == 'without_applications') ? 'selected' : '' ?>>Without Applications</option>
                        </select>
                        <button type="submit" class="ml-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300 button-hover">Filter</button>
                    </form>
                </div>

                <?php
                // Fetch total number of jobs for pagination
                $count_sql = "SELECT COUNT(*) as total FROM jobs WHERE created_by = ? $applications_filter";
                $count_stmt = $pdo->prepare($count_sql);
                $params = array_merge([$_SESSION['user_id']]);
                $count_stmt->execute($params);
                $total_jobs = $count_stmt->fetch()['total'];
                $total_pages = ceil($total_jobs / $limit);

                // Ensure $limit and $offset are integers
                $limit = (int)$limit;
                $offset = (int)$offset;

                // Fetch jobs with pagination
                $sql = "
                    SELECT jobs.*, users.first_name, users.last_name, users.profile_image 
                    FROM jobs 
                    INNER JOIN users ON jobs.created_by = users.id 
                    WHERE jobs.created_by = ? $applications_filter
                    ORDER BY jobs.created_at DESC 
                    LIMIT $limit OFFSET $offset
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_SESSION['user_id']]);
                $jobs = $stmt->fetchAll();

                if (count($jobs) > 0):
                ?>
                    <!-- Display jobs -->
                    <!-- Include your code for displaying job cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($jobs as $job):
                            // Capitalize first letter of first name and last name
                            $first_name = ucwords(strtolower($job['first_name']));
                            $last_name = ucwords(strtolower($job['last_name']));
                            
                            // Fetch application count
                            $stmt_app = $pdo->prepare("SELECT COUNT(*) AS count FROM applications WHERE job_id = ?");
                            $stmt_app->execute([$job['id']]);
                            $app_count = $stmt_app->fetch()['count'];

                            // Format the posted date and expiration date
                            $posted_date = date("F d, Y", strtotime($job['created_at']));
                            $expiration_date = date("F d, Y", strtotime($job['expiration_date']));
                        ?>
                            <div class="job p-6 bg-gray-50 border border-gray-300 rounded-lg shadow-md fade-in flex flex-col" x-data="{ open: false }">
                                <div class="flex items-center mb-4">
                                    <img src="<?= htmlspecialchars($job['profile_image']) ?>" alt="Profile Image" class="w-12 h-12 rounded-full mr-4">
                                    <div>
                                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($first_name) ?> <?= htmlspecialchars($last_name) ?></p>
                                        <p class="text-sm text-gray-600">Posted on <?= $posted_date ?></p>
                                    </div>
                                </div>
                                <h2 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($job['title']) ?></h2>
                                <p class="text-gray-600 text-justify"><?= nl2br(htmlspecialchars(substr($job['description'], 0, 100))) ?><?php if (strlen($job['description']) > 100) echo '...'; ?></p>
                                <p class="text-gray-700 mt-2 text-justify"><strong>Requirements:</strong> <?= nl2br(htmlspecialchars(substr($job['requirements'], 0, 100))) ?><?php if (strlen($job['requirements']) > 100) echo '...'; ?></p>
                                <p class="text-gray-700 mt-2"><strong>Location:</strong> <?= htmlspecialchars($job['location']) ?></p>
                                <p class="text-gray-700 mt-2"><strong>Salary:</strong> ₱<?= number_format($job['salary'], 2) ?></p>
                                <p class="text-gray-700 mt-2"><strong>Applications Received:</strong> <?= $app_count ?></p>
                                <p class="text-gray-700 mt-2"><strong>Expires on:</strong> <?= $expiration_date ?></p>
                                <div class="mt-auto space-y-2 mt-4">
                                    <!-- View Details Button -->
                                    <button @click="open = true" type="button" class="w-full bg-green-500 text-white py-2 rounded-lg hover:bg-green-600 transition duration-300 button-hover">
                                        View Details
                                    </button>
                                    <!-- Other Buttons -->
                                    <div class="flex space-x-2">
                                        <!-- View Applications Button -->
                                        <form action="applications.php" method="GET" class="flex-1">
                                            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition duration-300 button-hover">
                                                Applications
                                            </button>
                                        </form>
                                        <!-- Edit Button -->
                                        <form action="jobs.php" method="GET" class="flex-1">
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                            <button type="submit" class="w-full bg-yellow-500 text-white py-2 rounded-lg hover:bg-yellow-600 transition duration-300 button-hover">
                                                Edit
                                            </button>
                                        </form>
                                        <!-- Delete Button -->
                                        <form action="jobs.php" method="POST" class="flex-1">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                            <button type="submit" class="w-full bg-red-500 text-white py-2 rounded-lg hover:bg-red-600 transition duration-300 button-hover" onclick="return confirm('Are you sure you want to delete this job?');">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Job Details Modal -->
                                <div x-show="open" x-transition.opacity class="fixed inset-0 flex items-center justify-center z-50" x-cloak>
                                    <div class="modal-overlay absolute inset-0 bg-black opacity-50" @click="open = false"></div>
                                    <div class="bg-white rounded-lg overflow-auto shadow-xl transform transition-all sm:max-w-4xl sm:w-full h-auto max-h-screen p-6 z-50 modal-content">
                                        <div class="px-4 py-5 sm:p-6">
                                            <div class="flex justify-between items-center">
                                                <h3 class="text-2xl leading-6 font-medium text-gray-900"><?= htmlspecialchars($job['title']) ?></h3>
                                                <!-- Close button -->
                                                <button @click="open = false" type="button" class="text-gray-400 hover:text-gray-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="mt-4">
                                                <p class="text-gray-700"><strong>Posted on:</strong> <?= $posted_date ?></p>
                                                <p class="text-gray-700"><strong>Expires on:</strong> <?= $expiration_date ?></p>
                                                <p class="text-gray-700"><strong>Location:</strong> <?= htmlspecialchars($job['location']) ?></p>
                                                <p class="text-gray-700"><strong>Salary:</strong> ₱<?= number_format($job['salary'], 2) ?></p>
                                                <!-- Job Description -->
                                                <p class="text-gray-700 text-lg font-semibold mt-2">Description:</p>
                                                <p class="text-gray-600 text-justify whitespace-pre-wrap"><?= nl2br(htmlspecialchars($job['description'])) ?></p>
                                                
                                                <p class="text-gray-700 mt-4 text-lg font-semibold">Requirements:</p>
                                                <p class="text-gray-700 text-justify whitespace-pre-wrap"><?= nl2br(htmlspecialchars($job['requirements'])) ?></p>
                                                
                                                <p class="text-gray-700 mt-2"><strong>Applications Received:</strong> <?= $app_count ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination Controls -->
                    <div class="flex justify-center mt-6">
                        <nav class="flex space-x-2" aria-label="Pagination">
                            <?php
                            // Build the base URL for pagination links
                            $base_url = "jobs.php?action=manage";
                            if (isset($_GET['applications_filter']) && !empty($_GET['applications_filter'])) {
                                $base_url .= "&applications_filter=" . urlencode($_GET['applications_filter']);
                            }

                            if ($page > 1): ?>
                                <a href="<?= $base_url ?>&page=<?= $page - 1 ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-semibold">Previous</a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="<?= $base_url ?>&page=<?= $i ?>" class="px-4 py-2 <?= $i === $page ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-700' ?> rounded-md hover:bg-gray-300 font-semibold"><?= $i ?></a>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="<?= $base_url ?>&page=<?= $page + 1 ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-semibold">Next</a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php
                else:
                    echo "<p>No jobs found based on your filter.</p>";
                endif;
                ?>

            <?php elseif ($role === 'HR' && $action === 'edit' && isset($_GET['job_id'])): ?>
                <!-- HR Edit a Job -->
                <!-- Include your job editing form code here -->
                <?php
                $job_id = (int)$_GET['job_id'];
                // Fetch job details
                $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? AND created_by = ?");
                $stmt->execute([$job_id, $_SESSION['user_id']]);
                $job = $stmt->fetch();

                if ($job):
                ?>
                    <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Edit Job</h1>
                    <form action="jobs.php" method="POST" class="space-y-6" onsubmit="return validateCharacterCount();">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="job_id" value="<?= $job_id ?>">
                        <div>
                            <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Job Title</label>
                            <input type="text" name="title" id="title" value="<?= htmlspecialchars($job['title']) ?>" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" pattern="[A-Za-z\s]{1,100}" title="Only letters and spaces allowed, max 100 characters">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Job Description</label>
                            <textarea name="description" id="description" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-justify" rows="6" oninput="countCharacters(this, 'descriptionCount');" maxlength="1500"><?= htmlspecialchars($job['description']) ?></textarea>
                            <p class="text-sm text-gray-500 mt-1">Character Count: <span id="descriptionCount"><?= strlen($job['description']) ?></span>/1500</p>
                        </div>

                        <div>
                            <label for="requirements" class="block text-sm font-semibold text-gray-700 mb-2">Job Requirements</label>
                            <textarea name="requirements" id="requirements" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-justify" rows="4" oninput="countCharacters(this, 'requirementsCount');" maxlength="1500"><?= htmlspecialchars($job['requirements']) ?></textarea>
                            <p class="text-sm text-gray-500 mt-1">Character Count: <span id="requirementsCount"><?= strlen($job['requirements']) ?></span>/1500</p>
                        </div>

                        <div>
                            <label for="location" class="block text-sm font-semibold text-gray-700 mb-2">Job Location</label>
                            <input type="text" name="location" id="location" value="<?= htmlspecialchars($job['location']) ?>" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" pattern="[A-Za-z\s]{1,100}" title="Only letters and spaces allowed, max 100 characters">
                        </div>

                        <div>
                            <label for="salary" class="block text-sm font-semibold text-gray-700 mb-2">Job Salary</label>
                            <input type="text" name="salary" id="salary" value="₱<?= number_format($job['salary'], 2) ?>" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" pattern="^[₱\d\.,\s]+$" title="Only numbers, dots, commas, spaces, and the Peso sign (₱) are allowed">
                        </div>

                        <div>
                            <label for="expiration_date" class="block text-sm font-semibold text-gray-700 mb-2">Expiration Date</label>
                            <input type="date" name="expiration_date" id="expiration_date" value="<?= htmlspecialchars($job['expiration_date']) ?>" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>

                        <!-- Centered and smaller Update Job Button -->
                        <div class="flex justify-center">
                            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition duration-300 button-hover">
                                Update Job
                            </button>
                        </div>
                    </form>

                    <!-- JavaScript for Character Count Validation -->
                    <script>
                        function countCharacters(textarea, countElementId) {
                            var count = textarea.value.length;
                            var countElement = document.getElementById(countElementId);
                            countElement.innerText = count;

                            if (count === 1500) {
                                countElement.classList.add('text-red-500');
                            } else {
                                countElement.classList.remove('text-red-500');
                            }

                            if (count > 1500) {
                                textarea.value = textarea.value.substring(0, 1500);
                                countElement.innerText = 1500;
                                alert("Maximum character limit of 1500 has been reached.");
                            }
                        }

                        function validateCharacterCount() {
                            var description = document.getElementById('description').value;
                            var requirements = document.getElementById('requirements').value;
                            if (description.length > 1500) {
                                alert("Job Description cannot exceed 1500 characters.");
                                return false;
                            }
                            if (requirements.length > 1500) {
                                alert("Job Requirements cannot exceed 1500 characters.");
                                return false;
                            }
                            return true;
                        }
                    </script>

                <?php
                else:
                    echo "<p>Job not found or you do not have permission to edit this job.</p>";
                endif;
                ?>

            <?php elseif ($role === 'Applicant' && $action === 'view'): ?>
                <!-- Applicant View Available Jobs -->
                <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Available Jobs</h1>

                <!-- Job Search Form and Applied Filter -->
<div class="mb-6">
    <form method="GET" action="jobs.php" class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-4">
        <input type="hidden" name="action" value="view">
        
        <!-- Job Title Search Input -->
        <div class="w-full md:flex-1">
            <input type="text" name="search" id="searchInput" placeholder="Search for a job title..." 
                   value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>

        <!-- Applied Filter Dropdown -->
        <div class="w-full md:w-auto">
            <select name="applied_filter" id="applied_filter" 
                    class="w-full md:w-auto px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="">All Jobs</option>
                <option value="applied" <?= (isset($_GET['applied_filter']) && $_GET['applied_filter'] == 'applied') ? 'selected' : '' ?>>Applied Jobs</option>
                <option value="not_applied" <?= (isset($_GET['applied_filter']) && $_GET['applied_filter'] == 'not_applied') ? 'selected' : '' ?>>Not Applied Jobs</option>
            </select>
        </div>

        <!-- Search Button -->
        <div class="w-full md:w-auto">
            
        </div>
    </form>
</div>


                <?php
                // Fetch total number of jobs for pagination
                $count_sql = "SELECT COUNT(DISTINCT jobs.id) as total FROM jobs LEFT JOIN applications ON jobs.id = applications.job_id AND applications.applicant_id = ? WHERE jobs.expiration_date >= CURDATE() $search_query $applied_filter";
                $count_stmt = $pdo->prepare($count_sql);
                $params = array_merge([$_SESSION['user_id']], $search_params);
                $count_stmt->execute($params);
                $total_jobs = $count_stmt->fetch()['total'];
                $total_pages = ceil($total_jobs / $limit);

                // Ensure $limit and $offset are integers
                $limit = (int)$limit;
                $offset = (int)$offset;

                // Fetch jobs with pagination
                $sql = "
                    SELECT jobs.*, users.first_name, users.last_name, users.profile_image 
                    FROM jobs 
                    INNER JOIN users ON jobs.created_by = users.id 
                    LEFT JOIN applications ON jobs.id = applications.job_id AND applications.applicant_id = ?
                    WHERE jobs.expiration_date >= CURDATE() $search_query $applied_filter
                    GROUP BY jobs.id
                    ORDER BY jobs.created_at DESC 
                    LIMIT $limit OFFSET $offset
                ";
                $stmt = $pdo->prepare($sql);
                $params = array_merge([$_SESSION['user_id']], $search_params);
                $stmt->execute($params);
                $jobs = $stmt->fetchAll();
                ?>

                <div id="jobsList" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php
                    if (count($jobs) > 0):
                        foreach ($jobs as $job):
                            // Capitalize first letter of first name and last name
                            $first_name = ucwords(strtolower($job['first_name']));
                            $last_name = ucwords(strtolower($job['last_name']));

                            // Check if the user has already applied for the job
                            $stmt_app = $pdo->prepare("SELECT status, applied_at FROM applications WHERE job_id = ? AND applicant_id = ?");
                            $stmt_app->execute([$job['id'], $_SESSION['user_id']]);
                            $application = $stmt_app->fetch();

                            // Fetch application count
                            $stmt_app_count = $pdo->prepare("SELECT COUNT(*) AS count FROM applications WHERE job_id = ?");
                            $stmt_app_count->execute([$job['id']]);
                            $app_count = $stmt_app_count->fetch()['count'];

                            // Format the posted date and expiration date
                            $posted_date = date("F d, Y", strtotime($job['created_at']));
                            $expiration_date = date("F d, Y", strtotime($job['expiration_date']));

                            // Include the job card template within the loop where $job is defined
                            include 'job_card_applicant.php';
                        endforeach;
                    else:
                        echo "<p>No jobs found based on your filter.</p>";
                    endif;
                    ?>
                </div>

                <!-- Pagination Controls -->
                <div class="flex justify-center mt-6">
                    <nav class="flex space-x-2" aria-label="Pagination">
                        <?php
                        // Build the base URL for pagination links
                        $base_url = "jobs.php?action=view";
                        if (isset($_GET['search']) && !empty($_GET['search'])) {
                            $base_url .= "&search=" . urlencode($_GET['search']);
                        }
                        if (isset($_GET['applied_filter']) && !empty($_GET['applied_filter'])) {
                            $base_url .= "&applied_filter=" . urlencode($_GET['applied_filter']);
                        }

                        if ($page > 1): ?>
                            <a href="<?= $base_url ?>&page=<?= $page - 1 ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-semibold">Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="<?= $base_url ?>&page=<?= $i ?>" class="px-4 py-2 <?= $i === $page ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-700' ?> rounded-md hover:bg-gray-300 font-semibold"><?= $i ?></a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="<?= $base_url ?>&page=<?= $page + 1 ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-semibold">Next</a>
                        <?php endif; ?>
                    </nav>
                </div>

                <!-- JavaScript for Real-Time Search -->
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var searchInput = document.getElementById('searchInput');
                    var appliedFilter = document.getElementById('applied_filter');
                    var jobsList = document.getElementById('jobsList');

                    function fetchJobs() {
                        var query = searchInput.value;
                        var applied = appliedFilter.value;

                        var xhr = new XMLHttpRequest();
                        xhr.open('GET', 'jobs.php?action=search&search=' + encodeURIComponent(query) + '&applied_filter=' + encodeURIComponent(applied), true);

                        xhr.onload = function() {
                            if (xhr.status === 200) {
                                // Update the jobs list
                                jobsList.innerHTML = xhr.responseText;
                            }
                        };
                        xhr.send();
                    }

                    searchInput.addEventListener('input', fetchJobs);
                    appliedFilter.addEventListener('change', fetchJobs);
                });
                </script>

            <?php endif; ?>

        </div>
    </div>

    <?php footerContent(); ?>

</body>
</html>
