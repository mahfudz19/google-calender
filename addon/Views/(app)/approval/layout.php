<div class="approval-container">
  <header class="page-header">
    <div class="header-content">
      <h1>Persetujuan Agenda</h1>
      <p class="subtitle">Kelola permintaan penggunaan ruangan dan jadwal kegiatan.</p>
    </div>
    <div class="header-actions">
      <div class="filter-tabs">
        <a data-spa href="/approval" class="tab-btn <?= strpos($_SERVER['REQUEST_URI'], '/approval/history') === false ? 'active' : '' ?>">
          Menunggu
          <?php if (isset($approvals) && strpos($_SERVER['REQUEST_URI'], '/approval/history') === false): ?>
            <span class="badge"><?= count($approvals) ?></span>
          <?php endif; ?>
        </a>
        <a data-spa href="/approval/history" class="tab-btn <?= strpos($_SERVER['REQUEST_URI'], '/approval/history') !== false ? 'active' : '' ?>">Riwayat</a>
      </div>
    </div>
  </header>

  <div class="approval-list" data-layout="addon/Views/(app)/approval/layout.php">
    <?= $children ?>
  </div>
</div>