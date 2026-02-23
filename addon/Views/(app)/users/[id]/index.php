<?php if ($item): ?>
  <div style="display: flex; gap: 24px; align-items: flex-start;">
    <!-- Avatar -->
    <?php if (!empty($item['avatar'])): ?>
      <img src="<?= htmlspecialchars($item['avatar']) ?>" alt="Avatar" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
    <?php else: ?>
      <div style="width: 100px; height: 100px; border-radius: 50%; background-color: var(--md-sys-color-primary-container); color: var(--md-sys-color-on-primary-container); display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: bold;">
        <?= strtoupper(substr($item['name'] ?? 'U', 0, 1)) ?>
      </div>
    <?php endif; ?>

    <!-- Info -->
    <div style="flex: 1;">
      <h2 class="title-large"><?= htmlspecialchars($item['name'] ?? '-') ?></h2>
      <p class="body-medium text-secondary" style="margin-bottom: 24px;"><?= htmlspecialchars($item['email'] ?? '-') ?></p>

      <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;">
        <div>
          <label class="label-small text-secondary">Role</label>
          <p class="body-large"><?= ucfirst($item['role'] ?? '-') ?></p>
        </div>
        <div>
          <label class="label-small text-secondary">Status</label>
          <p class="body-large"><?= ($item['is_active'] ?? false) ? 'Active' : 'Inactive' ?></p>
        </div>
        <div>
          <label class="label-small text-secondary">Last Login</label>
          <p class="body-large"><?= $item['last_login_at'] ? date('d M Y H:i', strtotime($item['last_login_at'])) : '-' ?></p>
        </div>
        <div>
          <label class="label-small text-secondary">Created At</label>
          <p class="body-large"><?= $item['created_at'] ? date('d M Y H:i', strtotime($item['created_at'])) : '-' ?></p>
        </div>
      </div>
    </div>
  </div>
<?php else: ?>
  <p>User tidak ditemukan.</p>
<?php endif; ?>