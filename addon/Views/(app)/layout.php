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

      <div class="nav-left">
        <button type="button" class="mobile-hamburger-btn" id="sidebarToggle" title="Menu Utama">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
          </svg>
        </button>

        <a data-spa href="<?= getBaseUrl('/dashboard') ?>" class="nav-brand">
          <img src="<?= getBaseUrl('/logo_app/apple-touch-icon.png') ?>" alt="Mazu">
          <span class="nav-brand-text">Calendar</span>
        </a>
      </div>

      <div class="nav-center-spacer" style="flex: 1;"></div>

      <div class="nav-right" style="display: flex; align-items: center; gap: 4px;">

        <?php if (in_array($role, ['admin', 'approver'])): ?>
          <div class="apps-wrapper" style="position: relative;">
            <input type="checkbox" id="apps-toggle" class="css-toggle-input" hidden>

            <label for="apps-toggle" class="icon-action-btn" title="Aplikasi Mazu">
              <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                <path d="M6 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm6 0c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm6 0c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zM6 14c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm6 0c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm6 0c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zM6 20c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm6 0c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm6 0c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"></path>
              </svg>
            </label>

            <label for="apps-toggle" class="dropdown-backdrop" hidden></label>

            <div class="dropdown-menu apps-dropdown-custom">
              <div class="apps-grid">
                <a data-spa href="<?= getBaseUrl('/approval') ?>"
                  onclick="document.getElementById('apps-toggle').checked=false"
                  class="app-grid-item nav-link-item <?= str_contains($currentUri, getBaseUrl('/approval')) ? 'active' : '' ?>">
                  <div class="app-grid-icon" style="background: var(--warning-bg); color: var(--warning-dark);">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="9 11 12 14 22 4"></polyline>
                      <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                  </div>
                  <span>Persetujuan</span>
                </a>

                <a data-spa href="<?= getBaseUrl('/input-many') ?>"
                  onclick="document.getElementById('apps-toggle').checked=false"
                  class="app-grid-item nav-link-item <?= str_contains($currentUri, getBaseUrl('/input-many')) ? 'active' : '' ?>">
                  <div class="app-grid-icon" style="background: var(--primary-bg); color: var(--primary-dark);">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                      <polyline points="17 8 12 3 7 8"></polyline>
                      <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                  </div>
                  <span>Input Many</span>
                </a>

                <?php if ($role === 'admin'): ?>
                  <a data-spa href="<?= getBaseUrl('/users') ?>"
                    onclick="document.getElementById('apps-toggle').checked=false"
                    class="app-grid-item nav-link-item <?= str_contains($currentUri, getBaseUrl('/users')) ? 'active' : '' ?>">
                    <div class="app-grid-icon" style="background: var(--success-bg); color: var(--success-dark);">
                      <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                      </svg>
                    </div>
                    <span>Users</span>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <script>
            (function() {
              function updateActiveState() {
                const currentPath = window.location.pathname;
                document.querySelectorAll('.nav-link-item').forEach(link => {
                  const href = link.getAttribute('href');
                  // Set active jika path saat ini dimulai dengan href menu
                  if (currentPath.startsWith(href)) {
                    link.classList.add('active');
                  } else {
                    link.classList.remove('active');
                  }
                });
              }

              // Jalankan setiap kali SPA selesai berpindah halaman
              window.addEventListener('spa:navigated', updateActiveState);
              // Jalankan juga saat tombol back/forward browser diklik
              window.addEventListener('popstate', updateActiveState);
            })();
          </script>
        <?php endif; ?>

        <div class="profile-wrapper" style="position: relative;">
          <input type="checkbox" id="profile-toggle" class="css-toggle-input" hidden>

          <label for="profile-toggle" class="profile-btn" title="Akun Google">
            <img src="<?= $avatar ?>" alt="Profile">
          </label>

          <label for="profile-toggle" class="dropdown-backdrop" hidden></label>

          <div class="dropdown-menu" style="width: 280px; top: 48px; right: 0;">
            <div class="dropdown-header">
              <img src="<?= $avatar ?>" alt="Profile" class="dropdown-avatar-large">
              <div class="dropdown-info">
                <div class="dropdown-name"><?= $name ?></div>
                <div class="dropdown-role"><?= ucfirst($role) ?></div>
              </div>
            </div>
            <div class="dropdown-divider"></div>

            <a data-spa href="<?= getBaseUrl('/profile') ?>" onclick="document.getElementById('profile-toggle').checked=false" class="dropdown-item">
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

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <main class="app-main-content" id="app-content" data-layout="(app)/layout.php">
    <?= $children ?>
  </main>
</div>

<script>
  (function initSidebarLogic() {
    const toggleBtn = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');

    // Mengambil elemen sidebar yang dirender dinamis dari $children
    function getSidebar() {
      return document.querySelector('.app-sidebar');
    }

    function toggleSidebar() {
      const sidebar = getSidebar();
      if (!sidebar) return; // Batalkan jika halaman ini tidak punya sidebar

      sidebar.classList.toggle('show');
      if (overlay) overlay.classList.toggle('active');
    }

    function closeSidebar() {
      const sidebar = getSidebar();
      if (sidebar) sidebar.classList.remove('show');
      if (overlay) overlay.classList.remove('active');
    }

    // Fitur Cerdas: Sembunyikan hamburger jika halaman tidak punya sidebar
    function checkSidebarPresence() {
      if (toggleBtn) {
        if (getSidebar()) {
          toggleBtn.style.display = ''; // Biarkan CSS memunculkannya di mobile
        } else {
          toggleBtn.style.display = 'none'; // Sembunyikan paksa karena tidak ada sidebar
        }
      }
    }

    // Pasang Event Listeners
    if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);

    // CLICK-AWAY SEKARANG PASTI BEKERJA
    if (overlay) overlay.addEventListener('click', closeSidebar);

    // Inisialisasi saat pertama dimuat
    checkSidebarPresence();

    // Event saat SPA berpindah halaman
    window.addEventListener('spa:navigated', () => {
      closeSidebar(); // Tutup sidebar jika sedang terbuka
      checkSidebarPresence(); // Periksa lagi apakah halaman baru ini punya sidebar
    });
  })();
</script>