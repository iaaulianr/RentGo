<?php 
require_once 'config.php';
if(!is_login() || !isset($_SESSION['booking_id'])) { header("Location: dashboard.php"); exit; }

$success = false;
$booking_code = "";
$metode_pilihan = "QRIS"; // Default

if(isset($_POST['bayar'])) {
    // Tangkap metode pembayaran yang dipilih dari hidden input
    if(isset($_POST['metode'])) {
        $metode_pilihan = $_POST['metode'];
    }

    // 1. Ubah status pesanan menjadi 'paid' di database
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'paid' WHERE id = ?");
    $stmt->execute([$_SESSION['booking_id']]);
    
    // 2. Ambil vehicle_id dari pesanan yang baru saja dibayar
    $stmt_booking = $pdo->prepare("SELECT vehicle_id FROM bookings WHERE id = ?");
    $stmt_booking->execute([$_SESSION['booking_id']]);
    $booking = $stmt_booking->fetch();
    
    // 3. Ubah status kendaraan menjadi 'disewa' agar muncul label Leased di koleksi
    if($booking) {
        $stmt_vehicle = $pdo->prepare("UPDATE vehicles SET status = 'disewa' WHERE id = ?");
        $stmt_vehicle->execute([$booking['vehicle_id']]);
    }

    $success = true;
    // Generate kode booking dummy untuk tampilan
    $booking_code = "RG-" . strtoupper(substr(md5(uniqid()), 0, 6));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - RentGo</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #060b14; --card: #0d1627; --input-bg: #080f1c; --border: #1e293b;
            --primary: #3b82f6; --text: #f8fafc; --text-muted: #94a3b8;
            --success: #22c55e; --error: #ef4444;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: var(--bg); color: var(--text); min-height: 100vh; padding: 30px 20px; display: flex; justify-content: center; }
        
        .container { width: 100%; max-width: 1100px; }

        /* === STEPPER MODERN (SAMA PERSIS CHECKOUT) === */
        .stepper-wrapper {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 40px; background: var(--card); padding: 20px 40px;
            border-radius: 16px; border: 1px solid var(--border);
        }
        .step { display: flex; align-items: center; gap: 12px; position: relative; z-index: 2; }
        .step-circle {
            width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 600; background: var(--input-bg); border: 1px solid var(--border);
            color: var(--text-muted); transition: 0.3s;
        }
        .step-label { font-size: 13px; font-weight: 500; color: var(--text-muted); transition: 0.3s; }
        
        .step.active .step-circle { background: var(--primary); border-color: var(--primary); color: white; box-shadow: 0 0 15px rgba(59, 130, 246, 0.4); }
        .step.active .step-label { color: var(--text); font-weight: 600; }
        .step.completed .step-circle { background: rgba(34, 197, 94, 0.2); border-color: var(--success); color: var(--success); }
        .step.completed .step-label { color: var(--text); }
        
        .step-line { flex: 1; height: 2px; background: var(--border); margin: 0 20px; position: relative; z-index: 1; }
        .step-line.active { background: var(--success); }

        /* === GRID LAYOUT === */
        .grid-layout { display: grid; grid-template-columns: 1.2fr 1fr; gap: 24px; align-items: start; }
        @media (max-width: 900px) { .grid-layout { grid-template-columns: 1fr; } }

        /* === CARD === */
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 24px; }
        .card-title { font-size: 18px; font-weight: 600; margin-bottom: 20px; color: var(--text); display: flex; align-items: center; gap: 10px;}
        .title-icon { width: 24px; height: 24px; background: rgba(59, 130, 246, 0.15); color: var(--primary); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 12px; }

        /* === METODE PEMBAYARAN === */
        .payment-method { background: var(--input-bg); border: 1px solid var(--border); border-radius: 10px; padding: 15px; margin-bottom: 12px; cursor: pointer; transition: 0.3s; }
        .payment-method.active { border-color: var(--primary); background: rgba(59, 130, 246, 0.05); }
        .payment-method h4 { font-size: 14px; font-weight: 600; margin-bottom: 4px; }
        .payment-method p { font-size: 11px; color: var(--text-muted); }

        /* Konten Pembayaran Dinamis */
        .payment-content { display: none; margin-top: 20px; padding-top: 20px; border-top: 1px dashed var(--border); }
        .payment-content.active { display: block; animation: fadeIn 0.3s ease-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .qr-container { text-align: center; background: white; padding: 15px; border-radius: 12px; width: max-content; margin: 0 auto; }
        .qr-container svg { width: 150px; height: 150px; }
        
        .va-box { background: var(--input-bg); border: 1px solid var(--border); padding: 15px; border-radius: 10px; text-align: center; font-size: 18px; font-weight: 600; letter-spacing: 1px; color: var(--primary); margin-bottom: 10px; }
        
        .card-input-group { display: flex; flex-direction: column; gap: 10px; }
        .card-input-group input { background: var(--input-bg); border: 1px solid var(--border); padding: 10px; border-radius: 8px; color: white; outline: none; }
        .card-input-group input:focus { border-color: var(--primary); }
        .card-row { display: flex; gap: 10px; }

        /* === RINGKASAN PESANAN === */
        .summary-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 12px; color: var(--text-muted); }
        .summary-row span:last-child { color: var(--text); font-weight: 500; }
        .summary-row.total { color: var(--text); font-weight: 600; font-size: 16px; margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border); }

        /* === BUTTON === */
        .btn { width: 100%; padding: 14px; border-radius: 10px; border: none; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.3s; text-align: center; text-decoration: none; display: block; }
        .btn-primary { background: var(--primary); color: white; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3); }
        .btn-primary:hover { background: #2563eb; }

        /* === SUCCESS RECEIPT (TENGAH LAYAR) === */
        .receipt-wrapper { 
            display: flex; 
            justify-content: center; 
            width: 100%;
            animation: slideUp 0.5s ease-out;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .receipt-card {
            width: 100%;
            max-width: 500px; 
            background: var(--card); 
            border: 1px solid var(--success); 
            border-radius: 16px; 
            padding: 35px 30px;
            box-shadow: 0 10px 40px rgba(34, 197, 94, 0.1);
        }

        .receipt-header { text-align: center; margin-bottom: 25px; }
        .success-icon { width: 70px; height: 70px; background: var(--success); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto; font-size: 36px; color: white; box-shadow: 0 10px 20px rgba(34, 197, 94, 0.3); }
        .receipt-title { font-size: 22px; font-weight: 600; margin-bottom: 5px; }
        .receipt-subtitle { font-size: 13px; color: var(--text-muted); }
        
        .booking-code { font-size: 24px; font-weight: 700; color: var(--primary); text-align: center; letter-spacing: 3px; margin: 15px 0 30px 0; background: rgba(59, 130, 246, 0.1); padding: 12px; border-radius: 8px; border: 1px dashed rgba(59, 130, 246, 0.3); }

        .receipt-details { margin-bottom: 20px; }
        .receipt-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 15px; }
        .receipt-row span:first-child { color: var(--text-muted); }
        .receipt-row span:last-child { font-weight: 500; text-align: right; max-width: 60%; }
        .receipt-row.total { font-weight: 600; font-size: 16px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border); }
    </style>
