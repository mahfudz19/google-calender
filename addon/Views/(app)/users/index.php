<?php
// Pastikan data aman untuk di-inject ke atribut HTML
$jsonUsers = htmlspecialchars(json_encode($users ?? []), ENT_QUOTES, 'UTF-8');
$loggedInEmail = htmlspecialchars($user_login['email'] ?? '', ENT_QUOTES, 'UTF-8');
?>

<div class="usr-layout-wrapper" id="usersApp" data-base-url="<?= getBaseUrl() ?>" data-users="<?= $jsonUsers ?>" data-email="<?= $loggedInEmail ?>">

  <div class="usr-header-section">
    <div class="usr-header-text">
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