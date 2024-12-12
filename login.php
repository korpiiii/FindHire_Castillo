<?php
session_start();
include 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier']); // Can be email or username
    $password = $_POST['password'];

    // Check if identifier exists in the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();

    if ($user) {
        // Check if password matches
        if (password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['first_name'];
            $_SESSION['profile_image'] = $user['profile_image'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Incorrect password. Please try again.";
        }
    } else {
        $error = "No account found with the provided username or email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FindHire</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes bounce-in {
            0% {
                transform: scale(0.5);
                opacity: 0;
            }
            60% {
                transform: scale(1.2);
                opacity: 1;
            }
            100% {
                transform: scale(1);
            }
        }

        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-animation {
            animation: bounce-in 1s ease-out;
        }

        .input-animation {
            animation: fade-in 0.5s ease-in-out;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-green-500 to-teal-600 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-4xl w-full bg-white shadow-lg rounded-lg overflow-hidden flex flex-col lg:flex-row my-auto">
        <!-- Left Container -->
        <div class="lg:w-1/2 w-full bg-gradient-to-br from-teal-500 to-green-600 flex flex-col items-center justify-center p-6 text-white rounded-lg">
            <img src="assets/findhire logo.png" alt="FindHire Logo" class="w-48 h-auto mb-4 logo-animation"> <!-- Increased size and adjusted margin -->
            <h2 class="text-3xl font-bold mt-0 leading-tight">Welcome Back to FindHire</h2> <!-- Removed top margin and tightened line height -->
            <p class="mt-2 text-center text-lg leading-normal">
                Log in to continue your journey with us.
            </p>
        </div>

        <!-- Right Container -->
        <div class="lg:w-1/2 w-full p-6">
            <h1 class="text-2xl lg:text-3xl font-semibold text-gray-800 text-center">Login</h1>
            <p class="text-gray-600 text-center mt-2">Enter your credentials to access your account.</p>

            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4 input-animation">
                    <strong class="font-bold">Error: </strong>
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="mt-6 space-y-4">
                <div class="input-animation">
                    <label for="identifier" class="block text-sm font-medium text-gray-700">Email or Username</label>
                    <input 
                        type="text" 
                        id="identifier" 
                        name="identifier" 
                        placeholder="Enter your email or username" 
                        required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 transition-transform"
                    >
                </div>
                <div class="input-animation">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password" 
                        required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 transition-transform"
                    >
                </div>
                <button 
                    type="submit" 
                    class="w-full bg-green-500 text-white py-2 rounded-lg hover:bg-green-600 transition-transform transform hover:scale-105"
                >
                    Login
                </button>
            </form>
            <p class="text-center text-gray-600 mt-4">
                Don't have an account? 
                <a href="signup.php" class="text-green-500 hover:underline">Sign Up</a>
            </p>
        </div>
    </div>
</body>
</html>
