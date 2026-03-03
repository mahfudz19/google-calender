<?php

/**
 * Komponen Reusable Form Agenda
 * @var array $agenda Data agenda jika mode Edit (opsional)
 * @var string $action URL target form disubmit
 * @var array $ruangan Data ruangan dari API
 */
$agenda = $agenda ?? [];
$ruangan = $ruangan ?? [];
$isEdit = !empty($agenda['id']);

$error = $_GET['error'] ?? null;
$message = $_GET['message'] ?? null;

if ($error && $message) {
  $decodedMessage = urldecode($message);

  // Determine error type and styling
  $errorType = match ($error) {
    '500' => 'error',
    '400' => 'warning',
    '403' => 'warning',
    '404' => 'info',
    default => 'error'
  };

  $errorTitle = match ($error) {
    '500' => 'Kesalahan Server',
    '400' => 'Kesalahan Permintaan',
    '403' => 'Akses Ditolak',
    '404' => 'Data Tidak Ditemukan',
    default => 'Terjadi Kesalahan'
  };
}
?>

<?php if ($error && $message): ?>
  <div class="alert alert-<?= $errorType ?> mb-4" role="alert">
    <div class="alert-header">
      <strong><?= $errorTitle ?></strong>
      <button type="button" class="alert-close" onclick="this.parentElement.parentElement.remove();const cleanUrl = window.location.pathname;window.history.replaceState({}, document.title, cleanUrl);">
        <span>&times;</span>
      </button>
    </div>
    <div class="alert-message">
      <?= htmlspecialchars($decodedMessage) ?>
    </div>
  </div>

  <style>
    .alert {
      padding: 1rem;
      border-radius: 0.375rem;
      margin-bottom: 1rem;
      border: 1px solid;
      position: relative;
    }

    .alert-error {
      background-color: #fef2f2;
      border-color: #fecaca;
      color: #dc2626;
    }

    .alert-warning {
      background-color: #fffbeb;
      border-color: #fed7aa;
      color: #d97706;
    }

    .alert-info {
      background-color: #eff6ff;
      border-color: #bfdbfe;
      color: #2563eb;
    }

    .alert-header {
      display: flex;
      justify-content: between;
      align-items: center;
      margin-bottom: 0.5rem;
    }

    .alert-close {
      background: none;
      border: none;
      font-size: 1.25rem;
      cursor: pointer;
      opacity: 0.7;
      margin-left: auto;
    }

    .alert-close:hover {
      opacity: 1;
    }

    .alert-message {
      font-size: 0.875rem;
      line-height: 1.25rem;
    }
  </style>
<?php endif; ?>

<!-- Include autocomplete component CSS -->
<link rel="stylesheet" href="/components-js/autocomplete/style.32ecab46.min.css">

