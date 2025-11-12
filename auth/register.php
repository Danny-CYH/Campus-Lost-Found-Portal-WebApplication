<?php include '../includes/config.php'; ?>

<!DOCTYPE html>
<html lang="en" class="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - UUM Campus Lost & Found Portal</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/styles.css">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        uum: {
                            green: '#006837',
                            gold: '#FFD700',
                            blue: '#0056b3',
                            light: {
                                green: '#e8f5e8',
                                blue: '#e6f0fa'
                            }
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-uum-light-green to-uum-light-blue dark:from-gray-800 dark:to-gray-900 min-h-screen flex items-center justify-center p-4">
    <!-- Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-uum-green/10 rounded-full animate-float"></div>
        <div class="absolute top-1/4 -right-16 w-32 h-32 bg-uum-blue/10 rounded-full animate-float" style="animation-delay: 2s;"></div>
        <div class="absolute bottom-32 -left-16 w-40 h-40 bg-uum-green/5 rounded-full animate-float" style="animation-delay: 4s;"></div>
        <div class="absolute -bottom-20 -right-20 w-56 h-56 bg-uum-blue/5 rounded-full animate-float" style="animation-delay: 1s;"></div>
    </div>

    <div class="w-full max-w-6xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            <!-- Left Side - Brand & Benefits -->
            <div class="text-center lg:text-left space-y-8">
                <!-- Header -->
                <div class="space-y-4">
                    <a href="../index.php" class="inline-flex items-center space-x-4">
                        <div class="w-16 h-16 bg-gradient-to-r from-uum-green to-uum-blue rounded-2xl flex items-center justify-center shadow-2xl">
                            <i class="fas fa-search-location text-white text-2xl"></i>
                        </div>
                        <div class="text-left">
                            <h1 class="text-3xl lg:text-4xl font-bold text-uum-green dark:text-uum-gold">
                                UUM Find
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400 text-sm lg:text-base">Lost & Found Portal</p>
                        </div>
                    </a>
                    
                    <div class="bg-uum-green/10 border border-uum-green/20 rounded-2xl p-4 inline-flex items-center space-x-2">
                        <i class="fas fa-university text-uum-green text-lg"></i>
                        <span class="text-uum-green font-medium text-sm">Universiti Utara Malaysia</span>
                    </div>
                </div>

                <!-- Benefits -->
                <div class="space-y-6">
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
                        Join the UUM 
                        <span class="text-uum-green dark:text-uum-gold">Community</span>
                    </h2>
                    <p class="text-lg text-gray-600 dark:text-gray-300 max-w-md">
                        Create your account to report lost items, help others find their belongings, and contribute to our campus community.
                    </p>
                    
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3 p-3 bg-white/50 dark:bg-gray-800/50 rounded-xl backdrop-blur-sm">
                            <div class="w-10 h-10 bg-uum-green/10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-bullhorn text-uum-green"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-700 dark:text-gray-300">Report Lost Items</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Quickly report your lost belongings</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3 p-3 bg-white/50 dark:bg-gray-800/50 rounded-xl backdrop-blur-sm">
                            <div class="w-10 h-10 bg-uum-blue/10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-hand-holding-heart text-uum-blue"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-700 dark:text-gray-300">Help Others</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Return found items to their owners</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3 p-3 bg-white/50 dark:bg-gray-800/50 rounded-xl backdrop-blur-sm">
                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shield-alt text-green-600 dark:text-green-400"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-700 dark:text-gray-300">Secure Platform</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Your data is protected and secure</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial -->
                <div class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 bg-uum-green rounded-full flex items-center justify-center text-white font-semibold">
                            AS
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-white">Ahmad Salleh</h4>
                            <p class="text-sm text-uum-green">Computer Science Student</p>
                        </div>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 text-sm italic">
                        "Found my lost laptop within hours thanks to UUM Find! The community here is amazing."
                    </p>
                </div>
            </div>

            <!-- Right Side - Registration Form -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/20 p-8">
                <!-- Form Header -->
                <div class="text-center mb-8">
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
                        Create Account
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Join UUM Campus Find today
                    </p>
                </div>

                <!-- Registration Form -->
                <form class="space-y-6" action="process_register.php" method="POST">
                    <div class="space-y-4">
                        <!-- Username Field -->
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fas fa-user mr-2 text-uum-green"></i>
                                Username
                            </label>
                            <div class="relative">
                                <input 
                                    id="username" 
                                    name="username" 
                                    type="text" 
                                    required 
                                    class="w-full pl-4 pr-4 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-uum-green focus:border-uum-green transition-all duration-200"
                                    placeholder="Choose a username"
                                >
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i class="fas fa-check-circle text-green-500 opacity-0 transition-opacity duration-200" id="username-check"></i>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">3-20 characters, letters and numbers only</p>
                        </div>

                        <!-- Email Field -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fas fa-envelope mr-2 text-uum-green"></i>
                                Email Address
                            </label>
                            <div class="relative">
                                <input 
                                    id="email" 
                                    name="email" 
                                    type="email" 
                                    required 
                                    class="w-full pl-4 pr-4 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-uum-green focus:border-uum-green transition-all duration-200"
                                    placeholder="Enter your email address"
                                >
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i class="fas fa-check-circle text-green-500 opacity-0 transition-opacity duration-200" id="email-check"></i>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">We'll send a verification email</p>
                        </div>

                        <!-- Password Field -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fas fa-lock mr-2 text-uum-green"></i>
                                Password
                            </label>
                            <div class="relative">
                                <input 
                                    id="password" 
                                    name="password" 
                                    type="password" 
                                    required 
                                    class="w-full pl-4 pr-12 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-uum-green focus:border-uum-green transition-all duration-200"
                                    placeholder="Create a strong password"
                                >
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" id="toggle-password">
                                    <i class="fas fa-eye text-gray-400 hover:text-uum-green transition-colors"></i>
                                </button>
                            </div>
                            <div class="mt-2 space-y-1">
                                <div class="flex items-center space-x-2">
                                    <div id="length-check" class="w-2 h-2 bg-gray-300 rounded-full"></div>
                                    <span class="text-xs text-gray-500">At least 8 characters</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div id="complexity-check" class="w-2 h-2 bg-gray-300 rounded-full"></div>
                                    <span class="text-xs text-gray-500">Letters, numbers, and symbols</span>
                                </div>
                            </div>
                        </div>

                        <!-- Confirm Password Field -->
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fas fa-lock mr-2 text-uum-green"></i>
                                Confirm Password
                            </label>
                            <div class="relative">
                                <input 
                                    id="confirm_password" 
                                    name="confirm_password" 
                                    type="password" 
                                    required 
                                    class="w-full pl-4 pr-12 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-uum-green focus:border-uum-green transition-all duration-200"
                                    placeholder="Confirm your password"
                                >
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" id="toggle-confirm-password">
                                    <i class="fas fa-eye text-gray-400 hover:text-uum-green transition-colors"></i>
                                </button>
                            </div>
                            <div class="flex items-center space-x-2 mt-2">
                                <div id="match-check" class="w-2 h-2 bg-gray-300 rounded-full"></div>
                                <span class="text-xs text-gray-500">Passwords must match</span>
                            </div>
                        </div>
                    </div>

                    <!-- User Type Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            <i class="fas fa-user-tag mr-2 text-uum-green"></i>
                            I am a:
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative flex cursor-pointer">
                                <input type="radio" name="user_type" value="student" class="sr-only" checked>
                                <div class="flex-1 text-center py-3 px-4 border-2 border-gray-300 dark:border-gray-600 rounded-xl transition-all duration-200 radio-card">
                                    <i class="fas fa-graduation-cap text-uum-green text-lg mb-2"></i>
                                    <div class="font-medium text-gray-700 dark:text-gray-300">Student</div>
                                </div>
                            </label>
                            <label class="relative flex cursor-pointer">
                                <input type="radio" name="user_type" value="staff" class="sr-only">
                                <div class="flex-1 text-center py-3 px-4 border-2 border-gray-300 dark:border-gray-600 rounded-xl transition-all duration-200 radio-card">
                                    <i class="fas fa-briefcase text-uum-blue text-lg mb-2"></i>
                                    <div class="font-medium text-gray-700 dark:text-gray-300">Staff</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Terms Agreement -->
                    <div class="flex items-start space-x-3">
                        <input 
                            id="agree_terms" 
                            name="agree_terms" 
                            type="checkbox" 
                            required
                            class="w-4 h-4 text-uum-green focus:ring-uum-green border-gray-300 rounded mt-1"
                        >
                        <label for="agree_terms" class="text-sm text-gray-700 dark:text-gray-300">
                            I agree to the
                            <a href="#" class="font-semibold text-uum-green hover:text-uum-blue transition-colors">Terms of Service</a>
                            and
                            <a href="#" class="font-semibold text-uum-green hover:text-uum-blue transition-colors">Privacy Policy</a>
                            <span class="text-red-500">*</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-uum-green to-uum-blue hover:from-uum-blue hover:to-uum-green text-white py-3 px-4 rounded-xl font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-uum-green focus:ring-offset-2"
                    >
                        <i class="fas fa-user-plus mr-2"></i>
                        Create Your Account
                    </button>

                    <!-- Divider -->
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white dark:bg-gray-800 text-gray-500">Or sign up with</span>
                        </div>
                    </div>

                    <!-- Social Registration -->
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" class="w-full inline-flex justify-center items-center py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            <i class="fab fa-google text-red-500 mr-2"></i>
                            Google
                        </button>
                        <button type="button" class="w-full inline-flex justify-center items-center py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            <i class="fab fa-microsoft text-blue-500 mr-2"></i>
                            Microsoft
                        </button>
                    </div>

                    <!-- Login Link -->
                    <div class="text-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            Already have an account?
                            <a href="login.php" class="font-semibold text-uum-green hover:text-uum-blue transition-colors">
                                Sign in here
                            </a>
                        </span>
                    </div>
                </form>

                <!-- Security Notice -->
                <div class="mt-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-shield-alt text-green-600"></i>
                        <span class="text-sm font-medium text-green-800 dark:text-green-300">Secure Registration</span>
                    </div>
                    <p class="text-xs text-green-700 dark:text-green-400 mt-1">
                        Your information is protected with industry-standard encryption and security measures.
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                &copy; 2024 Universiti Utara Malaysia - Lost & Found Portal. 
                <a href="#" class="text-uum-green hover:text-uum-blue transition-colors">Privacy Policy</a> • 
                <a href="#" class="text-uum-green hover:text-uum-blue transition-colors">Terms of Service</a>
            </p>
        </div>
    </div>

    <script src="../js/theme.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password visibility toggle
            const togglePassword = document.getElementById('toggle-password');
            const passwordInput = document.getElementById('password');
            const toggleConfirmPassword = document.getElementById('toggle-confirm-password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            
            function setupPasswordToggle(toggle, input) {
                toggle.addEventListener('click', function() {
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
                usernameInput.addEventListener('input', function() {
                    const isValid = this.value.length >= 3 && /^[a-zA-Z0-9_]+$/.test(this.value);
                    usernameCheck.classList.toggle('opacity-100', isValid);
                    usernameCheck.classList.toggle('opacity-0', !isValid);
                });
            }

            // Email validation
            const emailInput = document.getElementById('email');
            const emailCheck = document.getElementById('email-check');
            
            if (emailInput && emailCheck) {
                emailInput.addEventListener('input', function() {
                    const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value);
                    emailCheck.classList.toggle('opacity-100', isValid);
                    emailCheck.classList.toggle('opacity-0', !isValid);
                });
            }

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
                form.addEventListener('submit', function(e) {
                    const button = this.querySelector('button[type="submit"]');
                    if (button) {
                        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating Account...';
                        button.disabled = true;
                    }
                });
            }
        });
    </script>
</body>
</html>