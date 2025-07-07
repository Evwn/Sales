<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome Admin</title>
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
        <div class="header">Welcome to the Sales Management System (Admin Account)!</div>
        <div class="content">
            <p>Hello {{ $user->name }},</p>
            <p>
                Your admin account has been created. You now have access to manage users, roles, businesses, branches, and system settings.<br>
                <strong>Your role:</strong> Admin
            </p>
            <p>
                Please check your email for an activation link or contact your system administrator if you need assistance.
            </p>
            <p>
                We are excited to have you as a system administrator!
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Sales Management System. All rights reserved.
        </div>
    </div>
</body>
</html> 