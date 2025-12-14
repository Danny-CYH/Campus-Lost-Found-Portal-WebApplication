<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

// Get current user's profile image
$user_id = $_SESSION['user_id']; // This was missing
$stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current_user = $stmt->fetch();
$current_user_profile_image = $current_user['profile_image'] ?? null;

// Get conversations for chat
$stmt = $pdo->prepare("
    SELECT c.*, 
           u.username, 
           u.id as other_user_id,
           u.profile_image as other_user_profile_image,
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
$stmt = $pdo->prepare("SELECT id, username, profile_image FROM users WHERE id != ? ORDER BY username");
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
    <html lang="en" class="light">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Campus Lost & Found</title>
    <link rel="stylesheet" href="css/chat.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <style>
        .profile-image-container {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #e5e7eb;
            font-weight: bold;
            color: #4b5563;
        }

        .profile-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #3b82f6;
            color: white;
            font-weight: bold;
            font-size: 14px;
            flex-shrink: 0;
        }

        .message-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .chat-header-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #3b82f6;
            color: white;
            font-weight: bold;
            font-size: 16px;
            flex-shrink: 0;
        }

        .chat-header-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8'
                        }
                    }
                }
            }
        }
    </script>
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
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Messages</h1>
                            <p class="text-gray-600 dark:text-white mt-1 hidden md:block">
                                Chat with other users about lost and found items
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Layout -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden chat-layout-container">
                <div class="flex flex-col md:flex-row h-full">
                    <!-- Conversations Sidebar -->
                    <div id="conversations-sidebar"
                        class="conversations-sidebar flex flex-col border-r border-gray-200 bg-white dark:bg-black">
                        <!-- Search Bar -->
                        <div class="p-4 border-b border-gray-200 flex-shrink-0">
                            <div class="relative">
                                <input type="text" placeholder="Search conversations..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-black dark:text-white">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Conversations List -->
                        <div class="conversations-list">
                            <?php if (count($conversations) > 0): ?>
                                <?php foreach ($conversations as $conv): ?>
                                    <div class="conversation-item p-4 border-b border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors"
                                        data-conversation-id="<?php echo $conv['id']; ?>"
                                        data-other-user-id="<?php echo $conv['other_user_id']; ?>"
                                        data-other-user-name="<?php echo htmlspecialchars($conv['username']); ?>"
                                        data-other-user-profile-image="<?php echo htmlspecialchars($conv['other_user_profile_image'] ?? ''); ?>">
                                        <div class="flex items-center space-x-3">
                                            <div class="relative flex-shrink-0">
                                                <div class="profile-image-container">
                                                    <?php if (!empty($conv['other_user_profile_image'])): ?>
                                                        <img src="uploads/profile_images/<?php echo htmlspecialchars($conv['other_user_profile_image']); ?>"
                                                            alt="<?php echo htmlspecialchars($conv['username']); ?>"
                                                            class="profile-image">
                                                    <?php else: ?>
                                                        <?php echo strtoupper(substr($conv['username'], 0, 2)); ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div
                                                    class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white">
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between mb-1">
                                                    <h3 class="font-semibold text-gray-900 truncate dark:text-white">
                                                        <?php echo htmlspecialchars($conv['username']); ?>
                                                    </h3>
                                                    <span class="text-xs text-gray-500">
                                                        <?php echo $conv['last_message_time'] ? date('M j', strtotime($conv['last_message_time'])) : ''; ?>
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-600 truncate dark:text-gray-400">
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
                                    <p class="text-sm text-gray-400 mt-1">Start a chat to connect with other users</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Chat Area -->
                    <div id="chat-area" class="chat-area flex-1 flex-col bg-gray-50">
                        <div class="chat-area-inner h-full">
                            <!-- Chat Header -->
                            <div id="chat-header"
                                class="p-4 border-b border-gray-200 bg-white flex-shrink-0 hidden dark:bg-black">
                                <div class="flex items-center space-x-3">
                                    <div class="relative">
                                        <div class="chat-header-avatar" id="chat-header-avatar">
                                            <!-- Profile image will be inserted here by JavaScript -->
                                        </div>
                                        <div
                                            class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white">
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h4 id="chat-header-username"
                                            class="font-semibold text-gray-900 dark:text-white"></h4>
                                        <p class="text-sm text-green-600">Online</p>
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
                                            <p class="text-sm mt-2">Or start a new conversation using the button above
                                            </p>
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
                                        autocomplete="off"
                                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg bg-white dark:bg-black dark:text-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
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

    <script src="../js/theme.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ===== GLOBAL VARIABLES =====
            let messages = [];
            let lastMessageId = 0;
            let pollingInterval = null;
            let pusher = null;
            let conversationChannel = null;
            const currentUserId = <?php echo $_SESSION['user_id']; ?>;
            const currentUserProfileImage = <?php echo json_encode($current_user_profile_image); ?>;
            let currentConversationId = null;
            let currentReceiverId = null;
            let currentReceiverName = null;
            let currentReceiverProfileImage = null;

            // ===== PROFILE IMAGE FUNCTIONS =====
            function getProfileImageElement(profileImage, username, size = 'medium') {
                // Check if profileImage is valid
                if (profileImage &&
                    profileImage !== 'null' &&
                    profileImage !== 'undefined' &&
                    profileImage.trim() !== '' &&
                    !profileImage.startsWith('undefined')) {

                    // Make sure the path is correct
                    const imagePath = `uploads/profile_images/${profileImage}`;
                    return `<img src="${imagePath}" alt="${username || 'User'}" class="profile-image">`;
                } else {
                    // Use initials if no profile image
                    const initials = username ? username.substring(0, 2).toUpperCase() : 'U';
                    return initials;
                }
            }

            function getProfileImageUrl(profileImage) {
                if (profileImage && profileImage !== 'null' && profileImage !== '') {
                    return `uploads/profiles/${profileImage}`;
                }
                return null;
            }

            // ===== POLLING FUNCTIONS =====
            function startPolling(conversationId) {
                stopPolling();

                if (messages.length > 0) {
                    lastMessageId = Math.max(...messages.map(msg => parseInt(msg.id)));
                } else {
                    lastMessageId = 0;
                }

                pollingInterval = setInterval(async () => {
                    await checkForNewMessages(conversationId);
                }, 3000);
            }

            function stopPolling() {
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                    pollingInterval = null;
                }
            }

            async function checkForNewMessages(conversationId) {
                if (!conversationId || conversationId !== currentConversationId) {
                    return;
                }

                try {
                    const response = await fetch(`api/check_new_messages.php?conversation_id=${conversationId}&after=${lastMessageId}&user_id=${currentUserId}`);

                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    const result = await response.json();
                    // console.log('Polling response:', result);

                    // Ensure messages is an array
                    if (!Array.isArray(messages)) {
                        messages = [];
                    }

                    if (result.success && result.new_messages && Array.isArray(result.new_messages)) {
                        result.new_messages.forEach(message => {
                            if (message.sender_id == currentUserId) {
                                return;
                            }

                            // Ensure messages array exists
                            const messageExists = messages.some ? messages.some(msg => msg.id == message.id) : false;
                            const tempMessageExists = document.querySelector(`[data-message-id="temp-${message.id}"]`);

                            if (!messageExists && !tempMessageExists) {
                                messages.push(message);
                                addMessageToChat(message, true);
                                lastMessageId = Math.max(lastMessageId, parseInt(message.id));
                            }
                        });

                        if (result.new_messages.length > 0) {
                            const latestMessage = result.new_messages[result.new_messages.length - 1];
                            updateConversationPreview({
                                conversation_id: conversationId,
                                message: latestMessage.message,
                                created_at: latestMessage.created_at,
                                sender_name: latestMessage.sender_username || 'User',
                                sender_profile_image: latestMessage.sender_profile_image || null
                            });
                        }
                    }
                } catch (error) {
                    console.error('Polling error:', error);
                }
            }

            // ===== PUSHER REAL-TIME CHAT =====
            function initializePusher() {
                try {
                    pusher = new Pusher('585e3129f2fd92e29c0b', {
                        cluster: 'ap1',
                        forceTLS: true,
                        authEndpoint: 'auth/pusher_auth.php',
                        auth: {
                            params: {
                                user_id: currentUserId,
                                username: '<?php echo $_SESSION['username'] ?? ''; ?>',
                                profile_image: currentUserProfileImage || '',
                                timestamp: Date.now()
                            },
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        },
                        enableLogging: true,
                        logToConsole: true
                    });

                    pusher.connection.bind('connected', () => {
                        console.log('✅ Pusher CONNECTED');
                    });

                    pusher.connection.bind('error', (err) => {
                        console.error('⚠️ Pusher ERROR:', err);
                    });

                } catch (error) {
                    console.error('❌ Pusher initialization failed:', error);
                }
            }

            function subscribeToConversationChannel(conversationId) {
                if (!conversationId) {
                    console.error('No conversation ID provided');
                    return;
                }

                if (conversationChannel) {
                    pusher.unsubscribe(conversationChannel.name);
                }

                const channelName = 'private-chat-' + conversationId;

                try {
                    conversationChannel = pusher.subscribe(channelName);

                    conversationChannel.bind('pusher:subscription_succeeded', () => {
                        console.log('✅ Successfully subscribed to conversation channel:', channelName);
                    });

                    conversationChannel.bind('new-message', (data) => {
                        // console.log('📨 NEW MESSAGE received via Pusher:', data);
                        handleIncomingMessage(data);
                    });

                    conversationChannel.bind('client-typing', (data) => {
                        if (data.user_id != currentUserId) {
                            if (data.typing) {
                                showTypingIndicator(data.username, data.profile_image);
                            } else {
                                hideTypingIndicator();
                            }
                        }
                    });

                } catch (error) {
                    console.error('Error subscribing to conversation channel:', error);
                }
            }

            // ===== MESSAGE HANDLING =====
            function handleIncomingMessage(data) {
                const isSentByCurrentUser = data.sender_id == currentUserId;

                if (currentConversationId && data.conversation_id == currentConversationId) {
                    const existingMessage = document.querySelector(`[data-message-id="${data.message_id}"]`);
                    if (existingMessage) {
                        return;
                    }

                    const tempMessages = document.querySelectorAll('[data-message-id^="temp-"]');
                    let foundTemp = false;

                    tempMessages.forEach(tempMsg => {
                        const content = tempMsg.querySelector('.message-content');
                        if (content && content.textContent.trim() === data.message.trim()) {
                            tempMsg.setAttribute('data-message-id', data.message_id);
                            foundTemp = true;
                        }
                    });

                    if (!foundTemp) {
                        messages.push(data);
                        addMessageToChat(data, !isSentByCurrentUser);
                        scrollToBottom();
                    }

                    updateConversationPreview(data);
                    lastMessageId = Math.max(lastMessageId, parseInt(data.message_id || data.id));

                    if (!isSentByCurrentUser) {
                        playNotificationSound();
                        showMessageNotification(data);
                    }

                } else {
                    updateConversationPreview(data);

                    if (!isSentByCurrentUser) {
                        showMessageNotification(data);
                        playNotificationSound();
                    }
                }
            }

            function addMessageToChat(messageData, isReceived) {
                const messagesContainer = document.getElementById('messages-container');
                if (!messagesContainer) return;

                const time = new Date(messageData.created_at).toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });

                const messageRow = document.createElement('div');
                messageRow.className = `message-row ${!isReceived ? 'sent' : 'received'}`;
                messageRow.setAttribute('data-message-id', messageData.message_id || messageData.id);

                // Determine sender's profile image and name
                let senderProfileImage, senderName;

                if (isReceived) {
                    // Message from other user
                    senderProfileImage = messageData.sender_profile_image || currentReceiverProfileImage;
                    senderName = messageData.sender_username || currentReceiverName || 'User';
                } else {
                    // Message from current user
                    senderProfileImage = currentUserProfileImage;
                    senderName = '<?php echo $_SESSION['username']; ?>';
                }

                // Create profile image HTML
                let profileImageHtml = '';
                if (senderProfileImage && senderProfileImage !== 'null' && senderProfileImage.trim() !== '') {
                    profileImageHtml = `<img src="uploads/profile_images/${senderProfileImage}" alt="${senderName}" class="profile-image">`;
                } else {
                    // Use initials if no profile image
                    const initials = senderName ? senderName.substring(0, 2).toUpperCase() : (isReceived ? 'TU' : 'ME');
                    profileImageHtml = initials;
                }

                messageRow.innerHTML = `
        ${isReceived ? `
            <div class="message-avatar">
                ${profileImageHtml}
            </div>
        ` : ''}
        
        <div class="message-bubble ${!isReceived ? 'sent' : 'received'}">
            <div class="message-content">${escapeHtml(messageData.message)}</div>
            <div class="message-time">${time}</div>
        </div>
        
        ${!isReceived ? `
            <div class="message-avatar">
                ${profileImageHtml}
            </div>
        ` : ''}
    `;

                messagesContainer.appendChild(messageRow);
            }

            // ===== CONVERSATION MANAGEMENT =====
            function openChat(conversationId, receiverId, userName, userProfileImage = null) {
                currentConversationId = conversationId;
                currentReceiverId = receiverId;
                currentReceiverName = userName;
                currentReceiverProfileImage = userProfileImage;

                subscribeToConversationChannel(conversationId);
                startPolling(conversationId);

                // Update chat header with profile image
                const chatHeaderAvatar = document.getElementById('chat-header-avatar');
                chatHeaderAvatar.innerHTML = getProfileImageElement(userProfileImage, userName);

                document.getElementById('chat-header-username').textContent = userName;
                document.getElementById('chat-header').classList.remove('hidden');

                document.getElementById('message-input').disabled = false;
                document.getElementById('send-message').disabled = false;

                setTimeout(() => {
                    document.getElementById('message-input').focus();
                }, 300);

                loadMessages(conversationId);
            }

            async function loadMessages(conversationId) {
                try {
                    const response = await fetch(`api/get_message.php?conversation_id=${conversationId}&include_profile_images=true`);

                    if (!response.ok) {
                        throw new Error(`Failed to fetch messages. Status: ${response.status}`);
                    }

                    const result = await response.json();
                    // console.log('API response:', result);

                    // Check if result is an array
                    if (!Array.isArray(result)) {
                        console.error('Invalid response format. Expected array but got:', result);

                        // Initialize as empty array if invalid
                        messages = [];

                        const messagesContainer = document.getElementById('messages-container');
                        if (messagesContainer) {
                            messagesContainer.innerHTML = `
                    <div class="flex justify-center items-center h-full">
                        <div class="text-center text-yellow-600">
                            <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                            <p>No messages found</p>
                            <p class="text-sm">Start the conversation!</p>
                        </div>
                    </div>
                `;
                        }
                        return;
                    }

                    messages = result;

                    if (messages.length > 0) {
                        lastMessageId = Math.max(...messages.map(msg => parseInt(msg.id)));
                    }

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
                        <p class="text-sm">${error.message}</p>
                        <button onclick="loadMessages(${currentConversationId})" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                            Retry
                        </button>
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

                    const senderProfileImage = isSent ?
                        currentUserProfileImage :
                        (message.sender_profile_image || currentReceiverProfileImage);

                    const senderName = isSent ?
                        '<?php echo $_SESSION['username']; ?>' :
                        (message.sender_username || currentReceiverName);

                    messageRow.innerHTML = `
                        ${!isSent ? `
                            <div class="message-avatar">
                                ${getProfileImageElement(senderProfileImage, senderName)}
                            </div>
                        ` : ''}
                        
                        <div class="message-bubble ${isSent ? 'sent' : 'received'}">
                            <div class="message-content">${escapeHtml(message.message)}</div>
                            <div class="message-time">${time}</div>
                        </div>
                        
                        ${isSent ? `
                            <div class="message-avatar">
                                ${getProfileImageElement(currentUserProfileImage, '<?php echo $_SESSION['username']; ?>')}
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
                    // Update the last message preview
                    const previewElement = conversationItem.querySelector('.text-gray-600');
                    if (previewElement) {
                        const truncatedMessage = data.message.length > 50
                            ? data.message.substring(0, 50) + '...'
                            : data.message;
                        previewElement.textContent = truncatedMessage;
                    }

                    // Update the timestamp
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

                    // Move conversation to top if it's not the current one
                    if (data.conversation_id != currentConversationId) {
                        const conversationsList = document.querySelector('.conversations-list');
                        if (conversationsList && conversationItem.parentNode === conversationsList) {
                            conversationsList.insertBefore(conversationItem, conversationsList.firstChild);
                        }
                    }

                    // REMOVED OR MODIFIED: Do NOT update the profile image when messages come in
                    // Only update the profile image if we're opening the chat for the first time
                    // or if we have a specific reason to update it
                    // (this prevents the image from changing when you send messages)

                    // Note: The profile image should remain static as it was when the conversation
                    // was loaded. If you need to update the profile image (like when a user changes
                    // their profile picture), you should handle that separately.
                }
            }

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

                if ("Notification" in window && Notification.permission === "granted") {
                    new Notification("New Message from " + data.sender_name, {
                        body: data.message,
                        icon: data.sender_profile_image ? `uploads/profiles/${data.sender_profile_image}` : '/favicon.ico'
                    });
                }

                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-blue-500 text-white p-4 rounded-lg shadow-lg z-50 max-w-md notification';

                const senderAvatar = getProfileImageElement(data.sender_profile_image, data.sender_name);

                notification.innerHTML = `
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            ${senderAvatar.includes('<img') ?
                        `<div class="w-10 h-10 rounded-full overflow-hidden">${senderAvatar}</div>` :
                        `<div class="w-10 h-10 rounded-full bg-white text-blue-500 flex items-center justify-center font-bold">${senderAvatar}</div>`
                    }
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
            function showTypingIndicator(username, profileImage) {
                const messagesContainer = document.getElementById('messages-container');
                const existingTyping = document.querySelector('.typing-indicator');

                if (!existingTyping) {
                    const typingDiv = document.createElement('div');
                    typingDiv.className = 'typing-indicator';
                    typingDiv.innerHTML = `
                        <div class="flex items-center space-x-2">
                            <div class="message-avatar">
                                ${getProfileImageElement(profileImage, username)}
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
            initializePusher();

            if ("Notification" in window && Notification.permission === "default") {
                Notification.requestPermission();
            }

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
                    const userProfileImage = this.getAttribute('data-other-user-profile-image');

                    openChat(conversationId, receiverId, userName, userProfileImage);
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

                // Get current user's username from PHP session
                const currentUsername = '<?php echo $_SESSION['username']; ?>';

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

                // Create profile image HTML for optimistic update
                let profileImageHtml = '';
                if (currentUserProfileImage && currentUserProfileImage !== 'null' && currentUserProfileImage.trim() !== '') {
                    profileImageHtml = `<img src="uploads/profile_images/${currentUserProfileImage}" alt="${currentUsername}" class="profile-image">`;
                } else {
                    // Use initials if no profile image
                    const initials = currentUsername ? currentUsername.substring(0, 2).toUpperCase() : 'ME';
                    profileImageHtml = initials;
                }

                messageRow.innerHTML = `
        <div class="message-bubble sent">
            <div class="message-content">${escapeHtml(message)}</div>
            <div class="message-time">${time}</div>
        </div>
        <div class="message-avatar">
            ${profileImageHtml}
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
                    // console.log('Raw response:', responseText);

                    let result;
                    try {
                        result = JSON.parse(responseText);
                    } catch (parseError) {
                        console.error('Failed to parse JSON:', parseError);
                        console.error('Response was:', responseText);
                        throw new Error('Invalid server response');
                    }

                    // console.log('Send message response:', result);

                    if (result.success) {
                        messageInput.value = '';

                        if (result.message_id) {
                            const tempMessage = document.querySelector(`[data-message-id="${tempId}"]`);
                            if (tempMessage) {
                                tempMessage.setAttribute('data-message-id', result.message_id);
                            }
                        }

                        updateConversationPreview({
                            conversation_id: currentConversationId,
                            message: message,
                            created_at: new Date().toISOString(),
                            sender_name: '<?php echo $_SESSION['username']; ?>',
                            sender_profile_image: currentUserProfileImage || null
                        });

                    } else {
                        throw new Error(result.error || 'Unknown error');
                    }
                } catch (error) {
                    console.error('Error sending message:', error);

                    const tempMessage = document.querySelector(`[data-message-id="${tempId}"]`);
                    if (tempMessage) {
                        tempMessage.remove();
                    }

                    alert('Error sending message: ' + error.message);
                } finally {
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
                                profile_image: currentUserProfileImage || '',
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
                                profile_image: currentUserProfileImage || '',
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
                            profile_image: currentUserProfileImage || '',
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
        });
    </script>
</body>

</html>