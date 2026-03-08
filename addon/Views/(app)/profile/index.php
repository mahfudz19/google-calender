<div class="profile-view-container">
  <!-- Header Section -->
  <div class="profile-view-header">
    <div class="profile-view-header-content">
      <a data-spa href="/dashboard" class="profile-view-back-btn">
        <svg viewBox="0 0 24 24" width="20" height="20">
          <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" />
        </svg>
        Kembali
      </a>
      <div class="profile-view-title-section">
        <h1>Profil Pengguna</h1>
        <p>Informasi akun dan data pengguna</p>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="profile-content">
    <!-- Profile Card -->
    <div class="profile-view-main-card">
      <div class="profile-view-avatar-wrapper">
        <img src="<?= $user_login['avatar'] ?? '/assets/images/default-avatar.png' ?>" alt="Avatar">
        <div class="avatar-status"></div>
      </div>
      <div class="profile-view-user-info">
        <h2><?= htmlspecialchars($user_login['name'] ?? 'User') ?></h2>
        <p class="email"><?= htmlspecialchars($user_login['email'] ?? '') ?></p>
        <span class="profile-view-role-badge profile-view-badge-<?= $user_login['role'] ?? 'user' ?>">
          <?= ucfirst($user_login['role'] ?? 'user') ?>
        </span>
      </div>
    </div>

    <!-- Information Sections -->
    <div class="profile-view-details-container">
      <div class="profile-view-detail-section">
        <h3>Informasi Akun</h3>
        <div class="profile-view-detail-grid">
          <div class="profile-view-detail-item">
            <label>Nama Lengkap</label>
            <span><?= htmlspecialchars($user['name'] ?? $user_login['name'] ?? '-') ?></span>
          </div>
          <div class="profile-view-detail-item">
            <label>Email</label>
            <span><?= htmlspecialchars($user['email'] ?? $user_login['email'] ?? '-') ?></span>
          </div>
          <div class="profile-view-detail-item">
            <label>Peran</label>
            <span class="profile-view-role-badge profile-view-badge-<?= ($user['role'] ?? $user_login['role']) ?>">
              <?= ucfirst($user['role'] ?? $user_login['role'] ?? '-') ?>
            </span>
          </div>
          <div class="profile-view-detail-item">
            <label>Status</label>
            <span class="profile-view-status-active">Aktif</span>
          </div>
          <div class="profile-view-detail-item">
            <label>Google ID</label>
            <span class="profile-view-google-id"><?= $user_login['google_id'] ?? 'Tidak terhubung' ?></span>
          </div>
        </div>
      </div>

      <div class="profile-view-detail-section">
        <h3>Keamanan</h3>
        <div class="profile-view-security-container">
          <div class="profile-view-security-item">
            <div class="profile-view-security-icon">
              <svg viewBox="0 0 24 24" width="24" height="24">
                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z" />
              </svg>
            </div>
            <div class="profile-view-security-content">
              <h4>Autentikasi Google OAuth</h4>
              <p>Akun terautentikasi melalui Google</p>
              <span class="profile-view-auth-status connected">Terhubung</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>