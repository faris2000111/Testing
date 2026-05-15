<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Error')</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #0f0f1a;
      overflow: hidden;
      position: relative;
    }
    .bg-gradient {
      position: fixed; inset: 0;
      background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 25%, #16213e 50%, #0f3460 75%, #0f0f1a 100%);
      background-size: 400% 400%;
      animation: gradientShift 15s ease infinite;
      z-index: 0;
    }
    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    .orb {
      position: fixed; border-radius: 50%; filter: blur(80px); opacity: 0.3; z-index: 1;
      animation: orbPulse 8s ease-in-out infinite;
    }
    .orb-1 { width: 300px; height: 300px; background: @yield('orb-color', '#6366f1'); top: -100px; left: -100px; }
    .orb-2 { width: 400px; height: 400px; background: @yield('orb-color-2', '#8b5cf6'); bottom: -150px; right: -150px; animation-delay: 4s; }
    @keyframes orbPulse {
      0%, 100% { transform: scale(1); opacity: 0.3; }
      50% { transform: scale(1.2); opacity: 0.5; }
    }
    .particles { position: fixed; inset: 0; z-index: 1; pointer-events: none; }
    .particle {
      position: absolute; width: 4px; height: 4px;
      background: @yield('particle-color', 'rgba(99, 102, 241, 0.6)');
      border-radius: 50%; animation: float linear infinite;
    }
    .particle:nth-child(1) { left: 10%; animation-duration: 12s; }
    .particle:nth-child(2) { left: 25%; animation-duration: 15s; animation-delay: 2s; width: 5px; height: 5px; }
    .particle:nth-child(3) { left: 40%; animation-duration: 10s; animation-delay: 4s; }
    .particle:nth-child(4) { left: 55%; animation-duration: 18s; animation-delay: 1s; width: 6px; height: 6px; }
    .particle:nth-child(5) { left: 70%; animation-duration: 14s; animation-delay: 3s; }
    .particle:nth-child(6) { left: 85%; animation-duration: 11s; animation-delay: 5s; }
    @keyframes float {
      0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
      10% { opacity: 1; }
      90% { opacity: 1; }
      100% { transform: translateY(-100vh) rotate(720deg); opacity: 0; }
    }
    .container {
      position: relative; z-index: 10; text-align: center; padding: 2rem; max-width: 600px;
    }
    .error-code {
      font-size: 8rem; font-weight: 800; line-height: 1;
      background: linear-gradient(135deg, @yield('code-gradient-from', '#6366f1'), @yield('code-gradient-to', '#a855f7'));
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: fadeInUp 0.6s ease forwards; opacity: 0;
      margin-bottom: 0.5rem;
    }
    .title {
      font-size: 1.75rem; font-weight: 700; color: #fff;
      margin-bottom: 0.75rem;
      animation: fadeInUp 0.8s ease forwards; opacity: 0; animation-delay: 0.2s;
    }
    .subtitle {
      font-size: 1rem; color: rgba(255,255,255,0.6); line-height: 1.7;
      margin-bottom: 2rem;
      animation: fadeInUp 0.8s ease forwards; opacity: 0; animation-delay: 0.4s;
    }
    .btn-back {
      display: inline-flex; align-items: center; gap: 0.5rem;
      padding: 0.75rem 1.75rem; border-radius: 2rem;
      background: linear-gradient(135deg, @yield('code-gradient-from', '#6366f1'), @yield('code-gradient-to', '#a855f7'));
      color: #fff; text-decoration: none; font-weight: 600; font-size: 0.9rem;
      transition: all 0.3s ease; box-shadow: 0 4px 20px rgba(99, 102, 241, 0.3);
      animation: fadeInUp 0.8s ease forwards; opacity: 0; animation-delay: 0.6s;
    }
    .btn-back:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(99, 102, 241, 0.5); }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    @media (max-width: 480px) {
      .error-code { font-size: 5rem; }
      .title { font-size: 1.3rem; }
    }
  </style>
</head>
<body>
  <div class="bg-gradient"></div>
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="particles">
    <div class="particle"></div><div class="particle"></div><div class="particle"></div>
    <div class="particle"></div><div class="particle"></div><div class="particle"></div>
  </div>
  <div class="container">
    @yield('content')
  </div>
</body>
</html>
