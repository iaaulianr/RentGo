<?php 
require_once 'config.php';

if(isset($_SESSION['role']) && $_SESSION['role'] !== 'guest') {
    header("Location: dashboard.php");
    exit;
}

if(isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Email atau Password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - RentGo</title>
    
    <!-- Font Poppins -->
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
            --error-bg: rgba(239, 68, 68, 0.2);
            --error-border: #EF4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif; 
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-light);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden; 
        }

        /* === EFEK BACKGROUND GLOWING === */
        .glow-bg-1 {
            position: absolute;
            top: 20%;
            left: 20%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.12) 0%, transparent 70%);
            filter: blur(40px);
            z-index: 1;
        }

        .glow-bg-2 {
            position: absolute;
            bottom: 10%;
            right: 20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(101, 181, 245, 0.08) 0%, transparent 70%);
            filter: blur(40px);
            z-index: 1;
        }

        /* === CARD LOGIN (UKURAN DIKECILKAN) === */
        .login-card {
            background: var(--bg-card);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px; /* Sedikit lebih kecil dari 24px */
            padding: 30px 35px; /* Padding dikurangi agar tidak umpek2an */
            width: 100%;
            max-width: 360px; /* Lebar maksimal dikecilkan drastis (sebelumnya 420px) */
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 10;
        }

        /* Logo di atas form */
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            color: var(--text-light);
            font-weight: 700;
            font-size: 20px; /* Dikecilkan dari 24px */
            margin-bottom: 20px; /* Dikurangi dari 30px */
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

        .login-card h2 {
            font-size: 20px; /* Dikecilkan dari 24px */
            margin-bottom: 5px;
            font-weight: 600;
            text-align: center;
        }

        .login-card .subtitle {
            font-size: 12px; /* Dikecilkan */
            color: var(--text-gray);
            text-align: center;
            margin-bottom: 20px; /* Dikurangi dari 30px */
        }

        /* Form Group */
        .form-group {
            margin-bottom: 15px; /* Dikurangi dari 20px */
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-gray);
            font-size: 11px; /* Dikecilkan */
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px; /* Padding dikurangi agar tidak terlalu tinggi */
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: rgba(255, 255, 255, 0.03);
            color: var(--text-light);
            font-size: 13px; /* Dikecilkan */
            outline: none;
            transition: 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            background: rgba(59, 130, 246, 0.05);
            box-shadow: 0 0 8px rgba(59, 130, 246, 0.2);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.2);
        }

        /* Tombol Masuk */
        .btn-primary {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 11px; /* Dikurangi */
            border-radius: 10px;
            border: none;
            background: var(--primary-blue);
            color: white;
            font-size: 13px; /* Dikecilkan */
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            margin-top: 10px;
            text-decoration: none;
        }

        .btn-primary:hover {
            background: #2563EB;
            transform: translateY(-1px);
        }

        /* Link Navigasi Bawah */
        .bottom-links {
            margin-top: 20px;
            text-align: center;
            font-size: 12px; /* Dikecilkan */
            color: var(--text-gray);
        }

        .bottom-links a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 600;
        }

        .bottom-links a:hover {
            text-decoration: underline;
        }

        .back-home {
            display: block;
            text-align: center;
            margin-top: 15px; /* Dikurangi */
            font-size: 11px; /* Dikecilkan */
            color: var(--text-gray);
            text-decoration: none;
            transition: 0.3s;
        }
        .back-home:hover {
            color: var(--text-light);
        }

        /* Alert Error */
        .alert-danger {
            background: var(--error-bg);
            border: 1px solid var(--error-border);
            color: #FCA5A5;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Efek Cahaya Background -->
    <div class="glow-bg-1"></div>
    <div class="glow-bg-2"></div>

    <!-- Card Login -->
    <div class="login-card">
        
        <!-- Logo -->
        <a href="index.php" class="logo-container">
            <div class="logo-icon"></div>
            RentGo.
        </a>

        <h2>Selamat Datang!</h2>
        <p class="subtitle">Silakan masuk untuk melanjutkan.</p>
        
        <?php if(isset($error)) echo "<div class='alert-danger'>$error</div>"; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Alamat Email</label>
                <input type="email" name="email" class="form-control" placeholder="Masukkan email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
            </div>
            
            <button type="submit" name="login" class="btn-primary">
                Masuk Sekarang
            </button>
        </form>

        <div class="bottom-links">
            Belum punya akun? <a href="register.php">Daftar</a>
        </div>

        <a href="index.php" class="back-home">&larr; Kembali</a>
    </div>

</body>
</html>