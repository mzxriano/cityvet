<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Forgot Password</title>
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
    <!-- Enhanced Status Messages with animations -->
    <div id="message-container" class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-md px-4">
        <!-- Success Status Message -->
        @if(session('status'))
            <div class="mb-4 p-4 bg-gradient-to-r from-green-50 to-green-100 border-l-4 border-green-400 rounded-lg shadow-lg animate-slide-down">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">Success!</p>
                        <p class="text-sm text-green-700">{{ session('status') }}</p>
                    </div>
                    <div class="ml-auto">
                        <button onclick="this.parentElement.parentElement.parentElement.style.display='none'" class="text-green-400 hover:text-green-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Enhanced Error Messages -->
        @if ($errors->any())
            <div class="mb-4 p-4 bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-400 rounded-lg shadow-lg animate-slide-down">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-red-800">
                            {{ $errors->count() > 1 ? 'Please fix the following errors:' : 'Error occurred:' }}
                        </p>
                        <div class="mt-2 text-sm text-red-700">
                            @if($errors->count() === 1)
                                <p>{{ $errors->first() }}</p>
                            @else
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                    <div class="ml-auto">
                        <button onclick="this.parentElement.parentElement.parentElement.style.display='none'" class="text-red-400 hover:text-red-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-custom-green rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-semibold text-gray-800">Forgot Password</h2>
            <p class="text-gray-600 mt-2">Enter your email to receive a reset link</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <!-- Enhanced Success Message for JavaScript -->
            <div class="mb-4 p-4 bg-gradient-to-r from-green-50 to-green-100 border-l-4 border-green-400 rounded-lg shadow-sm hidden animate-fade-in" id="success-message">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">Success!</p>
                        <p class="text-sm text-green-700">Password reset link has been sent to your email.</p>
                    </div>
                    <div class="ml-auto">
                        <button onclick="this.parentElement.parentElement.parentElement.classList.add('hidden')" class="text-green-400 hover:text-green-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('admin.forgot_password.send') }}" id="forgot-password-form">
                <!-- CSRF Token -->
                @csrf                
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>
                    <div class="relative">
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required 
                            class="w-full px-3 py-2 pl-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-custom-green focus:border-transparent transition-colors"
                            placeholder="Enter your email"
                            value="{{ old('email') }}"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                    </div>
                    <!-- Enhanced field-specific error message -->
                    <div class="text-red-600 text-sm mt-2 hidden animate-fade-in" id="email-error">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            <span>Please enter a valid email address</span>
                        </div>
                    </div>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-custom-green hover:bg-custom-green-dark text-white font-medium py-3 px-4 rounded-md transition-all duration-200 flex items-center justify-center transform hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl"
                    id="submit-btn"
                >
                    <span id="btn-text" class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Send Reset Link
                    </span>
                    <span id="btn-loading" class="hidden">
                        <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Sending...
                    </span>
                </button>
            </form>

            <!-- Back to Login -->
            <div class="mt-6 text-center">
                <a href="{{ route('showLogin') }}" class="inline-flex items-center text-sm text-custom-green hover:text-custom-green-dark transition-colors group">
                    <svg class="w-4 h-4 mr-1 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Login
                </a>
            </div>
        </div>
    </div>

    <style>
        @keyframes slide-down {
            0% { transform: translateY(-100px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes fade-in {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
        
        .animate-slide-down {
            animation: slide-down 0.5s ease-out;
        }
        
        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
    </style>

    <script>
        // Enhanced form validation and user feedback
        document.getElementById('forgot-password-form').addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const emailError = document.getElementById('email-error');
            const submitBtn = document.getElementById('submit-btn');
            const btnText = document.getElementById('btn-text');
            const btnLoading = document.getElementById('btn-loading');
            
            // Reset error state
            emailError.classList.add('hidden');
            email.classList.remove('border-red-300', 'focus:ring-red-500');
            email.classList.add('border-gray-300', 'focus:ring-custom-green');
            
            // Validate email
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email.value)) {
                e.preventDefault();
                emailError.classList.remove('hidden');
                email.classList.remove('border-gray-300', 'focus:ring-custom-green');
                email.classList.add('border-red-300', 'focus:ring-red-500');
                email.focus();
                return;
            }
            
            // Show loading state
            submitBtn.disabled = true;
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
            submitBtn.classList.add('opacity-75');
        });
        
        // Real-time email validation
        document.getElementById('email').addEventListener('input', function() {
            const email = this;
            const emailError = document.getElementById('email-error');
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email.value && !emailPattern.test(email.value)) {
                emailError.classList.remove('hidden');
                email.classList.remove('border-gray-300', 'focus:ring-custom-green');
                email.classList.add('border-red-300', 'focus:ring-red-500');
            } else {
                emailError.classList.add('hidden');
                email.classList.remove('border-red-300', 'focus:ring-red-500');
                email.classList.add('border-gray-300', 'focus:ring-custom-green');
            }
        });
        
        // Auto-hide messages after 10 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('#message-container > div');
            messages.forEach(message => {
                message.style.opacity = '0';
                message.style.transform = 'translateY(-20px)';
                setTimeout(() => message.style.display = 'none', 300);
            });
        }, 10000);
    </script>
</body>
</html>