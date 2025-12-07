/**
 * Restaurant Booking System - Booking Page JavaScript
 */

// Step 1: Table selection
function selectTable(tableId, tableType) {
    window.location.href = `booking.php?step=2&table=${tableId}`;
}

// Step 2: Calendar initialization
function initCalendar() {
    const calendar = document.getElementById('calendar');
    if (!calendar) return;
    
    // Get configuration from data attributes
    const reservedDates = JSON.parse(calendar.dataset.reservedDates || '[]');
    const selectedTable = parseInt(calendar.dataset.selectedTable || '0');
    
    const today = new Date();
    const currentMonth = today.getMonth();
    const currentYear = today.getFullYear();
    
    let month = currentMonth;
    let year = currentYear;
    
    function renderCalendar() {
        calendar.innerHTML = '';
        
        // Month header
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];
        
        const header = document.createElement('div');
        header.className = 'calendar-header';
        header.innerHTML = `
            <button class="calendar-nav" onclick="changeMonth(-1)">←</button>
            <h3>${monthNames[month]} ${year}</h3>
            <button class="calendar-nav" onclick="changeMonth(1)">→</button>
        `;
        calendar.appendChild(header);
        
        // Weekday headers
        const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        const weekdayRow = document.createElement('div');
        weekdayRow.className = 'calendar-weekdays';
        weekdays.forEach(day => {
            const dayEl = document.createElement('div');
            dayEl.className = 'calendar-weekday';
            dayEl.textContent = day;
            weekdayRow.appendChild(dayEl);
        });
        calendar.appendChild(weekdayRow);
        
        // Days grid
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysGrid = document.createElement('div');
        daysGrid.className = 'calendar-days';
        
        // Empty cells for days before month starts
        for (let i = 0; i < firstDay; i++) {
            const empty = document.createElement('div');
            empty.className = 'calendar-day empty';
            daysGrid.appendChild(empty);
        }
        
        // Days of the month
        for (let day = 1; day <= daysInMonth; day++) {
            const dayEl = document.createElement('div');
            dayEl.className = 'calendar-day';
            
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const dateObj = new Date(year, month, day);
            const isPast = dateObj < new Date(today.getFullYear(), today.getMonth(), today.getDate());
            const isReserved = reservedDates.includes(dateStr);
            
            if (isPast) {
                dayEl.className += ' past';
                dayEl.textContent = day;
            } else if (isReserved) {
                dayEl.className += ' reserved';
                dayEl.textContent = day;
                dayEl.title = 'This date is already reserved';
            } else {
                dayEl.className += ' available';
                dayEl.textContent = day;
                dayEl.onclick = () => selectDate(dateStr);
            }
            
            daysGrid.appendChild(dayEl);
        }
        
        calendar.appendChild(daysGrid);
    }
    
    window.changeMonth = (direction) => {
        month += direction;
        if (month < 0) {
            month = 11;
            year--;
        } else if (month > 11) {
            month = 0;
            year++;
        }
        renderCalendar();
    };
    
    window.selectDate = (dateStr) => {
        window.location.href = `booking.php?step=3&table=${selectedTable}&date=${dateStr}`;
    };
    
    renderCalendar();
}

// Initialize calendar when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const currentStep = document.body.dataset.currentStep;
    
    if (currentStep == '2') {
        initCalendar();
    }
});

