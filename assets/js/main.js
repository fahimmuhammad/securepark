/* ============================================================
   SecurePark — Main JavaScript
   ============================================================ */

// ── Theme init (runs immediately to avoid flash) ───────────
(function () {
  const t = localStorage.getItem('spTheme');
  if (t) document.documentElement.dataset.theme = t;
})();

// ── Navbar scroll effect ───────────────────────────────────
(function () {
  const nav = document.querySelector('.navbar-custom');
  if (!nav) return;
  const onScroll = () => nav.classList.toggle('scrolled', window.scrollY > 60);
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
})();

// ── Scroll-reveal animations ───────────────────────────────
(function () {
  const els = document.querySelectorAll('.fade-up');
  if (!els.length) return;
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); } });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
  els.forEach((el, i) => {
    el.style.transitionDelay = (i % 4) * 80 + 'ms';
    io.observe(el);
  });
})();

// ── Animated counters ─────────────────────────────────────
(function () {
  const counterEls = document.querySelectorAll('[data-counter]');
  if (!counterEls.length) return;
  const animateCounter = (el) => {
    const target  = parseFloat(el.dataset.counter);
    const suffix  = el.dataset.suffix || '';
    const prefix  = el.dataset.prefix || '';
    const dec     = el.dataset.decimals ? parseInt(el.dataset.decimals) : 0;
    const dur     = 1800;
    const start   = performance.now();
    const update  = (now) => {
      const p = Math.min((now - start) / dur, 1);
      const ease = 1 - Math.pow(1 - p, 3);
      el.textContent = prefix + (target * ease).toFixed(dec) + suffix;
      if (p < 1) requestAnimationFrame(update);
    };
    requestAnimationFrame(update);
  };
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { animateCounter(e.target); io.unobserve(e.target); } });
  }, { threshold: 0.5 });
  counterEls.forEach(el => io.observe(el));
})();

// ── Toast notification system ─────────────────────────────
const Toast = (() => {
  let container = null;
  const getContainer = () => {
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container-custom';
      document.body.appendChild(container);
    }
    return container;
  };
  const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', info: 'fa-circle-info', warning: 'fa-triangle-exclamation' };
  const titles = { success: 'Success', error: 'Error', info: 'Info', warning: 'Warning' };
  const show = (message, type = 'info', duration = 4000) => {
    const c    = getContainer();
    const wrap = document.createElement('div');
    wrap.className = `toast-custom toast-${type}`;
    wrap.innerHTML = `
      <i class="fas ${icons[type] || icons.info} toast-icon"></i>
      <div class="toast-body">
        <div class="toast-title">${titles[type]}</div>
        <div class="toast-msg">${message}</div>
      </div>
      <button class="toast-close"><i class="fas fa-xmark"></i></button>`;
    c.appendChild(wrap);
    const close = () => {
      wrap.style.animation = 'toastOut 0.35s ease forwards';
      wrap.addEventListener('animationend', () => wrap.remove(), { once: true });
    };
    wrap.querySelector('.toast-close').addEventListener('click', close);
    if (duration > 0) setTimeout(close, duration);
  };
  return { show, success: (m, d) => show(m, 'success', d), error: (m, d) => show(m, 'error', d), info: (m, d) => show(m, 'info', d), warning: (m, d) => show(m, 'warning', d) };
})();
window.Toast = Toast;

// ── Dashboard sidebar toggle ───────────────────────────────
(function () {
  const sidebar   = document.getElementById('dashSidebar');
  const topnav    = document.querySelector('.dash-topnav');
  const content   = document.querySelector('.dash-content');
  const toggleBtn = document.getElementById('sidebarToggle');
  if (!sidebar || !toggleBtn) return;

  let overlay = document.querySelector('.sidebar-overlay');
  if (!overlay) {
    overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);
  }

  const isMobile = () => window.innerWidth < 992;

  const close = () => {
    if (isMobile()) {
      sidebar.classList.remove('open');
      overlay.classList.remove('active');
    } else {
      sidebar.classList.add('collapsed');
      topnav?.classList.add('expanded');
      content?.classList.add('expanded');
    }
  };

  const open = () => {
    if (isMobile()) {
      sidebar.classList.add('open');
      overlay.classList.add('active');
    } else {
      sidebar.classList.remove('collapsed');
      topnav?.classList.remove('expanded');
      content?.classList.remove('expanded');
    }
  };

  toggleBtn.addEventListener('click', () => {
    if (isMobile()) { sidebar.classList.contains('open') ? close() : open(); }
    else             { sidebar.classList.contains('collapsed') ? open() : close(); }
  });

  overlay.addEventListener('click', close);

  window.addEventListener('resize', () => {
    if (!isMobile()) {
      sidebar.classList.remove('open');
      overlay.classList.remove('active');
    }
  });
})();

