<?php
require_once __DIR__ . '/includes/auth.php';
$logged_in = isLoggedIn();
$is_admin  = isAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SecurePark — Smart Parking for Modern Cities</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/securepark/assets/css/style.css">
  <script>!function(){var t=localStorage.getItem('spTheme');if(t)document.documentElement.dataset.theme=t;}();</script>
</head>
<body>

<!-- ── Navbar ─────────────────────────────────────────────── -->
<nav class="navbar-custom" id="mainNav">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between w-100">
      <a href="/securepark/" class="nav-brand text-white">
        <span class="brand-icon"><i class="fas fa-shield-halved"></i></span>
        SecurePark
      </a>

      <div class="d-none d-lg-flex align-items-center gap-1">
        <a href="#features"   class="nav-link">Features</a>
        <a href="#how-it-works" class="nav-link">How It Works</a>
        <a href="#pricing"    class="nav-link">Pricing</a>
        <a href="#contact"    class="nav-link">Contact</a>
      </div>

      <div class="d-flex align-items-center gap-2">
        <button class="theme-toggle" id="themeToggle" title="Toggle theme" aria-label="Toggle dark/light mode">
          <i class="fas fa-sun"></i>
        </button>
        <?php if ($logged_in): ?>
          <a href="<?= $is_admin ? '/securepark/admin/index.php' : '/securepark/dashboard.php' ?>" class="btn-primary-custom btn-sm-custom">
            <i class="fas fa-gauge-high"></i> Dashboard
          </a>
        <?php else: ?>
          <a href="/securepark/login.php" class="btn-outline-custom btn-sm-custom d-none d-sm-inline-flex">Log In</a>
          <a href="/securepark/login.php?tab=register" class="btn-primary-custom btn-sm-custom">Get Started</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<!-- ── Hero ───────────────────────────────────────────────── -->
<section class="hero-section" id="home">
  <div class="hero-bg"></div>
  <div class="hero-grid-lines"></div>
  <div class="container position-relative">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="hero-badge fade-up">
          <i class="fas fa-bolt"></i>
          Real-Time Parking Availability
        </div>
        <h1 class="hero-title fade-up">
          Smart Parking<br>
          for <span class="text-gradient">Modern Cities</span>
        </h1>
        <p class="hero-desc fade-up">
          Find, book, and manage your parking spot in seconds. Say goodbye to the stress of searching for parking — SecurePark makes it effortless.
        </p>
        <div class="hero-actions fade-up">
          <a href="/securepark/<?= $logged_in ? 'book.php' : 'login.php?tab=register' ?>" class="btn-primary-custom">
            <i class="fas fa-square-parking"></i> Book a Spot Now
          </a>
          <a href="#how-it-works" class="btn-outline-custom">
            <i class="fas fa-play-circle"></i> How It Works
          </a>
        </div>
        <div class="hero-stats fade-up">
          <div class="hero-stat-item">
            <div class="hero-stat-num" data-counter="500" data-suffix="+">0+</div>
            <div class="hero-stat-lbl">Parking Spots</div>
          </div>
          <div class="hero-stat-item">
            <div class="hero-stat-num" data-counter="10" data-suffix="k+" data-decimals="0">0k+</div>
            <div class="hero-stat-lbl">Happy Users</div>
          </div>
          <div class="hero-stat-item">
            <div class="hero-stat-num" data-counter="99.9" data-suffix="%" data-decimals="1">0%</div>
            <div class="hero-stat-lbl">Uptime</div>
          </div>
          <div class="hero-stat-item">
            <div class="hero-stat-num" data-counter="24" data-suffix="/7">0/7</div>
            <div class="hero-stat-lbl">Support</div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="hero-visual">
          <!-- Floating chips -->
          <div class="hero-float hero-float-1">
            <i class="fas fa-circle-check"></i>
            <span>Slot A12 Available</span>
          </div>
          <div class="hero-float hero-float-2">
            <i class="fas fa-bolt"></i>
            <span>EV Charging Ready</span>
          </div>

          <!-- Parking widget card -->
          <div class="hero-parking-card">
            <div class="hp-header">
              <div class="hp-title"><i class="fas fa-map me-2" style="color:var(--primary-light)"></i>Zone A — Ground Floor</div>
              <div class="hp-live"><div class="hp-live-dot"></div> Live</div>
            </div>
            <div class="hp-grid">
              <div class="hp-slot av">A01</div><div class="hp-slot oc">A02</div>
              <div class="hp-slot av">A03</div><div class="hp-slot av">A04</div>
              <div class="hp-slot rs">A05</div><div class="hp-slot av">A06</div>
              <div class="hp-slot av">A07</div><div class="hp-slot oc">A08</div>
              <div class="hp-slot av">A09</div><div class="hp-slot av">A10</div>
              <div class="hp-slot av">A11</div><div class="hp-slot av">A12</div>
              <div class="hp-slot oc">A13</div><div class="hp-slot av">A14</div>
              <div class="hp-slot av">A15</div><div class="hp-slot av">A16</div>
              <div class="hp-slot rs">A17</div><div class="hp-slot ev">A18</div>
              <div class="hp-slot ev">A19</div><div class="hp-slot av">A20</div>
            </div>
            <div class="hp-legend">
              <div class="hp-leg-item"><div class="hp-leg-dot" style="background:var(--success)"></div>Available</div>
              <div class="hp-leg-item"><div class="hp-leg-dot" style="background:var(--danger)"></div>Occupied</div>
              <div class="hp-leg-item"><div class="hp-leg-dot" style="background:var(--warning)"></div>Reserved</div>
              <div class="hp-leg-item"><div class="hp-leg-dot" style="background:var(--accent)"></div>EV</div>
            </div>
            <button class="hp-cta" onclick="window.location.href='/securepark/<?= $logged_in ? 'book.php' : 'login.php' ?>'">
              <i class="fas fa-ticket me-2"></i>Book This Spot
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── Stats ──────────────────────────────────────────────── -->
<section class="stats-section">
  <div class="container">
    <div class="row g-4">
      <?php $stats = [
        ['500+','Parking Spots Available','fas fa-square-parking','icon-purple'],
        ['10k+','Registered Users','fas fa-users','icon-cyan'],
        ['50k+','Successful Bookings','fas fa-calendar-check','icon-green'],
        ['4','Parking Zones','fas fa-map','icon-amber'],
      ]; foreach ($stats as [$num,$lbl,$icon,$cls]): ?>
      <div class="col-6 col-md-3 fade-up">
        <div class="stat-card d-flex align-items-center gap-3">
          <div class="stat-icon-box <?= $cls ?>"><i class="<?= $icon ?>"></i></div>
          <div>
            <div class="stat-number"><?= $num ?></div>
            <div class="stat-label"><?= $lbl ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Features ───────────────────────────────────────────── -->
