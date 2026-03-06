<div class="users-container" style="max-width: 800px; margin: 0 auto;">

  <div class="page-header" style="margin-bottom: 1.5rem;">
    <a data-spa href="<?= getBaseUrl('/users') ?>" class="btn-back">← Kembali ke Daftar User</a>
  </div>

  <?php if ($item): ?>
    <div class="profile-card">
      <div class="profile-header">
        <div class="profile-avatar-lg">
          <?php if (!empty($item['avatar'])): ?>
            <img src="<?= htmlspecialchars($item['avatar']) ?>" alt="Avatar">
          <?php else: ?>
            <div class="avatar-placeholder"><?= strtoupper(substr($item['name'] ?? 'U', 0, 1)) ?></div>
          <?php endif; ?>
        </div>
        <div class="profile-title-group">
          <h2 class="profile-name"><?= htmlspecialchars($item['name'] ?? 'Tanpa Nama') ?></h2>
          <p class="profile-email"><?= htmlspecialchars($item['email'] ?? '-') ?></p>

          <div class="profile-badges">
            <?php
            $roleClass = 'bg-blue';
            if ($item['role'] === 'admin') $roleClass = 'bg-primary';
            if ($item['role'] === 'approver') $roleClass = 'bg-orange';
            ?>
            <span class="badge <?= $roleClass ?>"><?= strtoupper($item['role'] ?? 'User') ?></span>

            <?php if ($item['is_active']): ?>
              <span class="badge bg-green">AKTIF</span>
            <?php else: ?>
              <span class="badge bg-gray">NON-AKTIF</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="profile-body">
        <h3 class="section-title">Informasi Aktivitas</h3>
        <div class="info-grid">
          <div class="info-box">
            <span class="info-label">Terakhir Login</span>
            <span class="info-value">
              <?= $item['last_login_at'] ? date('d F Y • H:i', strtotime($item['last_login_at'])) : 'Belum pernah login' ?>
            </span>
          </div>
          <div class="info-box">
            <span class="info-label">Tanggal Didaftarkan</span>
            <span class="info-value">
              <?= $item['created_at'] ? date('d F Y', strtotime($item['created_at'])) : '-' ?>
            </span>
          </div>
        </div>
      </div>

      <div class="profile-footer">
        <a data-spa href="<?= getBaseUrl('/users/' . $item['id'] . '/edit') ?>" class="btn-confirm success" style="text-decoration: none;">✎ Edit Hak Akses</a>
      </div>
    </div>
  <?php else: ?>
    <div class="table-card" style="padding: 4rem; text-align: center;">
      <p class="text-muted">Pengguna tidak ditemukan di dalam sistem.</p>
    </div>
  <?php endif; ?>
</div>