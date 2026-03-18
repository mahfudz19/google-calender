<div class="app-layout">

  <aside class="app-sidebar">
    <a data-spa href="<?= getBaseUrl('/dashboard') ?>" class="app-btn-back">
      <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      Kembali ke Dashboard
    </a>

    <?php
    // Logika sederhana untuk menghitung statistik (Sesuaikan dengan struktur array $users Anda)
    $totalUsers = count($users ?? []);
    $integratedUsers = 0;
    $adminUsers = 0;
    foreach ($users ?? [] as $u) {
      if (!empty($u['is_registered'])) $integratedUsers++;
      if (($u['role'] ?? '') === 'admin') $adminUsers++;
    }
    ?>

    <div class="usr-sidebar-widget">
      <h3 class="usr-widget-title">Ringkasan Sistem</h3>
      <div class="usr-stat-list">
        <div class="usr-stat-item">
          <span class="usr-stat-label">Total Akun G-Suite</span>
          <span class="usr-stat-value"><?= $totalUsers ?></span>
        </div>
        <div class="usr-stat-item">
          <span class="usr-stat-label">User Terintegrasi</span>
          <span class="usr-stat-value text-success"><?= $integratedUsers ?></span>
        </div>
        <div class="usr-stat-item">
          <span class="usr-stat-label">Super Admin</span>
          <span class="usr-stat-value text-primary"><?= $adminUsers ?></span>
        </div>
      </div>
    </div>

    <div class="usr-sidebar-widget">
      <h3 class="usr-widget-title">Filter Cepat</h3>
      <div class="usr-filter-menu">
        <button type="button" class="usr-filter-btn active" data-filter="all">
          <span class="usr-filter-dot" style="background-color: var(--text-secondary);"></span> Semua Pengguna
        </button>
        <button type="button" class="usr-filter-btn" data-filter="admin">
          <span class="usr-filter-dot" style="background-color: var(--primary-main);"></span> Hanya Admin
        </button>
        <button type="button" class="usr-filter-btn" data-filter="approver">
          <span class="usr-filter-dot" style="background-color: var(--warning-main);"></span> Hanya Approver
        </button>
      </div>
    </div>

    <div class="usr-sidebar-info">
      <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="16" x2="12" y2="12"></line>
        <line x1="12" y1="8" x2="12.01" y2="8"></line>
      </svg>
      <div>
        <strong>G-Suite Only</strong> berarti akun ada di Google Workspace organisasi Anda, tetapi belum didaftarkan ke database Mazu Calendar.
      </div>
    </div>
  </aside>

  <main class="app-main">
    <div class="usr-main" data-layout="addon/Views/(app)/users/layout.php">


      <?php
      // Pastikan data aman untuk di-inject ke atribut HTML
      $jsonUsers = htmlspecialchars(json_encode($users ?? []), ENT_QUOTES, 'UTF-8');
      $loggedInEmail = htmlspecialchars($user_login['email'] ?? '', ENT_QUOTES, 'UTF-8');
      ?>

      <div class="usr-layout-wrapper" id="usersApp" data-base-url="<?= getBaseUrl() ?>" data-users="<?= $jsonUsers ?>" data-email="<?= $loggedInEmail ?>">

        <div class="usr-header-section">
          <div>
            <h2 class="usr-page-title">Manajemen Pengguna</h2>
            <p class="usr-page-subtitle">Kelola akses dan peran pengguna yang tersinkronisasi dengan Google Workspace.</p>
          </div>

          <div class="usr-controls">
            <div class="usr-search-box">
              <svg class="usr-search-icon" viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
              </svg>
              <input type="text" id="searchInput" class="usr-search-input" placeholder="Telusuri pengguna..." autocomplete="off">
            </div>

            <div class="usr-select-box">
              <select id="limitSelect" class="usr-select-input">
                <option value="10">10 baris</option>
                <option value="25">25 baris</option>
                <option value="50">50 baris</option>
                <option value="all">Semua</option>
              </select>
            </div>
          </div>
        </div>

        <div class="usr-table-container">
          <table class="usr-table" id="usersTable">
            <thead>
              <tr>
                <th data-sort="name" class="usr-sortable">Pengguna <span class="usr-sort-icon"></span></th>
                <th data-sort="email" class="usr-sortable">Email <span class="usr-sort-icon"></span></th>
                <th data-sort="google_org_unit" class="usr-sortable">Departemen <span class="usr-sort-icon"></span></th>
                <th data-sort="is_registered" class="usr-sortable">Status <span class="usr-sort-icon"></span></th>
                <th data-sort="role" class="usr-sortable">Peran <span class="usr-sort-icon"></span></th>
                <th class="usr-text-right">Aksi</th>
              </tr>
            </thead>
            <tbody id="tableBody">
            </tbody>
          </table>
        </div>

        <div class="usr-table-footer">
          <div class="usr-table-info" id="tableInfo">Menampilkan 0 dari 0 data</div>
          <div class="usr-pagination" id="paginationWrapper">
          </div>
        </div>

      </div>


    </div>
  </main>

</div>