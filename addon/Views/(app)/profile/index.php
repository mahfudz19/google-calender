<div class="myprof-container">

  <div class="myprof-page-header">
    <a data-spa href="/dashboard" class="myprof-btn-back">
      <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      Kembali ke Dashboard
    </a>
  </div>

  <div class="myprof-header-section">
    <div class="myprof-avatar-wrapper">
      <img src="<?= htmlspecialchars($user_login['avatar'] ?? '/assets/images/default-avatar.png') ?>" alt="Avatar" class="myprof-avatar">
    </div>
    <h1 class="myprof-name"><?= htmlspecialchars($user_login['name'] ?? 'User') ?></h1>
    <p class="myprof-email"><?= htmlspecialchars($user_login['email'] ?? '') ?></p>
    <span class="myprof-badge badge-<?= $user_login['role'] ?? 'user' ?>">
      <?= ucfirst($user_login['role'] ?? 'User') ?> Sistem
    </span>
  </div>

  <div class="myprof-card">
    <h2 class="myprof-card-title">Informasi Dasar</h2>
    <div class="myprof-list">
      <div class="myprof-list-item">
        <div class="myprof-item-label">Nama Lengkap</div>
        <div class="myprof-item-value"><?= htmlspecialchars($user['name'] ?? $user_login['name'] ?? '-') ?></div>
      </div>
      <div class="myprof-list-item">
        <div class="myprof-item-label">Email</div>
        <div class="myprof-item-value"><?= htmlspecialchars($user['email'] ?? $user_login['email'] ?? '-') ?></div>
      </div>
      <div class="myprof-list-item">
        <div class="myprof-item-label">Peran (Role)</div>
        <div class="myprof-item-value"><?= ucfirst($user['role'] ?? $user_login['role'] ?? '-') ?></div>
      </div>
      <div class="myprof-list-item">
        <div class="myprof-item-label">Status Akun</div>
        <div class="myprof-item-value">
          <span style="color: var(--success-main); font-weight: 500;">Aktif</span>
        </div>
      </div>
      <div class="myprof-list-item">
        <div class="myprof-item-label">Google ID</div>
        <div class="myprof-item-value" style="font-family: monospace; color: var(--text-secondary); background: var(--bg-default); padding: 2px 8px; border-radius: 4px; display: inline-block;">
          <?= htmlspecialchars($user_login['google_id'] ?? 'Tidak terhubung') ?>
        </div>
      </div>
    </div>
  </div>

  <div class="myprof-card">
    <h2 class="myprof-card-title">Keamanan & Login</h2>
    <div class="myprof-security-item">
      <div class="myprof-security-icon">
        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
        </svg>
      </div>
      <div class="myprof-security-info">
        <h3>Autentikasi Google Workspace</h3>
        <p>Akses akun Anda dikelola dan diamankan secara langsung oleh sistem SSO Google.</p>
      </div>
      <div class="myprof-security-status">
        <span class="myprof-status-pill">Tersambung</span>
      </div>
    </div>
  </div>

</div>