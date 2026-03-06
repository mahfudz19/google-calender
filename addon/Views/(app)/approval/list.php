<?php
$errorCode = $_GET['error'] ?? null;
$errorMessage = $_GET['message'] ?? null;
$displayError = null;

if ($errorCode && $errorMessage) {
  $decodedMessage = urldecode($errorMessage);
  switch ($errorCode) {
    case '500':
      $displayError = strpos($decodedMessage, 'Conflict detected') !== false
        ? "Conflict detected! Agenda yang ingin disetujui bertabrakan dengan agenda lain."
        : (strpos($decodedMessage, 'user_controller') !== false
          ? "Terjadi kesalahan sistem: Tidak dapat mengakses data pengguna Google."
          : "Terjadi kesalahan server: " . $decodedMessage);
      break;
    case 'conflict':
      $displayError = strpos($decodedMessage, 'Conflict detected') !== false
        ? "Conflict detected! Agenda yang ingin disetujui bertabrakan dengan agenda lain."
        : $decodedMessage;
      break;
    case '400':
      $displayError = "Data yang dikirim tidak valid: " . $decodedMessage;
      break;
    default:
      $displayError = $decodedMessage;
  }
}
?>

<div id="mazu-approval-inner">

  <?php if ($displayError): ?>
    <div class="modern-alert error" id="globalErrorAlert">
      <div class="alert-icon">⚠️</div>
      <div class="alert-body">
        <h4 class="alert-title">Terjadi Kesalahan</h4>
        <p class="alert-text"><?= htmlspecialchars($displayError) ?></p>
      </div>
      <button class="alert-close" onclick="this.closest('.modern-alert').remove()">&times;</button>
    </div>
  <?php endif; ?>

  <?php if (empty($approvals)): ?>
    <div class="modern-empty-state">
      <div class="empty-icon-wrapper">
        <span class="empty-icon">✨</span>
      </div>
      <h3 class="empty-title">Semua Bersih!</h3>
      <p class="empty-desc">Tidak ada permintaan agenda yang memerlukan persetujuan saat ini.</p>
    </div>
  <?php else: ?>
    <div class="modern-list-group">
      <?php foreach ($approvals as $item):
        $startDate = new DateTime($item['start_time']);
        $endDate = new DateTime($item['end_time']);
        $dateStr = $startDate->format('d M Y');
        $timeStr = $startDate->format('H:i') . ' - ' . $endDate->format('H:i');
        $isHistory = $item['status'] !== 'pending';
      ?>

        <div class="modern-list-item" data-id="<?= $item['id'] ?>">

          <div class="item-requester">
            <?php if ($item['requester_avatar']): ?>
              <img src="<?= $item['requester_avatar'] ?>" alt="Avatar" class="req-avatar">
            <?php else: ?>
              <div class="req-avatar fallback"><?= strtoupper(substr($item['requester_name'] ?? 'U', 0, 1)) ?></div>
            <?php endif; ?>
            <div class="req-info">
              <span class="req-name"><?= htmlspecialchars($item['requester_name'] ?? 'Unknown User') ?></span>
              <span class="req-role"><?= htmlspecialchars($item['requester_role'] ?? 'User') ?></span>
            </div>
          </div>

          <div class="item-details">
            <h3 class="event-title"><?= htmlspecialchars($item['title']) ?></h3>
            <p class="event-desc"><?= htmlspecialchars($item['description'] ?? 'Tidak ada deskripsi tambahan.') ?></p>

            <div class="event-meta">
              <span class="meta-badge date-time">
                🕒 <?= $dateStr ?> • <?= $timeStr ?>
              </span>
              <?php if ($item['location']): ?>
                <span class="meta-badge location">
                  📍 <?= htmlspecialchars($item['location']) ?>
                </span>
              <?php endif; ?>
            </div>
          </div>

          <div class="item-actions-panel">
            <?php if ($isHistory): ?>
              <div class="status-pill <?= $item['status'] ?>">
                <?php if ($item['status'] === 'processing'): ?>
                  <span class="spinner-mini"></span> Memproses...
                <?php else: ?>
                  <?= ucfirst($item['status']) ?>
                <?php endif; ?>
              </div>

              <?php if ($item['status'] === 'approved'): ?>
                <div class="action-buttons">
                  <a data-spa href="<?= getBaseUrl('/agenda/' . $item['id'] . '/edit') ?>" class="btn-icon outline" title="Edit Agenda">✏️</a>
                  <button type="button" onclick="openModal('deleteModal_<?= $item['id'] ?>')" class="btn-icon danger outline" title="Hapus Agenda">🗑️</button>
                </div>
              <?php endif; ?>

            <?php else: ?>
              <div class="action-buttons full-width">
                <button type="button" onclick="openModal('rejectModal_<?= $item['id'] ?>')" class="btn-modern btn-reject-outline">Tolak</button>
                <button type="button" onclick="handleApprove(<?= $item['id'] ?>)" class="btn-modern btn-approve-solid" id="approveBtn_<?= $item['id'] ?>">
                  <span id="approveBtnText_<?= $item['id'] ?>">Setujui</span>
                </button>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <?php if ($isHistory): ?>
          <?php if ($item['status'] === 'approved'): ?>
            <div id="deleteModal_<?= $item['id'] ?>" class="css-modal">
              <div class="modal-overlay" onclick="closeModal('deleteModal_<?= $item['id'] ?>')"></div>
              <div class="modal-content">
                <div class="modal-header">
                  <h3 class="modal-title text-danger">⚠️ Konfirmasi Hapus</h3>
                  <button type="button" class="modal-close" onclick="closeModal('deleteModal_<?= $item['id'] ?>')">&times;</button>
                </div>
                <div class="modal-body">
                  <p>Apakah anda yakin ingin menghapus agenda ini?</p>
                  <p><strong><?= htmlspecialchars($item['title']) ?></strong></p>
                  <p style="color: var(--md-sys-color-error); font-size: 0.85rem; margin-top: 1rem;">Agenda akan dihapus dari kalender semua peserta.</p>
                </div>
                <div class="modal-footer">
                  <form id="deleteForm_<?= $item['id'] ?>" action="/agenda/<?= $item['id'] ?>/cancel" method="POST" data-spa>
                    <button type="button" class="btn-cancel" onclick="closeModal('deleteModal_<?= $item['id'] ?>')">Batal</button>
                    <button type="submit" class="btn-confirm danger">Ya, Hapus Agenda</button>
                  </form>
                </div>
              </div>
            </div>
          <?php endif; ?>

        <?php else: ?>
          <div id="rejectModal_<?= $item['id'] ?>" class="css-modal">
            <div class="modal-overlay" onclick="closeModal('rejectModal_<?= $item['id'] ?>')"></div>
            <div class="modal-content">
              <div class="modal-header">
                <h3 class="modal-title">Konfirmasi Penolakan</h3>
                <button type="button" class="modal-close" onclick="closeModal('rejectModal_<?= $item['id'] ?>')">&times;</button>
              </div>
              <div class="modal-body" style="text-align: left;">
                <p>Apakah anda yakin ingin menolak pengajuan agenda ini?</p>
                <p class="text-center" style="margin: 1rem 0;"><strong><?= htmlspecialchars($item['title']) ?></strong></p>
                <form id="rejectCommentForm_<?= $item['id'] ?>" action="/approval/<?= $item['id'] ?>/reject" method="POST" data-spa>
                  <div class="form-group">
                    <label class="form-label">Alasan Penolakan <span style="color:red">*</span></label>
                    <textarea name="comment" class="form-control" rows="3" placeholder="Berikan alasan yang jelas..." required></textarea>
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('rejectModal_<?= $item['id'] ?>')">Batal</button>
                <button type="submit" form="rejectCommentForm_<?= $item['id'] ?>" class="btn-confirm danger">Ya, Tolak</button>
              </div>
            </div>
          </div>

          <div id="approveModal_<?= $item['id'] ?>" class="css-modal">
            <div class="modal-overlay" onclick="closeModal('approveModal_<?= $item['id'] ?>')"></div>
            <div class="modal-content">
              <div class="modal-header">
                <h3 class="modal-title">Konfirmasi Persetujuan</h3>
                <button type="button" class="modal-close" onclick="closeModal('approveModal_<?= $item['id'] ?>')">&times;</button>
              </div>
              <div class="modal-body">
                <div class="modal-icon-wrapper success" style="margin: 0 auto 1rem;">✨</div>
                <p>Apakah anda yakin ingin menyetujui pengajuan agenda ini?</p>
                <p><strong><?= htmlspecialchars($item['title']) ?></strong></p>
                <p style="color: var(--md-sys-color-primary); font-size: 0.85rem; margin-top: 1rem;">Agenda akan disetujui dan otomatis masuk ke kalender.</p>
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