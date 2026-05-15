let emergencySearchTimer = null;
let allAdminEmergencyRecords = [];

document.addEventListener('DOMContentLoaded', function () {
    console.log('admin-emergency.js loaded');

    if (document.getElementById('emergency-table')) {
        loadEmergencyActivities();
    }
});

window.debounceEmergency = function () {
    clearTimeout(emergencySearchTimer);

    emergencySearchTimer = setTimeout(function () {
        window.loadEmergencyActivities();
    }, 350);
};

window.loadEmergencyActivities = function () {
    console.log('loadEmergencyActivities called');

    const tbody = document.getElementById('emergency-table');

    if (!tbody) {
        console.error('emergency-table tbody not found.');
        return;
    }

    const keyword = getEmergencyValue('emergKeyword');
    const dateFrom = getEmergencyValue('emergDateFrom');
    const dateTo = getEmergencyValue('emergDateTo');
    const status = getEmergencyValue('emergStatus');
    const midwifeId = getEmergencyValue('emergMidwife');

    const params = new URLSearchParams();

    if (keyword) params.append('keyword', keyword);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    if (status) params.append('status', status);
    if (midwifeId) params.append('midwife_id', midwifeId);

    tbody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center" style="padding:2rem;">
                <i class="fas fa-spinner fa-spin"></i> Loading emergency responses...
            </td>
        </tr>
    `;

    fetch('../php/admin/get_emergency_activities.php?' + params.toString(), {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.text();
        })
        .then(function (text) {
            console.log('Emergency raw response:', text);

            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error('Invalid emergency JSON:', text);
                renderEmergencyError('Invalid JSON response from PHP.');
                return;
            }

            if (!data.success) {
                renderEmergencyError(data.message || 'Failed to load emergency responses.');
                return;
            }

            allAdminEmergencyRecords = data.records || [];

            renderEmergencyMidwifeOptions(data.midwives || []);
            renderEmergencyRows(allAdminEmergencyRecords);
        })
        .catch(function (error) {
            console.error('Emergency fetch error:', error);
            renderEmergencyError('Network/server error while loading emergency responses.');
        });
};

function renderEmergencyRows(records) {
    const tbody = document.getElementById('emergency-table');

    if (!tbody) return;

    if (!records || records.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center" style="padding:2rem;">
                    No emergency responses found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = records.map(function (record) {
        return `
            <tr>
                <td>
                    <strong>${escapeEmergencyHtml(formatEmergencyDate(record.emergency_date))}</strong>
                    <br>
                    <small style="color:var(--text-muted);">
                        ${escapeEmergencyHtml(formatEmergencyTime(record.start_time))}
                        ${record.end_time ? ' - ' + escapeEmergencyHtml(formatEmergencyTime(record.end_time)) : ''}
                    </small>
                </td>

                <td>
                    <strong>${escapeEmergencyHtml(record.midwife_name || '-')}</strong>
                    <br>
                    <small style="color:var(--text-muted);">
                        ${escapeEmergencyHtml(record.assigned_area || '-')}
                    </small>
                </td>

                <td>
                    <strong>${escapeEmergencyHtml(record.patient_name || '-')}</strong>
                    ${record.patient_age ? ' (' + escapeEmergencyHtml(record.patient_age) + 'y)' : ''}
                    <br>
                    <small>
                        Type: ${escapeEmergencyHtml(formatEmergencyLabel(record.emergency_type || '-'))}
                        ${record.patient_contact ? '<br>Contact: ' + escapeEmergencyHtml(record.patient_contact) : ''}
                        ${record.description ? '<br>Details: ' + escapeEmergencyHtml(shortEmergencyText(record.description, 70)) : ''}
                    </small>
                </td>

                <td>
                    <small>
                        ${escapeEmergencyHtml(record.location || '-')}
                    </small>
                </td>

                <td>
                    ${renderEmergencyPriority(record.priority)}
                </td>

                <td>
                    ${renderEmergencyStatus(record.status)}
                </td>

                <td>
                    <button
                        type="button"
                        class="btn btn-sm btn-info"
                        onclick="viewEmergencyDetails('${escapeEmergencyAttribute(record.record_key)}')"
                    >
                        View Details
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function renderEmergencyMidwifeOptions(midwives) {
    const select = document.getElementById('emergMidwife');

    if (!select) return;

    const selectedValue = select.value;

    let html = `<option value="">All Midwives</option>`;

    midwives.forEach(function (midwife) {
        html += `
            <option value="${escapeEmergencyHtml(midwife.midwife_id)}">
                ${escapeEmergencyHtml(midwife.full_name)} (${escapeEmergencyHtml(midwife.employee_id || '-')})
            </option>
        `;
    });

    select.innerHTML = html;

    if (selectedValue) {
        select.value = selectedValue;
    }
}

window.viewEmergencyDetails = function (recordKey) {
    const record = allAdminEmergencyRecords.find(function (item) {
        return String(item.record_key) === String(recordKey);
    });

    if (!record) {
        alert('Emergency response record not found.');
        return;
    }

    alert(
        'Emergency Response Details\n\n' +
        'Source: ' + valueOrDashEmergency(formatEmergencyLabel(record.record_source)) + '\n' +
        'Type: ' + valueOrDashEmergency(formatEmergencyLabel(record.emergency_type)) + '\n' +
        'Patient: ' + valueOrDashEmergency(record.patient_name) + '\n' +
        'Patient Age: ' + valueOrDashEmergency(record.patient_age) + '\n' +
        'Contact: ' + valueOrDashEmergency(record.patient_contact) + '\n' +
        'Date: ' + valueOrDashEmergency(record.emergency_date) + '\n' +
        'Start Time: ' + valueOrDashEmergency(record.start_time) + '\n' +
        'End Time: ' + valueOrDashEmergency(record.end_time) + '\n' +
        'Midwife: ' + valueOrDashEmergency(record.midwife_name) + '\n' +
        'Area: ' + valueOrDashEmergency(record.assigned_area) + '\n' +
        'Location: ' + valueOrDashEmergency(record.location) + '\n' +
        'Priority: ' + valueOrDashEmergency(formatEmergencyLabel(record.priority)) + '\n' +
        'Status: ' + valueOrDashEmergency(formatEmergencyLabel(record.status)) + '\n' +
        'Description / Reason: ' + valueOrDashEmergency(record.description) + '\n' +
        'Observations / Notes: ' + valueOrDashEmergency(record.observations) + '\n' +
        'Recommendations: ' + valueOrDashEmergency(record.recommendations)
    );
};

function renderEmergencyPriority(priority) {
    const value = String(priority || 'normal').toLowerCase();

    let className = 'status-badge status-active';
    let label = formatEmergencyLabel(value);

    if (value === 'critical' || value === 'urgent' || value === 'high') {
        className = 'status-badge status-inactive';
    }

    if (value === 'medium') {
        className = 'status-badge status-on-leave';
    }

    return `<span class="${className}">${escapeEmergencyHtml(label)}</span>`;
}

function renderEmergencyStatus(status) {
    const value = String(status || 'pending').toLowerCase();

    let className = 'status-badge status-on-leave';
    let label = formatEmergencyLabel(value);

    if (value === 'completed') {
        className = 'status-badge status-active';
    }

    if (value === 'in_progress' || value === 'in-progress') {
        className = 'status-badge status-inactive';
        label = 'In Progress';
    }

    if (value === 'cancelled' || value === 'canceled') {
        className = 'status-badge status-inactive';
    }

    return `<span class="${className}">${escapeEmergencyHtml(label)}</span>`;
}

function renderEmergencyError(message) {
    const tbody = document.getElementById('emergency-table');

    if (!tbody) return;

    tbody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center" style="padding:2rem; color:red;">
                ${escapeEmergencyHtml(message)}
            </td>
        </tr>
    `;
}

function getEmergencyValue(id) {
    const element = document.getElementById(id);
    return element ? String(element.value || '').trim() : '';
}

function formatEmergencyDate(value) {
    if (!value) return '-';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString('en-GB');
}

function formatEmergencyTime(value) {
    if (!value) return '-';
    return String(value).substring(0, 5);
}

function formatEmergencyLabel(value) {
    return String(value || '-')
        .replaceAll('_', ' ')
        .replaceAll('-', ' ')
        .replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });
}

function shortEmergencyText(value, limit) {
    const text = String(value || '');

    if (text.length <= limit) {
        return text;
    }

    return text.substring(0, limit) + '...';
}

function valueOrDashEmergency(value) {
    return value && String(value).trim() !== '' ? String(value) : '-';
}

function escapeEmergencyHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function escapeEmergencyAttribute(value) {
    return escapeEmergencyHtml(value);
}