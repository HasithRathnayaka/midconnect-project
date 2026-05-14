document.addEventListener('DOMContentLoaded', function () {
    loadDashboardWidgets();
});

function loadDashboardWidgets() {
    fetch('../php/midwife/get_dashboard_widgets.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.text();
        })
        .then(function (text) {
            console.log('Dashboard widgets raw response:', text);

            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error('Dashboard widgets returned non-JSON:', text);
                setDashboardWidgetError('urgentMeetingsList', 'Unable to load urgent meetings.');
                setDashboardWidgetError('upcomingClinicsList', 'Unable to load upcoming clinics.');
                setDashboardWidgetError('todayTimetableList', 'Unable to load today timetable.');
                return;
            }

            if (!data.success) {
                console.error(data.message || 'Dashboard widget loading failed.');
                setDashboardWidgetError('urgentMeetingsList', data.message || 'Unable to load urgent meetings.');
                setDashboardWidgetError('upcomingClinicsList', data.message || 'Unable to load upcoming clinics.');
                setDashboardWidgetError('todayTimetableList', data.message || 'Unable to load today timetable.');
                return;
            }

            renderUrgentMeetings(data.urgent_meetings || []);
            renderUpcomingClinics(data.upcoming_clinics || []);
            renderTodayTimetable(data.todays_timetable || []);

            updateDashboardGreetingCount(data.summary || {});
        })
        .catch(function (error) {
            console.error('Dashboard widgets load error:', error);
            setDashboardWidgetError('urgentMeetingsList', 'Server error while loading urgent meetings.');
            setDashboardWidgetError('upcomingClinicsList', 'Server error while loading upcoming clinics.');
            setDashboardWidgetError('todayTimetableList', 'Server error while loading today timetable.');
        });
}

function renderUrgentMeetings(records) {
    const container = document.getElementById('urgentMeetingsList');
    if (!container) return;

    if (records.length === 0) {
        container.innerHTML = `
            <p class="text-muted">
                No urgent meetings found.
            </p>
        `;
        return;
    }

    container.innerHTML = records.map(function (record) {
        return `
            <div class="dashboard-widget-item urgent-widget-item">
                <div class="widget-item-title">
                    ${escapeDashboardHtml(record.type_name || 'Urgent Meeting')}
                </div>

                <div class="widget-item-meta">
                    <i class="fas fa-calendar-alt"></i>
                    ${formatDashboardDate(record.scheduled_date)}
                    &nbsp; | &nbsp;
                    <i class="fas fa-clock"></i>
                    ${formatDashboardTime(record.start_time)}
                </div>

                <div class="widget-item-meta">
                    <i class="fas fa-map-marker-alt"></i>
                    ${escapeDashboardHtml(record.location || '-')}
                </div>

                <span class="status-badge status-danger">
                    ${escapeDashboardHtml(record.priority_level || 'Urgent')}
                </span>
            </div>
        `;
    }).join('');
}

function renderUpcomingClinics(records) {
    const container = document.getElementById('upcomingClinicsList');
    if (!container) return;

    if (records.length === 0) {
        container.innerHTML = `
            <p class="text-muted">
                No upcoming clinics found.
            </p>
        `;
        return;
    }

    container.innerHTML = records.map(function (record) {
        return `
            <div class="dashboard-widget-item clinic-widget-item">
                <div class="widget-item-title">
                    ${escapeDashboardHtml(record.type_name || 'Clinic')}
                </div>

                <div class="widget-item-meta">
                    <i class="fas fa-calendar-alt"></i>
                    ${formatDashboardDate(record.scheduled_date)}
                    &nbsp; | &nbsp;
                    <i class="fas fa-clock"></i>
                    ${formatDashboardTime(record.start_time)}
                </div>

                <div class="widget-item-meta">
                    <i class="fas fa-map-marker-alt"></i>
                    ${escapeDashboardHtml(record.location || '-')}
                </div>

                <span class="status-badge status-active">
                    ${escapeDashboardHtml(record.status || 'Scheduled')}
                </span>
            </div>
        `;
    }).join('');
}

function renderTodayTimetable(records) {
    const container = document.getElementById('todayTimetableList');
    if (!container) return;

    if (records.length === 0) {
        container.innerHTML = `
            <p class="text-muted">
                No scheduled activities for today.
            </p>
        `;
        return;
    }

    container.innerHTML = records.map(function (record) {
        return `
            <div class="dashboard-widget-item timetable-widget-item">
                <div class="widget-time-row">
                    <strong>${formatDashboardTime(record.start_time)}</strong>
                    <span>${formatDashboardTime(record.estimated_end_time)}</span>
                </div>

                <div class="widget-item-title">
                    ${escapeDashboardHtml(record.type_name || 'Scheduled Activity')}
                </div>

                <div class="widget-item-meta">
                    <i class="fas fa-map-marker-alt"></i>
                    ${escapeDashboardHtml(record.location || '-')}
                </div>

                ${
                    record.patient_name
                        ? `<div class="widget-item-meta">
                            <i class="fas fa-user"></i>
                            ${escapeDashboardHtml(record.patient_name)}
                           </div>`
                        : ''
                }
            </div>
        `;
    }).join('');
}

function updateDashboardGreetingCount(summary) {
    const scheduledCount = summary.scheduled_activities ?? null;

    const greetingText = document.getElementById('dashboardGreetingSubText');

    if (greetingText && scheduledCount !== null) {
        greetingText.textContent = `Ready to make a difference in your community today. You have ${scheduledCount} scheduled activities.`;
    }
}

function setDashboardWidgetError(containerId, message) {
    const container = document.getElementById(containerId);

    if (!container) return;

    container.innerHTML = `
        <p class="text-danger">
            ${escapeDashboardHtml(message)}
        </p>
    `;
}

function formatDashboardDate(value) {
    if (!value) return '-';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toISOString().split('T')[0];
}

function formatDashboardTime(value) {
    if (!value) return '-';

    return String(value).slice(0, 5);
}

function escapeDashboardHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}