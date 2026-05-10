<?php
// Email notification helpers — uses PHP mail()
// For XAMPP: configure SMTP settings in php.ini (or use a plugin like hMailServer)

function _spMailSend(string $to, string $subject, string $html): bool {
    $headers = implode("\r\n", [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: SecurePark <noreply@securepark.com>',
        'Reply-To: noreply@securepark.com',
        'X-Mailer: PHP/' . PHP_VERSION,
    ]);
    return @mail($to, $subject, $html, $headers);
}

function _spEmailTemplate(string $title, string $body): string {
    return '<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>
body{margin:0;padding:0;background:#060c1a;font-family:Arial,sans-serif}
.w{max-width:560px;margin:32px auto;background:#0d1424;border-radius:16px;overflow:hidden;border:1px solid rgba(255,255,255,.08)}
.hd{background:linear-gradient(135deg,#7c3aed,#06b6d4);padding:28px 36px;text-align:center}
.hd h1{margin:0;color:#fff;font-size:22px;font-weight:700;letter-spacing:-0.5px}
.hd p{margin:5px 0 0;color:rgba(255,255,255,.75);font-size:13px}
.bd{padding:28px 36px}
.bd h2{color:#e2e8f0;margin:0 0 14px;font-size:17px;font-weight:600}
.bd p{color:#94a3b8;font-size:14px;line-height:1.7;margin:0 0 14px}
.box{background:#111c30;border-radius:10px;padding:16px 20px;margin:18px 0;border:1px solid rgba(255,255,255,.06)}
.r{display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid rgba(255,255,255,.05);font-size:13px}
.r:last-child{border:none}
.lbl{color:#64748b}.val{color:#e2e8f0;font-weight:600;text-align:right}
a.cta{display:inline-block;background:linear-gradient(135deg,#7c3aed,#06b6d4);color:#fff!important;text-decoration:none;padding:11px 26px;border-radius:8px;font-weight:600;font-size:13px;margin:14px 0}
.ft{padding:18px 36px;text-align:center;border-top:1px solid rgba(255,255,255,.06)}
.ft p{color:#475569;font-size:11px;margin:0}
</style></head><body>
<div class="w">
  <div class="hd"><h1>&#x1F3E2; SecurePark</h1><p>Smart Parking Solutions</p></div>
  <div class="bd"><h2>' . $title . '</h2>' . $body . '</div>
  <div class="ft"><p>&copy; ' . date('Y') . ' SecurePark &mdash; This is an automated message, please do not reply.</p></div>
</div></body></html>';
}

function spNotifyBookingCreated(string $email, string $name, array $b, string $slotNum, string $zoneName): void {
    $rows =
        '<div class="r"><span class="lbl">Booking Ref</span><span class="val">' . htmlspecialchars($b['booking_ref']) . '</span></div>' .
        '<div class="r"><span class="lbl">Slot / Zone</span><span class="val">' . htmlspecialchars($slotNum) . ' &middot; ' . htmlspecialchars($zoneName) . '</span></div>' .
        '<div class="r"><span class="lbl">Start</span><span class="val">' . date('M d, Y h:i A', strtotime($b['start_time'])) . '</span></div>' .
        '<div class="r"><span class="lbl">End</span><span class="val">' . date('M d, Y h:i A', strtotime($b['end_time'])) . '</span></div>' .
        '<div class="r"><span class="lbl">Duration</span><span class="val">' . number_format((float)$b['duration_hours'], 1) . ' hrs</span></div>' .
        '<div class="r"><span class="lbl">Amount</span><span class="val">$' . number_format((float)$b['amount'], 2) . '</span></div>';
    $body =
        '<p>Hi ' . htmlspecialchars($name) . ', your parking booking has been placed successfully!</p>' .
        '<div class="box">' . $rows . '</div>' .
        '<a class="cta" href="http://localhost/securepark/booking-receipt.php?ref=' . urlencode($b['booking_ref']) . '">View Receipt &rarr;</a>' .
        '<p style="font-size:12px;color:#64748b;margin-top:10px">Please show your booking reference at the entrance gate.</p>';
    _spMailSend($email, 'Booking Confirmed — ' . $b['booking_ref'], _spEmailTemplate('Booking Confirmed! &#x2705;', $body));
}

function spNotifyBookingStatus(string $email, string $name, array $b, string $newStatus): void {
    $msgs = [
        'confirmed' => ['Booking Confirmed &#x2705;',           'Your booking has been confirmed by our team.'],
        'active'    => ['Parking Session Active &#x1F697;',     'Your parking session is now active. Welcome to SecurePark!'],
        'completed' => ['Session Completed &#x1F3C1;',          'Your parking session is complete. Thank you for using SecurePark!'],
        'cancelled' => ['Booking Cancelled &#x274C;',           'Your booking has been cancelled.'],
    ];
    [$title, $desc] = $msgs[$newStatus] ?? ['Booking Updated', 'Your booking status has been updated.'];
    $body =
        '<p>Hi ' . htmlspecialchars($name) . ', ' . $desc . '</p>' .
        '<div class="box">' .
          '<div class="r"><span class="lbl">Booking Ref</span><span class="val">' . htmlspecialchars($b['booking_ref']) . '</span></div>' .
          '<div class="r"><span class="lbl">New Status</span><span class="val">' . ucfirst($newStatus) . '</span></div>' .
          '<div class="r"><span class="lbl">Amount</span><span class="val">$' . number_format((float)$b['amount'], 2) . '</span></div>' .
        '</div>' .
        '<a class="cta" href="http://localhost/securepark/my-bookings.php">View My Bookings &rarr;</a>';
    _spMailSend($email, strip_tags($title) . ' — ' . $b['booking_ref'], _spEmailTemplate($title, $body));
}
