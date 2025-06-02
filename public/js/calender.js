document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing FullCalendar v6.1.17...');
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        plugins: ['dayGrid', 'interaction', 'bootstrap5'],
        initialView: 'dayGridMonth',
        timeZone: 'local',
        themeSystem: 'bootstrap5',
        editable: true,
        selectable: true,
        events: '/appointment/fetch-events',
        select: function(info) {
            $('#event_start_date').val(moment(info.start).format('YYYY-MM-DDTHH:mm'));
            $('#event_entry_modal').modal('show');
        },
        eventClick: function(info) {
            alert('Appointment ID: ' + info.event.extendedProps.event_id);
        }
    });
    calendar.render();

    // Form submission
    $('#event_entry_form').on('submit', function(e) {
        e.preventDefault();
        var eventData = {
            patient_id: $('#patient_id').val(),
            doctor_id: $('#doctor_id').val(),
            start_date: $('#event_start_date').val()
        };
        $.ajax({
            url: '/appointment/save-event',
            type: 'POST',
            dataType: 'json',
            data: eventData,
            success: function(response) {
                $('#event_entry_modal').modal('hide');
                if (response.status) {
                    alert(response.msg);
                    calendar.refetchEvents();
                } else {
                    alert('Error: ' + response.msg);
                }
            },
            error: function(xhr) {
                alert('AJAX error: ' + xhr.statusText);
            }
        });
    });
});