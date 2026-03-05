<div class="approval-container">

  <div class="approval-widget">
    <link rel="stylesheet" href="<?= getBaseUrl('/components-js/queue-widget/style.css') ?>">
    <div id="queue-widget" class="queue-widget"></div>
    <script>
      const SWR_CONFIG = {
        interval: 10000,
        cacheKey: 'mazu_qw_cache',
        apiEndpoint: '<?= getBaseUrl('/queue') ?>',
      };
    </script>
    <script src="<?= getBaseUrl('/components-js/queue-widget/index.js') ?>"></script>
  </div>

  <div class="approval-content">
    <div class="page-header">
      <div class="header-content">
        <h1>Persetujuan Agenda</h1>
        <p class="subtitle">Kelola permintaan penggunaan ruangan dan jadwal kegiatan.</p>
      </div>
      <div class="header-actions">
        <div class="filter-tabs">
          <a data-spa href="<?= getBaseUrl('/approval') ?>" class="tab-btn <?= strpos($_SERVER['REQUEST_URI'], getBaseUrl('/approval/history')) === false ? 'active' : '' ?>">
            Menunggu
            <?php if (isset($approvals) && strpos($_SERVER['REQUEST_URI'], getBaseUrl('/approval/history')) === false): ?>
              <span class="badge"><?= count($approvals) ?></span>
            <?php endif; ?>
          </a>
          <a data-spa href="<?= getBaseUrl('/approval/history') ?>" class="tab-btn <?= strpos($_SERVER['REQUEST_URI'], getBaseUrl('/approval/history')) !== false ? 'active' : '' ?>">Riwayat</a>
        </div>
      </div>
    </div>

    <div class="approval-list" data-layout="addon/Views/(app)/approval/layout.php">
      <?= $children ?>
    </div>
  </div>

</div>