<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
requireLogin();

$user        = getCurrentUser();
$active_page = 'book';

// Fetch zones with slots
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
  <title>Book a Slot — SecurePark</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/securepark/assets/css/style.css">
</head>
<body>
<div class="dash-layout">
  <?php include __DIR__ . '/includes/dash_sidebar.php'; ?>
  <div class="dash-content" id="dashContent">
    <?php include __DIR__ . '/includes/dash_nav.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Book a Parking Slot</h1>
          <div class="page-breadcrumb"><a href="/securepark/dashboard.php">Dashboard</a> / Book a Slot</div>
        </div>
      </div>

      <div class="row g-4">
        <!-- LEFT: Slot Map -->
        <div class="col-lg-8">
          <div class="slot-map-wrap">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
              <h5 class="mb-0"><i class="fas fa-map me-2" style="color:var(--primary-light)"></i>Select a Parking Slot</h5>
              <div class="slot-legend">
                <div class="legend-item"><div class="legend-dot ld-av"></div>Available</div>
                <div class="legend-item"><div class="legend-dot ld-sl"></div>Selected</div>
                <div class="legend-item"><div class="legend-dot ld-oc"></div>Occupied</div>
                <div class="legend-item"><div class="legend-dot ld-rs"></div>Reserved</div>
                <div class="legend-item"><div class="legend-dot ld-mt"></div>Maintenance</div>
              </div>
            </div>

            <!-- Zone Tabs -->
            <div class="zone-tabs">
              <?php foreach ($zones as $i => $zone): ?>
              <button class="zone-tab <?= $i===0?'active':'' ?>" data-zone="zone-<?= $zone['id'] ?>">
                <i class="fas fa-layer-group me-1"></i><?= htmlspecialchars($zone['name']) ?>
                <span style="font-size:0.72rem;color:var(--text-muted);margin-left:4px"><?= $zone['floor'] ?></span>
              </button>
              <?php endforeach; ?>
            </div>

            <!-- Slot Grids per Zone -->
            <?php foreach ($zones as $i => $zone): ?>
            <div class="zone-panel" data-zone="zone-<?= $zone['id'] ?>" style="display:<?= $i===0?'block':'none' ?>">
              <div class="d-flex align-items-center gap-2 mb-3">
                <div style="width:12px;height:12px;border-radius:50%;background:<?= htmlspecialchars($zone['color']) ?>"></div>
                <span style="font-size:0.88rem;color:var(--text-secondary)"><?= htmlspecialchars($zone['name']) ?> — <?= htmlspecialchars($zone['floor']) ?></span>
                <?php
                $avail = count(array_filter($zone['slots'], fn($s) => $s['status'] === 'available'));
                ?>
                <span style="font-size:0.78rem;background:rgba(16,185,129,0.15);color:var(--success);padding:2px 10px;border-radius:50px"><?= $avail ?> available</span>
              </div>
              <div class="slot-map-grid" id="slotMap">
                <?php foreach ($zone['slots'] as $slot): ?>
                <div class="slot-item <?= $slot['status'] ?>"
                     data-id="<?= $slot['id'] ?>"
                     data-num="<?= htmlspecialchars($slot['slot_number']) ?>"
                     data-type="<?= $slot['slot_type'] ?>"
                     data-status="<?= $slot['status'] ?>"
                     data-rate="<?= $slot['hourly_rate'] ?>"
                     title="<?= htmlspecialchars($slot['slot_number']) ?> — <?= ucfirst($slot['slot_type']) ?> ($<?= number_format($slot['hourly_rate'],2) ?>/hr) — <?= ucfirst($slot['status']) ?>">
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

        <!-- RIGHT: Booking Form -->
        <div class="col-lg-4">
          <div class="section-card sticky-top" style="top:80px">
            <div class="section-card-header">
              <div class="section-card-title"><i class="fas fa-ticket me-2" style="color:var(--accent)"></i>Booking Details</div>
            </div>
            <div class="section-card-body">
              <!-- Selected Slot Display -->
              <div id="noSlotMsg" class="text-center py-4" style="color:var(--text-muted);font-size:0.88rem">
                <i class="fas fa-hand-pointer" style="font-size:2rem;margin-bottom:10px;display:block;color:var(--primary-light)"></i>
                Click an available slot on the map to begin booking
              </div>

              <form id="bookingForm" style="display:none">
                <input type="hidden" name="slot_id" id="selectedSlotId">

                <!-- Selected Slot Info -->
                <div class="price-summary mb-4">
                  <div class="d-flex align-items-center gap-3 mb-2">
                    <div style="width:42px;height:42px;background:rgba(124,58,237,0.2);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;color:var(--primary-light)"><i class="fas fa-square-parking"></i></div>
                    <div>
                      <div style="font-weight:700" id="selectedSlotDisplay">—</div>
                      <div style="font-size:0.78rem;color:var(--text-muted)">Rate: <span id="slotRate">—</span>/hr</div>
                    </div>
                  </div>
                </div>

                <div class="form-group-custom">
                  <label class="form-label-custom">Vehicle Number</label>
                  <div class="input-icon-wrap">
                    <i class="fas fa-car"></i>
                    <input type="text" name="vehicle_number" class="form-input-custom" value="<?= htmlspecialchars($user['vehicle_number'] ?? '') ?>" placeholder="e.g. NYC-1234" required>
                  </div>
                </div>

                <div class="form-group-custom">
                  <label class="form-label-custom">Vehicle Type</label>
                  <select name="vehicle_type" class="form-input-custom" required>
                    <?php foreach (['car'=>'Car','motorcycle'=>'Motorcycle','suv'=>'SUV','truck'=>'Truck','van'=>'Van','ev'=>'EV'] as $v=>$l): ?>
                    <option value="<?= $v ?>"><?= $l ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="form-group-custom">
                  <label class="form-label-custom">Start Date & Time</label>
                  <input type="datetime-local" name="start_time" id="startTime" class="form-input-custom" required>
                </div>

                <div class="form-group-custom">
                  <label class="form-label-custom">End Date & Time</label>
                  <input type="datetime-local" name="end_time" id="endTime" class="form-input-custom" required>
                </div>

                <div class="form-group-custom">
                  <label class="form-label-custom">Notes (Optional)</label>
                  <textarea name="notes" class="form-input-custom" rows="2" placeholder="Special requests, instructions..."></textarea>
                </div>

                <!-- Price Breakdown -->
                <div class="price-summary mb-4">
                  <div class="price-row"><span>Duration</span><span id="calcDuration">—</span></div>
                  <div class="price-row"><span>Hourly Rate</span><span id="slotRateCopy">—</span></div>
                  <div class="price-row total"><span>Total Amount</span><span id="calcTotal">—</span></div>
                </div>

                <button type="submit" class="btn-primary-custom w-100 justify-content-center">
                  <i class="fas fa-ticket"></i> Confirm Booking
                </button>
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
// Show form when slot selected
document.querySelectorAll('.slot-map-grid').forEach(map => {
  map.addEventListener('click', (e) => {
    const slot = e.target.closest('.slot-item');
    if (!slot || slot.dataset.status !== 'available') return;
    document.getElementById('noSlotMsg').style.display = 'none';
    document.getElementById('bookingForm').style.display = 'block';
    const rateEl2 = document.getElementById('slotRateCopy');
    if (rateEl2) rateEl2.textContent = '$' + parseFloat(slot.dataset.rate).toFixed(2) + '/hr';
  });
});