</head>
<body>

    <div class="container">
        
        <!-- STEPPER -->
        <div class="stepper-wrapper">
            <div class="step completed">
                <div class="step-circle">1</div>
                <div class="step-label">Data</div>
            </div>
            <div class="step-line active"></div>
            <div class="step completed">
                <div class="step-circle">2</div>
                <div class="step-label">Periksa</div>
            </div>
            <div class="step-line <?= $success ? 'active' : '' ?>"></div>
            <div class="step <?= $success ? 'completed' : 'active' ?>">
                <div class="step-circle">3</div>
                <div class="step-label">Bayar</div>
            </div>
            <div class="step-line <?= $success ? 'active' : '' ?>"></div>
            <div class="step <?= $success ? 'active' : '' ?>">
                <div class="step-circle">4</div>
                <div class="step-label">Selesai</div>
            </div>
        </div>

        <!-- JIKA BELUM BAYAR -->
        <?php if(!$success): ?>
            <div class="grid-layout">
                
                <!-- KOLOM KIRI: METODE PEMBAYARAN -->
                <div class="card">
                    <div class="card-title">
                        <div class="title-icon">💳</div> Pembayaran
                    </div>
                    
                    <div class="alert" style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); color: #86efac; padding: 12px 15px; border-radius: 8px; font-size: 12px; margin-bottom: 20px;">
                        ✅ Pesanan dikonfirmasi mitra. Selesaikan pembayaran sebelum <strong>15:00</strong> WIB.
                    </div>

                    <form method="POST" id="paymentForm">
                        <input type="hidden" name="metode" id="input-metode" value="QRIS">

                        <!-- Opsi QRIS -->
                        <div class="payment-method active" onclick="selectMethod('QRIS', this)">
                            <h4>QRIS</h4>
                            <p>Semua bank dan e-wallet</p>
                        </div>
                        <div id="content-QRIS" class="payment-content active">
                            <div class="qr-container">
                                <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="100" height="100" fill="white"/>
                                    <path d="M10,10 h20 v20 h-20 z M15,15 h10 v10 h-10 z" fill="black"/>
                                    <path d="M70,10 h20 v20 h-20 z M75,15 h10 v10 h-10 z" fill="black"/>
                                    <path d="M10,70 h20 v20 h-20 z M15,75 h10 v10 h-10 z" fill="black"/>
                                    <path d="M40,10 h10 v10 h-10 z M60,10 h5 v5 h-5 z M40,30 h5 v10 h-5 z M50,40 h10 v10 h-10 z M70,40 h20 v5 h-20 z M30,50 h20 v5 h-20 z M60,60 h20 v20 h-20 z M10,40 h10 v10 h-10 z M10,60 h15 v5 h-15 z" fill="black"/>
                                </svg>
                            </div>
                            <p style="text-align: center; font-size: 12px; color: var(--text-muted); margin-top: 15px;">Scan QR ini menggunakan aplikasi M-Banking atau E-Wallet Anda.</p>
                        </div>

                        <!-- Opsi Virtual Account -->
                        <div class="payment-method" onclick="selectMethod('Virtual Account', this)">
                            <h4>Virtual Account</h4>
                            <p>BCA, BNI, BRI, Mandiri, dll</p>
                        </div>
                        <div id="content-Virtual Account" class="payment-content">
                            <div class="va-box">8808 1234 5678 9012</div>
                            <p style="text-align: center; font-size: 12px; color: var(--text-muted);">Silakan transfer tepat sesuai nominal ke nomor Virtual Account di atas.</p>
                        </div>

                        <!-- Opsi E-Wallet -->
                        <div class="payment-method" onclick="selectMethod('E-wallet', this)">
                            <h4>E-wallet</h4>
                            <p>GoPay, OVO, DANA, ShopeePay</p>
                        </div>
                        <div id="content-E-wallet" class="payment-content">
                            <div class="va-box" style="color: #22c55e;">0812 3456 7890</div>
                            <p style="text-align: center; font-size: 12px; color: var(--text-muted);">Buka aplikasi E-Wallet Anda dan kirim ke nomor di atas.</p>
                        </div>

                        <!-- Opsi Kartu -->
                        <div class="payment-method" onclick="selectMethod('Kartu Debit/Kredit', this)">
                            <h4>Kartu Debit/Kredit</h4>
                            <p>Visa, Mastercard, JCB</p>
                        </div>
                        <div id="content-Kartu Debit/Kredit" class="payment-content">
                            <div class="card-input-group">
                                <input type="text" placeholder="Nomor Kartu (16 Digit)" maxlength="19">
                                <div class="card-row">
                                    <input type="text" placeholder="MM/YY" maxlength="5">
                                    <input type="text" placeholder="CVV" maxlength="3">
                                </div>
                                <input type="text" placeholder="Nama pada Kartu">
                            </div>
                        </div>

                        <button type="submit" name="bayar" class="btn btn-primary" style="margin-top: 25px;">
                            Saya sudah bayar
                        </button>
                    </form>
                </div>

                <!-- KOLOM KANAN: RINGKASAN PESANAN -->
                <div class="card">
                    <div class="card-title">
                        <div class="title-icon">🧾</div> Ringkasan Pesanan
                    </div>
                    
                    <div class="summary-row">
                        <span>Kendaraan</span>
                        <span><?= htmlspecialchars($_SESSION['booking_name']) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Durasi Sewa</span>
                        <span><?= $_SESSION['booking_duration'] ?> Hari</span>
                    </div>
                    <div class="summary-row">
                        <span>Biaya Layanan</span>
                        <span>Rp 5.000</span>
                    </div>
                    <div class="summary-row">
                        <span>Deposit (Refundable)</span>
                        <span>Rp 100.000</span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Total Tagihan</span>
                        <span style="color: var(--primary); font-size: 18px;">Rp<?= number_format($_SESSION['booking_total'], 0, ',', '.') ?></span>
                    </div>

                    <div style="margin-top: 25px; font-size: 11px; color: var(--text-muted); line-height: 1.6; background: var(--input-bg); padding: 15px; border-radius: 8px;">
                        <strong>Perhatian:</strong> Pastikan nominal transfer sama persis dengan total tagihan. Pembayaran yang tidak sesuai akan tertunda.
                    </div>
                </div>

            </div>
        <?php else: ?>
            
            <!-- JIKA SUDAH BAYAR: HANYA TAMPILKAN 1 RESI DI TENGAH -->
            <div class="receipt-wrapper">
                <div class="receipt-card">
                    <div class="receipt-header">
                        <div class="success-icon">✓</div>
                        <div class="receipt-title">Pembayaran Berhasil</div>
                        <div class="receipt-subtitle">Terima kasih telah menyewa di RentGo</div>
                    </div>

                    <div class="booking-code"><?= $booking_code ?></div>

                    <div class="receipt-details">
                        <div class="receipt-row">
                            <span>Kendaraan</span>
                            <span><?= htmlspecialchars($_SESSION['booking_name']) ?></span>
                        </div>
                        <div class="receipt-row">
                            <span>Durasi Sewa</span>
                            <span><?= $_SESSION['booking_duration'] ?> Hari</span>
                        </div>
                        <div class="receipt-row">
                            <span>Metode Pembayaran</span>
                            <span><?= htmlspecialchars($metode_pilihan) ?></span>
                        </div>
                        <div class="receipt-row">
                            <span>Waktu Transaksi</span>
                            <span><?= date('d M Y, H:i') ?> WIB</span>
                        </div>
                        
                        <div class="receipt-row total">
                            <span>Total Dibayar</span>
                            <span style="color: var(--success);">Rp<?= number_format($_SESSION['booking_total'], 0, ',', '.') ?></span>
                        </div>
                    </div>

                    <div style="font-size: 11px; color: var(--text-muted); text-align: center; margin: 25px 0; line-height: 1.6; border-top: 1px dashed var(--border); padding-top: 20px;">
                        Tunjukkan kode booking dan KTP asli Anda saat pengambilan kendaraan. Bukti transaksi ini juga tersimpan di riwayat akun Anda.
                    </div>
                    
                    <a href="dashboard.php" class="btn btn-primary">Selesai & Kembali ke Beranda</a>
                </div>
            </div>

        <?php endif; ?>

    </div>

    <!-- SCRIPT UNTUK LOGIKA METODE PEMBAYARAN -->
    <?php if(!$success): ?>
    <script>
        function selectMethod(methodName, element) {
            // 1. Hapus kelas active dari semua tombol metode
            const methods = document.querySelectorAll('.payment-method');
            methods.forEach(m => m.classList.remove('active'));
            
            // 2. Tambahkan kelas active ke tombol yang diklik
            element.classList.add('active');

            // 3. Sembunyikan semua konten
            const contents = document.querySelectorAll('.payment-content');
            contents.forEach(c => c.classList.remove('active'));

            // 4. Tampilkan konten yang sesuai
            const targetContent = document.getElementById('content-' + methodName);
            if(targetContent) {
                targetContent.classList.add('active');
            }

            // 5. Update hidden input untuk disimpan ke PHP
            document.getElementById('input-metode').value = methodName;
        }
    </script>
    <?php endif; ?>

</body>
</html>