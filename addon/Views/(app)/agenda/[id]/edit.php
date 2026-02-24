<?php

/**
 * View: Halaman Edit Agenda
 * Variabel: $agenda (dari controller)
 */
?>
<div style="max-width: 800px; margin: 0 auto; padding: 2rem 1rem;">
  <div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.75rem; font-weight: 700; color: #111827; margin: 0 0 0.5rem 0;">Edit Agenda</h1>
    <p style="color: #6b7280; margin: 0;">Perbarui detail agenda Anda.</p>
  </div>

  <div style="background: white; padding: 2rem; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
    <?php
    // Asumsi ID ada di $agenda['id']
    $actionUrl = getBaseUrl("/agenda/{$agenda['id']}/update");
    $submitLabel = 'Simpan Perubahan';

    // Path ke component harus naik satu level dari folder [id]
    include dirname(__DIR__) . '/component-form-agenda.php';
    ?>
  </div>
</div>