<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email Verification Status Changed</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; }
        .container { max-width: 600px; margin: 0 auto; padding: 24px; background: #f9f9f9; border-radius: 8px; }
        .header { color: #2d3748; font-size: 24px; font-weight: bold; margin-bottom: 16px; }
        .content { font-size: 16px; line-height: 1.6; }
        .footer { margin-top: 32px; font-size: 13px; color: #888; }
        .status {
            font-weight: bold;
            color: {{ $status === 'verified' ? '#28a745' : '#dc3545' }};
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Email Verification Status Update</div>
        <div class="content">
            <p>Hello {{ $user->name }},</p>
            <p>
                This is to inform you that your email address has been
                <span class="status">{{ $status === 'verified' ? 'verified' : 'unverified' }}</span>
                by a system administrator.
            </p>
            @if($status === 'verified')
                <p>
                    Your email is now verified. You have full access to all features of the system. If you have any questions or need assistance, please contact support.
                </p>
            @else
                <p>
                    Your email is now unverified. Some features may be restricted until your email is verified again. If you believe this is a mistake or need further information, please contact support.
                </p>
            @endif
            <p>
                Thank you for being part of our platform.
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Sales Management System. All rights reserved.
        </div>
    </div>
</body>
</html> 