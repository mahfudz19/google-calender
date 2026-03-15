<div class="agenda-container">
  <div class="gcal-form-wrapper">
    <div class="gcal-form-header">
      <div class="header-left">
        <a data-spa href="<?= getBaseUrl('/agenda') ?>" class="btn-close-icon" title="Batal">
          <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </a>
        <span class="gcal-header-title">Buat Agenda Baru</span>
      </div>
      <div class="header-right">
      </div>
    </div>

    <div class="gcal-form-body">
      <?php
      $action = getBaseUrl('/agenda/store');
      include __DIR__ . '/form-agenda.php';
      ?>
    </div>
  </div>
</div>