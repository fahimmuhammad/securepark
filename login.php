<?php
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? '/securepark/admin/index.php' : '/securepark/dashboard.php'));
    exit;
}
$tab = isset($_GET['tab']) && $_GET['tab'] === 'register' ? 'register' : 'login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SecurePark — Sign In</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/securepark/assets/css/style.css">
  <script>!function(){var t=localStorage.getItem('spTheme');if(t)document.documentElement.dataset.theme=t;}();</script>
</head>
<body>
<div id="phpFlashData"
     data-type="<?= htmlspecialchars($_SESSION['flash_type'] ?? '') ?>"
     data-msg="<?= htmlspecialchars($_SESSION['flash_msg']  ?? '') ?>">
</div>
<?php unset($_SESSION['flash_type'], $_SESSION['flash_msg']); ?>

<button class="theme-toggle login-theme-toggle" id="themeToggle" title="Toggle theme" aria-label="Toggle dark/light mode">
  <i class="fas fa-sun"></i>
</button>
<div class="login-page">
  <!-- Left Branding Panel -->
  <div class="login-left d-none d-lg-flex">
    <div class="position-relative text-center" style="max-width:420px">
      <a href="/securepark/" class="d-flex align-items-center justify-content-center gap-2 mb-4 text-white text-decoration-none">
        <div style="width:44px;height:44px;background:var(--gradient);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem"><i class="fas fa-shield-halved"></i></div>
        <span style="font-family:'Poppins',sans-serif;font-weight:700;font-size:1.5rem">SecurePark</span>
      </a>

      <h2 class="mb-3" style="font-size:2.2rem;font-weight:800;line-height:1.2">
        Park Smarter,<br><span class="text-gradient">Live Better</span>
      </h2>
      <p style="color:var(--text-secondary);font-size:1rem;line-height:1.7;margin-bottom:2rem">
        Join thousands of drivers who save time and reduce parking stress every day with SecurePark.
      </p>

      <!-- Mini stats -->
      <div class="d-flex justify-content-center gap-4">
        <?php foreach ([['500+','Spots'],['10k+','Users'],['24/7','Support']] as [$n,$l]): ?>
        <div class="text-center">
          <div style="font-family:'Poppins',sans-serif;font-size:1.5rem;font-weight:700;background:var(--gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text"><?= $n ?></div>
          <div style="font-size:0.78rem;color:var(--text-muted)"><?= $l ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Decorative parking grid -->
      <div class="mt-5 p-4" style="background:var(--glass-bg);border:1px solid var(--glass-border);border-radius:var(--radius-lg)">
        <div style="font-size:0.8rem;color:var(--text-secondary);margin-bottom:12px;text-align:left"><i class="fas fa-map me-2" style="color:var(--primary-light)"></i>Live Availability</div>
        <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:6px">
          <?php
          $statuses = ['av','oc','av','av','rs','av','av','ev','oc','av','av','av'];
          foreach($statuses as $s): ?>
          <div class="hp-slot <?= $s ?>" style="height:32px;border-radius:4px"></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Right Auth Panel -->
  <div class="login-right">
    <div style="max-width:380px;width:100%;margin:0 auto">
      <!-- Mobile logo -->
      <a href="/securepark/" class="d-flex d-lg-none align-items-center gap-2 mb-4 text-white text-decoration-none">
        <div style="width:36px;height:36px;background:var(--gradient);border-radius:10px;display:flex;align-items:center;justify-content:center"><i class="fas fa-shield-halved"></i></div>
        <span style="font-family:'Poppins',sans-serif;font-weight:700;font-size:1.2rem">SecurePark</span>
      </a>

      <div class="auth-tabs">
        <div class="auth-tab <?= $tab==='login'?'active':'' ?>" data-target="loginForm">Sign In</div>
        <div class="auth-tab <?= $tab==='register'?'active':'' ?>" data-target="registerForm">Create Account</div>
      </div>

      <!-- Login Form -->
      <div class="auth-form <?= $tab==='login'?'active':'' ?>" id="loginForm">
        <p style="color:var(--text-secondary);font-size:0.88rem;margin-bottom:20px">Welcome back! Sign in to manage your parking.</p>
        <form id="loginFormEl" action="/securepark/api/login.php" method="POST">
          <div class="form-group-custom">
            <label class="form-label-custom">Email Address</label>
            <div class="input-icon-wrap">
              <i class="fas fa-envelope"></i>
              <input type="email" name="email" class="form-input-custom" placeholder="you@example.com" required autocomplete="email">
            </div>
          </div>
          <div class="form-group-custom">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <label class="form-label-custom mb-0">Password</label>
              <a href="#" style="font-size:0.78rem;color:var(--primary-light)">Forgot password?</a>
            </div>
            <div class="input-icon-wrap">
              <i class="fas fa-lock"></i>
              <input type="password" name="password" id="loginPass" class="form-input-custom" placeholder="••••••••" required autocomplete="current-password">
              <button type="button" onclick="togglePass('loginPass','loginPassEye')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer">
                <i class="fas fa-eye" id="loginPassEye"></i>
              </button>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2 mb-4">
            <input type="checkbox" name="remember" id="rememberMe" style="accent-color:var(--primary)">
            <label for="rememberMe" style="font-size:0.85rem;color:var(--text-secondary);cursor:pointer">Remember me</label>
          </div>
          <button type="submit" class="btn-primary-custom w-100 justify-content-center">
            <i class="fas fa-right-to-bracket"></i> Sign In
          </button>
          <div class="divider-or">or continue with</div>
          <div class="d-flex gap-3">
            <button type="button" class="btn-outline-custom flex-1 justify-content-center" style="flex:1;font-size:0.85rem" onclick="Toast.info('OAuth coming soon!')">
              <i class="fab fa-google"></i> Google
            </button>
            <button type="button" class="btn-outline-custom flex-1 justify-content-center" style="flex:1;font-size:0.85rem" onclick="Toast.info('OAuth coming soon!')">
              <i class="fab fa-github"></i> GitHub
            </button>
          </div>
          <p style="text-align:center;margin-top:20px;font-size:0.85rem;color:var(--text-muted)">
            Don't have an account?
            <a href="#" style="color:var(--primary-light);font-weight:500" onclick="switchTab('registerForm')">Create one free</a>
          </p>
        </form>
      </div>

      <!-- Register Form -->
      <div class="auth-form <?= $tab==='register'?'active':'' ?>" id="registerForm">
        <p style="color:var(--text-secondary);font-size:0.88rem;margin-bottom:20px">Create your free account in seconds.</p>
        <form action="/securepark/api/register.php" method="POST">
          <div class="form-group-custom">
            <label class="form-label-custom">Full Name</label>
            <div class="input-icon-wrap">
              <i class="fas fa-user"></i>
              <input type="text" name="name" class="form-input-custom" placeholder="John Smith" required autocomplete="name">
            </div>
          </div>
          <div class="form-group-custom">
            <label class="form-label-custom">Email Address</label>
            <div class="input-icon-wrap">
              <i class="fas fa-envelope"></i>
              <input type="email" name="email" class="form-input-custom" placeholder="you@example.com" required autocomplete="email">
            </div>
          </div>
          <div class="row g-3">
            <div class="col-6">
              <div class="form-group-custom">
                <label class="form-label-custom">Phone</label>
                <div class="input-icon-wrap">
                  <i class="fas fa-phone"></i>
                  <input type="tel" name="phone" class="form-input-custom" placeholder="+1 555 0100" autocomplete="tel">
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="form-group-custom">
                <label class="form-label-custom">Vehicle No.</label>
                <div class="input-icon-wrap">
                  <i class="fas fa-car"></i>
                  <input type="text" name="vehicle_number" class="form-input-custom" placeholder="NYC-1234">
                </div>
              </div>
            </div>
          </div>
          <div class="form-group-custom">
            <label class="form-label-custom">Password</label>
            <div class="input-icon-wrap">
              <i class="fas fa-lock"></i>
              <input type="password" name="password" id="regPass" class="form-input-custom" placeholder="Min. 8 characters" required autocomplete="new-password" minlength="8">
              <button type="button" onclick="togglePass('regPass','regPassEye')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer">
                <i class="fas fa-eye" id="regPassEye"></i>
              </button>
            </div>
          </div>
          <div class="form-group-custom">
            <label class="form-label-custom">Confirm Password</label>
            <div class="input-icon-wrap">
              <i class="fas fa-shield-check"></i>
              <input type="password" name="confirm_password" class="form-input-custom" placeholder="••••••••" required autocomplete="new-password">
            </div>
          </div>
          <div class="d-flex align-items-start gap-2 mb-4">
            <input type="checkbox" name="terms" id="terms" style="accent-color:var(--primary);margin-top:3px" required>
            <label for="terms" style="font-size:0.82rem;color:var(--text-secondary);cursor:pointer">
              I agree to the <a href="#" style="color:var(--primary-light)">Terms of Service</a> and <a href="#" style="color:var(--primary-light)">Privacy Policy</a>
            </label>
          </div>
          <button type="submit" class="btn-primary-custom w-100 justify-content-center">
            <i class="fas fa-user-plus"></i> Create Account
          </button>
          <p style="text-align:center;margin-top:20px;font-size:0.85rem;color:var(--text-muted)">
            Already have an account?
            <a href="#" style="color:var(--primary-light);font-weight:500" onclick="switchTab('loginForm')">Sign in</a>
          </p>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="/securepark/assets/js/main.js"></script>
<script>
function togglePass(inputId, eyeId) {
  const input = document.getElementById(inputId);
  const eye   = document.getElementById(eyeId);
  if (input.type === 'password') { input.type = 'text'; eye.className = 'fas fa-eye-slash'; }
  else { input.type = 'password'; eye.className = 'fas fa-eye'; }
}
function switchTab(targetId) {
  document.querySelectorAll('.auth-tab').forEach(t => {
    t.classList.toggle('active', t.dataset.target === targetId);
  });
  document.querySelectorAll('.auth-form').forEach(f => {
    f.classList.toggle('active', f.id === targetId);
  });
  return false;
}
</script>
</body>
</html>
