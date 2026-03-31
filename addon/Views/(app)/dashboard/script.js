function initFullCalendar() {
  const calendarEl = document.getElementById("mazuCalendar");
  if (calendarEl && window.FullCalendar) {

    // Palet Warna Khas Google Workspace
    const gcalPalette = [
      '#4285F4', // 0: Blue (Default)
      '#0F9D58', // 1: Green
      '#DB4437', // 2: Red
      '#AB47BC', // 3: Purple
      '#00ACC1', // 4: Cyan
      '#FF7043', // 5: Orange
      '#9E9D24', // 6: Olive
      '#5C6BC0', // 7: Indigo
      '#F06292', // 8: Pink
      '#00796B'  // 9: Teal
    ];

    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: "dayGridMonth",
      height: "100%", 
      headerToolbar: {
        left: "today prev,next title", 
        center: "", 
        right: "dayGridMonth,timeGridWeek,timeGridDay,listWeek",
      },
      buttonText: {
        today: 'Hari ini', month: 'Bulan', week: 'Minggu', day: 'Hari', list: 'Agenda'
      },
      
      // 1. PETAKAN SELURUH DATA & TANGANI "ALL-DAY" SERTA WARNA DINAMIS
      events: (window.approvedAgendas || []).map((agenda) => {
        const isGlobal = agenda.is_global == 1 || agenda.is_global == '1' || agenda.is_global === true;
        
        let eventStart = agenda.start_time;
        let eventEnd = agenda.end_time;
        let isAllDay = isGlobal;

        // DETEKSI CERDAS: Jika bukan global, tapi event multi-hari yang diinput dari jam 00:00
        // Kita ubah paksa menjadi "All-Day" agar teks "12a" hilang dan menjadi blok rapi.
        if (!isGlobal && agenda.start_time && agenda.start_time.includes('00:00:00')) {
          const startDateStr = agenda.start_time.split(' ')[0];
          const endDateStr = agenda.end_time ? agenda.end_time.split(' ')[0] : startDateStr;
          
          if (startDateStr !== endDateStr) {
            isAllDay = true;
            eventStart = startDateStr;
            
            // Aturan FullCalendar: End Date harus +1 hari (Exclusive)
            const endDateObj = new Date(agenda.end_time.replace(' ', 'T'));
            endDateObj.setDate(endDateObj.getDate() + 1);
            eventEnd = endDateObj.toISOString().split('T')[0];
          }
        }

        // LOGIKA WARNA BARU (HASHING): Agar warna selalu unik meski ID ruangan sama / kosong
        const seedString = agenda.ruangan_id ? String(agenda.ruangan_id) : String(agenda.title);
        let hash = 0;
        for (let i = 0; i < seedString.length; i++) {
            hash = seedString.charCodeAt(i) + ((hash << 5) - hash);
        }
        // Ambil angka absolut dari hash, lalu mod dengan jumlah warna di palet kita (10)
        const colorIndex = Math.abs(hash) % gcalPalette.length;
        
        let eventColor = gcalPalette[colorIndex];
        let eventTextColor = '#ffffff'; // Teks putih untuk background warna-warni

        // KONDISI KHUSUS: KALENDER AKADEMIK
        if (isGlobal) {
          eventStart = agenda.start_time.split(' ')[0];
          if (agenda.end_time) {
            const endDateObj = new Date(agenda.end_time.replace(' ', 'T'));
            endDateObj.setDate(endDateObj.getDate() + 1);
            eventEnd = endDateObj.toISOString().split('T')[0];
          }
          eventColor = '#fbbc04'; // Kuning mencolok
          eventTextColor = '#202124'; // Teks gelap agar kontras
        }

        return {
          id: agenda.id,
          title: agenda.title,
          start: eventStart,
          end: eventEnd,
          allDay: isAllDay,
          color: eventColor,
          textColor: eventTextColor,
          extendedProps: agenda
        };
      }),
      
      eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: false },
      dayMaxEvents: true,

      // 2. TAMBAHKAN EVENT CLICK (MODAL CERDAS)
      eventClick: function(info) {
        info.jsEvent.preventDefault();
        const data = info.event.extendedProps;
        
        document.getElementById('modal-ev-title').textContent = data.title;
        
        const start = new Date((data.start_time || "").replace(' ', 'T'));
        const end = new Date((data.end_time || "").replace(' ', 'T'));
        
        const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const timeOptions = { hour: '2-digit', minute: '2-digit' };
        
        const isGlobal = data.is_global == 1 || data.is_global == '1' || data.is_global === true;

        if (!isNaN(start.getTime())) {
          const startDateStr = start.toLocaleDateString('id-ID', dateOptions);
          const endDateStr = end.toLocaleDateString('id-ID', dateOptions);
          const timeStrStart = start.toLocaleTimeString('id-ID', timeOptions).replace(':', '.');
          const timeStrEnd = end.toLocaleTimeString('id-ID', timeOptions).replace(':', '.');
          
          let displayDate = startDateStr;
          let displayTime = `${timeStrStart} – ${timeStrEnd}`;

          if (startDateStr !== endDateStr) {
            displayDate = `${startDateStr} – ${endDateStr}`;
          }

          if (isGlobal) {
            displayTime = "Sepanjang Hari (All-Day)";
          }
          
          document.getElementById('modal-ev-time').innerHTML = `
            <div class="ds-modal-text">${displayDate}</div>
            <div class="ds-modal-subtext">${displayTime}</div>
          `;
        }
        
        let locText = "";
        if (isGlobal) {
           locText = `<div class="ds-modal-text" style="color: var(--primary-main); font-weight: 600;">🌍 Berlaku Global (Seluruh Kampus)</div>`;
        } else {
            if (data.ruangan_name) {
              locText += `<div class="ds-modal-text">${data.ruangan_name} ${data.ruangan_capacity ? `<span class="ds-modal-subtext">(${data.ruangan_capacity} org)</span>` : ''}</div>`;
            }
            if (data.location) {
              const link = data.location.startsWith('http') ? data.location : 'https://' + data.location;
              locText += `<div><a href="${link}" target="_blank" style="color: #1a73e8; text-decoration: underline; font-size: 14px;">${data.location}</a></div>`;
            }
            if (!locText) locText = '<div class="ds-modal-subtext">Tidak ada lokasi</div>';
        }
        document.getElementById('modal-ev-location').innerHTML = locText;

        const avatarStr = data.requester_avatar 
          ? `<img src="${data.requester_avatar}" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">` 
          : `<div style="width: 32px; height: 32px; border-radius: 50%; background: var(--bg-default); display: flex; align-items: center; justify-content: center; font-weight: bold; color: var(--text-secondary);">${(data.requester_name || 'U').charAt(0).toUpperCase()}</div>`;
        
        document.getElementById('modal-ev-requester').innerHTML = `
          <div style="display: flex; align-items: center; gap: 12px;">
            ${avatarStr}
            <div>
              <div class="ds-modal-text">${data.requester_name || 'User'}</div>
              <div class="ds-modal-subtext" style="text-transform: capitalize;">${(data.requester_email || '').toLowerCase()} • ${data.requester_role || 'User'}</div>
            </div>
          </div>
        `;

        document.getElementById('modal-ev-desc').textContent = data.description || "Tidak ada deskripsi.";
        
        // PENTING: Set warna titik ikon di modal agar sesuai dengan warna event-nya!
        const modalIconDot = document.querySelector('.ds-modal-icon div');
        if (modalIconDot) {
          modalIconDot.style.backgroundColor = info.event.backgroundColor;
        }

        document.getElementById('modal-event-detail').classList.add('show');
      }
    });
    calendar.render();
  }
}

window.approvedAgendas = window.approvedAgendas || [];

document.addEventListener("DOMContentLoaded", initFullCalendar);
window.addEventListener("spa:navigated", initFullCalendar);