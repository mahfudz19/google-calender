<div class="mazu-container">
  <div class="header-actions">
    <div style="display: flex; align-items: center; gap: 16px;">
      <a data-spa href="/users" class="btn-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" y1="12" x2="5" y2="12"></line>
          <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
      </a>
      <h1 class="display-small"><?= $meta->title ?></h1>
    </div>
  </div>

  <div class="mazu-card" style="padding: 24px;" data-layout="(app)/users/[id]/layout.php">
    <?= $children; ?>
  </div>
</div>