<?php
session_start();
if (!isset($_SESSION['registration_success'])) {
    header('Location: register.php');
    exit;
}

$success_message = $_SESSION['registration_success'];
$email = $_SESSION['registered_email'];

// Clear the session messages
unset($_SESSION['registration_success']);
unset($_SESSION['registered_email']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - UUM Find</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl p-8 text-center">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-check-circle text-green-600 text-3xl"></i>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-4">Registration Successful!</h1>

        <p class="text-gray-600 mb-6">
            <?php echo htmlspecialchars($success_message); ?>
        </p>

        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
            <i class="fas fa-envelope text-blue-500 text-lg mb-2"></i>
            <p class="text-sm text-blue-800">
                Verification email sent to:<br>
                <strong><?php echo htmlspecialchars($email); ?></strong>
            </p>
        </div>

        <div class="space-y-3">
            <a href="login.php"
                class="w-full bg-uum-green hover:bg-uum-blue text-white py-3 px-4 rounded-xl font-semibold transition-colors block">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Go to Login
            </a>

            <a href="../index.php"
                class="w-full border border-gray-300 text-gray-700 hover:bg-gray-50 py-3 px-4 rounded-xl font-semibold transition-colors block">
                <i class="fas fa-home mr-2"></i>
                Back to Home
            </a>
        </div>

        <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
            <span class="text-sm text-yellow-800">
                Can't find the email? Check your spam folder!
            </span>
        </div>
    </div>
</body>

</html>