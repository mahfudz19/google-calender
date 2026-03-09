<?php
// Ambil data user dari session (Sesuaikan dengan struktur framework Mazu Anda)
$userSession = $_SESSION['user'] ?? [];
$currentUser = [
  'name' => $userSession['name'] ?? 'Unknown User',
  'email' => $userSession['email'] ?? 'unknown@email.com',
  'role' => $userSession['role'] ?? 'user',
  'avatar' => $userSession['avatar'] ?? ''
];
?>

<div class="input-many-layout-wrapper">
  <aside class="input-many-sidebar">
    <div class="input-many-checking-widget">
      <div class="input-many-checking-widget-title" style="margin-bottom: 1rem; font-weight: 700;">Alur Pemeriksaan</div>

      <ul class="input-many-step-list">

        <li class="step-item disabled" id="step-ruangan">
          <div class="step-header">
            <span class="step-title">1. Check Ruangan</span>
            <button class="btn-step" id="btn-check-ruangan" disabled>Check</button>
          </div>
          <p class="step-desc">Memastikan ID ruangan pada file CSV terdaftar di database.</p>
        </li>

        <li class="step-item disabled" id="step-internal">
          <div class="step-header">
            <span class="step-title">2. Check Internal</span>
            <button class="btn-step" id="btn-check-internal" disabled>Check</button>
          </div>
          <p class="step-desc">Mendeteksi bentrok jadwal antar baris di dalam file CSV ini.</p>
        </li>

        <li class="step-item disabled" id="step-db">
          <div class="step-header">
            <span class="step-title">3. Check Database</span>
            <button class="btn-step" id="btn-check-db" disabled>Check</button>
          </div>
          <p class="step-desc">Mencocokkan jadwal CSV dengan data asli di server.</p>
        </li>

      </ul>

      <div class="input-many-upload-section" style="margin-top: 1.5rem;">
        <button class="input-many-upload-btn" id="btn-upload-all" disabled style="width: 100%;">Upload Semua Data</button>
      </div>
    </div>

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

  <div class="input-many-main">

    <div class="csv-upload-container" id="upload-section">
      <div class="csv-upload-header">
        <h2 class="csv-upload-title">Upload File CSV</h2>
        <p class="csv-upload-description">Pilih file CSV untuk diunggah. Header CSV harus sesuai dengan skema database.</p>
      </div>

      <div class="csv-upload-area">
        <div class="csv-upload-zone" id="csv-upload-zone">
          <div class="csv-upload-icon" id="csv-upload-icon">📁</div>
          <div class="spinner-mini" id="csv-upload-spinner" style="display: none; width: 40px; height: 40px; border-width: 4px; margin: 0 auto 1rem auto; border-top-color: var(--md-sys-color-primary);"></div>

          <div class="csv-upload-text">
            <p class="csv-upload-main-text" id="csv-upload-main-text">Seret dan lepas file CSV di sini</p>
            <p class="csv-upload-subtext" id="csv-upload-subtext">atau</p>
          </div>
          <label for="csv-file-input" class="csv-upload-button" id="csv-upload-btn-label">Pilih File</label>
          <input type="file" id="csv-file-input" accept=".csv" class="csv-file-input">
        </div>

        <div class="csv-upload-info">
          <div class="csv-info-item" style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
            <div>
              <span class="csv-info-icon">✅</span>
              <span class="csv-info-text">Format file: CSV (.csv)</span>
            </div>
            <a href="<?= getBaseUrl('/example/contoh_agenda.csv') ?>" download="contoh_agenda.csv" style="font-size: 0.75rem; color: var(--md-sys-color-primary); text-decoration: none; font-weight: 600; padding: 4px 10px; border: 1px solid var(--md-sys-color-primary); border-radius: 6px; background: var(--md-sys-color-surface); transition: all 0.2s ease; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
              📥 Unduh Contoh
            </a>
          </div>

          <div class="csv-info-item">
            <span class="csv-info-icon">👤</span>
            <span class="csv-info-text">Pengaju agenda: <strong><?= $currentUser['name'] ?></strong></span>
          </div>
        </div>
      </div>
    </div>

    <div class="csv-preview-container" id="preview-section" style="display: none;">
      <div class="preview-header">
        <h3 class="preview-title">Preview Data Agenda (<span id="preview-count">0</span> Baris)</h3>
        <button type="button" id="btn-reset-data" style="background: transparent; border: 1px solid var(--md-sys-color-error); color: var(--md-sys-color-error); padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.85rem;">
          🗑️ Hapus Data
        </button>
      </div>

      <div class="table-responsive">
        <table class="preview-table">
          <thead>
            <tr>
              <th width="5%">No</th>
              <th width="25%">Detail Agenda</th>
              <th width="20%">Waktu Pelaksanaan</th>
              <th width="25%">Pemohon</th>
              <th width="20%">Ruangan</th>
            </tr>
          </thead>
          <tbody id="csv-preview-tbody">
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<div id="csvGlobalModal" class="css-modal">
  <div class="modal-overlay" id="csvModalOverlay"></div>
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title" id="csvModalTitle">Pemberitahuan</h3>
      <button type="button" class="modal-close" id="csvModalCloseBtn">&times;</button>
    </div>
    <div class="modal-body">
      <div class="modal-icon-wrapper" id="csvModalIcon" style="margin: 0 auto 1rem;">⚠️</div>
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

  // Oper Session Data ke JS
  const currentUser = <?= json_encode($currentUser) ?>;

  // Oper Endpoint API Mazu
  const apiCheckDbUrl = "<?= getBaseUrl('/input-many/check-database') ?>";
  const apiUploadUrl = "<?= getBaseUrl('/input-many/upload') ?>"; // <-- ENDPOINT BARU

  function runInit() {
    document.querySelectorAll('body > .autocomplete-list-wrapper').forEach(el => el.remove());
    // Tambahkan parameter ke-4
    initCsvUploader(Autocomplete, currentUser, apiCheckDbUrl, apiUploadUrl);
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