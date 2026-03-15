<?php
$userSession = $_SESSION['user'] ?? [];
$currentUser = [
  'name' => $userSession['name'] ?? 'Unknown User',
  'email' => $userSession['email'] ?? 'unknown@email.com',
  'role' => $userSession['role'] ?? 'user',
  'avatar' => $userSession['avatar'] ?? ''
];
?>

<div class="app-layout">
  <aside class="app-sidebar">
    <a data-spa href="<?= getBaseUrl('/dashboard') ?>" class="app-btn-back">
      <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      Kembali ke Dashboard
    </a>
    <div class="blk-widget">
      <div class="blk-widget-header">Alur Pemeriksaan</div>
      <ul class="blk-steps">
        <li class="blk-step disabled" id="step-ruangan">
          <div class="blk-step-head">
            <span class="blk-step-title">1. Check Ruangan</span>
            <button class="blk-btn-step" id="btn-check-ruangan" disabled>Check</button>
          </div>
          <p class="blk-step-desc">Memastikan ID ruangan terdaftar di database server.</p>
        </li>
        <li class="blk-step disabled" id="step-internal">
          <div class="blk-step-head">
            <span class="blk-step-title">2. Check Internal</span>
            <button class="blk-btn-step" id="btn-check-internal" disabled>Check</button>
          </div>
          <p class="blk-step-desc">Mendeteksi bentrok jadwal di dalam file CSV.</p>
        </li>
        <li class="blk-step disabled" id="step-db">
          <div class="blk-step-head">
            <span class="blk-step-title">3. Check Database</span>
            <button class="blk-btn-step" id="btn-check-db" disabled>Check</button>
          </div>
          <p class="blk-step-desc">Mencocokkan jadwal dengan data di server Mazu.</p>
        </li>
      </ul>
      <div class="blk-upload-action">
        <button class="blk-btn-upload" id="btn-upload-all" disabled>
          <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="17 8 12 3 7 8"></polyline>
            <line x1="12" y1="3" x2="12" y2="15"></line>
          </svg>
          Upload Semua Data
        </button>
      </div>
    </div>

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

  <main class="app-main">

    <div id="upload-section" class="blk-upload-section">
      <div class="blk-upload-header">
        <h2 class="blk-title">Upload File CSV</h2>
        <p class="blk-subtitle">Pilih file CSV untuk diunggah. Pastikan format kolom sesuai dengan template.</p>
      </div>

      <div class="blk-dropzone" id="csv-upload-zone">
        <div class="blk-dropzone-icon" id="csv-upload-icon">
          <svg viewBox="0 0 24 24" width="48" height="48" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="12" y1="18" x2="12" y2="12"></line>
            <polyline points="9 15 12 12 15 15"></polyline>
          </svg>
        </div>
        <div class="spinner-mini" id="csv-upload-spinner" style="display: none; width: 40px; height: 40px; border-width: 4px; margin: 0 auto 1rem auto; border-top-color: var(--primary-main);"></div>

        <p class="blk-dropzone-text" id="csv-upload-main-text">Seret dan lepas file CSV di sini</p>
        <p class="blk-dropzone-subtext" id="csv-upload-subtext">atau</p>

        <label for="csv-file-input" class="blk-btn-select" id="csv-upload-btn-label">Pilih File</label>
        <input type="file" id="csv-file-input" accept=".csv" class="blk-file-input">
      </div>

      <div class="blk-info-grid">
        <div class="blk-info-card">
          <span class="blk-info-icon">📄</span>
          <span class="blk-info-text">Format file: CSV (.csv)</span>
          <a href="<?= getBaseUrl('/example/contoh_agenda.csv') ?>" download="contoh_agenda.csv" class="blk-download-link">
            Unduh Contoh
          </a>
        </div>
        <div class="blk-info-card">
          <span class="blk-info-icon">👤</span>
          <span class="blk-info-text">Pengaju: <strong><?= htmlspecialchars($currentUser['name']) ?></strong></span>
        </div>
      </div>
    </div>

    <div id="preview-section" class="blk-preview-section" style="display: none;">
      <div class="blk-preview-header">
        <h3 class="blk-preview-title">Preview Data (<span id="preview-count">0</span> Baris)</h3>
        <button type="button" id="btn-reset-data" class="blk-btn-reset">
          <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
            <polyline points="3 6 5 6 21 6"></polyline>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
          </svg>
          Hapus Data
        </button>
      </div>

      <div class="blk-table-wrapper">
        <table class="blk-table">
          <thead>
            <tr>
              <th width="5%">No</th>
              <th width="25%">Detail Agenda</th>
              <th width="25%">Waktu Pelaksanaan</th>
              <th width="25%">Pemohon</th>
              <th width="20%">Ruangan</th>
            </tr>
          </thead>
          <tbody id="csv-preview-tbody">
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<div id="csvGlobalModal" class="css-modal">
  <div class="modal-overlay" id="csvModalOverlay"></div>
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title" id="csvModalTitle">Pemberitahuan</h3>
      <button type="button" class="modal-close" id="csvModalCloseBtn">
        <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>
    <div class="modal-body" style="text-align: left;">
      <div class="modal-icon-wrapper" id="csvModalIcon" style="margin: 0 auto 16px; display: none;"></div>
      <p id="csvModalText">Pesan akan muncul di sini</p>
    </div>
    <div class="modal-footer" id="csvModalFooter">
      <button type="button" class="btn-cancel" id="csvModalCancelBtn">Batal</button>
      <button type="button" class="btn-confirm" id="csvModalConfirmBtn">OK</button>
    </div>
  </div>
</div>

<link rel="stylesheet" href="<?= getBaseUrl('/components-js/autocomplete/style.32ecab46.min.css') ?>">

<script type="module">
  import Autocomplete from "<?= getBaseUrl('/components-js/autocomplete/index.2586e72a.min.js') ?>";
  import {
    initCsvUploader
  } from "<?= getBaseUrl('/components-js/input-many/script.js') ?>";

  const currentUser = <?= json_encode($currentUser) ?>;
  const apiCheckDbUrl = "<?= getBaseUrl('/input-many/check-database') ?>";
  const apiUploadUrl = "<?= getBaseUrl('/input-many/upload') ?>";
  const apiRuanganUrl = "<?= getBaseUrl('/api/ruangan') ?>";

  function runInit() {
    document.querySelectorAll('body > .autocomplete-list-wrapper').forEach(el => el.remove());
    initCsvUploader(Autocomplete, currentUser, apiCheckDbUrl, apiUploadUrl, apiRuanganUrl);
  }

  setTimeout(runInit, 50);

  if (!window.mazuCsvInit) {
    window.addEventListener('spa:before-navigate', () => {
      document.querySelectorAll('body > .autocomplete-list-wrapper').forEach(el => el.remove());
    });
    window.addEventListener('spa:navigated', () => {
      setTimeout(runInit, 50);
    });
    window.mazuCsvInit = true;
  }
</script>