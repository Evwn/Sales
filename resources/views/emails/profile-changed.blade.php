<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profile Updated</title>
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
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 10px;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 5px;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            font-size: 14px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="logo">
        <h1>Profile Updated</h1>
    </div>
    <div class="content">
        <p>Hello <strong>{{ $user->name }}</strong>,</p>
        <p>We wanted to let you know that your profile information was recently updated on <strong>{{ config('app.name') }}</strong>.</p>
        <p>{{ $summary }}</p>
        <p>If you did not make this change, please contact our support team immediately.</p>
        <div class="footer">
            <p>Thanks,<br>
            <strong>{{ config('app.name') }}</strong></p>
        </div>
    </div>
</body>
</html> 