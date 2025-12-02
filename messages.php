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
            margin-top: 2px;
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
</body>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Auto-open conversation if coming from view-item.php
        function autoOpenConversation() {
            const urlParams = new URLSearchParams(window.location.search);
            const startConversation = urlParams.get('start_conversation');
            const conversationId = urlParams.get('conversation_id');

            if (startConversation || conversationId) {
                let targetConversationItem = null;

                if (conversationId) {
                    // Find conversation by ID
                    targetConversationItem = document.querySelector(`.conversation-item[data-conversation-id="${conversationId}"]`);
                } else if (startConversation) {
                    // Find conversation by user ID
                    targetConversationItem = document.querySelector(`.conversation-item[data-other-user-id="${startConversation}"]`);
                }

                if (targetConversationItem) {
                    console.log('Auto-opening conversation:', targetConversationItem);

                    // Click the conversation item
                    targetConversationItem.click();

                    // Show chat area on mobile
                    showChatArea();

                    // Add initial message if provided
                    const initialMessage = <?php echo $item_context ? json_encode($item_context) : 'null'; ?>;
                    if (initialMessage && document.getElementById('message-input')) {
                        document.getElementById('message-input').value = initialMessage;

                        // Auto-send after 1 second (optional)
                        setTimeout(() => {
                            if (confirm('Would you like to send a greeting message about the item?')) {
                                sendMessage();
                            }
                        }, 1000);
                    }

                    // Remove the query parameters from URL without reloading
                    const newUrl = window.location.pathname;
                    window.history.replaceState({}, document.title, newUrl);
                } else {
                    console.log('Could not find conversation item');

                    // If we have user ID but no conversation, try to create one
                    if (startConversation && !conversationId) {
                        setTimeout(() => {
                            // Find user in new chat modal and create conversation
                            const newChatBtn = document.getElementById('new-chat-btn');
                            if (newChatBtn) {
                                newChatBtn.click();

                                // Set the user in the dropdown
                                setTimeout(() => {
                                    const select = document.querySelector('select[name="receiver_id"]');
                                    if (select) {
                                        select.value = startConversation;

                                        // Auto-submit form after a delay
                                        setTimeout(() => {
                                            const form = document.getElementById('new-chat-form');
                                            if (form) {
                                                form.submit();
                                            }
                                        }, 500);
                                    }
                                }, 300);
                            }
                        }, 500);
                    }
                }
            }
        }

        // Call this function after the DOM is loaded
        setTimeout(autoOpenConversation, 500);
        // 

        // Chat functionality
        let currentConversationId = null;
        let currentReceiverId = null;
        let currentReceiverName = null;

        // DOM Elements
        const conversationsSidebar = document.getElementById('conversations-sidebar');
        const chatArea = document.getElementById('chat-area');
        const backToConversationsBtn = document.getElementById('back-to-conversations');
        const newChatModal = document.getElementById('new-chat-modal');
        const closeNewChatModal = document.getElementById('close-new-chat-modal');
        const cancelNewChat = document.getElementById('cancel-new-chat');
        const newChatBtn = document.getElementById('new-chat-btn');
        const messagesContainer = document.getElementById('messages-container');

        // Mobile responsive functions
        function showChatArea() {
            if (window.innerWidth < 768) {
                conversationsSidebar.classList.add('mobile-hidden');
                chatArea.classList.add('mobile-active');
                backToConversationsBtn.style.display = 'flex';

                // Force scroll to bottom when opening chat on mobile
                setTimeout(() => {
                    scrollToBottom();
                }, 100);

                // Prevent body scrolling when chat is open on mobile
                document.body.style.overflow = 'hidden';
            }
        }

        function showConversationsSidebar() {
            if (window.innerWidth < 768) {
                conversationsSidebar.classList.remove('mobile-hidden');
                chatArea.classList.remove('mobile-active');
                backToConversationsBtn.style.display = 'none';

                // Restore body scrolling
                document.body.style.overflow = '';
            }
        }

        // Back button event listener
        if (backToConversationsBtn) {
            backToConversationsBtn.addEventListener('click', showConversationsSidebar);
        }

        // Open new chat modal
        if (newChatBtn) {
            newChatBtn.addEventListener('click', function () {
                console.log('Opening new chat modal');
                newChatModal.classList.remove('hidden');
            });
        }

        // Close modals
        if (closeNewChatModal) {
            closeNewChatModal.addEventListener('click', function () {
                newChatModal.classList.add('hidden');
            });
        }

        if (cancelNewChat) {
            cancelNewChat.addEventListener('click', function () {
                newChatModal.classList.add('hidden');
            });
        }

        // Close modal when clicking outside
        document.addEventListener('click', function (event) {
            if (event.target === newChatModal) {
                newChatModal.classList.add('hidden');
            }
        });

        // New chat form submission
        const newChatForm = document.getElementById('new-chat-form');
        if (newChatForm) {
            newChatForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                console.log('Submitting new chat form');

                const formData = new FormData(this);
                const receiverId = formData.get('receiver_id');

                try {
                    const response = await fetch('api/create_conversation.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `receiver_id=${receiverId}`
                    });

                    const result = await response.json();
                    console.log('Create conversation response:', result);

                    if (result.success) {
                        // Get receiver info and open chat
                        const receiverSelect = document.querySelector('select[name="receiver_id"]');
                        const selectedOption = receiverSelect.options[receiverSelect.selectedIndex];
                        const receiverName = selectedOption.text;

                        openChat(result.conversation_id, receiverId, receiverName);
                        newChatModal.classList.add('hidden');

                        // Refresh page to show new conversation
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        alert('Error creating conversation: ' + (result.error || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error creating conversation:', error);
                    alert('Error creating conversation. Please try again.');
                }
            });
        }

        // Conversation item click handlers
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.addEventListener('click', function () {
                // Remove active class from all items
                document.querySelectorAll('.conversation-item').forEach(i => {
                    i.classList.remove('active');
                });

                // Add active class to clicked item
                this.classList.add('active');

                const conversationId = this.getAttribute('data-conversation-id');
                const receiverId = this.getAttribute('data-other-user-id');
                const userName = this.getAttribute('data-other-user-name');

                console.log('Opening conversation:', { conversationId, receiverId, userName });
                openChat(conversationId, receiverId, userName);

                // Show chat area on mobile
                showChatArea();
            });
        });

        function openChat(conversationId, receiverId, userName) {
            currentConversationId = conversationId;
            currentReceiverId = receiverId;
            currentReceiverName = userName;

            console.log('Setting up chat for:', userName);

            // Update chat header
            document.getElementById('chat-header-username').textContent = userName;
            document.getElementById('chat-header-avatar').textContent = userName.substring(0, 2).toUpperCase();
            document.getElementById('chat-header').classList.remove('hidden');

            // Enable message input
            document.getElementById('message-input').disabled = false;
            document.getElementById('send-message').disabled = false;

            // Focus on message input
            setTimeout(() => {
                document.getElementById('message-input').focus();
            }, 300);

            // Load messages
            loadMessages(conversationId);
        }

        // Function to get conversation info for a specific user
        async function getOrCreateConversation(receiverId, receiverName) {
            try {
                const response = await fetch('api/get_or_create_conversation.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `receiver_id=${receiverId}`
                });

                const result = await response.json();

                if (result.success) {
                    return result.conversation_id;
                }
                return null;
            } catch (error) {
                console.error('Error getting conversation:', error);
                return null;
            }
        }

        // Handle URL parameters for auto-opening conversations
        function handleUrlParameters() {
            const urlParams = new URLSearchParams(window.location.search);
            const receiverId = urlParams.get('start_conversation');
            const conversationId = urlParams.get('conversation_id');

            if (receiverId || conversationId) {
                // If we have a conversation ID, find and click it
                if (conversationId) {
                    const conversationItem = document.querySelector(`.conversation-item[data-conversation-id="${conversationId}"]`);
                    if (conversationItem) {
                        conversationItem.click();
                        showChatArea();
                    }
                }
                // If we only have a user ID, try to find existing conversation
                else if (receiverId) {
                    const conversationItem = document.querySelector(`.conversation-item[data-other-user-id="${receiverId}"]`);
                    if (conversationItem) {
                        conversationItem.click();
                        showChatArea();
                    } else {
                        // Show new chat modal with user pre-selected
                        setTimeout(() => {
                            const newChatBtn = document.getElementById('new-chat-btn');
                            if (newChatBtn) {
                                newChatBtn.click();

                                setTimeout(() => {
                                    const select = document.querySelector('select[name="receiver_id"]');
                                    if (select) {
                                        select.value = receiverId;
                                    }
                                }, 300);
                            }
                        }, 1000);
                    }
                }

                // Clear URL parameters
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }
        }

        // Call this on page load
        setTimeout(handleUrlParameters, 1000);

        async function loadMessages(conversationId) {
            try {
                console.log('Loading messages for conversation:', conversationId);
                const response = await fetch(`api/get_message.php?conversation_id=${conversationId}`);

                if (!response.ok) {
                    throw new Error('Failed to fetch messages');
                }

                const messages = await response.json();
                console.log('Loaded messages:', messages);

                displayMessagesInContainer(messages, messagesContainer);
                scrollToBottom();
            } catch (error) {
                console.error('Error loading messages:', error);
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

        function displayMessagesInContainer(messages, container) {
            container.innerHTML = '';

            if (!messages || messages.length === 0) {
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

            messages.forEach(message => {
                const messageDate = new Date(message.created_at).toDateString();

                // Add date separator if needed
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
            if (messagesContainer) {
                // Use setTimeout to ensure DOM is updated
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

        // Send message functionality
        const sendMessageBtn = document.getElementById('send-message');
        const messageInput = document.getElementById('message-input');

        if (sendMessageBtn && messageInput) {
            sendMessageBtn.addEventListener('click', sendMessage);
            messageInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
        }

        async function sendMessage() {
            const input = document.getElementById('message-input');
            const message = input.value.trim();

            if (!message || !currentConversationId) {
                console.log('Cannot send message: missing message or conversation ID');
                return;
            }

            console.log('Sending message:', message);

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

                const result = await response.json();
                console.log('Send message response:', result);

                if (result.success) {
                    input.value = '';

                    // Add the new message immediately to the chat
                    const messageRow = document.createElement('div');
                    messageRow.className = 'message-row sent';

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

                    messagesContainer.appendChild(messageRow);
                    scrollToBottom();

                } else {
                    alert('Error sending message: ' + (result.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error sending message:', error);
                alert('Error sending message. Please try again.');
            }
        }

        // User dropdown functionality - using CSS group hover instead
        // This is already handled by Tailwind's group-hover classes in the HTML

        // Handle window resize
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 768) {
                // On desktop, show both sidebar and chat area
                conversationsSidebar.classList.remove('mobile-hidden');
                chatArea.classList.remove('mobile-active');
                backToConversationsBtn.style.display = 'none';
                document.body.style.overflow = '';
            } else if (!currentConversationId) {
                // On mobile with no active conversation, show conversations
                showConversationsSidebar();
            }
        });

        // Handle mobile back button (hardware back button)
        window.addEventListener('popstate', function () {
            if (window.innerWidth < 768 && currentConversationId) {
                showConversationsSidebar();
            }
        });

        console.log('Messages page ready');
    });
</script>
</body>

</html>