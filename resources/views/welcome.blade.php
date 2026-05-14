<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'My Project') }}</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Open Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        .container {
            text-align: center;
            padding: 2rem;
        }
        h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.5);
            border-radius: 0.5rem;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn:hover {
            background: rgba(255,255,255,0.3);
            border-color: #fff;
        }
        .version {
            margin-top: 2rem;
            font-size: 0.85rem;
            opacity: 0.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ config('app.name', 'My Project') }}</h1>
        <p>Template starter siap digunakan.</p>
        <a href="{{ route('login') }}" class="btn">Masuk ke Admin Panel →</a>
        <div class="version">Laravel v{{ Illuminate\Foundation\Application::VERSION }} · PHP v{{ PHP_VERSION }}</div>
    </div>
</body>
</html>
