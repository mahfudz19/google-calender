<?php

/**
 * Komponen Reusable Form Agenda
 */
$agenda = $agenda ?? [];
$ruangan = $ruangan ?? [];
$isEdit = !empty($agenda['id']);

$error = $_GET['error'] ?? null;
$message = $_GET['message'] ?? null;

if ($error && $message) {
  $decodedMessage = urldecode($message);
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
  <div class="alert alert-<?= $errorType ?> mb-4" role="alert" style="margin: 0 24px 16px 24px;">
    <div class="alert-header">
      <strong><?= $errorTitle ?></strong>
      <button type="button" class="alert-close" onclick="this.parentElement.parentElement.remove();const cleanUrl = window.location.pathname;window.history.replaceState({}, document.title, cleanUrl);"><span>&times;</span></button>
    </div>
    <div class="alert-message"><?= htmlspecialchars($decodedMessage) ?></div>
  </div>
<?php endif; ?>

<link rel="stylesheet" href="<?= getBaseUrl('/components-js/autocomplete/style.32ecab46.min.css') ?>">

<form action="<?= $action ?>" method="POST" data-spa class="mazu-form" id="agendaForm">
  <?php if ($isEdit): ?>
    <input type="hidden" name="_method" value="PUT">
  <?php endif; ?>

  <div class="gcal-form-content">

    <div class="gcal-row title-row">
      <div class="gcal-icon"></div>
      <div class="gcal-input-wrapper">
        <input type="text" id="title" name="title" class="gcal-input-title"
          value="<?= htmlspecialchars($agenda['title'] ?? '') ?>"
          placeholder="Tambahkan judul" required autofocus>
      </div>
    </div>

    <div class="gcal-row">
      <div class="gcal-icon" title="Waktu Pelaksanaan">
        <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
      </div>
      <div class="gcal-input-wrapper time-wrapper">
        <input type="datetime-local" id="start_time" name="start_time" class="gcal-input"
          value="<?= isset($agenda['start_time']) ? date('Y-m-d\TH:i', strtotime($agenda['start_time'])) : '' ?>" required>
        <span class="time-separator">–</span>
        <input type="datetime-local" id="end_time" name="end_time" class="gcal-input"
          value="<?= isset($agenda['end_time']) ? date('Y-m-d\TH:i', strtotime($agenda['end_time'])) : '' ?>" required>
      </div>
    </div>

    <div class="gcal-row">
      <div class="gcal-icon" title="Pilih Ruangan">
        <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
          <rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect>
          <rect x="9" y="9" width="6" height="6"></rect>
          <line x1="9" y1="1" x2="9" y2="4"></line>
          <line x1="15" y1="1" x2="15" y2="4"></line>
          <line x1="9" y1="20" x2="9" y2="23"></line>
          <line x1="15" y1="20" x2="15" y2="23"></line>
          <line x1="20" y1="9" x2="23" y2="9"></line>
          <line x1="20" y1="14" x2="23" y2="14"></line>
          <line x1="1" y1="9" x2="4" y2="9"></line>
          <line x1="1" y1="14" x2="4" y2="14"></line>
        </svg>
      </div>
      <div class="gcal-input-wrapper">
        <?php $dbRuanganId = $agenda['ruangan_id'] ?? $agenda['ID_ruangan'] ?? ''; ?>
        <input type="hidden" id="input_ruangan_id" name="ruangan_id" value="<?= htmlspecialchars($dbRuanganId) ?>">
        <input type="hidden" id="input_ruangan_name" name="ruangan_name" value="<?= htmlspecialchars($agenda['ruangan_name'] ?? '') ?>">
        <input type="hidden" id="input_ruangan_location" name="ruangan_location" value="<?= htmlspecialchars($agenda['ruangan_location'] ?? '') ?>">
        <input type="hidden" id="input_ruangan_capacity" name="ruangan_capacity" value="<?= htmlspecialchars($agenda['ruangan_capacity'] ?? '') ?>">

        <div class="autocomplete-container" data-placeholder="Cari ruangan kampus..." data-required="true">
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
                  <?= htmlspecialchars($item['name']) ?> (<?= htmlspecialchars($item['capacity'] ?? 0) ?> org) - <?= htmlspecialchars($item['lokasi'] ?? 'Tanpa lokasi') ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
      </div>
    </div>

    <div class="gcal-row">
      <div class="gcal-icon" title="Lokasi / Tautan Video Call">
        <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
          <circle cx="12" cy="10" r="3"></circle>
        </svg>
      </div>
      <div class="gcal-input-wrapper">
        <input type="text" id="location" name="location" class="gcal-input"
          value="<?= htmlspecialchars($agenda['location'] ?? '') ?>"
          placeholder="Tambahkan tautan rapat atau detail tempat...">
      </div>
    </div>

    <div class="gcal-row align-top">
      <div class="gcal-icon" title="Deskripsi Kegiatan">
        <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
          <line x1="21" y1="10" x2="3" y2="10"></line>
          <line x1="21" y1="6" x2="3" y2="6"></line>
          <line x1="21" y1="14" x2="3" y2="14"></line>
          <line x1="21" y1="18" x2="3" y2="18"></line>
        </svg>
      </div>
      <div class="gcal-input-wrapper">
        <textarea id="description" name="description" class="gcal-input gcal-textarea" rows="4"
          placeholder="Tambahkan deskripsi atau lampiran teks..."><?= htmlspecialchars($agenda['description'] ?? '') ?></textarea>
      </div>
    </div>

  </div>

  <div class="gcal-form-actions">
    <button type="submit" class="btn-save-gcal">
      <?= $isEdit ? 'Simpan' : 'Simpan' ?>
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

    if (select && select.value) {
      const selectedOption = select.options[select.selectedIndex];
      const customInput = container.querySelector('input[type="text"]');
      if (customInput && selectedOption && selectedOption.value !== "") {
        customInput.value = selectedOption.text;
      }
    }

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
        if (inId) inId.value = '';
        if (inName) inName.value = '';
        if (inLoc) inLoc.value = '';
        if (inCap) inCap.value = '';
      }
    };

    if (!form.dataset.autocompleteHandled) {
      select.addEventListener('change', updateHiddenFields);
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
    window.addEventListener('spa:navigated', () => setTimeout(initAgendaForm, 50));
    window.mazuFormAgendaInit = true;
  }
</script>