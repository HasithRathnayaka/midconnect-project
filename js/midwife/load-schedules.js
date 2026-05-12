document.addEventListener('DOMContentLoaded', function () {
    loadSchedules();
});

function loadSchedules() {
    const scheduleTableBody = document.getElementById('scheduleTableBody');

    if (!scheduleTableBody) {
        return;
    }

    fetch('../php/midwife/get_schedules.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                scheduleTableBody.innerHTML = `
                    <tr>
                        <td colspan="6">${data.message || 'Failed to load schedules'}</td>
                    </tr>
                `;
                return;
            }

            renderSchedules(data.schedules || []);
        })
        .catch(error => {
            console.error('Schedule Load Error:', error);

            scheduleTableBody.innerHTML = `
                <tr>
                    <td colspan="6">Server error while loading schedules.</td>
                </tr>
            `;
        });
}

function renderSchedules(schedules) {
    const scheduleTableBody = document.getElementById('scheduleTableBody');
    const selectedAreaTitle = document.getElementById('selectedAreaTitle');
    const selectedAreaInfo = document.getElementById('selectedAreaInfo');

    if (!scheduleTableBody) {
        return;
    }

    const activeAreaBtn = document.querySelector('.duty-area-btn.active');
    const selectedArea = activeAreaBtn ? activeAreaBtn.dataset.area : 'all';

    let filteredSchedules = schedules;

    if (selectedArea !== 'all') {
        filteredSchedules = schedules.filter(item => {
            return (item.duty_area || '').toLowerCase() === selectedArea.toLowerCase();
        });
    }

    if (selectedAreaTitle) {
        selectedAreaTitle.textContent = selectedArea === 'all'
            ? 'All Area Schedule'
            : selectedArea + ' Area Schedule';
    }

    if (selectedAreaInfo) {
        selectedAreaInfo.textContent = filteredSchedules.length + ' schedule item(s)';
    }

    if (filteredSchedules.length === 0) {
        scheduleTableBody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align:center;">No schedule items found.</td>
            </tr>
        `;
        return;
    }

    scheduleTableBody.innerHTML = filteredSchedules.map(item => {
        const time = formatTime(item.start_time);
        const activity = escapeHtml(item.description || item.type_name || '-');
        const location = escapeHtml(item.location || '-');
        const status = escapeHtml(item.status || 'scheduled');
        const priority = escapeHtml(item.priority_level || 'normal');
        const patientName = item.patient_name ? `<br><small>Patient: ${escapeHtml(item.patient_name)}</small>` : '';

        return `
            <tr>
                <td>${time}</td>
                <td>
                    ${activity}
                    ${patientName}
                    <br><small>${escapeHtml(item.type_name || '')}</small>
                </td>
                <td>${location}</td>
                <td>
                    <span class="status-badge status-active">${status}</span>
                    <br><small>${priority}</small>
                </td>
                <td>
                    ${item.status === 'completed'
                        ? '<span class="status-badge status-active">Completed</span>'
                        : `<button class="btn btn-success btn-sm" onclick="completeSchedule(${item.schedule_id})">Complete</button>`
                    }
                </td>
            </tr>
        `;
    }).join('');
}

function switchScheduleArea(area) {
    document.querySelectorAll('.duty-area-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    const selectedBtn = document.querySelector(`.duty-area-btn[data-area="${area}"]`);

    if (selectedBtn) {
        selectedBtn.classList.add('active');
    }

    loadSchedules();
}

function completeSchedule(scheduleId) {
    if (!confirm('Are you sure you want to complete this schedule item?')) {
        return;
    }

    const formData = new FormData();
    formData.append('schedule_id', scheduleId);

    fetch('../php/midwife/complete_schedule.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            alert(data.message);

            if (data.success) {
                loadSchedules();
            }
        })
        .catch(error => {
            console.error('Complete Schedule Error:', error);
            alert('Server error while completing schedule item.');
        });
}

function formatTime(timeString) {
    if (!timeString) return '-';

    return timeString.substring(0, 5);
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}