<section class="features-section" id="features">
  <div class="container">
    <div class="text-center mb-5">
      <div class="hero-badge d-inline-flex mb-3"><i class="fas fa-star"></i> Key Features</div>
      <h2 class="section-title fade-up">Everything You Need for<br><span class="text-gradient">Hassle-Free Parking</span></h2>
      <p class="section-subtitle mx-auto fade-up">SecurePark combines cutting-edge technology with intuitive design to deliver the ultimate parking experience.</p>
    </div>
    <div class="row g-4">
      <?php $features = [
        ['fas fa-radar',         'Real-Time Availability',  'See live parking availability across all zones instantly. No more circling the lot — know before you go.'],
        ['fas fa-bolt-lightning','Instant Booking',         'Reserve your spot in under 60 seconds with our streamlined booking flow and instant confirmation.'],
        ['fas fa-lock',          'Secure Payments',         'Bank-grade encryption protects your payment data. Multiple payment options for your convenience.'],
        ['fas fa-clock-rotate-left','Flexible Scheduling',  'Book for exact hours or extend your stay on the fly. Our flexible system adapts to your needs.'],
        ['fas fa-car-side',      'Multi-Vehicle Support',   'Manage multiple vehicles from one account. Standard, SUV, compact, EV, and more.'],
        ['fas fa-headset',       '24/7 Support',            'Our support team is available around the clock to assist you with any parking-related issues.'],
        ['fas fa-mobile-screen', 'Mobile Friendly',         'Seamlessly manage your bookings from any device — desktop, tablet, or smartphone.'],
        ['fas fa-plug-circle-bolt','EV Charging Stations',  'Dedicated EV charging spots available across all zones. Charge while you park.'],
        ['fas fa-qrcode',        'Easy Access',             'Get a digital booking reference instantly. Quick check-in at the gate with your booking code.'],
      ]; foreach ($features as [$icon,$title,$desc]): ?>
      <div class="col-md-6 col-lg-4 fade-up">
        <div class="feature-card">
          <div class="feature-icon"><i class="<?= $icon ?>"></i></div>
          <h5 class="feature-title"><?= $title ?></h5>
          <p class="feature-desc"><?= $desc ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── How It Works ───────────────────────────────────────── -->
