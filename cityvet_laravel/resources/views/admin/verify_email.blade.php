<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Email Verification Required</title>
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
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
            border: none;
            cursor: pointer;
        }
        .button:hover {
            background-color: #54AF26;
        }
        .info {
            margin-top: 20px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Email Verification Required</h1>
        <p>Before you can access the admin dashboard, you need to verify your email address.</p>
        <p>Please check your inbox for a verification link.</p>
        <form method="POST" action="{{ route('admin.verification.resend') }}">
            @csrf
            <button type="submit" class="button">Resend Verification Email</button>
        </form>
        @if (session('status'))
            <div class="info">{{ session('status') }}</div>
        @endif
        <div class="info">
            If you did not receive the email, click the button above to resend the verification link.
        </div>
    </div>
</body>
</html> 