// Real-time slot status polling — refreshes every 30 seconds
let _pollSelectedId = null;
const _bookForm = document.getElementById('bookingForm');
if (_bookForm) {
  const hiddenSlot = _bookForm.querySelector('[name="slot_id"]');
  if (hiddenSlot) {
    const obs = new MutationObserver(() => { _pollSelectedId = hiddenSlot.value || null; });
    obs.observe(hiddenSlot, { attributes: true, childList: true });
    hiddenSlot.addEventListener('change', () => { _pollSelectedId = hiddenSlot.value || null; });
  }
}

function _applySlotStatuses(slots) {
  document.querySelectorAll('.slot-item').forEach(el => {
    const id  = parseInt(el.dataset.id);
    const cur = el.dataset.status;
    const nxt = slots[id];
    if (!nxt || cur === nxt || cur === 'selected') return;
    el.classList.remove('available','occupied','reserved','maintenance');
    el.classList.add(nxt);
    el.dataset.status = nxt;
    // If this was the currently selected slot and it became unavailable, hide form
    if (_pollSelectedId && parseInt(_pollSelectedId) === id && nxt !== 'available') {
      document.getElementById('noSlotMsg').style.display = '';
      document.getElementById('bookingForm').style.display = 'none';
      if (window.Toast) window.Toast.show('The selected slot is no longer available.', 'warning');
    }
  });
}

// Indicator element
const _pollBadge = document.createElement('div');
_pollBadge.style.cssText = 'position:fixed;bottom:20px;right:20px;background:rgba(13,20,36,0.92);border:1px solid rgba(255,255,255,0.1);border-radius:50px;padding:6px 14px;font-size:0.72rem;color:#64748b;display:flex;align-items:center;gap:6px;z-index:999;transition:opacity .3s';
_pollBadge.innerHTML = '<span style="width:6px;height:6px;border-radius:50%;background:#10b981;display:inline-block"></span>Live';
document.body.appendChild(_pollBadge);

setInterval(async () => {
  try {
    const res  = await fetch('/securepark/api/get_slots.php');
    const data = await res.json();
    if (data.slots) _applySlotStatuses(data.slots);
    _pollBadge.style.opacity = '1';
    setTimeout(() => { _pollBadge.style.opacity = '0.4'; }, 800);
  } catch (e) { /* silent fail */ }
}, 30000);
</script>
</body>
</html>
