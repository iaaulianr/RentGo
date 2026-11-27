<?php 
require_once 'config.php'; 

if(isset($_SESSION['role']) && $_SESSION['role'] !== 'guest') {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentGo - Sewa Motor & Mobil</title>
    
    <!-- Memanggil Font Poppins dari Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-main: #050A14; 
            --bg-card: rgba(20, 30, 48, 0.7);
            --primary-blue: #3B82F6; 
            --text-light: #F8FAFC;
            --text-gray: #94A3B8;
            --border-color: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif; 
        }

        html, body {
            height: 100vh;
            overflow: hidden; 
            background-color: var(--bg-main);
            color: var(--text-light);
        }

        body {
            display: flex;
            flex-direction: column;
            background-image: radial-gradient(circle at 20% 50%, rgba(59, 130, 246, 0.1) 0%, transparent 50%);
        }

        /* === NAVBAR === */
        .navbar {
            display: flex;
            align-items: center;
            padding: 15px 50px 0 50px; 
            max-width: 1300px;
            width: 100%;
            margin: 0 auto;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 8px; 
            text-decoration: none;
            color: var(--text-light);
            font-weight: 700;
            font-size: 20px; 
        }

        .logo-icon {
            width: 26px; 
            height: 26px;
            background-color: var(--primary-blue);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-icon::after {
            content: '';
            width: 10px;
            height: 10px;
            background-color: var(--text-light);
            border-radius: 3px;
            border-bottom-right-radius: 0;
        }

        /* === MAIN CONTAINER === */
        .main-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center; 
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 20px 50px 40px 50px; 
            gap: 50px; 
        }

        /* === KOLOM KIRI === */
        .left-col {
            width: 55%;
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .left-col h1 {
            font-size: 52px; 
            line-height: 1.15;
            font-weight: 800;
            margin-bottom: 20px;
            letter-spacing: -1px;
        }

        .left-col h1 span {
            color: var(--primary-blue);
        }

        .left-col p {
            color: var(--text-gray);
            font-size: 14px; 
            line-height: 1.6;
            max-width: 460px;
            margin-bottom: 30px;
        }

        .features-tags {
            display: flex;
            gap: 12px;
            margin-bottom: 10px; 
            flex-wrap: wrap;
            position: relative;
            z-index: 3;
        }

        .tag {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px; 
            font-weight: 500;
            color: var(--text-light);
        }

        .tag.highlight {
            border-color: rgba(59, 130, 246, 0.3);
            background: rgba(59, 130, 246, 0.05);
        }

        /* === ILUSTRASI === */
        .illustration-area {
            position: relative;
            width: 100%;
            height: 180px; 
            display: flex;
            align-items: flex-end;
            justify-content: flex-start;
            margin-top: -15px; 
        }

        .glowing-arc {
            position: absolute;
            bottom: 5px; 
            left: -30px;
            width: 450px;
            height: 180px;
            border-radius: 50%;
            border-bottom: 2px solid rgba(59, 130, 246, 0.4);
            border-left: 1px solid rgba(59, 130, 246, 0.1);
            border-right: 1px solid rgba(59, 130, 246, 0.1);
            box-shadow: 0 20px 40px rgba(59, 130, 246, 0.15);
            z-index: 1;
            transform: rotate(-5deg);
        }

        .car-motor-illustration {
            position: relative;
            z-index: 3;
            filter: drop-shadow(0 20px 25px rgba(0,0,0,0.8));
            transform: translateY(-30px); 
        }

        /* === KOLOM KANAN === */
        .right-col {
            width: 40%;
            max-width: 400px; 
        }

        .action-card {
            background: var(--bg-card);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 35px; 
            width: 100%;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
        }

        .action-card h2 {
            font-size: 22px; 
            margin-bottom: 8px;
            font-weight: 600;
        }

        .action-card .subtitle {
            font-size: 14px; 
            font-weight: 400;
            color: var(--text-gray);
            margin-bottom: 25px;
        }

        /* Desain Tombol */
        .btn-login-reg {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 14px 20px; 
            border-radius: 12px;
            text-decoration: none;
            margin-bottom: 12px;
            transition: all 0.3s ease;
        }

        .btn-login-reg .btn-text {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .btn-login-reg .btn-text strong {
            font-size: 15px; 
            font-weight: 600;
            margin-bottom: 2px;
        }

        .btn-login-reg .btn-text small {
            font-size: 12px; 
            font-weight: 400;
            opacity: 0.8;
        }

        .btn-masuk {
            background: var(--primary-blue);
            color: white;
            border: 1px solid var(--primary-blue);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        .btn-masuk:hover { background: #2563EB; }
        .btn-masuk .icon-left {
            background: rgba(255,255,255,0.2);
            width: 32px; height: 32px; 
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            margin-right: 15px;
        }

        .btn-daftar {
            background: rgba(255, 255, 255, 0.03);
            color: var(--text-light);
            border: 1px solid var(--border-color);
        }
        .btn-daftar:hover { background: rgba(255, 255, 255, 0.08); }
        .btn-daftar .icon-left {
            background: rgba(255,255,255,0.05);
            width: 32px; height: 32px; 
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            margin-right: 15px;
        }

        .chevron {
            color: rgba(255,255,255,0.5);
            font-size: 18px;
            font-weight: bold;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: var(--text-gray);
            font-size: 12px;
            margin: 18px 0;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--border-color);
        }
        .divider::before { margin-right: 15px; }
        .divider::after { margin-left: 15px; }

        .btn-tamu {
            display: block;
            text-align: center;
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-light);
            padding: 14px; 
            border-radius: 12px;
            text-decoration: none;
            font-size: 14px; 
            font-weight: 500;
            transition: 0.3s;
            margin-bottom: 20px;
        }
        .btn-tamu:hover { background: rgba(255, 255, 255, 0.05); }

        .terms {
            text-align: center;
            font-size: 11px;
            font-weight: 300;
            color: var(--text-gray);
            line-height: 1.6;
        }
        .terms a { color: var(--text-light); text-decoration: none; font-weight: 400; }
        .terms a:hover { text-decoration: underline; }

        /* RESPONSIVE */
        @media (max-height: 800px) {
            .left-col h1 { font-size: 42px; }
            .action-card { padding: 25px; }
            .illustration-area { height: 150px; }
            .car-motor-illustration { transform: translateY(-15px) scale(0.9); transform-origin: left bottom; }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <a href="index.php" class="logo-container">
            <div class="logo-icon"></div>
            RentGo.
        </a>
    </nav>

    <main class="main-container">
        
        <!-- KOLOM KIRI -->
        <div class="left-col">
            <h1>Selamat datang di <br><span>RentGo.</span> Sewa motor <br>& mobil di seluruh <br>Indonesia.</h1>
            
            <p>Cari, bandingkan, dan pesan kendaraan dari banyak rental dalam satu platform. Masuk atau buat akun untuk mulai.</p>
            
            <div class="features-tags">
                <div class="tag">Mitra terverifikasi</div>
                <div class="tag">Harga transparan</div>
                <div class="tag highlight">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                    128 unit tersedia di sekitarmu
                </div>
            </div>

            <div class="illustration-area">
                <div class="glowing-arc"></div>
                
                <div class="car-motor-illustration">
                    <svg width="480" height="190" viewBox="0 0 480 190" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="carBody" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#3B82F6" />
                                <stop offset="50%" stop-color="#2563EB" />
                                <stop offset="100%" stop-color="#1E3A8A" />
                            </linearGradient>
                            <linearGradient id="carGlass" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#0F172A" stop-opacity="0.9" />
                                <stop offset="100%" stop-color="#1E293B" stop-opacity="0.7" />
                            </linearGradient>
                            <linearGradient id="motorBody" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#60A5FA" />
                                <stop offset="100%" stop-color="#1D4ED8" />
                            </linearGradient>
                            <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="12" stdDeviation="10" flood-color="#000" flood-opacity="0.7"/>
                            </filter>
                        </defs>

                        <!-- Mobil -->
                        <g filter="url(#shadow)" transform="translate(10, 30)">
                            <ellipse cx="200" cy="140" rx="180" ry="12" fill="#000" opacity="0.5" filter="blur(4px)"/>
                            <path d="M20,110 C20,80 50,60 90,60 L190,60 C230,60 260,70 290,90 L370,90 C400,90 420,100 420,120 L420,130 C420,140 410,150 400,150 L40,150 C20,150 20,140 20,130 Z" fill="url(#carBody)"/>
                            <path d="M90,60 L130,25 L210,25 L240,60 Z" fill="url(#carGlass)"/>
                            <path d="M415,110 L425,110" stroke="#93C5FD" stroke-width="4" stroke-linecap="round"/>
                            <path d="M15,115 L25,115" stroke="#EF4444" stroke-width="4" stroke-linecap="round"/>
                            <g transform="translate(100, 150)">
                                <circle cx="0" cy="0" r="26" fill="#0F172A" />
                                <circle cx="0" cy="0" r="18" fill="#334155" />
                                <circle cx="0" cy="0" r="10" fill="#64748B" />
                            </g>
                            <g transform="translate(340, 150)">
                                <circle cx="0" cy="0" r="26" fill="#0F172A" />
                                <circle cx="0" cy="0" r="18" fill="#334155" />
                                <circle cx="0" cy="0" r="10" fill="#64748B" />
                            </g>
                            <path d="M20,110 C20,80 50,60 90,60 L190,60 C230,60 260,70 290,90" stroke="#93C5FD" stroke-width="2" fill="none" opacity="0.6"/>
                        </g>

                        <!-- Motor -->
                        <g filter="url(#shadow)" transform="translate(320, 50)">
                            <ellipse cx="70" cy="140" rx="80" ry="8" fill="#000" opacity="0.5" filter="blur(3px)"/>
                            <circle cx="20" cy="125" r="22" fill="#0F172A" stroke="#475569" stroke-width="4"/>
                            <circle cx="20" cy="125" r="10" fill="#3B82F6"/>
                            <circle cx="130" cy="125" r="22" fill="#0F172A" stroke="#475569" stroke-width="4"/>
                            <circle cx="130" cy="125" r="10" fill="#3B82F6"/>
                            <path d="M20,125 L45,65 L90,65 L130,125" stroke="url(#motorBody)" stroke-width="10" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                            <path d="M90,65 L115,35 L140,35" stroke="url(#motorBody)" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                            <path d="M130,125 L140,95" stroke="url(#motorBody)" stroke-width="6" stroke-linecap="round"/>
                            <path d="M40,105 L20,110" stroke="#94A3B8" stroke-width="5" stroke-linecap="round"/>
                            <path d="M115,35 L125,25" stroke="#94A3B8" stroke-width="3" stroke-linecap="round"/>
                        </g>
                    </svg>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN -->
        <div class="right-col">
            <div class="action-card">
                <h2>Mau mulai dari mana?</h2>
                <p class="subtitle">Pilih salah satu untuk melanjutkan ke RentGo.</p>
                
                <!-- Navigasi ke Login -->
                <a href="login.php" class="btn-login-reg btn-masuk">
                    <div class="icon-left">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                    </div>
                    <div class="btn-text">
                        <strong>Masuk</strong>
                        <small>Saya sudah punya akun</small>
                    </div>
                    <div class="chevron">&rsaquo;</div>
                </a>

                <!-- Navigasi ke Register -->
                <a href="register.php" class="btn-login-reg btn-daftar">
                    <div class="icon-left">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                    </div>
                    <div class="btn-text">
                        <strong>Daftar</strong>
                        <small>Buat akun baru, gratis</small>
                    </div>
                    <div class="chevron">&rsaquo;</div>
                </a>

                <div class="divider">atau</div>

                <!-- Navigasi ke Guest (Telah Diperbaiki) -->
                <a href="guest.php" class="btn-tamu">Jelajahi sebagai tamu</a>

                <p class="terms">
                    Dengan melanjutkan, kamu menyetujui <a href="#">syarat layanan</a> dan <a href="#">kebijakan privasi</a> RentGo.
                </p>
            </div>
        </div>

    </main>

</body>
</html>