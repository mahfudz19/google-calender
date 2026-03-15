<?php $item = $item ?? null; ?>
<div class="upf-container">

  <div class="upf-page-header">
    <a data-spa href="<?= getBaseUrl('/users') ?>" class="upf-btn-back">
      <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      Kembali ke Daftar Pengguna
    </a>
  </div>

  <?php if ($item): ?>
    <div class="upf-card">

      <div class="upf-header">
        <div class="upf-avatar-wrapper">
          <?php if (!empty($item['avatar'])): ?>
            <img src="<?= htmlspecialchars($item['avatar']) ?>" alt="Avatar" class="upf-avatar-lg">
          <?php else: ?>
            <div class="upf-avatar-lg fallback"><?= strtoupper(substr($item['name'] ?? 'U', 0, 1)) ?></div>
          <?php endif; ?>
        </div>

        <div class="upf-title-group">
          <h2 class="upf-name"><?= htmlspecialchars($item['name'] ?? 'Tanpa Nama') ?></h2>
          <p class="upf-email"><?= htmlspecialchars($item['email'] ?? '-') ?></p>

          <div class="upf-badges">
            <?php
            $roleClass = 'badge-blue';
            if ($item['role'] === 'admin') $roleClass = 'badge-primary';
            if ($item['role'] === 'approver') $roleClass = 'badge-orange';
            ?>
            <span class="upf-badge <?= $roleClass ?>"><?= strtoupper($item['role'] ?? 'User') ?></span>

            <?php if ($item['is_active']): ?>
              <span class="upf-badge badge-green">AKSES AKTIF</span>
            <?php else: ?>
              <span class="upf-badge badge-gray">AKSES DITOLAK</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="upf-body">
        <h3 class="upf-section-title">Aktivitas Sistem</h3>
        <div class="upf-info-grid">
          <div class="upf-info-box">
            <span class="upf-info-label">Terakhir Login</span>
            <span class="upf-info-value">
              <?= $item['last_login_at'] ? date('d F Y • H:i', strtotime($item['last_login_at'])) : 'Belum pernah login' ?>
            </span>
          </div>
          <div class="upf-info-box">
            <span class="upf-info-label">Tanggal Didaftarkan</span>
            <span class="upf-info-value">
              <?= $item['created_at'] ? date('d F Y', strtotime($item['created_at'])) : '-' ?>
            </span>
          </div>
        </div>
      </div>

      <div class="upf-footer">
        <a data-spa href="<?= getBaseUrl('/users/' . $item['id'] . '/edit') ?>" class="upf-btn-primary">
          <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
          </svg>
          Edit Hak Akses
        </a>
      </div>
    </div>
  <?php else: ?>
    <div class="upf-empty-state">
      <div class="upf-empty-icon">🔍</div>
      <p>Pengguna tidak ditemukan di dalam sistem.</p>
    </div>
  <?php endif; ?>

</div>