let adminActivities = [];

document.addEventListener('DOMContentLoaded', function () {
    loadAdminActivities();
});

function loadAdminActivities() {
    const tbody = document.getElementById('adminActivitiesTableBody');

    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-muted text-center">
                    <i class="fas fa-spinner fa-spin"></i> Loading activities...
                </td>
            </tr>
        `;
    }

    fetch('../php/admin/get_activities.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.text();
        })
        .then(function (text) {
            console.log('Admin activities raw response:', text);

            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error('Invalid activities JSON:', text);
                renderAdminActivitiesError('Activities PHP returned non-JSON response. Check php_errors.log.');
                return;
            }

            if (data.success !== true) {
                renderAdminActivitiesError(data.message || 'Failed to load activities.');
                return;
            }

            adminActivities = data.activities || [];
            renderAdminActivitiesTable();
        })
        .catch(function (error) {
            console.error('Admin Activities Load Error:', error);
            renderAdminActivitiesError('Server error while loading activities.');
        });
}

function renderAdminActivitiesTable() {
    const tbody = document.getElementById('adminActivitiesTableBody');

    if (!tbody) return;

    if (!adminActivities.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-muted text-center">
                    No activity records found for this admin MOH office.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = adminActivities.map(function (activity) {
        return `
            <tr>
                <td>${escapeAdminActivityHtml(formatAdminActivityTime(activity.start_time))}</td>
                <td>${escapeAdminActivityHtml(formatAdminActivityDate(activity.activity_date))}</td>
                <td>${escapeAdminActivityHtml(shortenAdminMidwifeName(activity.midwife_name))}</td>
                <td>${escapeAdminActivityHtml(activity.activity_type || '-')}</td>
                <td>${escapeAdminActivityHtml(activity.location || '-')}</td>
                <td>${renderAdminActivityStatusBadge(activity.status)}</td>
                <td>
                    <button class="btn btn-info btn-sm" type="button" onclick="viewAdminActivityDetails(${Number(activity.activity_id)})">
                        View Details
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function viewAdminActivityDetails(activityId) {
    const activity = adminActivities.find(function (item) {
        return Number(item.activity_id) === Number(activityId);
    });

    if (!activity) {
        alert('Activity record not found.');
        return;
    }

    const body = document.getElementById('adminActivityDetailsBody');

    if (!body) return;

    body.innerHTML = `
        <div class="row">
            <div class="col-6">
                <p><strong>Activity Type:</strong><br>${escapeAdminActivityHtml(activity.activity_type || '-')}</p>
            </div>
            <div class="col-6">
                <p><strong>Status:</strong><br>${renderAdminActivityStatusBadge(activity.status)}</p>
            </div>
        </div>

        <div class="row">
            <div class="col-6">
                <p><strong>Date:</strong><br>${escapeAdminActivityHtml(formatAdminActivityDate(activity.activity_date))}</p>
            </div>
            <div class="col-6">
                <p><strong>Time:</strong><br>${escapeAdminActivityHtml(formatAdminActivityTime(activity.start_time))} - ${escapeAdminActivityHtml(formatAdminActivityTime(activity.end_time))}</p>
            </div>
        </div>

        <div class="row">
            <div class="col-6">
                <p><strong>Midwife:</strong><br>${escapeAdminActivityHtml(activity.midwife_name || '-')}</p>
            </div>
            <div class="col-6">
                <p><strong>Employee ID:</strong><br>${escapeAdminActivityHtml(activity.employee_id || '-')}</p>
            </div>
        </div>

        <div class="row">
            <div class="col-6">
                <p><strong>Assigned Area:</strong><br>${escapeAdminActivityHtml(activity.assigned_area || '-')}</p>
            </div>
            <div class="col-6">
                <p><strong>MOH Office:</strong><br>${escapeAdminActivityHtml(activity.moh_office || '-')}</p>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-6">
                <p><strong>Patient Name:</strong><br>${escapeAdminActivityHtml(activity.patient_name || '-')}</p>
            </div>
            <div class="col-6">
                <p><strong>Patient Contact:</strong><br>${escapeAdminActivityHtml(activity.patient_contact || '-')}</p>
            </div>
        </div>

        <p><strong>Location:</strong><br>${escapeAdminActivityHtml(activity.location || '-')}</p>
        <p><strong>Description:</strong><br>${escapeAdminActivityHtml(activity.description || '-')}</p>
        <p><strong>Observations:</strong><br>${escapeAdminActivityHtml(activity.observations || '-')}</p>
        <p><strong>Recommendations:</strong><br>${escapeAdminActivityHtml(activity.recommendations || '-')}</p>

        <div class="row">
            <div class="col-6">
                <p><strong>Follow-up Required:</strong><br>${Number(activity.follow_up_required) === 1 ? 'Yes' : 'No'}</p>
            </div>
            <div class="col-6">
                <p><strong>Follow-up Date:</strong><br>${escapeAdminActivityHtml(formatAdminActivityDate(activity.follow_up_date))}</p>
            </div>
        </div>
    `;

    if (typeof $ !== 'undefined') {
        $('#adminActivityDetailsModal').modal('show');
    } else {
        alert('Bootstrap modal is not loaded.');
    }
}

function renderAdminActivitiesError(message) {
    const tbody = document.getElementById('adminActivitiesTableBody');

    if (!tbody) return;

    tbody.innerHTML = `
        <tr>
            <td colspan="7" class="text-danger text-center">
                ${escapeAdminActivityHtml(message)}
            </td>
        </tr>
    `;
}

function renderAdminActivityStatusBadge(status) {
    const value = String(status || 'pending');
    const normalized = value.toLowerCase();

    let className = 'status-badge status-warning';

    if (
        normalized.includes('completed') ||
        normalized.includes('done') ||
        normalized.includes('active')
    ) {
        className = 'status-badge status-active';
    }

    if (
        normalized.includes('cancel') ||
        normalized.includes('inactive') ||
        normalized.includes('failed')
    ) {
        className = 'status-badge status-danger';
    }

    return `<span class="${className}">${escapeAdminActivityHtml(formatAdminActivityStatus(value))}</span>`;
}

function formatAdminActivityStatus(status) {
    return String(status || '-')
        .replaceAll('_', ' ')
        .replaceAll('-', ' ')
        .replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });
}

function formatAdminActivityDate(value) {
    if (!value) return '-';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toISOString().split('T')[0];
}

function formatAdminActivityTime(value) {
    if (!value) return '-';

    return String(value).substring(0, 5);
}

function shortenAdminMidwifeName(name) {
    if (!name) return '-';

    const parts = String(name).trim().split(/\s+/);

    if (parts.length === 1) {
        return parts[0];
    }

    return parts[0].charAt(0) + '. ' + parts[parts.length - 1];
}

function escapeAdminActivityHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}