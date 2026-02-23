<?php
// Helper untuk membersihkan URL query string
$buildUrl = function (array $overrides = []) use ($pagination) {
  $params = array_merge([
    'page' => $pagination['current_page'],
    'sort' => $pagination['sort'] ?? 'name',
    'order' => $pagination['order'] ?? 'asc',
    'search' => $pagination['search'] ?? '',
  ], $overrides);

  // Hapus default values agar URL lebih bersih
  if (($params['page'] ?? 1) <= 1) unset($params['page']);
  if (($params['sort'] ?? 'name') === 'name') unset($params['sort']);
  if (($params['order'] ?? 'asc') === 'asc') unset($params['order']);
  if (empty($params['search'])) unset($params['search']);

  // Hapus parameter kosong/null
  $params = array_filter($params, fn($v) => $v !== '' && $v !== null);

  return empty($params) ? strtok($_SERVER['REQUEST_URI'], '?') : '?' . http_build_query($params);
};
?>
<div class="mazu-container">
  <div class="header-actions">
    <h1 class="display-small">Manajemen User (Google Directory)</h1>
    <form id="search-form" action="" method="get" class="search-box">
      <input type="text" name="search" placeholder="Cari nama atau email..." value="<?= htmlspecialchars($pagination['search'] ?? '') ?>">
      <button type="submit" class="btn btn-filled">Cari</button>
    </form>
  </div>

  <div class="mazu-card" style="padding: 0; overflow: hidden; margin-top: 24px;">
    <div class="table-responsive">
      <table class="mazu-table">
        <thead>
          <tr>
            <th>
              <?php
              $sort = $pagination['sort'] ?? 'name';
              $order = $pagination['order'] ?? 'asc';

              $nextOrderName = ($sort === 'name' && $order === 'asc') ? 'desc' : 'asc';
              $iconName = $sort === 'name' ? ($order === 'asc' ? '▲' : '▼') : '';
              ?>
              <a data-spa href="<?= $buildUrl(['sort' => 'name', 'order' => $nextOrderName]) ?>" style="display: flex; align-items: center; gap: 4px; color: inherit; text-decoration: none; cursor: pointer;">
                User (Google) <span style="font-size: 0.8em;"><?= $iconName ?></span>
              </a>
            </th>
            <th>
              <?php
              $nextOrderEmail = ($sort === 'email' && $order === 'asc') ? 'desc' : 'asc';
              $iconEmail = $sort === 'email' ? ($order === 'asc' ? '▲' : '▼') : '';
              ?>
              <a data-spa href="<?= $buildUrl(['sort' => 'email', 'order' => $nextOrderEmail]) ?>" style="display: flex; align-items: center; gap: 4px; color: inherit; text-decoration: none; cursor: pointer;">
                Email <span style="font-size: 0.8em;"><?= $iconEmail ?></span>
              </a>
            </th>
            <th>Org Unit</th>
            <th>Status Sistem</th>
            <th>Role</th>
            <th>Last Login</th>
            <th style="text-align: right;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($users)): ?>
            <tr>
              <td colspan="7" style="text-align: center; padding: 48px;">
                <p class="body-large" style="color: var(--text-secondary);">Tidak ada user ditemukan.</p>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($users as $user): ?>
              <tr>
                <td>
                  <div class="user-cell">
                    <?php if (!empty($user['avatar'])): ?>
                      <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="avatar-sm">
                    <?php else: ?>
                      <div class="avatar-sm placeholder">
                        <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                      </div>
                    <?php endif; ?>
                    <div class="user-info">
                      <span class="body-medium weight-bold"><?= htmlspecialchars($user['name'] ?? 'No Name') ?></span>
                    </div>
                  </div>
                </td>
                <td class="body-medium"><?= htmlspecialchars($user['email']) ?></td>
                <td class="body-small text-secondary"><?= htmlspecialchars($user['google_org_unit'] ?? '-') ?></td>
                <td>
                  <?php if ($user['is_registered']): ?>
                    <span class="badge badge-primary">Registered</span>
                  <?php else: ?>
                    <span class="badge badge-neutral">Google Only</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php
                  $roleColors = [
                    'admin' => 'badge-primary',
                    'approver' => 'badge-secondary',
                    'user' => 'badge-neutral'
                  ];
                  $badgeClass = $roleColors[$user['role'] ?? 'user'] ?? 'badge-neutral';
                  ?>
                  <span class="badge <?= $badgeClass ?>"><?= ucfirst($user['role'] ?? 'user') ?></span>
                </td>
                <td class="body-small text-secondary">
                  <?= $user['last_login_at'] ? date('d M Y H:i', strtotime($user['last_login_at'])) : '-' ?>
                </td>
                <td style="text-align: right;">
                  <?php if ($user_loggin['email'] === $user['email']): ?>
                    <span class="badge badge-neutral" style="font-style: italic;">It’s you</span>
                  <?php elseif ($user['is_registered']): ?>
                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                      <a data-spa href="/users/<?= $user['id'] ?>" class="btn-icon" title="Lihat Detail">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                          <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                      </a>
                      <a data-spa href="/users/<?= $user['id'] ?>/edit" class="btn-icon" title="Edit Role">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                      </a>
                      <a
                        href="/users/<?= $user['id'] ?>/delete"
                        data-spa
                        data-spa-method="POST"
                        class="btn-icon"
                        title="Hapus"
                        style="color: var(--md-sys-color-error);"
                        onclick="return confirm('Apakah Anda yakin ingin menghapus user ini dari database lokal?');">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <polyline points="3 6 5 6 21 6"></polyline>
                          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                      </a>
                    </div>
                  <?php else: ?>
                    <form
                      action="/users/register-from-google"
                      method="POST"
                      data-spa
                      data-spa-method="POST"
                      style="display: inline;">
                      <input type="hidden" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                      <input type="hidden" name="name" value="<?= htmlspecialchars($user['name']) ?>">
                      <button
                        type="submit"
                        class="btn-icon"
                        title="Daftarkan user ini sebagai Approver">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                          <circle cx="10" cy="8" r="4" />
                          <line x1="20" y1="8" x2="20" y2="14" />
                          <line x1="23" y1="11" x2="17" y2="11" />
                        </svg>
                      </button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['total_pages'] > 1): ?>
      <div class="pagination-container">
        <span class="body-small text-secondary">
          Showing <?= ($pagination['current_page'] - 1) * $pagination['per_page'] + 1 ?> to <?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total_items']) ?> of <?= $pagination['total_items'] ?> users
        </span>
        <div class="pagination-controls">
          <?php if ($pagination['has_prev']): ?>
            <a data-spa href="<?= $buildUrl(['page' => $pagination['current_page'] - 1]) ?>" class="btn-pagination">Previous</a>
          <?php else: ?>
            <button disabled class="btn-pagination disabled">Previous</button>
          <?php endif; ?>

          <?php if ($pagination['has_next']): ?>
            <a data-spa href="<?= $buildUrl(['page' => $pagination['current_page'] + 1]) ?>" class="btn-pagination">Next</a>
          <?php else: ?>
            <button disabled class="btn-pagination disabled">Next</button>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>