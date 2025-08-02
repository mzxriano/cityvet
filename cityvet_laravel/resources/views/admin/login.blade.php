<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CityVet</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5; 
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    
    <div class="flex flex-col lg:flex-row w-full max-w-7xl bg-white rounded-lg shadow-lg overflow-hidden">
        
        <div class="lg:w-1/2 flex flex-col items-center justify-center p-8 bg-gray-100">
            @if($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                </div>
            @endif
            
            <img src="{{ asset('images/cityvet-logo.png') }}">
            <h2 class="text-4xl font-bold text-gray-800 mb-2">CityVet</h2>
            <p class="text-gray-600 text-center text-sm">URDANETA CITY AGRICULTURE OFFICE</p>
        </div>
        
        <div class="lg:w-1/2 p-8 sm:p-12 lg:p-16 flex flex-col justify-center">
            <h2 class="text-3xl font-semibold text-center text-gray-900 mb-8">Log In</h2>
            
            <form class="space-y-6" method="POST" action="{{ route('login') }}">
                @csrf
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-gray-400"></i>
                    </div>
                    <input type="email" name="email" placeholder="Enter your Email" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input type="password" id="password" name="password" placeholder="Enter your Password" class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-700">
                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                        <i id="eyeIcon" class="fas fa-eye"></i>
                    </button>
                </div>
                
                <div class="text-right">
                    <a href="{{ route('admin.forgot_password') }}" class="text-sm font-medium text-red-500 hover:text-red-600">Forgot Password?</a>
                </div>
                
                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-lg font-medium text-white bg-green-500 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-200 ease-in-out">
                        Log In
                    </button>
                </div>
            </form>
            
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            togglePassword.addEventListener('click', function() {
                // Toggle the type attribute
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle the eye icon
                if (type === 'password') {
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                } else {
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                }
            });
        });
    </script>
</body>
</html>