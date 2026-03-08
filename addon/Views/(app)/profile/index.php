<div class="profile-container">
  <div class="profile-header">
    <a data-spa href="/dashboard" class="btn-back">← Kembali ke Dashboard</a>
    <h1 class="page-title">Profil Pengguna</h1>
    <p class="page-subtitle">Informasi akun dan pengaturan profil Anda</p>
  </div>

  <div class="profile-content">
    <div>
      <div class="profile-card">
        <div class="profile-avatar-section">
          <div class="avatar-wrapper">
            <img id="profile-avatar" src="<?= $user_login['avatar'] ?? '/assets/images/default-avatar.png' ?>" alt="Profile Avatar" class="profile-avatar-large">
            <div class="avatar-badge">
              <svg viewBox="0 0 24 24" width="20" height="20">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
              </svg>
            </div>
          </div>
          <div class="profile-info">
            <h2 id="profile-name" class="profile-name"><?= htmlspecialchars($user_login['name'] ?? 'User') ?></h2>
            <div class="profile-role-badge">
              <span id="profile-role" class="badge <?= $user_login['role'] === 'admin' ? 'badge-orange' : 'badge-green' ?>">
                <?= ucfirst($user_login['role'] ?? 'user') ?>
              </span>
            </div>
            <p id="profile-email" class="profile-email"><?= htmlspecialchars($user_login['email'] ?? '') ?></p>
          </div>
        </div>

        <div class="profile-actions">
          <button type="button" class="btn-google-sync" onclick="syncWithGoogle()">
            <svg viewBox="0 0 24 24" width="20" height="20">
              <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
              <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
              <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
              <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
            </svg>
            Sync dengan Google Profile
          </button>
        </div>
      </div>
    </div>

    <div class="profile-details">
      <div class="detail-section">
        <h3 class="section-title">Informasi Akun</h3>
        <div class="detail-grid">
          <div class="detail-item">
            <label class="detail-label">Nama Lengkap</label>
            <div id="profile-name" class="detail-value"><?= htmlspecialchars($user['name'] ?? $user_login['name'] ?? '-') ?></div>
          </div>
          <div class="detail-item">
            <label class="detail-label">Email</label>
            <div id="profile-email" class="detail-value"><?= htmlspecialchars($user['email'] ?? $user_login['email'] ?? '-') ?></div>
          </div>
          <div class="detail-item">
            <label class="detail-label">Peran</label>
            <div class="detail-value">
              <span id="profile-role" class="badge <?= ($user['role'] ?? $user_login['role']) === 'admin' ? 'badge-orange' : 'badge-green' ?>">
                <?= ucfirst($user['role'] ?? $user_login['role'] ?? '-') ?>
              </span>
            </div>
          </div>
          <div class="detail-item">
            <label class="detail-label">Status Akun</label>
            <div class="detail-value">
              <span class="status-badge active">
                <svg viewBox="0 0 24 24" width="12" height="12">
                  <circle cx="12" cy="12" r="10" />
                </svg>
                Aktif
              </span>
            </div>
          </div>
          <div class="detail-item">
            <label class="detail-label">Google ID</label>
            <div class="detail-value">
              <code class="google-id"><?= $user_login['google_id'] ?? 'Tidak terhubung' ?></code>
            </div>
          </div>
        </div>
      </div>

      <div class="detail-section">
        <h3 class="section-title">Keamanan</h3>
        <div class="security-info">
          <div class="security-item">
            <div class="security-icon">
              <svg viewBox="0 0 24 24" width="24" height="24">
                <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z" />
              </svg>
            </div>
            <div class="security-content">
              <h4>Autentikasi Google</h4>
              <p>Akun Anda terautentikasi melalui Google OAuth</p>
              <div class="auth-status">
                <span class="status-badge connected">Terhubung</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function syncWithGoogle() {
    // Placeholder untuk sync functionality
    showToast('Fitur sync dengan Google akan segera tersedia');
  }
</script>