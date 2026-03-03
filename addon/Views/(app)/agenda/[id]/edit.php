<?php $agenda = $agenda ?? [] ?>
<div class="agenda-container">
  <a href="/agenda" class="btn-back">← Batal Edit</a>

  <div class="form-card-wrapper">
    <div class="form-header">
      <h2>Edit Agenda</h2>
      <p>Lakukan perubahan pada pengajuan agenda <strong><?= htmlspecialchars($agenda['title']) ?></strong>.</p>
    </div>

    <?php
    // Set action url ke route update Mazu
    $action = getBaseUrl("/agenda/{$agenda['id']}/update");
    include dirname(__DIR__) . '/form-agenda.php';
    ?>
  </div>
</div>