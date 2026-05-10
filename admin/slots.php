<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requireAdmin();

$active_page = 'slots';

$zones_res = $conn->query("SELECT * FROM parking_zones ORDER BY id");
$zones = [];
while ($z = $zones_res->fetch_assoc()) {
    $z['slots'] = [];
    $sr = $conn->query("SELECT * FROM parking_slots WHERE zone_id = {$z['id']} ORDER BY slot_number");
    while ($s = $sr->fetch_assoc()) $z['slots'][] = $s;
    $zones[] = $z;
}

$type_icons = ['standard'=>'fas fa-car','compact'=>'fas fa-car-side','large'=>'fas fa-truck','handicap'=>'fas fa-wheelchair','ev'=>'fas fa-bolt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Parking Slots — SecurePark Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/securepark/assets/css/style.css">
</head>
<body>
<div class="dash-layout">
  <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
  <div class="dash-content" id="dashContent">
    <nav class="dash-topnav">
      <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
      <div class="nav-brand"><span style="font-family:'Poppins',sans-serif;font-weight:600;font-size:1rem;color:var(--text-secondary)">Parking Slot Management</span></div>
      <div class="ms-auto"><a href="/securepark/logout.php" class="btn-outline-custom btn-sm-custom" style="padding:7px 14px;font-size:0.82rem"><i class="fas fa-right-from-bracket"></i> Logout</a></div>
    </nav>

    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Parking Slots</h1>
          <div class="page-breadcrumb"><a href="/securepark/admin/index.php">Admin</a> / Slots</div>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="row g-3 mb-4">
        <?php
        $summary = $conn->query("SELECT status, COUNT(*) c FROM parking_slots GROUP BY status");
        $counts  = ['available'=>0,'occupied'=>0,'reserved'=>0,'maintenance'=>0];
        while ($r = $summary->fetch_assoc()) $counts[$r['status']] = $r['c'];
        $stats = [
          [$counts['available'],  'Available',   'icon-green',  'fas fa-circle-check'],
          [$counts['occupied'],   'Occupied',    'icon-red',    'fas fa-circle-xmark'],
          [$counts['reserved'],   'Reserved',    'icon-amber',  'fas fa-clock'],
          [$counts['maintenance'],'Maintenance', 'icon-purple', 'fas fa-wrench'],
        ];
        foreach ($stats as [$val,$lbl,$cls,$icon]): ?>
        <div class="col-6 col-md-3">
          <div class="dash-stat-card">
            <div class="stat-icon-box <?= $cls ?>"><i class="<?= $icon ?>"></i></div>
            <div class="stat-info">
              <div class="stat-value"><?= $val ?></div>
              <div class="stat-title"><?= $lbl ?></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Legend -->
      <div class="section-card mb-4">
        <div class="section-card-body" style="padding:16px 24px">
          <div class="d-flex align-items-center gap-4 flex-wrap" style="font-size:0.82rem">
            <strong style="color:var(--text-secondary)">Click a slot to change status:</strong>
            <div class="d-flex align-items-center gap-2"><div class="legend-dot ld-av"></div>Available</div>
            <div class="d-flex align-items-center gap-2"><div class="legend-dot ld-oc"></div>Occupied</div>
            <div class="d-flex align-items-center gap-2"><div class="legend-dot ld-rs"></div>Reserved</div>
            <div class="d-flex align-items-center gap-2"><div class="legend-dot ld-mt"></div>Maintenance</div>
          </div>
        </div>
      </div>

      <!-- Zone Tabs + Maps -->
      <div class="slot-map-wrap">
        <div class="zone-tabs">
          <?php foreach ($zones as $i => $zone): ?>
          <button class="zone-tab <?= $i===0?'active':'' ?>" data-zone="zone-<?= $zone['id'] ?>">
            <i class="fas fa-layer-group me-1"></i><?= htmlspecialchars($zone['name']) ?>
          </button>
          <?php endforeach; ?>
        </div>

        <?php foreach ($zones as $i => $zone): ?>
        <div class="zone-panel" data-zone="zone-<?= $zone['id'] ?>" style="display:<?= $i===0?'block':'none' ?>">
          <div class="d-flex align-items-center gap-2 mb-3">
            <div style="width:12px;height:12px;border-radius:50%;background:<?= htmlspecialchars($zone['color']) ?>"></div>
            <span style="font-size:0.88rem;color:var(--text-secondary)"><?= htmlspecialchars($zone['name']) ?> — <?= htmlspecialchars($zone['floor']) ?></span>
          </div>
          <div class="slot-map-grid">
            <?php foreach ($zone['slots'] as $slot): ?>
            <div class="slot-item <?= $slot['status'] ?>"
                 style="cursor:pointer"
                 onclick="openSlotModal(<?= $slot['id'] ?>, '<?= htmlspecialchars($slot['slot_number']) ?>', '<?= $slot['slot_type'] ?>', '<?= $slot['status'] ?>', <?= $slot['hourly_rate'] ?>)"
                 title="<?= htmlspecialchars($slot['slot_number']) ?> — <?= ucfirst($slot['slot_type']) ?> ($<?= number_format($slot['hourly_rate'],2) ?>/hr)">
              <i class="<?= $type_icons[$slot['slot_type']] ?? 'fas fa-car' ?> slot-icon"></i>
              <span class="slot-num"><?= htmlspecialchars($slot['slot_number']) ?></span>
              <span class="slot-type"><?= $slot['slot_type'] ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- Slot Status Modal -->
<div class="modal-custom" id="slotModal">
  <div class="modal-box" style="max-width:380px">
    <div class="modal-header">
      <h5 style="margin:0;font-weight:600">Update Slot Status</h5>
      <button class="modal-close" onclick="closeSlotModal()"><i class="fas fa-xmark"></i></button>
    </div>
    <div id="slotModalBody">
      <div class="d-flex align-items-center gap-3 mb-4">
        <div style="width:52px;height:52px;background:rgba(124,58,237,0.15);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:var(--primary-light)"><i class="fas fa-square-parking"></i></div>
        <div>
          <div style="font-weight:700;font-size:1.1rem" id="modalSlotNum">—</div>
          <div style="font-size:0.82rem;color:var(--text-muted)" id="modalSlotInfo">—</div>
        </div>
      </div>
      <div class="form-group-custom">
        <label class="form-label-custom">Change Status To</label>
        <select id="newSlotStatus" class="form-input-custom">
          <option value="available">Available</option>
          <option value="occupied">Occupied</option>
          <option value="reserved">Reserved</option>
          <option value="maintenance">Maintenance</option>
        </select>
      </div>
      <button class="btn-primary-custom w-100 justify-content-center mt-2" onclick="saveSlotStatus()">
        <i class="fas fa-floppy-disk"></i> Save Status
      </button>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="/securepark/assets/js/main.js"></script>
<script>
let currentSlotId = null;
function openSlotModal(id, num, type, status, rate) {
  currentSlotId = id;
  document.getElementById('modalSlotNum').textContent  = num;
  document.getElementById('modalSlotInfo').textContent = ucfirst(type) + ' · $' + rate.toFixed(2) + '/hr · ' + ucfirst(status);
  document.getElementById('newSlotStatus').value = status;
  document.getElementById('slotModal').classList.add('open');
}
function closeSlotModal() { document.getElementById('slotModal').classList.remove('open'); }
function ucfirst(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : s; }
function saveSlotStatus() {
  const btn = document.querySelector('#slotModal .btn-primary-custom');
  updateSlotStatus(currentSlotId, document.getElementById('newSlotStatus').value, btn);
  closeSlotModal();
}
document.getElementById('slotModal').addEventListener('click', (e) => { if (e.target === e.currentTarget) closeSlotModal(); });
</script>
</body>
</html>
