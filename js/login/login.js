document.addEventListener('DOMContentLoaded', function () {
    // Password visibility toggle
    const togglePassword = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye text-gray-400 hover:text-uum-green transition-colors"></i>' : '<i class="fas fa-eye-slash text-gray-400 hover:text-uum-green transition-colors"></i>';
        });
    }

    // Username validation indicator
    const usernameInput = document.getElementById('username');
    const usernameCheck = document.getElementById('username-check');

    if (usernameInput && usernameCheck) {
        usernameInput.addEventListener('input', function () {
            if (this.value.length > 2) {
                usernameCheck.classList.remove('opacity-0');
                usernameCheck.classList.add('opacity-100');
            } else {
                usernameCheck.classList.remove('opacity-100');
                usernameCheck.classList.add('opacity-0');
            }
        });
    }

    // Form submission animation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            const button = this.querySelector('button[type="submit"]');
            if (button) {
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Signing in...';
                button.disabled = true;
            }
        });
    }
});