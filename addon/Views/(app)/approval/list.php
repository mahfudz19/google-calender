<?php
// Handle error message dari query string
$errorCode = $_GET['error'] ?? null;
$errorMessage = $_GET['message'] ?? null;
$displayError = null;

if ($errorCode && $errorMessage) {
    // Decode URL-encoded message
    $decodedMessage = urldecode($errorMessage);
    
    // Custom error messages
    switch ($errorCode) {
        case '500':
            if (strpos($decodedMessage, 'Conflict detected') !== false) {
                $displayError = "Conflict detected! Agenda yang ingin disetujui bertabrakan dengan agenda lain yang sudah disetujui.";
            } elseif (strpos($decodedMessage, 'user_controller') !== false) {
                $displayError = "Terjadi kesalahan sistem: Tidak dapat mengakses data pengguna Google. Silakan coba lagi atau hubungi administrator.";
            } else {
                $displayError = "Terjadi kesalahan server: " . $decodedMessage;
            }
            break;
        case 'conflict':
            if (strpos($decodedMessage, 'Conflict detected') !== false) {
                $displayError = "Conflict detected! Agenda yang ingin disetujui bertabrakan dengan agenda lain yang sudah disetujui.";
            } else {
                $displayError = $decodedMessage;
            }
            break;
        case '400':
            $displayError = "Data yang dikirim tidak valid: " . $decodedMessage;
            break;
        default:
            $displayError = $decodedMessage;
    }
}
?>

<!-- Error Alert -->
<?php if ($displayError): ?>
<div class="error-alert" style="margin-bottom: 2rem;">
    <div class="error-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>
    </div>
    <div class="error-content">
        <div class="error-title">Terjadi Kesalahan</div>
        <div class="error-message"><?= htmlspecialchars($displayError) ?></div>
    </div>
    <button class="error-close" onclick="this.parentElement.remove()" title="Tutup">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>
</div>
<?php endif; ?>

<?php if (empty($approvals)): ?>
  <div class="empty-state">
    <div class="empty-icon">🎉</div>
    <h3>Semua bersih!</h3>
    <p>Tidak ada permintaan agenda yang perlu persetujuan saat ini.</p>
  </div>
