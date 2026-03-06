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
      justify-content: space-between;
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

<link rel="stylesheet" href="<?= getBaseUrl('/components-js/autocomplete/style.32ecab46.min.css') ?>">

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
              let tzOffset = startDate.getTimezoneOffset() * 60000;
              let localISOTime = (new Date(startDate - tzOffset)).toISOString().slice(0, 16);
              endInput.value = localISOTime;
            }
          }
        }

        startInput.addEventListener('change', updateMinEndTime);
        updateMinEndTime();
      })();
    </script>

    <div class="form-group full-width">
      <label for="ruangan_select">Ruangan <span class="text-red">*</span></label>

      <?php $dbRuanganId = $agenda['ruangan_id'] ?? $agenda['ID_ruangan'] ?? ''; ?>
      <input type="hidden" id="input_ruangan_id" name="ruangan_id" value="<?= htmlspecialchars($dbRuanganId) ?>">
      <input type="hidden" id="input_ruangan_name" name="ruangan_name" value="<?= htmlspecialchars($agenda['ruangan_name'] ?? '') ?>">
      <input type="hidden" id="input_ruangan_location" name="ruangan_location" value="<?= htmlspecialchars($agenda['ruangan_location'] ?? '') ?>">
      <input type="hidden" id="input_ruangan_capacity" name="ruangan_capacity" value="<?= htmlspecialchars($agenda['ruangan_capacity'] ?? '') ?>">

      <div class="autocomplete-container" data-placeholder="Pilih ruangan..." data-required="true">
        <select id="ruangan_select" class="form-select">
          <option value="">Pilih ruangan...</option>
          <?php if (!empty($ruangan['data'])): ?>
            <?php foreach ($ruangan['data'] as $item): ?>
              <?php $isSelected = (strval($dbRuanganId) === strval($item['ID_ruangan'])) ? 'selected' : ''; ?>
              <option value="<?= htmlspecialchars($item['ID_ruangan']) ?>"
                data-name="<?= htmlspecialchars($item['name']) ?>"
                data-location="<?= htmlspecialchars($item['lokasi'] ?? '') ?>"
                data-capacity="<?= htmlspecialchars($item['capacity'] ?? '') ?>"
                <?= $isSelected ?>>
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
    <a data-spa href="<?= getBaseUrl('/agenda') ?>" class="btn-cancel">Batal</a>
    <button type="submit" class="btn-submit">
      <?= $isEdit ? 'Simpan Perubahan' : 'Ajukan Agenda' ?>
    </button>
  </div>
</form>

<script type="module">
  import Autocomplete from "<?= getBaseUrl('/components-js/autocomplete/index.2586e72a.min.js') ?>";

  function initAgendaForm() {
    document.querySelectorAll('body > .autocomplete-list-wrapper').forEach(el => el.remove());
    const form = document.querySelector('form.mazu-form');
    if (!form) return;
    initAutocomplete(form);
    initTimeValidation(form);
  }

  function initAutocomplete(form) {
    const container = document.querySelector('.autocomplete-container');
    if (!container) return;
    const existingWrapper = container.querySelector('.autocomplete-wrapper');
    if (existingWrapper) existingWrapper.remove();

    const select = container.querySelector('select');
    if (select) select.style.display = '';

    new Autocomplete(container);

    // Perbarui teks input custom dari Autocomplete jika ada default value
    if (select && select.value) {
      const selectedOption = select.options[select.selectedIndex];
      const customInput = container.querySelector('input[type="text"]');
      if (customInput && selectedOption && selectedOption.value !== "") {
        customInput.value = selectedOption.text;
      }
    }

    // Fungsi untuk memperbarui DOM elemen Static Hidden Input
    const updateHiddenFields = () => {
      if (!select) return;
      const selected = select.options[select.selectedIndex];

      const inId = document.getElementById('input_ruangan_id');
      const inName = document.getElementById('input_ruangan_name');
      const inLoc = document.getElementById('input_ruangan_location');
      const inCap = document.getElementById('input_ruangan_capacity');

      if (selected && selected.value) {
        if (inId) inId.value = selected.value;
        if (inName) inName.value = selected.dataset.name || '';
        if (inLoc) inLoc.value = selected.dataset.location || '';
        if (inCap) inCap.value = selected.dataset.capacity || '';
      } else {
        // Reset jika user memilih ulang opsi kosong ("Pilih ruangan...")
        if (inId) inId.value = '';
        if (inName) inName.value = '';
        if (inLoc) inLoc.value = '';
        if (inCap) inCap.value = '';
      }
    };

    if (!form.dataset.autocompleteHandled) {
      // Dengarkan perubahan pada select
      select.addEventListener('change', updateHiddenFields);

      // Validasi pencegahan submit berdasarkan static hidden input
      form.addEventListener('submit', (e) => {
        const inId = document.getElementById('input_ruangan_id');
        if (!inId || !inId.value) {
          e.preventDefault();
          showError(container, 'Silakan pilih ruangan terlebih dahulu');
        }
      });

      form.dataset.autocompleteHandled = 'true';
    }
  }

  function showError(container, message) {
    container.parentNode.querySelector('.autocomplete-error')?.remove();
    const err = document.createElement('div');
    err.className = 'autocomplete-error';
    err.textContent = message;
    err.style.cssText = 'color: var(--md-sys-color-error); font-size: 0.8rem; margin-top: 4px; display: block;';

    container.parentNode.insertBefore(err, container.nextSibling);
    setTimeout(() => err?.remove(), 3000);
  }

  function initTimeValidation(form) {
    const startInput = document.getElementById('start_time');
    const endInput = document.getElementById('end_time');
    if (!startInput || !endInput) return;

    const updateMinTime = () => {
      if (!startInput.value) return;
      endInput.min = startInput.value;
      if (endInput.value && endInput.value < startInput.value) {
        const startDate = new Date(startInput.value);
        startDate.setHours(startDate.getHours() + 1);
        const tzOffset = startDate.getTimezoneOffset() * 60000;
        endInput.value = (new Date(startDate - tzOffset)).toISOString().slice(0, 16);
      }
    };

    const validateTimeSubmit = (e) => {
      if (endInput.value <= startInput.value) {
        if (e && e.preventDefault) e.preventDefault();
        endInput.setCustomValidity('Waktu selesai harus setelah waktu mulai');
        endInput.reportValidity();
      } else {
        endInput.setCustomValidity('');
      }
    };

    startInput.removeEventListener('change', updateMinTime);
    startInput.addEventListener('change', updateMinTime);
    endInput.removeEventListener('change', validateTimeSubmit);
    endInput.addEventListener('change', validateTimeSubmit);

    if (!form.dataset.timeHandled) {
      form.addEventListener('submit', validateTimeSubmit);
      form.dataset.timeHandled = 'true';
    }
  }

  setTimeout(initAgendaForm, 50);
  if (!window.mazuFormAgendaInit) {
    window.addEventListener('spa:before-navigate', () => {
      document.querySelectorAll('body > .autocomplete-list-wrapper').forEach(el => el.remove());
    });
    window.addEventListener('spa:navigated', () => {
      setTimeout(initAgendaForm, 50);
    });
    window.mazuFormAgendaInit = true;
  }
</script>