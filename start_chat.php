<?php include 'includes/config.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start Chat - UUM Find</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold text-uum-green mb-4">Start a Conversation</h2>

        <?php if (isset($_SESSION['user_id'])): ?>
            <form action="api/create_conversation.php" method="POST">
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Select User to Chat With:</label>
                    <select name="receiver_id" class="w-full border border-gray-300 rounded-lg p-2" required>
                        <option value="">Select a user...</option>
                        <?php
                        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id != ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $users = $stmt->fetchAll();

                        foreach ($users as $user) {
                            echo "<option value='{$user['id']}'>{$user['username']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit"
                    class="w-full bg-uum-green text-white py-2 rounded-lg hover:bg-uum-blue transition-colors">
                    Start Chatting
                </button>
            </form>
        <?php else: ?>
            <p class="text-gray-600">Please <a href="auth/login.php" class="text-uum-green hover:underline">login</a> to
                start chatting.</p>
        <?php endif; ?>
    </div>
</body>

</html>