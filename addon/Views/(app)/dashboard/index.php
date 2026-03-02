<?php
$role = $role ?? 'user';
$pendingTasks = $pendingTasks ?? [];
$recentAgendas = $recentAgendas ?? [];
?>
<div class="dashboard-split">

  <div class="dash-main">
    <div class="dash-header">
      <h2 class="dash-title">Halo, <?= htmlspecialchars($_SESSION['user']['name'] ?? 'User') ?> 👋</h2>
      <p class="dash-subtitle">Selamat datang kembali di Pusat Jadwal Akademik.</p>
    </div>

    <div class="calendar-card">
      <div id="mazuCalendar"></div>
    </div>
  </div>

  <div class="dash-sidebar">

    <a href="/agenda/create" class="btn-create-agenda">
      <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="5" x2="12" y2="19"></line>
        <line x1="5" y1="12" x2="19" y2="12"></line>
      </svg>
      Ajukan Agenda Baru
    </a>

    <?php if (in_array($role, ['admin', 'approver'])): ?>
      <div class="widget-card">
        <div class="page-title">
          <span>Tugas Persetujuan</span>
          <?php if (count($pendingTasks) > 0): ?>
            <span class="badge bg-orange"><?= count($pendingTasks) ?></span>
          <?php endif; ?>
        </div>
        <div class="widget-list">
          <?php if (empty($pendingTasks)): ?>
            <div class="empty-state">Semua tugas sudah diproses! ☕</div>
          <?php else: ?>
            <?php foreach ($pendingTasks as $task): ?>
              <a href="/approval" class="widget-item">
                <div class="item-icon bg-orange">⏳</div>
                <div class="item-info">
                  <strong><?= htmlspecialchars($task['title']) ?></strong>
                  <span><?= htmlspecialchars($task['requester_name'] ?? 'Sistem') ?></span>
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="widget-card">
      <div class="page-title">Status Pengajuan Saya</div>
      <div class="widget-list">
        <?php if (empty($recentAgendas)): ?>
          <div class="empty-state">Belum ada pengajuan bulan ini.</div>
        <?php else: ?>
          <?php foreach ($recentAgendas as $agenda): ?>
            <?php
            $bgClass = 'bg-orange';
            $icon = '⏳';
            if ($agenda['status'] === 'approved') {
              $bgClass = 'bg-green';
              $icon = '✅';
            } elseif ($agenda['status'] === 'rejected') {
              $bgClass = 'bg-red';
              $icon = '❌';
            }
            ?>
            <a data-spa href="/agenda/<?= $agenda['id'] ?? '' ?>" class="widget-item">
              <div class="item-icon <?= $bgClass ?>"><?= $icon ?></div>
              <div class="item-info">
                <strong><?= htmlspecialchars($agenda['title']) ?></strong>
                <span><?= date('d M', strtotime($agenda['start_time'])) ?> • <?= ucfirst($agenda['status']) ?></span>
              </div>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<script>
  window.approvedAgendas = <?= json_encode($approvedAgendas ?? []) ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>