<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MOH Admin Dashboard - MidConnect</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            position: relative;
            height: 300px;
            margin: 1rem 0;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 0.875rem;
        }
        
        .activity-icon.success { background-color: var(--secondary-light-green); color: white; }
        .activity-icon.warning { background-color: var(--accent-orange); color: white; }
        .activity-icon.info { background-color: var(--accent-teal); color: white; }
        .activity-icon.danger { background-color: var(--accent-red); color: white; }
        
        .midwife-card {
            border-left: 4px solid var(--primary-blue);
            transition: var(--transition-medium);
        }
        
        .midwife-card:hover {
            border-left-color: var(--secondary-green);
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-active { background-color: #d4edda; color: #155724; }
        .status-inactive { background-color: #f8d7da; color: #721c24; }
        .status-on-leave { background-color: #fff3cd; color: #856404; }
        
        .quick-stats {
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-teal));
            color: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .quick-stats h2,
        .quick-stats h3,
        .quick-stats p {
            color: white;
        }
        
        .filter-bar {
            background: var(--white);
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1rem;
            box-shadow: var(--shadow-light);
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">
                <img src="../logoimage.png" alt="MidConnect Logo">
                <span>MidConnect - Admin</span>
            </div>
            <nav>
                <ul class="nav-menu">
                    <li><a href="#" class="admin-name">Dr. Sarah Johnson</a></li>
                    <li><a href="#" id="notifications"><i class="fas fa-bell"></i> <span class="badge">3</span></a></li>
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
                    <li><a href="#notifications-panel"><i class="fas fa-bell"></i> Notifications</a></li>
                    <li><a href="#settings"><i class="fas fa-cog"></i> System Settings</a></li>
                </ul>
            </div>
        </div>

        <div class="content-with-sidebar">
            <!-- Dashboard Overview -->
            <div id="dashboard" class="content-section">
                <div class="quick-stats">
                    <div class="row">
                        <div class="col-3">
                            <h3>Welcome Back!</h3>
                            <p>MOH Colombo 01 Office</p>
                        </div>
                        <div class="col-9">
                            <div class="row">
                                <div class="col-4 text-center">
                                    <h2>24</h2>
                                    <p>Active Midwives</p>
                                </div>
                                <div class="col-4 text-center">
                                    <h2>156</h2>
                                    <p>Activities Today</p>
                                </div>
                                <div class="col-4 text-center">
                                    <h2>98%</h2>
                                    <p>Coverage Rate</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Key Metrics -->
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-number">89</div>
                        <div class="stat-label">Home Visits Today</div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-number">45</div>
                        <div class="stat-label">Vaccinations</div>
                    </div>
                    <div class="stat-card info">
                        <div class="stat-number">22</div>
                        <div class="stat-label">Clinic Sessions</div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-number">12</div>
                        <div class="stat-label">Pending Reports</div>
                    </div>
                </div>

                <div class="row">
                    <!-- Activity Chart -->
                    <div class="col-8">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Weekly Activity Overview</h4>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="activityChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="col-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Recent Activities</h4>
                            </div>
                            <div class="card-body">
                                <div class="activity-item">
                                    <div class="activity-icon success">
                                        <i class="fas fa-home"></i>
                                    </div>
                                    <div>
                                        <strong>Home Visit Completed</strong>
                                        <p>M. Perera - 2 hours ago</p>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon info">
                                        <i class="fas fa-syringe"></i>
                                    </div>
                                    <div>
                                        <strong>Vaccination Session</strong>
                                        <p>K. Silva - 3 hours ago</p>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon warning">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div>
                                        <strong>Report Submitted</strong>
                                        <p>A. Fernando - 4 hours ago</p>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon success">
                                        <i class="fas fa-comments"></i>
                                    </div>
                                    <div>
                                        <strong>Counseling Session</strong>
                                        <p>D. Jayawardene - 5 hours ago</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Summary -->
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Performance Summary</h4>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="performanceChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Top Performing Midwives</h4>
                            </div>
                            <div class="card-body">
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; border-bottom: 1px solid #e9ecef;">
                                    <div>
                                        <strong>Madhavi Perera</strong>
                                        <p style="margin: 0; color: var(--gray);">95% completion rate</p>
                                    </div>
                                    <div class="status-badge status-active">Excellent</div>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; border-bottom: 1px solid #e9ecef;">
                                    <div>
                                        <strong>Kumari Silva</strong>
                                        <p style="margin: 0; color: var(--gray);">92% completion rate</p>
                                    </div>
                                    <div class="status-badge status-active">Very Good</div>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; border-bottom: 1px solid #e9ecef;">
                                    <div>
                                        <strong>Anura Fernando</strong>
                                        <p style="margin: 0; color: var(--gray);">88% completion rate</p>
                                    </div>
                                    <div class="status-badge status-active">Good</div>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0;">
                                    <div>
                                        <strong>Dilani Jayawardene</strong>
                                        <p style="margin: 0; color: var(--gray);">85% completion rate</p>
                                    </div>
                                    <div class="status-badge status-active">Good</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Midwife Management -->
            <div id="midwives" class="content-section" style="display: none;">
                <div class="filter-bar">
                    <div class="row">
                        <div class="col-3">
                            <input type="text" class="form-control" placeholder="Search midwives..." id="searchMidwives">
                        </div>
                        <div class="col-3">
                            <select class="form-control form-select" id="filterStatus">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="on-leave">On Leave</option>
                            </select>
                        </div>
                        <div class="col-3">
                            <select class="form-control form-select" id="filterArea">
                                <option value="">All Areas</option>
                                <option value="area1">Colombo Central</option>
                                <option value="area2">Colombo North</option>
                                <option value="area3">Colombo South</option>
                            </select>
                        </div>
                        <div class="col-3">
                            <button class="btn btn-primary" onclick="addNewMidwife()">
                                <i class="fas fa-plus"></i> Add Midwife
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row" id="midwivesGrid">
                    <div class="col-6">
                        <div class="card midwife-card">
                            <div class="card-body">
                                <div class="d-flex justify-between align-center">
                                    <div>
                                        <h5>Madhavi Perera</h5>
                                        <p>Employee ID: MW001</p>
                                        <p>Area: Colombo Central</p>
                                    </div>
                                    <div class="text-right">
                                        <div class="status-badge status-active">Active</div>
                                        <div class="mt-2">
                                            <button class="btn btn-info btn-sm" onclick="viewMidwifeDetails('MW001')">View</button>
                                            <button class="btn btn-warning btn-sm" onclick="editMidwife('MW001')">Edit</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card midwife-card">
                            <div class="card-body">
                                <div class="d-flex justify-between align-center">
                                    <div>
                                        <h5>Kumari Silva</h5>
                                        <p>Employee ID: MW002</p>
                                        <p>Area: Colombo North</p>
                                    </div>
                                    <div class="text-right">
                                        <div class="status-badge status-active">Active</div>
                                        <div class="mt-2">
                                            <button class="btn btn-info btn-sm" onclick="viewMidwifeDetails('MW002')">View</button>
                                            <button class="btn btn-warning btn-sm" onclick="editMidwife('MW002')">Edit</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Monitoring -->
            <div id="activities" class="content-section" style="display: none;">
                <h2>Activity Monitoring</h2>
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Real-time Activity Feed</h4>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Midwife</th>
                                    <th>Activity Type</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>14:30</td>
                                    <td>M. Perera</td>
                                    <td>Home Visit</td>
                                    <td>Kollupitiya</td>
                                    <td><span class="status-badge status-active">Completed</span></td>
                                    <td>
                                        <button class="btn btn-info btn-sm">View Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>13:45</td>
                                    <td>K. Silva</td>
                                    <td>Vaccination</td>
                                    <td>Clinic Center</td>
                                    <td><span class="status-badge status-active">Completed</span></td>
                                    <td>
                                        <button class="btn btn-info btn-sm">View Details</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Adding New Midwife -->
    <div class="modal" id="addMidwifeModal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h4>Add New Midwife</h4>
                <button type="button" class="modal-close" onclick="closeAddMidwifeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addMidwifeForm">
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="fullname" required>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Employee ID</label>
                            <input type="text" class="form-control" name="employee_id" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Assigned Area</label>
                            <select class="form-control form-select" name="area" required>
                                <option value="">Select Area</option>
                                <option value="area1">Colombo Central</option>
                                <option value="area2">Colombo North</option>
                                <option value="area3">Colombo South</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-success">Add Midwife</button>
                    <button type="button" class="btn btn-secondary" onclick="closeAddMidwifeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/page-transitions.js"></script>
    <script src="../js/theme-toggle.js"></script>
    <script>
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
            setupNavigation();
            checkAuthentication();
        });

        function checkAuthentication() {
            const adminUser = localStorage.getItem('admin_user');
            if (!adminUser) {
                window.location.href = '../admin-login.html';
                return;
            }
            
            const user = JSON.parse(adminUser);
            document.querySelector('.admin-name').textContent = user.name || 'Admin User';
        }

        function setupNavigation() {
            const menuLinks = document.querySelectorAll('.sidebar-menu a');
            const contentSections = document.querySelectorAll('.content-section');
            const SECTION_TRANSITION_MS = 200;
            let currentSection = Array.from(contentSections).find(section =>
                window.getComputedStyle(section).display !== 'none'
            ) || contentSections[0];

            menuLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetSectionId = this.getAttribute('href').substring(1);
                    const nextSection = document.getElementById(targetSectionId);

                    if (!nextSection || nextSection === currentSection) {
                        return;
                    }
                    
                    // Remove active class from all links
                    menuLinks.forEach(l => l.classList.remove('active'));
                    // Add active class to clicked link
                    this.classList.add('active');

                    if (currentSection) {
                        currentSection.classList.remove('section-slide-in');
                        currentSection.classList.add('section-slide-out');

                        setTimeout(() => {
                            currentSection.style.display = 'none';
                            currentSection.classList.remove('section-slide-out');

                            nextSection.style.display = 'block';
                            nextSection.classList.add('section-slide-in');
                            currentSection = nextSection;
                        }, SECTION_TRANSITION_MS);

                        return;
                    }

                    nextSection.style.display = 'block';
                    nextSection.classList.add('section-slide-in');
                    currentSection = nextSection;
                });
            });

            // Logout functionality
            document.getElementById('logout').addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to logout?')) {
                    localStorage.removeItem('admin_user');
                    window.location.href = '../admin-login.html';
                }
            });
        }

        function initializeCharts() {
            // Activity Chart
            const activityCtx = document.getElementById('activityChart').getContext('2d');
            new Chart(activityCtx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Home Visits',
                        data: [12, 19, 15, 17, 20, 18, 22],
                        borderColor: '#002E4F',
                        backgroundColor: 'rgba(0, 46, 79, 0.1)',
                        tension: 0.1
                    }, {
                        label: 'Vaccinations',
                        data: [8, 12, 10, 14, 16, 12, 18],
                        borderColor: '#00A699',
                        backgroundColor: 'rgba(0, 166, 153, 0.1)',
                        tension: 0.1
                    }, {
                        label: 'Clinic Sessions',
                        data: [5, 7, 6, 8, 9, 7, 10],
                        borderColor: '#00A699',
                        backgroundColor: 'rgba(0, 166, 153, 0.1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Performance Chart
            const performanceCtx = document.getElementById('performanceChart').getContext('2d');
            new Chart(performanceCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'In Progress', 'Pending', 'Overdue'],
                    datasets: [{
                        data: [65, 20, 10, 5],
                        backgroundColor: ['#00A699', '#002E4F', '#ffc107', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        function addNewMidwife() {
            document.getElementById('addMidwifeModal').classList.add('show');
        }

        function closeAddMidwifeModal() {
            document.getElementById('addMidwifeModal').classList.remove('show');
        }

        function viewMidwifeDetails(id) {
            // Implement view details functionality
            alert('View details for midwife: ' + id);
        }

        function editMidwife(id) {
            // Implement edit functionality
            alert('Edit midwife: ' + id);
        }

        // Handle add midwife form submission
        document.getElementById('addMidwifeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Handle form submission
            alert('New midwife added successfully!');
            closeAddMidwifeModal();
        });

        // Close modal when clicking outside
        document.getElementById('addMidwifeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddMidwifeModal();
            }
        });

        // Search and filter functionality
        document.getElementById('searchMidwives').addEventListener('input', function() {
            // Implement search functionality
            console.log('Searching for:', this.value);
        });

        document.getElementById('filterStatus').addEventListener('change', function() {
            // Implement status filter
            console.log('Filter by status:', this.value);
        });

        document.getElementById('filterArea').addEventListener('change', function() {
            // Implement area filter
            console.log('Filter by area:', this.value);
        });
    </script>
</body>
</html>