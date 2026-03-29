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
        extendedProps: agenda,
        color: 'var(--primary-main)'
      })),
      
      eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: false },
      dayMaxEvents: true,

      eventClick: function(info) {
        info.jsEvent.preventDefault();
        const data = info.event.extendedProps;
        
        document.getElementById('modal-ev-title').textContent = data.title;
        
        const start = new Date((data.start_time || "").replace(' ', 'T'));
        const end = new Date((data.end_time || "").replace(' ', 'T'));
        
        const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const timeOptions = { hour: '2-digit', minute: '2-digit' };
        
        if (!isNaN(start.getTime())) {
          const dateStr = start.toLocaleDateString('id-ID', dateOptions);
          const timeStr = start.toLocaleTimeString('id-ID', timeOptions) + ' – ' + end.toLocaleTimeString('id-ID', timeOptions);
          document.getElementById('modal-ev-time').innerHTML = `${dateStr}<br><span style="color: var(--text-secondary);">${timeStr}</span>`;
        }
        
        let locText = "";
        if (data.ruangan_name) locText += `<div style="font-weight: 500;">${data.ruangan_name} ${data.ruangan_capacity ? `<span style="color: var(--text-secondary); font-weight: 400;">(${data.ruangan_capacity} org)</span>` : ''}</div>`;
        if (data.location) locText += `<div>${data.location}</div>`;
        document.getElementById('modal-ev-location').innerHTML = locText || '<span style="color: var(--text-disabled);">Tidak ada lokasi</span>';

        const avatarStr = data.requester_avatar 
          ? `<img src="${data.requester_avatar}" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover;">` 
          : `<div style="width: 28px; height: 28px; border-radius: 50%; background: var(--bg-default); display: flex; align-items: center; justify-content: center; font-weight: bold; color: var(--text-secondary);">${(data.requester_name || 'U').charAt(0).toUpperCase()}</div>`;
        
        document.getElementById('modal-ev-requester').innerHTML = `
          <div style="display: flex; align-items: center; gap: 12px;">
            ${avatarStr}
            <div>
              <div style="font-size: 14px; color: var(--text-primary);">${data.requester_name || 'User'}</div>
              <div style="font-size: 12px; color: var(--text-secondary); text-transform: capitalize;">${data.requester_email || ''} • ${data.requester_role || 'User'}</div>
            </div>
          </div>
        `;

        document.getElementById('modal-ev-desc').textContent = data.description || "Tidak ada deskripsi.";
        
        document.getElementById('modal-event-detail').classList.add('show');
      }
    });
    calendar.render();
  }
}

window.approvedAgendas = window.approvedAgendas || [];

document.addEventListener("DOMContentLoaded", initFullCalendar);
window.addEventListener("spa:navigated", initFullCalendar);