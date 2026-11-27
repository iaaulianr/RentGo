<?php 
require_once 'config.php';

// PROTEKSI: Hanya user login yang bisa akses, Guest dilarang
if(!is_login()) { 
    header("Location: dashboard.php"); 
    exit; 
}

$success = false;
$error = "";

if(isset($_POST['daftar_mitra'])) {
    // 1. Tangkap data dari form teks
    $nama_mitra = $_POST['nama_mitra'];
    $email_mitra = $_POST['email_mitra'];
    $telepon = $_POST['telepon'];
    $jenis_kendaraan = $_POST['jenis_kendaraan'];
    $merek = $_POST['merek'];
    $tahun = (int)$_POST['tahun'];
    $lokasi = $_POST['lokasi'];
    $transmisi = $_POST['transmisi'];
    
    // Harga sewa default
    $harga_sewa = ($jenis_kendaraan == 'mobil') ? 350000 : 75000;
    
    // Simpan icon sesuai jenisnya
    $icon = ($jenis_kendaraan == 'mobil') ? '🚗' : '🛵';

    // 2. Logika Upload Gambar
    $nama_file_baru = ""; 
    if (isset($_FILES['foto_kendaraan']) && $_FILES['foto_kendaraan']['error'] === UPLOAD_ERR_OK) {
        $nama_file = $_FILES['foto_kendaraan']['name'];
        $ukuran_file = $_FILES['foto_kendaraan']['size'];
        $tmp_file = $_FILES['foto_kendaraan']['tmp_name'];
        
        // Validasi Ukuran (Maks 3MB)
        if($ukuran_file > 3000000) {
            $error = "Ukuran foto terlalu besar. Maksimal 3MB.";
        } else {
            // Validasi Ekstensi
            $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
            $ekstensi_valid = ['jpg', 'jpeg', 'png'];
            
            if(!in_array($ekstensi, $ekstensi_valid)) {
                $error = "Format file tidak didukung. Gunakan JPG, JPEG, atau PNG.";
            } else {
                // Buat nama file unik
                $nama_file_baru = uniqid('mtr_') . '.' . $ekstensi;
                $tujuan_upload = 'uploads/' . $nama_file_baru;
                
                if(!move_uploaded_file($tmp_file, $tujuan_upload)) {
                    $error = "Gagal mengunggah foto. Pastikan folder uploads memiliki izin tulis.";
                }
            }
        }
    } else {
        $error = "Wajib mengunggah foto kendaraan.";
    }

    // 3. Cek Validasi dan Simpan ke Database
    if(empty($error)) {
        if($tahun < 2018) {
            $error = "Mohon maaf, kami hanya menerima kendaraan tahun 2018 ke atas.";
        } else {
            try {
                $pdo->beginTransaction();

                // Ubah role user menjadi 'mitra'
                $stmt = $pdo->prepare("UPDATE users SET role = 'mitra' WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $_SESSION['role'] = 'mitra'; 

                // Masukkan data kendaraan
                $stmt_veh = $pdo->prepare("
                    INSERT INTO vehicles (user_id, name, tahun, type, lokasi, kontak_mitra, transmisi, price, icon, image) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt_veh->execute([
                    $_SESSION['user_id'], 
                    $merek, 
                    $tahun, 
                    $jenis_kendaraan, 
                    $lokasi, 
                    $telepon, 
                    $transmisi, 
                    $harga_sewa, 
                    $icon,
                    $nama_file_baru
                ]);

                $pdo->commit();
                $success = true; 

            } catch (Exception $e) {
                $pdo->rollBack();
                // Hapus gambar jika query database gagal
                if(!empty($nama_file_baru) && file_exists('uploads/' . $nama_file_baru)) {
                    unlink('uploads/' . $nama_file_baru);
                }
                $error = "Terjadi kesalahan sistem: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadi Mitra - RentGo</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-main: #050A14; --bg-card: #0f172a; --input-bg: #080f1c; --border: #1e293b;
            --primary: #3B82F6; --text-light: #F8FAFC; --text-muted: #94A3B8;
            --success: #22C55E; --danger: #EF4444;
            
            --glass-gradient: linear-gradient(135deg, rgba(30, 41, 59, 0.85) 0%, rgba(15, 23, 42, 0.95) 100%);
            --glass-border: 1px solid rgba(255, 255, 255, 0.08);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-light); min-height: 100vh; background-image: radial-gradient(circle at 50% 0%, rgba(59, 130, 246, 0.05) 0%, transparent 50%); }
        
        .navbar-wrapper { padding: 20px 5% 0 5%; }
        .navbar { 
            display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; 
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

        .main-container { max-width: 900px; margin: 0 auto; padding: 60px 5% 80px 5%; }
        .section-header { text-align: center; margin-bottom: 40px; }
        .section-header h1 { font-size: 32px; font-weight: 700; margin-bottom: 10px; }
        .section-header p { font-size: 14px; color: var(--text-muted); }

        .form-wrapper { background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; padding: 40px; margin: 0 auto; box-shadow: 0 20px 50px rgba(0,0,0,0.3); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }

        .form-group { margin-bottom: 5px; }
        .form-group label { display: block; font-size: 12px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px; }
        
        .form-control { width: 100%; background: var(--input-bg); border: 1px solid var(--border); color: var(--text-light); padding: 14px 15px; border-radius: 10px; font-size: 14px; outline: none; transition: 0.3s; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 10px rgba(59, 130, 246, 0.2); }
        .form-control::placeholder { color: rgba(255, 255, 255, 0.2); }
        
        /* Styling khusus input file */
        input[type="file"].form-control { padding: 10px; }
        input[type="file"].form-control::file-selector-button { background: rgba(59, 130, 246, 0.2); border: none; color: var(--primary); padding: 8px 15px; border-radius: 6px; margin-right: 15px; cursor: pointer; font-weight: 600; transition: 0.3s; }
        input[type="file"].form-control::file-selector-button:hover { background: rgba(59, 130, 246, 0.3); }

        select.form-control { appearance: none; background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2394A3B8%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E"); background-repeat: no-repeat; background-position: right 15px top 50%; background-size: 10px auto; cursor: pointer; }
        select.form-control option { background: var(--bg-main); color: var(--text-light); }

        .btn-submit { width: 100%; background: var(--primary); color: white; border: none; padding: 16px; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.3s; margin-top: 25px; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3); }
        .btn-submit:hover { background: #2563EB; transform: translateY(-2px); }

        .alert { padding: 15px; border-radius: 10px; margin-bottom: 25px; font-size: 14px; text-align: center; }
        .alert-success { background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); color: #86efac; }
        .alert-danger { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5; }
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
                <a href="pesanan.php">Pesanan Saya</a>
                <a href="mitra.php" class="active">Jadi Mitra</a>
            </div>
            <div class="nav-actions">
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </nav>
    </div>

    <main class="main-container">
        
        <?php if($success): ?>
            <div class="alert alert-success">
                <strong>Pendaftaran Berhasil!</strong><br>
                Kendaraan Anda telah ditambahkan ke sistem RentGo dan siap disewakan.
            </div>
        <?php endif; ?>
        <?php if($error != ""): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="section-header">
            <h1>Daftar Menjadi Mitra</h1>
            <p>Untuk saat ini kami menerima pendaftaran Mobil dan Motor (Minimal Tahun 2018).</p>
        </div>

        <div class="form-wrapper">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Lengkap (Nama ID)</label>
                        <input type="text" name="nama_mitra" class="form-control" required placeholder="Masukkan Nama Lengkap">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email_mitra" class="form-control" required placeholder="Masukkan Email">
                    </div>
                    <div class="form-group">
                        <label>Nomor Telepon / WhatsApp</label>
                        <input type="tel" name="telepon" class="form-control" required placeholder="Masukkan Nomor Telepon">
                    </div>
                    <div class="form-group">
                        <label>Jenis Kendaraan</label>
                        <select name="jenis_kendaraan" class="form-control" required>
                            <option value="mobil">Mobil</option>
                            <option value="motor">Motor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Merek Kendaraan</label>
                        <input type="text" name="merek" class="form-control" required placeholder="Contoh: Toyota Avanza">
                    </div>
                    <div class="form-group">
                        <label>Tahun Keluaran (Minimal 2018)</label>
                        <input type="number" name="tahun" class="form-control" required min="2018" max="2026" placeholder="Contoh: 2022">
                    </div>
                    <div class="form-group">
                        <label>Lokasi (Alamat Kota)</label>
                        <input type="text" name="lokasi" class="form-control" required placeholder="Contoh: Yogyakarta">
                    </div>
                    <div class="form-group">
                        <label>Transmisi</label>
                        <select name="transmisi" class="form-control" required>
                            <option value="Automatic">Automatic</option>
                            <option value="Manual">Manual</option>
                        </select>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1; margin-top: 10px;">
                        <label>Foto Kendaraan (Format JPG/PNG, Maks 3MB)</label>
                        <input type="file" name="foto_kendaraan" class="form-control" accept="image/jpeg, image/png, image/jpg" required>
                    </div>
                </div>

                <button type="submit" name="daftar_mitra" class="btn-submit">
                    Daftar Sekarang
                </button>
            </form>
        </div>
    </main>
</body>
</html>