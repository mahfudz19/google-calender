  <div class="users-container" style="max-width: 600px; margin: 0 auto;">

    <div class="page-header" style="margin-bottom: 1.5rem;">
      <a data-spa href="<?= getBaseUrl('/users') ?>" class="btn-back">← Batal Edit</a>
    </div>

    <?php if ($item): ?>
      <div class="profile-card">
        <div class="profile-header-sm">
          <div class="profile-avatar-sm">
            <?php if (!empty($item['avatar'])): ?>
              <img src="<?= htmlspecialchars($item['avatar']) ?>" alt="Avatar">
            <?php else: ?>
              <div class="avatar-placeholder"><?= strtoupper(substr($item['name'] ?? 'U', 0, 1)) ?></div>
            <?php endif; ?>
          </div>
          <div class="profile-title-group">
            <h2 class="profile-name" style="font-size: 1.2rem;"><?= htmlspecialchars($item['name'] ?? 'Tanpa Nama') ?></h2>
            <p class="profile-email"><?= htmlspecialchars($item['email'] ?? '-') ?></p>
          </div>
        </div>

        <form action="<?= getBaseUrl('/users/' . $item['id'] . '/update') ?>" method="POST" data-spa class="mazu-edit-form">

          <div class="form-section">
            <label class="form-label">Tentukan Hak Akses <span class="text-danger">*</span></label>
            <div class="role-cards-wrapper">
              <label class="role-card <?= ($item['role'] ?? '') === 'approver' ? 'selected' : '' ?>">
                <input type="radio" name="role" value="approver" <?= ($item['role'] ?? '') === 'approver' ? 'checked' : '' ?> onchange="updateRoleSelection(this)">
                <div class="role-icon bg-orange">✓</div>
                <div class="role-info">
                  <strong>Approver</strong>
                  <span>Dapat menyetujui atau menolak pengajuan agenda.</span>
                </div>
              </label>

              <label class="role-card <?= ($item['role'] ?? '') === 'admin' ? 'selected' : '' ?>">
                <input type="radio" name="role" value="admin" <?= ($item['role'] ?? '') === 'admin' ? 'checked' : '' ?> onchange="updateRoleSelection(this)">
                <div class="role-icon bg-primary">★</div>
                <div class="role-info">
                  <strong>Super Admin</strong>
                  <span>Akses penuh ke sistem dan manajemen user.</span>
                </div>
              </label>
            </div>
          </div>

          <div class="form-section" style="border-top: 1px solid var(--md-sys-color-outline-variant); padding-top: 1.5rem;">
            <div class="toggle-wrapper">
              <div class="toggle-info">
                <label class="form-label" style="margin: 0;">Status Akun</label>
                <span class="text-muted" style="font-size: 0.85rem; display: block;">Izinkan user login ke Mazu Calendar</span>
              </div>

              <label class="mazu-switch">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" <?= ($item['is_active'] ?? false) ? 'checked' : '' ?>>
                <span class="slider round"></span>
              </label>
            </div>
          </div>

          <div class="form-footer">
            <button type="submit" class="btn-confirm success" style="width: 100%;">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    <?php else: ?>
      <div class="table-card" style="padding: 4rem; text-align: center;">
        <p class="text-muted">Pengguna tidak ditemukan.</p>
      </div>
    <?php endif; ?>
  </div>

  <script>
    // Fungsi kecil untuk mengubah class 'selected' pada Role Card saat diklik
    function updateRoleSelection(radioElement) {
      document.querySelectorAll('.role-card').forEach(card => card.classList.remove('selected'));
      radioElement.closest('.role-card').classList.add('selected');
    }
  </script>