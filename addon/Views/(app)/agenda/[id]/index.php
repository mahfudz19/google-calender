<?php
$agenda = $agenda ?? [];
// Pastikan variabel ini memeriksa apakah user berhak melakukan approval
$isApprover = $_SESSION['user']['role'] === 'admin' || $_SESSION['user']['role'] === 'approver';
?>

<div class="agenda-container">
  <div class="gcal-form-wrapper">

    <div class="gcal-form-header">
      <div class="header-left">
        <a data-spa href="<?= getBaseUrl('/agenda') ?>" class="btn-close-icon" title="Kembali ke Daftar">
          <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
          </svg>
        </a>
      </div>
      <div class="header-right" style="display: flex; gap: 8px; align-items: center;">
        <?php if ($agenda['status'] === 'pending'): ?>

          <a data-spa href="<?= getBaseUrl('/agenda/' . $agenda['id'] . '/edit') ?>" class="icon-action-btn" title="Edit Agenda">
            <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
          </a>
          <button type="button" class="icon-action-btn text-danger" title="Batalkan Agenda" onclick="document.getElementById('modal-cancel-<?= $agenda['id'] ?>').classList.add('show')">
            <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="3 6 5 6 21 6"></polyline>
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
              <line x1="10" y1="11" x2="10" y2="17"></line>
              <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
          </button>

          <?php if ($isApprover): ?>
            <div style="width: 1px; height: 24px; background-color: var(--border-light); margin: 0 4px;"></div>

            <button type="button" class="btn-action danger" style="padding: 8px 16px;" onclick="document.getElementById('modal-reject-<?= $agenda['id'] ?>').classList.add('show')">Tolak</button>
            <button type="button" class="btn-save-gcal" style="padding: 8px 20px; font-size: 13px;" onclick="document.getElementById('modal-approve-<?= $agenda['id'] ?>').classList.add('show')">Setujui</button>
          <?php endif; ?>

        <?php endif; ?>
      </div>
    </div>

    <div class="gcal-form-body" style="padding-bottom: 32px;">
      <?php
      // Konfigurasi warna status
      $dotColor = 'var(--warning-main)';
      $statusText = 'Menunggu Persetujuan';
      $badgeClass = 'badge-warning';
      if ($agenda['status'] === 'approved') {
        $dotColor = 'var(--success-main)';
        $statusText = 'Disetujui';
        $badgeClass = 'badge-success';
      } elseif ($agenda['status'] === 'rejected') {
        $dotColor = 'var(--error-main)';
        $statusText = 'Ditolak';
        $badgeClass = 'badge-error';
      }
      ?>

      <div class="gcal-row title-row" style="margin-bottom: 8px;">
        <div class="gcal-icon" style="padding-top: 10px;">
          <div style="width: 16px; height: 16px; border-radius: 50%; background-color: <?= $dotColor ?>;"></div>
        </div>
        <div class="gcal-input-wrapper">
          <h2 style="margin: 0; font-size: 1.5rem; font-weight: 400; color: var(--text-primary); font-family: 'Product Sans', 'Inter', sans-serif;">
            <?= htmlspecialchars($agenda['title']) ?>
          </h2>
        </div>
      </div>

      <div class="gcal-row" style="margin-bottom: 24px;">
        <div class="gcal-icon"></div>
        <div class="gcal-input-wrapper">
          <span class="detail-badge <?= $badgeClass ?>"><?= $statusText ?></span>
        </div>
      </div>

      <div class="gcal-row align-top">
        <div class="gcal-icon">
          <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
          </svg>
        </div>
        <div class="gcal-input-wrapper">
          <div class="gcal-detail-text">
            <?= date('l, d F Y', strtotime($agenda['start_time'])) ?><br>
            <?= date('H:i', strtotime($agenda['start_time'])) ?> – <?= date('H:i', strtotime($agenda['end_time'])) ?>
          </div>
        </div>
      </div>

      <div class="gcal-row align-top">
        <div class="gcal-icon">
          <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
        </div>
        <div class="gcal-input-wrapper">
          <div style="display: flex; align-items: center; gap: 12px;">
            <?php if (!empty($agenda['requester_avatar'])): ?>
              <img src="<?= htmlspecialchars($agenda['requester_avatar']) ?>" alt="Avatar" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
            <?php endif; ?>
            <div>
              <div class="gcal-detail-text"><?= htmlspecialchars($agenda['requester_name'] ?? 'User') ?></div>
              <div class="gcal-detail-subtext"><?= htmlspecialchars($agenda['requester_email'] ?? '') ?> • <?= ucfirst($agenda['requester_role'] ?? '') ?></div>
            </div>
          </div>
        </div>
      </div>

      <div class="gcal-row align-top">
        <div class="gcal-icon">
          <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
            <circle cx="12" cy="10" r="3"></circle>
          </svg>
        </div>
        <div class="gcal-input-wrapper">
          <?php if (!empty($agenda['ruangan_name'])): ?>
            <div class="gcal-detail-text">
              <?= htmlspecialchars($agenda['ruangan_name']) ?>
              <?php if (!empty($agenda['ruangan_capacity'])): ?>
                <span class="gcal-detail-subtext">(<?= htmlspecialchars($agenda['ruangan_capacity']) ?> org)</span>
              <?php endif; ?>
            </div>
            <?php if (!empty($agenda['ruangan_location'])): ?>
              <div class="gcal-detail-subtext"><?= htmlspecialchars($agenda['ruangan_location']) ?></div>
            <?php endif; ?>
          <?php endif; ?>

          <?php if (!empty($agenda['location'])): ?>
            <div class="gcal-detail-text" style="<?= !empty($agenda['ruangan_name']) ? 'margin-top: 8px;' : '' ?>">
              <a href="<?= (str_starts_with($agenda['location'], 'http') ? '' : 'https://') . htmlspecialchars($agenda['location']) ?>" target="_blank" rel="noopener noreferrer" style="color: var(--primary-main); text-decoration: underline; word-break: break-all;">
                <?= htmlspecialchars($agenda['location']) ?>
              </a>
            </div>
          <?php endif; ?>

          <?php if (empty($agenda['ruangan_name']) && empty($agenda['location'])): ?>
            <div class="gcal-detail-text gcal-detail-subtext">Tidak ada lokasi yang ditentukan</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="gcal-row align-top">
        <div class="gcal-icon">
          <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <line x1="21" y1="10" x2="3" y2="10"></line>
            <line x1="21" y1="6" x2="3" y2="6"></line>
            <line x1="21" y1="14" x2="3" y2="14"></line>
            <line x1="21" y1="18" x2="3" y2="18"></line>
          </svg>
        </div>
        <div class="gcal-input-wrapper">
          <div class="gcal-detail-text" style="white-space: pre-wrap; line-height: 1.6;"><?= htmlspecialchars($agenda['description'] ?? 'Tidak ada deskripsi.') ?></div>
        </div>
      </div>

      <?php if (!empty($agenda['message'])): ?>
        <div class="gcal-row align-top" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-light);">
          <div class="gcal-icon text-danger">
            <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="12"></line>
              <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
          </div>
          <div class="gcal-input-wrapper">
            <div class="gcal-detail-text text-danger" style="font-weight: 500; margin-bottom: 4px;">Alasan Penolakan:</div>
            <div class="gcal-detail-text" style="color: var(--error-dark);"><?= nl2br(htmlspecialchars($agenda['message'])) ?></div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($agenda['status'] === 'pending'): ?>
    <div id="modal-cancel-<?= $agenda['id'] ?>" class="css-modal">
      <div class="modal-overlay" onclick="this.parentElement.classList.remove('show')"></div>
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title text-danger">Batalkan Agenda?</h3>
        </div>
        <div class="modal-body" style="text-align: left;">
          <p>Apakah Anda yakin ingin membatalkan pengajuan agenda <strong><?= htmlspecialchars($agenda['title']) ?></strong>?</p>
          <p>Tindakan ini tidak dapat dikembalikan.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-cancel" onclick="this.closest('.css-modal').classList.remove('show')">Kembali</button>
          <form action="<?= getBaseUrl('/agenda/' . $agenda['id'] . '/cancel') ?>" method="post" data-spa style="margin:0;">
            <button type="submit" class="btn-confirm danger">Ya, Batalkan</button>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($isApprover && $agenda['status'] === 'pending'): ?>

    <div id="modal-reject-<?= $agenda['id'] ?>" class="css-modal">
      <div class="modal-overlay" onclick="this.parentElement.classList.remove('show')"></div>
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title">Tolak Agenda</h3>
          <button type="button" class="modal-close" onclick="this.closest('.css-modal').classList.remove('show')">
            <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
        <div class="modal-body" style="text-align: left;">
          <p>Silakan berikan alasan penolakan untuk agenda <strong><?= htmlspecialchars($agenda['title']) ?></strong>:</p>
          <form id="rejectForm_<?= $agenda['id'] ?>" action="<?= getBaseUrl('/approval/' . $agenda['id'] . '/reject') ?>" method="POST" data-spa style="margin-top: 1rem;">
            <textarea name="comment" class="gcal-input gcal-textarea" rows="3" placeholder="Contoh: Ruangan sudah dipakai acara lain..." required style="width: 100%; border: 1px solid var(--border-light); padding: 12px; border-radius: 8px; font-family: inherit; resize: vertical;"></textarea>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-cancel" onclick="this.closest('.css-modal').classList.remove('show')">Batal</button>
          <button type="submit" form="rejectForm_<?= $agenda['id'] ?>" class="btn-confirm danger">Tolak Pengajuan</button>
        </div>
      </div>
    </div>

    <div id="modal-approve-<?= $agenda['id'] ?>" class="css-modal">
      <div class="modal-overlay" onclick="this.parentElement.classList.remove('show')"></div>
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title">Setujui Agenda?</h3>
          <button type="button" class="modal-close" onclick="this.closest('.css-modal').classList.remove('show')">
            <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>

        <div class="modal-body" style="text-align: left;" id="approve-body-<?= $agenda['id'] ?>">
          <p>Agenda <strong><?= htmlspecialchars($agenda['title']) ?></strong> akan diproses dan disinkronisasi langsung ke Google Calendar peserta.</p>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-cancel" onclick="this.closest('.css-modal').classList.remove('show')">Batal</button>

          <button type="button" id="btn-submit-approve-<?= $agenda['id'] ?>" class="btn-confirm success" onclick="submitApproveDetail(<?= $agenda['id'] ?>)">Ya, Setujui</button>
        </div>
      </div>
    </div>

    <script>
      function submitApproveDetail(agendaId) {
        const btn = document.getElementById('btn-submit-approve-' + agendaId);
        const originalText = btn.innerHTML;

        // State Loading
        btn.disabled = true;
        btn.innerHTML = `<svg style="animation: apv-spin 1s linear infinite; margin-right: 8px;" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-linecap="round"></circle></svg> Memproses...`;

        // Hapus error sebelumnya jika ada
        const existingError = document.getElementById('approve-error-' + agendaId);
        if (existingError) existingError.remove();

        fetch('<?= getBaseUrl('/approval/') ?>' + agendaId + '/approve', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest' // Penting agar dikenali sebagai AJAX
            }
          })
          .then(response => response.json())
          .then(data => {
            if (data.status === 'success') {
              // Jika sukses, langsung arahkan ke Dashboard
              window.location.href = '<?= getBaseUrl('/dashboard') ?>';
            } else {
              // Jika gagal, tampilkan error dan kembalikan tombol
              showApproveError(agendaId, data.message || 'Terjadi kesalahan sistem. Tidak dapat menyetujui.');
              btn.disabled = false;
              btn.innerHTML = originalText;
            }
          })
          .catch(error => {
            console.error(error);
            showApproveError(agendaId, 'Terjadi kesalahan jaringan. Periksa koneksi Anda.');
            btn.disabled = false;
            btn.innerHTML = originalText;
          });
      }

      function showApproveError(agendaId, message) {
        const modalBody = document.getElementById('approve-body-' + agendaId);
        if (modalBody) {
          const errDiv = document.createElement('div');
          errDiv.id = 'approve-error-' + agendaId;
          errDiv.style.cssText = 'background-color: var(--error-bg); border: 1px solid var(--error-main); color: var(--error-dark); padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; display: flex; align-items: flex-start; gap: 8px; font-size: 0.9rem;';
          errDiv.innerHTML = `
            <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" style="flex-shrink: 0; margin-top: 2px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span><strong>Gagal!</strong> ${message}</span>
          `;
          modalBody.prepend(errDiv);
        }
      }
    </script>
  <?php endif; ?>

</div>