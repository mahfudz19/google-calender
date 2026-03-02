<?php
$user = $_SESSION['user'] ?? [];
$role = $user['role'] ?? 'guest';
$avatar = $user['avatar'] ?? '/public/logo_app/mazu-icon.svg';
$name = htmlspecialchars($user['name'] ?? 'User');
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
?>

<div data-layout="layout.php" class="app-wrapper">
  <header class="top-navbar">
    <div class="nav-container">
      <a data-spa href="/dashboard" class="nav-brand">
        <img src="logo_app/apple-touch-icon.png" alt="Mazu">
      </a>

      <button class="mobile-menu-btn" id="mobileMenuBtn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="3" y1="12" x2="21" y2="12"></line>
          <line x1="3" y1="6" x2="21" y2="6"></line>
          <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
      </button>

      <nav class="nav-menu" id="navMenu">
        <a data-spa href="<?= getBaseUrl('/dashboard') ?>" class="nav-item <?= str_contains($currentUri, getBaseUrl('/dashboard')) ? 'active' : '' ?>">Dashboard</a>
        <a data-spa href="<?= getBaseUrl('/agenda') ?>" class="nav-item <?= str_contains($currentUri, getBaseUrl('/agenda')) ? 'active' : '' ?>">Agenda Saya</a>

        <?php if ($role === 'admin'): ?>
          <a data-spa href="<?= getBaseUrl('/approval') ?>" class="nav-item <?= str_contains($currentUri, getBaseUrl('/approval')) ? 'active' : '' ?>">Persetujuan</a>
          <a data-spa href="<?= getBaseUrl('/users') ?>" class="nav-item <?= str_contains($currentUri, getBaseUrl('/users')) ? 'active' : '' ?>">Manajemen User</a>
        <?php endif; ?>

        <div class="nav-profile mobile-only">
          <div class="profile-info">
            <img src="<?= $avatar ?>" alt="Avatar" class="avatar">
            <span><?= $name ?></span>
          </div>
          <form action="/logout" data-spa method="post">
            <button type="submit" class="btn-logout">Logout</button>
          </form>
        </div>
      </nav>

      <div class="nav-profile desktop-only">
        <img src="<?= $avatar ?>" alt="Avatar" class="avatar">
        <div class="profile-text">
          <span class="user-name"><?= $name ?></span>
          <span class="user-role"><?= ucfirst($role) ?></span>
        </div>
        <form action="/logout" data-spa method="post">
          <button type="submit" class="btn-logout-icon">
            <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
              <polyline points="16 17 21 12 16 7"></polyline>
              <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
          </button>
        </form>
      </div>
    </div>
  </header>

  <main class="app-main">
    <div class="content-container" data-layout="(app)/layout.php">
      <?= $children  ?>
    </div>
  </main>
</div>