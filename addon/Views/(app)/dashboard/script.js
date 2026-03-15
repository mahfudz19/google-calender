function initFullCalendar() {
  const calendarEl = document.getElementById("mazuCalendar");
  if (calendarEl && window.FullCalendar) {
    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: "dayGridMonth",
      height: "100%", 
      headerToolbar: {
        // Susunan GCal sebenar: Butang dan Tajuk Bulan disatukan di sebelah kiri
        left: "today prev,next title", 
        center: "", 
        right: "dayGridMonth,timeGridWeek,timeGridDay,listWeek",
      },
      buttonText: {
        today: 'Hari ini',
        month: 'Bulan',
        week: 'Minggu',
        day: 'Hari',
        list: 'Agenda'
      },
      events: (window.approvedAgendas || []).map((agenda) => ({
        id: agenda.id,
        title: agenda.title,
        start: agenda.start_time,
        end: agenda.end_time,
        description: agenda.description,
        location: agenda.location,
        // Gunakan properti 'color' biasa agar kalendar memaparkan ikon 'titik' (dot) 
        // secara automatik untuk acara yang mempunyai jam.
        color: 'var(--primary-main)' 
      })),
      eventTimeFormat: {
        hour: '2-digit',
        minute: '2-digit',
        meridiem: false
      },
      dayMaxEvents: true
    });
    calendar.render();
  }
}

window.approvedAgendas = window.approvedAgendas || [];

document.addEventListener("DOMContentLoaded", initFullCalendar);
window.addEventListener("spa:navigated", initFullCalendar);