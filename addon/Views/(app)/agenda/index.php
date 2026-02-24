<div class="my-agenda-container">
  <header class="page-header">
    <h1 class="page-title">Pengajuan Saya</h1>
    <a data-spa href="/agenda/create" class="btn-add">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="5" x2="12" y2="19"></line>
        <line x1="5" y1="12" x2="19" y2="12"></line>
      </svg>
      Buat Agenda Baru
    </a>
  </header>

  <div class="filter-tabs">
    <a data-spa href="#" class="tab-item active">Semua</a>
    <a data-spa href="#" class="tab-item">Pending</a>
    <a data-spa href="#" class="tab-item">Disetujui</a>
    <a data-spa href="#" class="tab-item">Ditolak</a>
  </div>

  <div class="agenda-stack">
    <?php if (empty($myAgendas)): ?>
      <div class="empty-state">
        <span class="empty-icon">📭</span>
        <h3>Belum ada pengajuan</h3>
        <p>Anda belum pernah mengajukan agenda apapun. Mulai dengan membuat baru!</p>
      </div>
    <?php else: ?>
      <?php foreach ($myAgendas as $item):
        $start = new DateTime($item['start_time']);
        $end = new DateTime($item['end_time']);
        $isPending = $item['status'] === 'pending';
      ?>
        <div class="agenda-card">
          <!-- Tanggal -->
          <div class="card-date">
            <span class="date-d"><?= $start->format('d') ?></span>
            <span class="date-m"><?= $start->format('M') ?></span>
          </div>

          <!-- Info Utama -->
          <div class="card-info">
            <h3 class="card-title"><?= htmlspecialchars($item['title']) ?></h3>
            <div class="card-meta">
              <div class="meta-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <?= $start->format('H:i') ?> - <?= $end->format('H:i') ?>
              </div>
              <div class="meta-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                  <circle cx="12" cy="10" r="3"></circle>
                </svg>
                <?= htmlspecialchars($item['location']) ?>
              </div>
            </div>
          </div>

          <!-- Status & Aksi -->
          <div class="card-actions">
            <span class="status-badge status-<?= $item['status'] ?>">
              <?= ucfirst($item['status']) ?>
            </span>

            <?php if ($isPending): ?>
              <div class="action-group">
                <a data-spa href="/agenda/<?= $item['id'] ?>/edit" class="btn-icon" title="Edit Pengajuan">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                  </svg>
                </a>
                <form action="/agenda/<?= $item['id'] ?>/cancel" method="POST" data-spa onsubmit="return confirm('Batalkan pengajuan ini?');" style="margin:0;">
                  <button type="submit" class="btn-icon delete" title="Batalkan Pengajuan">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="3 6 5 6 21 6"></polyline>
                      <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                  </button>
                </form>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>