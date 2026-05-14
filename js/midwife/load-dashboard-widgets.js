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
                console.error('Invalid dashboard widgets JSON:', text);
                renderDashboardWidgetError();
                return;
            }

            if (!data.success) {
                console.error(data.message || 'Failed to load dashboard widgets.');
                renderDashboardWidgetError(data.message);
                return;
            }

            renderDashboardGreeting(data);
            renderUrgentMeetings(data.urgent_meetings || []);
            renderUpcomingClinics(data.upcoming_clinics || []);
            renderTodaysTimetable(data.todays_timetable || []);
            renderDashboardSummary(data.summary || {});
        })
        .catch(function (error) {
            console.error('Dashboard Widget Load Error:', error);
            renderDashboardWidgetError();
        });
}

function renderDashboardGreeting(data) {
    const greetingText = document.getElementById('greeting-text');

    if (!greetingText) return;

    const sessionName = window.midwifeSession && window.midwifeSession.full_name
        ? window.midwifeSession.full_name
        : '';

    const firstName = sessionName
        ? sessionName.split(' ')[0]
        : 'Midwife';

    greetingText.textContent = getGreetingText() + ', ' + firstName + '!';
}

function renderDashboardSummary(summary) {
    const countElement = document.getElementById('dashboardScheduledCount');

    if (!countElement) return;

    countElement.textContent = Number(summary.scheduled_activities || 0);
}

function renderUrgentMeetings(records) {
    const container = document.getElementById('urgentMeetingsList');

    if (!container) return;

    if (!records.length) {
        container.innerHTML = `
            <p class="text-muted" style="margin: 0;">
                No urgent meetings found.
            </p>
        `;
        return;
    }

    container.innerHTML = records.map(function (item) {
        return `
            <div class="dashboard-widget-item urgent-widget-item">
                <div class="widget-title">
                    <i class="fas fa-exclamation-circle"></i>
                    ${escapeDashboardHtml(item.type_name || 'Urgent Meeting')}
                </div>

                <div class="widget-meta">
                    <span>
                        <i class="fas fa-calendar"></i>
                        ${formatDashboardDate(item.scheduled_date)}
                    </span>

                    <span>
                        <i class="fas fa-clock"></i>
                        ${formatDashboardTime(item.start_time)}
                    </span>
                </div>

                <div class="widget-meta">
                    <span>
                        <i class="fas fa-map-marker-alt"></i>
                        ${escapeDashboardHtml(item.location || '-')}
                    </span>
                </div>

                ${item.patient_name ? `
                    <div class="widget-small">
                        Patient: ${escapeDashboardHtml(item.patient_name)}
                    </div>
                ` : ''}

                ${item.description ? `
                    <div class="widget-small">
                        ${escapeDashboardHtml(item.description)}
                    </div>
                ` : ''}
            </div>
        `;
    }).join('');
}

function renderUpcomingClinics(records) {
    const container = document.getElementById('upcomingClinicsList');

    if (!container) return;

    if (!records.length) {
        container.innerHTML = `
            <p class="text-muted" style="margin: 0;">
                No upcoming clinics found.
            </p>
        `;
        return;
    }

    container.innerHTML = records.map(function (item) {
        return `
            <div class="dashboard-widget-item clinic-widget-item">
                <div class="widget-title">
                    <i class="fas fa-hospital"></i>
                    ${escapeDashboardHtml(item.type_name || 'Clinic')}
                </div>

                <div class="widget-meta">
                    <span>
                        <i class="fas fa-calendar"></i>
                        ${formatDashboardDate(item.scheduled_date)}
                    </span>

                    <span>
                        <i class="fas fa-clock"></i>
                        ${formatDashboardTime(item.start_time)}
                    </span>
                </div>

                <div class="widget-meta">
                    <span>
                        <i class="fas fa-map-marker-alt"></i>
                        ${escapeDashboardHtml(item.location || '-')}
                    </span>
                </div>

                ${item.description ? `
                    <div class="widget-small">
                        ${escapeDashboardHtml(item.description)}
                    </div>
                ` : ''}
            </div>
        `;
    }).join('');
}

function renderTodaysTimetable(records) {
    const container = document.getElementById('todaysTimetableList');

    if (!container) return;

    if (!records.length) {
        container.innerHTML = `
            <p class="text-muted" style="margin: 0;">
                No activities scheduled for today.
            </p>
        `;
        return;
    }

    container.innerHTML = records.map(function (item) {
        return `
            <div class="dashboard-widget-item timetable-widget-item">
                <div class="widget-title">
                    <i class="fas fa-clock"></i>
                    ${formatDashboardTime(item.start_time)}
                    -
                    ${formatDashboardTime(item.estimated_end_time)}
                </div>

                <div class="widget-meta">
                    <span>
                        ${escapeDashboardHtml(item.type_name || 'Scheduled Activity')}
                    </span>
                </div>

                <div class="widget-meta">
                    <span>
                        <i class="fas fa-map-marker-alt"></i>
                        ${escapeDashboardHtml(item.location || '-')}
                    </span>
                </div>

                ${item.patient_name ? `
                    <div class="widget-small">
                        Patient: ${escapeDashboardHtml(item.patient_name)}
                    </div>
                ` : ''}

                <div class="widget-status">
                    ${renderDashboardStatusBadge(item.status || 'scheduled')}
                </div>
            </div>
        `;
    }).join('');
}

function renderDashboardWidgetError(message) {
    const errorText = message || 'Unable to load dashboard data.';

    const containers = [
        'urgentMeetingsList',
        'upcomingClinicsList',
        'todaysTimetableList'
    ];

    containers.forEach(function (id) {
        const container = document.getElementById(id);

        if (container) {
            container.innerHTML = `
                <p class="text-muted" style="margin: 0;">
                    ${escapeDashboardHtml(errorText)}
                </p>
            `;
        }
    });
}

function getGreetingText() {
    const hour = new Date().getHours();

    if (hour < 12) {
        return 'Good Morning';
    }

    if (hour < 18) {
        return 'Good Afternoon';
    }

    return 'Good Evening';
}

function formatDashboardDate(value) {
    if (!value) return '-';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatDashboardTime(value) {
    if (!value) return '-';

    const parts = String(value).split(':');

    if (parts.length < 2) {
        return value;
    }

    return parts[0] + ':' + parts[1];
}

function renderDashboardStatusBadge(status) {
    const normalized = String(status || '').toLowerCase();

    let className = 'status-badge status-active';

    if (normalized === 'completed') {
        className = 'status-badge status-success';
    }

    if (normalized === 'cancelled' || normalized === 'canceled') {
        className = 'status-badge status-danger';
    }

    if (normalized === 'pending') {
        className = 'status-badge status-warning';
    }

    return `
        <span class="${className}">
            ${escapeDashboardHtml(formatDashboardStatus(status))}
        </span>
    `;
}

function formatDashboardStatus(value) {
    return String(value || '-')
        .replaceAll('_', ' ')
        .replaceAll('-', ' ')
        .replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });
}

function escapeDashboardHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}