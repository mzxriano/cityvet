<!DOCTYPE html>
<html>
<head>
    <title>CityVet Account Created</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2563eb;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8fafc;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            border: 1px solid #e2e8f0;
        }
        .credentials-box {
            background-color: #e0f2fe;
            border: 1px solid #0ea5e9;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 14px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🐾 Welcome to CityVet</h1>
        <p>Your animal owner account has been created</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $user->first_name }} {{ $user->last_name }},</h2>
        
        <p>Your CityVet animal owner account has been successfully created by <strong>{{ $created_by }}</strong>.</p>
        
        <div class="credentials-box">
            <h3>🔐 Your Login Credentials</h3>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Password:</strong> <code style="background: #e2e8f0; padding: 4px 8px; border-radius: 4px; font-family: monospace;">{{ $password }}</code></p>
        </div>
        
        <div class="warning">
            <h4>⚠️ Important Security Notice</h4>
            <ul>
                <li>Please change your password immediately after logging in</li>
                <li>Keep your login credentials secure and private</li>
                <li>Do not share your account with others</li>
            </ul>
        </div>
        
        {{-- <p>If you have any questions or need assistance, please contact us or the staff member who created your account.</p> --}}
        
        {{-- <div class="footer">
            <p>This email was sent automatically from the CityVet system. Please do not reply to this email.</p>
            <p><strong>CityVet</strong> - Keeping Your Animals Healthy</p>
        </div> --}}
    </div>
</body>
</html>
