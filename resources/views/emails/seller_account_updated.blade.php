<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Seller Account Updated</title>
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
        <div class="header">Your Seller Account Has Been Updated</div>
        <div class="content">
            <p>Hello {{ $seller->name }},</p>
            <p>
                Your seller account for <strong>{{ $business->name }}</strong> (branch: <strong>{{ $branch->name }}</strong>) has been updated by an administrator.
            </p>
            <p>
                <strong>Summary of changes:</strong>
                <ul>
                    @foreach($changes as $change)
                        <li>{{ $change }}</li>
                    @endforeach
                </ul>
            </p>
            <p>
                If you did not expect these changes, please contact your administrator immediately.
            </p>
            <p>
                Thank you for being part of <strong>{{ $business->name }}</strong>!
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ $business->name }}. All rights reserved.
        </div>
    </div>
</body>
</html> 