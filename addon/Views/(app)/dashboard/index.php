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
      <p class="stat-number"><?= $total ?></p>
    </div>
  </div>
</div>

<div class="calendar-card">
  <div id="mazuCalendar"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
  // Inisialisasi FullCalendar
  const calendarEl = document.getElementById('mazuCalendar');
  if (calendarEl) {
    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,listWeek'
      },
      events: <?= json_encode($events) ?>
    });
    calendar.render();
  }
</script>