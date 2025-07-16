<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Email Verification</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
        }
        .button {
            display: inline-block;
            background-color: #8ED968;
            color: #ffffff;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-size: 16px;
        }
        .button:hover {
            background-color: #54AF26;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome {{ $user->name }}!</h1>
        <p>Thank you for registering with our app. Please verify your email address by clicking the button below:</p>
        
        <a href="{{ $verificationUrl }}" class="button">Verify Email Address</a>
        
        <p>If you didn't create an account, please ignore this email.</p>
    </div>
</body>
</html>