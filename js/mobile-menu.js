// Mobile menu functionality
document.addEventListener('DOMContentLoaded', function () {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    const closeMobileMenu = document.getElementById('close-mobile-menu');

    if (mobileMenuButton && mobileMenu && closeMobileMenu) {
        // Open mobile menu
        mobileMenuButton.addEventListener('click', function () {
            mobileMenu.classList.remove('translate-x-full');
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        });

        // Close mobile menu
        closeMobileMenu.addEventListener('click', function () {
            mobileMenu.classList.add('translate-x-full');
            document.body.style.overflow = ''; // Restore scrolling
        });

        // Close menu when clicking on links
        const mobileMenuLinks = mobileMenu.querySelectorAll('a');
        mobileMenuLinks.forEach(link => {
            link.addEventListener('click', function () {
                mobileMenu.classList.add('translate-x-full');
                document.body.style.overflow = ''; // Restore scrolling
            });
        });

        // Close menu when clicking outside
        mobileMenu.addEventListener('click', function (e) {
            if (e.target === mobileMenu) {
                mobileMenu.classList.add('translate-x-full');
                document.body.style.overflow = ''; // Restore scrolling
            }
        });

        // Mobile theme toggle
        const mobileThemeToggle = document.getElementById('mobile-theme-toggle');
        const mobileThemeIcon = mobileThemeToggle ? mobileThemeToggle.querySelector('i') : null;

        if (mobileThemeToggle && mobileThemeIcon) {
            mobileThemeToggle.addEventListener('click', function () {
                const html = document.documentElement;
                const isDark = html.classList.contains('dark');

                if (isDark) {
                    html.classList.remove('dark');
                    mobileThemeIcon.classList.remove('fa-sun');
                    mobileThemeIcon.classList.add('fa-moon');
                    document.cookie = 'theme=light; path=/; max-age=31536000';
                } else {
                    html.classList.add('dark');
                    mobileThemeIcon.classList.remove('fa-moon');
                    mobileThemeIcon.classList.add('fa-sun');
                    document.cookie = 'theme=dark; path=/; max-age=31536000';
                }
            });
        }
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// User dropdown functionality
document.addEventListener('DOMContentLoaded', function () {
    const userDropdownBtn = document.getElementById('user-dropdown-btn');
    const userDropdownMenu = document.getElementById('user-dropdown-menu');
    const dropdownArrow = document.getElementById('dropdown-arrow');

    if (userDropdownBtn && userDropdownMenu && dropdownArrow) {
        let isOpen = false;

        // Toggle dropdown menu
        userDropdownBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            isOpen = !isOpen;

            if (isOpen) {
                openDropdown();
            } else {
                closeDropdown();
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (isOpen && !userDropdownBtn.contains(e.target) && !userDropdownMenu.contains(e.target)) {
                closeDropdown();
            }
        });

        // Close dropdown when pressing Escape key
        document.addEventListener('keydown', function (e) {
            if (isOpen && e.key === 'Escape') {
                closeDropdown();
            }
        });

        // Close dropdown when clicking on links inside dropdown
        const dropdownLinks = userDropdownMenu.querySelectorAll('a');
        dropdownLinks.forEach(link => {
            link.addEventListener('click', function () {
                closeDropdown();
            });
        });

        function openDropdown() {
            userDropdownMenu.classList.remove('opacity-0', 'invisible', 'scale-95');
            userDropdownMenu.classList.add('opacity-100', 'visible', 'scale-100');
            dropdownArrow.style.transform = 'rotate(180deg)';
            isOpen = true;
        }

        function closeDropdown() {
            userDropdownMenu.classList.remove('opacity-100', 'visible', 'scale-100');
            userDropdownMenu.classList.add('opacity-0', 'invisible', 'scale-95');
            dropdownArrow.style.transform = 'rotate(0deg)';
            isOpen = false;
        }

        // Initialize dropdown as closed
        closeDropdown();
    }
});

// Mobile menu functionality
document.addEventListener('DOMContentLoaded', function () {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    const closeMobileMenu = document.getElementById('close-mobile-menu');

    if (mobileMenuButton && mobileMenu && closeMobileMenu) {
        // Open mobile menu
        mobileMenuButton.addEventListener('click', function () {
            mobileMenu.classList.remove('translate-x-full');
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        });

        // Close mobile menu
        closeMobileMenu.addEventListener('click', function () {
            mobileMenu.classList.add('translate-x-full');
            document.body.style.overflow = ''; // Restore scrolling
        });

        // Close menu when clicking outside
        mobileMenu.addEventListener('click', function (e) {
            if (e.target === mobileMenu) {
                mobileMenu.classList.add('translate-x-full');
                document.body.style.overflow = ''; // Restore scrolling
            }
        });

        // Mobile theme toggle
        const mobileThemeToggle = document.getElementById('mobile-theme-toggle');
        const themeStatus = document.getElementById('theme-status');

        if (mobileThemeToggle && themeStatus) {
            mobileThemeToggle.addEventListener('click', function () {
                const html = document.documentElement;
                const isDark = html.classList.contains('dark');
                const mobileThemeIcon = mobileThemeToggle.querySelector('.fas');

                if (isDark) {
                    html.classList.remove('dark');
                    mobileThemeIcon.classList.remove('fa-sun');
                    mobileThemeIcon.classList.add('fa-moon');
                    themeStatus.textContent = 'Light';
                    document.cookie = 'theme=light; path=/; max-age=31536000';

                    // Update desktop theme icon if it exists
                    const desktopThemeIcon = document.querySelector('#theme-icon');
                    if (desktopThemeIcon) {
                        desktopThemeIcon.classList.remove('fa-sun');
                        desktopThemeIcon.classList.add('fa-moon');
                    }
                } else {
                    html.classList.add('dark');
                    mobileThemeIcon.classList.remove('fa-moon');
                    mobileThemeIcon.classList.add('fa-sun');
                    themeStatus.textContent = 'Dark';
                    document.cookie = 'theme=dark; path=/; max-age=31536000';

                    // Update desktop theme icon if it exists
                    const desktopThemeIcon = document.querySelector('#theme-icon');
                    if (desktopThemeIcon) {
                        desktopThemeIcon.classList.remove('fa-moon');
                        desktopThemeIcon.classList.add('fa-sun');
                    }
                }
            });

            // Initialize theme status on load
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');
            themeStatus.textContent = isDark ? 'Dark' : 'Light';
        }
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});