<?php else: ?>
  <?php foreach ($approvals as $item):
    $startDate = new DateTime($item['start_time']);
    $endDate = new DateTime($item['end_time']);
    $dateStr = $startDate->format('d M Y');
    $timeStr = $startDate->format('H:i') . ' - ' . $endDate->format('H:i');
    $isHistory = $item['status'] !== 'pending';
  ?>
    <div class="approval-card" data-id="<?= $item['id'] ?>">
      <!-- Kolom Waktu (Kiri) -->
      <div class="card-time">
        <span class="date-day"><?= $startDate->format('d') ?></span>
        <span class="date-month"><?= $startDate->format('M') ?></span>
        <span class="date-year"><?= $startDate->format('Y') ?></span>
      </div>

      <!-- Kolom Detail (Tengah) -->
      <div class="card-details">
        <div class="detail-header">
          <span class="time-badge">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <?= $timeStr ?>
          </span>
          <?php if ($item['location']): ?>
            <span class="location-badge">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                <circle cx="12" cy="10" r="3"></circle>
              </svg>
              <?= htmlspecialchars($item['location']) ?>
            </span>
          <?php endif; ?>

          <?php if ($isHistory): ?>
            <span class="status-badge status-<?= $item['status'] ?>">
              <?= ucfirst($item['status']) ?>
            </span>
          <?php endif; ?>
        </div>

        <h3 class="event-title"><?= htmlspecialchars($item['title']) ?></h3>
        <p class="event-desc"><?= htmlspecialchars($item['description'] ?? '') ?></p>

        <div class="requester-info">
          <?php if ($item['requester_avatar']): ?>
            <img src="<?= $item['requester_avatar'] ?>" alt="Avatar" class="avatar">
          <?php endif; ?>
          <div class="requester-text">
            <span class="name"><?= htmlspecialchars($item['requester_name'] ?? 'Unknown') ?></span>
            <span class="role"><?= htmlspecialchars($item['requester_role'] ?? 'User') ?></span>
          </div>
        </div>
      </div>

      <!-- Kolom Aksi (Kanan) -->
      <?php if (!$isHistory): ?>
        <div class="card-actions">
          <!-- Reject Modal Dialog -->
          <dialog id="rejectModal_<?= $item['id'] ?>" class="confirm-modal">
            <div class="modal-content">
              <div class="modal-header">
                <h3>Konfirmasi Penolakan</h3>
                <button type="button" onclick="this.closest('dialog').close()" class="close-btn">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                  </svg>
                </button>
              </div>

              <div class="modal-body">
                <p>Apakah Anda yakin ingin menolak pengajuan agenda ini?</p>
                <p style="font-size: 0.9rem; color: #6b7280; margin-top: 0.5rem;">
                  <strong><?= htmlspecialchars($item['title']) ?></strong>
                </p>

                <!-- Form untuk comment -->
                <form id="rejectCommentForm_<?= $item['id'] ?>" action="/approval/<?= $item['id'] ?>/reject" method="POST" data-spa style="margin-top: 1rem;">
                  <div class="form-group">
                    <label for="comment_<?= $item['id'] ?>" class="form-label">Alasan Penolakan <span style="color:red">*</span></label>
                    <textarea
                      id="comment_<?= $item['id'] ?>"
                      name="comment"
                      class="form-control"
                      rows="3"
                      placeholder="Jelaskan alasan penolakan agenda ini..."
                      required></textarea>
                  </div>

                  <div class="modal-actions" style="margin-top: 1rem;">
                    <button type="button" onclick="this.closest('dialog').close()" class="btn-cancel">
                      Batal
                    </button>
                    <button type="submit" class="btn-confirm btn-reject">
                      Ya, Tolak Agenda
                    </button>
                  </div>
                </form>

                <p style="font-size: 0.85rem; color: #dc2626; margin-top: 1rem;">
                  ⚠️ Tindakan ini tidak dapat dibatalkan.
                </p>
              </div>
            </div>
          </dialog>

          <!-- Approve Modal Dialog -->
          <dialog id="approveModal_<?= $item['id'] ?>" class="confirm-modal">
            <div class="modal-content">
              <div class="modal-header">
                <h3>Konfirmasi Persetujuan</h3>
                <button type="button" onclick="this.closest('dialog').close()" class="close-btn">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                  </svg>
                </button>
              </div>

              <div class="modal-body">
                <p>Apakah Anda yakin ingin menyetujui pengajuan agenda ini?</p>
                <p style="font-size: 0.9rem; color: #6b7280; margin-top: 0.5rem;">
                  <strong><?= htmlspecialchars($item['title']) ?></strong>
                </p>
                <p style="font-size: 0.85rem; color: #059669; margin-top: 0.5rem;">
                  ✅ Agenda akan disetujui dan ditampilkan di kalender.
                </p>
              </div>

              <form method="dialog" class="modal-actions">
                <button type="button" onclick="this.closest('dialog').close()" class="btn-cancel">
                  Batal
                </button>
                <button type="submit" form="approveForm_<?= $item['id'] ?>" class="btn-confirm btn-approve">
                  Ya, Setujui Agenda
                </button>
              </form>
            </div>
          </dialog>

          <!-- Hidden Forms -->
          <form id="approveForm_<?= $item['id'] ?>" action="/approval/<?= $item['id'] ?>/approve" method="POST" style="display: none;"></form>

          <!-- Trigger Buttons -->
          <button type="button" onclick="document.getElementById('rejectModal_<?= $item['id'] ?>').showModal()" class="btn-action btn-reject">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
            Tolak
          </button>

          <button type="button" onclick="document.getElementById('approveModal_<?= $item['id'] ?>').showModal()" class="btn-action btn-approve">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            Setujui
          </button>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>