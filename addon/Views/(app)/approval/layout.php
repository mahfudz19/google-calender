<div class="app-layout">

  <aside class="app-sidebar">
    <a data-spa href="<?= getBaseUrl('/dashboard') ?>" class="app-btn-back">
      <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      Kembali ke Dashboard
    </a>

    <link rel="stylesheet" href="<?= getBaseUrl('/components-js/queue-widget/style.css') ?>">
    <div id="queue-widget" class="queue-widget"></div>
    <script>
      window.SWR_CONFIG = {
        interval: 10000,
        cacheKey: 'mazu_qw_cache',
        apiEndpoint: '<?= getBaseUrl('/queue') ?>',
        getBaseUrl: '<?= getBaseUrl() ?>',
      };
    </script>
    <script src="<?= getBaseUrl('/components-js/queue-widget/index.js') ?>"></script>
  </aside>

  <main class="app-main apv-main">
    <header class="apv-header">
      <div class="apv-header-text">
        <h1 class="apv-page-title">Persetujuan Agenda</h1>
        <p class="apv-page-subtitle">Kelola dan tinjau permintaan penggunaan ruangan serta jadwal kegiatan.</p>
      </div>

      <nav class="apv-filter-chips">
        <a data-spa href="<?= getBaseUrl('/approval') ?>"
          class="apv-chip <?= strpos($_SERVER['REQUEST_URI'], getBaseUrl('/approval/history')) === false ? 'active' : '' ?>">
          Menunggu
          <?php if (!empty($await)): ?>
            <span class="apv-chip-badge"><?= ($await) ?></span>
          <?php endif; ?>
        </a>
        <a data-spa href="<?= getBaseUrl('/approval/history') ?>"
          class="apv-chip <?= strpos($_SERVER['REQUEST_URI'], getBaseUrl('/approval/history')) !== false ? 'active' : '' ?>">
          Riwayat
        </a>
      </nav>
    </header>

    <div class="apv-list-container" data-layout="addon/Views/(app)/approval/layout.php">
      <?= $children ?>
    </div>
  </main>

</div>