<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: system-ui, sans-serif;
            background: #f9fafb;
            color: #111827;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            max-width: 500px;
        }
        h1 {
            font-size: 4rem;
            margin: 0;
            color: #2563eb;
        }
        p {
            margin: 1rem 0;
        }
        a, button {
            background: #2563eb;
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 0.5rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: 0.2s;
        }
        a:hover, button:hover {
            background: #1e40af;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>@yield('code')</h1>
        <h2>@yield('title')</h2>
        <p>@yield('message')</p>
        @yield('action')
    </div>
</body>
</html>
