const fs = require('fs');
const path = require('path');

const dashPath = path.join(__dirname, 'admin', 'dashboard.html');

const newDashboard = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MOH Admin Dashboard - MidConnect</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container { position: relative; height: 280px; margin: 0.75rem 0; }
        .activity-item { display: flex; align-items: center; padding: 0.85rem 1rem; border-bottom: 1px solid #e9ecef; gap: 0.75rem; }
        .activity-item:last-child { border-bottom: none; }
        .activity-icon { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; flex-shrink: 0; }
        .activity-icon.success { background: #d4edda; color: #155724; }
        .activity-icon.warning { background: #fff3cd; color: #856404; }
        .activity-icon.info    { background: #d1ecf1; color: #0c5460; }
        .activity-icon.danger  { background: #f8d7da; color: #721c24; }
        .midwife-card { border-left: 4px solid var(--primary-blue); transition: border-left-color 0.2s; }
        .midwife-card:hover { border-left-color: var(--secondary-green); }
        .quick-stats { background: linear-gradient(135deg, var(--primary-blue), var(--accent-teal)); color: white; border-radius: var(--radius-lg); padding: 2rem; margin-bottom: 1.5rem; }
        .quick-stats h2, .quick-stats h3, .quick-stats p { color: white; }
        .filter-bar { background: var(--white); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1rem; box-shadow: var(--shadow-light); }
        .priority-critical { background: #c0392b !important; color: white !important; }
        .priority-high     { background: #e74c3c !important; color: white !important; }
        .type-pill { display: inline-block; padding: 0.2rem 0.55rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; background: var(--primary-blue); color: white; }
        .type-pill.HOME_VISIT         { background: #0056b3; }
        .type-pill.CLINIC_VISIT       { background: #117a8b; }
        .type-pill.VACCINATION        { background: #28a745; }
        .type-pill.COUNSELING         { background: #6f42c1; }
        .type-pill.HEALTH_EDUCATION   { background: #fd7e14; }
        .type-pill.EMERGENCY_RESPONSE { background: #dc3545; }
        .type-pill.PRENATAL_CARE      { background: #20c997; }
        .type-pill.POSTNATAL_CARE     { background: #e83e8c; }
        .type-pill.FAMILY_PLANNING    { background: #795548; }
        .type-pill.NUTRITION_COUNSELING { background: #009688; }
        .monitoring-table th { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.03em; background: var(--bg-secondary); }
        .monitoring-table td { font-size: 0.88rem; vertical-align: middle; }
        .top-mw-row { display: flex; justify-content: space-between; align-items: center; padding: 0.85rem 0; border-bottom: 1px solid #e9ecef; }
        .top-mw-row:last-child { border-bottom: none; }
        .stat-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .stat-badge.excellent { background: #d4edda; color: #155724; }
        .stat-badge.good      { background: #d1ecf1; color: #0c5460; }
        .stat-badge.needs-imp { background: #fff3cd; color: #856404; }
        @media (max-width: 768px) { .filter-bar .row > div { margin-bottom: 0.5rem; } }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">
                <img src="../images/logoimage.png" alt="MidConnect Logo">
                <span>MidConnect - Admin</span>
            </div>
            <nav>
                <ul class="nav-menu">
                    <li><a href="admin-profile.html" class="admin-name" id="admin-name-link"><i class="fas fa-user-circle"></i> Admin</a></li>
                    <li><a href="#" id="notifications"><i class="fas fa-bell"></i> <span class="badge" id="notif-badge">0</span></a></li>
                    <li><a href="#" id="logout" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <li><button type="button" class="theme-toggle-btn" aria-label="Toggle dark and light theme"><span aria-hidden="true">🌙</span><span>Dark Mode</span></button></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="main-content">
        <div class="sidebar">
            <div class="sidebar-menu">
                <ul>
                    <li><a href="#dashboard" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="#midwives"><i class="fas fa-users"></i> Midwife Management</a></li>
                    <li><a href="#activities"><i class="fas fa-clipboard-list"></i> Activity Monitoring</a></li>
                    <li><a href="#schedules"><i class="fas fa-calendar-alt"></i> Schedule Management</a></li>
                    <li><a href="#performance"><i class="fas fa-trophy"></i> Performance Tracking</a></li>
                    <li><a href="#settings"><i class="fas fa-cog"></i> System Settings</a></li>
                </ul>
            </div>
        </div>

        <div class="content-with-sidebar">

            <!-- ===== DASHBOARD OVERVIEW ===== -->
            <div id="dashboard" class="content-section">
                <div class="quick-stats">
                    <div class="row">
                        <div class="col-3">
                            <h3 id="admin-greeting">Welcome Back!</h3>
                            <p id="admin-office">MOH Office</p>
                        </div>
                        <div class="col-9">
                            <div class="row">
                                <div class="col-4 text-center">
                                    <h2 id="top-total-midwives"><i class="fas fa-spinner fa-spin"></i></h2>
                                    <p>Active Midwives</p>
                                </div>
                                <div class="col-4 text-center">
                                    <h2 id="top-today-activities"><i class="fas fa-spinner fa-spin"></i></h2>
                                    <p>Activities Today</p>
                                </div>
                                <div class="col-4 text-center">
                                    <h2 id="top-urgent"><i class="fas fa-spinner fa-spin"></i></h2>
                                    <p>Urgent / Pending</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stat Cards -->
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-number" id="sc-total-midwives"><i class="fas fa-spinner fa-spin"></i></div>
                        <div class="stat-label">Total Midwives</div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-number" id="sc-total-activities"><i class="fas fa-spinner fa-spin"></i></div>
                        <div class="stat-label">Total Activities</div>
                    </div>
                    <div class="stat-card info">
                        <div class="stat-number" id="sc-today-activities"><i class="fas fa-spinner fa-spin"></i></div>
                        <div class="stat-label">Today's Activities</div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-number" id="sc-urgent"><i class="fas fa-spinner fa-spin"></i></div>
                        <div class="stat-label">Pending / Urgent</div>
                    </div>
                </div>

                <div class="row" style="margin-top:1.5rem;">
                    <!-- Weekly Chart -->
                    <div class="col-8">
                        <div class="card">
                            <div class="card-header"><h4 class="card-title">Weekly Activity Breakdown (Last 7 Days)</h4></div>
                            <div class="card-body">
                                <div class="chart-container"><canvas id="activityChart"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <!-- Live Recent Activities Feed -->
                    <div class="col-4">
                        <div class="card">
                            <div class="card-header"><h4 class="card-title">Recent Activities</h4></div>
                            <div class="card-body" style="padding:0; max-height:300px; overflow-y:auto;" id="recent-activities-feed">
                                <div class="activity-item"><i class="fas fa-spinner fa-spin"></i>&nbsp;Loading...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top:1.5rem;">
                    <!-- Status Doughnut -->
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header"><h4 class="card-title">Activity Status Distribution</h4></div>
                            <div class="card-body">
                                <div class="chart-container"><canvas id="statusChart"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <!-- Top Midwives — live from DB -->
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header"><h4 class="card-title">Top Active Midwives (Last 30 Days)</h4></div>
                            <div class="card-body" id="top-midwives-list">
                                <div style="text-align:center;padding:1rem;color:var(--text-muted);"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== MIDWIFE MANAGEMENT ===== -->
            <div id="midwives" class="content-section" style="display:none;">
                <div class="filter-bar">
                    <div class="row">
                        <div class="col-4">
                            <input type="text" class="form-control" id="searchMidwives" placeholder="Search midwives...">
                        </div>
                        <div class="col-4">
                            <select class="form-control form-select" id="filterMwStatus">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-primary" onclick="document.getElementById('addMidwifeModal').classList.add('show')">
                                <i class="fas fa-plus"></i> Add Midwife
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row" id="midwivesGrid">
                    <div class="col-12 text-center" style="padding:2rem;color:var(--text-muted);">
                        <i class="fas fa-spinner fa-spin"></i> Loading midwives...
                    </div>
                </div>
            </div>

            <!-- ===== ACTIVITY MONITORING ===== -->
            <div id="activities" class="content-section" style="display:none;">
                <div class="d-flex justify-between align-center" style="margin-bottom:1.25rem;">
                    <h2>System Activity Monitoring</h2>
                    <button class="btn btn-primary" onclick="loadAdminActivities()"><i class="fas fa-sync"></i> Refresh</button>
                </div>

                <!-- Filter Bar -->
                <div class="filter-bar">
                    <div class="row">
                        <div class="col-3">
                            <input type="text" class="form-control" id="actKeyword" placeholder="Search patient, location, midwife..." oninput="debounceActivities()">
                        </div>
                        <div class="col-2">
                            <input type="date" class="form-control" id="actDateFrom" onchange="loadAdminActivities()">
                        </div>
                        <div class="col-2">
                            <input type="date" class="form-control" id="actDateTo" onchange="loadAdminActivities()">
                        </div>
                        <div class="col-2">
                            <select class="form-control form-select" id="actType" onchange="loadAdminActivities()">
                                <option value="">All Activity Types</option>
                                <option value="HOME_VISIT">Home Visit</option>
                                <option value="CLINIC_VISIT">Clinic Visit</option>
                                <option value="VACCINATION">Vaccination</option>
                                <option value="COUNSELING">Counseling Session</option>
                                <option value="HEALTH_EDUCATION">Health Education</option>
                                <option value="EMERGENCY_RESPONSE">Emergency Response</option>
                                <option value="PRENATAL_CARE">Prenatal Care</option>
                                <option value="POSTNATAL_CARE">Postnatal Care</option>
                                <option value="FAMILY_PLANNING">Family Planning</option>
                                <option value="NUTRITION_COUNSELING">Nutrition Counseling</option>
                            </select>
                        </div>
                        <div class="col-1">
                            <select class="form-control form-select" id="actStatus" onchange="loadAdminActivities()">
                                <option value="">All Status</option>
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <select class="form-control form-select" id="actMidwife" onchange="loadAdminActivities()">
                                <option value="">All Midwives</option>
                            </select>
                        </div>
                    </div>
                    <div class="row" style="margin-top:0.5rem;">
                        <div class="col-3">
                            <select class="form-control form-select" id="actSort" onchange="loadAdminActivities()">
                                <option value="latest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                                <option value="urgent">Urgent First</option>
                            </select>
                        </div>
                        <div class="col-9" style="display:flex;align-items:center;">
                            <span id="act-count-label" style="font-size:0.85rem;color:var(--text-muted);"></span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body" style="padding:0;">
                        <div style="overflow-x:auto;">
                            <table class="table monitoring-table" style="margin:0;">
                                <thead>
                                    <tr>
                                        <th>Date &amp; Time</th>
                                        <th>Midwife</th>
                                        <th>Activity Type</th>
                                        <th>Patient / Details</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="admin-activities-table">
                                    <tr><td colspan="8" class="text-center" style="padding:2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== SCHEDULE MANAGEMENT ===== -->
            <div id="schedules" class="content-section" style="display:none;">
                <h2>Schedule Management</h2>
                <p style="color:var(--text-muted);">View and manage scheduled activities for all midwives.</p>
                <div class="card">
                    <div class="card-body" id="schedules-body">
                        <div style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="fas fa-spinner fa-spin"></i> Loading schedules...</div>
                    </div>
                </div>
            </div>

            <!-- ===== PERFORMANCE TRACKING ===== -->
            <div id="performance" class="content-section" style="display:none;">
                <h2>Performance Tracking</h2>
                <div class="card">
                    <div class="card-body" id="performance-body">
                        <div style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="fas fa-spinner fa-spin"></i> Loading performance data...</div>
                    </div>
                </div>
            </div>

            <!-- ===== SYSTEM SETTINGS ===== -->
            <div id="settings" class="content-section" style="display:none;">
                <h2>System Settings</h2>
                <div class="card">
                    <div class="card-body">
                        <p style="color:var(--text-muted);">System configuration options will appear here.</p>
                    </div>
                </div>
            </div>

        </div><!-- end content-with-sidebar -->
    </div><!-- end main-content -->

    <!-- Add Midwife Modal -->
    <div class="modal" id="addMidwifeModal">
        <div class="modal-content" style="max-width:600px;">
            <div class="modal-header">
                <h4>Add New Midwife</h4>
                <button type="button" class="modal-close" onclick="document.getElementById('addMidwifeModal').classList.remove('show')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addMidwifeForm">
                <div class="row">
                    <div class="col-6"><div class="form-group"><label class="form-label">Full Name</label><input type="text" class="form-control" name="fullname" required></div></div>
                    <div class="col-6"><div class="form-group"><label class="form-label">Employee ID</label><input type="text" class="form-control" name="employee_id" required></div></div>
                </div>
                <div class="row">
                    <div class="col-6"><div class="form-group"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required></div></div>
                    <div class="col-6"><div class="form-group"><label class="form-label">Phone</label><input type="tel" class="form-control" name="phone" required></div></div>
                </div>
                <div class="row">
                    <div class="col-6"><div class="form-group"><label class="form-label">Assigned Area</label><input type="text" class="form-control" name="area" required></div></div>
                    <div class="col-6"><div class="form-group"><label class="form-label">Hire Date</label><input type="date" class="form-control" name="hire_date" required></div></div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-success">Add Midwife</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('addMidwifeModal').classList.remove('show')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Activity Detail Modal -->
    <div class="modal" id="activityDetailModal">
        <div class="modal-content" style="max-width:640px;">
            <div class="modal-header">
                <h4>Activity Record Details</h4>
                <button type="button" class="modal-close" onclick="document.getElementById('activityDetailModal').classList.remove('show')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="activity-detail-body" style="padding:1.5rem;">Loading...</div>
        </div>
    </div>

    <script src="../js/page-transitions.js"></script>
    <script src="../js/theme-toggle.js"></script>
    <script>
    // ============================================================
    // GLOBALS
    // ============================================================
    let activityChartInstance = null;
    let statusChartInstance   = null;
    let themeObserver         = null;
    let chartRefreshTimer     = null;
    let debounceTimer         = null;

    // ============================================================
    // BOOT
    // ============================================================
    document.addEventListener('DOMContentLoaded', function () {
        checkAuthentication();
        setupNavigation();
        setupThemeAwareCharts();
        loadAdminStats();
        loadAdminActivities();

        document.getElementById('addMidwifeModal').addEventListener('click', function(e){ if(e.target===this) this.classList.remove('show'); });
        document.getElementById('activityDetailModal').addEventListener('click', function(e){ if(e.target===this) this.classList.remove('show'); });
        document.getElementById('addMidwifeForm').addEventListener('submit', function(e){
            e.preventDefault();
            alert('Midwife record submitted (connect to save endpoint).');
            this.closest('.modal').classList.remove('show');
        });
    });

    // ============================================================
    // AUTH
    // ============================================================
    function checkAuthentication() {
        let adminUser = localStorage.getItem('admin_user');
        if (!adminUser) {
            // Dev fallback — remove in production
            localStorage.setItem('admin_user', JSON.stringify({ name: 'Dr. Sarah Johnson', moh_office: 'MOH Colombo 01' }));
            adminUser = localStorage.getItem('admin_user');
        }
        try {
            const u = JSON.parse(adminUser);
            const nameEl = document.getElementById('admin-name-link');
            if (nameEl) nameEl.innerHTML = '<i class="fas fa-user-circle"></i> ' + (u.name || 'Admin');
            const greetEl = document.getElementById('admin-greeting');
            if (greetEl) greetEl.textContent = 'Welcome, ' + (u.name || 'Admin');
            const officeEl = document.getElementById('admin-office');
            if (officeEl && u.moh_office) officeEl.textContent = u.moh_office;
        } catch(e) {}
    }

    // ============================================================
    // NAVIGATION
    // ============================================================
    function setupNavigation() {
        const links    = document.querySelectorAll('.sidebar-menu a');
        const sections = document.querySelectorAll('.content-section');
        const MS       = 180;
        let current    = Array.from(sections).find(s => getComputedStyle(s).display !== 'none') || sections[0];

        links.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const id   = this.getAttribute('href').substring(1);
                const next = document.getElementById(id);
                if (!next || next === current) return;

                links.forEach(l => l.classList.remove('active'));
                this.classList.add('active');

                if (current) {
                    current.classList.remove('section-slide-in');
                    current.classList.add('section-slide-out');
                    setTimeout(() => {
                        current.style.display = 'none';
                        current.classList.remove('section-slide-out');
                        next.style.display = 'block';
                        next.classList.add('section-slide-in');
                        current = next;
                        // Lazy-load section data
                        if (id === 'midwives')     loadMidwivesGrid();
                        if (id === 'performance')  loadPerformanceData();
                        if (id === 'activities')   loadAdminActivities();
                        if (id === 'schedules')    loadSchedulesData();
                    }, MS);
                }
            });
        });

        document.getElementById('logout').addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Logout?')) {
                localStorage.removeItem('admin_user');
                window.location.href = '../admin-login.html';
            }
        });
    }

    // ============================================================
    // STATS
    // ============================================================
    function loadAdminStats() {
        fetch('../php/get_admin_stats.php')
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                const d = data.data;

                // Top banner
                setText('top-total-midwives', d.total_midwives);
                setText('top-today-activities', d.activities_today);
                setText('top-urgent', d.urgent_pending);

                // Stat cards
                setText('sc-total-midwives', d.total_midwives);
                setText('sc-total-activities', d.total_activities);
                setText('sc-today-activities', d.activities_today);
                setText('sc-urgent', d.urgent_pending);

                // Charts
                buildWeeklyChart(d.weekly_breakdown);
                buildStatusChart(d.status_breakdown);

                // Top midwives
                renderTopMidwives(d.top_midwives);

                // Recent activities (last 5 from full list)
                loadRecentActivityFeed();

                // Populate midwife filter dropdown
                populateMidwifeDropdown(d.midwives_list);
            })
            .catch(err => console.error('Stats error:', err));
    }

    function setText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val !== undefined && val !== null ? val : '—';
    }

    // ============================================================
    // RECENT ACTIVITIES FEED (dashboard overview)
    // ============================================================
    function loadRecentActivityFeed() {
        fetch('../php/get_admin_activities.php?limit=8&sort=latest')
            .then(r => r.json())
            .then(data => {
                const feed = document.getElementById('recent-activities-feed');
                if (!data.success || !data.data.activities.length) {
                    feed.innerHTML = '<div class="activity-item" style="color:var(--text-muted);">No recent activities.</div>';
                    return;
                }
                const iconMap = {
                    HOME_VISIT:'home success', CLINIC_VISIT:'hospital-alt info', VACCINATION:'syringe info',
                    COUNSELING:'comments success', HEALTH_EDUCATION:'chalkboard-teacher warning',
                    EMERGENCY_RESPONSE:'ambulance danger', PRENATAL_CARE:'baby-carriage success',
                    POSTNATAL_CARE:'heart info', FAMILY_PLANNING:'venus-mars warning',
                    NUTRITION_COUNSELING:'apple-alt success'
                };
                feed.innerHTML = data.data.activities.map(a => {
                    const [icon, cls] = (iconMap[a.activity_type_code] || 'clipboard-list info').split(' ');
                    return \`<div class="activity-item">
                        <div class="activity-icon \${cls}"><i class="fas fa-\${icon}"></i></div>
                        <div>
                            <strong>\${a.activity_type_name}</strong>
                            <p style="margin:0;font-size:0.8rem;color:var(--text-muted);">\${a.midwife_name} · \${a.date}</p>
                        </div>
                    </div>\`;
                }).join('');
            })
            .catch(() => {});
    }

    // ============================================================
    // TOP MIDWIVES
    // ============================================================
    function renderTopMidwives(list) {
        const el = document.getElementById('top-midwives-list');
        if (!list || !list.length) {
            el.innerHTML = '<p style="color:var(--text-muted);padding:1rem;">No activity data yet.</p>';
            return;
        }
        el.innerHTML = list.map(m => {
            const rate = m.completion_rate ?? 0;
            const badge = rate >= 90 ? 'excellent' : rate >= 75 ? 'good' : 'needs-imp';
            const label = rate >= 90 ? 'Excellent' : rate >= 75 ? 'Good' : 'Needs Improvement';
            return \`<div class="top-mw-row">
                <div>
                    <strong>\${m.full_name}</strong>
                    <p style="margin:0;font-size:0.8rem;color:var(--text-muted);">\${m.assigned_area} · \${m.total_activities} activities · \${rate}% completion</p>
                </div>
                <span class="stat-badge \${badge}">\${label}</span>
            </div>\`;
        }).join('');
    }

    // ============================================================
    // MIDWIFE DROPDOWN POPULATION
    // ============================================================
    function populateMidwifeDropdown(list) {
        const sel = document.getElementById('actMidwife');
        if (!sel || !list) return;
        list.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.midwife_id;
            opt.textContent = m.full_name + ' (' + m.employee_id + ')';
            sel.appendChild(opt);
        });
    }

    // ============================================================
    // ADMIN ACTIVITIES TABLE
    // ============================================================
    function debounceActivities() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(loadAdminActivities, 500);
    }

    function loadAdminActivities() {
        const tbody = document.getElementById('admin-activities-table');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="8" class="text-center" style="padding:2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';

        const keyword  = (document.getElementById('actKeyword')   || {}).value || '';
        const dateFrom = (document.getElementById('actDateFrom')   || {}).value || '';
        const dateTo   = (document.getElementById('actDateTo')     || {}).value || '';
        const type     = (document.getElementById('actType')       || {}).value || '';
        const status   = (document.getElementById('actStatus')     || {}).value || '';
        const midwife  = (document.getElementById('actMidwife')    || {}).value || '';
        const sort     = (document.getElementById('actSort')       || {}).value || 'latest';

        let url = \`../php/get_admin_activities.php?limit=100&sort=\${sort}\`;
        if (keyword)  url += \`&keyword=\${encodeURIComponent(keyword)}\`;
        if (dateFrom) url += \`&date_from=\${dateFrom}\`;
        if (dateTo)   url += \`&date_to=\${dateTo}\`;
        if (type)     url += \`&activity_type=\${encodeURIComponent(type)}\`;
        if (status)   url += \`&status=\${status}\`;
        if (midwife)  url += \`&midwife_id=\${midwife}\`;

        fetch(url)
            .then(r => r.json())
            .then(data => {
                const lbl = document.getElementById('act-count-label');
                if (!data.success) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center" style="color:red;">Failed to load activities.</td></tr>';
                    return;
                }
                const acts = data.data.activities;
                if (lbl) lbl.textContent = \`Showing \${acts.length} of \${data.data.total} records\`;
                if (!acts.length) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center" style="padding:2rem;color:var(--text-muted);">No activities match your filters.</td></tr>';
                    return;
                }

                tbody.innerHTML = acts.map(a => {
                    const statusCls = a.status === 'completed' ? 'status-active'
                                    : a.status === 'pending'   ? 'status-on-leave'
                                    : 'status-inactive';

                    const priBadge = a.priority_level && a.priority_level !== 'normal' && a.priority_level !== ''
                        ? \`<span class="status-badge priority-\${a.priority_level === 'critical' ? 'critical' : 'high'}" style="display:inline-block;margin-top:3px;">\${a.priority_level.toUpperCase()}</span>\`
                        : '';

                    const desc   = a.description  ? a.description.substring(0,60) + (a.description.length > 60 ? '…' : '') : '';
                    const patient = a.patient_name  ? \`<strong>\${a.patient_name}</strong>\${a.patient_age ? ' ('+a.patient_age+'y)' : ''}<br>\` : '';

                    return \`<tr>
                        <td style="white-space:nowrap;">
                            <strong>\${a.date}</strong><br>
                            <small style="color:var(--text-muted);">\${a.start_time}\${a.end_time ? ' – '+a.end_time : ''}</small>
                        </td>
                        <td>
                            <strong>\${a.midwife_name}</strong><br>
                            <small style="color:var(--text-muted);">\${a.assigned_area}</small>
                        </td>
                        <td><span class="type-pill \${a.activity_type_code}">\${a.activity_type_name}</span></td>
                        <td>\${patient}<small>\${desc}</small></td>
                        <td><small>\${a.location || '—'}</small></td>
                        <td><span class="status-badge \${statusCls}">\${a.status}</span></td>
                        <td>\${priBadge || '<small style="color:var(--text-muted);">Normal</small>'}</td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="viewActivityDetail(\${a.activity_id})" title="View Details"><i class="fas fa-eye"></i></button>
                        </td>
                    </tr>\`;
                }).join('');
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="8" class="text-center" style="color:red;">Network error.</td></tr>';
            });
    }

    // ============================================================
    // ACTIVITY DETAIL MODAL
    // ============================================================
    function viewActivityDetail(id) {
        const modal = document.getElementById('activityDetailModal');
        const body  = document.getElementById('activity-detail-body');
        modal.classList.add('show');
        fetch('../php/get_admin_activities.php?limit=200&sort=latest')
            .then(r => r.json())
            .then(data => {
                if (!data.success) { body.innerHTML = 'Error loading record.'; return; }
                const a = data.data.activities.find(x => x.activity_id === id);
                if (!a) { body.innerHTML = 'Record not found.'; return; }
                body.innerHTML = \`
                    <table class="table" style="margin:0;">
                        <tr><td><strong>Date</strong></td><td>\${a.date} \${a.start_time}\${a.end_time ? ' – '+a.end_time : ''}</td></tr>
                        <tr><td><strong>Midwife</strong></td><td>\${a.midwife_name} (\${a.midwife_emp_id}) — \${a.assigned_area}</td></tr>
                        <tr><td><strong>Activity Type</strong></td><td>\${a.activity_type_name}</td></tr>
                        <tr><td><strong>Patient</strong></td><td>\${a.patient_name || '—'}\${a.patient_age ? ' ('+a.patient_age+'y)' : ''}</td></tr>
                        <tr><td><strong>Location</strong></td><td>\${a.location || '—'}</td></tr>
                        <tr><td><strong>Description</strong></td><td>\${a.description || '—'}</td></tr>
                        <tr><td><strong>Observations</strong></td><td>\${a.observations || '—'}</td></tr>
                        <tr><td><strong>Status</strong></td><td>\${a.status}</td></tr>
                        <tr><td><strong>Priority</strong></td><td>\${a.priority_level || 'normal'}</td></tr>
                        <tr><td><strong>Follow-up</strong></td><td>\${a.follow_up_required ? 'Yes — ' + (a.follow_up_date || 'date TBD') : 'No'}</td></tr>
                        <tr><td><strong>Logged At</strong></td><td>\${a.created_at}</td></tr>
                    </table>
                \`;
            })
            .catch(() => { body.innerHTML = 'Error loading record.'; });
    }

    // ============================================================
    // MIDWIVES GRID
    // ============================================================
    function loadMidwivesGrid() {
        fetch('../php/get_admin_stats.php')
            .then(r => r.json())
            .then(data => {
                const grid = document.getElementById('midwivesGrid');
                if (!data.success || !data.data.midwives_list.length) {
                    grid.innerHTML = '<div class="col-12 text-center" style="padding:2rem;color:var(--text-muted);">No midwives found.</div>';
                    return;
                }
                grid.innerHTML = data.data.midwives_list.map(m => \`
                    <div class="col-6">
                        <div class="card midwife-card">
                            <div class="card-body">
                                <div class="d-flex justify-between align-center">
                                    <div>
                                        <h5>\${m.full_name}</h5>
                                        <p>ID: \${m.employee_id}</p>
                                        <p>Area: \${m.assigned_area}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="status-badge status-active">Active</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                \`).join('');
            })
            .catch(err => console.error(err));
    }

    // ============================================================
    // PERFORMANCE DATA
    // ============================================================
    function loadPerformanceData() {
        fetch('../php/get_admin_stats.php')
            .then(r => r.json())
            .then(data => {
                const el = document.getElementById('performance-body');
                if (!data.success || !data.data.top_midwives.length) {
                    el.innerHTML = '<p style="color:var(--text-muted);">No performance data available.</p>';
                    return;
                }
                el.innerHTML = '<table class="table"><thead><tr><th>Midwife</th><th>Area</th><th>Activities (30d)</th><th>Completed</th><th>Rate</th></tr></thead><tbody>'
                    + data.data.top_midwives.map(m => {
                        const rate = m.completion_rate ?? 0;
                        const badge = rate >= 90 ? 'excellent' : rate >= 75 ? 'good' : 'needs-imp';
                        return \`<tr>
                            <td><strong>\${m.full_name}</strong></td>
                            <td>\${m.assigned_area}</td>
                            <td>\${m.total_activities}</td>
                            <td>\${m.completed}</td>
                            <td><span class="stat-badge \${badge}">\${rate}%</span></td>
                        </tr>\`;
                    }).join('')
                    + '</tbody></table>';
            })
            .catch(err => console.error(err));
    }

    // ============================================================
    // SCHEDULES DATA
    // ============================================================
    function loadSchedulesData() {
        fetch('../php/get_dashboard_widgets.php?midwife_id=0')
            .then(r => r.json())
            .then(data => {
                const el = document.getElementById('schedules-body');
                if (!data.success || !data.data.timetable || !data.data.timetable.length) {
                    el.innerHTML = '<p style="color:var(--text-muted);">No schedules for today.</p>';
                    return;
                }
                el.innerHTML = '<h4 style="margin-bottom:1rem;">Today\'s Schedules</h4>'
                    + data.data.timetable.map(s => \`
                        <div style="padding:0.75rem;border-left:4px solid var(--primary-blue);margin-bottom:0.75rem;background:var(--bg-secondary);border-radius:4px;">
                            <strong>\${s.start_time} – \${s.estimated_end_time}</strong> · \${s.description}<br>
                            <small style="color:var(--text-muted);">\${s.location}</small>
                        </div>
                    \`).join('');
            })
            .catch(err => console.error(err));
    }

    // ============================================================
    // CHARTS
    // ============================================================
    function setupThemeAwareCharts() {
        if (themeObserver) return;
        themeObserver = new MutationObserver(mutations => {
            const changed = mutations.some(m => m.type === 'attributes' && m.attributeName === 'data-theme');
            if (changed) {
                clearTimeout(chartRefreshTimer);
                chartRefreshTimer = setTimeout(loadAdminStats, 80);
            }
        });
        themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
    }

    function buildWeeklyChart(breakdown) {
        const ctx = document.getElementById('activityChart');
        if (!ctx) return;

        const isDark     = document.documentElement.getAttribute('data-theme') === 'dark';
        const textColor  = isDark ? '#cbd5e1' : '#6c757d';
        const gridColor  = isDark ? 'rgba(200,200,200,0.1)' : 'rgba(0,0,0,0.08)';

        if (activityChartInstance) activityChartInstance.destroy();

        // Build label array for last 7 days
        const days = [];
        for (let i = 6; i >= 0; i--) {
            const d = new Date();
            d.setDate(d.getDate() - i);
            days.push(d.toISOString().slice(0, 10));
        }

        const dayLabels = days.map(d => {
            const dt = new Date(d + 'T00:00:00');
            return dt.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
        });

        const dataMap = {};
        if (breakdown) breakdown.forEach(row => { dataMap[row.day] = row; });

        const extract = (field) => days.map(d => dataMap[d] ? (parseInt(dataMap[d][field]) || 0) : 0);

        activityChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dayLabels,
                datasets: [
                    { label: 'Home Visits',     data: extract('home_visits'),     backgroundColor: isDark ? '#60a5fa' : '#0056b3' },
                    { label: 'Vaccinations',     data: extract('vaccinations'),    backgroundColor: isDark ? '#2dd4bf' : '#28a745' },
                    { label: 'Clinic Visits',    data: extract('clinic_visits'),   backgroundColor: isDark ? '#f59e0b' : '#fd7e14' },
                    { label: 'Counseling',       data: extract('counseling'),      backgroundColor: isDark ? '#a78bfa' : '#6f42c1' },
                    { label: 'Emergencies',      data: extract('emergencies'),     backgroundColor: '#dc3545' },
                    { label: 'Health Education', data: extract('health_education'),backgroundColor: '#20c997' },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: textColor, boxWidth: 14, font: { size: 11 } } } },
                scales: {
                    x: { stacked: true, ticks: { color: textColor }, grid: { color: gridColor } },
                    y: { stacked: true, beginAtZero: true, ticks: { color: textColor, precision: 0 }, grid: { color: gridColor } }
                }
            }
        });
    }

    function buildStatusChart(breakdown) {
        const ctx = document.getElementById('statusChart');
        if (!ctx) return;
        if (statusChartInstance) statusChartInstance.destroy();

        const isDark    = document.documentElement.getAttribute('data-theme') === 'dark';
        const textColor = isDark ? '#cbd5e1' : '#6c757d';

        const labels = (breakdown || []).map(r => r.status);
        const values = (breakdown || []).map(r => parseInt(r.cnt));
        const colors = labels.map(s =>
            s === 'completed'   ? '#28a745' :
            s === 'pending'     ? '#ffc107' :
            s === 'in_progress' ? '#007bff' : '#dc3545'
        );

        statusChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: { labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 2 }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: textColor } } }
            }
        });
    }
    </script>

    <!-- Chatbot Widget -->
    <div id="chatbot" class="chatbot">
        <div class="chatbot-header">
            <h4>MidConnect Support</h4>
            <button id="chatbot-close" class="chatbot-close">&times;</button>
        </div>
        <div class="chatbot-messages" id="chatbot-messages"></div>
        <div class="chatbot-input-group">
            <input type="text" id="chatbot-input" placeholder="Type your message..." class="chatbot-input">
            <button id="chatbot-send" class="chatbot-send"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
    <button id="chatbot-toggle" class="chatbot-toggle"><i class="fas fa-comments"></i></button>

    <style>
        .chatbot-toggle{position:fixed;bottom:2rem;right:2rem;width:60px;height:60px;border-radius:50%;background:var(--accent-teal);color:white;border:none;font-size:1.5rem;cursor:pointer;box-shadow:0 4px 12px rgba(0,166,153,.4);z-index:99;transition:all .3s}
        .chatbot-toggle:hover{background:var(--secondary-dark-green);transform:scale(1.1)}
        .chatbot{position:fixed;bottom:5.5rem;right:2rem;width:350px;height:450px;background:var(--white);border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.15);display:none;flex-direction:column;z-index:100;overflow:hidden}
        .chatbot.active{display:flex}
        .chatbot-header{background:var(--accent-teal);color:white;padding:1rem;display:flex;justify-content:space-between;align-items:center}
        .chatbot-header h4{margin:0;font-size:1rem;color:white}
        .chatbot-close{background:none;border:none;color:white;font-size:1.5rem;cursor:pointer;padding:0}
        .chatbot-messages{flex:1;overflow-y:auto;padding:1rem;background:var(--bg-primary)}
        .chatbot-message{margin-bottom:1rem;display:flex}
        .chatbot-message.user{justify-content:flex-end}
        .chatbot-message-content{max-width:80%;padding:.75rem 1rem;border-radius:8px;word-wrap:break-word}
        .chatbot-message.bot .chatbot-message-content{background:#e9ecef;color:var(--text-primary)}
        .chatbot-message.user .chatbot-message-content{background:var(--accent-teal);color:white}
        .chatbot-input-group{display:flex;padding:1rem;border-top:1px solid #e9ecef;background:white;gap:.5rem}
        .chatbot-input{flex:1;border:1px solid #e9ecef;border-radius:6px;padding:.75rem;font-size:.9rem;font-family:inherit}
        .chatbot-input:focus{outline:none;border-color:var(--accent-teal)}
        .chatbot-send{background:var(--accent-teal);color:white;border:none;border-radius:6px;padding:.75rem 1rem;cursor:pointer}
    </style>
    <script>
        const chatbotToggle = document.getElementById('chatbot-toggle');
        const chatbotEl     = document.getElementById('chatbot');
        document.getElementById('chatbot-close').addEventListener('click', () => chatbotEl.classList.remove('active'));
        chatbotToggle.addEventListener('click', () => { chatbotEl.classList.toggle('active'); if(chatbotEl.classList.contains('active')) document.getElementById('chatbot-input').focus(); });
        function sendMsg(){
            const inp=document.getElementById('chatbot-input'), msg=inp.value.trim();
            if(!msg)return;
            const msgs=document.getElementById('chatbot-messages');
            msgs.innerHTML+=\`<div class="chatbot-message user"><div class="chatbot-message-content">\${msg}</div></div>\`;
            inp.value=''; msgs.scrollTop=msgs.scrollHeight;
            setTimeout(()=>{ msgs.innerHTML+=\`<div class="chatbot-message bot"><div class="chatbot-message-content">Thank you for your message. How can I assist?</div></div>\`; msgs.scrollTop=msgs.scrollHeight; },500);
        }
        document.getElementById('chatbot-send').addEventListener('click', sendMsg);
        document.getElementById('chatbot-input').addEventListener('keypress', e=>{ if(e.key==='Enter') sendMsg(); });
    </script>
</body>
</html>`;

fs.writeFileSync(dashPath, newDashboard, 'utf8');
console.log('Dashboard rebuilt. Lines:', newDashboard.split('\n').length);
