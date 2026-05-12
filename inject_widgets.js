const fs = require('fs');
const path = require('path');

const dashboardPath = path.join(__dirname, 'midwife', 'dashboard.php');
let content = fs.readFileSync(dashboardPath, 'utf8');

const htmlToInject = `
                <!-- Dynamic Dashboard Widgets -->
                <div class="row" style="margin-bottom: 2rem;" id="dynamic-widgets-container">
                    <!-- 1. Urgent Meetings -->
                    <div class="col-6" style="margin-bottom: 1.5rem;">
                        <div class="card" style="border-top: 4px solid var(--accent-red); height: 100%;">
                            <div class="card-header">
                                <h4 class="card-title" style="color: var(--accent-red);"><i class="fas fa-exclamation-triangle"></i> Urgent Meetings</h4>
                            </div>
                            <div class="card-body" id="widget-urgent" style="max-height: 250px; overflow-y: auto;">
                                <div style="text-align: center; color: var(--text-muted); padding: 1rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 2. Upcoming Clinics -->
                    <div class="col-6" style="margin-bottom: 1.5rem;">
                        <div class="card" style="border-top: 4px solid var(--primary-blue); height: 100%;">
                            <div class="card-header">
                                <h4 class="card-title" style="color: var(--primary-blue);"><i class="fas fa-hospital"></i> Upcoming Clinics</h4>
                            </div>
                            <div class="card-body" id="widget-clinics" style="max-height: 250px; overflow-y: auto;">
                                <div style="text-align: center; color: var(--text-muted); padding: 1rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 3. Time Table -->
                    <div class="col-6" style="margin-bottom: 1.5rem;">
                        <div class="card" style="border-top: 4px solid var(--secondary-green); height: 100%;">
                            <div class="card-header">
                                <h4 class="card-title" style="color: var(--secondary-green);"><i class="fas fa-clock"></i> Today's Time Table</h4>
                            </div>
                            <div class="card-body" id="widget-timetable" style="max-height: 250px; overflow-y: auto;">
                                <div style="text-align: center; color: var(--text-muted); padding: 1rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 4. Notifications -->
                    <div class="col-6" style="margin-bottom: 1.5rem;">
                        <div class="card" style="border-top: 4px solid var(--accent-orange); height: 100%;">
                            <div class="card-header">
                                <h4 class="card-title" style="color: var(--accent-orange);"><i class="fas fa-bell"></i> Notifications & Alerts</h4>
                            </div>
                            <div class="card-body" id="widget-notifications" style="max-height: 250px; overflow-y: auto;">
                                <div style="text-align: center; color: var(--text-muted); padding: 1rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                            </div>
                        </div>
                    </div>
                </div>
`;

// Regex to clear out the old Stats Row and Recent Activities divs
// It starts at <!-- Stats Row --> and ends right before <!-- Log Activity Section --> or just before </div> \n </div> \n <!-- Log Activity Section -->
const rx = /<!-- Stats Row -->[\s\S]*?<!-- Recent Activities -->[\s\S]*?<\/div>[\s]*<\/div>[\s]*<\/div>[\s]*(?=<\/div>\s*<!-- Log Activity Section -->)/;
const replacementContent = htmlToInject;

content = content.replace(rx, htmlToInject);

const jsToInject = `
        function loadDashboardWidgets() {
            fetch('../php/get_dashboard_widgets.php?midwife_id=1')
                .then(res => res.json())
                .then(data => {
                    if(!data.success) return;
                    const d = data.data;

                    // Urgent Meetings
                    const wu = document.getElementById('widget-urgent');
                    if (d.urgent_meetings.length === 0) {
                        wu.innerHTML = '<div style="text-align: center; color: var(--text-muted); padding: 1rem;">No urgent meetings.</div>';
                    } else {
                        wu.innerHTML = d.urgent_meetings.map(m => \`
                            <div style="padding: 0.75rem; border-bottom: 1px solid #e9ecef;">
                                <div style="font-weight: 600; color: var(--text-primary);">\${m.description}</div>
                                <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">
                                    <i class="fas fa-calendar-alt"></i> \${m.scheduled_date} \${m.start_time} | <i class="fas fa-map-marker-alt"></i> \${m.location}
                                </div>
                            </div>
                        \`).join('');
                    }

                    // Clinics
                    const wc = document.getElementById('widget-clinics');
                    if (d.upcoming_clinics.length === 0) {
                        wc.innerHTML = '<div style="text-align: center; color: var(--text-muted); padding: 1rem;">No upcoming clinics scheduled.</div>';
                    } else {
                        wc.innerHTML = d.upcoming_clinics.map(m => \`
                            <div style="padding: 0.75rem; border-bottom: 1px solid #e9ecef;">
                                <div style="font-weight: 600; color: var(--text-primary);">\${m.description}</div>
                                <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">
                                    <i class="fas fa-calendar-alt"></i> \${m.scheduled_date} \${m.start_time} | <i class="fas fa-map-marker-alt"></i> \${m.location}
                                </div>
                            </div>
                        \`).join('');
                    }

                    // Time Table
                    const wt = document.getElementById('widget-timetable');
                    if (d.timetable.length === 0) {
                        wt.innerHTML = '<div style="text-align: center; color: var(--text-muted); padding: 1rem;">No schedule for today.</div>';
                    } else {
                        wt.innerHTML = d.timetable.map(m => \`
                            <div style="padding: 0.75rem; border-left: 4px solid var(--secondary-green); margin-bottom: 0.5rem; background: #f8f9fa;">
                                <div style="font-weight: 600; color: var(--text-primary);">\${m.start_time} - \${m.estimated_end_time}</div>
                                <div style="font-size: 0.9rem; color: var(--text-dark); margin-top: 0.15rem;">\${m.description}</div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">\${m.patient_name ? m.patient_name + ' | ' : ''}\${m.location}</div>
                            </div>
                        \`).join('');
                    }

                    // Notifications
                    const wn = document.getElementById('widget-notifications');
                    if (d.notifications.length === 0) {
                        wn.innerHTML = '<div style="text-align: center; color: var(--text-muted); padding: 1rem;">No new notifications.</div>';
                    } else {
                        wn.innerHTML = d.notifications.map(n => \`
                            <div style="padding: 0.75rem; border-bottom: 1px solid #e9ecef; position: relative;">
                                <div style="font-weight: 600; color: var(--text-primary);">\${n.title}</div>
                                <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">\${n.message}</div>
                                <span class="badge" style="position: absolute; top: 0.75rem; right: 0.75rem; background: var(--accent-orange);">New</span>
                            </div>
                        \`).join('');
                    }
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    ['widget-urgent','widget-clinics','widget-timetable','widget-notifications'].forEach(id => {
                        document.getElementById(id).innerHTML = '<div style="text-align: center; color: red; padding: 1rem;">Failed to load data.</div>';
                    });
                });
        }
`;

// Insert the jsToInject before `function setupNavigation()`
content = content.replace('function setupNavigation() {', jsToInject + '\n        function setupNavigation() {');

// Execute loadDashboardWidgets() in DOMContentLoaded
const domLoadCall = `
            applyHashSection();
            loadDashboardWidgets();
`;
content = content.replace('applyHashSection();', domLoadCall);

fs.writeFileSync(dashboardPath, content, 'utf8');

console.log("Widgets injected successfully!");
