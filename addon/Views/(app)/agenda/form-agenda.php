<?php

/**
 * Komponen Reusable Form Agenda
 * @var array $agenda Data agenda jika mode Edit (opsional)
 * @var string $action URL target form disubmit
 */
$agenda = $agenda ?? [];
$isEdit = !empty($agenda['id']);
?>

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

    <div class="form-group full-width">
      <label for="location">Lokasi / Tautan Rapat <span class="text-red">*</span></label>
      <input type="text" id="location" name="location" class="form-control"
        value="<?= htmlspecialchars($agenda['location'] ?? '') ?>"
        placeholder="Contoh: Ruang Rapat Lt. 2 atau Link Google Meet" required>
    </div>

    <div class="form-group full-width">
      <label for="description">Deskripsi Tambahan</label>
      <textarea id="description" name="description" class="form-control" rows="4"
        placeholder="Catatan tambahan atau deskripsi kegiatan..."><?= htmlspecialchars($agenda['description'] ?? '') ?></textarea>
    </div>
  </div>

  <div class="form-actions">
    <a href="/agenda" class="btn-cancel">Batal</a>
    <button type="submit" class="btn-submit">
      <?= $isEdit ? 'Simpan Perubahan' : 'Ajukan Agenda' ?>
    </button>
  </div>
</form>