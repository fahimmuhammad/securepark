<?php
// ============================================================
// SecurePark — Database Setup Script
// Visit: http://localhost/securepark/setup.php
// Run this ONCE to create the DB and seed demo data.
// ============================================================

$host = 'localhost';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) die('MySQL connection failed: ' . $conn->connect_error);

$sql_file = __DIR__ . '/securepark.sql';
if (!file_exists($sql_file)) die('securepark.sql not found in project root.');

$sql = file_get_contents($sql_file);

// Split on semicolons, skip empty statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

$errors = [];
$ok     = 0;

foreach ($statements as $stmt) {
    if (!$stmt) continue;
    if (!$conn->query($stmt)) {
        $errors[] = htmlspecialchars($conn->error) . '<br><small>' . htmlspecialchars(substr($stmt, 0, 120)) . '...</small>';
    } else {
        $ok++;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SecurePark Setup</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/securepark/assets/css/style.css">
  <style>body{display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px}</style>
</head>
<body>
<div style="max-width:600px;width:100%">
  <div class="section-card">
    <div class="section-card-header">
      <div class="d-flex align-items-center gap-2">
        <div style="width:36px;height:36px;background:var(--gradient);border-radius:9px;display:flex;align-items:center;justify-content:center;color:#fff"><i class="fas fa-shield-halved"></i></div>
        <div class="section-card-title">SecurePark Setup</div>
      </div>
    </div>
    <div class="section-card-body">
      <?php if (empty($errors)): ?>
      <div style="text-align:center;padding:20px 0">
        <div style="width:64px;height:64px;background:rgba(16,185,129,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:var(--success);margin:0 auto 16px">
          <i class="fas fa-circle-check"></i>
        </div>
        <h4 style="color:var(--success);margin-bottom:8px">Setup Complete!</h4>
        <p style="color:var(--text-secondary);font-size:0.9rem;margin-bottom:24px">
          Database <strong>securepark_db</strong> created and seeded with <?= $ok ?> statements.
        </p>
        <div style="background:rgba(124,58,237,0.08);border:1px dashed rgba(124,58,237,0.3);border-radius:var(--radius-md);padding:16px;margin-bottom:24px;text-align:left;font-size:0.85rem;color:var(--text-secondary)">
          <strong style="color:var(--primary-light)">Demo Credentials:</strong><br><br>
          <i class="fas fa-crown me-2" style="color:var(--warning)"></i><strong>Admin:</strong> admin@securepark.com / admin123<br>
          <i class="fas fa-user me-2 mt-1" style="color:var(--accent)"></i><strong>User:</strong> john@example.com / user123
        </div>
        <div class="d-flex gap-3 justify-content-center">
          <a href="/securepark/index.php" class="btn-primary-custom"><i class="fas fa-globe"></i> View Website</a>
          <a href="/securepark/login.php" class="btn-outline-custom"><i class="fas fa-right-to-bracket"></i> Login</a>
        </div>
      </div>
      <?php else: ?>
      <div style="text-align:center;margin-bottom:20px">
        <div style="width:64px;height:64px;background:rgba(245,158,11,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:var(--warning);margin:0 auto 12px">
          <i class="fas fa-triangle-exclamation"></i>
        </div>
        <p style="color:var(--text-secondary);font-size:0.9rem"><?= $ok ?> statements ran, <?= count($errors) ?> had errors (usually safe to ignore if DB already exists).</p>
        <a href="/securepark/index.php" class="btn-primary-custom mt-3"><i class="fas fa-globe"></i> Continue to Website</a>
      </div>
      <div style="max-height:300px;overflow-y:auto">
        <?php foreach ($errors as $err): ?>
        <div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);border-radius:var(--radius-sm);padding:10px 14px;margin-bottom:8px;font-size:0.78rem;color:var(--danger)"><?= $err ?></div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