// ── Nav avatar dropdown ────────────────────────────────────
(function () {
  const btn  = document.getElementById('navAvatarBtn');
  const drop = document.getElementById('navDropdown');
  if (!btn || !drop) return;
  btn.addEventListener('click', (e) => { e.stopPropagation(); drop.classList.toggle('open'); });
  document.addEventListener('click', () => drop.classList.remove('open'));
})();

// ── Theme toggle ───────────────────────────────────────────
(function () {
  const applyTheme = (theme) => {
    document.documentElement.dataset.theme = theme;
    localStorage.setItem('spTheme', theme);
    document.querySelectorAll('.theme-toggle i').forEach(icon => {
      icon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
    });
  };
  const current = () => document.documentElement.dataset.theme || 'dark';
  applyTheme(current());
  document.querySelectorAll('.theme-toggle').forEach(btn => {
    btn.addEventListener('click', () => applyTheme(current() === 'dark' ? 'light' : 'dark'));
  });
})();

// ── Auth page tabs ─────────────────────────────────────────
(function () {
  const tabs  = document.querySelectorAll('.auth-tab');
  const forms = document.querySelectorAll('.auth-form');
  if (!tabs.length) return;
  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      forms.forEach(f => f.classList.remove('active'));
      tab.classList.add('active');
      const target = document.getElementById(tab.dataset.target);
      if (target) target.classList.add('active');
    });
  });
})();

// ── Parking slot selection ─────────────────────────────────
(function () {
  const map      = document.getElementById('slotMap');
  const hiddenId = document.getElementById('selectedSlotId');
  const display  = document.getElementById('selectedSlotDisplay');
  const rateEl   = document.getElementById('slotRate');
  if (!map) return;

  map.addEventListener('click', (e) => {
    const slot = e.target.closest('.slot-item');
    if (!slot || slot.dataset.status !== 'available') return;

    map.querySelectorAll('.slot-item').forEach(s => s.classList.remove('selected'));
    slot.classList.add('selected');

    if (hiddenId)   hiddenId.value  = slot.dataset.id;
    if (display)    display.textContent = slot.dataset.num + ' (' + slot.dataset.type + ')';
    if (rateEl)     rateEl.textContent = '$' + parseFloat(slot.dataset.rate).toFixed(2);

    updatePrice();
  });
})();

// ── Price calculator ───────────────────────────────────────
function updatePrice() {
  const start = document.getElementById('startTime');
  const end   = document.getElementById('endTime');
  const rate  = document.getElementById('slotRate');
  const durEl = document.getElementById('calcDuration');
  const totEl = document.getElementById('calcTotal');
  if (!start || !end || !rate || !durEl || !totEl) return;

  if (!start.value || !end.value) return;

  const s = new Date(start.value);
  const e = new Date(end.value);
  if (e <= s) { durEl.textContent = '—'; totEl.textContent = '—'; return; }

  const hrs = (e - s) / 3600000;
  const r   = parseFloat(rate.textContent.replace('$', '')) || 0;
  durEl.textContent = hrs.toFixed(1) + ' hr' + (hrs !== 1 ? 's' : '');
  totEl.textContent = '$' + (hrs * r).toFixed(2);
}
(function () {
  ['startTime','endTime'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('change', updatePrice);
  });
  // Set min date/time to now
  const st = document.getElementById('startTime');
  const et = document.getElementById('endTime');
  if (st) {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    st.min = now.toISOString().slice(0,16);
    st.addEventListener('change', () => { if(et) et.min = st.value; });
  }
})();

