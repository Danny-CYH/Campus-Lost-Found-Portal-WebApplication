// Mobile menu functionality
document.addEventListener('DOMContentLoaded', function () {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const closeMobileMenu = document.getElementById('close-mobile-menu');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');

    // Open mobile menu
    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', function () {
            mobileMenu.classList.remove('translate-x-full');
            mobileMenuOverlay.classList.remove('opacity-0', 'invisible');
            mobileMenuOverlay.classList.add('opacity-50', 'visible');
            document.body.style.overflow = 'hidden';
        });
    }

    // Close mobile menu
    function closeMenu() {
        mobileMenu.classList.add('translate-x-full');
        mobileMenuOverlay.classList.remove('opacity-50', 'visible');
        mobileMenuOverlay.classList.add('opacity-0', 'invisible');
        document.body.style.overflow = '';
    }

    if (closeMobileMenu) {
        closeMobileMenu.addEventListener('click', closeMenu);
    }

    // Close menu when clicking on overlay
    if (mobileMenuOverlay) {
        mobileMenuOverlay.addEventListener('click', closeMenu);
    }

    // Mobile theme toggle
    const mobileThemeToggle = document.getElementById('mobile-theme-toggle');
    const themeStatus = document.getElementById('theme-status');

    if (mobileThemeToggle && themeStatus) {
        mobileThemeToggle.addEventListener('click', function () {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');
            const mobileThemeIcon = mobileThemeToggle.querySelector('.fas');

            if (isDark) {
                // Switch to light mode
                html.classList.remove('dark');
                mobileThemeIcon.classList.remove('fa-sun');
                mobileThemeIcon.classList.add('fa-moon');
                themeStatus.textContent = 'Light';
                document.cookie = 'theme=light; path=/; max-age=31536000';

                // Update desktop theme icon if exists
                const desktopThemeIcon = document.querySelector('#theme-icon');
                if (desktopThemeIcon) {
                    desktopThemeIcon.classList.remove('fa-sun');
                    desktopThemeIcon.classList.add('fa-moon');
                }
            } else {
                // Switch to dark mode
                html.classList.add('dark');
                mobileThemeIcon.classList.remove('fa-moon');
                mobileThemeIcon.classList.add('fa-sun');
                themeStatus.textContent = 'Dark';
                document.cookie = 'theme=dark; path=/; max-age=31536000';

                // Update desktop theme icon if exists
                const desktopThemeIcon = document.querySelector('#theme-icon');
                if (desktopThemeIcon) {
                    desktopThemeIcon.classList.remove('fa-moon');
                    desktopThemeIcon.classList.add('fa-sun');
                }
            }
        });

        // Initialize theme status
        const html = document.documentElement;
        const isDark = html.classList.contains('dark');
        themeStatus.textContent = isDark ? 'Dark' : 'Light';
    }
});