<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - FindHire</title>
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

        .content-animation {
            animation: fade-in 1.5s ease-out;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-green-500 to-teal-600 min-h-screen flex items-center justify-center p-4">

<?php
// Database connection
$servername = "localhost"; // Change to your server name
$username = "root"; // Change to your database username
$password = ""; // Change to your database password
$dbname = "findhire"; // Change to your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize messages
$error = $success = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Secure password hashing
    $role = $_POST['role'];
    $profile_image = $_FILES['profile_image']['name'];

    // Upload profile image
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($profile_image);
    if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
        // Insert data into the database
        $sql = "INSERT INTO users (username, first_name, last_name, email, phone, password, role, profile_image)
                VALUES ('$username', '$first_name', '$last_name', '$email', '$phone', '$password', '$role', '$target_file')";

        if ($conn->query($sql) === TRUE) {
            $success = "Account created successfully!";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        $error = "Failed to upload profile image.";
    }
}
?>

<div class="max-w-4xl w-full bg-white shadow-lg rounded-lg overflow-hidden flex flex-col lg:flex-row my-auto">
    <!-- Left Container -->
    <div class="lg:w-1/2 w-full bg-gradient-to-br from-teal-500 to-green-600 flex flex-col items-center justify-center p-6 text-white rounded-lg">
        <img src="assets/findhire logo.png" alt="FindHire Logo" class="w-48 h-auto mb-4 logo-animation">
        <h2 class="text-3xl font-bold mt-0 leading-tight content-animation">Welcome to FindHire</h2>
        <p class="mt-2 text-center text-lg leading-normal content-animation">
            Join us to find your dream job or hire top talent with ease.
        </p>
    </div>

    <!-- Right Container -->
    <div class="lg:w-1/2 w-full p-6 content-animation">
        <h1 class="text-2xl lg:text-3xl font-semibold text-gray-800 text-center">Create an Account</h1>
        <p class="text-gray-600 text-center mt-2">Sign up to start your journey with FindHire.</p>

        <!-- Display Error/Success Messages -->
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4">
                <strong class="font-bold">Error:</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4">
                <strong class="font-bold">Success:</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    placeholder="Choose a username" 
                    required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                >
            </div>
            <div class="flex space-x-4">
                <div class="w-1/2">
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                    <input 
                        type="text" 
                        id="first_name" 
                        name="first_name" 
                        placeholder="First name" 
                        required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                </div>
                <div class="w-1/2">
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input 
                        type="text" 
                        id="last_name" 
                        name="last_name" 
                        placeholder="Last name" 
                        required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                </div>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="Your email address" 
                    required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                >
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                <input 
                    type="text" 
                    id="phone" 
                    name="phone" 
                    placeholder="Your phone number" 
                    required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                >
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Create a password" 
                    required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                >
            </div>
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Select Role</label>
                <select 
                    id="role" 
                    name="role" 
                    required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                >
                    <option value="" disabled selected>Select a role</option>
                    <option value="HR">HR</option>
                    <option value="Applicant">Applicant</option>
                </select>
            </div>
            <div>
                <label for="profile_image" class="block text-sm font-medium text-gray-700">Upload Profile Image</label>
                <input 
                    type="file" 
                    id="profile_image" 
                    name="profile_image" 
                    accept="image/*" 
                    required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                >
            </div>
            <button 
                type="submit" 
                class="w-full bg-green-500 text-white py-2 rounded-lg hover:bg-green-600 transition transform hover:scale-105"
            >
                Sign Up
            </button>
        </form>
        <p class="text-center text-gray-600 mt-4">
            Already have an account? 
            <a href="login.php" class="text-green-500 hover:underline">Login</a>
        </p>
    </div>
</div>
</body>
</html>
