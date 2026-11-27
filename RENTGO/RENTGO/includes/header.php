<?php require_once __DIR__ . '/../config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentGo - Rental Mobil & Motor</title>
    <style>
        /* CSS LANGSUNG DI SINI */
        :root {
            --navy: #142B4A;
            --primary-blue: #2878D0;
            --light-blue: #65B5F5;
            --off-white: #F4F7FB;
            --white: #FFFFFF;
            --bg-dark: #0a1525; 
            --text-main: #F4F7FB;
            --text-muted: #8ba3c7;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--bg-dark); color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; width: 100%; }
        
        /* NAVBAR */
        nav { background-color: rgba(20, 43, 74, 0.9); backdrop-filter: blur(10px); padding: 15px 0; border-bottom: 1px solid rgba(101, 181, 245, 0.2); position: sticky; top: 0; z-index: 100; }
        .nav-content { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 24px; font-weight: bold; color: var(--light-blue); text-decoration: none; }
        .nav-links { display: flex; gap: 20px; align-items: center; }
        .nav-links a { color: var(--text-main); text-decoration: none; font-weight: 500; transition: 0.3s; }
        .nav-links a:hover { color: var(--light-blue); }

        /* BUTTONS */
        .btn { padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-weight: bold; transition: 0.3s; text-decoration: none; display: inline-block; text-align: center; }
        .btn-primary { background-color: var(--primary-blue); color: var(--white); box-shadow: 0 4px 15px rgba(40, 120, 208, 0.4); }
        .btn-primary:hover { background-color: var(--light-blue); color: var(--navy); }
        .btn-outline { background: transparent; border: 1px solid var(--light-blue); color: var(--light-blue); }
        .btn-outline:hover { background: var(--light-blue); color: var(--navy); }
        .btn-disabled { background: #555; color: #aaa; cursor: not-allowed; box-shadow: none; }

        /* CARDS & FORMS */
        .card { background: var(--navy); border: 1px solid rgba(101, 181, 245, 0.3); border-radius: 16px; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: var(--text-muted); font-size: 14px; }
        .form-control { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(101, 181, 245, 0.3); background: var(--bg-dark); color: var(--white); outline: none; }
        .form-control:focus { border-color: var(--light-blue); }

        /* HERO SECTION */
        .hero { text-align: center; padding: 100px 20px; background: radial-gradient(circle at center, rgba(40,120,208,0.2) 0%, transparent 70%); }
        .hero h1 { font-size: 48px; margin-bottom: 20px; }
        .hero h1 span { color: var(--light-blue); }
        .hero p { color: var(--text-muted); margin-bottom: 40px; max-width: 600px; margin-left: auto; margin-right: auto; }

        /* GRID & KARTU KENDARAAN */
        .filter-box { display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap; }
        .vehicle-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .vehicle-card { background: var(--navy); border-radius: 16px; overflow: hidden; border: 1px solid transparent; transition: 0.3s; }
        .vehicle-card:hover { border-color: var(--light-blue); transform: translateY(-5px); }
        .vehicle-img { height: 150px; background: var(--bg-dark); display: flex; align-items: center; justify-content: center; font-size: 60px; }
        .vehicle-info { padding: 15px; }
        .vehicle-price { color: var(--light-blue); font-weight: bold; font-size: 18px; margin: 10px 0; }

        /* ALERTS */
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .alert-danger { background: rgba(217, 83, 79, 0.2); border: 1px solid #d9534f; color: #ffb3b3; }
        .alert-success { background: rgba(40, 208, 100, 0.2); border: 1px solid #28d064; color: #b3ffcc; }
    </style>
</head>
<body>
    <nav>
        <div class="container nav-content">
            <a href="index.php" class="logo">RentGo.</a>
            <div class="nav-links">
                <?php if(!isset($_SESSION['role'])): ?>
                    <a href="index.php">Beranda</a>
                    <a href="login.php" class="btn btn-primary">Login / Daftar</a>
                <?php elseif($_SESSION['role'] === 'guest'): ?>
                    <a href="dashboard.php">Koleksi (Tamu)</a>
                    <a href="logout.php" class="btn btn-outline">Keluar Mode Tamu</a>
                <?php else: ?>
                    <a href="dashboard.php">Beranda</a>
                    <a href="mitra.php">Jadi Mitra</a>
                    <a href="logout.php" class="btn btn-outline">Logout (<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>)</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="container" style="flex: 1; padding-top: 40px; margin-bottom: 40px;">