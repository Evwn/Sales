<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome Owner</title>
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
        <div class="header">Welcome to the Sales Management System!</div>
        <div class="content">
            <p>Hello {{ $user->name }},</p>
            <p>
                Congratulations! Your email has been verified and you are now ready to manage your business with our Sales Management System.
            </p>
            <p>
                Here you can:
                <ul>
                    <li>Manage your businesses and branches</li>
                    <li>Oversee sales and inventory</li>
                    <li>Assign sellers and admins to branches</li>
                    <li>Generate reports and track performance</li>
                </ul>
            </p>
            <p>
                We are excited to help you grow and manage your business efficiently!
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Sales Management System. All rights reserved.
        </div>
    </div>
</body>
</html> 