<?php
$user = $_SESSION['user'] ?? ['name' => 'Guest', 'email' => '', 'avatar' => null];
$role = $_SESSION['user']['role'] ?? 'user';
?>

<div class="app-layout">
  <!-- Top Navigation Bar -->
  <nav class="top-nav">
    <div class="nav-left">
      <a data-spa href="<?= getBaseUrl('/') ?>" class="nav-brand">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #4f46e5;">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="16" y1="2" x2="16" y2="6"></line>
          <line x1="8" y1="2" x2="8" y2="6"></line>
          <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        <span>AgendaApp</span>
      </a>

      <div class="nav-links">
        <a data-spa href="<?= getBaseUrl('/dashboard') ?>" class="nav-link" data-nav-path="<?= getBaseUrl('/dashboard') ?>">
          Dashboard
        </a>
        <a data-spa href="<?= getBaseUrl('/agenda') ?>" class="nav-link" data-nav-path="<?= getBaseUrl('/agenda') ?>">
          Pengajuan Saya
        </a>

        <?php if (in_array($role, ['admin', 'approver'])): ?>
          <a data-spa href="<?= getBaseUrl('/approval') ?>" class="nav-link" data-nav-path="<?= getBaseUrl('/approval') ?>">
            Persetujuan
            <!-- Badge bisa ditambah disini nanti -->
          </a>
        <?php endif; ?>

        <?php if ($role === 'admin'): ?>
          <a data-spa href="<?= getBaseUrl('/users') ?>" class="nav-link" data-nav-path="<?= getBaseUrl('/users') ?>">
            Users
          </a>
        <?php endif; ?>
      </div>
    </div>

    <div class="nav-right">
      <div class="user-menu">
        <div class="user-avatar">
          <?php if (!empty($user['avatar'])): ?>
            <img src="<?= $user['avatar'] ?>" alt="Avatar" style="width:100%; height:100%; border-radius:50%;">
          <?php else: ?>
            <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
          <?php endif; ?>
        </div>
        <span style="font-size: 0.9rem; font-weight: 500; color: #334155; display: none; @media(min-width: 768px){display:block;}">
          <?= htmlspecialchars($user['name']) ?>
        </span>
      </div>
      <a href="/logout" class="logout-btn" title="Keluar">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
          <polyline points="16 17 21 12 16 7"></polyline>
          <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
      </a>
    </div>
  </nav>

  <!-- Main Content Wrapper -->
  <main style="flex: 1; padding: 0;" data-layout="(app)/layout.php">
    <?= $children; ?>
  </main>
  <!-- App Footer -->
  <footer style="background: white; border-top: 1px solid #e2e8f0; padding: 1.5rem; text-align: center; color: #64748b; font-size: 0.85rem; margin-top: auto;">
    <p style="margin: 0;">&copy; <?= date('Y') ?> AgendaApp Internal System. All rights reserved.</p>
  </footer>

</div>