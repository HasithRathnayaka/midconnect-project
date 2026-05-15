let vaccinationSearchTimer = null;
let allVaccinationRecords = [];
let vaccinationMidwivesLoaded = false;

document.addEventListener('DOMContentLoaded', function () {
    loadVaccinationActivities();
});

function debounceVaccinations() {
    clearTimeout(vaccinationSearchTimer);

    vaccinationSearchTimer = setTimeout(function () {
        loadVaccinationActivities();
    }, 350);
}

function loadVaccinationActivities() {
    const keyword = getInputValue('vaccKeyword');
    const dateFrom = getInputValue('vaccDateFrom');
    const dateTo = getInputValue('vaccDateTo');
    const status = getInputValue('vaccStatus');
    const midwifeId = getInputValue('vaccMidwife');

    const params = new URLSearchParams();

    if (keyword !== '') params.append('keyword', keyword);
    if (dateFrom !== '') params.append('date_from', dateFrom);
    if (dateTo !== '') params.append('date_to', dateTo);
    if (status !== '') params.append('status', status);
    if (midwifeId !== '') params.append('midwife_id', midwifeId);

    const tbody = document.getElementById('vaccination-table');

    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center" style="padding:2rem;">
                    <i class="fas fa-spinner fa-spin"></i> Loading vaccinations...
                </td>
            </tr>
        `;
    }

    fetch('../php/admin/get_vaccination_activities.php?' + params.toString(), {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.text();
        })
        .then(function (text) {
            console.log('Admin vaccination raw response:', text);

            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error('Invalid vaccination JSON:', text);
                renderVaccinationError('PHP returned non-JSON response. Check php_errors.log.');
                return;
            }

            if (!data.success) {
                renderVaccinationError(data.message || 'Failed to load vaccination records.');
                return;
            }

            allVaccinationRecords = data.records || [];

            if (!vaccinationMidwivesLoaded) {
                renderVaccinationMidwifeFilter(data.midwives || []);
                vaccinationMidwivesLoaded = true;
            }

            renderVaccinationTable(allVaccinationRecords);
        })
        .catch(function (error) {
            console.error('Vaccination Load Error:', error);
            renderVaccinationError('Server error while loading vaccination records.');
        });
}

function renderVaccinationMidwifeFilter(midwives) {
    const select = document.getElementById('vaccMidwife');

    if (!select) return;

    const currentValue = select.value;

    let html = `<option value="">All Midwives</option>`;

    midwives.forEach(function (midwife) {
        html += `
            <option value="${escapeVaccHtml(midwife.midwife_id)}">
                ${escapeVaccHtml(midwife.full_name)} (${escapeVaccHtml(midwife.employee_id || '-')})
            </option>
        `;
    });

    select.innerHTML = html;

    if (currentValue) {
        select.value = currentValue;
    }
}

function renderVaccinationTable(records) {
    const tbody = document.getElementById('vaccination-table');

    if (!tbody) return;

    if (!records || records.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center" style="padding:2rem;">
                    No vaccination records found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = records.map(function (record) {
        const dateTimeHtml = `
            <strong>${formatVaccDate(record.vaccination_date)} ${formatVaccTime(record.vaccination_time)}</strong>
            <br>
            <small style="color:var(--text-muted);">
                ${formatVaccTime(record.vaccination_time)}
                ${record.duration_minutes ? ' | ' + escapeVaccHtml(record.duration_minutes) + ' mins' : ''}
            </small>
        `;

        const midwifeHtml = `
            <strong>${escapeVaccHtml(shortName(record.midwife_name))}</strong>
            <br>
            <small style="color:var(--text-muted);">
                ${escapeVaccHtml(record.assigned_area || '-')}
            </small>
        `;

        const patientHtml = `
            <strong>${escapeVaccHtml(record.patient_name || '-')}</strong>
            ${record.patient_age ? '(' + escapeVaccHtml(record.patient_age) + ')' : ''}
            <br>
            <small>
                Vaccine: ${escapeVaccHtml(record.vaccine_name || '-')}
                ${record.dose_number ? ' | Dose: ' + escapeVaccHtml(record.dose_number) : ''}
                ${record.mother_name ? '<br>Mother: ' + escapeVaccHtml(record.mother_name) : ''}
            </small>
        `;

        return `
            <tr>
                <td>${dateTimeHtml}</td>
                <td>${midwifeHtml}</td>
                <td>${patientHtml}</td>
                <td>
                    <small>
                        ${escapeVaccHtml(record.location || record.duty_area || '-')}
                    </small>
                </td>
                <td>${renderVaccStatusBadge(record.status)}</td>
                <td>
                    <button 
                        class="btn btn-sm btn-info" 
                        type="button"
                        onclick="viewVaccinationDetails(${Number(record.vaccination_id)})"
                    >
                        View Details
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function viewVaccinationDetails(vaccinationId) {
    const record = allVaccinationRecords.find(function (item) {
        return Number(item.vaccination_id) === Number(vaccinationId);
    });

    if (!record) {
        alert('Vaccination record not found.');
        return;
    }

    alert(
        'Vaccination Details\n\n' +
        'Patient: ' + valueOrDash(record.patient_name) + '\n' +
        'Mother: ' + valueOrDash(record.mother_name) + '\n' +
        'Age: ' + valueOrDash(record.patient_age) + '\n' +
        'Vaccine: ' + valueOrDash(record.vaccine_name) + '\n' +
        'Dose: ' + valueOrDash(record.dose_number) + '\n' +
        'Batch: ' + valueOrDash(record.batch_number) + '\n' +
        'Date: ' + formatVaccDate(record.vaccination_date) + '\n' +
        'Time: ' + formatVaccTime(record.vaccination_time) + '\n' +
        'Location: ' + valueOrDash(record.location) + '\n' +
        'Duty Area: ' + valueOrDash(record.duty_area) + '\n' +
        'Midwife: ' + valueOrDash(record.midwife_name) + '\n' +
        'Status: ' + valueOrDash(record.status) + '\n' +
        'Priority: ' + valueOrDash(record.priority) + '\n' +
        'Next Due Date: ' + formatVaccDate(record.next_due_date) + '\n' +
        'Contact: ' + valueOrDash(record.contact_number) + '\n' +
        'Address: ' + valueOrDash(record.address) + '\n' +
        'Notes: ' + valueOrDash(record.notes)
    );
}

function renderVaccinationError(message) {
    const tbody = document.getElementById('vaccination-table');

    if (!tbody) return;

    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center" style="padding:2rem; color: var(--accent-red);">
                ${escapeVaccHtml(message)}
            </td>
        </tr>
    `;
}

function renderVaccStatusBadge(status) {
    const normalized = String(status || '').toLowerCase();
    let className = 'status-badge status-active';
    let label = formatStatusLabel(status || 'pending');

    if (
        normalized === 'completed' ||
        normalized === 'administered' ||
        normalized === 'done'
    ) {
        className = 'status-badge status-active';
        label = 'Completed';
    } else if (
        normalized === 'pending' ||
        normalized === 'scheduled'
    ) {
        className = 'status-badge status-on-leave';
        label = formatStatusLabel(status);
    } else if (
        normalized === 'in_progress' ||
        normalized === 'in-progress' ||
        normalized === 'active'
    ) {
        className = 'status-badge status-inactive';
        label = 'In Progress';
    } else if (
        normalized === 'cancelled' ||
        normalized === 'canceled' ||
        normalized === 'missed'
    ) {
        className = 'status-badge status-inactive';
        label = formatStatusLabel(status);
    }

    return `<span class="${className}">${escapeVaccHtml(label)}</span>`;
}

function getInputValue(id) {
    const element = document.getElementById(id);
    return element ? String(element.value || '').trim() : '';
}

function formatVaccDate(value) {
    if (!value) return '-';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return escapeVaccHtml(value);
    }

    return date.toLocaleDateString('en-GB');
}

function formatVaccTime(value) {
    if (!value) return '-';

    return String(value).substring(0, 5);
}

function formatStatusLabel(value) {
    if (!value) return '-';

    return String(value)
        .replaceAll('_', ' ')
        .replaceAll('-', ' ')
        .replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });
}

function shortName(fullName) {
    if (!fullName) return '-';

    const parts = String(fullName).trim().split(/\s+/);

    if (parts.length === 1) {
        return parts[0];
    }

    return parts[0].charAt(0) + '. ' + parts.slice(1).join(' ');
}

function valueOrDash(value) {
    return value && String(value).trim() !== '' ? String(value) : '-';
}

function escapeVaccHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}