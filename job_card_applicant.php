<?php
// Ensure that $job is defined and not empty
if (!isset($job) || empty($job)) {
    // Optionally log the issue for debugging
    error_log("job_card_applicant.php was included without a valid \$job.");
    // Do not output any message to avoid confusion
    return;
}

// Proceed with rendering the job card

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
    <p class="text-gray-700 mt-2"><strong>Salary:</strong> <?= htmlspecialchars($job['salary']) ?></p>
    <p class="text-gray-700 mt-2"><strong>Applications Received:</strong> <?= $app_count ?></p>
    <p class="text-gray-700 mt-2"><strong>Expires on:</strong> <?= $expiration_date ?></p>

    <?php if ($application): ?>
        <!-- User has applied, show status and possibly cancel option -->
        <div class="flex flex-col mt-4 space-y-2">
            <button class="w-full bg-gray-400 text-white py-2 rounded-lg cursor-not-allowed mt-2.5" disabled>
                Already Applied (<?= ucfirst($application['status']) ?>)
            </button>
            <p class="text-sm text-gray-600">Applied on <?= date("F d, Y", strtotime($application['applied_at'])) ?></p>

            <?php if ($application['status'] == 'Pending'): ?>
                <!-- Cancel Application Button -->
                <a href="jobs.php?action=view&cancel_application=true&job_id=<?= $job['id'] ?>" class="w-full bg-red-500 text-white py-2 rounded-lg hover:bg-red-600 transition duration-300 button-hover text-center font-semibold mt-2.5">
                    Cancel Application
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Apply for Job button -->
        <div class="mt-auto">
            <button @click="open = true" type="button" class="bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-green-600 transition duration-300 button-hover w-full mt-4">
                View Details & Apply
            </button>
        </div>
    <?php endif; ?>

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
                    <p class="text-gray-700"><strong>Salary:</strong> <?= htmlspecialchars($job['salary']) ?></p>
                    <!-- Job Description -->
                    <p class="text-gray-700 text-lg font-semibold mt-2">Description:</p>
                    <p class="text-gray-600 text-justify whitespace-pre-wrap"><?= nl2br(htmlspecialchars($job['description'])) ?></p>
                    
                    <p class="text-gray-700 mt-4 text-lg font-semibold">Requirements:</p>
                    <p class="text-gray-700 text-justify whitespace-pre-wrap"><?= nl2br(htmlspecialchars($job['requirements'])) ?></p>
                    
                    <p class="text-gray-700 mt-2"><strong>Applications Received:</strong> <?= $app_count ?></p>

                    <?php if (!$application): ?>
                        <!-- Apply for Job button -->
                        <form action="apply.php" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4">
                            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                            <div>
                                <label for="resume" class="block text-sm font-semibold text-gray-700 mb-2">Upload Resume</label>
                                <input type="file" name="resume" id="resume" accept=".pdf" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            </div>
                            <button type="submit" class="bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-green-600 transition duration-300 button-hover">
                                Apply for Job
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
