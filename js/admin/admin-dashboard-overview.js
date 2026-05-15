let adminDashboardActivityChart = null;
let adminDashboardPerformanceChart = null;
let dashboardAreaRecords = [];

document.addEventListener('DOMContentLoaded', function () {
    loadAdminDashboardOverview();
});

window.loadAdminDashboardOverview = function () {
    fetch('../php/admin/get_dashboard_overview.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.text();
        })
        .then(function (text) {
            console.log('Dashboard overview raw response:', text);

            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error('Invalid dashboard overview JSON:', text);
                return;
            }

            if (!data.success) {
                console.error(data.message || 'Failed to load dashboard overview.');
                return;
            }

            renderDashboardSummary(data.summary || {});
            renderDashboardAdmin(data.admin || {});
            renderDashboardAreas(data.areas || []);
            renderDashboardRecentActivities(data.recent_activities || []);
            renderDashboardTopMidwives(data.top_midwives || []);
            renderDashboardWeeklyChart(data.weekly_overview || []);
            renderDashboardPerformanceChart(data.performance_summary || {});
        })
        .catch(function (error) {
            console.error('Dashboard overview error:', error);
        });
};

function renderDashboardAdmin(admin) {
    setDashboardText('dashboard-admin-office', admin.moh_office || '-');

    const adminNameElement = document.querySelector('.admin-name');

    if (adminNameElement) {
        adminNameElement.textContent = admin.full_name || 'Admin';
    }
}

function renderDashboardSummary(summary) {
    setDashboardText('top-total-midwives', summary.active_midwives ?? 0);
    setDashboardText('top-today-activities', summary.today_activities ?? 0);
    setDashboardText('top-coverage-rate', (summary.coverage_rate ?? 0) + '%');

    setDashboardText('sc-home-visits', summary.home_visits_today ?? 0);
    setDashboardText('sc-vaccinations', summary.vaccinations ?? 0);
    setDashboardText('sc-clinic-sessions', summary.clinic_sessions ?? 0);
    setDashboardText('sc-pending-tasks', summary.pending_tasks ?? 0);
}

function renderDashboardAreas(areas) {
    const container = document.getElementById('dashboard-area-cards');

    if (!container) return;

    if (!areas || areas.length === 0) {
        container.innerHTML = `
            <div class="text-center" style="padding:2rem; color:var(--text-muted);">
                No duty areas found for this MOH office.
            </div>
        `;
        return;
    }

    container.innerHTML = areas.map(function (area) {
        const icon = getAreaIcon(area.area);

        return `
            <div class="area-card" data-area="${escapeDashboardAttribute(area.area)}" onclick="selectArea('${escapeDashboardAttribute(area.area)}')">
                <div class="area-card-icon">
                    <i class="fas fa-${icon}"></i>
                </div>

                <div class="area-card-title">
                    ${escapeDashboardHtml(area.area)}
                </div>

                <div class="area-card-count">
                    ${escapeDashboardHtml(area.appointments_today ?? 0)} appointments today
                </div>

                <div style="margin-top:0.4rem; font-size:0.8rem; color:var(--text-muted);">
                    ${escapeDashboardHtml(area.active_midwives ?? 0)} active midwives
                </div>
            </div>
        `;
    }).join('');
}

