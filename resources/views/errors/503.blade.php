<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Maintenance Mode</title>
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

    /* Animated gradient background */
    .bg-gradient {
      position: fixed;
      inset: 0;
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

    /* Floating particles */
    .particles {
      position: fixed;
      inset: 0;
      z-index: 1;
      pointer-events: none;
    }

    .particle {
      position: absolute;
      width: 4px;
      height: 4px;
      background: rgba(99, 102, 241, 0.6);
      border-radius: 50%;
      animation: float linear infinite;
    }

    .particle:nth-child(1) { left: 10%; animation-duration: 12s; animation-delay: 0s; width: 3px; height: 3px; }
    .particle:nth-child(2) { left: 20%; animation-duration: 15s; animation-delay: 2s; width: 5px; height: 5px; background: rgba(139, 92, 246, 0.5); }
    .particle:nth-child(3) { left: 35%; animation-duration: 10s; animation-delay: 4s; }
    .particle:nth-child(4) { left: 50%; animation-duration: 18s; animation-delay: 1s; width: 6px; height: 6px; background: rgba(59, 130, 246, 0.4); }
    .particle:nth-child(5) { left: 65%; animation-duration: 14s; animation-delay: 3s; width: 3px; height: 3px; }
    .particle:nth-child(6) { left: 75%; animation-duration: 11s; animation-delay: 5s; background: rgba(168, 85, 247, 0.5); }
    .particle:nth-child(7) { left: 85%; animation-duration: 16s; animation-delay: 2.5s; width: 5px; height: 5px; }
    .particle:nth-child(8) { left: 45%; animation-duration: 13s; animation-delay: 6s; width: 4px; height: 4px; background: rgba(96, 165, 250, 0.5); }
    .particle:nth-child(9) { left: 5%; animation-duration: 17s; animation-delay: 1.5s; width: 3px; height: 3px; background: rgba(167, 139, 250, 0.6); }
    .particle:nth-child(10) { left: 92%; animation-duration: 12s; animation-delay: 4.5s; width: 4px; height: 4px; }

    @keyframes float {
      0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
      10% { opacity: 1; }
      90% { opacity: 1; }
      100% { transform: translateY(-100vh) rotate(720deg); opacity: 0; }
    }

    /* Glowing orbs */
    .orb {
      position: fixed;
      border-radius: 50%;
      filter: blur(80px);
      opacity: 0.3;
      z-index: 1;
      animation: orbPulse 8s ease-in-out infinite;
    }

    .orb-1 {
      width: 300px; height: 300px;
      background: #6366f1;
      top: -100px; left: -100px;
      animation-delay: 0s;
    }

    .orb-2 {
      width: 400px; height: 400px;
      background: #8b5cf6;
      bottom: -150px; right: -150px;
      animation-delay: 4s;
    }

    .orb-3 {
      width: 200px; height: 200px;
      background: #3b82f6;
      top: 50%; left: 60%;
      animation-delay: 2s;
    }

    @keyframes orbPulse {
      0%, 100% { transform: scale(1); opacity: 0.3; }
      50% { transform: scale(1.2); opacity: 0.5; }
    }

    /* Main content */
    .container {
      position: relative;
      z-index: 10;
      text-align: center;
      padding: 2rem;
      max-width: 600px;
    }

    /* Animated gear icon */
    .icon-wrapper {
      margin-bottom: 2rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }

    .gear-outer {
      width: 100px;
      height: 100px;
      animation: spinSlow 10s linear infinite;
    }

    .gear-inner {
      position: absolute;
      width: 50px;
      height: 50px;
      animation: spinSlow 7s linear infinite reverse;
    }

    .gear-outer svg, .gear-inner svg {
      width: 100%;
      height: 100%;
      fill: rgba(99, 102, 241, 0.8);
      filter: drop-shadow(0 0 20px rgba(99, 102, 241, 0.4));
    }

    @keyframes spinSlow {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }

    /* Text */
    .title {
      font-size: 2.5rem;
      font-weight: 800;
      color: #fff;
      margin-bottom: 0.75rem;
      letter-spacing: -0.02em;
      animation: fadeInUp 0.8s ease forwards;
      opacity: 0;
      animation-delay: 0.3s;
    }

    .subtitle {
      font-size: 1.1rem;
      color: rgba(255, 255, 255, 0.6);
      line-height: 1.7;
      margin-bottom: 2.5rem;
      animation: fadeInUp 0.8s ease forwards;
      opacity: 0;
      animation-delay: 0.5s;
    }

    /* Progress bar */
    .progress-wrapper {
      margin-bottom: 2rem;
      animation: fadeInUp 0.8s ease forwards;
      opacity: 0;
      animation-delay: 0.7s;
    }

    .progress-bar {
      width: 100%;
      max-width: 300px;
      height: 4px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 4px;
      margin: 0 auto;
      overflow: hidden;
      position: relative;
    }

    .progress-bar::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 60%;
      height: 100%;
      background: linear-gradient(90deg, transparent, #6366f1, #8b5cf6, transparent);
      border-radius: 4px;
      animation: progressSlide 2s ease-in-out infinite;
    }

    @keyframes progressSlide {
      0% { left: -60%; }
      100% { left: 100%; }
    }

    .progress-text {
      font-size: 0.8rem;
      color: rgba(255, 255, 255, 0.4);
      margin-top: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.1em;
    }

    /* Pulse dot */
    .status {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1.25rem;
      background: rgba(99, 102, 241, 0.1);
      border: 1px solid rgba(99, 102, 241, 0.2);
      border-radius: 2rem;
      animation: fadeInUp 0.8s ease forwards;
      opacity: 0;
      animation-delay: 0.9s;
    }

    .status-dot {
      width: 8px;
      height: 8px;
      background: #f59e0b;
      border-radius: 50%;
      animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.5; transform: scale(1.5); }
    }

    .status-text {
      font-size: 0.8rem;
      color: rgba(255, 255, 255, 0.7);
      font-weight: 500;
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Responsive */
    @media (max-width: 480px) {
      .title { font-size: 1.75rem; }
      .subtitle { font-size: 0.95rem; }
      .gear-outer { width: 70px; height: 70px; }
      .gear-inner { width: 35px; height: 35px; }
    }
  </style>
</head>
<body>
  <div class="bg-gradient"></div>

  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>

  <div class="particles">
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
  </div>

  <div class="container">
    <div class="icon-wrapper">
      <div class="gear-outer">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 15.5A3.5 3.5 0 0 1 8.5 12 3.5 3.5 0 0 1 12 8.5a3.5 3.5 0 0 1 3.5 3.5 3.5 3.5 0 0 1-3.5 3.5m7.43-2.53c.04-.32.07-.64.07-.97s-.03-.66-.07-1l2.11-1.63c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64L4.57 11c-.04.34-.07.67-.07 1s.03.65.07.97l-2.11 1.66c-.19.15-.25.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1.01c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.58 1.69-.98l2.49 1.01c.22.08.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.66Z"/>
        </svg>
      </div>
      <div class="gear-inner">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 15.5A3.5 3.5 0 0 1 8.5 12 3.5 3.5 0 0 1 12 8.5a3.5 3.5 0 0 1 3.5 3.5 3.5 3.5 0 0 1-3.5 3.5m7.43-2.53c.04-.32.07-.64.07-.97s-.03-.66-.07-1l2.11-1.63c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64L4.57 11c-.04.34-.07.67-.07 1s.03.65.07.97l-2.11 1.66c-.19.15-.25.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1.01c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.58 1.69-.98l2.49 1.01c.22.08.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.66Z"/>
        </svg>
      </div>
    </div>

    <h1 class="title">Sedang Maintenance</h1>
    <p class="subtitle">
      Kami sedang melakukan peningkatan sistem untuk pengalaman yang lebih baik.
      Silakan kembali beberapa saat lagi.
    </p>

    <div class="progress-wrapper">
      <div class="progress-bar"></div>
      <p class="progress-text">Sedang dalam proses...</p>
    </div>

    <div class="status">
      <span class="status-dot"></span>
      <span class="status-text">Maintenance Mode Aktif</span>
    </div>

    @auth
      <form action="{{ route('logout') }}" method="POST" style="margin-top: 1.5rem; animation: fadeInUp 0.8s ease forwards; opacity: 0; animation-delay: 1.1s;">
        @csrf
        <button type="submit" style="
          background: rgba(255,255,255,0.08);
          border: 1px solid rgba(255,255,255,0.15);
          color: rgba(255,255,255,0.8);
          padding: 0.6rem 1.5rem;
          border-radius: 2rem;
          font-size: 0.85rem;
          font-weight: 500;
          cursor: pointer;
          transition: all 0.2s ease;
          font-family: 'Inter', sans-serif;
        " onmouseover="this.style.background='rgba(255,255,255,0.15)';this.style.color='#fff'" onmouseout="this.style.background='rgba(255,255,255,0.08)';this.style.color='rgba(255,255,255,0.8)'">
          &#x2190; Logout &amp; Kembali ke Login
        </button>
      </form>
    @endauth
  </div>
</body>
</html>
