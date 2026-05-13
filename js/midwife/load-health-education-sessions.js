document.addEventListener('DOMContentLoaded', function () {
    loadHealthEducationSessions();

    const healthEdForm = document.getElementById('healthEdForm');

    if (healthEdForm) {
        healthEdForm.addEventListener('submit', function (e) {
            e.preventDefault();
            submitHealthEducationSession(this);
        });
    }
});

let allHealthEducationSessions = [];

function loadHealthEducationSessions() {
    fetch('../php/midwife/get_health_education_sessions.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            if (!data.success) {
                console.error(data.message || 'Failed to load health education sessions.');
                allHealthEducationSessions = [];
                renderHealthEducationSessions();
                return;
            }

            allHealthEducationSessions = data.sessions || [];
            renderHealthEducationSessions();
        })
        .catch(function (error) {
            console.error('Health Education Load Error:', error);
            allHealthEducationSessions = [];
            renderHealthEducationSessions();
        });
}

function submitHealthEducationSession(form) {
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.innerHTML : '';

    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    }

    fetch(form.action, {
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

                const attendeesInput = form.querySelector('[name="attendees"]');
                const durationInput = form.querySelector('[name="duration_mins"]');

                if (attendeesInput) attendeesInput.value = 25;
                if (durationInput) durationInput.value = 45;

                loadHealthEducationSessions();
            }

            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(function (error) {
            console.error('Health Education Save Error:', error);
            alert('Server error while saving health education session.');

            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
}

function renderHealthEducationSessions() {
    renderHealthEducationStats();
    renderHealthEducationList();
}

function renderHealthEducationStats() {
    const today = new Date();
    const ninetyDaysAgo = new Date();
    ninetyDaysAgo.setDate(today.getDate() - 90);

    const sessions90Days = allHealthEducationSessions.filter(function (session) {
        const sessionDate = new Date(session.session_date);
        return sessionDate >= ninetyDaysAgo && sessionDate <= today;
    });

    const participantsReached = allHealthEducationSessions.reduce(function (sum, session) {
        return sum + Number(session.attendees || 0);
    }, 0);

    const upcomingCount = allHealthEducationSessions.filter(function (session) {
        return session.status === 'upcoming' || session.status === 'planned';
    }).length;

    setHealthEducationText('healthEdSessions90Days', sessions90Days.length);
    setHealthEducationText('healthEdParticipantsReached', participantsReached);
    setHealthEducationText('healthEdUpcomingCount', upcomingCount);
}

function renderHealthEducationList() {
    const container = document.getElementById('healthEducationSessionList');

    if (!container) {
        return;
    }

    if (allHealthEducationSessions.length === 0) {
        container.innerHTML = '<p class="text-muted">No health education sessions found.</p>';
        return;
    }

    container.innerHTML = allHealthEducationSessions.map(function (session) {
        const borderColor = session.status === 'upcoming' || session.status === 'planned'
            ? 'var(--secondary-green)'
            : 'var(--primary-blue)';

        return `
            <div style="display: flex; align-items: flex-start; padding: 1rem; border: 1px solid #e9ecef; border-radius: var(--radius-md); border-left: 4px solid ${borderColor};">
                <div style="min-width: 88px; font-weight: 600; color: var(--primary-blue);">
                    ${formatHealthEducationShortDate(session.session_date)}
                </div>

                <div style="flex: 1;">
                    <strong>${escapeHealthEducationHtml(formatHealthEducationTopic(session.topic))}</strong>

                    <div style="font-size: 0.9rem; color: var(--text-secondary); margin-top: 0.25rem;">
                        ${escapeHealthEducationHtml(session.venue || '-')}
                        · ${escapeHealthEducationHtml(session.attendees || 0)} participants
                        ${session.materials ? ' · ' + escapeHealthEducationHtml(session.materials) : ''}
                    </div>

                    ${session.outcomes ? `
                        <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.35rem;">
                            ${escapeHealthEducationHtml(session.outcomes)}
                        </div>
                    ` : ''}

                    <div style="margin-top: 0.5rem;">
                        <span class="topic-pill">${escapeHealthEducationHtml(formatHealthEducationStatus(session.status))}</span>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function setHealthEducationText(id, value) {
    const element = document.getElementById(id);

    if (element) {
        element.textContent = value;
    }
}

function formatHealthEducationShortDate(dateString) {
    if (!dateString) return '-';

    const date = new Date(dateString);

    return date.toLocaleDateString('en-US', {
        day: '2-digit',
        month: 'short'
    });
}

function formatHealthEducationTopic(value) {
    const map = {
        nutrition: 'Maternal nutrition & iron',
        danger_signs: 'Pregnancy danger signs',
        newborn: 'Newborn care & warmth',
        immunization: 'Immunization schedule',
        fp: 'Family planning methods',
        dengue: 'Dengue & environmental health',
        mental: 'Perinatal mental wellbeing',
        other: 'Other'
    };

    return map[value] || value || '-';
}

function formatHealthEducationStatus(value) {
    return String(value || '')
        .replaceAll('_', ' ')
        .replaceAll('-', ' ')
        .replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });
}

function escapeHealthEducationHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}