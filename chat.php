<?php include 'includes/config.php'; ?>

<!DOCTYPE html>
<html lang="en" class="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UUM Campus Chat - Lost & Found Portal</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">

    <!-- Pusher JS -->
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        uum: {
                            green: '#006837',
                            gold: '#FFD700',
                            blue: '#0056b3'
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300 min-h-screen">
    <!-- Mobile Menu -->
    <div id="mobile-menu"
        class="lg:hidden fixed inset-0 bg-white dark:bg-gray-800 z-50 transform -translate-x-full transition-transform duration-300">
        <div class="flex flex-col h-full p-6">
            <div class="flex justify-between items-center mb-8">
                <a href="index.php" class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-r from-uum-green to-uum-blue rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-search-location text-white text-lg"></i>
                    </div>
                    <div>
                        <span class="text-xl font-bold text-uum-green dark:text-uum-gold">UUM Find</span>
                        <p class="text-xs text-gray-500 dark:text-gray-400 -mt-1">Lost & Found</p>
                    </div>
                </a>
                <button id="close-mobile-menu" class="p-2">
                    <i class="fas fa-times text-gray-600 dark:text-gray-400 text-xl"></i>
                </button>
            </div>

            <nav class="flex-1 space-y-4">
                <a href="index.php"
                    class="flex items-center space-x-3 p-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <i class="fas fa-home w-5"></i>
                    <span>Home</span>
                </a>
                <a href="lost-items.php"
                    class="flex items-center space-x-3 p-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <i class="fas fa-search w-5"></i>
                    <span>Lost Items</span>
                </a>
                <a href="chat.php"
                    class="flex items-center space-x-3 p-3 rounded-xl bg-uum-green/10 text-uum-green dark:text-uum-gold">
                    <i class="fas fa-comments w-5"></i>
                    <span>Messages</span>
                </a>
                <a href="dashboard.php"
                    class="flex items-center space-x-3 p-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Dashboard</span>
                </a>
            </nav>

            <div class="pt-6 border-t border-gray-200 dark:border-gray-700 space-y-3">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="auth/logout.php"
                        class="block w-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-center py-3 rounded-xl font-medium transition-colors">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                <?php else: ?>
                    <a href="auth/login.php"
                        class="block w-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-center py-3 rounded-xl font-medium transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                    <a href="auth/register.php"
                        class="block w-full bg-uum-green hover:bg-uum-blue text-white text-center py-3 rounded-xl font-medium transition-colors">
                        <i class="fas fa-user-plus mr-2"></i>Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md shadow-lg sticky top-0 z-30 hidden md:block">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 bg-gradient-to-r from-uum-green to-uum-blue rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-search-location text-white text-lg"></i>
                        </div>
                        <div>
                            <span class="text-xl font-bold text-uum-green dark:text-uum-gold">
                                UUM Find
                            </span>
                            <p class="text-xs text-gray-500 dark:text-gray-400 -mt-1">Lost & Found Portal</p>
                        </div>
                    </a>
                </div>

                <div class="flex items-center space-x-4">
                    <button id="theme-toggle"
                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-300 transform hover:scale-110">
                        <i class="fas fa-moon text-gray-600 dark:text-uum-gold text-lg" id="theme-icon"></i>
                    </button>

                    <div class="hidden md:flex space-x-6">
                        <a href="index.php"
                            class="text-gray-700 dark:text-gray-300 hover:text-uum-green font-medium transition-colors">Home</a>
                        <a href="lost-items.php"
                            class="text-gray-700 dark:text-gray-300 hover:text-uum-green font-medium transition-colors">List
                            Items</a>
                        <a href="chat.php"
                            class="text-gray-700 dark:text-gray-300 hover:text-uum-green font-medium transition-colors">Messages</a>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="dashboard.php"
                            class="bg-uum-green hover:bg-uum-blue text-white px-6 py-2.5 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>
                    <?php else: ?>
                        <div class="flex space-x-3">
                            <a href="auth/login.php"
                                class="text-uum-green hover:text-uum-blue font-medium px-4 py-2 transition-colors">
                                <i class="fas fa-sign-in-alt mr-2"></i>Login
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden chat-container">
            <div class="flex flex-col lg:flex-row h-[80vh] min-h-[600px]">

                <!-- Conversations Sidebar -->
                <div id="conversations-sidebar"
                    class="lg:w-1/3 xl:w-1/4 border-r border-gray-200 dark:border-gray-700 flex flex-col">
                    <!-- Sidebar Header -->
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Messages</h2>
                            <button class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <i class="fas fa-edit text-uum-green"></i>
                            </button>
                        </div>

                        <!-- Search -->
                        <div class="relative">
                            <input type="text" placeholder="Search conversations..."
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-uum-green focus:border-uum-green">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Conversations List -->
                    <div class="flex-1 overflow-y-auto">
                        <?php
                        // Fetch real conversations from database
                        if (isset($_SESSION['user_id'])) {
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

                            foreach ($conversations as $conv) {
                                $last_message_time = $conv['last_message_time'] ? date('g:i A', strtotime($conv['last_message_time'])) : '';
                                $last_message = $conv['last_message'] ? (strlen($conv['last_message']) > 50 ? substr($conv['last_message'], 0, 50) . '...' : $conv['last_message']) : 'No messages yet';
                                $initials = strtoupper(substr($conv['username'], 0, 2));
                                ?>
                                <div class="conversation-item p-4 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors"
                                    data-conversation-id="<?php echo $conv['id']; ?>"
                                    data-other-user-id="<?php echo $conv['other_user_id']; ?>"
                                    data-other-user-name="<?php echo htmlspecialchars($conv['username']); ?>">
                                    <div class="flex items-center space-x-3">
                                        <div class="relative flex-shrink-0">
                                            <div
                                                class="w-12 h-12 bg-gradient-to-r from-uum-green to-uum-blue rounded-full flex items-center justify-center text-white font-semibold">
                                                <?php echo $initials; ?>
                                            </div>
                                            <div
                                                class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white dark:border-gray-800">
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-1">
                                                <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                                                    <?php echo htmlspecialchars($conv['username']); ?>
                                                </h3>
                                                <span class="text-xs text-gray-500"><?php echo $last_message_time; ?></span>
                                            </div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                                <?php echo htmlspecialchars($last_message); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>

                <!-- Chat Area -->
                <div id="chat-area" class="lg:flex-1 flex flex-col hidden lg:flex">
                    <!-- Chat Header -->
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="relative">
                                    <div id="chat-avatar"
                                        class="w-12 h-12 bg-gradient-to-r from-uum-green to-uum-blue rounded-full flex items-center justify-center text-white font-semibold">
                                    </div>
                                    <div
                                        class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white dark:border-gray-800">
                                    </div>
                                </div>
                                <div>
                                    <h3 id="chat-user-name" class="font-semibold text-gray-900 dark:text-white text-lg">
                                    </h3>
                                    <p class="text-sm text-green-600 dark:text-green-400 flex items-center">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                        Online
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button id="back-to-conversations"
                                    class="lg:hidden p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                    <i class="fas fa-arrow-left text-uum-green"></i>
                                </button>
                                <button
                                    class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                    <i class="fas fa-phone text-uum-green"></i>
                                </button>
                                <button class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-colors">
                                    <i class="fas fa-video text-uum-green"></i>
                                </button>
                                <button
                                    class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                    <i class="fas fa-info-circle text-uum-green"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Messages Container -->
                    <div id="messages-container"
                        class="messages-container flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-gray-900">
                        <!-- Messages will be loaded here dynamically -->
                        <div class="flex justify-center items-center h-full">
                            <div class="text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-comments text-4xl mb-4 text-uum-green"></i>
                                <p class="text-lg">Select a conversation to start chatting</p>
                            </div>
                        </div>
                    </div>

                    <!-- Message Input -->
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center space-x-3">
                            <button class="p-3 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <i class="fas fa-plus text-uum-green"></i>
                            </button>
                            <button class="p-3 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <i class="fas fa-image text-uum-green"></i>
                            </button>
                            <div class="flex-1 relative">
                                <input type="text" id="message-input" placeholder="Type your message..."
                                    class="w-full pl-4 pr-12 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-uum-green focus:border-uum-green"
                                    disabled>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <button
                                        class="p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded transition-colors">
                                        <i class="far fa-smile text-uum-green"></i>
                                    </button>
                                </div>
                            </div>
                            <button id="send-message"
                                class="bg-uum-green hover:bg-uum-blue text-white p-3 rounded-lg transition-colors"
                                disabled>
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Empty State -->
                <div id="mobile-empty-state" class="lg:hidden flex-1 flex flex-col items-center justify-center p-8">
                    <div class="text-center text-gray-500 dark:text-gray-400">
                        <i class="fas fa-comments text-6xl mb-4 text-uum-green"></i>
                        <h3 class="text-xl font-semibold mb-2">No Conversation Selected</h3>
                        <p>Choose a conversation from the list to start chatting</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/theme.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Mobile Menu functionality
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            const closeMobileMenu = document.getElementById('close-mobile-menu');

            if (mobileMenuButton && mobileMenu && closeMobileMenu) {
                mobileMenuButton.addEventListener('click', function () {
                    mobileMenu.classList.remove('-translate-x-full');
                });

                closeMobileMenu.addEventListener('click', function () {
                    mobileMenu.classList.add('-translate-x-full');
                });
            }

            // Chat variables
            let currentConversationId = null;
            let currentReceiverId = null;
            let currentReceiverName = null;
            let pusher = null;
            let channel = null;
            let messages = [];
            let pollingInterval = null;
            let lastMessageId = 0;
            let isTyping = false;
            let typingTimer = null;

            // Polling functions
            function startPolling(conversationId) {
                stopPolling();

                if (messages.length > 0) {
                    lastMessageId = Math.max(...messages.map(msg => parseInt(msg.id)));
                }

                pollingInterval = setInterval(async () => {
                    await checkForNewMessages(conversationId);
                }, 2000);

                console.log('🔁 Started polling for new messages every 2 seconds');
            }

            function stopPolling() {
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                    pollingInterval = null;
                    console.log('🛑 Stopped message polling');
                }
            }

            async function checkForNewMessages(conversationId) {
                if (!conversationId || conversationId !== currentConversationId) return;

                try {
                    const response = await fetch(`api/check_new_messages.php?conversation_id=${conversationId}&after=${lastMessageId}`);

                    if (!response.ok) throw new Error('Failed to check messages');

                    const result = await response.json();

                    if (result.success && result.new_messages && result.new_messages.length > 0) {
                        console.log(`📨 Found ${result.new_messages.length} new messages`);

                        result.new_messages.forEach(message => {
                            // Skip messages that we sent (they're already displayed optimistically)
                            const isFromCurrentUser = message.sender_id == <?php echo $_SESSION['user_id'] ?? 'null'; ?>;
                            if (isFromCurrentUser) {
                                return; // Skip our own messages
                            }

                            const messageExists = messages.some(msg => msg.id === message.id);

                            if (!messageExists) {
                                messages.push(message);
                                displayMessage(message, true);

                                lastMessageId = Math.max(lastMessageId, parseInt(message.id));
                            }
                        });
                    }
                } catch (error) {
                    console.error('Polling error:', error);
                }
            }

            // Initialize Pusher
            function initializePusher() {
                try {
                    pusher = new Pusher('585e3129f2fd92e29c0b', {
                        cluster: 'ap1',
                        authEndpoint: 'auth/pusher_auth.php',
                        auth: {
                            params: {
                                user_id: <?php echo $_SESSION['user_id'] ?? 'null'; ?>,
                                username: '<?php echo $_SESSION['username'] ?? ''; ?>'
                            }
                        },
                        enabledTransports: ['ws', 'wss']
                    });

                    pusher.connection.bind('connected', function () {
                        console.log('✅ Pusher connected successfully');
                        console.log('Socket ID:', pusher.connection.socket_id);
                    });

                    pusher.connection.bind('error', function (err) {
                        console.error('❌ Pusher connection error:', err);
                    });

                    pusher.connection.bind('state_change', function (states) {
                        console.log('Pusher state changed:', states.previous, '->', states.current);
                    });

                } catch (error) {
                    console.error('❌ Pusher initialization failed:', error);
                }
            }

            initializePusher();

            // Conversation selection
            const conversationItems = document.querySelectorAll('.conversation-item');

            conversationItems.forEach(item => {
                item.addEventListener('click', function () {
                    // Remove active class from all conversations
                    conversationItems.forEach(i => i.classList.remove('active'));

                    // Add active class to selected conversation
                    this.classList.add('active');

                    const conversationId = this.getAttribute('data-conversation-id');
                    const receiverId = this.getAttribute('data-other-user-id');
                    const userName = this.getAttribute('data-other-user-name');
                    const userInitials = userName.substring(0, 2).toUpperCase();

                    currentConversationId = conversationId;
                    currentReceiverId = receiverId;
                    currentReceiverName = userName;

                    document.getElementById('chat-user-name').textContent = userName;
                    document.getElementById('chat-avatar').textContent = userInitials;
                    document.getElementById('message-input').disabled = false;
                    document.getElementById('send-message').disabled = false;

                    loadMessages(conversationId);
                    subscribeToPrivateChannel(conversationId);
                    startPolling(conversationId);
                    handleMobileChatView();
                });
            });

            // Enhanced message display with professional styling
            function displayMessage(data, isReceived = true) {
                const messagesContainer = document.getElementById('messages-container');

                // Remove empty state if it exists
                if (messagesContainer.children.length === 1 &&
                    (messagesContainer.children[0].classList.contains('flex') ||
                        messagesContainer.children[0].classList.contains('justify-center'))) {
                    messagesContainer.innerHTML = '';
                }

                // Check if message already exists in DOM to prevent duplicates
                const existingMessage = document.querySelector(`[data-message-id="${data.id}"]`);
                if (existingMessage) {
                    // If message exists, just update its status if needed
                    if (!isReceived) {
                        const statusElement = existingMessage.querySelector('.message-status');
                        if (statusElement && data.id && !data.id.toString().includes('temp')) {
                            statusElement.innerHTML = '<span class="text-uum-green">Delivered</span>';
                        }
                    }
                    return;
                }

                // Rest of your existing displayMessage code remains the same...
                // Group messages by date
                const messageDate = new Date(data.created_at).toDateString();
                const lastMessage = messages[messages.length - 1];
                let shouldAddDateSeparator = false;

                if (!lastMessage || new Date(lastMessage.created_at).toDateString() !== messageDate) {
                    shouldAddDateSeparator = true;
                }

                // Add date separator if needed
                if (shouldAddDateSeparator && messages.length > 0) {
                    const dateSeparator = document.createElement('div');
                    dateSeparator.className = 'date-divider';
                    const formattedDate = formatMessageDate(data.created_at);
                    dateSeparator.innerHTML = `<span>${formattedDate}</span>`;
                    messagesContainer.appendChild(dateSeparator);
                }

                const messageDiv = document.createElement('div');
                messageDiv.setAttribute('data-message-id', data.id || 'temp-' + Date.now());
                messageDiv.className = `flex ${isReceived ? 'justify-start' : 'justify-end'}`;

                const messageTime = data.created_at ? formatTime(data.created_at) : 'Just now';

                if (isReceived) {
                    // Received message (left side)
                    messageDiv.innerHTML = `
            <div class="flex items-end space-x-2 max-w-[80%]">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-gradient-to-r from-uum-green to-uum-blue rounded-full flex items-center justify-center text-white font-semibold text-sm">
                        ${data.sender_initials || 'U'}
                    </div>
                </div>
                <div class="flex flex-col">
                    <div class="chat-message received px-4 py-3 max-w-full">
                        <p class="whitespace-pre-wrap break-words">${escapeHtml(data.message)}</p>
                    </div>
                    <div class="message-time text-xs text-gray-500 mt-1 ml-1">
                        ${messageTime}
                    </div>
                </div>
            </div>
        `;
                } else {
                    // Sent message (right side)
                    const messageStatus = data.id && data.id.toString().includes('temp') ?
                        '<span class="text-gray-400">Sending...</span>' :
                        '<span class="text-uum-green">Delivered</span>';

                    messageDiv.innerHTML = `
            <div class="flex items-end space-x-2 max-w-[80%]">
                <div class="flex flex-col items-end">
                    <div class="chat-message sent px-4 py-3 max-w-full">
                        <p class="whitespace-pre-wrap break-words">${escapeHtml(data.message)}</p>
                    </div>
                    <div class="flex items-center space-x-2 mt-1">
                        <div class="message-time text-xs text-white/70">
                            ${messageTime}
                        </div>
                        <div class="message-status text-xs">
                            ${messageStatus}
                        </div>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                        ${data.sender_initials || 'Y'}
                    </div>
                </div>
            </div>
        `;
                }

                messagesContainer.appendChild(messageDiv);
                scrollToBottom();
            }

            // Format time for messages
            function formatTime(dateString) {
                const date = new Date(dateString);
                return date.toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
            }

            // Format date for separators
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
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                }
            }

            // Escape HTML to prevent XSS
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Typing indicator
            function showTypingIndicator() {
                const messagesContainer = document.getElementById('messages-container');
                const existingTyping = document.querySelector('.typing-indicator');

                if (!existingTyping) {
                    const typingDiv = document.createElement('div');
                    typingDiv.className = 'typing-indicator';
                    typingDiv.innerHTML = `
                <span>${currentReceiverName} is typing</span>
                <div class="typing-dots">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
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

            // Typing detection
            const messageInput = document.getElementById('message-input');
            if (messageInput) {
                messageInput.addEventListener('input', function () {
                    if (!isTyping) {
                        isTyping = true;
                        // Send typing start event via client events
                        if (channel) {
                            channel.trigger('client-typing', {
                                user_id: <?php echo $_SESSION['user_id'] ?? 'null'; ?>,
                                username: '<?php echo $_SESSION['username'] ?? ''; ?>',
                                typing: true
                            });
                        }
                    }

                    clearTimeout(typingTimer);
                    typingTimer = setTimeout(function () {
                        isTyping = false;
                        // Send typing stop event via client events
                        if (channel) {
                            channel.trigger('client-typing', {
                                user_id: <?php echo $_SESSION['user_id'] ?? 'null'; ?>,
                                username: '<?php echo $_SESSION['username'] ?? ''; ?>',
                                typing: false
                            });
                        }
                    }, 1000);
                });

                // Also stop typing when input loses focus
                messageInput.addEventListener('blur', function () {
                    if (isTyping && channel) {
                        isTyping = false;
                        channel.trigger('client-typing', {
                            user_id: <?php echo $_SESSION['user_id'] ?? 'null'; ?>,
                            username: '<?php echo $_SESSION['username'] ?? ''; ?>',
                            typing: false
                        });
                    }
                });
            }

            // Enhanced Pusher subscription
            function subscribeToPrivateChannel(conversationId) {
                if (channel) {
                    pusher.unsubscribe(`private-chat-${currentConversationId}`);
                }

                const channelName = `private-chat-${conversationId}`;

                try {
                    channel = pusher.subscribe(channelName);

                    channel.bind('pusher:subscription_succeeded', function () {
                        console.log('✅ Successfully subscribed to PRIVATE channel:', channelName);
                    });

                    channel.bind('pusher:subscription_error', function (status) {
                        console.error('❌ Subscription error for channel:', channelName, status);
                    });

                    // Server-triggered events (from send_message.php)
                    channel.bind('new-message', function (data) {
                        console.log('📨 New real-time message received via Pusher:', data);

                        const isFromCurrentUser = data.sender_id == <?php echo $_SESSION['user_id'] ?? 'null'; ?>;

                        // Skip our own messages (they're already displayed optimistically)
                        if (isFromCurrentUser) {
                            return;
                        }

                        const messageExists = messages.some(msg => msg.id === data.id);

                        if (!messageExists) {
                            messages.push(data);
                            displayMessage(data, true);
                        }
                    });

                    // Client events for typing indicators
                    channel.bind('client-typing', function (data) {
                        if (data.user_id != <?php echo $_SESSION['user_id'] ?? 'null'; ?>) {
                            if (data.typing) {
                                showTypingIndicator();
                            } else {
                                hideTypingIndicator();
                            }
                        }
                    });

                } catch (error) {
                    console.error('❌ Channel subscription failed:', error);
                }
            }

            // Mobile view handling
            function handleMobileChatView() {
                const conversationsSidebar = document.getElementById('conversations-sidebar');
                const chatArea = document.getElementById('chat-area');
                const mobileEmptyState = document.getElementById('mobile-empty-state');

                if (window.innerWidth < 1024) {
                    conversationsSidebar.classList.add('hidden');
                    chatArea.classList.remove('hidden');
                    mobileEmptyState.classList.add('hidden');

                    // Adjust layout for mobile
                    setTimeout(() => {
                        adjustMobileLayout();
                    }, 100);
                }
            }


            // Back to conversations on mobile
            const backToConversations = document.getElementById('back-to-conversations');
            if (backToConversations) {
                backToConversations.addEventListener('click', function () {
                    const conversationsSidebar = document.getElementById('conversations-sidebar');
                    const chatArea = document.getElementById('chat-area');
                    const mobileEmptyState = document.getElementById('mobile-empty-state');

                    conversationsSidebar.classList.remove('hidden');
                    chatArea.classList.add('hidden');
                    mobileEmptyState.classList.remove('hidden');
                });
            }

            // Load messages function
            async function loadMessages(conversationId) {
                try {
                    const response = await fetch(`api/get_message.php?conversation_id=${conversationId}`);

                    if (!response.ok) {
                        throw new Error('Failed to fetch messages');
                    }

                    const messagesData = await response.json();

                    messages = messagesData;

                    const messagesContainer = document.getElementById('messages-container');
                    messagesContainer.innerHTML = '';

                    if (messages.length === 0) {
                        messagesContainer.innerHTML = `
                    <div class="flex justify-center items-center h-full">
                        <div class="text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-comments text-4xl mb-4 text-uum-green"></i>
                            <p class="text-lg">No messages yet. Start the conversation!</p>
                        </div>
                    </div>
                `;
                        return;
                    }

                    // Display all messages with enhanced formatting
                    let lastDate = null;
                    messages.forEach(message => {
                        const messageDate = new Date(message.created_at).toDateString();

                        // Add date separator if needed
                        if (lastDate !== messageDate) {
                            const dateSeparator = document.createElement('div');
                            dateSeparator.className = 'date-divider';
                            const formattedDate = formatMessageDate(message.created_at);
                            dateSeparator.innerHTML = `<span>${formattedDate}</span>`;
                            messagesContainer.appendChild(dateSeparator);
                            lastDate = messageDate;
                        }

                        const isReceived = message.sender_id != <?php echo $_SESSION['user_id'] ?? 'null'; ?>;
                        displayMessage(message, isReceived);
                    });

                    scrollToBottom();

                } catch (error) {
                    console.error('❌ Error loading messages:', error);
                    const messagesContainer = document.getElementById('messages-container');
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

            // Scroll to bottom function
            function scrollToBottom() {
                const messagesContainer = document.getElementById('messages-container');
                if (messagesContainer) {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
            }

            // Send message function
            async function sendMessage() {
                const messageInput = document.getElementById('message-input');
                const message = messageInput.value.trim();

                if (!message || !currentConversationId || !currentReceiverId) {
                    console.error('Missing required data');
                    return;
                }

                // Stop typing when sending message
                if (isTyping && channel) {
                    isTyping = false;
                    channel.trigger('client-typing', {
                        user_id: <?php echo $_SESSION['user_id'] ?? 'null'; ?>,
                        username: '<?php echo $_SESSION['username'] ?? ''; ?>',
                        typing: false
                    });
                }

                // Create temporary message ID
                const tempMessageId = 'temp-' + Date.now();

                // Optimistic UI update
                const tempMessage = {
                    id: tempMessageId,
                    conversation_id: currentConversationId,
                    sender_id: <?php echo $_SESSION['user_id'] ?? 'null'; ?>,
                    receiver_id: currentReceiverId,
                    sender_username: '<?php echo $_SESSION['username'] ?? 'You'; ?>',
                    sender_initials: '<?php echo isset($_SESSION["username"]) ? strtoupper(substr($_SESSION["username"], 0, 1)) : "Y"; ?>',
                    message: message,
                    timestamp: 'Just now',
                    created_at: new Date().toISOString(),
                    is_read: 0
                };

                // Add to messages array and display
                messages.push(tempMessage);
                displayMessage(tempMessage, false);

                // Clear input
                messageInput.value = '';

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
                        console.log('✅ Message saved to database');

                        // Update the temporary message with the real ID
                        const realMessageId = result.message_id || result.id;
                        if (realMessageId) {
                            // Find and update the temporary message
                            const tempMessageIndex = messages.findIndex(msg => msg.id === tempMessageId);
                            if (tempMessageIndex !== -1) {
                                messages[tempMessageIndex].id = realMessageId;
                                messages[tempMessageIndex].timestamp = formatTime(new Date().toISOString());

                                // Update the message status in the DOM
                                const messageElement = document.querySelector(`[data-message-id="${tempMessageId}"]`);
                                if (messageElement) {
                                    messageElement.setAttribute('data-message-id', realMessageId);

                                    // Update status to "Delivered"
                                    const statusElement = messageElement.querySelector('.message-status');
                                    if (statusElement) {
                                        statusElement.innerHTML = '<span class="text-uum-green">Delivered</span>';
                                    }

                                    // Update timestamp
                                    const timeElement = messageElement.querySelector('.message-time');
                                    if (timeElement) {
                                        timeElement.textContent = formatTime(new Date().toISOString());
                                    }
                                }
                            }
                        }

                    } else {
                        console.error('❌ Failed to send message:', result.error);
                        showMessageError(tempMessageId);
                    }

                } catch (error) {
                    console.error('❌ Error sending message:', error);
                    showMessageError(tempMessageId);
                }
            }

            function showMessageError(tempMessageId) {
                const tempMessageElement = document.querySelector(`[data-message-id="${tempMessageId}"]`);
                if (tempMessageElement) {
                    const statusElement = tempMessageElement.querySelector('.message-status');
                    if (statusElement) {
                        statusElement.innerHTML = '<span class="text-red-400">Failed</span>';
                    }
                }
            }

            function adjustMobileLayout() {
                if (window.innerWidth < 1024) {
                    const messagesContainer = document.getElementById('messages-container');
                    const chatArea = document.getElementById('chat-area');

                    if (chatArea && messagesContainer) {
                        // Ensure proper heights for mobile
                        const headerHeight = document.querySelector('#chat-area > div:first-child').offsetHeight;
                        const inputHeight = document.querySelector('#chat-area > div:last-child').offsetHeight;
                        const availableHeight = window.innerHeight - 64; // Subtract nav height

                        chatArea.style.height = `${availableHeight}px`;
                        messagesContainer.style.height = `${availableHeight - headerHeight - inputHeight}px`;

                        // Force scroll to bottom after layout adjustment
                        setTimeout(() => {
                            scrollToBottom();
                        }, 100);
                    }
                }
            }

            // Event listeners for sending messages
            const sendButton = document.getElementById('send-message');

            if (sendButton && messageInput) {
                sendButton.addEventListener('click', sendMessage);
                messageInput.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        sendMessage();
                    }
                });
            }

            // Handle window resize
            window.addEventListener('resize', function () {
                if (window.innerWidth >= 1024) {
                    const conversationsSidebar = document.getElementById('conversations-sidebar');
                    const chatArea = document.getElementById('chat-area');
                    const mobileEmptyState = document.getElementById('mobile-empty-state');

                    conversationsSidebar.classList.remove('hidden');
                    chatArea.classList.remove('hidden');
                    mobileEmptyState.classList.add('hidden');
                } else {
                    // Adjust mobile layout on resize
                    adjustMobileLayout();
                }
            });

            // Call adjustMobileLayout initially and when loading messages
            document.addEventListener('DOMContentLoaded', function () {
                // Initial mobile layout adjustment
                adjustMobileLayout();

                // Update the loadMessages function to call adjustMobileLayout
                const originalLoadMessages = loadMessages;
                loadMessages = async function (conversationId) {
                    await originalLoadMessages(conversationId);
                    adjustMobileLayout();
                };
            });

            // Test Pusher connection
            setTimeout(() => {
                if (pusher && pusher.connection.state === 'connected') {
                    console.log('🎯 Pusher is connected and ready for real-time messages');
                } else {
                    console.warn('⚠️ Pusher not connected. Real-time messaging may not work.');
                }
            }, 2000);
        });
    </script>
</body>

</html>