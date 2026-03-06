<div class="approval-layout-wrapper">

  <aside class="approval-sidebar">
    <link rel="stylesheet" href="<?= getBaseUrl('/components-js/queue-widget/style.css') ?>">
    <div id="queue-widget" class="queue-widget"></div>
    <script>
      window.SWR_CONFIG = {
        interval: 10000,
        cacheKey: 'mazu_qw_cache',
        apiEndpoint: '<?= getBaseUrl('/queue') ?>',
      };
    </script>
    <script src="<?= getBaseUrl('/components-js/queue-widget/index.js') ?>"></script>
  </aside>

  <main class="approval-main">
    <header class="approval-header">
      <div class="header-text">
        <h1 class="page-title">Persetujuan Agenda</h1>
        <p class="page-subtitle">Kelola dan tinjau permintaan penggunaan ruangan serta jadwal kegiatan.</p>
      </div>

      <nav class="approval-tabs">
        <a data-spa href="<?= getBaseUrl('/approval') ?>"
          class="tab-item <?= strpos($_SERVER['REQUEST_URI'], getBaseUrl('/approval/history')) === false ? 'active' : '' ?>">
          Menunggu
          <?php if (isset($approvals) && strpos($_SERVER['REQUEST_URI'], getBaseUrl('/approval/history')) === false): ?>
            <span class="tab-badge"><?= count($approvals) ?></span>
          <?php endif; ?>
        </a>
        <a data-spa href="<?= getBaseUrl('/approval/history') ?>"
          class="tab-item <?= strpos($_SERVER['REQUEST_URI'], getBaseUrl('/approval/history')) !== false ? 'active' : '' ?>">
          Riwayat
        </a>
      </nav>
    </header>

    <div class="approval-list-container" data-layout="addon/Views/(app)/approval/layout.php">
      <?= $children ?>
    </div>
  </main>

</div>