<?php
$user = $_SESSION['user'] ?? [];
$role = $user['role'] ?? 'guest';
$avatar = $user['avatar'] ?? getBaseUrl('/logo_app/apple-touch-icon.png');
$name = htmlspecialchars($user['name'] ?? 'User');
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
?>

<div class="app-wrapper">
  <header class="top-navbar">
    <div class="nav-container">

      <input type="checkbox" id="mobile-menu-toggle" class="css-toggle-input" hidden>
      <label for="mobile-menu-toggle" class="mobile-menu-backdrop" hidden></label>

      <div class="nav-left">
        <label for="mobile-menu-toggle" class="mobile-menu-btn" title="Menu Utama">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
          </svg>
        </label>

        <a data-spa href="<?= getBaseUrl('/dashboard') ?>" class="nav-brand">
          <img src="<?= getBaseUrl('/logo_app/apple-touch-icon.png') ?>" alt="Mazu">
          <span class="nav-brand-text">Calendar</span>
        </a>
      </div>
      <?php if ($role === 'admin' || $role === 'approver'): ?>
        <nav class="nav-menu" id="navMenu">
          <a data-spa href="<?= getBaseUrl('/approval') ?>" class="nav-item <?= str_contains($currentUri, getBaseUrl('/approval')) ? 'active' : '' ?>">Persetujuan</a>
          <a data-spa href="<?= getBaseUrl('/input-many') ?>" class="nav-item <?= str_contains($currentUri, getBaseUrl('/input-many')) ? 'active' : '' ?>">Input Many</a>
          <?php if ($role === 'admin'): ?>
            <a data-spa href="<?= getBaseUrl('/users') ?>" class="nav-item <?= str_contains($currentUri, getBaseUrl('/users')) ? 'active' : '' ?>">Users</a>
          <?php endif; ?>
        </nav>
      <?php endif; ?>

      <div class="nav-right" style="display: flex; align-items: center; gap: 8px;">
        <div class="profile-wrapper" style="position: relative;">
          <input type="checkbox" id="profile-toggle" class="css-toggle-input" hidden>

          <label for="profile-toggle" class="profile-btn" title="Akun Google">
            <img src="<?= $avatar ?>" alt="Profile">
          </label>

          <label for="profile-toggle" class="dropdown-backdrop" hidden></label>

          <div class="dropdown-menu" style="width: 280px; top: 50px;">
            <div class="dropdown-header">
              <img src="<?= $avatar ?>" alt="Profile" class="dropdown-avatar-large">
              <div class="dropdown-info">
                <div class="dropdown-name"><?= $name ?></div>
                <div class="dropdown-role"><?= ucfirst($role) ?></div>
              </div>
            </div>
            <div class="dropdown-divider"></div>
            <a data-spa href="<?= getBaseUrl('/profile') ?>" class="dropdown-item">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              Profile Saya
            </a>
            <form action="<?= getBaseUrl('/logout') ?>" data-spa method="post">
              <button type="submit" class="dropdown-item logout">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                  <polyline points="16 17 21 12 16 7"></polyline>
                  <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                Keluar
              </button>
            </form>
          </div>
        </div>

      </div>

    </div>
  </header>

  <main class="app-main-content" id="app-content" data-layout="(app)/layout.php">
    <?= $children ?>
  </main>
</div>