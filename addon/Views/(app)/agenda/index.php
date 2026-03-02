<?php
$agendas = $agendas ?? [];
$currentStatus = $currentStatus ?? null;
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$totalAgendas = $totalAgendas ?? 0;
?>

<div class="agenda-container">

  <div class="page-header">
    <div>
      <h2 class="page-title">Agenda Saya</h2>
      <p class="page-subtitle">Kelola dan pantau status pengajuan agenda akademik Anda.</p>
    </div>
    <a data-spa href="/agenda/create" class="btn-primary">+ Ajukan Agenda</a>
  </div>

  <div class="filter-tabs">
    <a data-spa href="/agenda" class="tab-item <?= $currentStatus === null ? 'active' : '' ?>">Semua</a>
    <a data-spa href="/agenda?status=pending" class="tab-item <?= $currentStatus === 'pending' ? 'active' : '' ?>">⏳ Menunggu</a>
    <a data-spa href="/agenda?status=approved" class="tab-item <?= $currentStatus === 'approved' ? 'active' : '' ?>">✅ Disetujui</a>
    <a data-spa href="/agenda?status=rejected" class="tab-item <?= $currentStatus === 'rejected' ? 'active' : '' ?>">❌ Ditolak</a>
  </div>

  <div class="agenda-card">
    <?php if (empty($agendas)): ?>
      <div class="empty-state">
        <span class="empty-icon">📂</span>
        <h3>Belum ada agenda</h3>
        <p>Anda belum memiliki pengajuan agenda di kategori ini.</p>
      </div>
    <?php else: ?>
      <div class="agenda-list">
        <?php foreach ($agendas as $agenda): ?>
          <div class="agenda-item">
            <div class="item-main">
              <h3 class="item-title"><?= htmlspecialchars($agenda['title']) ?></h3>
              <div class="item-meta">
                <span>📅 <?= date('d M Y, H:i', strtotime($agenda['start_time'])) ?></span>
                <span>📍 <?= htmlspecialchars($agenda['location'] ?? 'Virtual') ?></span>
              </div>
            </div>
            <div class="item-action">
              <?php
              $badge = 'badge-orange';
              $label = 'Pending';
              if ($agenda['status'] === 'approved') {
                $badge = 'badge-green';
                $label = 'Approved';
              } elseif ($agenda['status'] === 'rejected') {
                $badge = 'badge-red';
                $label = 'Rejected';
              }
              ?>
              <span class="badge <?= $badge ?>"><?= $label ?></span>

              <?php if ($agenda['status'] === 'pending'): ?>
                <a data-spa href="/agenda/<?= $agenda['id'] ?>/edit" class="btn-outline-small">Edit</a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($totalPages > 1): ?>
    <div class="pagination-wrap">
      <span class="page-info">Menampilkan <?= count($agendas) ?> dari <?= $totalAgendas ?> agenda</span>
      <div class="pagination">
        <?php
        $prevDisabled = $currentPage <= 1 ? 'disabled' : '';
        $nextDisabled = $currentPage >= $totalPages ? 'disabled' : '';
        $statusQuery = $currentStatus ? "&status={$currentStatus}" : "";
        ?>
        <a data-spa href="/agenda?page=<?= $currentPage - 1 ?><?= $statusQuery ?>" class="btn-page <?= $prevDisabled ?>">« Prev</a>
        <span class="page-current">Hal <?= $currentPage ?> / <?= $totalPages ?></span>
        <a data-spa href="/agenda?page=<?= $currentPage + 1 ?><?= $statusQuery ?>" class="btn-page <?= $nextDisabled ?>">Next »</a>
      </div>
    </div>
  <?php endif; ?>

</div>