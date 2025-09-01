<?php
http_response_code(503);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>We’ll be back soon</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            margin: 0;
            font-family: system-ui, sans-serif;
            background: #f9fafb;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: #374151;
        }
        .container {
            text-align: center;
            max-width: 600px;
            padding: 2rem;
        }
        h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #111827;
        }
        p {
            margin-bottom: 1.5rem;
            color: #4b5563;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #2563eb;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
        }
        .btn:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚧</h1>
        <h1>We’re down for maintenance</h1>
        <p>Our site is getting a quick update. Please check back soon, thanks for your patience.</p>
        <a href="javascript:location.reload()" class="btn">Try Again</a>
    </div>
</body>
</html>

