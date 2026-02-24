<?php

/**
 * View: Detail Agenda (Read Only)
 * Variabel: $agenda
 */
$start = new DateTime($agenda['start_time'] ?? '');
$end = new DateTime($agenda['end_time'] ?? '');
?>
<div style="max-width: 800px; margin: 0 auto; padding: 2rem 1rem;">
  <!-- Header & Navigasi -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <a href="<?= getBaseUrl('/agenda') ?>" style="text-decoration: none; color: #6b7280; display: flex; align-items: center; gap: 0.5rem; font-weight: 500;">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M19 12H5M12 19l-7-7 7-7" />
      </svg>
      Kembali
    </a>

    <?php if ($agenda && $agenda['status'] === 'pending'): ?>
      <div style="display: flex; gap: 0.5rem;">
        <a href="/agenda/<?= $agenda['id'] ?>/edit" style="background: white; color: #4f46e5; border: 1px solid #4f46e5; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.9rem;">
          Edit
        </a>
      </div>
    <?php endif; ?>
  </div>

  <!-- Main Card -->
  <div style="background: white; border-radius: 16px; border: 1px solid #e5e7eb; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);">

    <!-- Status Banner -->
    <div style="padding: 1rem 2rem; background: #f9fafb; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
      <span style="font-size: 0.9rem; color: #6b7280; font-weight: 500;">Status Pengajuan</span>
      <span class="status-badge status-<?= $agenda['status'] ?>" style="padding: 0.25rem 0.75rem; border-radius: 99px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; 
                <?php
                if ($agenda['status'] == 'approved') echo 'background:#dcfce7; color:#166534;';
                elseif ($agenda['status'] == 'rejected') echo 'background:#fee2e2; color:#991b1b;';
                else echo 'background:#ffedd5; color:#9a3412;';
                ?>">
        <?= $agenda ? ucfirst($agenda['status']) : 'Unknown' ?>
      </span>
    </div>

    <div style="padding: 2rem;">
      <!-- Judul -->
      <h1 style="margin: 0 0 1.5rem 0; font-size: 2rem; color: #111827; line-height: 1.2;"><?= htmlspecialchars($agenda['title'] ?? '') ?></h1>

      <!-- Grid Info -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
        <!-- Waktu -->
        <div>
          <h3 style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; margin-bottom: 0.5rem;">Waktu Pelaksanaan</h3>
          <div style="color: #111827; font-weight: 500;">
            <div style="font-size: 1.1rem; margin-bottom: 0.25rem;">
              <?= $start->format('l, d F Y') ?>
            </div>
            <div style="color: #4b5563;">
              <?= $start->format('H:i') ?> - <?= $end->format('H:i') ?> WIB
            </div>
          </div>
        </div>

        <!-- Lokasi -->
        <div>
          <h3 style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; margin-bottom: 0.5rem;">Lokasi</h3>
          <div style="display: flex; align-items: center; gap: 0.5rem; color: #111827; font-weight: 500; font-size: 1.1rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#4f46e5">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
              <circle cx="12" cy="10" r="3"></circle>
            </svg>
            <?= htmlspecialchars($agenda['location'] ?? '-') ?>
          </div>
        </div>
      </div>

      <!-- Deskripsi -->
      <?php if (!empty($agenda['description'])): ?>
        <div style="border-top: 1px solid #e5e7eb; padding-top: 2rem;">
          <h3 style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; margin-bottom: 1rem;">Deskripsi / Catatan</h3>
          <div style="color: #374151; line-height: 1.6; white-space: pre-wrap;"><?= htmlspecialchars($agenda['description']) ?></div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Footer Info User -->
    <div style="background: #f9fafb; padding: 1.5rem 2rem; border-top: 1px solid #e5e7eb; display: flex; align-items: center; gap: 1rem;">
      <div style="width: 40px; height: 40px; background: #e0e7ff; color: #4f46e5; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
        <?= strtoupper(substr($agenda['requester_name'] ?? 'U', 0, 1)) ?>
      </div>
      <div>
        <div style="font-weight: 600; color: #1f2937;"><?= htmlspecialchars($agenda['requester_name'] ?? 'Unknown') ?></div>
        <div style="font-size: 0.85rem; color: #6b7280;">Diajukan pada <?= date('d M Y, H:i', strtotime($agenda['created_at'] ?? 'now')) ?></div>
      </div>
    </div>

  </div>
</div>