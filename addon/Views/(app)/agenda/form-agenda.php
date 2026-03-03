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
    <script>
      (function initAgendaForm() {
        const startInput = document.getElementById('start_time');
        const endInput = document.getElementById('end_time');
        if (!startInput || !endInput) return;

        function updateMinEndTime() {
          if (startInput.value) {
            endInput.min = startInput.value;
            if (endInput.value && endInput.value < startInput.value) {
              let startDate = new Date(startInput.value);
              startDate.setHours(startDate.getHours() + 1);
              let tzOffset = startDate.getTimezoneOffset() * 60000; // offset dalam milidetik
              let localISOTime = (new Date(startDate - tzOffset)).toISOString().slice(0, 16);
              endInput.value = localISOTime;
            }
          }
        }

        startInput.addEventListener('change', updateMinEndTime);
        updateMinEndTime();
      })();
    </script>
    <!-- Tambahkan field ruangan dengan autocomplete -->
    <div class="form-group full-width">
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
                ID - <?= htmlspecialchars($item['ID_ruangan']) ?> - <?= htmlspecialchars($item['name']) ?> (<?= htmlspecialchars($item['capacity'] ?? 0) ?> orang) - <?= htmlspecialchars($item['lokasi'] ?? 'Tidak ada lokasi') ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>
      <small class="form-hint">Pilih ruangan yang tersedia untuk agenda anda</small>
    </div>

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
<script type="module">
  import Autocomplete from '/components-js/autocomplete/index.2586e72a.min.js';

  // Function untuk initialize autocomplete
  function initializeAutocomplete() {
    const ruanganContainer = document.querySelector('.autocomplete-container');
    if (ruanganContainer) {
      // Cleanup existing instance
      const existingWrapper = ruanganContainer.querySelector('.autocomplete-wrapper');
      if (existingWrapper) {
        existingWrapper.remove();
      }

      // Restore original select
      const originalSelect = ruanganContainer.querySelector('select');
      if (originalSelect && originalSelect.style.display === 'none') {
        originalSelect.style.display = '';
      }

      // Initialize new instance
      new Autocomplete(ruanganContainer);

      // Setup form submission handler
      setupFormSubmissionHandler(ruanganContainer);
    }
  }

  // Function untuk handle form submission dengan dynamic hidden fields
  function setupFormSubmissionHandler(container) {
    const form = container.closest('form');
    const select = container.querySelector('select');

    if (form && select) {
      form.addEventListener('submit', function(e) {
        // Validasi ruangan
        const selectedOption = select.options[select.selectedIndex];

        if (!selectedOption.value) {
          e.preventDefault();
          showValidationError(container, 'Silakan pilih ruangan terlebih dahulu');
          return false;
        }

        // Tambah hidden fields secara dinamis
        addDynamicHiddenFields(form, selectedOption);

        return true;
      });
    }
  }

  // Function untuk menambah hidden fields secara dinamis
  function addDynamicHiddenFields(form, option) {
    // Hapus hidden fields lama jika ada
    const oldFields = form.querySelectorAll('[data-dynamic-field="ruangan"]');
    oldFields.forEach(field => field.remove());

    // Tambah hidden fields baru
    const fields = [{
        name: 'ruangan_id',
        value: option.value
      },
      {
        name: 'ruangan_name',
        value: option.dataset.name || ''
      },
      {
        name: 'ruangan_location',
        value: option.dataset.location || ''
      },
      {
        name: 'ruangan_capacity',
        value: option.dataset.capacity || ''
      }
    ];

    fields.forEach(field => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = field.name;
      input.value = field.value;
      input.setAttribute('data-dynamic-field', 'ruangan');
      form.appendChild(input);
    });
  }

  // Function untuk show validation error
  function showValidationError(container, message) {
    // Remove existing error
    const existingError = container.querySelector('.autocomplete-error');
    if (existingError) {
      existingError.remove();
    }

    // Create error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'autocomplete-error';
    errorDiv.textContent = message;
    errorDiv.style.cssText = `
      color: var(--md-sys-color-error);
      font-size: 0.8rem;
      margin-top: 4px;
      display: block;
    `;

    // Insert after container
    container.parentNode.insertBefore(errorDiv, container.nextSibling);

    // Auto remove setelah 3 detik
    setTimeout(() => {
      if (errorDiv.parentNode) {
        errorDiv.remove();
      }
    }, 3000);
  }

  // Time validation (tetap dipertahankan)
  document.addEventListener('DOMContentLoaded', function() {
    const startTime = document.getElementById('start_time');
    const endTime = document.getElementById('end_time');

    if (startTime && endTime) {
      startTime.addEventListener('change', function() {
        const startDate = new Date(this.value);
        startDate.setMinutes(startDate.getMinutes() + 15);
        endTime.min = startDate.toISOString().slice(0, 16);
      });

      endTime.addEventListener('change', function() {
        const start = new Date(startTime.value);
        const end = new Date(this.value);

        if (end <= start) {
          this.setCustomValidity('End time must be after start time');
          this.reportValidity();
        } else {
          this.setCustomValidity('');
        }
      });

      document.querySelector('form').addEventListener('submit', function(e) {
        const start = new Date(startTime.value);
        const end = new Date(endTime.value);

        if (end <= start) {
          e.preventDefault();
          endTime.setCustomValidity('End time must be after start time');
          endTime.reportValidity();
        } else {
          endTime.setCustomValidity('');
        }
      });

      if (startTime.value) {
        startTime.dispatchEvent(new Event('change'));
      }
    }
  });

  // Initialize autocomplete
  document.addEventListener('DOMContentLoaded', initializeAutocomplete);
  document.addEventListener('spa:navigated', initializeAutocomplete);

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAutocomplete);
  } else {
    setTimeout(initializeAutocomplete, 1);
  }
</script>