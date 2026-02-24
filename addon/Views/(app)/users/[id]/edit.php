<?php if ($item): ?>
  <div style="display: flex; gap: 24px; align-items: flex-start; margin-bottom: 32px;">
    <!-- Avatar -->
    <?php if (!empty($item['avatar'])): ?>
      <img src="<?= htmlspecialchars($item['avatar']) ?>" alt="Avatar" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
    <?php else: ?>
      <div style="width: 80px; height: 80px; border-radius: 50%; background-color: var(--md-sys-color-primary-container); color: var(--md-sys-color-on-primary-container); display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold;">
        <?= strtoupper(substr($item['name'] ?? 'U', 0, 1)) ?>
      </div>
    <?php endif; ?>

    <!-- Info -->
    <div style="flex: 1;">
      <h2 class="title-large"><?= htmlspecialchars($item['name'] ?? '-') ?></h2>
      <p class="body-medium text-secondary"><?= htmlspecialchars($item['email'] ?? '-') ?></p>
    </div>
  </div>

  <form
    action="/users/<?= $item['id'] ?>/update"
    method="POST"
    data-spa
    style="max-width: 500px;">
    <div style="margin-bottom: 24px;">
      <label class="label-large" style="display: block; margin-bottom: 8px;">Role</label>
      <div style="display: flex; gap: 16px;">
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
          <input type="radio" name="role" value="approver" <?= ($item['role'] ?? '') === 'approver' ? 'checked' : '' ?>>
          <span class="body-medium">Approver</span>
        </label>
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
          <input type="radio" name="role" value="admin" <?= ($item['role'] ?? '') === 'admin' ? 'checked' : '' ?>>
          <span class="body-medium">Admin</span>
        </label>
      </div>
    </div>

    <div style="margin-bottom: 32px;">
      <label class="label-large" style="display: block; margin-bottom: 8px;">Status Akun</label>
      <label class="switch">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" <?= ($item['is_active'] ?? false) ? 'checked' : '' ?>>
        <span class="slider round"></span>
      </label>
      <span class="body-small text-secondary" style="margin-left: 12px; vertical-align: middle;">
        <?= ($item['is_active'] ?? false) ? 'Aktif' : 'Non-aktif' ?>
      </span>
    </div>

    <div style="display: flex; gap: 16px;">
      <a data-spa href="/users" class="btn btn-outlined">Batal</a>
      <button type="submit" class="btn btn-filled">Simpan Perubahan</button>
    </div>
  </form>
<?php else: ?>
  <p>User tidak ditemukan.</p>
<?php endif; ?>