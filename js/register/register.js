document.addEventListener('DOMContentLoaded', function () {
    
    // 1. PASSWORD VISIBILITY TOGGLE
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


    // 2. USERNAME VALIDATION
    const usernameInput = document.getElementById('username');
    const usernameCheck = document.getElementById('username-check');

    if (usernameInput && usernameCheck) {
        usernameInput.addEventListener('input', function () {
            // Allow letters, numbers, underscores (3+ chars)
            const isValid = this.value.length >= 3 && /^[a-zA-Z0-9_]+$/.test(this.value);
            usernameCheck.classList.toggle('opacity-100', isValid);
            usernameCheck.classList.toggle('opacity-0', !isValid);
        });
    }


    // 3. EMAIL VALIDATION
    const emailInput = document.getElementById('email');
    const emailCheck = document.getElementById('email-check');

    if (emailInput && emailCheck) {
        emailInput.addEventListener('input', function () {
            const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value);
            emailCheck.classList.toggle('opacity-100', isValid);
            emailCheck.classList.toggle('opacity-0', !isValid);
        });
    }


    // 4. SCHOOL DROPDOWN VALIDATION (New)
    const schoolInput = document.getElementById('school');
    
    if (schoolInput) {
        schoolInput.addEventListener('change', function() {
            if (this.value !== "") {
                // Add green border when a valid school is selected
                this.classList.add('border-uum-green', 'ring-1', 'ring-uum-green');
                this.classList.remove('border-gray-300', 'dark:border-gray-600');
            } else {
                this.classList.remove('border-uum-green', 'ring-1', 'ring-uum-green');
                this.classList.add('border-gray-300', 'dark:border-gray-600');
            }
        });
    }


    // 5. RADIO CARD SELECTION (Updated for Gender + User Type)
    // This now checks the 'name' attribute so Gender clicks don't mess up User Type clicks
    const radioCards = document.querySelectorAll('.radio-card');
    
    radioCards.forEach(card => {
        card.addEventListener('click', function() {
            // Find the input associated with this specific card
            const input = this.parentElement.querySelector('input[type="radio"]');
            const groupName = input.name; // e.g., 'user_type' or 'gender'
            
            // 1. Find all inputs belonging to this specific group
            const groupInputs = document.querySelectorAll(`input[name="${groupName}"]`);
            
            // 2. Reset visual state ONLY for cards in this group
            groupInputs.forEach(groupInput => {
                const visualCard = groupInput.parentElement.querySelector('.radio-card');
                if (visualCard) {
                    visualCard.classList.remove('border-uum-green', 'bg-uum-green/5', 'dark:border-uum-gold');
                    visualCard.classList.add('border-gray-300', 'dark:border-gray-600');
                }
            });

            // 3. Activate the clicked card
            this.classList.remove('border-gray-300', 'dark:border-gray-600');
            this.classList.add('border-uum-green', 'bg-uum-green/5', 'dark:border-uum-gold');

            // 4. Actually check the radio button
            input.checked = true;
        });
    });
    
    // Initialize active states for all checked radios on load (fixes refresh issue)
    document.querySelectorAll('input[type="radio"]:checked').forEach(checkedInput => {
        const visualCard = checkedInput.parentElement.querySelector('.radio-card');
        if (visualCard) {
            visualCard.classList.remove('border-gray-300', 'dark:border-gray-600');
            visualCard.classList.add('border-uum-green', 'bg-uum-green/5', 'dark:border-uum-gold');
        }
    });


    // 6. PASSWORD STRENGTH VALIDATION
    const passwordChecks = {
        length: document.getElementById('length-check'),
        complexity: document.getElementById('complexity-check'),
        match: document.getElementById('match-check')
    };

    function updatePasswordChecks() {
        if (!passwordChecks.length) return; // Guard clause if elements missing

        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        // Length check
        const hasLength = password.length >= 8;
        passwordChecks.length.className = `w-2 h-2 rounded-full ${hasLength ? 'bg-green-500' : 'bg-gray-300'}`;

        // Complexity check (Letters + Numbers + Special Char)
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


    // 7. FORM SUBMISSION ANIMATION
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            const button = this.querySelector('button[type="submit"]');
            // Only show spinner if form is valid (basic check)
            if (button && form.checkValidity()) {
                const originalText = button.innerHTML; // Save text just in case
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
                button.classList.add('opacity-75', 'cursor-not-allowed');
                // button.disabled = true; // Optional: disable to prevent double submit
            }
        });
    }
});

// 8. IMAGE PREVIEW (Must be global window function for onchange="loadFile(event)")
window.loadFile = function(event) {
    var input = event.target;
    var output = document.getElementById('preview_img');
    var placeholder = document.getElementById('preview_placeholder');

    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function(e) {
            output.src = e.target.result;
            output.classList.remove('hidden');
            if (placeholder) placeholder.classList.add('hidden');
        };

        reader.readAsDataURL(input.files[0]);
    }
};