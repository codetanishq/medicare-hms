

      document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar')
        const calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridWeek'
        })
        calendar.render()
      })

