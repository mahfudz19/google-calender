<?php
$errorCode = $_GET['error'] ?? null;
$errorMessage = $_GET['message'] ?? null;
$displayError = null;

if ($errorCode && $errorMessage) {
  $decodedMessage = urldecode($errorMessage);
  switch ($errorCode) {
    case '500':
      $displayError = strpos($decodedMessage, 'Conflict detected') !== false
        ? "Jadwal bertabrakan dengan agenda lain."
        : "Terjadi kesalahan server: " . $decodedMessage;
      break;
    case 'conflict':
      $displayError = strpos($decodedMessage, 'Conflict detected') !== false
        ? "Jadwal bertabrakan dengan agenda lain."
        : $decodedMessage;
      break;
    case '400':
      $displayError = "Data tidak valid: " . $decodedMessage;
      break;
    default:
      $displayError = $decodedMessage;
  }
}
?>

<div id="mazu-approval-inner">

  <?php if ($displayError): ?>
    <div class="apv-alert-error" id="globalErrorAlert">
      <div class="apv-alert-icon">⚠️</div>
      <div class="apv-alert-body">
        <strong>Peringatan Sistem</strong>
        <p><?= htmlspecialchars($displayError) ?></p>
      </div>
      <button type="button" class="apv-alert-close" onclick="this.closest('.apv-alert-error').remove()">
        <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>
  <?php endif; ?>

  <?php if (empty($approvals)): ?>
    <div class="apv-empty-state">
      <span class="apv-empty-icon">☕</span>
      <h3>Semua Selesai!</h3>
      <p>Tidak ada antrean persetujuan agenda saat ini.</p>
    </div>
  <?php else: ?>
    <div class="apv-list-group">
      <?php foreach ($approvals as $item):
        $startDate = new DateTime($item['start_time']);
        $endDate = new DateTime($item['end_time']);
        $dateStr = $startDate->format('d M Y');
        $timeStr = $startDate->format('H:i') . ' - ' . $endDate->format('H:i');
        $isHistory = $item['status'] !== 'pending';

        // Tentukan warna dot status
        $dotColor = 'var(--warning-main)';
        if ($item['status'] === 'approved') $dotColor = 'var(--success-main)';
        if ($item['status'] === 'rejected') $dotColor = 'var(--error-main)';
      ?>

        <div class="apv-card" data-id="<?= $item['id'] ?>">

          <div class="apv-card-main">
            <div class="apv-requester">
              <?php if ($item['requester_avatar']): ?>
                <img src="<?= $item['requester_avatar'] ?>" alt="Avatar" class="apv-avatar">
              <?php else: ?>
                <div class="apv-avatar fallback"><?= strtoupper(substr($item['requester_name'] ?? 'U', 0, 1)) ?></div>
              <?php endif; ?>
            </div>

            <div class="apv-info">
              <h3 class="apv-title">
                <div class="apv-status-dot" style="background-color: <?= $dotColor ?>;"></div>
                <?= htmlspecialchars($item['title']) ?>
              </h3>
              <div class="apv-meta">
                <span>👤 <?= htmlspecialchars($item['requester_name'] ?? 'User') ?></span>
                <span>📅 <?= $dateStr ?> (<?= $timeStr ?>)</span>
                <?php if ($item['location']): ?>
                  <span>📍 <?= htmlspecialchars($item['location']) ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="apv-actions">
            <?php if ($isHistory): ?>
              <span class="apv-status-badge <?= $item['status'] ?>">
                <?php if ($item['status'] === 'processing'): ?>
                  <svg class="apv-spinner" viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none">
                    <circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-linecap="round"></circle>
                  </svg>
                  Memproses...
                <?php else: ?>
                  <?= ucfirst($item['status']) ?>
                <?php endif; ?>
              </span>

              <?php if ($item['status'] === 'approved'): ?>
                <a data-spa href="<?= getBaseUrl('/agenda/' . $item['id'] . '/edit') ?>" class="apv-btn-icon" title="Edit">✏️</a>
                <button type="button" onclick="openModal('deleteModal_<?= $item['id'] ?>')" class="apv-btn-icon danger" title="Hapus">🗑️</button>
              <?php endif; ?>

            <?php else: ?>
              <button type="button" onclick="openModal('rejectModal_<?= $item['id'] ?>')" class="apv-btn outline-danger">Tolak</button>
              <button type="button" onclick="handleApprove(<?= $item['id'] ?>)" class="apv-btn solid-primary" id="approveBtn_<?= $item['id'] ?>">
                <span id="approveBtnText_<?= $item['id'] ?>">Setujui</span>
              </button>
            <?php endif; ?>
          </div>
        </div>

        <?php if ($isHistory && $item['status'] === 'approved'): ?>
          <div id="deleteModal_<?= $item['id'] ?>" class="css-modal">
            <div class="modal-overlay" onclick="closeModal('deleteModal_<?= $item['id'] ?>')"></div>
            <div class="modal-content">
              <div class="modal-header">
                <h3 class="modal-title text-danger">Hapus Agenda?</h3>
                <button type="button" class="modal-close" onclick="closeModal('deleteModal_<?= $item['id'] ?>')">
                  <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                  </svg>
                </button>
              </div>
              <div class="modal-body">
                <p>Agenda <strong><?= htmlspecialchars($item['title']) ?></strong> akan dihapus permanen dari sistem dan Google Calendar.</p>
              </div>
              <div class="modal-footer">
                <form id="deleteForm_<?= $item['id'] ?>" action="/agenda/<?= $item['id'] ?>/cancel" method="POST" data-spa style="margin:0;">
                  <button type="button" class="btn-cancel" onclick="closeModal('deleteModal_<?= $item['id'] ?>')">Batal</button>
                  <button type="submit" class="btn-confirm danger">Ya, Hapus</button>
                </form>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <?php if (!$isHistory): ?>
          <div id="rejectModal_<?= $item['id'] ?>" class="css-modal">
            <div class="modal-overlay" onclick="closeModal('rejectModal_<?= $item['id'] ?>')"></div>
            <div class="modal-content">
              <div class="modal-header">
                <h3 class="modal-title">Tolak Agenda</h3>
                <button type="button" class="modal-close" onclick="closeModal('rejectModal_<?= $item['id'] ?>')">
                  <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                  </svg>
                </button>
              </div>
              <div class="modal-body" style="text-align: left;">
                <p>Silakan berikan alasan penolakan untuk agenda <strong><?= htmlspecialchars($item['title']) ?></strong>:</p>
                <form id="rejectCommentForm_<?= $item['id'] ?>" action="/approval/<?= $item['id'] ?>/reject" method="POST" data-spa style="margin-top: 1rem;">
                  <textarea name="comment" class="apv-textarea" rows="3" placeholder="Contoh: Ruangan sudah dipakai acara lain..." required></textarea>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('rejectModal_<?= $item['id'] ?>')">Batal</button>
                <button type="submit" form="rejectCommentForm_<?= $item['id'] ?>" class="btn-confirm danger">Tolak Pengajuan</button>
              </div>
            </div>
          </div>

          <div id="approveModal_<?= $item['id'] ?>" class="css-modal">
            <div class="modal-overlay" onclick="closeModal('approveModal_<?= $item['id'] ?>')"></div>
            <div class="modal-content">
              <div class="modal-header">
                <h3 class="modal-title">Setujui Agenda?</h3>
                <button type="button" class="modal-close" onclick="closeModal('approveModal_<?= $item['id'] ?>')">
                  <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                  </svg>
                </button>
              </div>
              <div class="modal-body">
                <p>Agenda <strong><?= htmlspecialchars($item['title']) ?></strong> akan diproses dan disinkronisasi ke Google Calendar peserta.</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('approveModal_<?= $item['id'] ?>')">Batal</button>
                <button type="button" onclick="submitApprove(<?= $item['id'] ?>)" class="btn-confirm success">Ya, Setujui</button>
              </div>
            </div>
          </div>
        <?php endif; ?>

      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>