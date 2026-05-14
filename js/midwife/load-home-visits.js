document.addEventListener('DOMContentLoaded', function () {
    loadHomeVisitData();
});

let allHomeVisitsData = [];
let selectedHomeVisitArea = 'all';
let selectedHomeVisitDateFilter = 'all';

const fixedDutyAreas = [
    'uduthuththiripitiya',
    'kahabilihena',
    'opathella',
    'ambalangoda',

];

function loadHomeVisitData() {
    fetch('../php/midwife/get_home_visits.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            console.log('Home visits response:', data);

            if (!data.success) {
                console.error(data.message || 'Failed to load home visits');
                allHomeVisitsData = [];
                renderHomeVisitData();
                return;
            }

            allHomeVisitsData = data.visits || [];
            renderHomeVisitData();
        })
        .catch(error => {
            console.error('Home Visit Load Error:', error);
            allHomeVisitsData = [];
            renderHomeVisitData();
        });
}

function renderHomeVisitData() {
    renderHomeVisitAreaButtons();
    renderHomeVisitHeader();
    renderHomeVisitStats();
    renderScheduledHomeVisits();
    renderCompletedHomeVisits();
}

function renderHomeVisitAreaButtons() {
    const grid = document.getElementById('homeVisitAreaGrid');
    if (!grid) return;

    let html = `
        <div class="duty-area-btn ${selectedHomeVisitArea === 'all' ? 'active' : ''}" onclick="switchHomeVisitArea('all')">
            <i class="fas fa-list"></i>
            <h5>All Areas</h5>
            <div class="area-count">${allHomeVisitsData.length} visits</div>
        </div>
    `;

    html += fixedDutyAreas.map(area => {
        const count = allHomeVisitsData.filter(v => normalizeArea(v.duty_area) === normalizeArea(area)).length;

        return `
            <div class="duty-area-btn ${normalizeArea(selectedHomeVisitArea) === normalizeArea(area) ? 'active' : ''}" onclick="switchHomeVisitArea('${area}')">
                <i class="fas fa-map-marker-alt"></i>
                <h5>${formatAreaName(area)}</h5>
                <div class="area-count">${count} visits</div>
            </div>
        `;
    }).join('');

    grid.innerHTML = html;
}

function renderHomeVisitHeader() {
    const title = document.getElementById('homeVisitAreaTitle');
    const subtitle = document.getElementById('homeVisitAreaSubtitle');

    if (!title || !subtitle) return;

    const areaText = selectedHomeVisitArea === 'all'
        ? 'All Areas'
        : formatAreaName(selectedHomeVisitArea);

    title.innerHTML = `<i class="fas fa-map-marker-alt"></i> ${escapeHtml(areaText)} - Home Visits`;
    subtitle.textContent = `${getAreaFilteredVisits().length} visit(s) found`;
}

function renderHomeVisitStats() {
    const visits = getAreaFilteredVisits();
    const today = getTodayDate();

    setText('homeVisitTodayCount', visits.filter(v => v.visit_date === today).length);
    setText('homeVisitPendingCount', visits.filter(v => v.status !== 'completed').length);
    setText('homeVisitCompletedCount', visits.filter(v => v.status === 'completed').length);
    setText('homeVisitUrgentCount', visits.filter(v => v.priority === 'urgent' || v.priority === 'high').length);
}

