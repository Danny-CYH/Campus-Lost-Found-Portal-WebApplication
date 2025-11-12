// Mobile Menu Functionality
document.addEventListener('DOMContentLoaded', function () {
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

    // Chat functionality
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-message');
    const messagesContainer = document.getElementById('messages-container');

    function sendMessage() {
        const message = messageInput.value.trim();
        if (!message) return;

        // Create message element
        const messageDiv = document.createElement('div');
        messageDiv.className = 'flex items-start space-x-2 justify-end';
        messageDiv.innerHTML = `
                    <div class="flex-1 max-w-[85%] flex justify-end">
                        <div class="bg-uum-green text-white rounded-2xl rounded-tr-none px-3 py-2 shadow-sm">
                            <p class="text-sm">${message}</p>
                            <span class="text-xs text-green-100 mt-1 block">${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                        </div>
                    </div>
                    <div class="w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center text-white font-semibold text-xs flex-shrink-0">
                        <?php
                        if (isset($_SESSION['username'])) {
                            echo strtoupper(substr($_SESSION['username'], 0, 1));
                        } else {
                            echo 'Y';
                        }
                        ?>
                    </div>
                `;

        messagesContainer.appendChild(messageDiv);
        messageInput.value = '';
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        // Simulate reply after 1-2 seconds
        setTimeout(simulateReply, 1000 + Math.random() * 1000);
    }

    function simulateReply() {
        const replies = [
            "Thanks for letting me know!",
            "I'll be there in 10 minutes.",
            "Can you describe it in more detail?",
            "That's exactly what I was looking for!",
            "Where should we meet to exchange?"
        ];

        const randomReply = replies[Math.floor(Math.random() * replies.length)];

        const replyDiv = document.createElement('div');
        replyDiv.className = 'flex items-start space-x-2';
        replyDiv.innerHTML = `
                    <div class="w-8 h-8 bg-gradient-to-r from-uum-green to-uum-blue rounded-full flex items-center justify-center text-white font-semibold text-xs flex-shrink-0">
                        AS
                    </div>
                    <div class="flex-1 max-w-[85%]">
                        <div class="bg-white dark:bg-gray-700 rounded-2xl rounded-tl-none px-3 py-2 shadow-sm">
                            <p class="text-gray-900 dark:text-white text-sm">${randomReply}</p>
                            <span class="text-xs text-gray-500 mt-1 block">${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                        </div>
                    </div>
                `;

        messagesContainer.appendChild(replyDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    if (sendButton && messageInput) {
        sendButton.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }

    // Auto-scroll to bottom on load
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});