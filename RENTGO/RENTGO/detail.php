<?php 
require_once 'config.php';
if(!is_login()) { header("Location: dashboard.php"); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
$stmt->execute([$id]);
$v = $stmt->fetch();
if(!$v) { header("Location: dashboard.php"); exit; }

include 'includes/header.php'; 
?>
<div class="card" style="max-width: 600px; margin: 0 auto; text-align: center;">
    <div style="font-size: 100px; margin-bottom: 20px;"><?= $v['icon'] ?></div>
    <h1 style="color: var(--light-blue);"><?= htmlspecialchars($v['name']) ?></h1>
    <p style="color: var(--text-muted); margin-bottom: 20px; text-transform: uppercase;"><?= $v['type'] ?></p>
    <h3 style="margin-bottom: 20px;">Rp <?= number_format($v['price'], 0, ',', '.') ?> / hari</h3>
    
    <a href="checkout.php?id=<?= $v['id'] ?>" class="btn btn-primary" style="width: 100%;">Lanjut ke Pemesanan</a>
</div>
<?php include 'includes/footer.php'; ?>