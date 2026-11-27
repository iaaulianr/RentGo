<?php 
require_once 'config.php';

// PROTEKSI: Hanya user login yang bisa akses
if(!is_login()) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data pesanan user ini (KECUALI yang sudah completed)
$stmt = $pdo->prepare("
    SELECT b.*, v.name AS vehicle_name, v.type AS vehicle_type, v.icon AS vehicle_icon, v.image, v.transmisi, v.tahun 
    FROM bookings b 
    JOIN vehicles v ON b.vehicle_id = v.id 
    WHERE b.user_id = ? AND b.status != 'completed'
    ORDER BY b.id DESC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - RentGo</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-main: #050A14; --bg-card: #0f172a; --input-bg: #080f1c; --border: #1e293b;
            --primary: #3B82F6; --text-light: #F8FAFC; --text-muted: #94A3B8;
            --success: #22C55E; --warning: #F59E0B; --danger: #EF4444;
            
            --glass-gradient: linear-gradient(135deg, rgba(30, 41, 59, 0.85) 0%, rgba(15, 23, 42, 0.95) 100%);
            --glass-border: 1px solid rgba(255, 255, 255, 0.08);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-light); min-height: 100vh; background-image: radial-gradient(circle at 10% 50%, rgba(59, 130, 246, 0.05) 0%, transparent 50%); }
        
        .navbar-wrapper { padding: 20px 5% 0 5%; }
        .navbar { 
            display: flex; justify-content: space-between; align-items: center; max-width: 1300px; margin: 0 auto; 
            background: var(--glass-gradient); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            border: var(--glass-border); border-radius: 50px; padding: 12px 30px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }
        .logo-container { display: flex; align-items: center; gap: 8px; text-decoration: none; color: var(--text-light); font-weight: 700; font-size: 20px; }
        .logo-icon { width: 26px; height: 26px; background-color: var(--primary); border-radius: 6px; display: flex; align-items: center; justify-content: center; }
        .logo-icon::after { content: ''; width: 10px; height: 10px; background-color: var(--text-light); border-radius: 3px; border-bottom-right-radius: 0; }
        
        .nav-links { display: flex; gap: 25px; align-items: center; }
        .nav-links a { color: var(--text-muted); text-decoration: none; font-size: 13px; font-weight: 500; transition: 0.3s; }
        .nav-links a:hover, .nav-links a.active { color: var(--text-light); }
        
        .nav-actions { display: flex; gap: 15px; align-items: center; }
        .btn-logout { background: rgba(255, 255, 255, 0.05); border: var(--glass-border); color: var(--text-light); padding: 8px 18px; border-radius: 30px; text-decoration: none; font-size: 13px; transition: 0.3s; }
        .btn-logout:hover { background: rgba(255, 255, 255, 0.1); }

        .main-container { max-width: 1300px; margin: 0 auto; padding: 40px 5% 50px 5%; }

        .page-header { margin-bottom: 30px; }
        .page-header h1 { font-size: 28px; font-weight: 700; margin-bottom: 5px; }
        .page-header p { font-size: 13px; color: var(--text-muted); }

        .order-list { display: flex; flex-direction: column; gap: 20px; width: 100%; }
        
        .order-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; transition: 0.3s; width: 100%; }
        .order-card:hover { border-color: rgba(59, 130, 246, 0.4); box-shadow: 0 10px 25px rgba(0,0,0,0.4); }

        .order-header { display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; background: rgba(255, 255, 255, 0.02); border-bottom: 1px solid var(--border); }
        .invoice-id { font-size: 14px; font-weight: 600; color: var(--text-light); display: flex; align-items: center; gap: 8px;}
        .invoice-date { font-size: 12px; color: var(--text-muted); }
        
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .status-badge.paid { background: rgba(34, 197, 94, 0.15); color: var(--success); border: 1px solid rgba(34, 197, 94, 0.3); }
        .status-badge.pending { background: rgba(245, 158, 11, 0.15); color: var(--warning); border: 1px solid rgba(245, 158, 11, 0.3); }
        .status-badge.cancelled { background: rgba(239, 68, 68, 0.15); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.3); }

        .order-body { padding: 20px; display: flex; gap: 20px; align-items: flex-start; }
        
        .vehicle-img-box { width: 100px; height: 70px; background: var(--input-bg); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 32px; flex-shrink: 0; overflow: hidden; }
        
        .vehicle-info { flex: 1; }
        .vehicle-info h3 { font-size: 18px; font-weight: 600; margin-bottom: 4px; }
        .vehicle-type { font-size: 12px; color: var(--text-muted); margin-bottom: 15px; text-transform: capitalize; }
        
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        @media (max-width: 600px) { .detail-grid { grid-template-columns: 1fr; } }
        
        .detail-item label { display: block; font-size: 11px; color: var(--text-muted); margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;}
        .detail-item span { font-size: 13px; font-weight: 500; color: var(--text-light); }

        .order-footer { display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; border-top: 1px dashed var(--border); background: rgba(0,0,0,0.1); }
        .total-label { font-size: 13px; color: var(--text-muted); }
        .total-price { font-size: 18px; font-weight: 700; color: var(--primary); }
        
        .action-buttons { display: flex; gap: 10px; align-items: center; }
        
        .btn-action { background: var(--primary); color: white; padding: 8px 20px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600; transition: 0.3s; display: inline-block; }
        .btn-action:hover { background: #2563EB; }
        
        .btn-action.secondary { background: transparent; border: 1px solid var(--border); color: var(--text-light); }
        .btn-action.secondary:hover { background: rgba(255,255,255,0.05); }

        .empty-state { text-align: center; padding: 80px 20px; background: var(--bg-card); border: 1px dashed var(--border); border-radius: 16px; width: 100%; }
        .empty-icon { font-size: 60px; margin-bottom: 20px; opacity: 0.5; }
        .empty-state h2 { font-size: 20px; margin-bottom: 10px; }
        .empty-state p { font-size: 14px; color: var(--text-muted); margin-bottom: 25px; }
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
                <a href="dashboard.php">Beranda</a>
                <a href="koleksi.php">Kendaraan</a>
                <a href="pesanan.php" class="active">Pesanan Saya</a>
                <a href="mitra.php">Jadi Mitra</a>
            </div>
            <div class="nav-actions">
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </nav>
    </div>

    <main class="main-container">
        
        <div class="page-header">
            <h1>Pesanan Saya</h1>
            <p>Riwayat dan status penyewaan kendaraan Anda di RentGo.</p>
        </div>

        <div class="order-list">
            <?php if(empty($bookings)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h2>Belum Ada Pesanan Aktif</h2>
                    <p>Anda belum memiliki pesanan yang sedang berjalan. Yuk, cari kendaraan impianmu sekarang!</p>
                    <a href="koleksi.php" class="btn-action">Lihat Koleksi Kendaraan</a>
                </div>
            <?php else: ?>
                
                <?php foreach($bookings as $b): ?>
                    <div class="order-card">
                        
                        <!-- Header: ID & Status -->
                        <div class="order-header">
                            <div>
                                <div class="invoice-id">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                    #RG-<?= str_pad($b['id'], 5, '0', STR_PAD_LEFT) ?>
                                </div>
                                <div class="invoice-date">Dibuat: <?= date('d M Y, H:i', strtotime($b['created_at'])) ?> WIB</div>
                            </div>
                            
                            <!-- Status Badge -->
                            <?php if($b['status'] == 'paid'): ?>
                                <span class="status-badge paid">Lunas / Sedang Disewa</span>
                            <?php elseif($b['status'] == 'pending'): ?>
                                <span class="status-badge pending">Menunggu Pembayaran</span>
                            <?php else: ?>
                                <span class="status-badge cancelled">Dibatalkan</span>
                            <?php endif; ?>
                        </div>

                        <!-- Body: Detail Kendaraan & Waktu -->
                        <div class="order-body">
                            <div class="vehicle-img-box" style="padding: 0;">
                                <?php if(!empty($b['image']) && file_exists('uploads/' . $b['image'])): ?>
                                    <img src="uploads/<?= $b['image'] ?>" alt="<?= htmlspecialchars($b['vehicle_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <?= $b['vehicle_icon'] ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="vehicle-info">
                                <h3><?= htmlspecialchars($b['vehicle_name']) ?></h3>
                                <div class="vehicle-type"><?= $b['vehicle_type'] ?> &bull; <?= htmlspecialchars($b['transmisi']) ?> &bull; <?= htmlspecialchars($b['tahun']) ?></div>
                                
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <label>Tanggal Ambil</label>
                                        <span><?= date('d M Y', strtotime($b['tgl_ambil'])) ?> | <?= date('H:i', strtotime($b['jam_ambil'])) ?> WIB</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Durasi Sewa</label>
                                        <span><?= $b['duration'] ?> Hari</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Metode Pengambilan</label>
                                        <span><?= htmlspecialchars($b['lokasi_ambil']) ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Nama Penyewa</label>
                                        <span><?= htmlspecialchars($b['nama_penyewa']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer: Total Harga & Tombol Aksi -->
                        <div class="order-footer">
                            <div>
                                <span class="total-label">Total Tagihan:</span>
                                <span class="total-price">Rp<?= number_format($b['total_price'], 0, ',', '.') ?></span>
                            </div>
                            
                            <div class="action-buttons">
                                <?php if($b['status'] == 'pending'): ?>
                                    <a href="detailpesanan.php?id=<?= $b['id'] ?>" class="btn-action secondary">Lihat Detail</a>
                                    <a href="detailpesanan.php?id=<?= $b['id'] ?>" class="btn-action">Bayar Sekarang</a>
                                <?php else: ?>
                                    <a href="detailpesanan.php?id=<?= $b['id'] ?>" class="btn-action">Lihat Detail</a>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>

            <?php endif; ?>
        </div>

    </main>

</body>
</html>