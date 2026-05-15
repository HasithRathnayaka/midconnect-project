let homeVisitSearchTimer = null;
let allAdminHomeVisitRecords = [];

document.addEventListener('DOMContentLoaded', function () {
    console.log('admin-home-visits.js loaded');
});

window.debounceHomeVisits = function () {
    clearTimeout(homeVisitSearchTimer);

    homeVisitSearchTimer = setTimeout(function () {
        window.loadHomeVisitActivities();
    }, 350);
};

window.loadHomeVisitActivities = function () {
    console.log('loadHomeVisitActivities called');

    const tbody = document.getElementById('home-visits-table');

    if (!tbody) {
        console.error('home-visits-table tbody not found.');
        return;
    }

    const keyword = getHomeVisitValue('hvKeyword');
    const dateFrom = getHomeVisitValue('hvDateFrom');
    const dateTo = getHomeVisitValue('hvDateTo');
    const status = getHomeVisitValue('hvStatus');
    const midwifeId = getHomeVisitValue('hvMidwife');

    const params = new URLSearchParams();

    if (keyword) params.append('keyword', keyword);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    if (status) params.append('status', status);
    if (midwifeId) params.append('midwife_id', midwifeId);

    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center" style="padding:2rem;">
                <i class="fas fa-spinner fa-spin"></i> Loading home visits...
            </td>
        </tr>
    `;

    fetch('../php/admin/get_home_visit_activities.php?' + params.toString(), {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.text();
        })
        .then(function (text) {
            console.log('Home visit raw response:', text);

            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error('Invalid JSON:', text);
                renderHomeVisitError('Invalid JSON response from PHP.');
                return;
            }

            if (!data.success) {
                renderHomeVisitError(data.message || 'Failed to load home visits.');
                return;
            }

            allAdminHomeVisitRecords = data.records || [];

            renderHomeVisitMidwifeOptions(data.midwives || []);
            renderHomeVisitRows(allAdminHomeVisitRecords);
        })
        .catch(function (error) {
            console.error('Home visit fetch error:', error);
            renderHomeVisitError('Network/server error while loading home visits.');
        });
};

function renderHomeVisitRows(records) {
    const tbody = document.getElementById('home-visits-table');

    if (!tbody) return;

    if (!records || records.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center" style="padding:2rem;">
                    No home visits found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = records.map(function (record) {
        return `
            <tr>
                <td>
                    <strong>${escapeHomeVisitHtml(formatHomeVisitDate(record.visit_date))}</strong>
                    <br>
                    <small style="color:var(--text-muted);">
                        ${escapeHomeVisitHtml(formatHomeVisitTime(record.start_time))}
                        ${record.end_time ? ' - ' + escapeHomeVisitHtml(formatHomeVisitTime(record.end_time)) : ''}
                    </small>
                </td>

                <td>
                    <strong>${escapeHomeVisitHtml(record.midwife_name || '-')}</strong>
                    <br>
                    <small style="color:var(--text-muted);">
                        ${escapeHomeVisitHtml(record.assigned_area || '-')}
                    </small>
                </td>

                <td>
                    <strong>${escapeHomeVisitHtml(record.patient_name || '-')}</strong>
                    <br>
                    <small>
                        Type: ${escapeHomeVisitHtml(record.visit_type || '-')}
                        ${record.contact_number ? '<br>Contact: ' + escapeHomeVisitHtml(record.contact_number) : ''}
                        ${record.reason ? '<br>Reason: ' + escapeHomeVisitHtml(record.reason) : ''}
                    </small>
                </td>

                <td>
                    <small>
                        ${escapeHomeVisitHtml(record.address || record.duty_area || '-')}
                    </small>
                </td>

                <td>
                    ${renderHomeVisitStatus(record.status)}
                </td>

                <td>
                    <button 
                        type="button" 
                        class="btn btn-sm btn-info"
                        onclick="viewHomeVisitDetails('${escapeHomeVisitAttribute(record.record_key)}')"
                    >
                        View Details
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function renderHomeVisitMidwifeOptions(midwives) {
    const select = document.getElementById('hvMidwife');

    if (!select) return;

    const selectedValue = select.value;

    let html = `<option value="">All Midwives</option>`;

    midwives.forEach(function (midwife) {
        html += `
            <option value="${escapeHomeVisitHtml(midwife.midwife_id)}">
                ${escapeHomeVisitHtml(midwife.full_name)} (${escapeHomeVisitHtml(midwife.employee_id || '-')})
            </option>
        `;
    });

    select.innerHTML = html;

    if (selectedValue) {
        select.value = selectedValue;
    }
}

window.viewHomeVisitDetails = function (recordKey) {
    const record = allAdminHomeVisitRecords.find(function (item) {
        return String(item.record_key) === String(recordKey);
    });

    if (!record) {
        alert('Home visit record not found.');
        return;
    }

    alert(
        'Home Visit Details\n\n' +
        'Patient: ' + valueOrDash(record.patient_name) + '\n' +
        'Contact: ' + valueOrDash(record.contact_number) + '\n' +
        'Date: ' + valueOrDash(record.visit_date) + '\n' +
        'Start Time: ' + valueOrDash(record.start_time) + '\n' +
        'End Time: ' + valueOrDash(record.end_time) + '\n' +
        'Midwife: ' + valueOrDash(record.midwife_name) + '\n' +
        'Area: ' + valueOrDash(record.assigned_area) + '\n' +
        'Address: ' + valueOrDash(record.address) + '\n' +
        'Visit Type: ' + valueOrDash(record.visit_type) + '\n' +
        'Priority: ' + valueOrDash(record.priority) + '\n' +
        'Status: ' + valueOrDash(record.status) + '\n' +
        'Reason: ' + valueOrDash(record.reason) + '\n' +
        'Notes: ' + valueOrDash(record.notes)
    );
};

function renderHomeVisitStatus(status) {
    const value = String(status || 'scheduled').toLowerCase();

    let className = 'status-badge status-on-leave';
    let label = formatStatusLabel(value);

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

    return `<span class="${className}">${escapeHomeVisitHtml(label)}</span>`;
}

function renderHomeVisitError(message) {
    const tbody = document.getElementById('home-visits-table');

    if (!tbody) return;

    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center" style="padding:2rem; color:red;">
                ${escapeHomeVisitHtml(message)}
            </td>
        </tr>
    `;
}

function getHomeVisitValue(id) {
    const element = document.getElementById(id);
    return element ? String(element.value || '').trim() : '';
}

function formatHomeVisitDate(value) {
    if (!value) return '-';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString('en-GB');
}

function formatHomeVisitTime(value) {
    if (!value) return '-';
    return String(value).substring(0, 5);
}

function formatStatusLabel(value) {
    return String(value || '-')
        .replaceAll('_', ' ')
        .replaceAll('-', ' ')
        .replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });
}

function valueOrDash(value) {
    return value && String(value).trim() !== '' ? String(value) : '-';
}

function escapeHomeVisitHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function escapeHomeVisitAttribute(value) {
    return escapeHomeVisitHtml(value);
}