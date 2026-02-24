<?php

/**
 * Component: Form Agenda (Reusable)
 * Digunakan di: create.php, edit.php
 * Variabel:
 * - $agenda (array, optional): Data agenda untuk edit
 * - $actionUrl (string): URL action form
 * - $submitLabel (string): Label tombol submit
 */

// Handle error message dari query string
$errorCode = $_GET['error'] ?? null;
$errorMessage = $_GET['message'] ?? null;
$displayError = null;

if ($errorCode && $errorMessage) {
  // Decode URL-encoded message
  $decodedMessage = urldecode($errorMessage);
  
  // Custom error messages untuk common errors
  switch ($errorCode) {
    case '500':
      if (strpos($decodedMessage, "Field 'created_at' doesn't have a default value") !== false) {
        $displayError = "Terjadi kesalahan database: Field created_at tidak memiliki nilai default. Silakan hubungi administrator.";
      } elseif (strpos($decodedMessage, "SQLSTATE") !== false) {
        $displayError = "Terjadi kesalahan database. Silakan coba lagi atau hubungi administrator.";
      } else {
        $displayError = "Terjadi kesalahan server: " . $decodedMessage;
      }
      break;
    case '400':
      $displayError = "Data yang dikirim tidak valid: " . $decodedMessage;
      break;
    case '403':
      $displayError = "Anda tidak memiliki izin untuk melakukan aksi ini.";
      break;
    default:
      $displayError = $decodedMessage;
  }
}

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
  }

  .form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: border-color 0.2s, box-shadow 0.2s;
  }

  .form-control:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
  }

  .row-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
  }

  @media (max-width: 768px) {
    .row-grid {
      grid-template-columns: 1fr;
    }
  }

  .form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
  }

  .btn-submit {
    background: #4f46e5;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
  }

  .btn-submit:hover {
    background: #4338ca;
  }

  .btn-cancel {
    background: white;
    color: #6b7280;
    border: 1px solid #d1d5db;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s;
  }

  .btn-cancel:hover {
    background: #f9fafb;
    border-color: #9ca3af;
  }

  /* Error Alert Styles */
  .error-alert {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .error-icon {
    color: #dc2626;
    flex-shrink: 0;
    margin-top: 0.125rem;
  }

  .error-content {
    flex: 1;
  }

  .error-title {
    font-weight: 600;
    color: #dc2626;
    margin: 0 0 0.25rem 0;
    font-size: 0.9rem;
  }

  .error-message {
    color: #991b1b;
    margin: 0;
    font-size: 0.85rem;
    line-height: 1.4;
  }

  .error-close {
    background: none;
    border: none;
    color: #991b1b;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 4px;
    flex-shrink: 0;
  }

  .error-close:hover {
    background: #fecaca;
  }
</style>

<!-- Error Alert -->
<?php if ($displayError): ?>
<div class="error-alert">
  <div class="error-icon">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="12" cy="12" r="10"></circle>
      <line x1="15" y1="9" x2="9" y2="15"></line>
      <line x1="9" y1="9" x2="15" y2="15"></line>
    </svg>
  </div>
  <div class="error-content">
    <div class="error-title">Terjadi Kesalahan</div>
    <div class="error-message"><?= htmlspecialchars($displayError) ?></div>
  </div>
  <button class="error-close" onclick="this.parentElement.remove()" title="Tutup">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <line x1="18" y1="6" x2="6" y2="18"></line>
      <line x1="6" y1="6" x2="18" y2="18"></line>
    </svg>
  </button>
</div>
<?php endif; ?>

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

<script>
// Auto-hide error setelah 10 detik
document.addEventListener('DOMContentLoaded', function() {
  const errorAlert = document.querySelector('.error-alert');
  if (errorAlert) {
    setTimeout(() => {
      errorAlert.style.transition = 'opacity 0.3s';
      errorAlert.style.opacity = '0';
      setTimeout(() => errorAlert.remove(), 300);
    }, 10000);
  }
});
</script>