function renderScheduledHomeVisits() {
    const container = document.getElementById('scheduledHomeVisitList');
    if (!container) return;

    const visits = getFilteredVisits().filter(v => v.status !== 'completed');

    if (visits.length === 0) {
        container.innerHTML = `<p class="text-muted">No scheduled visits found.</p>`;
        return;
    }

    container.innerHTML = visits.map(visit => {
        const priorityClass = visit.priority === 'urgent' || visit.priority === 'high'
            ? 'priority-high'
            : 'priority-normal';

        return `
            <div class="visit-item ${priorityClass}">
                <div class="visit-time">
                    <span class="time">${formatTime(visit.start_time)}</span>
                    <span class="duration">${escapeHtml(visit.duration_minutes || '-')} min</span>
                </div>

                <div class="visit-details">
                    <h5>${escapeHtml(visit.patient_name)}</h5>

                    <p class="address">
                        <i class="fas fa-map-marker-alt"></i>
                        ${escapeHtml(visit.address || '-')}
                    </p>

                    <p class="address">
                        <i class="fas fa-location-dot"></i>
                        Duty Area: ${escapeHtml(formatAreaName(visit.duty_area || '-'))}
                    </p>

                    <p class="visit-type">
                        <span class="badge ${getVisitTypeBadgeClass(visit.visit_type)}">${formatVisitType(visit.visit_type)}</span>
                        <span class="badge ${getPriorityBadgeClass(visit.priority)}">${formatPriority(visit.priority)}</span>
                    </p>

                    <p class="notes">${escapeHtml(visit.reason || visit.notes || '')}</p>
                </div>

                <div class="visit-actions">
                    <button class="btn btn-success btn-sm" onclick="completeHomeVisit(${visit.id})">
                        <i class="fas fa-check"></i> Complete
                    </button>

                    <button class="btn btn-info btn-sm" onclick="viewHomeVisitDetails(${visit.id})">
                        <i class="fas fa-eye"></i> View Details
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

function renderCompletedHomeVisits() {
    const container = document.getElementById('completedHomeVisitList');
    if (!container) return;

    const visits = getFilteredVisits().filter(v => v.status === 'completed');

    if (visits.length === 0) {
        container.innerHTML = `<p class="text-muted">No completed visits found.</p>`;
        return;
    }

    container.innerHTML = visits.map(visit => {
        return `
            <div class="completed-visit-item">
                <div class="visit-timestamp">
                    <span class="date">${formatDate(visit.visit_date)}</span>
                    <span class="time">${formatTime(visit.start_time)}</span>
                </div>

                <div class="visit-summary">
                    <h5>${escapeHtml(visit.patient_name)}</h5>
                    <p class="visit-type">${formatVisitType(visit.visit_type)}</p>
                    <p><strong>Duty Area:</strong> ${escapeHtml(formatAreaName(visit.duty_area || '-'))}</p>
                    <p class="outcome">
                        <span class="status-badge status-success">Completed</span>
                        ${escapeHtml(visit.notes || visit.reason || '')}
                    </p>
                </div>
            </div>
        `;
    }).join('');
}

function switchHomeVisitArea(area) {
    selectedHomeVisitArea = area;
    renderHomeVisitData();
}

function filterHomeVisitsByDate(filterValue) {
    selectedHomeVisitDateFilter = filterValue;
    renderScheduledHomeVisits();
    renderCompletedHomeVisits();
}

function completeHomeVisit(visitId) {
    if (!confirm('Mark this home visit as completed?')) return;

    const formData = new FormData();
    formData.append('visit_id', visitId);

    fetch('../php/midwife/complete_home_visit.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            alert(data.message);

            if (data.success) {
                loadHomeVisitData();
            }
        })
        .catch(error => {
            console.error('Complete Home Visit Error:', error);
            alert('Server error while completing home visit.');
        });
}

function viewHomeVisitDetails(visitId) {
    const visit = allHomeVisitsData.find(v => Number(v.id) === Number(visitId));

    if (!visit) {
        alert('Visit details not found.');
        return;
    }

    const detailsCard = document.getElementById('home-visit-details-record');
    const detailsBody = document.getElementById('home-visit-details-body');

    if (!detailsCard || !detailsBody) return;

    detailsCard.style.display = 'block';

    detailsBody.innerHTML = `
        <p><strong>Patient:</strong> ${escapeHtml(visit.patient_name)}</p>
        <p><strong>Contact:</strong> ${escapeHtml(visit.contact_number || '-')}</p>
        <p><strong>Address:</strong> ${escapeHtml(visit.address || '-')}</p>
        <p><strong>Duty Area:</strong> ${escapeHtml(formatAreaName(visit.duty_area || '-'))}</p>
        <p><strong>Date:</strong> ${formatDate(visit.visit_date)}</p>
        <p><strong>Time:</strong> ${formatTime(visit.start_time)}</p>
        <p><strong>Duration:</strong> ${escapeHtml(visit.duration_minutes || '-')} min</p>
        <p><strong>Status:</strong> ${escapeHtml(visit.status || '-')}</p>
        <p><strong>Visit Type:</strong> ${formatVisitType(visit.visit_type)}</p>
        <p><strong>Priority:</strong> ${formatPriority(visit.priority)}</p>
        <p><strong>Reason:</strong> ${escapeHtml(visit.reason || '-')}</p>
        <p><strong>Notes:</strong> ${escapeHtml(visit.notes || '-')}</p>
    `;
}

function getFilteredVisits() {
    return applyDateFilter(getAreaFilteredVisits());
}

function getAreaFilteredVisits() {
    if (selectedHomeVisitArea === 'all') {
        return allHomeVisitsData;
    }

    return allHomeVisitsData.filter(v => normalizeArea(v.duty_area) === normalizeArea(selectedHomeVisitArea));
}

function applyDateFilter(visits) {
    if (selectedHomeVisitDateFilter === 'all') return visits;

    const today = getTodayDate();

    if (selectedHomeVisitDateFilter === 'today') {
        return visits.filter(v => v.visit_date === today);
    }

    return visits;
}

function normalizeArea(value) {
    return String(value || '').trim().toLowerCase();
}

function formatAreaName(value) {
    const areaMap = {
        'uduthuththiripitiya': 'Uduthuththiripitiya',
        'kahabilihena': 'Kahabilihena',
        'opathella': 'Opathella',
        'ambalangoda': 'Ambalangoda',
     
    };

    return areaMap[normalizeArea(value)] || String(value || '-');
}

function setText(id, value) {
    const element = document.getElementById(id);
    if (element) element.textContent = value;
}

function getTodayDate() {
    return new Date().toISOString().split('T')[0];
}

function formatTime(timeString) {
    if (!timeString) return '-';
    return String(timeString).substring(0, 5);
}

function formatDate(dateString) {
    if (!dateString) return '-';

    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatVisitType(value) {
    return String(value || '')
        .replaceAll('-', ' ')
        .replaceAll('_', ' ')
        .replace(/\b\w/g, char => char.toUpperCase());
}

function formatPriority(value) {
    return String(value || 'normal')
        .replaceAll('-', ' ')
        .replaceAll('_', ' ')
        .replace(/\b\w/g, char => char.toUpperCase());
}

function getVisitTypeBadgeClass(type) {
    if (type === 'postnatal') return 'badge-urgent';
    if (type === 'antenatal') return 'badge-success';
    if (type === 'routine') return 'badge-primary';
    return 'badge-info';
}

function getPriorityBadgeClass(priority) {
    if (priority === 'urgent') return 'badge-urgent';
    if (priority === 'high') return 'badge-warning';
    return 'badge-info';
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}


function switchVisitTab(tabName, event) {
    if (event) {
        event.preventDefault();
    }

    const scheduledTab = document.getElementById('scheduled-visits');
    const completedTab = document.getElementById('completed-visits');

    if (!scheduledTab || !completedTab) {
        console.error('Visit tab containers not found');
        return;
    }

    // Hide both tabs first
    scheduledTab.style.display = 'none';
    completedTab.style.display = 'none';

    // Remove active class from both tab buttons
    const tabLinks = document.querySelectorAll('#home-visits .nav-tabs .nav-link');
    tabLinks.forEach(link => {
        link.classList.remove('active');
    });

    // Show selected tab
    if (tabName === 'scheduled') {
        scheduledTab.style.display = 'block';

        const scheduledLink = document.querySelector('#home-visits .nav-tabs .nav-link[onclick*="scheduled"]');
        if (scheduledLink) {
            scheduledLink.classList.add('active');
        }
    }

    if (tabName === 'completed') {
        completedTab.style.display = 'block';

        const completedLink = document.querySelector('#home-visits .nav-tabs .nav-link[onclick*="completed"]');
        if (completedLink) {
            completedLink.classList.add('active');
        }
    }
}


function completeHomeVisit(visitId) {
    if (!visitId) {
        alert('Invalid visit ID.');
        return;
    }

    if (!confirm('Are you sure you want to mark this visit as completed?')) {
        return;
    }

    const formData = new FormData();
    formData.append('visit_id', visitId);

    fetch('../php/midwife/complete_home_visit.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            alert(data.message);

            if (data.success) {
                loadHomeVisitData(); // reload UI after completing
            }
        })
        .catch(error => {
            console.error('Complete Home Visit Error:', error);
            alert('Server error while completing home visit.');
        });
}