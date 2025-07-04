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
        .changes-list {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .change-item {
            margin: 12px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .change-item:last-child {
            border-bottom: none;
        }
        .field-name {
            font-weight: bold;
            color: #495057;
            margin-bottom: 5px;
        }
        .old-value {
            color: #dc3545;
            text-decoration: line-through;
            background-color: #f8d7da;
            padding: 2px 6px;
            border-radius: 3px;
            margin-right: 10px;
        }
        .new-value {
            color: #28a745;
            font-weight: bold;
            background-color: #d4edda;
            padding: 2px 6px;
            border-radius: 3px;
        }
        .arrow {
            color: #6c757d;
            margin: 0 10px;
            font-size: 18px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 15px 0;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            font-size: 14px;
            color: #6c757d;
        }
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .warning-title {
            margin-top: 0;
            color: #856404;
            font-weight: bold;
        }
        .warning-text {
            margin-bottom: 0;
            color: #856404;
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
        
        <div class="changes-list">
            <h3>Summary of Changes:</h3>
            @foreach($changes as $field => $change)
                <div class="change-item">
                    <div class="field-name">{{ ucfirst(str_replace('_', ' ', $field)) }} changed:</div>
                    <div>
                        @if($field === 'logo_url')
                            <span class="old-value">old Logo</span>
                            <span class="arrow">→</span>
                            <span class="new-value">new Logo</span>
                        @else
                            <span class="old-value">{{ $change['old'] }}</span>
                            <span class="arrow">→</span>
                            <span class="new-value">{{ $change['new'] }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        
        @if(isset($changes['email']))
            <div class="warning-box">
                <h4 class="warning-title">Important:</h4>
                <p class="warning-text">Because your email address was changed, you will need to verify your new email the next time you log in.</p>
            </div>
            
            <a href="{{ url('/email/verify') }}" class="button">Verify Email</a>
        @endif
        
        <p>If you did not make this change, please contact our support team immediately.</p>
        
        <div class="footer">
            <p>Thanks,<br>
            <strong>{{ config('app.name') }}</strong></p>
        </div>
    </div>
</body>
</html> 