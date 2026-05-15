let counselingSearchTimer = null;
let allAdminCounselingRecords = [];

document.addEventListener('DOMContentLoaded', function () {
    console.log('admin-counseling.js loaded');

    if (document.getElementById('counseling-table')) {
        loadCounselingActivities();
    }
});

window.debounceCounseling = function () {
    clearTimeout(counselingSearchTimer);

    counselingSearchTimer = setTimeout(function () {
        window.loadCounselingActivities();
    }, 350);
};

window.loadCounselingActivities = function () {
    console.log('loadCounselingActivities called');

    const tbody = document.getElementById('counseling-table');

    if (!tbody) {
        console.error('counseling-table tbody not found.');
        return;
    }

    const keyword = getCounselingValue('counselKeyword');
    const dateFrom = getCounselingValue('counselDateFrom');
    const dateTo = getCounselingValue('counselDateTo');
    const status = getCounselingValue('counselStatus');
    const midwifeId = getCounselingValue('counselMidwife');

    const params = new URLSearchParams();

    if (keyword) params.append('keyword', keyword);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    if (status) params.append('status', status);
    if (midwifeId) params.append('midwife_id', midwifeId);

    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center" style="padding:2rem;">
                <i class="fas fa-spinner fa-spin"></i> Loading counseling sessions...
            </td>
        </tr>
    `;

    fetch('../php/admin/get_counseling_activities.php?' + params.toString(), {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.text();
        })
        .then(function (text) {
            console.log('Counseling raw response:', text);

            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error('Invalid counseling JSON:', text);
                renderCounselingError('Invalid JSON response from PHP.');
                return;
            }

            if (!data.success) {
                renderCounselingError(data.message || 'Failed to load counseling sessions.');
                return;
            }

            allAdminCounselingRecords = data.records || [];

            renderCounselingMidwifeOptions(data.midwives || []);
            renderCounselingRows(allAdminCounselingRecords);
        })
        .catch(function (error) {
            console.error('Counseling fetch error:', error);
            renderCounselingError('Network/server error while loading counseling sessions.');
        });
};

function renderCounselingRows(records) {
    const tbody = document.getElementById('counseling-table');

    if (!tbody) return;

    if (!records || records.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center" style="padding:2rem;">
                    No counseling sessions found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = records.map(function (record) {
        return `
            <tr>
                <td>
                    <strong>${escapeCounselingHtml(formatCounselingDate(record.session_date))}</strong>
                    <br>
                    <small style="color:var(--text-muted);">
                        ${escapeCounselingHtml(formatCounselingTime(record.session_time))}
                        ${record.duration_mins ? ' - ' + escapeCounselingHtml(record.duration_mins) + ' mins' : ''}
                    </small>
                </td>

                <td>
                    <strong>${escapeCounselingHtml(record.midwife_name || '-')}</strong>
                    <br>
                    <small style="color:var(--text-muted);">
                        ${escapeCounselingHtml(record.assigned_area || '-')}
                    </small>
                </td>

                <td>
                    <strong>${escapeCounselingHtml(record.client_ref || '-')}</strong>
                    <br>
                    <small>
                        Focus: ${escapeCounselingHtml(formatCounselingLabel(record.focus || '-'))}
                        ${record.followup ? '<br>Follow-up: ' + escapeCounselingHtml(formatCounselingLabel(record.followup)) : ''}
                        ${record.followup_date ? '<br>Follow-up Date: ' + escapeCounselingHtml(formatCounselingDate(record.followup_date)) : ''}
                    </small>
                </td>

                <td>
                    <small>
                        ${escapeCounselingHtml(formatCounselingLabel(record.location_type || '-'))}
                    </small>
                </td>

                <td>
                    ${renderCounselingStatus(record.status)}
                </td>

                <td>
                    <button
                        type="button"
                        class="btn btn-sm btn-info"
                        onclick="viewCounselingDetails('${escapeCounselingAttribute(record.record_key)}')"
                    >
                        View Details
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function renderCounselingMidwifeOptions(midwives) {
    const select = document.getElementById('counselMidwife');

    if (!select) return;

    const selectedValue = select.value;

    let html = `<option value="">All Midwives</option>`;

    midwives.forEach(function (midwife) {
        html += `
            <option value="${escapeCounselingHtml(midwife.midwife_id)}">
                ${escapeCounselingHtml(midwife.full_name)} (${escapeCounselingHtml(midwife.employee_id || '-')})
            </option>
        `;
    });

    select.innerHTML = html;

    if (selectedValue) {
        select.value = selectedValue;
    }
}

window.viewCounselingDetails = function (recordKey) {
    const record = allAdminCounselingRecords.find(function (item) {
        return String(item.record_key) === String(recordKey);
    });

    if (!record) {
        alert('Counseling session record not found.');
        return;
    }

    alert(
        'Counseling Session Details\n\n' +
        'Client Ref: ' + valueOrDashCounseling(record.client_ref) + '\n' +
        'Focus: ' + valueOrDashCounseling(formatCounselingLabel(record.focus)) + '\n' +
        'Date & Time: ' + valueOrDashCounseling(record.session_datetime) + '\n' +
        'Duration: ' + valueOrDashCounseling(record.duration_mins) + ' mins\n' +
        'Location Type: ' + valueOrDashCounseling(formatCounselingLabel(record.location_type)) + '\n' +
        'Midwife: ' + valueOrDashCounseling(record.midwife_name) + '\n' +
        'Area: ' + valueOrDashCounseling(record.assigned_area) + '\n' +
        'Status: ' + valueOrDashCounseling(formatCounselingLabel(record.status)) + '\n' +
        'Follow-up: ' + valueOrDashCounseling(formatCounselingLabel(record.followup)) + '\n' +
        'Follow-up Date: ' + valueOrDashCounseling(record.followup_date) + '\n' +
        'Referral Details: ' + valueOrDashCounseling(record.referral_details) + '\n' +
        'Notes: ' + valueOrDashCounseling(record.notes)
    );
};

function renderCounselingStatus(status) {
    const value = String(status || 'completed').toLowerCase();

    let className = 'status-badge status-on-leave';
    let label = formatCounselingLabel(value);

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

    return `<span class="${className}">${escapeCounselingHtml(label)}</span>`;
}

function renderCounselingError(message) {
    const tbody = document.getElementById('counseling-table');

    if (!tbody) return;

    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center" style="padding:2rem; color:red;">
                ${escapeCounselingHtml(message)}
            </td>
        </tr>
    `;
}

function getCounselingValue(id) {
    const element = document.getElementById(id);
    return element ? String(element.value || '').trim() : '';
}

function formatCounselingDate(value) {
    if (!value) return '-';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString('en-GB');
}

function formatCounselingTime(value) {
    if (!value) return '-';
    return String(value).substring(0, 5);
}

function formatCounselingLabel(value) {
    return String(value || '-')
        .replaceAll('_', ' ')
        .replaceAll('-', ' ')
        .replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });
}

function valueOrDashCounseling(value) {
    return value && String(value).trim() !== '' ? String(value) : '-';
}

function escapeCounselingHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function escapeCounselingAttribute(value) {
    return escapeCounselingHtml(value);
}