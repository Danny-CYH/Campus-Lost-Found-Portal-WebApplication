document.addEventListener('DOMContentLoaded', function () {
    // Password visibility toggle
    const togglePassword = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password');
    const toggleConfirmPassword = document.getElementById('toggle-confirm-password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    function setupPasswordToggle(toggle, input) {
        toggle.addEventListener('click', function () {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.innerHTML = type === 'password' ?
                '<i class="fas fa-eye text-gray-400 hover:text-uum-green transition-colors"></i>' :
                '<i class="fas fa-eye-slash text-gray-400 hover:text-uum-green transition-colors"></i>';
        });
    }

    if (togglePassword && passwordInput) setupPasswordToggle(togglePassword, passwordInput);
    if (toggleConfirmPassword && confirmPasswordInput) setupPasswordToggle(toggleConfirmPassword, confirmPasswordInput);

    // Username validation
    const usernameInput = document.getElementById('username');
    const usernameCheck = document.getElementById('username-check');

    if (usernameInput && usernameCheck) {
        usernameInput.addEventListener('input', function () {
            const isValid = this.value.length >= 3 && /^[a-zA-Z0-9_]+$/.test(this.value);
            usernameCheck.classList.toggle('opacity-100', isValid);
            usernameCheck.classList.toggle('opacity-0', !isValid);
        });
    }

    // Email validation
    const emailInput = document.getElementById('email');
    const emailCheck = document.getElementById('email-check');

    if (emailInput && emailCheck) {
        emailInput.addEventListener('input', function () {
            const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value);
            emailCheck.classList.toggle('opacity-100', isValid);
            emailCheck.classList.toggle('opacity-0', !isValid);
        });
    }

    // Image preview
    window.loadFile = function(event) {
    var input = event.target;
    var output = document.getElementById('preview_img');
    var placeholder = document.getElementById('preview_placeholder');

    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function(e) {
            output.src = e.target.result;
            output.classList.remove('hidden');
            placeholder.classList.add('hidden');
        };

        reader.readAsDataURL(input.files[0]);
    }
};

    // Password strength validation
    const passwordChecks = {
        length: document.getElementById('length-check'),
        complexity: document.getElementById('complexity-check'),
        match: document.getElementById('match-check')
    };

    function updatePasswordChecks() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        // Length check
        const hasLength = password.length >= 8;
        passwordChecks.length.className = `w-2 h-2 rounded-full ${hasLength ? 'bg-green-500' : 'bg-gray-300'}`;

        // Complexity check
        const hasComplexity = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/.test(password);
        passwordChecks.complexity.className = `w-2 h-2 rounded-full ${hasComplexity ? 'bg-green-500' : 'bg-gray-300'}`;

        // Match check
        const hasMatch = password === confirmPassword && password.length > 0;
        passwordChecks.match.className = `w-2 h-2 rounded-full ${hasMatch ? 'bg-green-500' : 'bg-gray-300'}`;
    }

    if (passwordInput && confirmPasswordInput) {
        passwordInput.addEventListener('input', updatePasswordChecks);
        confirmPasswordInput.addEventListener('input', updatePasswordChecks);
    }

    // Radio card selection
    const radioCards = document.querySelectorAll('.radio-card');
    radioCards.forEach(card => {
        const input = card.parentElement.querySelector('input[type="radio"]');
        card.addEventListener('click', () => {
            // Remove active state from all cards
            radioCards.forEach(c => {
                c.classList.remove('border-uum-green', 'bg-uum-green/5', 'dark:border-uum-gold');
                c.classList.add('border-gray-300', 'dark:border-gray-600');
            });

            // Add active state to clicked card
            card.classList.remove('border-gray-300', 'dark:border-gray-600');
            card.classList.add('border-uum-green', 'bg-uum-green/5', 'dark:border-uum-gold');

            // Check the radio input
            input.checked = true;
        });
    });

    // Initialize first radio card as active
    if (radioCards.length > 0) {
        radioCards[0].classList.remove('border-gray-300', 'dark:border-gray-600');
        radioCards[0].classList.add('border-uum-green', 'bg-uum-green/5', 'dark:border-uum-gold');
    }

    // Form submission animation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            const button = this.querySelector('button[type="submit"]');
            if (button) {
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating Account...';
                button.disabled = true;
            }
        });
    }
});