<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'custom-green': '#8ED968',
                        'custom-green-light': '#A8E582',
                        'custom-green-dark': '#7BC951',
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-custom-green rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-semibold text-gray-800">Reset Password</h2>
            <p class="text-gray-600 mt-2">Enter your new password below</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <!-- Error Messages -->
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md hidden" id="error-messages">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <ul class="text-red-700 text-sm list-disc list-inside" id="error-list">
                            <!-- Errors will be populated here -->
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form onsubmit="handleSubmit(event)" action="{{ route('admin.reset_password.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">
                
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        New Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-custom-green focus:border-transparent transition-colors"
                            placeholder="Enter new password"
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword('password')"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center"
                        >
                            <svg class="w-5 h-5 text-gray-400 hover:text-gray-600" id="password-eye" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirm Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            required 
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-custom-green focus:border-transparent transition-colors"
                            placeholder="Confirm new password"
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword('password_confirmation')"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center"
                        >
                            <svg class="w-5 h-5 text-gray-400 hover:text-gray-600" id="password_confirmation-eye" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Password Strength Indicator -->
                <div class="mb-4">
                    <div class="text-sm text-gray-600 mb-2">Password Strength:</div>
                    <div class="flex space-x-1">
                        <div class="h-2 flex-1 bg-gray-200 rounded" id="strength-1"></div>
                        <div class="h-2 flex-1 bg-gray-200 rounded" id="strength-2"></div>
                        <div class="h-2 flex-1 bg-gray-200 rounded" id="strength-3"></div>
                        <div class="h-2 flex-1 bg-gray-200 rounded" id="strength-4"></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1" id="strength-text">Enter a password</div>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-custom-green hover:bg-custom-green-dark text-white font-medium py-2 px-4 rounded-md transition-colors duration-200 flex items-center justify-center"
                    id="submit-btn"
                >
                    <span id="btn-text">Reset Password</span>
                    <span id="btn-loading" class="hidden">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Resetting...
                    </span>
                </button>
            </form>

            <!-- Back to Login -->
            <div class="mt-4 text-center">
                <a href="{{ route('showLogin') }}" class="text-sm text-custom-green hover:text-custom-green-dark transition-colors">
                    ← Back to Login
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const eye = document.getElementById(fieldId + '-eye');
            
            if (field.type === 'password') {
                field.type = 'text';
                eye.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                `;
            } else {
                field.type = 'password';
                eye.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                `;
            }
        }

        function checkPasswordStrength(password) {
            let strength = 0;
            let feedback = [];

            if (password.length >= 8) strength++;
            else feedback.push('At least 8 characters');

            if (/[A-Z]/.test(password)) strength++;
            else feedback.push('One uppercase letter');

            if (/[a-z]/.test(password)) strength++;
            else feedback.push('One lowercase letter');

            if (/[0-9]/.test(password)) strength++;
            else feedback.push('One number');

            return { strength, feedback };
        }

        function updateStrengthIndicator(password) {
            const { strength, feedback } = checkPasswordStrength(password);
            const strengthText = document.getElementById('strength-text');
            
            // Reset all bars
            for (let i = 1; i <= 4; i++) {
                const bar = document.getElementById(`strength-${i}`);
                bar.className = 'h-2 flex-1 bg-gray-200 rounded';
            }

            // Update bars based on strength
            const colors = ['bg-red-500', 'bg-yellow-500', 'bg-blue-500', 'bg-custom-green'];
            const labels = ['Weak', 'Fair', 'Good', 'Strong'];

            for (let i = 1; i <= strength; i++) {
                const bar = document.getElementById(`strength-${i}`);
                bar.className = `h-2 flex-1 ${colors[strength - 1]} rounded`;
            }

            if (password.length === 0) {
                strengthText.textContent = 'Enter a password';
            } else {
                strengthText.textContent = strength > 0 ? labels[strength - 1] : 'Very Weak';
            }
        }

        function handleSubmit(event) {
            event.preventDefault();
            
            const password = document.getElementById('password').value;
            const passwordConfirmation = document.getElementById('password_confirmation').value;
            const errorMessages = document.getElementById('error-messages');
            const errorList = document.getElementById('error-list');
            const submitBtn = document.getElementById('submit-btn');
            const btnText = document.getElementById('btn-text');
            const btnLoading = document.getElementById('btn-loading');
            
            // Reset error display
            errorMessages.classList.add('hidden');
            errorList.innerHTML = '';
            
            // Validate passwords
            const errors = [];
            
            if (password.length < 8) {
                errors.push('Password must be at least 8 characters long');
            }
            
            if (password !== passwordConfirmation) {
                errors.push('Passwords do not match');
            }
            
            if (errors.length > 0) {
                errorList.innerHTML = errors.map(error => `<li>${error}</li>`).join('');
                errorMessages.classList.remove('hidden');
                return;
            }
            
            // Show loading state
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
            submitBtn.disabled = true;
            
            // Submit form (remove this setTimeout in production)
            setTimeout(() => {
                event.target.submit();
            }, 1000);
        }

        // Add event listeners
        document.getElementById('password').addEventListener('input', function() {
            updateStrengthIndicator(this.value);
        });

        document.getElementById('password_confirmation').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmation = this.value;
            
            if (confirmation && password !== confirmation) {
                this.classList.add('border-red-500');
            } else {
                this.classList.remove('border-red-500');
            }
        });
    </script>
</body>
</html>