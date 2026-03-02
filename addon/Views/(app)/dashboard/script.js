// Di addon/Views/(app)/script.js
function initFullCalendar() {
    const calendarEl = document.getElementById('mazuCalendar');
    if (calendarEl && window.FullCalendar) {
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            events: (window.approvedAgendas || []).map(agenda => ({
                id: agenda.id,
                title: agenda.title,
                start: agenda.start_time,
                end: agenda.end_time,
                description: agenda.description,
                location: agenda.location
            }))
        });
        calendar.render();
    }
}

// Simpan data globally
window.approvedAgendas = [];

document.addEventListener('DOMContentLoaded', initFullCalendar);
window.addEventListener('spa:navigated', initFullCalendar);