<section class="how-section" id="how-it-works">
  <div class="container">
    <div class="text-center mb-5">
      <div class="hero-badge d-inline-flex mb-3"><i class="fas fa-map-signs"></i> Simple Process</div>
      <h2 class="section-title fade-up">Park Smarter in<br><span class="text-gradient">3 Easy Steps</span></h2>
      <p class="section-subtitle mx-auto fade-up">Getting started with SecurePark takes less than 2 minutes.</p>
    </div>
    <div class="row g-4 justify-content-center">
      <?php $steps = [
        ['01','Create Account','Sign up for free in seconds. Add your vehicle details and you\'re ready to go.'],
        ['02','Find & Book',   'Browse real-time availability, select your preferred zone and slot, then confirm your booking.'],
        ['03','Park & Go',     'Arrive at the parking facility, show your booking reference, and park with zero hassle.'],
      ]; foreach ($steps as [$num,$title,$desc]): ?>
      <div class="col-md-4 fade-up">
        <div class="step-card">
          <div class="step-num"><?= $num ?></div>
          <h5 class="step-title"><?= $title ?></h5>
          <p class="step-desc"><?= $desc ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Pricing ────────────────────────────────────────────── -->
<section class="pricing-section" id="pricing">
  <div class="container">
    <div class="text-center mb-5">
      <div class="hero-badge d-inline-flex mb-3"><i class="fas fa-tag"></i> Transparent Pricing</div>
      <h2 class="section-title fade-up">Simple, <span class="text-gradient">Honest Pricing</span></h2>
      <p class="section-subtitle mx-auto fade-up">Pay only for the time you park. No hidden fees, no surprises.</p>
    </div>
    <div class="row g-4 justify-content-center">
      <div class="col-md-4 fade-up">
        <div class="pricing-card">
          <div class="pricing-name">Standard</div>
          <div class="pricing-price">$2<span>.50/hr</span></div>
          <div class="pricing-period">Regular parking spots</div>
          <hr class="pricing-divider">
          <?php foreach(['Standard slot access','Easy online booking','Email confirmation','Cancellation support','—','—'] as $f): ?>
          <div class="pricing-feature"><i class="fas <?= $f==='—'?'fa-xmark':'fa-check' ?>"></i><?= $f !=='—'?$f:'Premium features' ?></div>
          <?php endforeach; ?>
          <div class="mt-4">
            <a href="/securepark/<?= $logged_in?'book.php':'login.php' ?>" class="btn-outline-custom w-100 justify-content-center">Book Now</a>
          </div>
        </div>
      </div>
      <div class="col-md-4 fade-up">
        <div class="pricing-card featured">
          <div class="popular-badge">Most Popular</div>
          <div class="pricing-name">Compact</div>
          <div class="pricing-price" style="background:var(--gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">$2<span style="-webkit-text-fill-color:var(--text-secondary)">.00/hr</span></div>
          <div class="pricing-period">Compact vehicle spaces</div>
          <hr class="pricing-divider">
          <?php foreach(['Compact slot access','Priority booking','SMS + Email alerts','Free cancellation','Zone selection','24/7 support'] as $f): ?>
          <div class="pricing-feature"><i class="fas fa-check"></i><?= $f ?></div>
          <?php endforeach; ?>
          <div class="mt-4">
            <a href="/securepark/<?= $logged_in?'book.php':'login.php' ?>" class="btn-primary-custom w-100 justify-content-center">Book Now</a>
          </div>
        </div>
      </div>
      <div class="col-md-4 fade-up">
        <div class="pricing-card">
          <div class="pricing-name">Premium / EV</div>
          <div class="pricing-price">$3<span>.00–3.50/hr</span></div>
          <div class="pricing-period">Large & EV charging spots</div>
          <hr class="pricing-divider">
          <?php foreach(['Large / EV slot access','EV charging included','Priority booking','Free cancellation','Dedicated support','Analytics dashboard'] as $f): ?>
          <div class="pricing-feature"><i class="fas fa-check"></i><?= $f ?></div>
          <?php endforeach; ?>
          <div class="mt-4">
            <a href="/securepark/<?= $logged_in?'book.php':'login.php' ?>" class="btn-outline-custom w-100 justify-content-center">Book Now</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── Testimonials ───────────────────────────────────────── -->
