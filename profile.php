<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
requireLogin();

$user        = getCurrentUser();
$active_page = 'profile';
$uid         = (int)$user['id'];

$db_user = $conn->query("SELECT * FROM users WHERE id = $uid")->fetch_assoc();
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', trim($db_user['name'])), 0, 2)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile — SecurePark</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/securepark/assets/css/style.css">
</head>
<body>
<div id="phpFlashData"
     data-type="<?= htmlspecialchars($_SESSION['flash_type'] ?? '') ?>"
     data-msg="<?= htmlspecialchars($_SESSION['flash_msg']  ?? '') ?>">
</div>
<?php unset($_SESSION['flash_type'], $_SESSION['flash_msg']); ?>

<div class="dash-layout">
  <?php include __DIR__ . '/includes/dash_sidebar.php'; ?>
  <div class="dash-content" id="dashContent">
    <?php include __DIR__ . '/includes/dash_nav.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">My Profile</h1>
          <div class="page-breadcrumb"><a href="/securepark/dashboard.php">Dashboard</a> / Profile</div>
        </div>
      </div>

      <div class="row g-4">
        <!-- Profile Card -->
        <div class="col-lg-4">
          <div class="section-card text-center">
            <div class="section-card-body" style="padding:36px 24px">
              <div class="profile-avatar-lg mx-auto mb-3"><?= $initials ?></div>
              <h4 class="mb-1"><?= htmlspecialchars($db_user['name']) ?></h4>
              <p style="color:var(--text-muted);font-size:0.88rem"><?= htmlspecialchars($db_user['email']) ?></p>
              <span class="status-badge badge-active mb-4 d-inline-flex">
                <?= $db_user['role'] === 'admin' ? 'Administrator' : 'Member' ?>
              </span>
              <div class="w-100">
                <?php $fields = [
                  ['fas fa-phone',      'Phone',          $db_user['phone']          ?? '—'],
                  ['fas fa-car',        'Vehicle Number', $db_user['vehicle_number'] ?? '—'],
                  ['fas fa-calendar',   'Member Since',   date('M Y', strtotime($db_user['created_at']))],
                ]; foreach ($fields as [$icon,$label,$val]): ?>
                <div class="d-flex align-items-center gap-3 py-2" style="border-bottom:1px solid var(--border);font-size:0.85rem;text-align:left">
                  <i class="<?= $icon ?>" style="color:var(--primary-light);width:16px;text-align:center"></i>
                  <div style="flex:1">
                    <div style="font-size:0.72rem;color:var(--text-muted)"><?= $label ?></div>
                    <div class="fw-500"><?= htmlspecialchars($val) ?></div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Edit Forms -->
        <div class="col-lg-8">
          <!-- Profile Info Form -->
          <div class="section-card mb-4">
            <div class="section-card-header">
              <div class="section-card-title"><i class="fas fa-user-pen me-2" style="color:var(--primary-light)"></i>Edit Profile</div>
            </div>
            <div class="section-card-body">
              <form id="profileForm" action="/securepark/api/update_profile.php" method="POST">
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="form-group-custom">
                      <label class="form-label-custom">Full Name</label>
                      <div class="input-icon-wrap">
                        <i class="fas fa-user"></i>
                        <input type="text" name="name" class="form-input-custom" value="<?= htmlspecialchars($db_user['name']) ?>" required>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group-custom">
                      <label class="form-label-custom">Email Address</label>
                      <div class="input-icon-wrap">
                        <i class="fas fa-envelope"></i>
                        <input type="email" class="form-input-custom" value="<?= htmlspecialchars($db_user['email']) ?>" disabled>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group-custom">
                      <label class="form-label-custom">Phone Number</label>
                      <div class="input-icon-wrap">
                        <i class="fas fa-phone"></i>
                        <input type="tel" name="phone" class="form-input-custom" value="<?= htmlspecialchars($db_user['phone'] ?? '') ?>" placeholder="+1 555 0100">
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group-custom">
                      <label class="form-label-custom">Vehicle Number</label>
                      <div class="input-icon-wrap">
                        <i class="fas fa-car"></i>
                        <input type="text" name="vehicle_number" class="form-input-custom" value="<?= htmlspecialchars($db_user['vehicle_number'] ?? '') ?>" placeholder="NYC-1234">
                      </div>
                    </div>
                  </div>
                </div>
                <button type="submit" class="btn-primary-custom btn-sm-custom">
                  <i class="fas fa-floppy-disk"></i> Save Changes
                </button>
              </form>
            </div>
          </div>

          <!-- Password Change Form -->
          <div class="section-card">
            <div class="section-card-header">
              <div class="section-card-title"><i class="fas fa-key me-2" style="color:var(--warning)"></i>Change Password</div>
            </div>
            <div class="section-card-body">
              <form id="passwordForm" action="/securepark/api/update_profile.php" method="POST">
                <input type="hidden" name="change_password" value="1">
                <div class="row g-3">
                  <div class="col-12">
                    <div class="form-group-custom">
                      <label class="form-label-custom">Current Password</label>
                      <div class="input-icon-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="current_password" class="form-input-custom" placeholder="Your current password" required>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group-custom">
                      <label class="form-label-custom">New Password</label>
                      <div class="input-icon-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="new_password" class="form-input-custom" placeholder="Min. 8 characters" required minlength="8">
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group-custom">
                      <label class="form-label-custom">Confirm New Password</label>
                      <div class="input-icon-wrap">
                        <i class="fas fa-shield-check"></i>
                        <input type="password" name="confirm_password" class="form-input-custom" placeholder="Repeat new password" required>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-3 mt-1">
                  <button type="submit" class="btn-accent btn-sm-custom">
                    <i class="fas fa-key"></i> Update Password
                  </button>
                  <span style="font-size:0.8rem;color:var(--text-muted)">Use a strong password with at least 8 characters</span>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="/securepark/assets/js/main.js"></script>
<script>
// AJAX form submit for profile
['profileForm','passwordForm'].forEach(id => {
  const form = document.getElementById(id);
  if (!form) return;
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = form.querySelector('[type=submit]');
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    btn.disabled = true;
    try {
      const res  = await fetch(form.action, { method:'POST', body: new FormData(form) });
      const data = await res.json();
      if (data.success) Toast.success(data.message || 'Updated successfully!');
      else              Toast.error(data.error   || 'Update failed.');
    } catch { Toast.error('Network error.'); }
    btn.innerHTML = orig;
    btn.disabled  = false;
  });
});
</script>
</body>
</html>
