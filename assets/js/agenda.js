document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('fcw-calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        editable: true,
        events: {
            url: fcw_turnos.ajaxurl,
            method: 'POST',
            extraParams: {
                action: 'fcw_get_turnos',
                nonce: fcw_turnos.nonce
            }
        },
        eventDrop: function(info) {
            const id = info.event.id;
            const newDate = info.event.start.toISOString().slice(0, 19).replace('T', ' ');
            fetch(fcw_turnos.ajaxurl, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({
                    action: 'fcw_save_turno',
                    nonce: fcw_turnos.nonce,
                    id: id,
                    fecha_turno: newDate,
                    cliente_id: info.event.extendedProps.cliente_id,
                    nota: info.event.extendedProps.nota
                })
            }).then(() => calendar.refetchEvents());
        }
    });
    calendar.render();
});
