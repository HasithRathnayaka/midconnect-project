document.addEventListener('DOMContentLoaded', function () {
    setupCounselingForm();
    loadCounselingSessions();
});

let allCounselingSessions = [];

function setupCounselingForm() {
    const form = document.getElementById('counselingForm');

    if (!form) {
        return;
    }

    setDefaultCounselingDateTime();

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.innerHTML : '';

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        }

        fetch('../php/midwife/create_counseling_session.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                alert(data.message);

                if (data.success) {
                    form.reset();
                    setDefaultCounselingDateTime();
                    loadCounselingSessions();
                }

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(function (error) {
                console.error('Counseling Save Error:', error);
                alert('Server error while saving counseling session.');

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
    });
}

function loadCounselingSessions() {
    fetch('../php/midwife/get_counseling_sessions.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            console.log('Counseling sessions response:', data);

            if (!data.success) {
                console.error(data.message || 'Failed to load counseling sessions.');
                allCounselingSessions = [];
                renderCounselingSessions();
                return;
            }

            allCounselingSessions = data.sessions || [];
            renderCounselingSessions();
        })
        .catch(function (error) {
            console.error('Counseling Load Error:', error);
            allCounselingSessions = [];
            renderCounselingSessions();
        });
}

function renderCounselingSessions() {
    renderCounselingStats();
    renderCounselingTable();
}

function renderCounselingStats() {
    const now = new Date();
    const currentMonth = now.getMonth();
    const currentYear = now.getFullYear();

    const sessionsThisMonth = allCounselingSessions.filter(function (session) {
        const sessionDate = parseMysqlDateTime(session.session_datetime);

        return sessionDate &&
            sessionDate.getMonth() === currentMonth &&
            sessionDate.getFullYear() === currentYear;
    }).length;

    const followupsScheduled = allCounselingSessions.filter(function (session) {
        return session.followup === 'yes' || session.followup === 'referral';
    }).length;

    setCounselingText('counselingSessionsThisMonth', sessionsThisMonth);
    setCounselingText('counselingFollowupsScheduled', followupsScheduled);
}

function renderCounselingTable() {
    const tbody = document.getElementById('counselingSessionTableBody');

    if (!tbody) {
        return;
    }

    if (allCounselingSessions.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-muted">No counseling sessions found.</td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = allCounselingSessions.map(function (session) {
        return `
            <tr>
                <td>${escapeCounselingHtml(formatCounselingDateTime(session.session_datetime))}</td>
                <td>${escapeCounselingHtml(formatCounselingFocus(session.focus))}</td>
                <td>${escapeCounselingHtml(formatCounselingLocation(session.location_type))}</td>
                <td>${escapeCounselingHtml(session.duration_mins)} min</td>
                <td>
                    <span class="${getCounselingFollowupBadgeClass(session.followup)}">
                        ${escapeCounselingHtml(formatCounselingFollowup(session.followup))}
                    </span>
                </td>
            </tr>
        `;
    }).join('');
}

function setDefaultCounselingDateTime() {
    const input = document.querySelector('#counselingForm input[name="session_datetime"]');

    if (!input || input.value) {
        return;
    }

    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());

    input.value = now.toISOString().slice(0, 16);
}

function parseMysqlDateTime(value) {
    if (!value) {
        return null;
    }

    return new Date(String(value).replace(' ', 'T'));
}

function formatCounselingDateTime(value) {
    const date = parseMysqlDateTime(value);

    if (!date || Number.isNaN(date.getTime())) {
        return '-';
    }

    return date.toLocaleString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatCounselingFocus(value) {
    const map = {
        antenatal: 'Antenatal care & birth planning',
        postnatal: 'Postnatal & recovery',
        breastfeeding: 'Breastfeeding & nutrition',
        family_planning: 'Family planning',
        mental_health: 'Mental health & emotional support',
        gbv: 'Gender-based violence / safety',
        other: 'Other'
    };

    return map[value] || value || '-';
}

function formatCounselingLocation(value) {
    const map = {
        clinic: 'MOH Clinic',
        home: 'Home Visit',
        phone: 'Telephone',
        community: 'Community Center'
    };

    return map[value] || value || '-';
}

function formatCounselingFollowup(value) {
    const map = {
        no: 'None',
        yes: 'Scheduled',
        referral: 'Referral'
    };

    return map[value] || value || '-';
}

function getCounselingFollowupBadgeClass(value) {
    if (value === 'yes') {
        return 'status-badge status-active';
    }

    if (value === 'referral') {
        return 'status-badge status-warning';
    }

    return 'status-badge';
}

function setCounselingText(id, value) {
    const element = document.getElementById(id);

    if (element) {
        element.textContent = value;
    }
}

function escapeCounselingHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}