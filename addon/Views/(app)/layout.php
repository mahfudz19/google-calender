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

      <nav class="nav-menu" id="navMenu">
        <a data-spa href="<?= getBaseUrl('/dashboard') ?>" class="nav-item <?= str_contains($currentUri, getBaseUrl('/dashboard')) ? 'active' : '' ?>">Dashboard</a>
        <a data-spa href="<?= getBaseUrl('/agenda') ?>" class="nav-item <?= str_contains($currentUri, getBaseUrl('/agenda')) ? 'active' : '' ?>">Agenda Saya</a>

      </nav>

      <div class="nav-right" style="display: flex; align-items: center; gap: 8px;">

        <?php if ($role === 'admin' || $role === 'approver'): ?>
          <div class="settings-wrapper" style="position: relative;">
            <input type="checkbox" id="settings-toggle" class="css-toggle-input" hidden>

            <label for="settings-toggle" class="icon-action-btn" title="Menu Admin">
              <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="var(--text-secondary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
              </svg>
            </label>

            <label for="settings-toggle" class="dropdown-backdrop" hidden></label>

            <div class="dropdown-menu" style="width: 220px; top: 50px;">
              <div class="dropdown-header" style="padding: 12px 16px;">
                <div class="dropdown-name" style="font-size: 14px; color: var(--text-secondary);">Administrator</div>
              </div>
              <div class="dropdown-divider"></div>
              <a data-spa href="<?= getBaseUrl('/approval') ?>" class="dropdown-item">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                  <circle cx="9" cy="7" r="4"></circle>
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                  <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                Persetujuan
              </a>
              <?php if ($role === 'admin'): ?>
                <a data-spa href="<?= getBaseUrl('/users') ?>" class="dropdown-item">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                  </svg>
                  Kelola Users
                </a>
              <?php endif; ?>

              <a data-spa href="<?= getBaseUrl('/input-many') ?>" class="dropdown-item">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 20h9"></path>
                  <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                </svg>
                Input Many
              </a>
            </div>
          </div>
        <?php endif; ?>

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