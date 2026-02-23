<?php

/**
 * Component: Form Agenda (Reusable)
 * Digunakan di: create.php, edit.php
 * Variabel:
 * - $agenda (array, optional): Data agenda untuk edit
 * - $actionUrl (string): URL action form
 * - $submitLabel (string): Label tombol submit
 */

$agenda = $agenda ?? [];
$title = $agenda['title'] ?? '';
$description = $agenda['description'] ?? '';
// Format datetime-local requires Y-m-d\TH:i
$start_time = isset($agenda['start_time']) ? date('Y-m-d\TH:i', strtotime($agenda['start_time'])) : '';
$end_time = isset($agenda['end_time']) ? date('Y-m-d\TH:i', strtotime($agenda['end_time'])) : '';
$location = $agenda['location'] ?? '';
?>

<style>
  .form-group {
    margin-bottom: 1.5rem;
    width: 100%;
  }

  .form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.95rem;
  }

  .form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.2s, box-shadow 0.2s;
    font-family: inherit;
  }

  .form-control:focus {
    border-color: #4f46e5;
    outline: none;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
  }

  .form-actions {
    margin-top: 2.5rem;
    display: flex;
    gap: 1rem;
    align-items: center;
  }

  .btn-submit {
    background: #4f46e5;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    font-size: 1rem;
    transition: background 0.2s;
  }

  .btn-submit:hover {
    background: #4338ca;
  }

  .btn-cancel {
    background: white;
    color: #4b5563;
    border: 1px solid #d1d5db;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    font-size: 1rem;
    transition: background 0.2s;
  }

  .btn-cancel:hover {
    background: #f3f4f6;
    color: #111827;
  }

  .row-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }

  @media(min-width: 640px) {
    .row-grid {
      grid-template-columns: 1fr 1fr;
    }
  }

  .helper-text {
    font-size: 0.85rem;
    color: #6b7280;
    margin-top: 0.25rem;
  }
</style>

<form action="<?= $actionUrl ?>" method="POST" data-spa data-spa-method="POST">
  <div class="form-group">
    <label class="form-label">Judul Agenda <span style="color:red">*</span></label>
    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($title) ?>" required placeholder="Contoh: Rapat Koordinasi Q3" autofocus>
  </div>

  <div class="row-grid">
    <div class="form-group">
      <label class="form-label">Waktu Mulai <span style="color:red">*</span></label>
      <input type="datetime-local" name="start_time" class="form-control" value="<?= $start_time ?>" required>
    </div>
    <div class="form-group">
      <label class="form-label">Waktu Selesai <span style="color:red">*</span></label>
      <input type="datetime-local" name="end_time" class="form-control" value="<?= $end_time ?>" required>
    </div>
  </div>

  <div class="form-group">
    <label class="form-label">Lokasi</label>
    <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($location) ?>" placeholder="Contoh: Ruang Meeting 1 / Online (Zoom)">
  </div>

  <div class="form-group">
    <label class="form-label">Deskripsi / Catatan</label>
    <textarea name="description" class="form-control" rows="5" placeholder="Tambahkan detail agenda, tautan dokumen, atau catatan penting lainnya..."><?= htmlspecialchars($description) ?></textarea>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn-submit">
      <?= $submitLabel ?? 'Simpan Agenda' ?>
    </button>
    <a href="<?= getBaseUrl('/agenda') ?>" class="btn-cancel">Batal</a>
  </div>
</form>