<?php
$user = $_SESSION['user'] ?? [];

/**
 * Dashboard view default (Hybrid Layout)
 * Variabel tersedia:
 * - $user: array data user dari session
 * - $role: string role user
 * - $agendas: array daftar agenda (nanti dari controller)
 */

// Simulasi data agenda untuk UI preview (jika controller belum kirim data)
$agendas = $agendas ?? [
  [
    'id' => 1,
    'title' => 'Rapat Koordinasi Mingguan',
    'start_time' => date('Y-m-d 09:00:00'),
    'end_time' => date('Y-m-d 11:00:00'),
    'location' => 'Ruang Meeting Utama',
    'requester_name' => 'Budi Santoso',
    'type' => 'Rapat'
  ],
  [
    'id' => 2,
    'title' => 'Workshop UI/UX Design',
    'start_time' => date('Y-m-d 13:00:00', strtotime('+1 day')),
    'end_time' => date('Y-m-d 16:00:00', strtotime('+1 day')),
    'location' => 'Creative Lab',
    'requester_name' => 'Siti Aminah',
    'type' => 'Workshop'
  ]
];

// Helper untuk kalender mini
$currentMonth = date('F Y');
$daysInMonth = date('t');
$firstDayOfWeek = date('w', strtotime(date('Y-m-01'))); // 0 (Sun) - 6 (Sat)
?>

<div class="dashboard-container">
  <!-- SIDEBAR KIRI -->
  <aside class="dashboard-sidebar">
    <!-- User Info & CTA -->
    <div class="user-welcome">
      <div class="user-avatar">
        <?= strtoupper(substr($user['name'] ?? 'G', 0, 1)) ?>
      </div>
      <h3 style="margin:0; font-size:1.1rem;"><?= htmlspecialchars($user['name'] ?? 'Guest') ?></h3>
      <p style="margin:0.25rem 0 1rem; color:#6b7280; font-size:0.9rem;"><?= htmlspecialchars($user['email'] ?? '') ?></p>

      <a data-spa href="agenda/create" class="btn-create">
        + Ajukan Agenda
      </a>
      <div style="margin-top: 0.5rem; text-align: center;">
        <a data-spa href="agenda" style="font-size: 0.85rem; color: #4f46e5; text-decoration: none;">Lihat Pengajuan Saya &rarr;</a>
      </div>
    </div>

    <!-- Mini Calendar Widget -->
    <div class="mini-calendar">
      <div class="calendar-header"><?= $currentMonth ?></div>
      <div class="calendar-grid">
        <div class="cal-day-name">M</div>
        <div class="cal-day-name">S</div>
        <div class="cal-day-name">S</div>
        <div class="cal-day-name">R</div>
        <div class="cal-day-name">K</div>
        <div class="cal-day-name">J</div>
        <div class="cal-day-name">S</div>

        <!-- Empty cells for start of month -->
        <?php for ($i = 0; $i < $firstDayOfWeek; $i++): ?>
          <div class="cal-day empty"></div>
        <?php endfor; ?>

        <!-- Days -->
        <?php
        $today = date('j');
        for ($d = 1; $d <= $daysInMonth; $d++):
          $isToday = ($d == $today);
        ?>
          <div class="cal-day <?= $isToday ? 'today' : '' ?>"><?= $d ?></div>
        <?php endfor; ?>
      </div>
    </div>
  </aside>

  <!-- MAIN CONTENT KANAN -->
  <main class="dashboard-main">
    <div class="section-title">
      Agenda Mendatang
      <span style="font-size: 0.875rem; font-weight: normal; color: #6b7280;">
        <?= date('d M Y') ?>
      </span>
    </div>

    <div class="agenda-list">
      <?php if (empty($agendas)): ?>
        <div class="empty-agenda">
          <p>Belum ada agenda yang dijadwalkan dalam waktu dekat.</p>
        </div>
      <?php else: ?>
        <?php foreach ($agendas as $agenda):
          $start = new DateTime($agenda['start_time']);
          $end = new DateTime($agenda['end_time']);
        ?>
          <div class="agenda-card">
            <div class="agenda-date">
              <span class="date-day"><?= $start->format('d') ?></span>
              <span class="date-month"><?= $start->format('M') ?></span>
            </div>
            <div class="agenda-content">
              <div class="agenda-time">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <?= $start->format('H:i') ?> - <?= $end->format('H:i') ?>
              </div>
              <h4 class="agenda-title"><?= htmlspecialchars($agenda['title']) ?></h4>
              <div class="agenda-meta">
                <div class="meta-item">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                  </svg>
                  <?= htmlspecialchars($agenda['location']) ?>
                </div>
                <div class="meta-item">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                  </svg>
                  <?= htmlspecialchars($agenda['requester_name']) ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>
</div>