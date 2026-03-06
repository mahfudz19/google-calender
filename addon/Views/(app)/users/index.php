<?php
// Pastikan data aman untuk di-inject ke atribut HTML (mengubah kutip menjadi entitas HTML)
$jsonUsers = htmlspecialchars(json_encode($users ?? []), ENT_QUOTES, 'UTF-8');
$loggedInEmail = htmlspecialchars($user_login['email'] ?? '', ENT_QUOTES, 'UTF-8');
?>

<div class="users-container" id="usersApp" data-base-url="<?= getBaseUrl() ?>" data-users="<?= $jsonUsers ?>" data-email="<?= $loggedInEmail ?>">
  <div class="page-header">
    <div>
      <h2 class="page-title">Manajemen Pengguna</h2>
      <p class="page-subtitle">Kelola akses dan peran pengguna yang tersinkronisasi dengan Google Workspace.</p>
    </div>

    <div class="table-controls">
      <div class="search-wrapper">
        <span class="search-icon">🔍</span>
        <input type="text" id="searchInput" placeholder="Cari nama atau email..." autocomplete="off">
      </div>
      <div class="limit-wrapper">
        <select id="limitSelect">
          <option value="10">10 per halaman</option>
          <option value="25">25 per halaman</option>
          <option value="50">50 per halaman</option>
          <option value="all">Tampilkan Semua</option>
        </select>
      </div>
    </div>
  </div>

  <div class="table-card">
    <div class="table-responsive">
      <table class="mazu-datatable" id="usersTable">
        <thead>
          <tr>
            <th data-sort="name" class="sortable">Pengguna <span class="sort-icon"></span></th>
            <th data-sort="email" class="sortable">Email <span class="sort-icon"></span></th>
            <th data-sort="google_org_unit" class="sortable">Departemen <span class="sort-icon"></span></th>
            <th data-sort="is_registered" class="sortable text-center">Status Sistem <span class="sort-icon"></span></th>
            <th data-sort="role" class="sortable text-center">Hak Akses <span class="sort-icon"></span></th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody id="tableBody">
        </tbody>
      </table>
    </div>

    <div class="table-footer">
      <div class="table-info" id="tableInfo">Menampilkan 0 dari 0 data</div>
      <div class="pagination-controls" id="paginationWrapper">
      </div>
    </div>
  </div>

</div>