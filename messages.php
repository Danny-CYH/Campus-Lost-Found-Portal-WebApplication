<?php
require_once 'includes/config.php';
include 'includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

// Get conversations for chat
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT c.*, 
           u.username, 
           u.id as other_user_id,
           (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
           (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message_time
    FROM conversations c
    JOIN users u ON (
        (c.user1_id = u.id AND c.user1_id != ?) OR 
        (c.user2_id = u.id AND c.user2_id != ?)
    )
    WHERE c.user1_id = ? OR c.user2_id = ?
    ORDER BY last_message_time DESC
");
$stmt->execute([$user_id, $user_id, $user_id, $user_id]);
$conversations = $stmt->fetchAll();

// Get all users for new chat
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE id != ? ORDER BY username");
$stmt->execute([$user_id]);
$all_users = $stmt->fetchAll();

// Handle conversation start from view-item.php
$pre_selected_conversation_id = null;
$pre_selected_user_id = null;
$item_context = null;

if (isset($_GET['start_conversation'])) {
    $pre_selected_user_id = $_GET['start_conversation'];

    // Check if we have a specific conversation ID
    if (isset($_GET['conversation_id'])) {
        $pre_selected_conversation_id = $_GET['conversation_id'];

        // Verify this conversation belongs to current user
        $stmt = $pdo->prepare("SELECT * FROM conversations WHERE id = ? AND (user1_id = ? OR user2_id = ?)");
        $stmt->execute([$pre_selected_conversation_id, $user_id, $user_id]);
        $conversation = $stmt->fetch();

        if (!$conversation) {
            $pre_selected_conversation_id = null;
        }
    }

    // If no conversation ID but we have a user ID, find or create conversation
    if (!$pre_selected_conversation_id && $pre_selected_user_id) {
        $stmt = $pdo->prepare("
            SELECT id FROM conversations 
            WHERE (user1_id = ? AND user2_id = ?) 
               OR (user1_id = ? AND user2_id = ?)
            LIMIT 1
        ");
        $stmt->execute([$user_id, $pre_selected_user_id, $pre_selected_user_id, $user_id]);
        $existing_conversation = $stmt->fetch();

        if ($existing_conversation) {
            $pre_selected_conversation_id = $existing_conversation['id'];
        } else {
            // Create new conversation
            $stmt = $pdo->prepare("INSERT INTO conversations (user1_id, user2_id, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$user_id, $pre_selected_user_id]);
            $pre_selected_conversation_id = $pdo->lastInsertId();

            // Add initial message if we have item context
            if (isset($_GET['item_id'])) {
                $item_context = htmlspecialchars("Hi! I'm interested in your item: " . $_GET['item_id']);
                // You could fetch item title here for a better message
            }
        }
    }

    // Get item context for the initial message
    if (isset($_GET['item_id']) && !$item_context) {
        $stmt = $pdo->prepare("SELECT title FROM items WHERE id = ?");
        $stmt->execute([$_GET['item_id']]);
        $item_data = $stmt->fetch();
        if ($item_data) {
            $item_context = "Hi! I saw your item: \"" . htmlspecialchars($item_data['title']) . "\" and would like to chat about it.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Campus Lost & Found</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <style>
        /* Modern Chat Styles */
        .messages-container {
            height: 100%;
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding: 16px;
            background: #f0f2f5;
            height: 100%;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            /* Smooth scrolling on iOS */
        }

        .dark .messages-container {
            background: #1a1a1a;
        }

        .message-row {
            display: flex;
            margin-bottom: 2px;
        }

        .message-row.sent {
            justify-content: flex-end;
        }

        .message-row.received {
            justify-content: flex-start;
        }

        .message-bubble {
            max-width: 65%;
            padding: 8px 12px;
            border-radius: 18px;
            word-wrap: break-word;
            white-space: pre-wrap;
            overflow-wrap: break-word;
            position: relative;
            line-height: 0.4;
            font-size: 14px;
        }

        /* Sent messages (blue bubbles) */
        .message-bubble.sent {
            background: #0084ff;
            color: white;
            border-bottom-right-radius: 4px;
            margin-left: auto;
        }

        /* Received messages (light gray bubbles) */
        .message-bubble.received {
            background: white;
            color: #333;
            border-bottom-left-radius: 4px;
            margin-right: auto;
        }

        .dark .message-bubble.received {
            background: #2d3748;
            color: #e5e7eb;
        }

        /* Message time styling */
        .message-time {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 4px;
            text-align: right;
        }

        .message-bubble.received .message-time {
            color: rgba(0, 0, 0, 0.5);
        }

        .dark .message-bubble.received .message-time {
            color: rgba(255, 255, 255, 0.5);
        }

        /* Avatar styling */
        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            flex-shrink: 0;
            margin: 0 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }

        /* Date divider */
        .date-divider {
            text-align: center;
            margin: 16px 0;
            position: relative;
        }

        .date-divider span {
            background: rgba(0, 0, 0, 0.1);
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            color: #666;
            border: none;
        }

        .dark .date-divider span {
            background: rgba(255, 255, 255, 0.1);
            color: #999;
        }

        /* Conversation list styles */
        .conversation-item {
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
            cursor: pointer;
        }

        .conversation-item:hover {
            background: #f8fafc;
        }

        .dark .conversation-item:hover {
            background: #374151;
        }

        .conversation-item.active {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important;
            border-left-color: #1e40af;
            color: white;
        }

        .conversation-item.active .font-semibold,
        .conversation-item.active .text-gray-900,
        .conversation-item.active .text-gray-600,
        .conversation-item.active .text-gray-500,
        .conversation-item.active .text-gray-400 {
            color: white !important;
        }

        .dark .conversation-item.active {
            background: linear-gradient(135deg, #1e40af, #1e3a8a) !important;
            border-left-color: #1d4ed8;
        }

        /* Chat input styles */
        .chat-input-container {
            background: white;
            border-top: 1px solid #e5e7eb;
            padding: 16px;
            flex-shrink: 0;
        }

        .dark .chat-input-container {
            background: #1f2937;
            border-top-color: #374151;
        }

        /* Online indicator */
        .online-indicator {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            border: 2px solid white;
        }

        .dark .online-indicator {
            border-color: #1f2937;
        }

        /* Mobile responsive styles */
        .conversations-sidebar {
            width: 100%;
            height: 100%;
        }

        @media (min-width: 768px) {
            .conversations-sidebar {
                width: 35%;
            }
        }

        .chat-area {
            display: none;
        }

        @media (min-width: 768px) {
            .chat-area {
                display: flex;
                width: 65%;
            }
        }

        .chat-area.mobile-active {
            display: flex;
            width: 100%;
            height: 100%;
        }

        .conversations-sidebar.mobile-hidden {
            display: none;
        }

        /* Back button for mobile */
        .back-to-conversations {
            display: none;
        }

        @media (max-width: 767px) {
            .back-to-conversations {
                display: flex;
            }
        }

        /* Fix for mobile scrolling */
        .chat-layout-container {
            height: calc(100vh - 140px);
            overflow: hidden;
        }

        @media (max-width: 767px) {
            .chat-layout-container {
                height: calc(100vh - 120px);
            }
        }

        .conversations-list {
            flex: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            height: calc(100% - 73px);
        }

        .chat-area-inner {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
            height: 100%;
        }

        .messages-wrapper {
            flex: 1;
            min-height: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Improved mobile styles */
        @media (max-width: 767px) {
            body {
                overflow-x: hidden;
            }

            .chat-layout-container {
                border-radius: 0;
                margin: -1rem;
                width: calc(100% + 2rem);
                max-width: none;
            }

            .messages-container {
                padding: 12px 8px;
            }

            .message-bubble {
                max-width: 75%;
            }

            .chat-input-container {
                padding: 12px;
            }

            #message-input {
                padding: 12px;
                font-size: 16px;
                /* Prevents iOS zoom on focus */
            }

            #send-message {
                padding: 12px 16px;
            }
        }

        /* Better scrolling on mobile */
        .conversations-list::-webkit-scrollbar,
        .messages-container::-webkit-scrollbar {
            width: 4px;
        }

        .conversations-list::-webkit-scrollbar-track,
        .messages-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .conversations-list::-webkit-scrollbar-thumb,
        .messages-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 2px;
        }

        .dark .conversations-list::-webkit-scrollbar-thumb,
        .dark .messages-container::-webkit-scrollbar-thumb {
            background: #4b5563;
        }

        /* Notification styles */
        .notification {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Main Content -->
    <main class="w-full">
        <!-- Page Header -->
        <div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <!-- Back button for mobile -->
                        <button id="back-to-conversations" class="back-to-conversations text-gray-600">
                            <i class="fas fa-arrow-left text-xl"></i>
                        </button>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Messages</h1>
                            <p class="text-gray-600 mt-1 hidden md:block">
                                Chat with other users about lost and found items
                            </p>
                        </div>
                    </div>
                    <button id="new-chat-btn"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium flex items-center shadow-md">
                        <i class="fas fa-plus mr-2"></i> <span class="hidden sm:inline">New Chat</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Chat Layout -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden chat-layout-container">
                <div class="flex flex-col md:flex-row h-full">
                    <!-- Conversations Sidebar -->
                    <div id="conversations-sidebar"
                        class="conversations-sidebar flex flex-col border-r border-gray-200 bg-white">
                        <!-- Search Bar -->
                        <div class="p-4 border-b border-gray-200 flex-shrink-0">
                            <div class="relative">
                                <input type="text" placeholder="Search conversations..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Conversations List -->
                        <div class="conversations-list">
                            <?php if (count($conversations) > 0): ?>
                                <?php foreach ($conversations as $conv): ?>
                                    <div class="conversation-item p-4 border-b border-gray-200"
                                        data-conversation-id="<?php echo $conv['id']; ?>"
                                        data-other-user-id="<?php echo $conv['other_user_id']; ?>"
                                        data-other-user-name="<?php echo htmlspecialchars($conv['username']); ?>">
                                        <div class="flex items-center space-x-3">
                                            <div class="relative flex-shrink-0">
                                                <div class="message-avatar w-12 h-12">
                                                    <?php echo strtoupper(substr($conv['username'], 0, 2)); ?>
                                                </div>
                                                <div class="absolute bottom-0 right-0 online-indicator"></div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between mb-1">
                                                    <h3 class="font-semibold text-gray-900 truncate">
                                                        <?php echo htmlspecialchars($conv['username']); ?>
                                                    </h3>
                                                    <span class="text-xs text-gray-500">
                                                        <?php echo $conv['last_message_time'] ? date('M j', strtotime($conv['last_message_time'])) : ''; ?>
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-600 truncate">
                                                    <?php
                                                    $last_message = $conv['last_message'] ?
                                                        (strlen($conv['last_message']) > 50 ?
                                                            substr($conv['last_message'], 0, 50) . '...' :
                                                            $conv['last_message']) :
                                                        'No messages yet';
                                                    echo htmlspecialchars($last_message);
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="p-8 text-center">
                                    <i class="fas fa-comments text-gray-400 text-4xl mb-3"></i>
                                    <p class="text-gray-500">No conversations yet</p>
                                    <p class="text-sm text-gray-400 mt-1">Start a chat to connect
                                        with other users</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Chat Area -->
                    <div id="chat-area" class="chat-area flex-1 flex-col bg-gray-50">
                        <div class="chat-area-inner h-full">
                            <!-- Chat Header -->
                            <div id="chat-header" class="p-4 border-b border-gray-200 bg-white flex-shrink-0 hidden">
                                <div class="flex items-center space-x-3">
                                    <div class="relative">
                                        <div class="message-avatar w-10 h-10" id="chat-header-avatar"></div>
                                        <div class="absolute bottom-0 right-0 online-indicator"></div>
                                    </div>
                                    <div class="flex-1">
                                        <h4 id="chat-header-username" class="font-semibold text-gray-900"></h4>
                                        <p class="text-sm text-green-600">Online</p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button class="p-2 text-gray-500 hover:text-gray-700">
                                            <i class="fas fa-phone"></i>
                                        </button>
                                        <button class="p-2 text-gray-500 hover:text-gray-700">
                                            <i class="fas fa-video"></i>
                                        </button>
                                        <button class="p-2 text-gray-500 hover:text-gray-700">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Messages Wrapper -->
                            <div class="messages-wrapper">
                                <!-- Messages Container -->
                                <div id="messages-container" class="messages-container">
                                    <div class="flex justify-center items-center h-full text-gray-500">
                                        <div class="text-center">
                                            <i class="fas fa-comments text-4xl mb-4"></i>
                                            <p>Select a conversation to start chatting</p>
                                            <p class="text-sm mt-2">Or start a new conversation using the button
                                                above</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Message Input -->
                            <div class="chat-input-container">
                                <div class="flex items-center space-x-2">
                                    <button class="p-2 text-gray-500 hover:text-gray-700">
                                        <i class="fas fa-paperclip"></i>
                                    </button>
                                    <button class="p-2 text-gray-500 hover:text-gray-700">
                                        <i class="fas fa-image"></i>
                                    </button>
                                    <input type="text" id="message-input" placeholder="Type your message..."
                                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        disabled>
                                    <button id="send-message"
                                        class="bg-blue-500 hover:bg-blue-600 text-white p-3 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        disabled>
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- New Chat Modal -->
    <div id="new-chat-modal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="modal-content w-full max-w-md bg-white rounded-xl shadow-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-gray-900">Start New Chat</h3>
                    <button id="close-new-chat-modal" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="new-chat-form">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Select User to Chat With:
                        </label>
                        <select name="receiver_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                            <option value="">Select a user...</option>
                            <?php foreach ($all_users as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="cancel-new-chat"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-md transition-colors">
                            Start Chat
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ===== GLOBAL VARIABLES FOR POLLING =====
            let messages = []; // Store loaded messages
            let lastMessageId = 0; // Track last message ID for polling
            let pollingInterval = null; // Store polling interval

            // ===== POLLING FUNCTIONS =====
            function startPolling(conversationId) {
                stopPolling(); // Stop any existing polling

                // Set lastMessageId to the highest message ID
                if (messages.length > 0) {
                    lastMessageId = Math.max(...messages.map(msg => parseInt(msg.id)));
                } else {
                    lastMessageId = 0;
                }

                console.log(`🔁 Starting polling for conversation ${conversationId}, lastMessageId: ${lastMessageId}`);

                // Start polling every 3 seconds
                pollingInterval = setInterval(async () => {
                    await checkForNewMessages(conversationId);
                }, 3000);
            }

            function stopPolling() {
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                    pollingInterval = null;
                    console.log('🛑 Stopped message polling');
                }
            }

            async function checkForNewMessages(conversationId) {
                // Don't check if we're not in the right conversation
                if (!conversationId || conversationId !== currentConversationId) {
                    console.log('Polling skipped - wrong conversation');
                    return;
                }

                try {
                    console.log(`🔍 Polling for new messages after ID: ${lastMessageId}`);

                    const response = await fetch(`api/check_new_messages.php?conversation_id=${conversationId}&after=${lastMessageId}&user_id=${currentUserId}`);

                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    const result = await response.json();

                    if (result.success && result.new_messages && result.new_messages.length > 0) {
                        console.log(`📨 Found ${result.new_messages.length} new messages via polling`);

                        result.new_messages.forEach(message => {
                            // Skip messages that we sent (they're already displayed via Pusher/optimistic update)
                            if (message.sender_id == currentUserId) {
                                console.log('Skipping own message from polling');
                                return;
                            }

                            // Check if message already exists
                            const messageExists = messages.some(msg => msg.id == message.id);
                            const tempMessageExists = document.querySelector(`[data-message-id="temp-${message.id}"]`);

                            if (!messageExists && !tempMessageExists) {
                                console.log('Adding new message from polling:', message.id);
                                messages.push(message);
                                addMessageToChat(message, true); // true = is received message

                                // Update lastMessageId
                                lastMessageId = Math.max(lastMessageId, parseInt(message.id));
                            }
                        });

                        // Update conversation preview if we got new messages
                        if (result.new_messages.length > 0) {
                            const latestMessage = result.new_messages[result.new_messages.length - 1];
                            updateConversationPreview({
                                conversation_id: conversationId,
                                message: latestMessage.message,
                                created_at: latestMessage.created_at,
                                sender_name: latestMessage.sender_username || 'User'
                            });
                        }
                    }
                } catch (error) {
                    console.error('Polling error:', error);
                    // Don't show alert, just log error
                }
            }

            // ===== PUSHER REAL-TIME CHAT =====
            let pusher = null;
            let conversationChannel = null;
            const currentUserId = <?php echo $_SESSION['user_id']; ?>;
            let currentConversationId = null;
            let currentReceiverId = null;
            let currentReceiverName = null;

            // Initialize Pusher
            function initializePusher() {
                console.log('🚀 Initializing Pusher for user:', currentUserId);

                try {
                    pusher = new Pusher('585e3129f2fd92e29c0b', {
                        cluster: 'ap1',
                        forceTLS: true,
                        authEndpoint: 'auth/pusher_auth.php',
                        auth: {
                            params: {
                                user_id: currentUserId,
                                username: '<?php echo $_SESSION['username'] ?? ''; ?>',
                                timestamp: Date.now()
                            },
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        },
                        enableLogging: true,
                        logToConsole: true
                    });

                    // Debug logging
                    pusher.connection.bind('connecting', () => {
                        console.log('🔄 Connecting to Pusher...');
                    });

                    pusher.connection.bind('connected', () => {
                        console.log('✅ Pusher CONNECTED');
                        console.log('📡 Socket ID:', pusher.connection.socket_id);
                    });

                    pusher.connection.bind('disconnected', () => {
                        console.log('❌ Pusher DISCONNECTED');
                    });

                    pusher.connection.bind('error', (err) => {
                        console.error('⚠️ Pusher ERROR:', err);
                    });

                } catch (error) {
                    console.error('❌ Pusher initialization failed:', error);
                }
            }

            // Subscribe to conversation channel
            function subscribeToConversationChannel(conversationId) {
                if (!conversationId) {
                    console.error('No conversation ID provided');
                    return;
                }

                // Unsubscribe from previous channel
                if (conversationChannel) {
                    pusher.unsubscribe(conversationChannel.name);
                    console.log('Unsubscribed from previous channel:', conversationChannel.name);
                }

                const channelName = 'private-chat-' + conversationId;
                console.log('📡 Attempting to subscribe to conversation channel:', channelName);

                try {
                    conversationChannel = pusher.subscribe(channelName);

                    conversationChannel.bind('pusher:subscription_succeeded', () => {
                        console.log('✅ Successfully subscribed to conversation channel:', channelName);
                    });

                    conversationChannel.bind('pusher:subscription_error', (status) => {
                        console.error('❌ Subscription FAILED for channel:', channelName, 'Status:', status);
                    });

                    conversationChannel.bind('new-message', (data) => {
                        console.log('📨 NEW MESSAGE received via Pusher:', data);
                        handleIncomingMessage(data);
                    });

                    // Handle typing indicators
                    conversationChannel.bind('client-typing', (data) => {
                        if (data.user_id != currentUserId) {
                            if (data.typing) {
                                showTypingIndicator(data.username);
                            } else {
                                hideTypingIndicator();
                            }
                        }
                    });

                } catch (error) {
                    console.error('Error subscribing to conversation channel:', error);
                }
            }

            // Handle incoming messages (from Pusher or polling)
            function handleIncomingMessage(data) {
                console.log('🔍 Processing incoming message:', data);

                const isSentByCurrentUser = data.sender_id == currentUserId;

                // Check if message is for the current conversation
                if (currentConversationId && data.conversation_id == currentConversationId) {
                    console.log('💬 Message is for current conversation');

                    // Check if message already exists
                    const existingMessage = document.querySelector(`[data-message-id="${data.message_id}"]`);
                    if (existingMessage) {
                        console.log('⚠️ Message already displayed, skipping');
                        return;
                    }

                    // Check for temporary message with same content
                    const tempMessages = document.querySelectorAll('[data-message-id^="temp-"]');
                    let foundTemp = false;

                    tempMessages.forEach(tempMsg => {
                        const content = tempMsg.querySelector('.message-content');
                        if (content && content.textContent.trim() === data.message.trim()) {
                            // Update temp message with real ID
                            tempMsg.setAttribute('data-message-id', data.message_id);
                            foundTemp = true;
                            console.log('✅ Updated temp message with real ID');
                        }
                    });

                    if (!foundTemp) {
                        // Add message to messages array
                        messages.push(data);
                        // Add to chat UI
                        addMessageToChat(data, !isSentByCurrentUser);
                        scrollToBottom();
                    }

                    // Update conversation preview
                    updateConversationPreview(data);

                    // Update lastMessageId for polling
                    lastMessageId = Math.max(lastMessageId, parseInt(data.message_id || data.id));

                    // Play notification for received messages only
                    if (!isSentByCurrentUser) {
                        playNotificationSound();
                        showMessageNotification(data);
                    }

                } else {
                    // Message for different conversation
                    console.log('🔔 Message for different conversation');
                    updateConversationPreview(data);

                    if (!isSentByCurrentUser) {
                        showMessageNotification(data);
                        playNotificationSound();
                    }
                }
            }

            // ===== MESSAGE DISPLAY FUNCTIONS =====
            function addMessageToChat(messageData, isReceived) {
                const messagesContainer = document.getElementById('messages-container');
                if (!messagesContainer) return;

                // Format time
                const time = new Date(messageData.created_at).toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });

                // Get avatar content
                const avatarContent = !isReceived ?
                    '<?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>' :
                    (currentReceiverName ? currentReceiverName.substring(0, 1).toUpperCase() : 'U');

                // Create message element
                const messageRow = document.createElement('div');
                messageRow.className = `message-row ${!isReceived ? 'sent' : 'received'}`;
                messageRow.setAttribute('data-message-id', messageData.message_id || messageData.id);

                messageRow.innerHTML = `
            ${isReceived ? `
                <div class="message-avatar">
                    ${avatarContent}
                </div>
            ` : ''}
            
            <div class="message-bubble ${!isReceived ? 'sent' : 'received'}">
                <div class="message-content">${escapeHtml(messageData.message)}</div>
                <div class="message-time">${time}</div>
            </div>
            
            ${!isReceived ? `
                <div class="message-avatar">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
            ` : ''}
        `;

                messagesContainer.appendChild(messageRow);
            }

            // ===== CONVERSATION MANAGEMENT =====
            function openChat(conversationId, receiverId, userName) {
                currentConversationId = conversationId;
                currentReceiverId = receiverId;
                currentReceiverName = userName;

                console.log('Setting up chat for:', userName);

                // 1. Subscribe to Pusher channel
                subscribeToConversationChannel(conversationId);

                // 2. Start polling as backup
                startPolling(conversationId);

                // 3. Update UI
                document.getElementById('chat-header-username').textContent = userName;
                document.getElementById('chat-header-avatar').textContent = userName.substring(0, 2).toUpperCase();
                document.getElementById('chat-header').classList.remove('hidden');

                // 4. Enable message input
                document.getElementById('message-input').disabled = false;
                document.getElementById('send-message').disabled = false;

                setTimeout(() => {
                    document.getElementById('message-input').focus();
                }, 300);

                // 5. Load existing messages
                loadMessages(conversationId);
            }

            async function loadMessages(conversationId) {
                try {
                    const response = await fetch(`api/get_message.php?conversation_id=${conversationId}`);

                    if (!response.ok) {
                        throw new Error('Failed to fetch messages');
                    }

                    const fetchedMessages = await response.json();

                    // Store messages globally
                    messages = fetchedMessages;

                    // Update lastMessageId for polling
                    if (messages.length > 0) {
                        lastMessageId = Math.max(...messages.map(msg => parseInt(msg.id)));
                        console.log(`Loaded ${messages.length} messages, lastMessageId: ${lastMessageId}`);
                    }

                    // Display messages
                    displayMessagesInContainer(messages);
                    scrollToBottom();

                } catch (error) {
                    console.error('Error loading messages:', error);
                    const messagesContainer = document.getElementById('messages-container');
                    if (messagesContainer) {
                        messagesContainer.innerHTML = `
                    <div class="flex justify-center items-center h-full">
                        <div class="text-center text-red-500">
                            <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                            <p>Failed to load messages</p>
                        </div>
                    </div>
                `;
                    }
                }
            }

            function displayMessagesInContainer(messagesToDisplay) {
                const container = document.getElementById('messages-container');
                if (!container) return;

                container.innerHTML = '';

                if (!messagesToDisplay || messagesToDisplay.length === 0) {
                    container.innerHTML = `
                <div class="flex justify-center items-center h-full">
                    <div class="text-center text-gray-500">
                        <i class="fas fa-comments text-4xl mb-4"></i>
                        <p>No messages yet. Start the conversation!</p>
                    </div>
                </div>
            `;
                    return;
                }

                let currentDate = '';
                const currentUserId = <?php echo $_SESSION['user_id']; ?>;

                messagesToDisplay.forEach(message => {
                    const messageDate = new Date(message.created_at).toDateString();

                    if (messageDate !== currentDate) {
                        currentDate = messageDate;
                        const dateDivider = document.createElement('div');
                        dateDivider.className = 'date-divider';
                        dateDivider.innerHTML = `<span>${formatMessageDate(message.created_at)}</span>`;
                        container.appendChild(dateDivider);
                    }

                    const isSent = message.sender_id == currentUserId;

                    const messageRow = document.createElement('div');
                    messageRow.className = `message-row ${isSent ? 'sent' : 'received'}`;
                    messageRow.setAttribute('data-message-id', message.id);

                    const time = new Date(message.created_at).toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    });

                    messageRow.innerHTML = `
                ${!isSent ? `
                    <div class="message-avatar">
                        ${currentReceiverName ? currentReceiverName.substring(0, 1).toUpperCase() : 'U'}
                    </div>
                ` : ''}
                
                <div class="message-bubble ${isSent ? 'sent' : 'received'}">
                    <div class="message-content">${escapeHtml(message.message)}</div>
                    <div class="message-time">${time}</div>
                </div>
                
                ${isSent ? `
                    <div class="message-avatar">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                ` : ''}
            `;

                    container.appendChild(messageRow);
                });

                scrollToBottom();
            }

            // ===== HELPER FUNCTIONS =====
            function formatMessageDate(dateString) {
                const date = new Date(dateString);
                const today = new Date();
                const yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);

                if (date.toDateString() === today.toDateString()) {
                    return 'Today';
                } else if (date.toDateString() === yesterday.toDateString()) {
                    return 'Yesterday';
                } else {
                    return date.toLocaleDateString('en-US', {
                        weekday: 'long',
                        month: 'short',
                        day: 'numeric'
                    });
                }
            }

            function scrollToBottom() {
                const messagesContainer = document.getElementById('messages-container');
                if (messagesContainer) {
                    setTimeout(() => {
                        messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    }, 50);
                }
            }

            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            function updateConversationPreview(data) {
                const conversationItem = document.querySelector(`.conversation-item[data-conversation-id="${data.conversation_id}"]`);
                if (conversationItem) {
                    // Update last message preview
                    const previewElement = conversationItem.querySelector('.text-gray-600');
                    if (previewElement) {
                        const truncatedMessage = data.message.length > 50
                            ? data.message.substring(0, 50) + '...'
                            : data.message;
                        previewElement.textContent = truncatedMessage;
                    }

                    // Update timestamp
                    const timeElement = conversationItem.querySelector('.text-xs');
                    if (timeElement) {
                        try {
                            const date = new Date(data.created_at);
                            const now = new Date();

                            let timeText;
                            if (date.toDateString() === now.toDateString()) {
                                timeText = date.toLocaleTimeString('en-US', {
                                    hour: '2-digit',
                                    minute: '2-digit'
                                });
                            } else if (date.getDate() === now.getDate() - 1) {
                                timeText = 'Yesterday';
                            } else {
                                timeText = date.toLocaleDateString('en-US', {
                                    month: 'short',
                                    day: 'numeric'
                                });
                            }

                            timeElement.textContent = timeText;
                        } catch (e) {
                            console.error('Error formatting date:', e);
                            timeElement.textContent = 'Now';
                        }
                    }

                    // Move to top if not current conversation
                    if (data.conversation_id != currentConversationId) {
                        const conversationsList = document.querySelector('.conversations-list');
                        if (conversationsList && conversationItem.parentNode === conversationsList) {
                            conversationsList.insertBefore(conversationItem, conversationsList.firstChild);
                        }
                    }
                }
            }

            // ===== NOTIFICATION FUNCTIONS =====
            function playNotificationSound() {
                try {
                    const audio = new Audio('https://assets.mixkit.co/sfx/preview/mixkit-correct-answer-tone-2870.mp3');
                    audio.volume = 0.3;
                    audio.play().catch(e => console.log('Audio play failed:', e));
                } catch (e) {
                    console.log('Notification sound error:', e);
                }
            }

            function showMessageNotification(data) {
                if (currentConversationId && data.conversation_id == currentConversationId) {
                    return;
                }

                // Browser notification
                if ("Notification" in window && Notification.permission === "granted") {
                    new Notification("New Message from " + data.sender_name, {
                        body: data.message,
                        icon: '/favicon.ico'
                    });
                }

                // In-app notification
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-blue-500 text-white p-4 rounded-lg shadow-lg z-50 max-w-md notification';
                notification.innerHTML = `
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <i class="fas fa-comment text-white text-lg"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold truncate">New message from ${data.sender_name}</p>
                    <p class="text-sm opacity-90 truncate">${escapeHtml(data.message)}</p>
                    <button class="mt-2 text-xs bg-white text-blue-500 px-2 py-1 rounded hover:bg-gray-100 transition-colors" onclick="openConversation(${data.conversation_id})">
                        Open Chat
                    </button>
                </div>
                <button class="flex-shrink-0 text-white hover:text-gray-200" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

                document.body.appendChild(notification);

                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 5000);
            }

            window.openConversation = function (conversationId) {
                const conversationItem = document.querySelector(`.conversation-item[data-conversation-id="${conversationId}"]`);
                if (conversationItem) {
                    conversationItem.click();
                }
            };

            // ===== TYPING INDICATORS =====
            function showTypingIndicator(username) {
                const messagesContainer = document.getElementById('messages-container');
                const existingTyping = document.querySelector('.typing-indicator');

                if (!existingTyping) {
                    const typingDiv = document.createElement('div');
                    typingDiv.className = 'typing-indicator';
                    typingDiv.innerHTML = `
                <div class="flex items-center space-x-2">
                    <div class="message-avatar w-8 h-8">
                        ${username ? username.substring(0, 1).toUpperCase() : 'U'}
                    </div>
                    <div class="message-bubble received">
                        <div class="typing-dots flex space-x-1">
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-pulse"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-pulse animation-delay-200"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-pulse animation-delay-400"></div>
                        </div>
                    </div>
                </div>
            `;
                    messagesContainer.appendChild(typingDiv);
                    scrollToBottom();
                }
            }

            function hideTypingIndicator() {
                const typingIndicator = document.querySelector('.typing-indicator');
                if (typingIndicator) {
                    typingIndicator.remove();
                }
            }

            // Add CSS for typing animation
            const style = document.createElement('style');
            style.textContent = `
        @keyframes typing-pulse {
            0%, 60%, 100% { opacity: 0.4; }
            30% { opacity: 1; }
        }
        .animation-delay-200 { animation-delay: 0.2s; }
        .animation-delay-400 { animation-delay: 0.4s; }
        .typing-dots div {
            animation: typing-pulse 1.4s infinite;
        }
    `;
            document.head.appendChild(style);

            // ===== INITIALIZATION =====

            // Initialize Pusher
            initializePusher();

            // Request notification permission
            if ("Notification" in window && Notification.permission === "default") {
                Notification.requestPermission();
            }

            // Auto-open conversation if coming from view-item.php
            function autoOpenConversation() {
                const urlParams = new URLSearchParams(window.location.search);
                const startConversation = urlParams.get('start_conversation');
                const conversationId = urlParams.get('conversation_id');

                if (startConversation || conversationId) {
                    let targetConversationItem = null;

                    if (conversationId) {
                        targetConversationItem = document.querySelector(`.conversation-item[data-conversation-id="${conversationId}"]`);
                    } else if (startConversation) {
                        targetConversationItem = document.querySelector(`.conversation-item[data-other-user-id="${startConversation}"]`);
                    }

                    if (targetConversationItem) {
                        console.log('Auto-opening conversation:', targetConversationItem);
                        targetConversationItem.click();
                        showChatArea();

                        const initialMessage = <?php echo $item_context ? json_encode($item_context) : 'null'; ?>;
                        if (initialMessage && document.getElementById('message-input')) {
                            document.getElementById('message-input').value = initialMessage;
                        }

                        const newUrl = window.location.pathname;
                        window.history.replaceState({}, document.title, newUrl);
                    }
                }
            }

            setTimeout(autoOpenConversation, 500);

            // ===== DOM EVENT HANDLERS =====
            const conversationsSidebar = document.getElementById('conversations-sidebar');
            const chatArea = document.getElementById('chat-area');
            const backToConversationsBtn = document.getElementById('back-to-conversations');
            const newChatModal = document.getElementById('new-chat-modal');
            const closeNewChatModal = document.getElementById('close-new-chat-modal');
            const cancelNewChat = document.getElementById('cancel-new-chat');
            const newChatBtn = document.getElementById('new-chat-btn');
            const messageInput = document.getElementById('message-input');
            const sendMessageBtn = document.getElementById('send-message');

            // Mobile responsive functions
            function showChatArea() {
                if (window.innerWidth < 768) {
                    conversationsSidebar.classList.add('mobile-hidden');
                    chatArea.classList.add('mobile-active');
                    backToConversationsBtn.style.display = 'flex';
                    setTimeout(() => scrollToBottom(), 100);
                    document.body.style.overflow = 'hidden';
                }
            }

            function showConversationsSidebar() {
                if (window.innerWidth < 768) {
                    conversationsSidebar.classList.remove('mobile-hidden');
                    chatArea.classList.remove('mobile-active');
                    backToConversationsBtn.style.display = 'none';
                    document.body.style.overflow = '';
                }
            }

            if (backToConversationsBtn) {
                backToConversationsBtn.addEventListener('click', showConversationsSidebar);
            }

            // Conversation item click handlers
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.addEventListener('click', function () {
                    document.querySelectorAll('.conversation-item').forEach(i => {
                        i.classList.remove('active');
                    });

                    this.classList.add('active');

                    const conversationId = this.getAttribute('data-conversation-id');
                    const receiverId = this.getAttribute('data-other-user-id');
                    const userName = this.getAttribute('data-other-user-name');

                    console.log('Opening conversation:', { conversationId, receiverId, userName });
                    openChat(conversationId, receiverId, userName);
                    showChatArea();
                });
            });

            // ===== MESSAGE SENDING =====
            async function sendMessage() {
                if (!messageInput || !sendMessageBtn) return;

                const message = messageInput.value.trim();

                if (!message || !currentConversationId) {
                    console.log('Cannot send message: missing message or conversation ID');
                    return;
                }

                console.log('Sending message:', message);

                // Optimistic UI update
                const tempId = 'temp-' + Date.now();
                const messageRow = document.createElement('div');
                messageRow.className = 'message-row sent';
                messageRow.setAttribute('data-message-id', tempId);

                const time = new Date().toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });

                messageRow.innerHTML = `
            <div class="message-bubble sent">
                <div class="message-content">${escapeHtml(message)}</div>
                <div class="message-time">${time}</div>
            </div>
            <div class="message-avatar">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
            </div>
        `;

                const messagesContainer = document.getElementById('messages-container');
                if (messagesContainer) {
                    messagesContainer.appendChild(messageRow);
                    scrollToBottom();
                }

                // Disable input while sending
                messageInput.disabled = true;
                sendMessageBtn.disabled = true;
                const originalBtnHtml = sendMessageBtn.innerHTML;
                sendMessageBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                try {
                    const response = await fetch('api/send_message.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            message: message,
                            conversation_id: currentConversationId,
                            receiver_id: currentReceiverId
                        })
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    const responseText = await response.text();
                    console.log('Raw response:', responseText);

                    let result;
                    try {
                        result = JSON.parse(responseText);
                    } catch (parseError) {
                        console.error('Failed to parse JSON:', parseError);
                        console.error('Response was:', responseText);
                        throw new Error('Invalid server response');
                    }

                    console.log('Send message response:', result);

                    if (result.success) {
                        messageInput.value = '';

                        // Update temporary message with real ID if available
                        if (result.message_id) {
                            const tempMessage = document.querySelector(`[data-message-id="${tempId}"]`);
                            if (tempMessage) {
                                tempMessage.setAttribute('data-message-id', result.message_id);
                            }
                        }

                        // Update conversation preview
                        updateConversationPreview({
                            conversation_id: currentConversationId,
                            message: message,
                            created_at: new Date().toISOString(),
                            sender_name: '<?php echo $_SESSION['username']; ?>'
                        });

                    } else {
                        throw new Error(result.error || 'Unknown error');
                    }
                } catch (error) {
                    console.error('Error sending message:', error);

                    // Remove optimistic message if failed
                    const tempMessage = document.querySelector(`[data-message-id="${tempId}"]`);
                    if (tempMessage) {
                        tempMessage.remove();
                    }

                    alert('Error sending message: ' + error.message);
                } finally {
                    // Re-enable input
                    messageInput.disabled = false;
                    sendMessageBtn.disabled = false;
                    sendMessageBtn.innerHTML = originalBtnHtml;
                    messageInput.focus();
                }
            }

            if (sendMessageBtn && messageInput) {
                sendMessageBtn.addEventListener('click', sendMessage);
                messageInput.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        sendMessage();
                    }
                });
            }

            // Typing detection
            let isTyping = false;
            let typingTimer = null;

            if (messageInput) {
                messageInput.addEventListener('input', function () {
                    if (!isTyping) {
                        isTyping = true;
                        if (conversationChannel) {
                            conversationChannel.trigger('client-typing', {
                                user_id: currentUserId,
                                username: '<?php echo $_SESSION['username']; ?>',
                                typing: true
                            });
                        }
                    }

                    clearTimeout(typingTimer);
                    typingTimer = setTimeout(function () {
                        isTyping = false;
                        if (conversationChannel) {
                            conversationChannel.trigger('client-typing', {
                                user_id: currentUserId,
                                username: '<?php echo $_SESSION['username']; ?>',
                                typing: false
                            });
                        }
                    }, 1000);
                });

                messageInput.addEventListener('blur', function () {
                    if (isTyping && conversationChannel) {
                        isTyping = false;
                        conversationChannel.trigger('client-typing', {
                            user_id: currentUserId,
                            username: '<?php echo $_SESSION['username']; ?>',
                            typing: false
                        });
                    }
                });
            }

            // Window resize handler
            window.addEventListener('resize', function () {
                if (window.innerWidth >= 768) {
                    conversationsSidebar.classList.remove('mobile-hidden');
                    chatArea.classList.remove('mobile-active');
                    backToConversationsBtn.style.display = 'none';
                    document.body.style.overflow = '';
                } else if (!currentConversationId) {
                    showConversationsSidebar();
                }
            });

            console.log('Messages page ready with polling enabled');
        });
    </script>
</body>

</html>