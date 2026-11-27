<?php 
require_once 'config.php';
if(!is_login()) { header("Location: dashboard.php"); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
$stmt->execute([$id]);
$v = $stmt->fetch();
if(!$v) { header("Location: dashboard.php"); exit; }

// Biaya tambahan
$biaya_layanan = 5000;
$deposit = 100000;

if(isset($_POST['checkout'])) {
    $nama = $_POST['nama'];
    $wa = $_POST['wa'];
    $email = $_POST['email'];
    $nik = $_POST['nik'];
    $no_sim = $_POST['no_sim']; // TAMBAHAN: Menangkap input SIM
    $alamat = $_POST['alamat'];
    $tgl_ambil = $_POST['tgl_ambil'];
    $jam_ambil = $_POST['jam_ambil'];
    
    $lokasi = $_POST['lokasi'];
    $alamat_antar = isset($_POST['alamat_antar']) ? trim($_POST['alamat_antar']) : '';
    
    $lokasi_final = $lokasi;
    if($lokasi == 'Diantar ke alamat lain' && !empty($alamat_antar)) {
        $lokasi_final = "Diantar ke alamat lain: " . $alamat_antar;
    }

    $duration_input = (int)$_POST['duration'];
    $satuan = $_POST['satuan'];
    
    $days = $duration_input;
    if($satuan == 'minggu') { $days = $duration_input * 7; }
    elseif($satuan == 'bulan') { $days = $duration_input * 30; }

    $total = ($v['price'] * $days) + $biaya_layanan + $deposit;
    
    // TAMBAHAN: Memasukkan no_sim ke dalam query INSERT
    $sql = "INSERT INTO bookings (
                user_id, vehicle_id, duration, total_price, status, 
                nama_penyewa, no_wa, email_penyewa, nik, no_sim, alamat, tgl_ambil, jam_ambil, lokasi_ambil
            ) VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
    $stmt = $pdo->prepare($sql);
    // TAMBAHAN: Memasukkan variabel $no_sim ke dalam execute
    $stmt->execute([
        $_SESSION['user_id'], $v['id'], $days, $total,
        $nama, $wa, $email, $nik, $no_sim, $alamat, $tgl_ambil, $jam_ambil, $lokasi_final
    ]);
    
    $_SESSION['booking_id'] = $pdo->lastInsertId();
    $_SESSION['booking_total'] = $total;
    $_SESSION['booking_name'] = $v['name'];
    $_SESSION['booking_duration'] = $days; 
    
    header("Location: payment.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - RentGo</title>
    
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

        /* === TOMBOL KEMBALI === */
        .top-nav { margin-bottom: 25px; }
        .btn-back { 
            color: var(--text-muted); text-decoration: none; font-size: 13px; font-weight: 500; 
            display: inline-flex; align-items: center; gap: 8px; transition: 0.3s;
            background: rgba(255, 255, 255, 0.03); padding: 8px 15px; border-radius: 8px; border: 1px solid var(--border);
        }
        .btn-back:hover { color: var(--text); background: rgba(255, 255, 255, 0.08); }

        /* === STEPPER MODERN === */
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

        /* === LAYOUT === */
        .grid-top { display: grid; grid-template-columns: 1.3fr 1fr; gap: 24px; align-items: start; }
        @media (max-width: 900px) { .grid-top { grid-template-columns: 1fr; } }
        
        .review-section { display: none; margin-top: 24px; animation: slideDown 0.4s ease-out; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

        .card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 24px; }
        .card-title { font-size: 18px; font-weight: 600; margin-bottom: 20px; color: var(--text); display: flex; align-items: center; gap: 10px;}
        .title-icon { width: 24px; height: 24px; background: rgba(59, 130, 246, 0.15); color: var(--primary); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 12px; }

        /* === FORM ELEMENTS === */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 11px; color: var(--text-muted); margin-bottom: 6px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control { width: 100%; background: var(--input-bg); border: 1px solid var(--border); color: var(--text); padding: 12px 15px; border-radius: 10px; font-size: 13px; outline: none; transition: 0.3s; }
        .form-control:focus { border-color: var(--primary); }
        textarea.form-control { resize: vertical; min-height: 60px; }
        
        /* FIX UNTUK AUTOFILL BROWSER */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active{
            -webkit-box-shadow: 0 0 0 30px var(--input-bg) inset !important;
            -webkit-text-fill-color: var(--text) !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        /* Styling untuk Select Dropdown */
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2394A3B8%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
            background-repeat: no-repeat;
            background-position: right 15px top 50%;
            background-size: 10px auto;
            cursor: pointer;
        }
        select.form-control option { background: var(--bg); color: var(--text); }

        .row { display: flex; gap: 15px; }
        .row .form-group { flex: 1; }

        /* === DATA KENDARAAN (KANAN) === */
        .vehicle-info { background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border); border-radius: 12px; padding: 20px; }
        .vehicle-info-header { display: flex; align-items: center; gap: 15px; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px dashed var(--border); }
        .vehicle-img { width: 80px; height: 50px; background: var(--input-bg); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 24px; overflow: hidden; }
        .vehicle-name { font-size: 16px; font-weight: 600; }
        .vehicle-type { font-size: 11px; color: var(--text-muted); }
        
        .price-row { display: flex; justify-content: space-between; font-size: 13px; color: var(--text-muted); margin-bottom: 12px; }
        .price-row.total { color: var(--text); font-weight: 600; font-size: 16px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border); }

        /* === BUTTON === */
        .btn { width: 100%; padding: 14px; border-radius: 10px; border: none; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.3s; text-align: center; display: inline-block; text-decoration: none; }
        .btn-primary { background: var(--primary); color: white; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3); }
        .btn-primary:hover { background: #2563eb; }
        .btn-edit { background: transparent; color: var(--primary); border: 1px solid var(--primary); margin-top: 15px; width: auto; padding: 8px 20px; font-size: 12px; }
        
        /* === SUMMARY TEXT (REVIEW) === */
        .summary-list { margin-bottom: 25px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px 30px; }
        @media (max-width: 600px) { .summary-list { grid-template-columns: 1fr; } }
        
        .summary-item { display: flex; flex-direction: column; gap: 4px; }
        .summary-item span:first-child { color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
        .summary-item span:last-child { font-weight: 500; font-size: 14px; word-wrap: break-word; }

        .checkbox-group { display: flex; align-items: flex-start; gap: 10px; margin: 20px 0; font-size: 13px; color: var(--text-muted); line-height: 1.5; }
        .checkbox-group input { margin-top: 3px; cursor: pointer; width: 16px; height: 16px; }
    </style>
</head>
<body>

    <div class="container">
        
        <div class="top-nav">
            <a href="koleksi.php" class="btn-back">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Kembali ke Koleksi
            </a>
        </div>

        <!-- Stepper -->
        <div class="stepper-wrapper">
            <div class="step active" id="step1">
                <div class="step-circle">1</div>
                <div class="step-label">Data</div>
            </div>
            <div class="step-line" id="line1"></div>
            <div class="step" id="step2">
                <div class="step-circle">2</div>
                <div class="step-label">Periksa</div>
            </div>
            <div class="step-line"></div>
            <div class="step" id="step3">
                <div class="step-circle">3</div>
                <div class="step-label">Bayar</div>
            </div>
            <div class="step-line"></div>
            <div class="step" id="step4">
                <div class="step-circle">4</div>
                <div class="step-label">Selesai</div>
            </div>
        </div>

        <form method="POST" id="checkoutForm">
            
            <div class="grid-top">
                
                <!-- KIRI: Form Data Penyewa -->
                <div class="card">
                    <div class="card-title">
                        <div class="title-icon">📝</div> Data Penyewa
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Lengkap (Sesuai KTP)</label>
                        <input type="text" name="nama" id="input-nama" class="form-control" placeholder="Masukkan nama lengkap">
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label>No. WhatsApp</label>
                            <input type="tel" name="wa" id="input-wa" class="form-control" placeholder="0812xxxxxx">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="input-email" class="form-control" placeholder="email@example.com">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label>NIK (16 Digit)</label>
                            <input type="text" name="nik" id="input-nik" class="form-control" placeholder="3271xxxxxxxxxxxx">
                        </div>
                        <!-- TAMBAHAN FORM INPUT SIM -->
                        <div class="form-group">
                            <label>Nomor SIM</label>
                            <input type="text" name="no_sim" id="input-sim" class="form-control" placeholder="Masukkan Nomor SIM">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Alamat Lengkap (Sesuai KTP)</label>
                        <textarea name="alamat" id="input-alamat" class="form-control" placeholder="Masukkan alamat lengkap (jalan, RT/RW, kelurahan, kota)"></textarea>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label>Tanggal Ambil</label>
                            <input type="date" name="tgl_ambil" id="input-tgl" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Jam Ambil</label>
                            <input type="time" name="jam_ambil" id="input-jam" class="form-control" value="09:00">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label>Durasi Sewa</label>
                            <input type="number" name="duration" id="durationInput" class="form-control" min="1" value="1" oninput="calculateTotal()">
                        </div>
                        <div class="form-group">
                            <label>Satuan</label>
                            <select name="satuan" id="satuanInput" class="form-control" onchange="calculateTotal()">
                                <option value="hari">Hari</option>
                                <option value="minggu">Minggu</option>
                                <option value="bulan">Bulan</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Titik Ambil & Kembali</label>
                        <select name="lokasi" id="input-lokasi" class="form-control" onchange="toggleAlamatAntar()">
                            <option value="Lokasi rental (Ambil sendiri)">Lokasi rental (Ambil sendiri)</option>
                            <option value="Diantar ke alamat sesuai KTP">Diantar ke alamat sesuai KTP</option>
                            <option value="Diantar ke alamat lain">Diantar ke alamat lain</option>
                        </select>
                    </div>

                    <!-- Input Alamat Antar -->
                    <div class="form-group" id="alamat-antar-group" style="display: none;">
                        <label>Alamat Pengantaran Spesifik</label>
                        <input type="text" name="alamat_antar" id="input-alamat-antar" class="form-control" placeholder="Contoh: Hotel Grand Melia, Jl. Sudirman No. 1">
                    </div>

                    <button type="button" class="btn btn-primary" style="margin-top: 15px;" onclick="validateAndShowReview()">
                        Lanjut ke Periksa Pesanan
                    </button>
                </div>

                <!-- KANAN: Data Kendaraan -->
                <div class="card">
                    <div class="card-title">
                        <div class="title-icon">🚗</div> Data Kendaraan
                    </div>
                    
                    <div class="vehicle-info">
                        <div class="vehicle-info-header">
                            <!-- Ganti bagian gambar agar dinamis -->
                            <div class="vehicle-img" style="padding: 0; background: transparent;">
                                <?php if(!empty($v['image']) && file_exists('uploads/' . $v['image'])): ?>
                                    <img src="uploads/<?= $v['image'] ?>" alt="<?= htmlspecialchars($v['name']) ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                                <?php else: ?>
                                    <span style="font-size: 24px;"><?= $v['icon'] ?></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="vehicle-name"><?= htmlspecialchars($v['name']) ?></div>
                                <!-- Ganti bagian teks agar dinamis -->
                                <div class="vehicle-type"><?= ucfirst($v['type']) ?> • <?= htmlspecialchars($v['transmisi']) ?></div>
                            </div>
                        </div>
                        <div class="price-row">
                            <span>Sewa <span id="labelDurasi">1 Hari</span> x Rp<?= number_format($v['price'], 0, ',', '.') ?></span>
                            <span id="hargaSewa">Rp<?= number_format($v['price'], 0, ',', '.') ?></span>
                        </div>
                        <div class="price-row">
                            <span>Biaya layanan</span>
                            <span>Rp<?= number_format($biaya_layanan, 0, ',', '.') ?></span>
                        </div>
                        <div class="price-row">
                            <span>Deposit (dikembalikan)</span>
                            <span>Rp<?= number_format($deposit, 0, ',', '.') ?></span>
                        </div>
                        <div class="price-row total">
                            <span>Total Sementara</span>
                            <span id="totalHarga">Rp<?= number_format($v['price'] + $biaya_layanan + $deposit, 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- LAYOUT BAWAH (REVIEW) -->
            <div class="review-section" id="reviewSection">
                <div class="card" style="border-color: var(--primary);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <div class="card-title" style="margin: 0;">
                            <div class="title-icon">🔍</div> Periksa & Ajukan Pesanan
                        </div>
                        <button type="button" class="btn btn-edit" onclick="editData()">Edit Data</button>
                    </div>
                    
                    <div class="summary-list">
                        <div class="summary-item">
                            <span>Nama Penyewa</span>
                            <span id="review-nama">-</span>
                        </div>
                        <div class="summary-item">
                            <span>No. WhatsApp</span>
                            <span id="review-wa">-</span>
                        </div>
                        <div class="summary-item">
                            <span>Email</span>
                            <span id="review-email">-</span>
                        </div>
                        <div class="summary-item">
                            <span>NIK</span>
                            <span id="review-nik">-</span>
                        </div>
                        <!-- TAMBAHAN REVIEW SIM -->
                        <div class="summary-item">
                            <span>Nomor SIM</span>
                            <span id="review-sim">-</span>
                        </div>
                        <div class="summary-item" style="grid-column: 1 / -1;">
                            <span>Alamat KTP</span>
                            <span id="review-alamat">-</span>
                        </div>
                        <div class="summary-item">
                            <span>Tanggal & Jam Ambil</span>
                            <span id="review-waktu">-</span>
                        </div>
                        <div class="summary-item">
                            <span>Durasi Sewa</span>
                            <span id="review-durasi">-</span>
                        </div>
                        <div class="summary-item" style="grid-column: 1 / -1;">
                            <span>Metode Pengambilan</span>
                            <span id="review-lokasi">-</span>
                        </div>
                    </div>

                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 20px; padding: 15px; background: var(--input-bg); border-radius: 8px; border-left: 3px solid var(--primary);">
                        <strong>Informasi:</strong> Syarat sewa: KTP asli, SIM aktif, usia minimal 18 tahun. Deposit dikembalikan saat kendaraan kembali dalam kondisi baik.
                    </div>

                    <label class="checkbox-group">
                        <input type="checkbox" id="agreeCheck">
                        <span>Saya menyetujui syarat dan ketentuan sewa, serta data yang saya isi sudah benar.</span>
                    </label>

                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 20px; margin-bottom: 20px;">
                        <span style="font-weight: 600; font-size: 16px;">Total Pembayaran:</span>
                        <span style="font-weight: 700; font-size: 22px; color: var(--primary);" id="review-total">Rp0</span>
                    </div>

                    <button type="submit" name="checkout" class="btn btn-primary" id="submitBtn" onclick="return validateCheckbox(event)">
                        Konfirmasi & Ajukan Pesanan
                    </button>
                </div>
            </div>

        </form>
    </div>

    <script>
        const hargaPerHari = <?= $v['price'] ?>;
        const biayaLayanan = <?= $biaya_layanan ?>;
        const deposit = <?= $deposit ?>;
        const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });

        function toggleAlamatAntar() {
            const lokasi = document.getElementById('input-lokasi').value;
            const group = document.getElementById('alamat-antar-group');
            const inputAlamat = document.getElementById('input-alamat-antar');
            
            if (lokasi === 'Diantar ke alamat lain') {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
                inputAlamat.value = ''; 
            }
        }

        function calculateTotal() {
            let durasiVal = parseInt(document.getElementById('durationInput').value) || 1;
            let satuan = document.getElementById('satuanInput').value;
            
            let totalHari = durasiVal;
            if(satuan === 'minggu') totalHari = durasiVal * 7;
            if(satuan === 'bulan') totalHari = durasiVal * 30;
            
            const total = (hargaPerHari * totalHari) + biayaLayanan + deposit;
            let labelSatuan = satuan.charAt(0).toUpperCase() + satuan.slice(1);

            document.getElementById('labelDurasi').innerText = durasiVal + ' ' + labelSatuan;
            document.getElementById('hargaSewa').innerText = formatter.format(hargaPerHari * totalHari);
            document.getElementById('totalHarga').innerText = formatter.format(total);
            
            document.getElementById('review-total').innerText = formatter.format(total);
            
            if(satuan !== 'hari') {
                document.getElementById('review-durasi').innerText = `${durasiVal} ${labelSatuan} (${totalHari} Hari)`;
            } else {
                document.getElementById('review-durasi').innerText = `${durasiVal} Hari`;
            }
        }

        // VALIDASI MANUAL: Tidak menggunakan checkValidity() bawaan browser
        function validateAndShowReview() {
            const elNama = document.getElementById('input-nama');
            const elWa = document.getElementById('input-wa');
            const elEmail = document.getElementById('input-email');
            const elNik = document.getElementById('input-nik');
            const elSim = document.getElementById('input-sim'); // TAMBAHAN
            const elAlamat = document.getElementById('input-alamat');
            const elTgl = document.getElementById('input-tgl');
            const elJam = document.getElementById('input-jam');
            const elDurasi = document.getElementById('durationInput');
            const elLokasi = document.getElementById('input-lokasi');
            const elAlamatAntar = document.getElementById('input-alamat-antar');

            // Reset border merah sebelumnya (TAMBAHAN elSim)
            [elNama, elWa, elEmail, elNik, elSim, elAlamat, elTgl, elJam, elDurasi, elAlamatAntar].forEach(el => {
                if(el) el.style.borderColor = 'var(--border)';
            });

            // Cek satu per satu dengan pesan spesifik
            if(elNama.value.trim() === '') { showError(elNama, 'Nama Lengkap wajib diisi!'); return; }
            if(elWa.value.trim() === '') { showError(elWa, 'No. WhatsApp wajib diisi!'); return; }
            if(elEmail.value.trim() === '') { showError(elEmail, 'Email wajib diisi!'); return; }
            if(elNik.value.trim() === '') { showError(elNik, 'NIK wajib diisi!'); return; }
            if(elSim.value.trim() === '') { showError(elSim, 'Nomor SIM wajib diisi!'); return; } // TAMBAHAN VALIDASI
            if(elAlamat.value.trim() === '') { showError(elAlamat, 'Alamat wajib diisi!'); return; }
            
            // KHUSUS TANGGAL (Fix untuk bug browser Chrome)
            if(elTgl.value === '') { 
                showError(elTgl, 'Tanggal Ambil terdeteksi kosong oleh sistem. Silakan KLIK IKON KALENDER dan pilih tanggal dari kalender yang muncul, jangan diketik manual.'); 
                return; 
            }
            if(elJam.value === '') { showError(elJam, 'Jam Ambil wajib diisi!'); return; }
            
            if(elDurasi.value === '' || parseInt(elDurasi.value) < 1) { showError(elDurasi, 'Durasi minimal 1!'); return; }

            // Jika user pilih "Diantar ke alamat lain", pastikan alamat diisi
            if(elLokasi.value === 'Diantar ke alamat lain' && elAlamatAntar.value.trim() === '') {
                showError(elAlamatAntar, 'Alamat pengantaran wajib diisi karena Anda memilih diantar ke alamat lain!'); return;
            }

            // ===== JIKA SEMUA VALID =====
            try {
                let lokasiTampil = elLokasi.value;
                if(elLokasi.value === 'Diantar ke alamat lain' && elAlamatAntar.value !== '') {
                    lokasiTampil = `Diantar ke alamat lain: ${elAlamatAntar.value}`;
                }

                document.getElementById('review-nama').innerText = elNama.value;
                document.getElementById('review-wa').innerText = elWa.value;
                document.getElementById('review-email').innerText = elEmail.value;
                document.getElementById('review-nik').innerText = elNik.value;
                document.getElementById('review-sim').innerText = elSim.value; // TAMBAHAN TAMPILKAN REVIEW
                document.getElementById('review-alamat').innerText = elAlamat.value;
                document.getElementById('review-waktu').innerText = `${elTgl.value} | ${elJam.value}`;
                document.getElementById('review-lokasi').innerText = lokasiTampil;
                
                calculateTotal(); 

                document.getElementById('reviewSection').style.display = 'block';
                document.getElementById('reviewSection').scrollIntoView({ behavior: 'smooth', block: 'start' });

                document.getElementById('step1').classList.remove('active');
                document.getElementById('step1').classList.add('completed');
                document.getElementById('line1').classList.add('active');
                document.getElementById('step2').classList.add('active');

            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan sistem.');
            }
        }

        // Fungsi bantuan menampilkan pesan error
        function showError(element, message) {
            element.style.borderColor = 'var(--error)';
            element.focus();
            alert(message);
        }

        function editData() {
            document.getElementById('reviewSection').style.display = 'none';
            window.scrollTo({ top: 0, behavior: 'smooth' });

            document.getElementById('step1').classList.add('active');
            document.getElementById('step1').classList.remove('completed');
            document.getElementById('line1').classList.remove('active');
            document.getElementById('step2').classList.remove('active');
        }

        function validateCheckbox(e) {
            const check = document.getElementById('agreeCheck');
            if (!check.checked) {
                e.preventDefault();
                alert('Anda harus menyetujui syarat dan ketentuan sebelum melanjutkan.');
                return false;
            }
            return true;
        }

        document.addEventListener('DOMContentLoaded', () => {
            calculateTotal();
        });
    </script>

</body>
</html>