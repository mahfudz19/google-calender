<?php
$agendas = $agendas ?? [];
$currentStatus = $currentStatus ?? null;
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$totalAgendas = $totalAgendas ?? 0;
?>

<div class="agenda-container">

  <div class="page-header">
    <div class="header-text">
      <h2 class="page-title">Agenda Saya</h2>
      <p class="page-subtitle">Kelola dan pantau status pengajuan agenda akademik Anda.</p>
    </div>
    <a data-spa href="<?= getBaseUrl('/agenda/create') ?>" class="btn-create-primary">
      <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="5" x2="12" y2="19"></line>
        <line x1="5" y1="12" x2="19" y2="12"></line>
      </svg>
      Ajukan Agenda
    </a>
  </div>

  <div class="filter-chips">
    <a data-spa href="<?= getBaseUrl('/agenda') ?>" class="chip <?= $currentStatus === null ? 'active' : '' ?>">Semua</a>
    <a data-spa href="<?= getBaseUrl('/agenda?status=pending') ?>" class="chip <?= $currentStatus === 'pending' ? 'active' : '' ?>">Menunggu</a>
    <a data-spa href="<?= getBaseUrl('/agenda?status=approved') ?>" class="chip <?= $currentStatus === 'approved' ? 'active' : '' ?>">Disetujui</a>
    <a data-spa href="<?= getBaseUrl('/agenda?status=rejected') ?>" class="chip <?= $currentStatus === 'rejected' ? 'active' : '' ?>">Ditolak</a>
  </div>

  <div class="agenda-list-container">
    <?php if (empty($agendas)): ?>
      <div class="empty-state">
        <div class="empty-icon">📂</div>
        <h3>Belum ada agenda</h3>
        <p>Anda belum membuat pengajuan agenda apapun di kategori ini.</p>
      </div>
    <?php else: ?>
      <div class="agenda-list">
        <?php foreach ($agendas as $agenda): ?>
          <?php
          // Tentukan warna titik (Color Dot) dan teks status
          $dotColor = 'var(--warning-main)';
          $statusText = 'Menunggu';
          if ($agenda['status'] === 'approved') {
            $dotColor = 'var(--success-main)';
            $statusText = 'Disetujui';
          } elseif ($agenda['status'] === 'rejected') {
            $dotColor = 'var(--error-main)';
            $statusText = 'Ditolak';
          }
          ?>
          <div class="agenda-item">
            <div class="item-main">
              <div class="status-dot" style="background-color: <?= $dotColor ?>;"></div>

              <div class="item-info">
                <h3 class="item-title"><?= htmlspecialchars($agenda['title']) ?></h3>
                <div class="item-meta">
                  <span class="meta-date">
                    <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                      <line x1="16" y1="2" x2="16" y2="6"></line>
                      <line x1="8" y1="2" x2="8" y2="6"></line>
                      <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <?= date('d M Y, H:i', strtotime($agenda['start_time'])) ?>
                  </span>
                  <span class="meta-status" style="color: <?= $dotColor ?>;">• <?= $statusText ?></span>
                </div>
              </div>
            </div>

            <div class="item-actions">
              <?php if ($agenda['status'] === 'pending'): ?>
                <a data-spa href="<?= getBaseUrl('/agenda/' . $agenda['id'] . '/edit') ?>" class="btn-action">Edit</a>

                <button type="button" class="btn-action danger" onclick="document.getElementById('modal-cancel-<?= $agenda['id'] ?>').classList.add('show')">Batalkan</button>

                <div id="modal-cancel-<?= $agenda['id'] ?>" class="css-modal">
                  <div class="modal-overlay" onclick="this.parentElement.classList.remove('show')"></div>
                  <div class="modal-content">
                    <div class="modal-header">
                      <h3 class="modal-title text-danger">
                        <svg viewBox="0 0 24 24" width="22" height="22" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                          <line x1="12" y1="9" x2="12" y2="13"></line>
                          <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        Batalkan Agenda?
                      </h3>
                      <button type="button" class="modal-close" onclick="this.closest('.css-modal').classList.remove('show')">
                        <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                          <line x1="18" y1="6" x2="6" y2="18"></line>
                          <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                      </button>
                    </div>
                    <div class="modal-body">
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

              <?php else: ?>
                <a data-spa href="<?= getBaseUrl('/agenda/' . $agenda['id']) ?>" class="btn-action outline">Lihat Detail</a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($totalPages > 1): ?>
    <div class="pagination-wrap">
      <span class="page-info">Menampilkan <?= count($agendas) ?> dari <?= $totalAgendas ?> agenda</span>
      <div class="pagination">
        <?php
        $prevDisabled = $currentPage <= 1 ? 'disabled' : '';
        $nextDisabled = $currentPage >= $totalPages ? 'disabled' : '';
        $statusQuery = $currentStatus ? "&status={$currentStatus}" : "";
        ?>
        <a data-spa href="<?= getBaseUrl('/agenda?page=' . ($currentPage - 1) . $statusQuery) ?>" class="btn-page <?= $prevDisabled ?>">Sebelumnya</a>
        <span class="page-current"><?= $currentPage ?> dari <?= $totalPages ?></span>
        <a data-spa href="<?= getBaseUrl('/agenda?page=' . ($currentPage + 1) . $statusQuery) ?>" class="btn-page <?= $nextDisabled ?>">Selanjutnya</a>
      </div>
    </div>
  <?php endif; ?>

</div>