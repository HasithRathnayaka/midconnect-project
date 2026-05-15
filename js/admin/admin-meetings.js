let meetingSearchTimer = null;
let allAdminMeetingRecords = [];

document.addEventListener('DOMContentLoaded', function () {
    console.log('admin-meetings.js loaded');

    if (document.getElementById('meeting-table')) {
        loadMeetingActivities();
    }
});

window.debounceMeetings = function () {
    clearTimeout(meetingSearchTimer);

    meetingSearchTimer = setTimeout(function () {
        window.loadMeetingActivities();
    }, 350);
};

window.loadMeetingActivities = function () {
    console.log('loadMeetingActivities called');

    const tbody = document.getElementById('meeting-table');

    if (!tbody) {
        console.error('meeting-table tbody not found.');
        return;
    }

    const keyword = getMeetingValue('meetingKeyword');
    const dateFrom = getMeetingValue('meetingDateFrom');
    const dateTo = getMeetingValue('meetingDateTo');
    const status = getMeetingValue('meetingStatus');
    const midwifeId = getMeetingValue('meetingMidwife');

    const params = new URLSearchParams();

    if (keyword) params.append('keyword', keyword);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    if (status) params.append('status', status);
    if (midwifeId) params.append('midwife_id', midwifeId);

    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center" style="padding:2rem;">
                <i class="fas fa-spinner fa-spin"></i> Loading meetings...
            </td>
        </tr>
    `;

    fetch('../php/admin/get_meeting_activities.php?' + params.toString(), {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.text();
        })
        .then(function (text) {
            console.log('Meeting raw response:', text);

            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error('Invalid meeting JSON:', text);
                renderMeetingError('Invalid JSON response from PHP.');
                return;
            }

            if (!data.success) {
                renderMeetingError(data.message || 'Failed to load meetings.');
                return;
            }

            allAdminMeetingRecords = data.records || [];

            renderMeetingMidwifeOptions(data.midwives || []);
            renderMeetingRows(allAdminMeetingRecords);
        })
        .catch(function (error) {
            console.error('Meeting fetch error:', error);
            renderMeetingError('Network/server error while loading meetings.');
        });
};

function renderMeetingRows(records) {
    const tbody = document.getElementById('meeting-table');

    if (!tbody) return;

    if (!records || records.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center" style="padding:2rem;">
                    No meetings found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = records.map(function (record) {
        return `
            <tr>
                <td>
                    <strong>${escapeMeetingHtml(formatMeetingDate(record.meeting_date))}</strong>
                    <br>
                    <small style="color:var(--text-muted);">
                        ${escapeMeetingHtml(formatMeetingTime(record.start_time))}
                        ${record.end_time ? ' - ' + escapeMeetingHtml(formatMeetingTime(record.end_time)) : ''}
                    </small>
                </td>

                <td>
                    <strong>${escapeMeetingHtml(record.midwife_name || '-')}</strong>
                    <br>
                    <small style="color:var(--text-muted);">
                        ${escapeMeetingHtml(record.assigned_area || '-')}
                    </small>
                </td>

                <td>
                    <strong>${escapeMeetingHtml(formatMeetingLabel(record.meeting_topic || '-'))}</strong>
                    <br>
                    <small>
                        Source: ${escapeMeetingHtml(formatMeetingLabel(record.record_source || '-'))}
                        ${record.patient_name ? '<br>Related Person: ' + escapeMeetingHtml(record.patient_name) : ''}
                        ${record.description ? '<br>Details: ' + escapeMeetingHtml(shortMeetingText(record.description, 80)) : ''}
                    </small>
                </td>

                <td>
                    <small>
                        ${escapeMeetingHtml(record.location || '-')}
                    </small>
                </td>

                <td>
                    ${renderMeetingStatus(record.status)}
                </td>

                <td>
                    <button
                        type="button"
                        class="btn btn-sm btn-info"
                        onclick="viewMeetingDetails('${escapeMeetingAttribute(record.record_key)}')"
                    >
                        View Details
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function renderMeetingMidwifeOptions(midwives) {
    const select = document.getElementById('meetingMidwife');

    if (!select) return;

    const selectedValue = select.value;

    let html = `<option value="">All Midwives</option>`;

    midwives.forEach(function (midwife) {
        html += `
            <option value="${escapeMeetingHtml(midwife.midwife_id)}">
                ${escapeMeetingHtml(midwife.full_name)} (${escapeMeetingHtml(midwife.employee_id || '-')})
            </option>
        `;
    });

    select.innerHTML = html;

    if (selectedValue) {
        select.value = selectedValue;
    }
}

window.viewMeetingDetails = function (recordKey) {
    const record = allAdminMeetingRecords.find(function (item) {
        return String(item.record_key) === String(recordKey);
    });

    if (!record) {
        alert('Meeting record not found.');
        return;
    }

    alert(
        'Meeting Details\n\n' +
        'Source: ' + valueOrDashMeeting(formatMeetingLabel(record.record_source)) + '\n' +
        'Topic: ' + valueOrDashMeeting(formatMeetingLabel(record.meeting_topic)) + '\n' +
        'Date: ' + valueOrDashMeeting(record.meeting_date) + '\n' +
        'Start Time: ' + valueOrDashMeeting(record.start_time) + '\n' +
        'End Time: ' + valueOrDashMeeting(record.end_time) + '\n' +
        'Midwife: ' + valueOrDashMeeting(record.midwife_name) + '\n' +
        'Area: ' + valueOrDashMeeting(record.assigned_area) + '\n' +
        'Location: ' + valueOrDashMeeting(record.location) + '\n' +
        'Related Person: ' + valueOrDashMeeting(record.patient_name) + '\n' +
        'Status: ' + valueOrDashMeeting(formatMeetingLabel(record.status)) + '\n' +
        'Priority: ' + valueOrDashMeeting(formatMeetingLabel(record.priority_level)) + '\n' +
        'Description: ' + valueOrDashMeeting(record.description) + '\n' +
        'Observations: ' + valueOrDashMeeting(record.observations) + '\n' +
        'Recommendations: ' + valueOrDashMeeting(record.recommendations)
    );
};

function renderMeetingStatus(status) {
    const value = String(status || 'scheduled').toLowerCase();

    let className = 'status-badge status-on-leave';
    let label = formatMeetingLabel(value);

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

    return `<span class="${className}">${escapeMeetingHtml(label)}</span>`;
}

function renderMeetingError(message) {
    const tbody = document.getElementById('meeting-table');

    if (!tbody) return;

    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center" style="padding:2rem; color:red;">
                ${escapeMeetingHtml(message)}
            </td>
        </tr>
    `;
}

function getMeetingValue(id) {
    const element = document.getElementById(id);
    return element ? String(element.value || '').trim() : '';
}

function formatMeetingDate(value) {
    if (!value) return '-';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString('en-GB');
}

function formatMeetingTime(value) {
    if (!value) return '-';
    return String(value).substring(0, 5);
}

function formatMeetingLabel(value) {
    return String(value || '-')
        .replaceAll('_', ' ')
        .replaceAll('-', ' ')
        .replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });
}

function shortMeetingText(value, limit) {
    const text = String(value || '');

    if (text.length <= limit) {
        return text;
    }

    return text.substring(0, limit) + '...';
}

function valueOrDashMeeting(value) {
    return value && String(value).trim() !== '' ? String(value) : '-';
}

function escapeMeetingHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function escapeMeetingAttribute(value) {
    return escapeMeetingHtml(value);
}