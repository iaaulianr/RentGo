<?php 
require_once 'config.php';

// PROTEKSI: Hanya user login yang bisa akses
if(!is_login()) {
    header("Location: index.php");
    exit;
}

// Ambil ID pesanan dari URL
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Ambil data pesanan berdasarkan ID dan pastikan itu milik user yang sedang login
$stmt = $pdo->prepare("
    SELECT b.*, v.name AS vehicle_name, v.type AS vehicle_type, v.icon AS vehicle_icon, v.price AS vehicle_price, v.image, v.transmisi, v.tahun, v.kontak_mitra 
    FROM bookings b 
    JOIN vehicles v ON b.vehicle_id = v.id 
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $user_id]);
$b = $stmt->fetch();

if(!$b) {
    header("Location: pesanan.php");
    exit;
}

// Hitung rincian harga
$harga_sewa = $b['vehicle_price'] * $b['duration'];
$biaya_layanan = 5000;
$deposit = 100000;

// Tentukan warna & teks status
$status_class = 'pending';
$status_text = 'Menunggu Pembayaran';
if($b['status'] == 'paid') {
    $status_class = 'paid';
    $status_text = 'Lunas / Dibayar';
} elseif($b['status'] == 'completed') {
    $status_class = 'paid'; // Warna hijau
    $status_text = 'Selesai';
} elseif($b['status'] == 'cancelled') {
    $status_class = 'cancelled';
    $status_text = 'Dibatalkan';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #RG-<?= str_pad($b['id'], 5, '0', STR_PAD_LEFT) ?> - RentGo</title>
    
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
        body { background-color: var(--bg-main); color: var(--text-light); min-height: 100vh; padding: 30px 20px; display: flex; flex-direction: column; align-items: center; background-image: radial-gradient(circle at 50% 0%, rgba(59, 130, 246, 0.05) 0%, transparent 50%); }
        
        .container { width: 100%; max-width: 700px; }

        .top-nav { margin-bottom: 25px; }
        .btn-back { color: var(--text-muted); text-decoration: none; font-size: 13px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; background: rgba(255, 255, 255, 0.03); padding: 8px 15px; border-radius: 8px; border: 1px solid var(--border); }
        .btn-back:hover { color: var(--text-light); background: rgba(255, 255, 255, 0.08); }

        .receipt-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; position: relative; }

        .receipt-header { padding: 25px; background: rgba(255, 255, 255, 0.02); border-bottom: 1px dashed var(--border); text-align: center; }
        .receipt-header h2 { font-size: 20px; font-weight: 700; margin-bottom: 5px; }
        .receipt-header p { font-size: 13px; color: var(--text-muted); margin-bottom: 15px; }
        
        .status-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .status-badge.paid { background: rgba(34, 197, 94, 0.15); color: var(--success); border: 1px solid rgba(34, 197, 94, 0.3); }
        .status-badge.pending { background: rgba(245, 158, 11, 0.15); color: var(--warning); border: 1px solid rgba(245, 158, 11, 0.3); }
        .status-badge.cancelled { background: rgba(239, 68, 68, 0.15); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.3); }

        .receipt-body { padding: 25px; }

        .section-title { font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        .section-title::after { content: ''; flex: 1; height: 1px; background: var(--border); }

        .vehicle-box { display: flex; align-items: center; gap: 15px; background: var(--input-bg); border: 1px solid var(--border); padding: 15px; border-radius: 12px; margin-bottom: 20px; }
        .vehicle-img { width: 70px; height: 50px; background: var(--bg-card); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 28px; overflow: hidden; }
        .vehicle-name { font-size: 16px; font-weight: 600; margin-bottom: 2px; }
        .vehicle-type { font-size: 12px; color: var(--text-muted); text-transform: capitalize; }

        /* === KOTAK KONTAK (BANTUAN) === */
        .contact-section {
            background: rgba(59, 130, 246, 0.05);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-left: 4px solid var(--primary);
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 25px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        @media (max-width: 500px) { .contact-section { grid-template-columns: 1fr; } }

        .contact-item label { display: block; font-size: 11px; color: var(--text-muted); margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;}
        .contact-item span { font-size: 13px; font-weight: 600; color: var(--text-light); display: flex; align-items: center; gap: 6px; }
        .contact-item span svg { color: var(--primary); }

        .data-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; }
        @media (max-width: 500px) { .data-grid { grid-template-columns: 1fr; } }
        
        .data-item label { display: block; font-size: 11px; color: var(--text-muted); margin-bottom: 4px; }
        .data-item span { font-size: 13px; font-weight: 500; color: var(--text-light); word-wrap: break-word; }

        .price-list { margin-bottom: 20px; }
        .price-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 10px; color: var(--text-muted); }
        .price-row span:last-child { color: var(--text-light); font-weight: 500; }
        
        .price-total { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 15px; margin-top: 15px; font-size: 16px; font-weight: 700; color: var(--text-light); }
        .price-total span:last-child { color: var(--primary); font-size: 20px; }

        .receipt-footer { padding: 20px 25px; background: rgba(0, 0, 0, 0.2); border-top: 1px dashed var(--border); display: flex; gap: 15px; justify-content: flex-end; }

        .btn { padding: 12px 20px; border-radius: 10px; border: none; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.3s; text-align: center; text-decoration: none; display: inline-block; }
        .btn-primary { background: var(--primary); color: white; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3); }
        .btn-primary:hover { background: #2563EB; }
        .btn-secondary { background: transparent; border: 1px solid var(--border); color: var(--text-light); }
        .btn-secondary:hover { background: rgba(255, 255, 255, 0.05); }

        .info-note { font-size: 11px; color: var(--text-muted); text-align: center; margin-top: 20px; line-height: 1.6; }
    </style>
</head>
<body>

    <div class="container">
        
        <div class="top-nav">
            <a href="pesanan.php" class="btn-back">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Kembali ke Pesanan Saya
            </a>
        </div>

        <div class="receipt-card">
            
            <!-- Header -->
            <div class="receipt-header">
                <h2>E-Tiket / Invoice</h2>
                <p>ID Pesanan: <strong>#RG-<?= str_pad($b['id'], 5, '0', STR_PAD_LEFT) ?></strong></p>
                <div class="status-badge <?= $status_class ?>"><?= $status_text ?></div>
            </div>

            <!-- Body -->
            <div class="receipt-body">
                
                <!-- Detail Kendaraan -->
                <div class="section-title">Kendaraan</div>
                <div class="vehicle-box">
                    <div class="vehicle-img" style="padding: 0; background: transparent;">
                        <?php if(!empty($b['image']) && file_exists('uploads/' . $b['image'])): ?>
                            <img src="uploads/<?= $b['image'] ?>" alt="<?= htmlspecialchars($b['vehicle_name']) ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                        <?php else: ?>
                            <?= $b['vehicle_icon'] ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="vehicle-name"><?= htmlspecialchars($b['vehicle_name']) ?></div>
                        <div class="vehicle-type"><?= $b['vehicle_type'] ?> &bull; <?= htmlspecialchars($b['transmisi']) ?> &bull; <?= htmlspecialchars($b['tahun']) ?></div>
                    </div>
                </div>

                <!-- BOKS KONTAK BANTUAN -->
                <div class="contact-section">
                    <div class="contact-item">
                        <label>Kontak Mitra (Pemilik)</label>
                        <span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                            <?= htmlspecialchars($b['kontak_mitra'] ?? '81234567890') ?>
                        </span>
                    </div>
                    <div class="contact-item">
                        <label>RentGo Customer Service</label>
                        <span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            021-12345678
                        </span>
                    </div>
                </div>

                <!-- Waktu Sewa -->
                <div class="section-title">Waktu Sewa</div>
                <div class="data-grid">
                    <div class="data-item">
                        <label>Tanggal Ambil</label>
                        <span><?= date('d F Y', strtotime($b['tgl_ambil'])) ?> | <?= date('H:i', strtotime($b['jam_ambil'])) ?> WIB</span>
                    </div>
                    <div class="data-item">
                        <label>Durasi Sewa</label>
                        <span><?= $b['duration'] ?> Hari</span>
                    </div>
                </div>

                <!-- Data Penyewa -->
                <div class="section-title">Data Penyewa</div>
                <div class="data-grid">
                    <div class="data-item">
                        <label>Nama Lengkap</label>
                        <span><?= htmlspecialchars($b['nama_penyewa']) ?></span>
                    </div>
                    <div class="data-item">
                        <label>NIK</label>
                        <span><?= htmlspecialchars($b['nik']) ?></span>
                    </div>
                    <div class="data-item">
                        <label>No. WhatsApp</label>
                        <span><?= htmlspecialchars($b['no_wa']) ?></span>
                    </div>
                    <div class="data-item">
                        <label>Email</label>
                        <span><?= htmlspecialchars($b['email_penyewa']) ?></span>
                    </div>
                    <div class="data-item" style="grid-column: 1 / -1;">
                        <label>Alamat KTP</label>
                        <span><?= htmlspecialchars($b['alamat']) ?></span>
                    </div>
                    <div class="data-item" style="grid-column: 1 / -1;">
                        <label>Metode Pengambilan</label>
                        <span style="color: var(--primary);"><?= htmlspecialchars($b['lokasi_ambil']) ?></span>
                    </div>
                </div>

                <!-- Rincian Biaya -->
                <div class="section-title">Rincian Biaya</div>
                <div class="price-list">
                    <div class="price-row">
                        <span>Sewa <?= $b['duration'] ?> Hari x Rp<?= number_format($b['vehicle_price'], 0, ',', '.') ?></span>
                        <span>Rp<?= number_format($harga_sewa, 0, ',', '.') ?></span>
                    </div>
                    <div class="price-row">
                        <span>Biaya Layanan</span>
                        <span>Rp<?= number_format($biaya_layanan, 0, ',', '.') ?></span>
                    </div>
                    <div class="price-row">
                        <span>Deposit (Dikembalikan)</span>
                        <span>Rp<?= number_format($deposit, 0, ',', '.') ?></span>
                    </div>
                    
                    <div class="price-total">
                        <span>Total Tagihan</span>
                        <span>Rp<?= number_format($b['total_price'], 0, ',', '.') ?></span>
                    </div>
                </div>

            </div>

            <!-- Footer Aksi -->
            <div class="receipt-footer">
                <?php if($b['status'] == 'pending'): ?>
                    <a href="checkout.php?id=<?= $b['vehicle_id'] ?>" class="btn btn-primary">Lanjutkan Pembayaran</a>
                <?php elseif($b['status'] == 'paid' || $b['status'] == 'completed'): ?>
                    <button onclick="window.print()" class="btn btn-secondary">Cetak / Simpan PDF</button>
                    <a href="pesanan.php" class="btn btn-primary">Kembali</a>
                <?php else: ?>
                    <a href="pesanan.php" class="btn btn-primary">Kembali</a>
                <?php endif; ?>
            </div>

        </div>

        <div class="info-note">
            Tunjukkan e-tiket ini (beserta KTP asli) kepada mitra rental saat pengambilan kendaraan.<br>
            Jika ada kendala, silakan hubungi kontak yang tertera di atas.
        </div>

    </div>

</body>
</html>