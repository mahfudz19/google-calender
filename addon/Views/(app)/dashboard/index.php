<?php
// Fallback jika tidak ada data statistik
$stat = $stats ?? ['total' => 0, 'approved' => 0, 'pending' => 0];
?>

<div class="dashboard-header">
  <div>
    <h2 class="dash-title">Halo, <?= htmlspecialchars($_SESSION['user']['name'] ?? 'User') ?> 👋</h2>
    <p class="dash-subtitle">Berikut adalah ringkasan seluruh agenda Mazu yang sedang berjalan.</p>
  </div>
</div>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon bg-blue">📋</div>
    <div class="stat-data">
      <h3>Total Pengajuan</h3>
      <p class="stat-number"><?= $stat['total'] ?></p>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon bg-green">✅</div>
    <div class="stat-data">
      <h3>Agenda Disetujui</h3>
      <p class="stat-number"><?= $stat['approved'] ?></p>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon bg-orange">⏳</div>
    <div class="stat-data">
      <h3>Menunggu Persetujuan</h3>
      <p class="stat-number"><?= $stat['pending'] ?></p>
    </div>
  </div>
</div>

<div class="calendar-card">
  <div id="mazuCalendar"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>