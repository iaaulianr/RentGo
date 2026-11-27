<?php 
require_once 'config.php';

if(!is_login()) {
    header("Location: index.php");
    exit;
}

// Ambil parameter dari form pencarian
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : 'all';
$lokasi = isset($_GET['lokasi']) ? $_GET['lokasi'] : '';

// Query Database
$query = "SELECT * FROM vehicles WHERE 1=1";
$params = [];

if($jenis !== 'all') {
    $query .= " AND type = ?";
    $params[] = $jenis;
}

if(!empty($lokasi)) {
    $query .= " AND lokasi = ?";
    $params[] = $lokasi;
}

// Agar mobil yang baru ditambah muncul paling atas
$query .= " ORDER BY id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$vehicles = $stmt->fetchAll();

$page_title = "Koleksi Kendaraan";
if($jenis == 'mobil') $page_title = "Koleksi Mobil";
if($jenis == 'motor') $page_title = "Koleksi Motor";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - RentGo</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-main: #050A14; --primary-blue: #3B82F6; 
            --text-light: #F8FAFC; --text-gray: #94A3B8; --bg-card: #0f172a;
            --border-color: rgba(255, 255, 255, 0.08);
            --glass-gradient: linear-gradient(135deg, rgba(30, 41, 59, 0.85) 0%, rgba(15, 23, 42, 0.95) 100%);
            --glass-border: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-light); min-height: 100vh; background-image: radial-gradient(circle at 10% 50%, rgba(59, 130, 246, 0.05) 0%, transparent 50%); }

        /* === NAVBAR KAPSUL === */
        .navbar-wrapper { padding: 20px 5% 0 5%; }
        .navbar { 
            display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; 
            background: var(--glass-gradient); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            border: var(--glass-border); border-radius: 50px; padding: 12px 30px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
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

        /* === CONTAINER UTAMA === */
        .main-container { max-width: 1300px; margin: 0 auto; padding: 40px 5% 50px 5%; }

        /* === HEADER SECTION === */
        .header-section { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; flex-wrap: wrap; gap: 20px; }
        .header-text h1 { font-size: 28px; font-weight: 700; margin-bottom: 5px; }
        .header-text p { font-size: 13px; color: var(--text-gray); }
        .header-text p strong { color: var(--primary-blue); }

        /* Filter Pills */
        .filter-pills { display: flex; gap: 5px; background: var(--glass-gradient); padding: 5px; border-radius: 20px; border: var(--glass-border); }
        .pill { padding: 6px 18px; border-radius: 16px; font-size: 12px; font-weight: 500; text-decoration: none; color: var(--text-gray); transition: 0.3s; border: 1px solid transparent; }
        .pill:hover { color: var(--text-light); }
        .pill.active { background: rgba(59, 130, 246, 0.2); color: var(--primary-blue); border-color: rgba(59, 130, 246, 0.3); }

        /* === GRID & CARD === */
        .vehicle-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; }
        .vehicle-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 20px; display: flex; flex-direction: column; transition: 0.3s; }
        .vehicle-card:hover { transform: translateY(-5px); border-color: rgba(59, 130, 246, 0.4); box-shadow: 0 10px 25px rgba(0,0,0,0.4); }
        
        .card-badge { display: inline-block; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--border-color); padding: 4px 12px; border-radius: 6px; font-size: 10px; color: var(--text-gray); margin-bottom: 15px; align-self: flex-start; }
        
        /* === PLACEHOLDER GAMBAR & EFEK LEASED === */
        .image-placeholder { 
            width: 100%; height: 140px; background-color: rgba(255, 255, 255, 0.03); 
            border: 1px dashed rgba(255, 255, 255, 0.2); border-radius: 10px; 
            display: flex; align-items: center; justify-content: center; 
            margin-bottom: 20px; color: var(--text-gray); font-size: 12px;
            position: relative; overflow: hidden;
        }

        /* Overlay Leased (Efek Kaca/Gelap) */
        .overlay-leased {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(3px);
            display: flex; align-items: center; justify-content: center;
            z-index: 10;
        }
        .overlay-leased span {
            font-size: 20px; font-weight: 700; color: white;
            text-transform: uppercase; letter-spacing: 2px;
            border: 2px solid white; padding: 5px 15px; border-radius: 8px;
            transform: rotate(-10deg);
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
            background: rgba(0,0,0,0.3);
        }
        
        .card-details h3 { font-size: 16px; font-weight: 600; margin-bottom: 5px; }
        .card-specs { font-size: 11px; color: var(--text-gray); margin-bottom: 15px; display: flex; align-items: center; gap: 6px; }
        .card-price { font-size: 16px; font-weight: 700; display: flex; align-items: baseline; gap: 5px; margin-top: auto; }
        .card-price span { font-size: 11px; font-weight: 400; color: var(--text-gray); }

        /* Tombol Sewa */
        .btn-sewa { display: block; text-align: center; background: var(--primary-blue); color: white; padding: 10px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600; margin-top: 15px; transition: 0.3s; border: none; cursor: pointer; width: 100%;}
        .btn-sewa:hover { background: #2563EB; transform: translateY(-2px); }
        
        /* Tombol Disabled (Jika Leased) */
        .btn-sewa.disabled { background: rgba(255, 255, 255, 0.05); color: var(--text-gray); cursor: not-allowed; border: 1px solid var(--border-color); }
        .btn-sewa.disabled:hover { background: rgba(255, 255, 255, 0.05); transform: none; }
    </style>
</head>
<body>

    <!-- Navbar -->
    <div class="navbar-wrapper">
        <nav class="navbar">
            <a href="dashboard.php" class="logo-container">
                <div class="logo-icon"></div>
                RentGo.
            </a>
            <div class="nav-links">
                <a href="dashboard.php">Beranda</a>
                <a href="koleksi.php" class="active">Kendaraan</a>
                <a href="pesanan.php">Pesanan Saya</a>
                <a href="mitra.php">Jadi Mitra</a>
            </div>
            <div class="nav-actions">
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </nav>
    </div>

    <main class="main-container">
        
        <!-- Header Section -->
        <div class="header-section">
            <div class="header-text">
                <h1><?= $page_title ?></h1>
                <?php if(!empty($lokasi)): ?>
                    <p>Menampilkan kendaraan di area <strong><?= htmlspecialchars($lokasi) ?></strong></p>
                <?php else: ?>
                    <p>Menampilkan kendaraan di <strong>Seluruh Indonesia</strong></p>
                <?php endif; ?>
            </div>

            <!-- Filter Pills (Kanan) -->
            <div class="filter-pills">
                <a href="koleksi.php?jenis=all&lokasi=<?= $lokasi ?>" class="pill <?= $jenis == 'all' ? 'active' : '' ?>">Semua</a>
                <a href="koleksi.php?jenis=motor&lokasi=<?= $lokasi ?>" class="pill <?= $jenis == 'motor' ? 'active' : '' ?>">Motor</a>
                <a href="koleksi.php?jenis=mobil&lokasi=<?= $lokasi ?>" class="pill <?= $jenis == 'mobil' ? 'active' : '' ?>">Mobil</a>
            </div>
        </div>

        <!-- Grid Kendaraan -->
        <div class="vehicle-grid">
            <?php if(empty($vehicles)): ?>
                <p style="grid-column: 1/-1; text-align: center; color: var(--text-gray); padding: 40px;">
                    Kendaraan tidak tersedia untuk kategori dan lokasi ini.
                </p>
            <?php else: ?>
                <?php foreach($vehicles as $v): ?>
                    <div class="vehicle-card">
                        <div class="card-badge"><?= $v['type'] == 'mobil' ? 'Mobil' : 'Motor' ?></div>
                        
                        <!-- Gambar Kendaraan dari Database -->
                        <div class="image-placeholder" style="<?= !empty($v['image']) ? 'border:none; padding:0;' : '' ?>">
                            <?php if(!empty($v['image']) && file_exists('uploads/' . $v['image'])): ?>
                                <img src="uploads/<?= $v['image'] ?>" alt="<?= htmlspecialchars($v['name']) ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                            <?php else: ?>
                                <!-- Fallback ke Icon jika gambar tidak ada -->
                                <span style="font-size: 40px;"><?= $v['icon'] ?></span>
                            <?php endif; ?>

                            <?php if($v['status'] == 'disewa'): ?>
                                <div class="overlay-leased">
                                    <span>Leased</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Detail Kendaraan -->
                        <div class="card-details">
                            <h3><?= htmlspecialchars($v['name']) ?></h3>
                            <div class="card-specs">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                <?= htmlspecialchars($v['tahun']) ?> &bull; <?= htmlspecialchars($v['transmisi']) ?> &bull; <?= htmlspecialchars($v['lokasi']) ?>
                            </div>
                            <div class="card-price">Rp <?= number_format($v['price'], 0, ',', '.') ?> <span>/ hari</span></div>
                        </div>

                        <!-- Tombol Aksi -->
                        <?php if($v['status'] == 'tersedia'): ?>
                            <a href="checkout.php?id=<?= $v['id'] ?>" class="btn-sewa">Sewa Sekarang</a>
                        <?php else: ?>
                            <button class="btn-sewa disabled" disabled>Tidak Tersedia</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>