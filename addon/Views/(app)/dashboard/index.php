<?php
$role = $role ?? 'user';
$pendingTasks = $pendingTasks ?? [];
$recentAgendas = $recentAgendas ?? [];
?>
<div class="app-layout">

  <aside class="app-sidebar">

    <div class="create-btn-wrapper">
      <a data-spa href="<?= getBaseUrl('/agenda/create') ?>" class="btn-create-event">
        <svg width="36" height="36" viewBox="0 0 36 36">
          <path fill="#34A853" d="M16 16v14h4V20z"></path>
          <path fill="#4285F4" d="M30 16H20l-4 4h14z"></path>
          <path fill="#FBBC05" d="M6 16v4h10l4-4z"></path>
          <path fill="#EA4335" d="M20 16V6h-4v14z"></path>
          <path fill="none" d="M0 0h36v36H0z"></path>
        </svg>
        <span>Buat</span>
      </a>
    </div>

    <div class="subscribe-btn-wrapper">
      <a href="https://calendar.google.com/calendar/render?cid=<?= env('GLOBAL_CALENDAR_ID') ?>" target="_blank" class="btn-subscribe-calendar">
        <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="16" y1="2" x2="16" y2="6"></line>
          <line x1="8" y1="2" x2="8" y2="6"></line>
          <line x1="3" y1="10" x2="21" y2="10"></line>
          <line x1="12" y1="14" x2="12" y2="18"></line>
          <line x1="10" y1="16" x2="14" y2="16"></line>
        </svg>
        <span>+ Kalender Akademik</span>
      </a>
    </div>

    <?php if (in_array($role, ['admin', 'approver'])): ?>
      <div class="sidebar-section">
        <div class="section-header">
          <h3 class="section-title">Tugas Persetujuan</h3>
          <a data-spa href="<?= getBaseUrl('/approval') ?>" class="link-see-all">Lihat Semua</a>
        </div>
        <div class="section-content">
          <?php if (count($pendingTasks) > 0): ?>
            <?php foreach ($pendingTasks as $task): ?>
              <a data-spa href="<?= getBaseUrl('/agenda/' . $task['id'] ?? '') ?>" class="sidebar-card">
                <div class="card-icon-box" style="color: var(--info-dark); background-color: var(--info-bg);">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                  </svg>
                </div>
                <div class="card-info">
                  <h4 class="card-title"><?= htmlspecialchars($task['title'] ?? 'Tugas Baru') ?></h4>
                  <div class="card-meta">Perlu tindakan</div>
                </div>
              </a>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-state-sidebar">
              <span class="empty-emoji">👍</span>
              <p>Semua tugas selesai</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="sidebar-section">
      <div class="section-header">
        <h3 class="section-title">Pengajuan Saya</h3>
        <a data-spa href="<?= getBaseUrl('/agenda') ?>" class="link-see-all">Lihat Semua</a>
      </div>
      <div class="section-content">
        <?php if (empty($recentAgendas)): ?>
          <div class="empty-state-sidebar">
            <span class="empty-emoji">📅</span>
            <p>Belum ada pengajuan</p>
          </div>
        <?php else: ?>
          <?php foreach ($recentAgendas as $agenda): ?>
            <?php
            if ($agenda['status'] === 'approved') {
              $iconColor = 'var(--success-dark)';
              $iconBg = 'var(--success-bg)';
              $statusText = 'Disetujui';
              $iconSvg = '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>';
            } elseif ($agenda['status'] === 'rejected') {
              $iconColor = 'var(--error-dark)';
              $iconBg = 'var(--error-bg)';
              $statusText = 'Ditolak';
              $iconSvg = '<circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>';
            } else {
              $iconColor = 'var(--warning-dark)';
              $iconBg = 'var(--warning-bg)';
              $statusText = 'Menunggu';
              $iconSvg = '<circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>';
            }
            ?>
            <a data-spa href="<?= getBaseUrl('/agenda/' . $agenda['id'] ?? '') ?>" class="sidebar-card">
              <div class="card-icon-box" style="color: <?= $iconColor ?>; background-color: <?= $iconBg ?>;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <?= $iconSvg ?>
                </svg>
              </div>
              <div class="card-info">
                <h4 class="card-title"><?= htmlspecialchars($agenda['title']) ?></h4>
                <div class="card-meta">
                  <span><?= date('d M', strtotime($agenda['start_time'])) ?></span>
                  <span style="color: <?= $iconColor ?>; font-weight: 500;"><?= $statusText ?></span>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

  </aside>

  <main class="app-main">
    <div id="mazuCalendar" class="google-calendar-wrap"></div>
  </main>

</div>

<div id="modal-event-detail" class="css-modal">
  <div class="modal-overlay" onclick="this.parentElement.classList.remove('show')"></div>
  <div class="modal-content" style="max-width: 448px; padding: 0; border-radius: 24px;">
    
    <div class="modal-header" style="border-bottom: none; padding: 12px 12px 0; justify-content: flex-end;">
      <button type="button" class="modal-close" style="background: transparent; border: none; cursor: pointer; padding: 8px; border-radius: 50%; color: #5f6368;" onclick="document.getElementById('modal-event-detail').classList.remove('show')">
        <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
      </button>
    </div>

    <div class="modal-body" style="padding: 0 24px 24px 24px; text-align: left;">
      
      <div class="ds-modal-row">
        <div class="ds-modal-icon" style="padding-top: 6px;">
          <div style="width: 14px; height: 14px; border-radius: 50%; background-color: var(--primary-main);"></div>
        </div>
        <div class="ds-modal-content-wrap">
          <h2 id="modal-ev-title" class="ds-modal-title">Judul Agenda</h2>
        </div>
      </div>

      <div class="ds-modal-row">
        <div class="ds-modal-icon">
          <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        </div>
        <div class="ds-modal-content-wrap" id="modal-ev-time">
          </div>
      </div>

      <div class="ds-modal-row">
        <div class="ds-modal-icon">
          <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
        </div>
        <div class="ds-modal-content-wrap" id="modal-ev-location">
          </div>
      </div>

      <div class="ds-modal-row">
        <div class="ds-modal-content-wrap" id="modal-ev-requester"></div>
      </div>

      <div class="ds-modal-row">
        <div class="ds-modal-icon">
          <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><line x1="21" y1="10" x2="3" y2="10"></line><line x1="21" y1="6" x2="3" y2="6"></line><line x1="21" y1="14" x2="3" y2="14"></line><line x1="21" y1="18" x2="3" y2="18"></line></svg>
        </div>
        <div class="ds-modal-content-wrap">
          <div id="modal-ev-desc" class="ds-modal-text">Deskripsi</div>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
  window.approvedAgendas = <?= json_encode($approvedAgendas ?? []) ?>;
  console.log(window.approvedAgendas);
</script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>