<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome Seller</title>
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
        <div class="header">Welcome to {{ $business->name }}!</div>
        <div class="content">
            <p>Hello {{ $user->name }},</p>
            <p>
                Congratulations! Your email has been verified and you are now a seller at <strong>{{ $business->name }}</strong>.
            </p>
            <p>
                <strong>Your assigned branch:</strong> {{ $branch->name }}<br>
                We are excited to have you working with us at {{ $business->name }}!
            </p>
            <p>
                If you have any questions, please contact your branch manager or business administrator.
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ $business->name }}. All rights reserved.
        </div>
    </div>
</body>
</html> 