// ── Zone tabs switching ────────────────────────────────────
(function () {
  const zoneTabs = document.querySelectorAll('.zone-tab');
  if (!zoneTabs.length) return;
  zoneTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      zoneTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');

      const target = tab.dataset.zone;
      document.querySelectorAll('.zone-panel').forEach(p => {
        p.style.display = p.dataset.zone === target ? 'block' : 'none';
      });
    });
  });
})();

// ── AJAX Booking form ──────────────────────────────────────
(function () {
  const form = document.getElementById('bookingForm');
  if (!form) return;
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = form.querySelector('[type=submit]');
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    btn.disabled = true;

    try {
      const res  = await fetch('/securepark/api/book_slot.php', { method:'POST', body: new FormData(form) });
      const data = await res.json();
      if (data.success) {
        Toast.success(data.message || 'Booking confirmed!');
        setTimeout(() => window.location.href = '/securepark/my-bookings.php', 1500);
      } else {
        Toast.error(data.error || 'Booking failed. Please try again.');
        btn.innerHTML = orig; btn.disabled = false;
      }
    } catch {
      Toast.error('Network error. Please try again.');
      btn.innerHTML = orig; btn.disabled = false;
    }
  });
})();

// ── AJAX Cancel booking ────────────────────────────────────
async function cancelBooking(bookingId) {
  if (!confirm('Are you sure you want to cancel this booking?')) return;
  try {
    const res  = await fetch('/securepark/api/cancel_booking.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ booking_id: bookingId })
    });
    const data = await res.json();
    if (data.success) {
      Toast.success('Booking cancelled successfully.');
      setTimeout(() => window.location.reload(), 1500);
    } else {
      Toast.error(data.error || 'Could not cancel booking.');
    }
  } catch {
    Toast.error('Network error.');
  }
}
window.cancelBooking = cancelBooking;

// ── Table search ───────────────────────────────────────────
(function () {
  document.querySelectorAll('.table-search').forEach(input => {
    const tableId = input.dataset.table;
    const table   = document.getElementById(tableId);
    if (!table) return;
    input.addEventListener('input', () => {
      const q = input.value.toLowerCase();
      table.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  });
})();

// ── Admin slot status update ───────────────────────────────
async function updateSlotStatus(slotId, newStatus, btn) {
  const orig = btn.innerHTML;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
  btn.disabled = true;
  try {
    const res  = await fetch('/securepark/api/update_slot.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ slot_id: slotId, status: newStatus })
    });
    const data = await res.json();
    if (data.success) { Toast.success('Slot status updated.'); setTimeout(() => window.location.reload(), 1000); }
    else { Toast.error(data.error || 'Failed to update slot.'); btn.innerHTML = orig; btn.disabled = false; }
  } catch {
    Toast.error('Network error.');
    btn.innerHTML = orig; btn.disabled = false;
  }
}
window.updateSlotStatus = updateSlotStatus;

// ── Admin booking action ───────────────────────────────────
async function bookingAction(bookingId, action) {
  const labels = { confirm:'confirm', activate:'activate', complete:'complete', cancel:'cancel' };
  if (!confirm(`Are you sure you want to ${labels[action] || action} this booking?`)) return;
  try {
    const res  = await fetch('/securepark/api/admin_booking_action.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ booking_id: bookingId, action })
    });
    const data = await res.json();
    if (data.success) { Toast.success('Booking updated.'); setTimeout(() => window.location.reload(), 1200); }
    else { Toast.error(data.error || 'Action failed.'); }
  } catch {
    Toast.error('Network error.');
  }
}
window.bookingAction = bookingAction;

// ── Hero parking grid animation ────────────────────────────
(function () {
  const slots = document.querySelectorAll('.hp-slot');
  if (!slots.length) return;
  const statuses = ['av','av','av','oc','rs','av','ev','av','oc','av'];
  setInterval(() => {
    const i = Math.floor(Math.random() * slots.length);
    const s = statuses[Math.floor(Math.random() * statuses.length)];
    slots[i].className = 'hp-slot ' + s;
  }, 2000);
})();

// ── Flash message from PHP session ────────────────────────
(function () {
  const flash = document.getElementById('phpFlashData');
  if (!flash) return;
  const type = flash.dataset.type;
  const msg  = flash.dataset.msg;
  if (msg && type) Toast[type] ? Toast[type](msg) : Toast.info(msg);
})();
