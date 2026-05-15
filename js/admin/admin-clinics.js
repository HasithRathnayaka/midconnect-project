let adminClinicRecords = [];
let clinicSearchTimer = null;
let clinicMidwivesLoaded = false;

document.addEventListener('DOMContentLoaded', function () {
    loadClinicActivities();
});

function debounceClinics() {
    clearTimeout(clinicSearchTimer);

    clinicSearchTimer = setTimeout(function () {
        loadClinicActivities();
    }, 350);
}

function loadClinicActivities() {
    const tbody = document.getElementById('clinic-table');

    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-muted" style="text-align:center; padding:1rem;">
                    <i class="fas fa-spinner fa-spin"></i> Loading clinic visits...
                </td>
            </tr>
        `;
    }

    const params = new URLSearchParams();

    const keyword = getClinicInputValue('clinicKeyword');
    const dateFrom = getClinicInputValue('clinicDateFrom');
    const dateTo = getClinicInputValue('clinicDateTo');
    const status = getClinicInputValue('clinicStatus');
    const midwifeId = getClinicInputValue('clinicMidwife');

    if (keyword) params.append('keyword', keyword);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    if (status) params.append('status', status);
    if (midwifeId) params.append('midwife_id', midwifeId);

    fetch('../php/admin/get_clinic_activities.php?' + params.toString(), {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.text();
        })
        .then(function (text) {
            console.log('Clinic visits raw response:', text);

            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error('Invalid clinic JSON:', text);
                renderClinicError('Clinic PHP returned non-JSON response. Check php_errors.log.');
                return;
            }

            if (data.success !== true) {
                renderClinicError(data.message || 'Failed to load clinic visits.');
                return;
            }

            adminClinicRecords = data.records || [];

            if (!clinicMidwivesLoaded) {
                fillClinicMidwifeFilter(data.midwives || []);
                clinicMidwivesLoaded = true;
            }

            renderClinicTable();
        })
        .catch(function (error) {
            console.error('Clinic Visit Load Error:', error);
            renderClinicError('Server error while loading clinic visits.');
        });
}

function renderClinicTable() {
    const tbody = document.getElementById('clinic-table');

    if (!tbody) return;

    if (!adminClinicRecords.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-muted" style="text-align:center; padding:1rem;">
                    No clinic visit records found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = adminClinicRecords.map(function (record, index) {
        return `
            <tr>
                <td>
                    <strong>${escapeClinicHtml(formatClinicDate(record.clinic_date))} ${escapeClinicHtml(formatClinicTime(record.start_time))}</strong><br>
                    <small style="color:var(--text-muted);">
                        ${escapeClinicHtml(formatClinicTime(record.start_time))} – ${escapeClinicHtml(formatClinicTime(record.end_time))}
                    </small>
                </td>

                <td>
                    <strong>${escapeClinicHtml(shortenClinicMidwifeName(record.midwife_name))}</strong><br>
                    <small style="color:var(--text-muted);">
                        ${escapeClinicHtml(record.assigned_area || '-')}
                    </small>
                </td>

                <td>
                    <strong>${escapeClinicHtml(record.patient_name || 'No patient name')}</strong>
                    ${record.patient_age ? `(${escapeClinicHtml(record.patient_age)}y)` : ''}<br>
                    <small>${escapeClinicHtml(record.description || record.activity_type || '-')}</small>
                </td>

                <td>
                    <small>${escapeClinicHtml(record.location || '-')}</small>
                </td>

                <td>
                    ${renderClinicStatusBadge(record.status)}
                </td>

                <td>
                    <button class="btn btn-sm btn-info" type="button" onclick="viewClinicDetails(${index})">
                        View Details
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function fillClinicMidwifeFilter(midwives) {
    const select = document.getElementById('clinicMidwife');

    if (!select) return;

    const currentValue = select.value;

    select.innerHTML = '<option value="">All Midwives</option>' + midwives.map(function (midwife) {
        return `
            <option value="${escapeClinicHtml(midwife.midwife_id)}">
                ${escapeClinicHtml(midwife.full_name)} (${escapeClinicHtml(midwife.employee_id)})
            </option>
        `;
    }).join('');

    select.value = currentValue;
}

function viewClinicDetails(index) {
    const record = adminClinicRecords[index];

    if (!record) {
        alert('Clinic record not found.');
        return;
    }

    const body = document.getElementById('clinicDetailsBody');

    if (!body) {
        alert('Clinic details modal body not found.');
        return;
    }

    body.innerHTML = `
        <div class="row">
            <div class="col-6">
                <p><strong>Record Source:</strong><br>${escapeClinicHtml(formatClinicSource(record.record_source))}</p>
            </div>
            <div class="col-6">
                <p><strong>Status:</strong><br>${renderClinicStatusBadge(record.status)}</p>
            </div>
        </div>

        <div class="row">
            <div class="col-6">
                <p><strong>Date:</strong><br>${escapeClinicHtml(formatClinicDate(record.clinic_date))}</p>
            </div>
            <div class="col-6">
                <p><strong>Time:</strong><br>${escapeClinicHtml(formatClinicTime(record.start_time))} – ${escapeClinicHtml(formatClinicTime(record.end_time))}</p>
            </div>
        </div>

        <div class="row">
            <div class="col-6">
                <p><strong>Midwife:</strong><br>${escapeClinicHtml(record.midwife_name || '-')}</p>
            </div>
            <div class="col-6">
                <p><strong>Employee ID:</strong><br>${escapeClinicHtml(record.employee_id || '-')}</p>
            </div>
        </div>

        <div class="row">
            <div class="col-6">
                <p><strong>Assigned Area:</strong><br>${escapeClinicHtml(record.assigned_area || '-')}</p>
            </div>
            <div class="col-6">
                <p><strong>MOH Office:</strong><br>${escapeClinicHtml(record.moh_office || '-')}</p>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-6">
                <p><strong>Patient Name:</strong><br>${escapeClinicHtml(record.patient_name || '-')}</p>
            </div>
            <div class="col-6">
                <p><strong>Patient Age:</strong><br>${escapeClinicHtml(record.patient_age || '-')}</p>
            </div>
        </div>

        <p><strong>Activity Type:</strong><br>${escapeClinicHtml(record.activity_type || '-')}</p>
        <p><strong>Location:</strong><br>${escapeClinicHtml(record.location || '-')}</p>
        <p><strong>Description:</strong><br>${escapeClinicHtml(record.description || '-')}</p>
        <p><strong>Observations:</strong><br>${escapeClinicHtml(record.observations || '-')}</p>
        <p><strong>Recommendations:</strong><br>${escapeClinicHtml(record.recommendations || '-')}</p>
    `;

    if (typeof $ !== 'undefined') {
        $('#clinicDetailsModal').modal('show');
    } else {
        alert('Bootstrap modal is not loaded.');
    }
}

function renderClinicError(message) {
    const tbody = document.getElementById('clinic-table');

    if (!tbody) return;

    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-danger" style="text-align:center; padding:1rem;">
                ${escapeClinicHtml(message)}
            </td>
        </tr>
    `;
}

function renderClinicStatusBadge(status) {
    const value = String(status || 'pending');
    const normalized = value.toLowerCase();

    let className = 'status-badge status-on-leave';

    if (
        normalized.includes('completed') ||
        normalized.includes('done') ||
        normalized.includes('active')
    ) {
        className = 'status-badge status-active';
    }

    if (
        normalized.includes('progress') ||
        normalized.includes('ongoing')
    ) {
        className = 'status-badge status-inactive';
    }

    if (
        normalized.includes('cancel') ||
        normalized.includes('failed')
    ) {
        className = 'status-badge status-danger';
    }

    return `<span class="${className}">${escapeClinicHtml(formatClinicStatus(value))}</span>`;
}

function getClinicInputValue(id) {
    const element = document.getElementById(id);

    if (!element) return '';

    return String(element.value || '').trim();
}

function formatClinicDate(value) {
    if (!value) return '-';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toISOString().split('T')[0];
}

function formatClinicTime(value) {
    if (!value) return '-';

    return String(value).substring(0, 5);
}

function formatClinicStatus(status) {
    return String(status || '-')
        .replaceAll('_', ' ')
        .replaceAll('-', ' ')
        .replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });
}

function formatClinicSource(source) {
    if (source === 'activity') return 'Completed Activity';
    if (source === 'schedule') return 'Scheduled Clinic';
    return source || '-';
}

function shortenClinicMidwifeName(name) {
    if (!name) return '-';

    const parts = String(name).trim().split(/\s+/);

    if (parts.length === 1) {
        return parts[0];
    }

    return parts[0].charAt(0) + '. ' + parts[parts.length - 1];
}

function escapeClinicHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}