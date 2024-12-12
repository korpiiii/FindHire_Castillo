<?php
function headerContent() {
    $role = $_SESSION['role'] ?? 'Guest';
    $username = $_SESSION['username'] ?? 'Guest';
    $profile_image = $_SESSION['profile_image'] ?? 'assets/default-profile.png';

    echo '
    <header class="bg-gradient-to-br from-green-500 to-teal-600 text-white fixed top-0 left-0 w-full z-50 shadow-lg">
        <div class="container mx-auto flex flex-wrap items-center justify-between px-6 py-4">
            <div class="flex items-center justify-between w-full lg:w-auto">
                <a href="dashboard.php" class="flex items-center gap-2">
                    <img src="assets/findhire logo.png" alt="FindHire Logo" class="w-12 h-auto">
                    <span class="text-xl lg:text-2xl font-bold transition-transform transform hover:scale-110">FindHire</span>
                </a>
                <button id="mobile-menu-toggle" class="lg:hidden text-white focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button>
            </div>
            <nav id="nav-menu" class="hidden lg:flex flex-wrap items-center gap-4 mt-4 lg:mt-0">
                <a href="dashboard.php" class="hover:text-green-200 transition duration-300 ease-in-out text-sm md:text-base lg:text-lg font-medium flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6v3h3V9h-3V6H9v3H6v6h3v-3z" />
                    </svg>
                    <span class="hidden md:inline">Dashboard</span>
                </a>';
    
    // Role-based navigation links
    if ($role === 'HR') {
        echo '
                <a href="jobs.php?action=post" class="hover:text-green-200 transition duration-300 ease-in-out text-sm md:text-base lg:text-lg font-medium flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="hidden md:inline">Post Job</span>
                </a>
                <a href="jobs.php?action=manage" class="hover:text-green-200 transition duration-300 ease-in-out text-sm md:text-base lg:text-lg font-medium flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 17l-4-4-4 4m8-6l-4-4-4 4" />
                    </svg>
                    <span class="hidden md:inline">Manage Applications</span>
                </a>';
    } elseif ($role === 'Applicant') {
        echo '
                <a href="jobs.php?action=view" class="hover:text-green-200 transition duration-300 ease-in-out text-sm md:text-base lg:text-lg font-medium flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h18m-9-9v18" />
                    </svg>
                    <span class="hidden md:inline">Jobs</span>
                </a>';
    } else {
        echo '
                <a href="login.php" class="hover:text-green-200 transition duration-300 ease-in-out text-sm md:text-base lg:text-lg font-medium flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h18m-9-9v18" />
                    </svg>
                    <span class="hidden md:inline">Login</span>
                </a>
                <a href="signup.php" class="hover:text-green-200 transition duration-300 ease-in-out text-sm md:text-base lg:text-lg font-medium flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="hidden md:inline">Sign Up</span>
                </a>';
    }

    echo '
                <a href="about.php" class="hover:text-green-200 transition duration-300 ease-in-out text-sm md:text-base lg:text-lg font-medium flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m4-4H8" />
                    </svg>
                    <span class="hidden md:inline">About</span>
                </a>
            </nav>
            <div id="profile-section" class="hidden lg:flex items-center gap-4 mt-4 lg:mt-0">
                <img src="' . $profile_image . '" alt="Profile" class="w-10 h-10 rounded-full border-2 border-white transition-transform transform hover:scale-105">
                <span class="text-sm font-medium">Hi, ' . htmlspecialchars($username) . '</span>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg text-white transition-transform transform hover:scale-105">Logout</a>
            </div>
        </div>
        <div id="mobile-menu" class="lg:hidden hidden mt-4 px-6 py-2 bg-gradient-to-br from-green-500 to-teal-600 rounded-lg">
            <nav class="flex flex-col gap-4 text-white">';
    
    // Repeat the navigation for mobile
    echo '
                <a href="dashboard.php" class="hover:text-green-200 transition duration-300 ease-in-out text-sm md:text-base lg:text-lg font-medium flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6v3h3V9h-3V6H9v3H6v6h3v-3z" />
                    </svg>
                    Dashboard
                </a>';

    if ($role === 'HR') {
        echo '
                <a href="jobs.php?action=post" class="hover:text-green-200 transition duration-300 ease-in-out text-sm md:text-base lg:text-lg font-medium flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Post Job
                </a>
                <a href="jobs.php?action=manage" class="hover:text-green-200 transition duration-300 ease-in-out text-sm md:text-base lg:text-lg font-medium flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 17l-4-4-4 4m8-6l-4-4-4 4" />
                    </svg>
                    Manage Applications
                </a>';
    } elseif ($role === 'Applicant') {
        echo '
                <a href="jobs.php?action=view" class="hover:text-green-200 transition duration-300 ease-in-out text-sm md:text-base lg:text-lg font-medium flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h18m-9-9v18" />
                    </svg>
                    Jobs
                </a>';
    } else {
        echo '
                <a href="login.php" class="hover:text-green-200 transition duration-300 ease-in-out text-sm md:text-base lg:text-lg font-medium flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h18m-9-9v18" />
                    </svg>
                    Login
                </a>
                <a href="signup.php" class="hover:text-green-200 transition duration-300 ease-in-out text-sm md:text-base lg:text-lg font-medium flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Sign Up
                </a>';
    }

    echo '
                <a href="about.php" class="hover:text-green-200 transition duration-300 ease-in-out text-sm md:text-base lg:text-lg font-medium flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m4-4H8" />
                    </svg>
                    About
                </a>';

    if (isset($_SESSION['user_id'])) {
        echo '
                <div class="flex items-center gap-4 mt-4">
                    <img src="' . $profile_image . '" alt="Profile" class="w-10 h-10 rounded-full border-2 border-white transition-transform transform hover:scale-105">
                    <span class="text-sm font-medium">Hi, ' . htmlspecialchars($username) . '</span>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg text-white transition-transform transform hover:scale-105">Logout</a>
                </div>';
    }

    echo '
            </nav>
        </div>
    </header>';
}

function footerContent() {
    echo '
    <footer class="bg-gradient-to-br from-green-500 to-teal-600 text-white text-center py-4 fixed bottom-0 left-0 w-full">
        <div class="container mx-auto">
            <p class="hover:text-green-200 transition duration-300">&copy; ' . date('Y') . ' FindHire. All rights reserved.</p>
        </div>
    </footer>';

    // Messenger Icon Positioned Above the Footer at Bottom-Right Corner
    if (basename($_SERVER['PHP_SELF']) !== 'messages.php') {
        echo '
        <a href="messages.php" class="fixed bottom-20 right-4 bg-teal-600 hover:bg-teal-700 text-white rounded-full p-4 shadow-lg transition-transform transform hover:scale-110">
            <!-- Custom Icon SVG -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h12a2 2 0 012 2v10z"/>
            </svg>
        </a>';
    }
}
?>

<script>
    // Mobile menu toggle script
    document.addEventListener('DOMContentLoaded', () => {
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    });
</script>
