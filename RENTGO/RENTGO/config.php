<?php
// config.php
session_start();

// Kredensial Database (Sesuaikan dengan XAMPP/Laragon Anda)
$host = 'localhost';
$dbname = 'rentgo_db';
$username = 'root';
$password = ''; // Biasanya kosong di XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}

// Fungsi Bantuan (Sama seperti sebelumnya)
function is_login() {
    return isset($_SESSION['user_id']) && $_SESSION['role'] !== 'guest';
}

function is_logged_in_or_guest() {
    return isset($_SESSION['role']);
}
?>