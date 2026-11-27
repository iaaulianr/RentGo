<?php 
require_once 'config.php';

// PROTEKSI: Hanya user login yang bisa akses
if(!is_login()) {
    header("Location: index.php");
    exit;
}

$user_name = $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - RentGo</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-main: #050A14; 
            --primary-blue: #3B82F6; 
            --text-light: #F8FAFC; 
            --text-gray: #94A3B8; 
            
            /* Gradasi khusus untuk Navbar & Floating Badges */
            --glass-gradient: linear-gradient(135deg, rgba(30, 41, 59, 0.85) 0%, rgba(15, 23, 42, 0.95) 100%);
            --glass-border: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { 
            background-color: var(--bg-main); 
            color: var(--text-light); 
            min-height: 100vh; 
            overflow: hidden; 
            background-image: radial-gradient(circle at 10% 50%, rgba(59, 130, 246, 0.1) 0%, transparent 50%); 
        }

        /* === NAVBAR KAPSUL === */
        .navbar-wrapper { padding: 20px 5% 0 5%; }
        .navbar { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            max-width: 1200px; 
            margin: 0 auto; 
            background: var(--glass-gradient);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: var(--glass-border);
            border-radius: 50px; 
            padding: 12px 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }
        
        .logo-container { display: flex; align-items: center; gap: 8px; text-decoration: none; color: var(--text-light); font-weight: 700; font-size: 20px; }
        .logo-icon { width: 26px; height: 26px; background-color: var(--primary-blue); border-radius: 6px; display: flex; align-items: center; justify-content: center; }
        .logo-icon::after { content: ''; width: 10px; height: 10px; background-color: var(--text-light); border-radius: 3px; border-bottom-right-radius: 0; }
        
        .nav-links { display: flex; gap: 25px; align-items: center; }
        .nav-links a { color: var(--text-gray); text-decoration: none; font-size: 13px; font-weight: 500; transition: 0.3s; }
        .nav-links a:hover, .nav-links a.active { color: var(--text-light); }
        
        .nav-actions { display: flex; gap: 15px; align-items: center; }
        .btn-logout { background: rgba(255, 255, 255, 0.05); border: var(--glass-border); color: var(--text-light); padding: 8px 18px; border-radius: 30px; text-decoration: none; font-size: 13px; transition: 0.3s; }
        .btn-logout:hover { background: rgba(255, 255, 255, 0.1); }

        /* === MAIN CONTENT === */
        .main-container { display: flex; justify-content: space-between; align-items: center; max-width: 1300px; margin: 0 auto; padding: 60px 5% 0 5%; height: calc(100vh - 100px); }
        
        /* KIRI: Teks, Pencarian & Statistik */
        .left-col { width: 50%; position: relative; z-index: 2; }
        
        /* BADGE DINAikkan (margin-top diubah dari 30px jadi 5px) */
        .badge-terverifikasi { 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            background: rgba(255, 255, 255, 0.03); 
            border: var(--glass-border); 
            padding: 6px 14px; 
            border-radius: 20px; 
            font-size: 11px; 
            color: var(--text-gray); 
            margin-top: 5px; 
            margin-bottom: 25px; 
        }
        .badge-terverifikasi::before { content: ''; width: 6px; height: 6px; background-color: #22C55E; border-radius: 50%; }
        
        .left-col h1 { font-size: 50px; line-height: 1.15; font-weight: 800; margin-bottom: 20px; letter-spacing: -1px; }
        .left-col h1 span { color: var(--primary-blue); }
        
        /* TEKS DIKECILKAN (font-size diubah dari 14px jadi 12px) */
        .left-col p { 
            color: var(--text-gray); 
            font-size: 12px; 
            line-height: 1.6; 
            max-width: 480px; 
            margin-bottom: 35px; 
        }

        /* Kotak Pencarian */
        .search-box { background: var(--glass-gradient); backdrop-filter: blur(10px); border: var(--glass-border); border-radius: 16px; padding: 10px; display: flex; gap: 10px; width: 100%; max-width: 550px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); margin-bottom: 35px; }
        .search-item { flex: 1; padding: 8px 15px; border-right: 1px solid rgba(255,255,255,0.1); }
        .search-item:last-child { border-right: none; }
        .search-item label { display: block; font-size: 10px; color: var(--text-gray); margin-bottom: 5px; font-weight: 600; }
        .search-item select { width: 100%; background: transparent; border: none; color: var(--text-light); font-size: 13px; font-weight: 600; outline: none; cursor: pointer; }
        .search-item select option { background: var(--bg-main); color: var(--text-light); }
        
        .btn-cari { background: var(--primary-blue); color: white; border: none; padding: 0 25px; border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer; transition: 0.3s; }
        .btn-cari:hover { background: #2563EB; }

        /* STATISTIK */
        .stats-container { display: flex; gap: 40px; }
        .stat-item h3 { font-size: 24px; font-weight: 700; margin-bottom: 2px; color: var(--text-light); }
        .stat-item p { font-size: 11px; color: var(--text-gray); }

        /* === KANAN: Ilustrasi & Floating Elements === */
        .right-col { width: 50%; position: relative; height: 100%; display: flex; align-items: center; justify-content: center; }
        
        .glowing-circle { position: absolute; width: 400px; height: 400px; border: 1px solid rgba(59, 130, 246, 0.1); border-radius: 50%; z-index: 1; }
        .glowing-circle::after { content: ''; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 250px; height: 250px; border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 50%; }
        
        .car-illustration { position: relative; z-index: 2; width: 100%; max-width: 350px; filter: drop-shadow(0 20px 30px rgba(0,0,0,0.6)); }

        /* Gaya Dasar Floating Badges */
        .float-badge { 
            position: absolute; 
            background: var(--glass-gradient); 
            backdrop-filter: blur(10px); 
            border: var(--glass-border); 
            padding: 10px 15px; 
            border-radius: 12px; 
            z-index: 4; 
            display: flex; 
            flex-direction: column;
            gap: 4px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.4); 
            width: max-content;
        }

        .float-badge h4 { font-size: 12px; font-weight: 600; color: var(--text-light); margin: 0; }
        .float-badge p { font-size: 10px; color: var(--text-gray); margin: 0; }
        .float-badge .stars { color: #FBBF24; font-size: 10px; letter-spacing: 1px; }

        /* Posisi 6 Floating Badges */
        .badge-1 { top: 5%; left: 5%; } 
        .badge-2 { top: 5%; right: 5%; } 
        .badge-3 { top: 40%; left: 0%; } 
        .badge-4 { top: 35%; right: 0%; } 
        .badge-5 { bottom: 15%; left: 10%; } 
        .badge-6 { bottom: 10%; right: 10%; } 
    </style>
</head>
<body>

    <div class="navbar-wrapper">
        <nav class="navbar">
            <a href="dashboard.php" class="logo-container">
                <div class="logo-icon"></div>
                RentGo.
            </a>
            <div class="nav-links">
                <a href="dashboard.php" class="active">Beranda</a>
                <a href="koleksi.php">Kendaraan</a>
                <a href="pesanan.php">Pesanan Saya</a>
                <a href="mitra.php">Jadi Mitra</a>
            </div>
            <div class="nav-actions">
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </nav>
    </div>

    <main class="main-container">
        <div class="left-col">
            <div class="badge-terverifikasi">Tersedia di 30+ kota Indonesia</div>
            
            <h1>Halo, <span><?= htmlspecialchars($user_name) ?>!</span> <br>Mau ke mana <br>hari ini?</h1>
            
            <p>Satu platform untuk mencari, membandingkan, dan memesan kendaraan dari ratusan rental terverifikasi. Harga transparan, stok real-time, tanpa chat satu per satu.</p>
            
            <!-- Form Pencarian -->
            <form action="koleksi.php" method="GET" class="search-box">
                <div class="search-item">
                    <label>LOKASI</label>
                    <select name="lokasi">
    <option value="">Semua Kota di Indonesia</option>
    <option value="Banda Aceh">Banda Aceh</option>
    <option value="Medan">Medan</option>
    <option value="Padang">Padang</option>
    <option value="Pekanbaru">Pekanbaru</option>
    <option value="Jambi">Jambi</option>
    <option value="Palembang">Palembang</option>
    <option value="Bengkulu">Bengkulu</option>
    <option value="Bandar Lampung">Bandar Lampung</option>
    <option value="Pangkal Pinang">Pangkal Pinang</option>
    <option value="Batam">Batam</option>
    <option value="Jakarta">Jakarta</option>
    <option value="Bogor">Bogor</option>
    <option value="Depok">Depok</option>
    <option value="Tangerang">Tangerang</option>
    <option value="Bekasi">Bekasi</option>
    <option value="Bandung">Bandung</option>
    <option value="Semarang">Semarang</option>
    <option value="Yogyakarta">Yogyakarta</option>
    <option value="Solo">Surakarta / Solo</option>
    <option value="Surabaya">Surabaya</option>
    <option value="Malang">Malang</option>
    <option value="Pontianak">Pontianak</option>
    <option value="Palangkaraya">Palangkaraya</option>
    <option value="Banjarmasin">Banjarmasin</option>
    <option value="Samarinda">Samarinda</option>
    <option value="Balikpapan">Balikpapan</option>
    <option value="Manado">Manado</option>
    <option value="Palu">Palu</option>
    <option value="Makassar">Makassar</option>
    <option value="Kendari">Kendari</option>
    <option value="Gorontalo">Gorontalo</option>
    <option value="Denpasar">Denpasar (Bali)</option>
    <option value="Mataram">Mataram (Lombok)</option>
    <option value="Kupang">Kupang</option>
    <option value="Ambon">Ambon</option>
    <option value="Ternate">Ternate</option>
    <option value="Jayapura">Jayapura</option>
    <option value="Manokwari">Manokwari</option>
</select>
                </div>
                <div class="search-item">
                    <label>JENIS</label>
                    <select name="jenis">
                        <option value="all">Motor & Mobil</option>
                        <option value="mobil">Mobil</option>
                        <option value="motor">Motor</option>
                    </select>
                </div>
                <button type="submit" class="btn-cari">Cari</button>
            </form>

            <!-- STATISTIK -->
            <div class="stats-container">
                </div>
            </div>
        </div>

        <!-- Kanan: Ilustrasi & 6 Floating Badges -->
        <div class="right-col">
            <div class="glowing-circle"></div>
            
            <!-- Ilustrasi Mobil & Motor -->
            <div class="car-illustration">
                <svg viewBox="0 0 500 250" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g transform="translate(50, 80)">
                        <path d="M20,80 C20,50 50,30 90,30 L190,30 C230,30 260,40 290,60 L370,60 C400,60 420,70 420,90 L420,100 C420,110 410,120 400,120 L40,120 C20,120 20,110 20,100 Z" fill="#1E3A8A" stroke="#3B82F6" stroke-width="2"/>
                        <path d="M90,30 L130,-5 L210,-5 L240,30 Z" fill="#0F172A"/>
                        <circle cx="100" cy="120" r="25" fill="#0F172A" stroke="#475569" stroke-width="4"/>
                        <circle cx="340" cy="120" r="25" fill="#0F172A" stroke="#475569" stroke-width="4"/>
                        <path d="M415,80 L430,80" stroke="#93C5FD" stroke-width="6" stroke-linecap="round"/>
                        <path d="M10,85 L25,85" stroke="#EF4444" stroke-width="6" stroke-linecap="round"/>
                    </g>
                    <g transform="translate(320, 140)">
                        <circle cx="20" cy="60" r="18" fill="#0F172A" stroke="#3B82F6" stroke-width="3"/>
                        <circle cx="110" cy="60" r="18" fill="#0F172A" stroke="#3B82F6" stroke-width="3"/>
                        <path d="M20,60 L40,20 L80,20 L110,60" stroke="#3B82F6" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M80,20 L100,0 L120,0" stroke="#3B82F6" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                    </g>
                </svg>
            </div>

            <!-- 6 FLOATING BADGES -->
            <div class="float-badge badge-1">
                <h4>Tersedia sekarang</h4>
                <p><strong>128 unit</strong> di sekitarmu</p>
            </div>
            
            <div class="float-badge badge-2">
                <div class="stars">★★★★★</div>
                <h4>Avanza Veloz - Jogja</h4>
            </div>
            
            <div class="float-badge badge-3">
                <h4>Mitra Terverifikasi</h4>
                <p>500+ rental terpercaya</p>
            </div>

            <div class="float-badge badge-4">
                <h4>Harga Transparan</h4>
                <p>Mulai Rp 75.000 / hari</p>
            </div>

            <div class="float-badge badge-5">
                <h4>Pesanan dikonfirmasi</h4>
                <p>Bukti transaksi digital</p>
            </div>

            <div class="float-badge badge-6">
                <h4>Bebas Repot</h4>
                <p>Kunci digital & serah terima mudah</p>
            </div>
        </div>
    </main>

</body>
</html>