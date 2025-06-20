<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Seller Account Created</title>
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
            <p>Hello {{ $seller->name }},</p>
            <p>
                You have been assigned as a <strong>Seller</strong> to the branch <strong>{{ $branch->name }}</strong> by the business <strong>{{ $business->name }}</strong>.
            </p>
            <p>
                An account has been created for you on our sales platform. On your first login, you will be required to verify your email address. Please check your inbox for a verification email and follow the instructions to activate your account.
            </p>
            <p>
                <strong>If you did not expect this email, you can safely ignore it and no changes will be made to your account.</strong>
            </p>
            <p>
                We are excited to have you on board!<br>
                <em>- The {{ $business->name }} Team</em>
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ $business->name }}. All rights reserved.
        </div>
    </div>
</body>
</html> 