<form action="<?= $action ?>" method="POST" data-spa class="mazu-form">

  <?php if ($isEdit): ?>
    <input type="hidden" name="_method" value="PUT">
  <?php endif; ?>

  <div class="form-grid">
    <div class="form-group full-width">
      <label for="title">Judul Agenda <span class="text-red">*</span></label>
      <input type="text" id="title" name="title" class="form-control"
        value="<?= htmlspecialchars($agenda['title'] ?? '') ?>"
        placeholder="Contoh: Rapat Koordinasi Akademik" required>
    </div>

    <div class="form-group">
      <label for="start_time">Waktu Mulai <span class="text-red">*</span></label>
      <input type="datetime-local" id="start_time" name="start_time" class="form-control"
        value="<?= isset($agenda['start_time']) ? date('Y-m-d\TH:i', strtotime($agenda['start_time'])) : '' ?>" required>
    </div>

    <div class="form-group">
      <label for="end_time">Waktu Selesai <span class="text-red">*</span></label>
      <input type="datetime-local" id="end_time" name="end_time" class="form-control"
        value="<?= isset($agenda['end_time']) ? date('Y-m-d\TH:i', strtotime($agenda['end_time'])) : '' ?>" required>
    </div>

    <!-- Tambahkan field ruangan dengan autocomplete -->
    <!-- <div class="form-group full-width">
      <label for="ruangan_id">Ruangan <span class="text-red">*</span></label>

      <div class="autocomplete-container" data-placeholder="Pilih ruangan..." data-required="true">
        <select id="ruangan_select" name="ruangan_select" class="form-select">
          <option value="">Pilih ruangan...</option>
          <?php if (!empty($ruangan['data'])): ?>
            <?php foreach ($ruangan['data'] as $item): ?>
              <option value="<?= htmlspecialchars($item['ID_ruangan']) ?>"
                data-name="<?= htmlspecialchars($item['name']) ?>"
                data-location="<?= htmlspecialchars($item['lokasi'] ?? '') ?>"
                data-capacity="<?= htmlspecialchars($item['capacity'] ?? '') ?>"
                <?= ($agenda['ruangan_id'] ?? '') == $item['ID_ruangan'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($item['name']) ?> (<?= htmlspecialchars($item['capacity'] ?? 0) ?> orang) - <?= htmlspecialchars($item['lokasi'] ?? 'Tidak ada lokasi') ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>

      <input type="hidden" name="ruangan_id" id="ruangan_id" value="<?= htmlspecialchars($agenda['ruangan_id'] ?? '') ?>" required>
      <input type="hidden" name="ruangan_name" id="ruangan_name" value="<?= htmlspecialchars($agenda['ruangan_name'] ?? '') ?>">
      <input type="hidden" name="ruangan_location" id="ruangan_location" value="<?= htmlspecialchars($agenda['ruangan_location'] ?? '') ?>">
      <input type="hidden" name="ruangan_capacity" id="ruangan_capacity" value="<?= htmlspecialchars($agenda['ruangan_capacity'] ?? '') ?>">

      <small class="form-hint">Pilih ruangan yang tersedia untuk agenda anda</small>
    </div> -->

    <div class="form-group full-width">
      <label for="location">Lokasi Tambahan / Tautan Rapat</label>
      <input type="text" id="location" name="location" class="form-control"
        value="<?= htmlspecialchars($agenda['location'] ?? '') ?>"
        placeholder="Contoh: Link Google Meet atau detail lokasi tambahan">
    </div>

    <div class="form-group full-width">
      <label for="description">Deskripsi Tambahan</label>
      <textarea id="description" name="description" class="form-control" rows="4"
        placeholder="Catatan tambahan atau deskripsi kegiatan..."><?= htmlspecialchars($agenda['description'] ?? '') ?></textarea>
    </div>
  </div>

  <div class="form-actions">
    <a data-spa href="/agenda" class="btn-cancel">Batal</a>
    <button type="submit" class="btn-submit">
      <?= $isEdit ? 'Simpan Perubahan' : 'Ajukan Agenda' ?>
    </button>
  </div>
</form>

<!-- Include autocomplete component JS -->
<!-- <script type="module">
  import Autocomplete from '/components-js/autocomplete/index.2586e72a.min.js';

  // Configuration
  const CONFIG = {
    autocomplete: {
      container: '.autocomplete-container',
      wrapper: '.autocomplete-wrapper',
      select: 'select',
      input: '.autocomplete-input',
      error: '.autocomplete-error'
    },
    form: {
      selector: 'form',
      submit: '.btn-submit'
    },
    fields: {
      ruanganId: 'ruangan_id',
      startTime: 'start_time',
      endTime: 'end_time'
    },
    messages: {
      ruanganRequired: 'Silakan pilih ruangan terlebih dahulu'
    },
    timing: {
      minDuration: 15, // minutes
      debounceMs: 300
    }
  };

  // State management
  const state = {
    initialized: false,
    autocompleteInstance: null
  };

  // Utility functions
  const utils = {
    getById: (id) => document.getElementById(id),
    createElement: (tag, className, textContent = '') => {
      const el = document.createElement(tag);
      if (className) el.className = className;
      if (textContent) el.textContent = textContent;
      return el;
    },
    debounce: (func, wait) => {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    }
  };

  // Autocomplete management
  const autocompleteManager = {
    init() {
      const container = document.querySelector(CONFIG.autocomplete.container);
      if (!container || state.initialized) return;

      this.cleanup(container);
      this.createInstance(container);
      this.setupListeners(container);
      state.initialized = true;
    },

    cleanup(container) {
      const wrapper = container.querySelector(CONFIG.autocomplete.wrapper);
      if (wrapper) wrapper.remove();

      const select = container.querySelector(CONFIG.autocomplete.select);
      if (select?.style.display === 'none') {
        select.style.display = '';
      }
    },

    createInstance(container) {
      state.autocompleteInstance = new Autocomplete(container);
    },

    setupListeners(container) {
      const select = container.querySelector(CONFIG.autocomplete.select);
      if (!select) return;

      const newSelect = select.cloneNode(true);
      select.parentNode.replaceChild(newSelect, select);

      newSelect.addEventListener('change', (e) => {
        this.updateHiddenFields(e.target);
      });

      // Initialize edit mode
      if (newSelect.value) {
        this.updateHiddenFields(newSelect);
      }
    },

    updateHiddenFields(select) {
      const option = select.options[select.selectedIndex];
      const fields = {
        id: utils.getById(CONFIG.fields.ruanganId),
        name: utils.getById('ruangan_name'),
        location: utils.getById('ruangan_location'),
        capacity: utils.getById('ruangan_capacity')
      };

      if (option.value) {
        fields.id.value = option.value;
        fields.name.value = option.dataset.name || '';
        fields.location.value = option.dataset.location || '';
        fields.capacity.value = option.dataset.capacity || '';
      } else {
        Object.values(fields).forEach(field => field.value = '');
      }
    }
  };

  // Form validation
  const formValidator = {
    init() {
      const form = document.querySelector(CONFIG.form.selector);
      if (!form) return;

      form.addEventListener('submit', this.handleSubmit.bind(this));
    },

    handleSubmit(e) {
      const ruanganId = utils.getById(CONFIG.fields.ruanganId)?.value;

      if (!ruanganId) {
        e.preventDefault();
        this.showError(CONFIG.messages.ruanganRequired);
        return false;
      }

      this.clearErrors();
      return true;
    },

    showError(message) {
      this.clearErrors();

      const container = document.querySelector(CONFIG.autocomplete.container);
      const error = utils.createElement('div', 'autocomplete-error', message);
      error.style.cssText = `
        color: var(--md-sys-color-error);
        font-size: 0.8rem;
        margin-top: 4px;
        display: block;
      `;

      container.parentNode.insertBefore(error, container.nextSibling);
      setTimeout(() => error.remove?.(), 3000);
    },

    clearErrors() {
      const existing = document.querySelector(CONFIG.autocomplete.error);
      existing?.remove();
    }
  };

  // Time validation
  const timeValidator = {
    init() {
      const startTime = utils.getById(CONFIG.fields.startTime);
      const endTime = utils.getById(CONFIG.fields.endTime);

      if (!startTime || !endTime) return;

      // Debounced validation for better performance
      const validateEnd = utils.debounce(() => {
        this.validateEndTime(startTime, endTime);
      }, CONFIG.timing.debounceMs);

      startTime.addEventListener('change', () => {
        this.updateEndTimeMin(startTime, endTime);
        validateEnd();
      });

      endTime.addEventListener('change', validateEnd);

      // Initialize edit mode
      if (startTime.value) {
        this.updateEndTimeMin(startTime, endTime);
      }
    },

    updateEndTimeMin(startTime, endTime) {
      const startDate = new Date(startTime.value);
      startDate.setMinutes(startDate.getMinutes() + CONFIG.timing.minDuration);
      endTime.min = startDate.toISOString().slice(0, 16);
    },

    validateEndTime(startTime, endTime) {
      const start = new Date(startTime.value);
      const end = new Date(endTime.value);

      if (end <= start) {
        endTime.setCustomValidity('End time must be after start time');
        endTime.reportValidity();
      } else {
        endTime.setCustomValidity('');
      }
    }
  };

  // Main application
  const app = {
    init() {
      autocompleteManager.init();
      formValidator.init();
      timeValidator.init();
    },

    reinit() {
      state.initialized = false;
      this.init();
    }
  };

  // Event listeners
  document.addEventListener('DOMContentLoaded', app.init);
  document.addEventListener('spa:navigated', app.reinit);

  // Immediate initialization for non-SPA navigation
  if (document.readyState !== 'loading') {
    setTimeout(app.init, 1);
  }
</script> -->