// theme.js - Combined Theme and Mobile Menu Functionality
document.addEventListener('DOMContentLoaded', function () {
    // ===== THEME FUNCTIONALITY =====
    const themeToggle = document.getElementById('theme-toggle');
    const mobileThemeToggle = document.getElementById('mobile-theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    const themeStatus = document.getElementById('theme-status');

    // Initialize theme
    function initializeTheme() {
        // Check priority: Cookie > LocalStorage > System Preference > Light
        const cookieTheme = getCookie('theme');
        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        let currentTheme = cookieTheme || savedTheme;
        if (!currentTheme) {
            currentTheme = prefersDark ? 'dark' : 'light';
        }

        applyTheme(currentTheme);
        updateThemeUI(currentTheme);
    }

    // Apply theme to HTML
    function applyTheme(theme) {
        const html = document.documentElement;

        if (theme === 'dark') {
            html.classList.add('dark');
            html.classList.remove('light');
        } else {
            html.classList.add('light');
            html.classList.remove('dark');
        }
    }

    // Update theme UI elements
    function updateThemeUI(theme) {
        const isDark = theme === 'dark';

        // Update desktop icon
        if (themeIcon) {
            themeIcon.className = isDark ? 'fas fa-sun text-yellow-400 text-lg' : 'fas fa-moon text-gray-600 dark:text-gray-400 text-lg';
        }

        // Update mobile status
        if (themeStatus) {
            themeStatus.textContent = isDark ? 'Dark' : 'Light';
        }

        // Update mobile icon
        const mobileThemeIcon = mobileThemeToggle?.querySelector('.fas');
        if (mobileThemeIcon) {
            mobileThemeIcon.className = isDark ? 'fas fa-sun text-yellow-400' : 'fas fa-moon text-gray-600 dark:text-gray-400';
        }
    }

    // Toggle theme
    function toggleTheme() {
        const currentTheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        applyTheme(newTheme);
        updateThemeUI(newTheme);

        // Save preferences
        localStorage.setItem('theme', newTheme);
        setCookie('theme', newTheme, 365);

        // Play toggle sound (optional)
        playToggleSound();
    }

    // Cookie helper functions
    function setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
    }

    function getCookie(name) {
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [cookieName, cookieValue] = cookie.trim().split('=');
            if (cookieName === name) {
                return cookieValue;
            }
        }
        return null;
    }

    function playToggleSound() {
        try {
            const audio = new Audio('https://assets.mixkit.co/sfx/preview/mixkit-toggle-swipe-2881.mp3');
            audio.volume = 0.3;
            audio.play().catch(() => { });
        } catch (e) {
            // Silent fail
        }
    }

    // ===== MOBILE MENU FUNCTIONALITY =====
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const closeMobileMenu = document.getElementById('close-mobile-menu');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');

    function openMobileMenu() {
        if (!mobileMenu || !mobileMenuOverlay) return;

        mobileMenu.classList.remove('translate-x-full');
        mobileMenu.classList.add('translate-x-0');

        mobileMenuOverlay.classList.remove('opacity-0', 'invisible');
        mobileMenuOverlay.classList.add('opacity-50', 'visible');

        document.body.style.overflow = 'hidden';

        // Update theme status in mobile menu
        updateThemeUI(document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    }

    function closeMobileMenuFunc() {
        if (!mobileMenu || !mobileMenuOverlay) return;

        mobileMenu.classList.remove('translate-x-0');
        mobileMenu.classList.add('translate-x-full');

        mobileMenuOverlay.classList.remove('opacity-50', 'visible');
        mobileMenuOverlay.classList.add('opacity-0', 'invisible');

        document.body.style.overflow = '';
    }

    // ===== INITIALIZE EVERYTHING =====

    // 1. Initialize theme
    initializeTheme();

    // 2. Setup theme toggle listeners
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }

    if (mobileThemeToggle) {
        mobileThemeToggle.addEventListener('click', toggleTheme);
    }

    // 3. Setup mobile menu listeners
    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', openMobileMenu);
    }

    if (closeMobileMenu) {
        closeMobileMenu.addEventListener('click', closeMobileMenuFunc);
    }

    if (mobileMenuOverlay) {
        mobileMenuOverlay.addEventListener('click', closeMobileMenuFunc);
    }

    // Close menu on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && mobileMenu?.classList.contains('translate-x-0')) {
            closeMobileMenuFunc();
        }
    });

    // Add smooth transitions after page load
    setTimeout(() => {
        document.body.classList.add('transition-colors', 'duration-300');
    }, 100);

    // Listen for system theme changes (only when no user preference)
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem('theme') && !getCookie('theme')) {
            const newTheme = e.matches ? 'dark' : 'light';
            applyTheme(newTheme);
            updateThemeUI(newTheme);
        }
    });
});