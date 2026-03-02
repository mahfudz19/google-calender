<?php $agenda = $agenda ?? []; ?>

<div class="agenda-container">
  <a href="/agenda" class="btn-back">← Kembali ke Daftar</a>

  <div class="detail-card">
    <div class="detail-header">
      <div class="detail-title-group">
        <h2><?= htmlspecialchars($agenda['title']) ?></h2>
        <?php
        $badge = 'badge-orange';
        $label = 'Menunggu Persetujuan';
        if ($agenda['status'] === 'approved') {
          $badge = 'badge-green';
          $label = 'Disetujui';
        } elseif ($agenda['status'] === 'rejected') {
          $badge = 'badge-red';
          $label = 'Ditolak';
        }
        ?>
        <span class="badge <?= $badge ?> detail-badge"><?= $label ?></span>
      </div>

      <?php if ($agenda['status'] === 'pending'): ?>
        <div class="detail-actions">
          <a href="/agenda/<?= $agenda['id'] ?>/edit" class="btn-outline-primary">✎ Edit</a>

          <button type="button" class="btn-outline-danger" onclick="document.getElementById('modal-cancel-<?= $agenda['id'] ?>').classList.add('show')">Cancel</button>
          <div id="modal-cancel-<?= $agenda['id'] ?>" class="css-modal">
            <div class="modal-overlay" onclick="this.parentElement.classList.remove('show')"></div>

            <div class="modal-content">
              <div class="modal-header">
                <h3 class="modal-title text-danger">Konfirmasi Pembatalan</h3>
                <button type="button" class="modal-close" onclick="document.getElementById('modal-cancel-<?= $agenda['id'] ?>').classList.remove('show')">&times;</button>
              </div>
              <div class="modal-body">
                <p>Apakah Anda yakin ingin membatalkan agenda <strong><?= htmlspecialchars($agenda['title']) ?></strong>?</p>
                <p class="text-muted" style="margin-top: 0.5rem; font-size: 0.85rem;">Agenda yang dibatalkan tidak dapat dipulihkan kembali.</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="document.getElementById('modal-cancel-<?= $agenda['id'] ?>').classList.remove('show')">Batal</button>
                <form action="/agenda/<?= $agenda['id'] ?>/cancel" method="post" data-spa style="margin:0;">
                  <button type="submit" class="btn-confirm danger">Ya, Batalkan Agenda</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="detail-body">
      <div class="info-grid">
        <div class="info-box">
          <span class="info-label">Waktu Mulai</span>
          <span class="info-value"><?= date('d F Y • H:i', strtotime($agenda['start_time'])) ?></span>
        </div>
        <div class="info-box">
          <span class="info-label">Waktu Selesai</span>
          <span class="info-value"><?= date('d F Y • H:i', strtotime($agenda['end_time'])) ?></span>
        </div>
        <div class="info-box full-width">
          <span class="info-label">Lokasi / Tempat</span>
          <span class="info-value">📍 <?= htmlspecialchars($agenda['location'] ?? 'Tidak ditentukan') ?></span>
        </div>
      </div>

      <div class="detail-description">
        <span class="info-label">Deskripsi Agenda</span>
        <p class="desc-text">
          <?= nl2br(htmlspecialchars($agenda['description'] ?? 'Tidak ada deskripsi yang dilampirkan.')) ?>
        </p>
      </div>
    </div>

  </div>
</div>