<section class="testimonials-section" id="testimonials">
  <div class="container">
    <div class="text-center mb-5">
      <div class="hero-badge d-inline-flex mb-3"><i class="fas fa-heart"></i> Customer Reviews</div>
      <h2 class="section-title fade-up">What Our <span class="text-gradient">Customers Say</span></h2>
    </div>
    <div class="row g-4">
      <?php $testimonials = [
        ['★★★★★','SecurePark completely changed how I commute. Booking takes 30 seconds and I never have to worry about finding a spot again. Absolutely love it!','Michael Chen','Daily Commuter','MC'],
        ['★★★★★','The EV charging feature is a game changer. I book a spot, plug in my car, and come back to a fully charged vehicle. Incredible service!','Sarah Williams','EV Owner','SW'],
        ['★★★★★','As a business owner, I need reliability. SecurePark delivers every time — never had an issue with my booking. The admin dashboard is excellent too!','David Rodriguez','Business Owner','DR'],
      ]; foreach ($testimonials as [$stars,$text,$name,$role,$initials]): ?>
      <div class="col-md-4 fade-up">
        <div class="testimonial-card">
          <div class="stars"><?= $stars ?></div>
          <p class="testimonial-text">"<?= $text ?>"</p>
          <div class="testimonial-author">
            <div class="t-avatar"><?= $initials ?></div>
            <div><div class="t-name"><?= $name ?></div><div class="t-role"><?= $role ?></div></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── CTA ────────────────────────────────────────────────── -->
<section class="cta-section">
  <div class="container position-relative text-center">
    <div class="hero-badge d-inline-flex mb-3"><i class="fas fa-rocket"></i> Get Started Today</div>
    <h2 class="section-title fade-up">Ready to Park Smarter?</h2>
    <p class="section-subtitle mx-auto mb-4 fade-up">Join thousands of drivers who save time and reduce stress with SecurePark.</p>
    <div class="d-flex gap-3 justify-content-center flex-wrap fade-up">
      <a href="/securepark/<?= $logged_in?'book.php':'login.php?tab=register' ?>" class="btn-primary-custom">
        <i class="fas fa-square-parking"></i> Start Parking Smarter
      </a>
      <a href="#features" class="btn-outline-custom"><i class="fas fa-info-circle"></i> Learn More</a>
    </div>
  </div>
</section>

<!-- ── Footer ─────────────────────────────────────────────── -->
<footer class="footer-custom" id="contact">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-4">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="brand-icon" style="width:36px;height:36px;background:var(--gradient);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff"><i class="fas fa-shield-halved"></i></div>
          <span class="footer-brand">SecurePark</span>
        </div>
        <p class="footer-desc">Smart, reliable, and affordable parking solutions for modern urban life.</p>
        <div class="footer-social">
          <?php foreach([['fab fa-twitter','#'],['fab fa-facebook','#'],['fab fa-instagram','#'],['fab fa-linkedin','#']] as [$icon,$url]): ?>
          <a href="<?= $url ?>" class="social-btn"><i class="<?= $icon ?>"></i></a>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="col-6 col-lg-2">
        <div class="footer-heading">Product</div>
        <?php foreach(['Features','How It Works','Pricing','API'] as $l): ?>
        <a href="#" class="footer-link"><?= $l ?></a>
        <?php endforeach; ?>
      </div>
      <div class="col-6 col-lg-2">
        <div class="footer-heading">Company</div>
        <?php foreach(['About Us','Blog','Careers','Press'] as $l): ?>
        <a href="#" class="footer-link"><?= $l ?></a>
        <?php endforeach; ?>
      </div>
      <div class="col-6 col-lg-2">
        <div class="footer-heading">Legal</div>
        <?php foreach(['Privacy Policy','Terms of Service','Cookie Policy','Security'] as $l): ?>
        <a href="#" class="footer-link"><?= $l ?></a>
        <?php endforeach; ?>
      </div>
      <div class="col-6 col-lg-2">
        <div class="footer-heading">Contact</div>
        <a href="mailto:support@securepark.com" class="footer-link"><i class="fas fa-envelope me-1"></i> support@securepark.com</a>
        <a href="tel:+15550100" class="footer-link"><i class="fas fa-phone me-1"></i> +1 (555) 010-0000</a>
        <a href="#" class="footer-link"><i class="fas fa-location-dot me-1"></i> New York, NY</a>
      </div>
    </div>
    <div class="footer-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
      <span>&copy; <?= date('Y') ?> SecurePark. All rights reserved.</span>
      <span>Built with <i class="fas fa-heart" style="color:var(--danger)"></i> for smarter cities</span>
    </div>
  </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="/securepark/assets/js/main.js"></script>
</body>
</html>
