<?php $item = $item ?? null; ?>
<div class="upf-container narrow">

  <div class="upf-page-header">
    <a data-spa href="<?= getBaseUrl('/users') ?>" class="upf-btn-back">
      <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
      Batal Edit
    </a>
  </div>

  <?php if ($item): ?>
    <div class="upf-card">

      <div class="upf-header-sm">
        <div class="upf-avatar-wrapper">
          <?php if (!empty($item['avatar'])): ?>
            <img src="<?= htmlspecialchars($item['avatar']) ?>" alt="Avatar" class="upf-avatar-sm">
          <?php else: ?>
            <div class="upf-avatar-sm fallback"><?= strtoupper(substr($item['name'] ?? 'U', 0, 1)) ?></div>
          <?php endif; ?>
        </div>
        <div class="upf-title-group">
          <h2 class="upf-name-sm"><?= htmlspecialchars($item['name'] ?? 'Tanpa Nama') ?></h2>
          <p class="upf-email-sm"><?= htmlspecialchars($item['email'] ?? '-') ?></p>
        </div>
      </div>

      <form action="<?= getBaseUrl('/users/' . $item['id'] . '/update') ?>" method="POST" data-spa class="upf-form">

        <div class="upf-form-section">
          <label class="upf-form-label">Peran Sistem <span style="color: var(--error-main);">*</span></label>
          <div class="upf-role-grid">

            <label class="upf-role-card <?= ($item['role'] ?? '') === 'approver' ? 'selected' : '' ?>">
              <input type="radio" name="role" value="approver" <?= ($item['role'] ?? '') === 'approver' ? 'checked' : '' ?> onchange="updateRoleSelection(this)">
              <div class="upf-role-icon" style="color: var(--warning-dark); background: var(--warning-bg);">
                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                  <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                  <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
              </div>
              <div class="upf-role-info">
                <strong>Approver</strong>
                <span>Penyetuju agenda.</span>
              </div>
            </label>

            <label class="upf-role-card <?= ($item['role'] ?? '') === 'admin' ? 'selected' : '' ?>">
              <input type="radio" name="role" value="admin" <?= ($item['role'] ?? '') === 'admin' ? 'checked' : '' ?> onchange="updateRoleSelection(this)">
              <div class="upf-role-icon" style="color: var(--primary-dark); background: var(--primary-bg);">
                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                  <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
              </div>
              <div class="upf-role-info">
                <strong>Super Admin</strong>
                <span>Akses penuh sistem.</span>
              </div>
            </label>

          </div>
        </div>

        <div class="upf-form-section divider">
          <div class="upf-toggle-row">
            <div class="upf-toggle-info">
              <label class="upf-form-label" style="margin: 0;">Izin Akses Mazu</label>
              <span class="upf-form-desc">Izinkan pengguna ini untuk masuk ke dalam aplikasi.</span>
            </div>

            <label class="upf-switch">
              <input type="hidden" name="is_active" value="0">
              <input type="checkbox" name="is_active" value="1" <?= ($item['is_active'] ?? false) ? 'checked' : '' ?>>
              <span class="upf-slider"></span>
            </label>
          </div>
        </div>

        <div class="upf-form-footer">
          <button type="submit" class="upf-btn-primary full">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  <?php else: ?>
    <div class="upf-empty-state">
      <div class="upf-empty-icon">🔍</div>
      <p>Pengguna tidak ditemukan.</p>
    </div>
  <?php endif; ?>

</div>

<script>
  function updateRoleSelection(radioElement) {
    document.querySelectorAll('.upf-role-card').forEach(card => card.classList.remove('selected'));
    radioElement.closest('.upf-role-card').classList.add('selected');
  }
</script>