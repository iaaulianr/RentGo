<?php 
require_once 'config.php';

// Set session sebagai tamu
$_SESSION['role'] = 'guest';
$_SESSION['user_name'] = 'Tamu';

$filter_type = isset($_GET['type']) ? $_GET['type'] : 'all';

// Query Database (Sama persis dengan dashboard)
if($filter_type === 'all') {
    $stmt = $pdo->query("SELECT * FROM vehicles");
} else {
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE type = ?");
    $stmt->execute([$filter_type]);
}
$vehicles = $stmt->fetchAll();

$page_title = "Koleksi Kendaraan";
if($filter_type == 'mobil') $page_title = "Koleksi Mobil";
if($filter_type == 'motor') $page_title = "Koleksi Motor";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - RentGo</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-main: #050A14; --bg-card: #0f172a; --primary-blue: #3B82F6; 
            --text-light: #F8FAFC; --text-gray: #94A3B8; --border-color: rgba(255, 255, 255, 0.08);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body {
            background-color: var(--bg-main); color: var(--text-light); min-height: 100vh;
            background-image: radial-gradient(circle at 50% 0%, rgba(59, 130, 246, 0.08) 0%, transparent 50%);
        }

        /* NAVBAR TAMU (Hanya Logo & Tombol Kembali) */
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 20px 5%; max-width: 1400px; margin: 0 auto; }
        .logo-container { display: flex; align-items: center; gap: 8px; text-decoration: none; color: var(--text-light); font-weight: 700; font-size: 20px; }
        .logo-icon { width: 26px; height: 26px; background-color: var(--primary-blue); border-radius: 6px; display: flex; align-items: center; justify-content: center; }
        .logo-icon::after { content: ''; width: 10px; height: 10px; background-color: var(--text-light); border-radius: 3px; border-bottom-right-radius: 0; }
        .nav-btn { background: rgba(255, 255, 255, 0.05); border: 1px solid var(--border-color); color: var(--text-light); padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 500; transition: 0.3s; }
        .nav-btn:hover { background: rgba(255, 255, 255, 0.1); }

        .main-container { max-width: 1400px; margin: 0 auto; padding: 10px 5% 50px 5%; }

        /* HEADER */
        .dashboard-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 35px; flex-wrap: wrap; gap: 20px; }
        .header-text h1 { font-size: 28px; font-weight: 700; margin-bottom: 8px; }
        .header-text .sub-text { font-size: 13px; color: var(--text-gray); line-height: 1.5; }
        .header-text .guest-cta { margin-top: 10px; font-size: 13px; color: var(--text-gray); }
        .header-text .guest-cta a { color: var(--primary-blue); text-decoration: none; font-weight: 600; padding: 0 2px; }
        .header-text .guest-cta a:hover { text-decoration: underline; }

        /* FILTER PILLS */
        .filter-pills { display: flex; gap: 8px; background: rgba(255, 255, 255, 0.03); padding: 5px; border-radius: 20px; border: 1px solid var(--border-color); margin-bottom: 5px; }
        .pill { padding: 6px 18px; border-radius: 16px; font-size: 12px; font-weight: 500; text-decoration: none; color: var(--text-gray); transition: 0.3s; border: 1px solid transparent; }
        .pill:hover { color: var(--text-light); }
        .pill.active { background: rgba(59, 130, 246, 0.15); color: var(--primary-blue); border-color: rgba(59, 130, 246, 0.3); }

        /* GRID & CARD */
        .vehicle-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; }
        .vehicle-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 20px; display: flex; flex-direction: column; transition: 0.3s; }
        .vehicle-card:hover { transform: translateY(-5px); border-color: rgba(59, 130, 246, 0.4); box-shadow: 0 10px 25px rgba(0,0,0,0.4); }
        .card-badge { display: inline-block; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--border-color); padding: 4px 12px; border-radius: 6px; font-size: 10px; color: var(--text-gray); margin-bottom: 15px; align-self: flex-start; }
        
        .image-placeholder { width: 100%; height: 140px; background-color: rgba(255, 255, 255, 0.03); border: 1px dashed rgba(255, 255, 255, 0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; color: var(--text-gray); font-size: 12px; overflow: hidden; }
        
        .card-details h3 { font-size: 16px; font-weight: 600; margin-bottom: 5px; }
        .card-specs { font-size: 11px; color: var(--text-gray); margin-bottom: 15px; display: flex; align-items: center; gap: 6px; }
        .card-price { font-size: 16px; font-weight: 700; display: flex; align-items: baseline; gap: 5px; margin-top: auto; }
        .card-price span { font-size: 11px; font-weight: 400; color: var(--text-gray); }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo-container">
            <div class="logo-icon"></div>
            RentGo.
        </a>
        <a href="index.php" class="nav-btn">&larr; Kembali</a>
    </nav>

    <main class="main-container">
        <div class="dashboard-header">
            <div class="header-text">
                <h1><?= $page_title ?></h1>
                <p class="sub-text">Temukan kendaraan impianmu untuk perjalanan yang lebih mudah </p>
                <p class="guest-cta"><a href="login.php">Login / Daftar</a> untuk mulai menyewa kendaraan.</p>
            </div>

            <div class="filter-pills">
                <a href="guest.php?type=all" class="pill <?= $filter_type == 'all' ? 'active' : '' ?>">Semua</a>
                <a href="guest.php?type=motor" class="pill <?= $filter_type == 'motor' ? 'active' : '' ?>">Motor</a>
                <a href="guest.php?type=mobil" class="pill <?= $filter_type == 'mobil' ? 'active' : '' ?>">Mobil</a>
            </div>
        </div>

        <div class="vehicle-grid">
            <?php foreach($vehicles as $v): ?>
                <div class="vehicle-card">
                    <div class="card-badge"><?= $v['type'] == 'mobil' ? 'Mobil' : 'Motor' ?></div>
                    
                    <!-- Gambar Kendaraan dari Database -->
                    <div class="image-placeholder" style="<?= !empty($v['image']) ? 'border:none; padding:0;' : '' ?>">
                        <?php if(!empty($v['image']) && file_exists('uploads/' . $v['image'])): ?>
                            <img src="uploads/<?= $v['image'] ?>" alt="<?= htmlspecialchars($v['name']) ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                        <?php else: ?>
                            Foto <?= htmlspecialchars($v['name']) ?>
                        <?php endif; ?>
                    </div>

                    <div class="card-details">
                        <h3><?= htmlspecialchars($v['name']) ?></h3>
                        <div class="card-specs">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <?= htmlspecialchars($v['tahun']) ?> &bull; <?= htmlspecialchars($v['transmisi']) ?> &bull; <?= htmlspecialchars($v['lokasi']) ?>
                        </div>
                        <div class="card-price">Rp <?= number_format($v['price'], 0, ',', '.') ?> <span>/ hari</span></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

</body>
</html>