window.selectArea = function (areaName) {
    const panel = document.getElementById('areaDetailsPanel');
    const title = document.getElementById('areaTitle');
    const tbody = document.getElementById('areaActivitiesBody');

    document.querySelectorAll('#dashboard-area-cards .area-card').forEach(function (card) {
        card.classList.toggle('selected', card.getAttribute('data-area') === areaName);
    });

    if (panel) {
        panel.style.display = 'block';
    }

    if (title) {
        title.textContent = areaName + ' Area Schedule';
    }

    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center" style="padding:2rem;">
                    <i class="fas fa-spinner fa-spin"></i> Loading area activities...
                </td>
            </tr>
        `;
    }

    fetch('../php/admin/get_area_stats.php?area=' + encodeURIComponent(areaName), {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.text();
        })
        .then(function (text) {
            console.log('Dashboard selected area raw response:', text);

            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error('Invalid selected area JSON:', text);
                renderDashboardAreaActivitiesError('Invalid JSON response from PHP.');
                return;
            }

            if (!data.success) {
                renderDashboardAreaActivitiesError(data.message || 'Failed to load area activities.');
                return;
            }

            dashboardAreaRecords = data.data.recent_activities || [];
            renderDashboardAreaActivities(dashboardAreaRecords);
        })
        .catch(function (error) {
            console.error('Selected area error:', error);
            renderDashboardAreaActivitiesError('Network error while loading area activities.');
        });
};

function renderDashboardAreaActivities(records) {
    const tbody = document.getElementById('areaActivitiesBody');

    if (!tbody) return;

    if (!records || records.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center" style="padding:2rem;">
                    No activities found for this area.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = records.slice(0, 8).map(function (record) {
        return `
            <tr>
                <td>
                    <strong>${escapeDashboardHtml(formatDashboardTime(record.start_time))}</strong>
                    <br>
                    <small style="color:var(--text-muted);">
                        ${escapeDashboardHtml(formatDashboardDate(record.activity_date))}
                    </small>
                </td>

                <td>
                    ${escapeDashboardHtml(record.type_name || '-')}
                    <br>
                    <small>${escapeDashboardHtml(record.patient_name || record.description || '-')}</small>
                </td>

                <td>
                    ${escapeDashboardHtml(record.location || '-')}
                </td>

                <td>
                    ${renderDashboardStatusBadge(record.status)}
                </td>

                <td>
                    <button
                        type="button"
                        class="btn btn-sm btn-info"
                        onclick="viewDashboardAreaActivity('${escapeDashboardAttribute(record.record_key)}')"
                    >
                        View
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

window.viewDashboardAreaActivity = function (recordKey) {
    const record = dashboardAreaRecords.find(function (item) {
        return String(item.record_key) === String(recordKey);
    });

    if (!record) {
        alert('Activity not found.');
        return;
    }

    alert(
        'Activity Details\n\n' +
        'Activity: ' + valueOrDash(record.type_name) + '\n' +
        'Date: ' + valueOrDash(record.activity_date) + '\n' +
        'Time: ' + valueOrDash(record.start_time) + '\n' +
        'Midwife: ' + valueOrDash(record.midwife_name) + '\n' +
        'Patient: ' + valueOrDash(record.patient_name) + '\n' +
        'Location: ' + valueOrDash(record.location) + '\n' +
        'Status: ' + valueOrDash(record.status) + '\n' +
        'Description: ' + valueOrDash(record.description)
    );
};

function renderDashboardAreaActivitiesError(message) {
    const tbody = document.getElementById('areaActivitiesBody');

    if (!tbody) return;

    tbody.innerHTML = `
        <tr>
            <td colspan="5" class="text-center" style="padding:2rem; color:red;">
                ${escapeDashboardHtml(message)}
            </td>
        </tr>
    `;
}

function renderDashboardRecentActivities(records) {
    const container = document.getElementById('recent-activities-feed');

    if (!container) return;

    if (!records || records.length === 0) {
        container.innerHTML = `
            <div class="text-center" style="padding:2rem; color:var(--text-muted);">
                No recent activities found.
            </div>
        `;
        return;
    }

    container.innerHTML = records.map(function (record) {
        return `
            <div class="activity-item">
                <div class="activity-icon ${escapeDashboardHtml(record.icon_class || 'info')}">
                    <i class="fas fa-${escapeDashboardHtml(record.icon || 'clipboard-list')}"></i>
                </div>

                <div>
                    <strong>${escapeDashboardHtml(record.activity_type || '-')}</strong>
                    <p>${escapeDashboardHtml(shortName(record.midwife_name))} - ${escapeDashboardHtml(formatRelativeTime(record.created_at))}</p>
                </div>
            </div>
        `;
    }).join('');
}

function renderDashboardTopMidwives(midwives) {
    const container = document.getElementById('top-midwives-list');

    if (!container) return;

    if (!midwives || midwives.length === 0) {
        container.innerHTML = `
            <div class="text-center" style="padding:2rem; color:var(--text-muted);">
                No midwife performance data found.
            </div>
        `;
        return;
    }

    container.innerHTML = midwives.map(function (midwife) {
        const rate = Number(midwife.completion_rate || 0);
        const label = rate >= 90 ? 'Excellent' : rate >= 75 ? 'Good' : 'Needs Review';

        return `
            <div style="display:flex; justify-content:space-between; align-items:center; padding:1rem 0; border-bottom:1px solid #e9ecef;">
                <div>
                    <strong>${escapeDashboardHtml(midwife.full_name || '-')}</strong>
                    <p style="margin:0; color:var(--gray);">
                        ${escapeDashboardHtml(rate)}% completion rate
                    </p>
                </div>

                <div class="status-badge status-active">
                    ${escapeDashboardHtml(label)}
                </div>
            </div>
        `;
    }).join('');
}

function renderDashboardWeeklyChart(rows) {
    const canvas = document.getElementById('activityChart');

    if (!canvas || typeof Chart === 'undefined') return;

    if (adminDashboardActivityChart) {
        adminDashboardActivityChart.destroy();
    }

    adminDashboardActivityChart = new Chart(canvas, {
        type: 'line',
        data: {
            labels: rows.map(function (row) {
                return row.label;
            }),
            datasets: [
                {
                    label: 'Home Visits',
                    data: rows.map(function (row) {
                        return row.home_visits || 0;
                    }),
                    borderWidth: 3,
                    tension: 0.25
                },
                {
                    label: 'Vaccinations',
                    data: rows.map(function (row) {
                        return row.vaccinations || 0;
                    }),
                    borderWidth: 3,
                    tension: 0.25
                },
                {
                    label: 'Clinic Sessions',
                    data: rows.map(function (row) {
                        return row.clinic_sessions || 0;
                    }),
                    borderWidth: 3,
                    tension: 0.25
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function renderDashboardPerformanceChart(summary) {
    const canvas = document.getElementById('performanceChart');

    if (!canvas || typeof Chart === 'undefined') return;

    if (adminDashboardPerformanceChart) {
        adminDashboardPerformanceChart.destroy();
    }

    adminDashboardPerformanceChart = new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'In Progress', 'Pending', 'Scheduled'],
            datasets: [{
                data: [
                    summary.completed || 0,
                    summary.in_progress || 0,
                    summary.pending || 0,
                    summary.scheduled || 0
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function renderDashboardStatusBadge(status) {
    const value = String(status || 'scheduled').toLowerCase();

    let className = 'status-badge status-on-leave';
    let label = formatDashboardStatus(value);

    if (value === 'completed') {
        className = 'status-badge status-active';
    }

    if (value === 'in_progress' || value === 'in-progress') {
        className = 'status-badge status-inactive';
        label = 'In Progress';
    }

    return `<span class="${className}">${escapeDashboardHtml(label)}</span>`;
}

function getAreaIcon(area) {
    const value = String(area || '').toLowerCase();

    if (value.includes('clinic') || value.includes('kahambilihena')) return 'hospital';
    if (value.includes('opathella')) return 'city';
    if (value.includes('ambalangoda')) return 'tree';

    return 'home';
}

function shortName(fullName) {
    if (!fullName) return '-';

    const parts = String(fullName).trim().split(/\s+/);

    if (parts.length === 1) {
        return parts[0];
    }

    return parts[0].charAt(0) + '. ' + parts[parts.length - 1];
}

function formatRelativeTime(value) {
    if (!value) return '-';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    const diffMs = Date.now() - date.getTime();
    const diffMinutes = Math.floor(diffMs / 60000);

    if (diffMinutes < 1) return 'just now';
    if (diffMinutes < 60) return diffMinutes + ' min ago';

    const diffHours = Math.floor(diffMinutes / 60);

    if (diffHours < 24) return diffHours + ' hours ago';

    const diffDays = Math.floor(diffHours / 24);

    return diffDays + ' days ago';
}

function formatDashboardDate(value) {
    if (!value) return '-';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString('en-GB');
}

function formatDashboardTime(value) {
    if (!value) return '-';

    return String(value).substring(0, 5);
}

function formatDashboardStatus(value) {
    return String(value || '-')
        .replaceAll('_', ' ')
        .replaceAll('-', ' ')
        .replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });
}

function setDashboardText(id, value) {
    const element = document.getElementById(id);

    if (element) {
        element.textContent = value;
    }
}

function valueOrDash(value) {
    return value && String(value).trim() !== '' ? String(value) : '-';
}

function escapeDashboardHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function escapeDashboardAttribute(value) {
    return escapeDashboardHtml(value);
}