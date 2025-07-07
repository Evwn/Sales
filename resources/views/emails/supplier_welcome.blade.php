<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome Supplier</title>
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
        <div class="header">Welcome to the Sales Management System (Supplier Account)!</div>
        <div class="content">
            <p>Hello {{ $user->name }},</p>
            <p>
                Your supplier account has been created. You can now access our platform to manage your supplies, view orders, and collaborate with our business.
                <br>
                <strong>Your role:</strong> Supplier
            </p>
            <p>
                Please check your email for an activation link or contact our procurement team if you need assistance.
            </p>
            <p>
                We look forward to a successful partnership!
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Sales Management System. All rights reserved.
        </div>
    </div>
</body>
</html> 