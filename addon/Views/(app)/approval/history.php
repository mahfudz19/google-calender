<?php if (empty($approvals)): ?>
  <div class="empty-state">
    <div class="empty-icon">🎉</div>
    <h3>Semua bersih!</h3>
    <p>Tidak ada permintaan agenda yang perlu persetujuan saat ini.</p>
  </div>
<?php else: ?>
  <?php foreach ($approvals as $item):
    $startDate = new DateTime($item['start_time']);
    $endDate = new DateTime($item['end_time']);
    $dateStr = $startDate->format('d M Y');
    $timeStr = $startDate->format('H:i') . ' - ' . $endDate->format('H:i');
    $isHistory = $item['status'] !== 'pending';
  ?>
    <div class="approval-card" data-id="<?= $item['id'] ?>">
      <!-- Kolom Waktu (Kiri) -->
      <div class="card-time">
        <span class="date-day"><?= $startDate->format('d') ?></span>
        <span class="date-month"><?= $startDate->format('M') ?></span>
        <span class="date-year"><?= $startDate->format('Y') ?></span>
      </div>

      <!-- Kolom Detail (Tengah) -->
      <div class="card-details">
        <div class="detail-header">
          <span class="time-badge">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <?= $timeStr ?>
          </span>
          <?php if ($item['location']): ?>
            <span class="location-badge">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                <circle cx="12" cy="10" r="3"></circle>
              </svg>
              <?= htmlspecialchars($item['location']) ?>
            </span>
          <?php endif; ?>

          <?php if ($isHistory): ?>
            <span class="status-badge status-<?= $item['status'] ?>">
              <?= ucfirst($item['status']) ?>
            </span>
          <?php endif; ?>
        </div>

        <h3 class="event-title"><?= htmlspecialchars($item['title']) ?></h3>
        <p class="event-desc"><?= htmlspecialchars($item['description'] ?? '') ?></p>

        <div class="requester-info">
          <?php if ($item['requester_avatar']): ?>
            <img src="<?= $item['requester_avatar'] ?>" alt="Avatar" class="avatar">
          <?php endif; ?>
          <div class="requester-text">
            <span class="name"><?= htmlspecialchars($item['requester_name'] ?? 'Unknown') ?></span>
            <span class="role"><?= htmlspecialchars($item['requester_role'] ?? 'User') ?></span>
          </div>
        </div>
      </div>

      <!-- Kolom Aksi (Kanan) -->
      <?php if (!$isHistory): ?>
        <div class="card-actions">
          <form action="/approval/<?= $item['id'] ?>/reject" method="POST" data-spa onsubmit="return confirm('Tolak agenda ini?');">
            <button type="submit" class="btn-action btn-reject">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
              Tolak
            </button>
          </form>

          <form action="/approval/<?= $item['id'] ?>/approve" method="POST" data-spa onsubmit="return confirm('Setujui agenda ini?');">
            <button type="submit" class="btn-action btn-approve">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
              Setujui
            </button>
          </form>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>