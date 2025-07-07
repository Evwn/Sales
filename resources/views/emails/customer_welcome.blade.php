<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome Customer</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; }
        .container { max-width: 600px; margin: 0 auto; padding: 24px; background: #f9f9f9; border-radius: 8px; }
        .header { color: #2d3748; font-size: 24px; font-weight: bold; margin-bottom: 16px; }
        .content { font-size: 16px; line-height: 1.6; }
        .footer { margin-top: 32px; font-size: 13px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Welcome to the Sales Management System (Customer Account)!</div>
        <div class="content">
            <p>Hello {{ $user->name }},</p>
            <p>
                Your customer account has been created. You can now access our platform to view your purchases, manage your profile, and interact with our business.
                <br>
                <strong>Your role:</strong> Customer
            </p>
            <p>
                Please check your email for an activation link or contact support if you need assistance.
            </p>
            <p>
                We are delighted to have you as our customer!
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Sales Management System. All rights reserved.
        </div>
    </div>
</body>
</html> 