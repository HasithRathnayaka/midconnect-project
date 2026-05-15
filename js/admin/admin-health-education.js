let healthEducationSearchTimer = null;
let allAdminHealthEducationRecords = [];

document.addEventListener('DOMContentLoaded', function () {
    console.log('admin-health-education.js loaded');

    if (document.getElementById('health-education-table')) {
        loadHealthEducationActivities();
    }
});

window.debounceHealthEducation = function () {
    clearTimeout(healthEducationSearchTimer);

    healthEducationSearchTimer = setTimeout(function () {
        window.loadHealthEducationActivities();
    }, 350);
};

window.loadHealthEducationActivities = function () {
    console.log('loadHealthEducationActivities called');

    const tbody = document.getElementById('health-education-table');

    if (!tbody) {
        console.error('health-education-table tbody not found.');
        return;
    }

    const keyword = getHealthEducationValue('heKeyword');
    const dateFrom = getHealthEducationValue('heDateFrom');
    const dateTo = getHealthEducationValue('heDateTo');
    const status = getHealthEducationValue('heStatus');
    const midwifeId = getHealthEducationValue('heMidwife');

    const params = new URLSearchParams();

    if (keyword) params.append('keyword', keyword);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    if (status) params.append('status', status);
    if (midwifeId) params.append('midwife_id', midwifeId);

    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center" style="padding:2rem;">
                <i class="fas fa-spinner fa-spin"></i> Loading health education sessions...
            </td>
        </tr>
    `;

    fetch('../php/admin/get_health_education_activities.php?' + params.toString(), {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.text();
        })
        .then(function (text) {
            console.log('Health education raw response:', text);

            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error('Invalid health education JSON:', text);
                renderHealthEducationError('Invalid JSON response from PHP.');
                return;
            }

            if (!data.success) {
                renderHealthEducationError(data.message || 'Failed to load health education sessions.');
                return;
            }

            allAdminHealthEducationRecords = data.records || [];

            renderHealthEducationMidwifeOptions(data.midwives || []);
            renderHealthEducationRows(allAdminHealthEducationRecords);
        })
        .catch(function (error) {
            console.error('Health education fetch error:', error);
            renderHealthEducationError('Network/server error while loading health education sessions.');
        });
};

function renderHealthEducationRows(records) {
    const tbody = document.getElementById('health-education-table');

    if (!tbody) return;

    if (!records || records.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center" style="padding:2rem;">
                    No health education sessions found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = records.map(function (record) {
        return `
            <tr>
                <td>
                    <strong>${escapeHealthEducationHtml(formatHealthEducationDate(record.session_date))}</strong>
                    <br>
                    <small style="color:var(--text-muted);">
                        Duration: ${escapeHealthEducationHtml(record.duration_mins || '-')} mins
                    </small>
                </td>

                <td>
                    <strong>${escapeHealthEducationHtml(record.midwife_name || '-')}</strong>
                    <br>
                    <small style="color:var(--text-muted);">
                        ${escapeHealthEducationHtml(record.assigned_area || '-')}
                    </small>
                </td>

                <td>
                    <strong>${escapeHealthEducationHtml(formatHealthEducationLabel(record.topic || '-'))}</strong>
                    <br>
                    <small>
                        Audience: ${escapeHealthEducationHtml(formatHealthEducationLabel(record.audience || '-'))}
                        ${record.attendees ? '<br>Attendees: ' + escapeHealthEducationHtml(record.attendees) : ''}
                        ${record.materials ? '<br>Materials: ' + escapeHealthEducationHtml(record.materials) : ''}
                    </small>
                </td>

                <td>
                    <small>
                        ${escapeHealthEducationHtml(record.venue || '-')}
                    </small>
                </td>

                <td>
                    ${renderHealthEducationStatus(record.status)}
                </td>

                <td>
                    <button
                        type="button"
                        class="btn btn-sm btn-info"
                        onclick="viewHealthEducationDetails('${escapeHealthEducationAttribute(record.record_key)}')"
                    >
                        View Details
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function renderHealthEducationMidwifeOptions(midwives) {
    const select = document.getElementById('heMidwife');

    if (!select) return;

    const selectedValue = select.value;

    let html = `<option value="">All Midwives</option>`;

    midwives.forEach(function (midwife) {
        html += `
            <option value="${escapeHealthEducationHtml(midwife.midwife_id)}">
                ${escapeHealthEducationHtml(midwife.full_name)} (${escapeHealthEducationHtml(midwife.employee_id || '-')})
            </option>
        `;
    });

    select.innerHTML = html;

    if (selectedValue) {
        select.value = selectedValue;
    }
}

window.viewHealthEducationDetails = function (recordKey) {
    const record = allAdminHealthEducationRecords.find(function (item) {
        return String(item.record_key) === String(recordKey);
    });

    if (!record) {
        alert('Health education session record not found.');
        return;
    }

    alert(
        'Health Education Session Details\n\n' +
        'Topic: ' + valueOrDashHealthEducation(formatHealthEducationLabel(record.topic)) + '\n' +
        'Date: ' + valueOrDashHealthEducation(record.session_date) + '\n' +
        'Duration: ' + valueOrDashHealthEducation(record.duration_mins) + ' mins\n' +
        'Venue: ' + valueOrDashHealthEducation(record.venue) + '\n' +
        'Audience: ' + valueOrDashHealthEducation(formatHealthEducationLabel(record.audience)) + '\n' +
        'Attendees: ' + valueOrDashHealthEducation(record.attendees) + '\n' +
        'Materials: ' + valueOrDashHealthEducation(record.materials) + '\n' +
        'Outcomes: ' + valueOrDashHealthEducation(record.outcomes) + '\n' +
        'Midwife: ' + valueOrDashHealthEducation(record.midwife_name) + '\n' +
        'Area: ' + valueOrDashHealthEducation(record.assigned_area) + '\n' +
        'Status: ' + valueOrDashHealthEducation(formatHealthEducationLabel(record.status))
    );
};

function renderHealthEducationStatus(status) {
    const value = String(status || 'completed').toLowerCase();

    let className = 'status-badge status-on-leave';
    let label = formatHealthEducationLabel(value);

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

    return `<span class="${className}">${escapeHealthEducationHtml(label)}</span>`;
}

function renderHealthEducationError(message) {
    const tbody = document.getElementById('health-education-table');

    if (!tbody) return;

    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center" style="padding:2rem; color:red;">
                ${escapeHealthEducationHtml(message)}
            </td>
        </tr>
    `;
}

function getHealthEducationValue(id) {
    const element = document.getElementById(id);
    return element ? String(element.value || '').trim() : '';
}

function formatHealthEducationDate(value) {
    if (!value) return '-';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString('en-GB');
}

function formatHealthEducationLabel(value) {
    return String(value || '-')
        .replaceAll('_', ' ')
        .replaceAll('-', ' ')
        .replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });
}

function valueOrDashHealthEducation(value) {
    return value && String(value).trim() !== '' ? String(value) : '-';
}

function escapeHealthEducationHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function escapeHealthEducationAttribute(value) {
    return escapeHealthEducationHtml(value);
}