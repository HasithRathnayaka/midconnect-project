<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../admin-login.html');
    exit;
}

$adminId = $_SESSION['admin_id'] ?? null;
$adminName = $_SESSION['full_name'] ?? 'Admin';
$adminEmail = $_SESSION['email'] ?? '';
$adminMohOffice = $_SESSION['moh_office'] ?? '';
$adminPosition = $_SESSION['position'] ?? 'MOH Officer';
?>


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

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--radius-md);
            text-align: center;
            box-shadow: var(--shadow-light);
            border-top: 4px solid transparent;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card.success {
            border-top-color: var(--secondary-light-green);
        }

        .stat-card.info {
            border-top-color: var(--accent-teal);
        }

        .stat-card.warning {
            border-top-color: var(--accent-orange);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Area Cards Styling */
        .area-card {
            background: var(--white);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            box-shadow: var(--shadow-light);
            border-left: 4px solid var(--accent-teal);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .area-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-left-color: var(--secondary-green);
        }

        .area-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            color: var(--accent-teal);
        }

        .area-card-header i {
            font-size: 1.5rem;
            margin-right: 0.75rem;
        }

        .area-card-header h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .area-card-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .area-card-stats .stat-item {
            text-align: center;
        }

        .area-card-stats .stat-number {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1;
        }

        .area-card-stats .stat-label {
            display: block;
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        .area-card-footer {
            margin-top: auto;
            padding-top: 0.75rem;
            border-top: 1px solid #e9ecef;
        }

        .last-activity {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        /* Area Details Styling */
        .area-details-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            gap: 1rem;
        }

        .area-details-header h2 {
            margin: 0;
            color: var(--text-primary);
        }

        .area-stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .area-stat-item:last-child {
            border-bottom: none;
        }

        .area-stat-item .stat-label {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .area-stat-item .stat-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .activity-type-stat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
        }

        .activity-type-name {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .activity-type-count {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--accent-teal);
        }

        /* Area-based Activity Section Styling */
        .area-section {
            margin-bottom: 2rem;
        }

        .area-cards-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .area-card {
            flex: 1;
            min-width: 200px;
            background: var(--white);
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .area-card:hover {
            border-color: var(--accent-teal);
            box-shadow: 0 4px 12px rgba(0, 184, 148, 0.15);
            transform: translateY(-2px);
        }

        .area-card.selected {
            border-color: var(--accent-teal);
            background: linear-gradient(135deg, rgba(0, 184, 148, 0.05), rgba(0, 184, 148, 0.1));
            box-shadow: 0 4px 12px rgba(0, 184, 148, 0.2);
        }

        .area-card-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 60px;
        }

        .duty-area-btn i {
            font-size: 2rem;   /* exact size used */
        }

        .area-card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .area-card-count {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .area-details-panel {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .area-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .area-header h4 {
            margin: 0 0 0.5rem 0;
            color: var(--text-primary);
            font-size: 1.3rem;
            font-weight: 600;
        }

        .area-info {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .clinic-hours {
            color: var(--text-muted);
        }

        .contact-info {
            color: var(--accent-teal);
            font-weight: 500;
        }

        .area-activities-table {
            overflow-x: auto;
        }

        .area-activities-table .table {
            margin: 0;
            border-collapse: collapse;
        }

        .area-activities-table .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: var(--text-primary);
            border-bottom: 2px solid #e9ecef;
            padding: 1rem;
        }

        .area-activities-table .table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f3f4;
        }

        .area-activities-table .table tbody tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-badge.completed {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-badge.scheduled {
            background: #d1ecf1;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <!-- START: navbar.php -->
    <header class="header">
        <div class="container">
            <div class="header-left">
                <button class="hamburger-btn" id="hamburgerBtn" aria-label="Open menu">
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </button>
                <div class="logo">
                    <img src="../images/logoimage.png" alt="MidConnect Logo">
                    <span>MidConnect - Admin</span>
                </div>
            </div>
            <nav>
                <ul class="nav-menu">
                    <li><a href="admin-profile.html" class="admin-name"><i class="fas fa-user-circle"></i> Dr. Sarah Johnson</a></li>
                    <li><a href="#" id="notifications"><i class="fas fa-bell"></i> <span class="badge">3</span></a></li>
                    <li><a href="#" id="logout" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <li class="nav-actions">
                        <button type="button" class="theme-toggle-btn" aria-label="Toggle dark and light theme"><span aria-hidden="true">🌙</span><span>Dark Mode</span></button>
                        <button type="button" class="profile-link" id="navbarProfileBtn" aria-label="Open profile page">
                            <img src="../images/profile picture.png" alt="Profile" class="navbar-profile-img" id="navbar-profile-pic">
                        </button>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    <!-- END: navbar.php -->

    <div class="main-content">
        <!-- START: sidebar.php -->
        <div class="sidebar" id="sidebar">
            <button class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Close menu">&times;</button>
            <div class="sidebar-menu">
                <ul>
                    <li><a href="#dashboard" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="#areas"><i class="fas fa-map-marked-alt"></i> Areas</a></li>
                    <li><a href="#midwives"><i class="fas fa-users"></i> Midwives</a></li>
                    <li><a href="#activities"><i class="fas fa-clipboard-list"></i> Activities</a></li>
                    <li><a href="#clinics"><i class="fas fa-clinic-medical"></i> Clinics</a></li>
                    <li><a href="#vaccinations"><i class="fas fa-syringe"></i> Vaccinations</a></li>
                    <li><a href="#home-visits"><i class="fas fa-home"></i> Home Visits</a></li>
                    <li><a href="#counseling"><i class="fas fa-comments"></i> Counseling Sessions</a></li>
                    <li><a href="#health-education"><i class="fas fa-chalkboard-teacher"></i> Health Education Sessions</a></li>
                    <li><a href="#emergency"><i class="fas fa-ambulance"></i> Emergency Responses</a></li>
                    <li><a href="#meetings"><i class="fas fa-handshake"></i> Meetings</a></li>
                    <li><a href="#settings"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
            </div>
        </div>
        <!-- END: sidebar.php -->

        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

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
                                    <h2 id="top-total-midwives">24</h2>
                                    <p>Active Midwives</p>
                                </div>
                                <div class="col-4 text-center">
                                    <h2 id="top-today-activities">156</h2>
                                    <p>Activities Today</p>
                                </div>
                                <div class="col-4 text-center">
                                    <h2 id="top-coverage-rate">98%</h2>
                                    <p>Coverage Rate</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Key Metrics -->
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-number" id="sc-home-visits">89</div>
                        <div class="stat-label">Home Visits Today</div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-number" id="sc-vaccinations">45</div>
                        <div class="stat-label">Vaccinations</div>
                    </div>
                    <div class="stat-card info">
                        <div class="stat-number" id="sc-clinic-sessions">22</div>
                        <div class="stat-label">Clinic Sessions</div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-number" id="sc-pending-tasks">12</div>
                        <div class="stat-label">Pending Tasks</div>
                    </div>
                </div>

                <!-- Area-based Activity Section -->
                <div class="area-section" style="margin-top: 2rem;">
                    <h3 style="margin-bottom: 1.5rem; color: var(--text-primary);">Select Duty Area</h3>
                    
                    <!-- Area Cards -->
                    <div class="area-cards-container">
                        <div class="area-card" data-area="Uduthuththiripitiya" onclick="selectArea('Uduthuththiripitiya')">
                            <div class="area-card-icon">
                                <i class="fas fa-home"></i>
                            </div>
                            <div class="area-card-title">Uduthuththiripitiya</div>
                            <div class="area-card-count">2 appointments today</div>
                        </div>
                        
                        <div class="area-card" data-area="Kahambilihena" onclick="selectArea('Kahambilihena')">
                            <div class="area-card-icon">
                                <i class="fas fa-hospital"></i>
                            </div>
                            <div class="area-card-title">Kahambilihena</div>
                            <div class="area-card-count">3 appointments today</div>
                        </div>
                        
                        <div class="area-card" data-area="Opathella" onclick="selectArea('Opathella')">
                            <div class="area-card-icon">
                                <i class="fas fa-city"></i>
                            </div>
                            <div class="area-card-title">Opathella</div>
                            <div class="area-card-count">1 appointments today</div>
                        </div>
                        
                        <div class="area-card" data-area="Ambalangoda" onclick="selectArea('Ambalangoda')">
                            <div class="area-card-icon">
                                <i class="fas fa-tree"></i>
                            </div>
                            <div class="area-card-title">Ambalangoda</div>
                            <div class="area-card-count">4 appointments today</div>
                        </div>
                    </div>
                    
                    <!-- Area Details Panel -->
                    <div class="area-details-panel" id="areaDetailsPanel" style="display: none;">
                        <div class="area-header">
                            <h4 id="areaTitle">Uduthuththiripitiya Area Schedule</h4>
                            <div class="area-info">
                                <span class="clinic-hours">Clinic Hours: 8:00 AM - 4:00 PM</span>
                                <span class="contact-info">| Contact: +94 37 226 5432</span>
                            </div>
                        </div>
                        
                        <!-- Area Activities Table -->
                        <div class="area-activities-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Activity</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="areaActivitiesBody">
                                    <tr>
                                        <td>09:00 AM</td>
                                        <td>Home Visit - Postnatal Care</td>
                                        <td>Patient Residence</td>
                                        <td><span class="status-badge completed">Completed</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info">View</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>11:00 AM</td>
                                        <td>Vaccination - BCG</td>
                                        <td>Health Center</td>
                                        <td><span class="status-badge pending">Pending</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info">View</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>02:00 PM</td>
                                        <td>Clinic Consultation</td>
                                        <td>Main Clinic</td>
                                        <td><span class="status-badge scheduled">Scheduled</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info">View</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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
                            <div class="card-body" id="recent-activities-feed">
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
                            <div class="card-body" id="top-midwives-list">
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

            <!-- AREAS -->
            <div id="areas" class="content-section" style="display: none;">
                <h2 style="margin-bottom: 1.5rem;">Area Monitoring</h2>
                <div class="row">
                    <!-- Udathuththiripitiya Area -->
                    <div class="col-3">
                        <div class="area-card" onclick="openAreaDetails('Udathuththiripitiya')" style="cursor: pointer;">
                            <div class="area-card-header">
                                <i class="fas fa-map-marker-alt"></i>
                                <h4>Udathuththiripitiya</h4>
                            </div>
                            <div class="area-card-stats">
                                <div class="stat-item">
                                    <span class="stat-number" id="area-udathuththiripitiya-midwives">0</span>
                                    <span class="stat-label">Active Midwives</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number" id="area-udathuththiripitiya-activities">0</span>
                                    <span class="stat-label">Activities Today</span>
                                </div>
                            </div>
                            <div class="area-card-footer">
                                <span class="last-activity" id="area-udathuththiripitiya-last">No recent activity</span>
                            </div>
                        </div>
                    </div>

                    <!-- Kahambilihena Area -->
                    <div class="col-3">
                        <div class="area-card" onclick="openAreaDetails('Kahambilihena')" style="cursor: pointer;">
                            <div class="area-card-header">
                                <i class="fas fa-map-marker-alt"></i>
                                <h4>Kahambilihena</h4>
                            </div>
                            <div class="area-card-stats">
                                <div class="stat-item">
                                    <span class="stat-number" id="area-kahambilihena-midwives">0</span>
                                    <span class="stat-label">Active Midwives</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number" id="area-kahambilihena-activities">0</span>
                                    <span class="stat-label">Activities Today</span>
                                </div>
                            </div>
                            <div class="area-card-footer">
                                <span class="last-activity" id="area-kahambilihena-last">No recent activity</span>
                            </div>
                        </div>
                    </div>

                    <!-- Opathella Area -->
                    <div class="col-3">
                        <div class="area-card" onclick="openAreaDetails('Opathella')" style="cursor: pointer;">
                            <div class="area-card-header">
                                <i class="fas fa-map-marker-alt"></i>
                                <h4>Opathella</h4>
                            </div>
                            <div class="area-card-stats">
                                <div class="stat-item">
                                    <span class="stat-number" id="area-opathella-midwives">0</span>
                                    <span class="stat-label">Active Midwives</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number" id="area-opathella-activities">0</span>
                                    <span class="stat-label">Activities Today</span>
                                </div>
                            </div>
                            <div class="area-card-footer">
                                <span class="last-activity" id="area-opathella-last">No recent activity</span>
                            </div>
                        </div>
                    </div>

                    <!-- Ambalangoda Area -->
                    <div class="col-3">
                        <div class="area-card" onclick="openAreaDetails('Ambalangoda')" style="cursor: pointer;">
                            <div class="area-card-header">
                                <i class="fas fa-map-marker-alt"></i>
                                <h4>Ambalangoda</h4>
                            </div>
                            <div class="area-card-stats">
                                <div class="stat-item">
                                    <span class="stat-number" id="area-ambalangoda-midwives">0</span>
                                    <span class="stat-label">Active Midwives</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number" id="area-ambalangoda-activities">0</span>
                                    <span class="stat-label">Activities Today</span>
                                </div>
                            </div>
                            <div class="area-card-footer">
                                <span class="last-activity" id="area-ambalangoda-last">No recent activity</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AREA DETAILS -->
            <div id="area-details" class="content-section" style="display: none;">
                <div class="area-details-header">
                    <button class="btn btn-secondary" onclick="backToAreas()">
                        <i class="fas fa-arrow-left"></i> Back to Areas
                    </button>
                    <h2 id="area-details-title">Area Details</h2>
                </div>

                <div class="row">
                    <div class="col-8">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Recent Activities</h4>
                            </div>
                            <div class="card-body" style="padding: 0;">
                                <div style="overflow-x: auto;">
                                    <table class="table monitoring-table" style="margin: 0;">
                                        <thead>
                                            <tr>
                                                <th>Date & Time</th>
                                                <th>Midwife</th>
                                                <th>Activity Type</th>
                                                <th>Patient / Details</th>
                                                <th>Location</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="area-activities-table">
                                            <tr>
                                                <td colspan="7" class="text-center" style="padding: 2rem;">
                                                    <i class="fas fa-spinner fa-spin"></i> Loading...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Area Statistics</h4>
                            </div>
                            <div class="card-body">
                                <div class="area-stat-item">
                                    <span class="stat-label">Total Midwives:</span>
                                    <span class="stat-value" id="area-total-midwives">0</span>
                                </div>
                                <div class="area-stat-item">
                                    <span class="stat-label">Today's Activities:</span>
                                    <span class="stat-value" id="area-today-activities">0</span>
                                </div>
                                <div class="area-stat-item">
                                    <span class="stat-label">This Week:</span>
                                    <span class="stat-value" id="area-week-activities">0</span>
                                </div>
                                <div class="area-stat-item">
                                    <span class="stat-label">Completed:</span>
                                    <span class="stat-value" id="area-completed-activities">0</span>
                                </div>
                                <div class="area-stat-item">
                                    <span class="stat-label">Pending:</span>
                                    <span class="stat-value" id="area-pending-activities">0</span>
                                </div>
                            </div>
                        </div>

                        <div class="card" style="margin-top: 1rem;">
                            <div class="card-header">
                                <h4 class="card-title">Activity Breakdown</h4>
                            </div>
                            <div class="card-body">
                                <div id="area-activity-breakdown">
                                    <div class="activity-type-stat">
                                        <span class="activity-type-name">Home Visits:</span>
                                        <span class="activity-type-count" id="area-home-visits">0</span>
                                    </div>
                                    <div class="activity-type-stat">
                                        <span class="activity-type-name">Vaccinations:</span>
                                        <span class="activity-type-count" id="area-vaccinations">0</span>
                                    </div>
                                    <div class="activity-type-stat">
                                        <span class="activity-type-name">Clinic Visits:</span>
                                        <span class="activity-type-count" id="area-clinic-visits">0</span>
                                    </div>
                                    <div class="activity-type-stat">
                                        <span class="activity-type-name">Counseling:</span>
                                        <span class="activity-type-count" id="area-counseling">0</span>
                                    </div>
                                    <div class="activity-type-stat">
                                        <span class="activity-type-name">Health Education:</span>
                                        <span class="activity-type-count" id="area-health-education">0</span>
                                    </div>
                                    <div class="activity-type-stat">
                                        <span class="activity-type-name">Emergency:</span>
                                        <span class="activity-type-count" id="area-emergency">0</span>
                                    </div>
                                    <div class="activity-type-stat">
                                        <span class="activity-type-name">Meetings:</span>
                                        <span class="activity-type-count" id="area-meetings">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Midwife Management -->
           <!-- Midwife Management -->
<div id="midwives" class="content-section" style="display: none;">
    <div class="filter-bar">
        <div class="row">
            <div class="col-3">
                <input
                    type="text"
                    class="form-control"
                    placeholder="Search midwives..."
                    id="searchMidwives"
                >
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
                </select>
            </div>

            <div class="col-3">
                <!-- Existing add function/backend preserved -->
                <button class="btn btn-primary" onclick="addNewMidwife()">
                    <i class="fas fa-plus"></i> Add Midwife
                </button>
            </div>
        </div>
    </div>

    <div class="row" id="midwivesGrid">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-spinner fa-spin"></i> Loading midwives...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Midwife Modal -->
<div class="modal fade" id="viewMidwifeModal" tabindex="-1" role="dialog" aria-labelledby="viewMidwifeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content admin-midwife-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="viewMidwifeModalLabel">Midwife Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-6">
                        <p><strong>Full Name:</strong> <span id="viewMidwifeFullName">-</span></p>
                        <p><strong>Employee ID:</strong> <span id="viewMidwifeEmployeeId">-</span></p>
                        <p><strong>Email:</strong> <span id="viewMidwifeEmail">-</span></p>
                        <p><strong>Phone:</strong> <span id="viewMidwifePhone">-</span></p>
                    </div>

                    <div class="col-6">
                        <p><strong>Assigned Area:</strong> <span id="viewMidwifeArea">-</span></p>
                        <p><strong>MOH Office:</strong> <span id="viewMidwifeMohOffice">-</span></p>
                        <p><strong>Hire Date:</strong> <span id="viewMidwifeHireDate">-</span></p>
                        <p><strong>Status:</strong> <span id="viewMidwifeStatus">-</span></p>
                    </div>
                </div>

                <hr>

                <p><strong>Address:</strong></p>
                <p id="viewMidwifeAddress">-</p>

                <p><strong>Last Login:</strong> <span id="viewMidwifeLastLogin">-</span></p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Midwife Modal -->
<div class="modal fade" id="editMidwifeModal" tabindex="-1" role="dialog" aria-labelledby="editMidwifeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content admin-midwife-modal">
            <form id="editMidwifeForm" action="../php/admin/update_midwife.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMidwifeModalLabel">Edit Midwife</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="midwife_id" id="editMidwifeId">

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="full_name" id="editMidwifeFullName" required>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Employee ID</label>
                                <input type="text" class="form-control" id="editMidwifeEmployeeId" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" id="editMidwifeEmail" required>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="phone" id="editMidwifePhone">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Assigned Area *</label>
                                <input type="text" class="form-control" name="assigned_area" id="editMidwifeArea" required>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">MOH Office *</label>
                                <input type="text" class="form-control" name="moh_office" id="editMidwifeMohOffice" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Status *</label>
                                <select class="form-control form-select" name="status" id="editMidwifeStatus" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="on-leave">On Leave</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Experience Years</label>
                                <input type="number" class="form-control" name="experience_years" id="editMidwifeExperienceYears" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" id="editMidwifeAddress" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- Activity Monitoring -->
<div id="activities" class="content-section" style="display: none;">
    <div class="d-flex justify-between align-center mb-3">
        <h2>Activity Monitoring</h2>

        <button class="btn btn-info btn-sm" type="button" onclick="loadAdminActivities()">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Real-time Activity Feed</h4>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Date</th>
                            <th>Midwife</th>
                            <th>Activity Type</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody id="adminActivitiesTableBody">
                        <tr>
                            <td colspan="7" class="text-muted text-center">
                                <i class="fas fa-spinner fa-spin"></i> Loading activities...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Activity Details Modal -->
    <div class="modal fade" id="adminActivityDetailsModal" tabindex="-1" role="dialog" aria-labelledby="adminActivityDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="adminActivityDetailsModalLabel">Activity Details</h5>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body" id="adminActivityDetailsBody">
                    <p class="text-muted">Loading details...</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Close
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

            <!-- CLINICS -->
<div id="clinics" class="content-section" style="display:none;">
    <h2 style="margin-bottom:1rem;">Clinic Visits</h2>

    <div class="filter-bar">
        <div class="row">
            <div class="col-3">
                <input
                    type="text"
                    class="form-control"
                    id="clinicKeyword"
                    placeholder="Search patient, clinic..."
                    oninput="debounceClinics()"
                >
            </div>

            <div class="col-2">
                <input
                    type="date"
                    class="form-control"
                    id="clinicDateFrom"
                    onchange="loadClinicActivities()"
                >
            </div>

            <div class="col-2">
                <input
                    type="date"
                    class="form-control"
                    id="clinicDateTo"
                    onchange="loadClinicActivities()"
                >
            </div>

            <div class="col-2">
                <select
                    class="form-control form-select"
                    id="clinicStatus"
                    onchange="loadClinicActivities()"
                >
                    <option value="">All Status</option>
                    <option value="completed">Completed</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div class="col-3">
                <select
                    class="form-control form-select"
                    id="clinicMidwife"
                    onchange="loadClinicActivities()"
                >
                    <option value="">All Midwives</option>
                </select>
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
                            <th>Patient / Details</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody id="clinic-table">
                        <tr>
                            <td colspan="6" class="text-muted" style="text-align:center; padding:1rem;">
                                <i class="fas fa-spinner fa-spin"></i> Loading clinic visits...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Clinic Details Modal -->
<div class="modal fade" id="clinicDetailsModal" tabindex="-1" role="dialog" aria-labelledby="clinicDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="clinicDetailsModalLabel" class="modal-title">Clinic Visit Details</h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body" id="clinicDetailsBody">
                Loading...
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

            <!-- VACCINATIONS -->
            <div id="vaccinations" class="content-section" style="display:none;">
                <h2 style="margin-bottom:1rem;">Vaccinations</h2>
                <div class="filter-bar">
                    <div class="row">
                        <div class="col-3"><input type="text" class="form-control" id="vaccKeyword" placeholder="Search patient, vaccine..." oninput="debounceVaccinations()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="vaccDateFrom" onchange="loadVaccinationActivities()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="vaccDateTo" onchange="loadVaccinationActivities()"></div>
                        <div class="col-2"><select class="form-control form-select" id="vaccStatus" onchange="loadVaccinationActivities()"><option value="">All Status</option><option value="completed">Completed</option><option value="pending">Pending</option><option value="in_progress">In Progress</option></select></div>
                        <div class="col-3"><select class="form-control form-select" id="vaccMidwife" onchange="loadVaccinationActivities()"><option value="">All Midwives</option></select></div>
                    </div>
                </div>
                <div class="card"><div class="card-body" style="padding:0;"><div style="overflow-x:auto;"><table class="table monitoring-table" style="margin:0;"><thead><tr><th>Date & Time</th><th>Midwife</th><th>Patient / Details</th><th>Location</th><th>Status</th><th>Actions</th></tr></thead><tbody id="vaccination-table"><tr><td colspan="6" class="text-center" style="padding:2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr></tbody></table></div></div></div>
            </div>

            <!-- HOME VISITS -->
            <div id="home-visits" class="content-section" style="display:none;">
                <h2 style="margin-bottom:1rem;">Home Visits</h2>
                <div class="filter-bar">
                    <div class="row">
                        <div class="col-3"><input type="text" class="form-control" id="hvKeyword" placeholder="Search patient, address..." oninput="debounceHomeVisits()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="hvDateFrom" onchange="loadHomeVisitActivities()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="hvDateTo" onchange="loadHomeVisitActivities()"></div>
                        <div class="col-2"><select class="form-control form-select" id="hvStatus" onchange="loadHomeVisitActivities()"><option value="">All Status</option><option value="completed">Completed</option><option value="pending">Pending</option><option value="in_progress">In Progress</option></select></div>
                        <div class="col-3"><select class="form-control form-select" id="hvMidwife" onchange="loadHomeVisitActivities()"><option value="">All Midwives</option></select></div>
                    </div>
                </div>
                <div class="card"><div class="card-body" style="padding:0;"><div style="overflow-x:auto;"><table class="table monitoring-table" style="margin:0;"><thead><tr><th>Date & Time</th><th>Midwife</th><th>Patient / Details</th><th>Location</th><th>Status</th><th>Actions</th></tr></thead><tbody id="home-visits-table"><tr><td colspan="6" class="text-center" style="padding:2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr></tbody></table></div></div></div>
            </div>

            <!-- COUNSELING SESSIONS -->
            <div id="counseling" class="content-section" style="display:none;">
                <h2 style="margin-bottom:1rem;">Counseling Sessions</h2>
                <div class="filter-bar">
                    <div class="row">
                        <div class="col-3"><input type="text" class="form-control" id="counselKeyword" placeholder="Search patient, topic..." oninput="debounceCounseling()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="counselDateFrom" onchange="loadCounselingActivities()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="counselDateTo" onchange="loadCounselingActivities()"></div>
                        <div class="col-2"><select class="form-control form-select" id="counselStatus" onchange="loadCounselingActivities()"><option value="">All Status</option><option value="completed">Completed</option><option value="pending">Pending</option><option value="in_progress">In Progress</option></select></div>
                        <div class="col-3"><select class="form-control form-select" id="counselMidwife" onchange="loadCounselingActivities()"><option value="">All Midwives</option></select></div>
                    </div>
                </div>
                <div class="card"><div class="card-body" style="padding:0;"><div style="overflow-x:auto;"><table class="table monitoring-table" style="margin:0;"><thead><tr><th>Date & Time</th><th>Midwife</th><th>Patient / Details</th><th>Location</th><th>Status</th><th>Actions</th></tr></thead><tbody id="counseling-table"><tr><td colspan="6" class="text-center" style="padding:2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr></tbody></table></div></div></div>
            </div>

            <!-- HEALTH EDUCATION SESSIONS -->
            <div id="health-education" class="content-section" style="display:none;">
                <h2 style="margin-bottom:1rem;">Health Education Sessions</h2>
                <div class="filter-bar">
                    <div class="row">
                        <div class="col-3"><input type="text" class="form-control" id="heKeyword" placeholder="Search topic, location..." oninput="debounceHealthEducation()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="heDateFrom" onchange="loadHealthEducationActivities()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="heDateTo" onchange="loadHealthEducationActivities()"></div>
                        <div class="col-2"><select class="form-control form-select" id="heStatus" onchange="loadHealthEducationActivities()"><option value="">All Status</option><option value="completed">Completed</option><option value="pending">Pending</option><option value="in_progress">In Progress</option></select></div>
                        <div class="col-3"><select class="form-control form-select" id="heMidwife" onchange="loadHealthEducationActivities()"><option value="">All Midwives</option></select></div>
                    </div>
                </div>
                <div class="card"><div class="card-body" style="padding:0;"><div style="overflow-x:auto;"><table class="table monitoring-table" style="margin:0;"><thead><tr><th>Date & Time</th><th>Midwife</th><th>Topic / Details</th><th>Location</th><th>Status</th><th>Actions</th></tr></thead><tbody id="health-education-table"><tr><td colspan="6" class="text-center" style="padding:2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr></tbody></table></div></div></div>
            </div>

            <!-- EMERGENCY RESPONSES -->
            <div id="emergency" class="content-section" style="display:none;">
                <h2 style="margin-bottom:1rem;">Emergency Responses</h2>
                <div class="filter-bar">
                    <div class="row">
                        <div class="col-3"><input type="text" class="form-control" id="emergKeyword" placeholder="Search patient, emergency type..." oninput="debounceEmergency()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="emergDateFrom" onchange="loadEmergencyActivities()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="emergDateTo" onchange="loadEmergencyActivities()"></div>
                        <div class="col-2"><select class="form-control form-select" id="emergStatus" onchange="loadEmergencyActivities()"><option value="">All Status</option><option value="completed">Completed</option><option value="pending">Pending</option><option value="in_progress">In Progress</option></select></div>
                        <div class="col-3"><select class="form-control form-select" id="emergMidwife" onchange="loadEmergencyActivities()"><option value="">All Midwives</option></select></div>
                    </div>
                </div>
                <div class="card"><div class="card-body" style="padding:0;"><div style="overflow-x:auto;"><table class="table monitoring-table" style="margin:0;"><thead><tr><th>Date & Time</th><th>Midwife</th><th>Patient / Details</th><th>Location</th><th>Priority</th><th>Status</th><th>Actions</th></tr></thead><tbody id="emergency-table"><tr><td colspan="7" class="text-center" style="padding:2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr></tbody></table></div></div></div>
            </div>

            <!-- MEETINGS -->
            <div id="meetings" class="content-section" style="display:none;">
                <h2 style="margin-bottom:1rem;">Meetings</h2>
                <div class="filter-bar">
                    <div class="row">
                        <div class="col-3"><input type="text" class="form-control" id="meetingKeyword" placeholder="Search meeting topic..." oninput="debounceMeetings()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="meetingDateFrom" onchange="loadMeetingActivities()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="meetingDateTo" onchange="loadMeetingActivities()"></div>
                        <div class="col-2"><select class="form-control form-select" id="meetingStatus" onchange="loadMeetingActivities()"><option value="">All Status</option><option value="completed">Completed</option><option value="pending">Pending</option><option value="in_progress">In Progress</option></select></div>
                        <div class="col-3"><select class="form-control form-select" id="meetingMidwife" onchange="loadMeetingActivities()"><option value="">All Midwives</option></select></div>
                    </div>
                </div>
                <div class="card"><div class="card-body" style="padding:0;"><div style="overflow-x:auto;"><table class="table monitoring-table" style="margin:0;"><thead><tr><th>Date & Time</th><th>Midwife</th><th>Meeting Topic</th><th>Location</th><th>Status</th><th>Actions</th></tr></thead><tbody id="meeting-table"><tr><td colspan="6" class="text-center" style="padding:2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr></tbody></table></div></div></div>
            </div>

            <!-- SETTINGS -->
            <div id="settings" class="content-section" style="display:none;">
                <h2 style="margin-bottom:1rem;">System Settings</h2>
                <div class="card"><div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <h4>Admin Settings</h4>
                            <p>Manage admin accounts and permissions.</p>
                            <button class="btn btn-primary">Manage Admins</button>
                        </div>
                        <div class="col-6">
                            <h4>System Configuration</h4>
                            <p>Configure system-wide settings and preferences.</p>
                            <button class="btn btn-secondary">System Config</button>
                        </div>
                    </div>
                </div></div>
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
            <form id="" action="../php/admin/addMidwifeForm.php" method="POST">
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
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required minlength="6">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password" required minlength="6">
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

            <!-- MIDWIFE MANAGEMENT -->
            <div id="midwives" class="content-section" style="display:none;">
                <div class="filter-bar">
                    <div class="row">
                        <div class="col-5"><input type="text" class="form-control" id="searchMidwives" placeholder="Search midwives..."></div>
                        <div class="col-4">
                            <select class="form-control form-select" id="filterMwStatus">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-3">
                            <button class="btn btn-primary" onclick="document.getElementById('addMidwifeModal').classList.add('show')">
                                <i class="fas fa-plus"></i> Add Midwife
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row" id="midwivesGrid">
                    <div class="col-12 text-center" style="padding:2rem;color:var(--text-muted);"><i class="fas fa-spinner fa-spin"></i> Loading midwives...</div>
                </div>
            </div>

            <!-- ACTIVITY MONITORING -->
            <div id="activities" class="content-section" style="display:none;">
                <div class="d-flex justify-between align-center" style="margin-bottom:1.25rem;">
                    <h2>System Activity Monitoring</h2>
                    <button class="btn btn-primary" onclick="loadAdminActivities()"><i class="fas fa-sync"></i> Refresh</button>
                </div>
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
                                <option value="">All Types</option>
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

            <!-- CLINICS -->
            <div id="clinics" class="content-section" style="display:none;">
                <div class="d-flex justify-between align-center" style="margin-bottom:1.25rem;">
                    <h2>Clinic Sessions</h2>
                    <button class="btn btn-primary" onclick="loadClinicActivities()"><i class="fas fa-sync"></i> Refresh</button>
                </div>
                <div class="filter-bar">
                    <div class="row">
                        <div class="col-3"><input type="text" class="form-control" id="clinicKeyword" placeholder="Search patient, location..." oninput="debounceClinics()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="clinicDateFrom" onchange="loadClinicActivities()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="clinicDateTo" onchange="loadClinicActivities()"></div>
                        <div class="col-2"><select class="form-control form-select" id="clinicStatus" onchange="loadClinicActivities()"><option value="">All Status</option><option value="completed">Completed</option><option value="pending">Pending</option><option value="in_progress">In Progress</option></select></div>
                        <div class="col-3"><select class="form-control form-select" id="clinicMidwife" onchange="loadClinicActivities()"><option value="">All Midwives</option></select></div>
                    </div>
                </div>
                <div class="card"><div class="card-body" style="padding:0;"><div style="overflow-x:auto;"><table class="table monitoring-table" style="margin:0;"><thead><tr><th>Date & Time</th><th>Midwife</th><th>Patient / Details</th><th>Location</th><th>Status</th><th>Actions</th></tr></thead><tbody id="clinics-table">
                                    <tr>
                                        <td><strong>09/05/2026 08:20</strong><br><small style="color:var(--text-muted);">08:20 – 09:00</small></td>
                                        <td><strong>S. Fernando</strong><br><small style="color:var(--text-muted);">Central District</small></td>
                                        <td><strong>Nadeesha Perera</strong> (28y)<br><small>Routine antenatal check and vitals review.</small></td>
                                        <td><small>Galle Health Center</small></td>
                                        <td><span class="status-badge status-active">Completed</span></td>
                                        <td><button class="btn btn-sm btn-info">View Details</button></td>
                                    </tr>
                                    <tr>
                                        <td><strong>09/05/2026 10:15</strong><br><small style="color:var(--text-muted);">10:15 – 11:00</small></td>
                                        <td><strong>N. Jayasuriya</strong><br><small style="color:var(--text-muted);">Southern Zone</small></td>
                                        <td><strong>Kamala Silva</strong> (35y)<br><small>Postnatal follow-up with breastfeeding counseling.</small></td>
                                        <td><small>Matara Clinic</small></td>
                                        <td><span class="status-badge status-on-leave">Pending</span></td>
                                        <td><button class="btn btn-sm btn-info">View Details</button></td>
                                    </tr>
                                    <tr>
                                        <td><strong>09/05/2026 13:40</strong><br><small style="color:var(--text-muted);">13:40 – 14:20</small></td>
                                        <td><strong>P. Kumar</strong><br><small style="color:var(--text-muted);">Western Region</small></td>
                                        <td><strong>Madhawa Senanayake</strong> (22y)<br><small>First clinic visit after referral from local midwife.</small></td>
                                        <td><small>Colombo North Clinic</small></td>
                                        <td><span class="status-badge status-inactive">In Progress</span></td>
                                        <td><button class="btn btn-sm btn-info">View Details</button></td>
                                    </tr>
                                </tbody></table></div></div></div>
            </div>

            <!-- VACCINATIONS -->
            <div id="vaccinations" class="content-section" style="display:none;">
                <div class="d-flex justify-between align-center" style="margin-bottom:1.25rem;">
                    <h2>Vaccination Sessions</h2>
                    <button class="btn btn-primary" onclick="loadVaccinationActivities()"><i class="fas fa-sync"></i> Refresh</button>
                </div>
                <div class="filter-bar">
                    <div class="row">
                        <div class="col-3"><input type="text" class="form-control" id="vaccKeyword" placeholder="Search patient, vaccine type..." oninput="debounceVaccinations()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="vaccDateFrom" onchange="loadVaccinationActivities()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="vaccDateTo" onchange="loadVaccinationActivities()"></div>
                        <div class="col-2"><select class="form-control form-select" id="vaccStatus" onchange="loadVaccinationActivities()"><option value="">All Status</option><option value="completed">Completed</option><option value="pending">Pending</option><option value="in_progress">In Progress</option></select></div>
                        <div class="col-3"><select class="form-control form-select" id="vaccMidwife" onchange="loadVaccinationActivities()"><option value="">All Midwives</option></select></div>
                    </div>
                </div>
                <div class="card"><div class="card-body" style="padding:0;"><div style="overflow-x:auto;"><table class="table monitoring-table" style="margin:0;"><thead><tr><th>Date & Time</th><th>Midwife</th><th>Patient / Details</th><th>Location</th><th>Status</th><th>Actions</th></tr></thead><tbody id="vaccinations-table"><tr><td colspan="6" class="text-center" style="padding:2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr></tbody></table></div></div></div>
            </div>

            <!-- HOME VISITS -->
            <div id="home-visits" class="content-section" style="display:none;">
                <div class="d-flex justify-between align-center" style="margin-bottom:1.25rem;">
                    <h2>Home Visits</h2>
                </div>
                     <button class="btn btn-primary" onclick="loadHomeVisitActivities()"><i class="fas fa-sync"></i> Refresh</button>
               <div class="filter-bar">
                    <div class="row">
                        <div class="col-3"><input type="text" class="form-control" id="hvKeyword" placeholder="Search patient, address..." oninput="debounceHomeVisits()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="hvDateFrom" onchange="loadHomeVisitActivities()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="hvDateTo" onchange="loadHomeVisitActivities()"></div>
                        <div class="col-2"><select class="form-control form-select" id="hvStatus" onchange="loadHomeVisitActivities()"><option value="">All Status</option><option value="completed">Completed</option><option value="pending">Pending</option><option value="in_progress">In Progress</option></select></div>
                        <div class="col-3"><select class="form-control form-select" id="hvMidwife" onchange="loadHomeVisitActivities()"><option value="">All Midwives</option></select></div>
                    </div>
                </div>
                <div class="card"><div class="card-body" style="padding:0;"><div style="overflow-x:auto;"><table class="table monitoring-table" style="margin:0;"><thead><tr><th>Date & Time</th><th>Midwife</th><th>Patient / Details</th><th>Location</th><th>Status</th><th>Actions</th></tr></thead><tbody id="home-visits-table"><tr><td colspan="6" class="text-center" style="padding:2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr></tbody></table></div></div></div>
            </div>

            <!-- COUNSELING SESSIONS -->
            <div id="counseling" class="content-section" style="display:none;">
                <div class="d-flex justify-between align-center" style="margin-bottom:1.25rem;">
                    <h2>Counseling Sessions</h2>
                    <button class="btn btn-primary" onclick="loadCounselingActivities()"><i class="fas fa-sync"></i> Refresh</button>
                </div>
                <div class="filter-bar">
                    <div class="row">
                        <div class="col-3"><input type="text" class="form-control" id="counselKeyword" placeholder="Search patient, counseling type..." oninput="debounceCounseling()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="counselDateFrom" onchange="loadCounselingActivities()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="counselDateTo" onchange="loadCounselingActivities()"></div>
                        <div class="col-2"><select class="form-control form-select" id="counselStatus" onchange="loadCounselingActivities()"><option value="">All Status</option><option value="completed">Completed</option><option value="pending">Pending</option><option value="in_progress">In Progress</option></select></div>
                        <div class="col-3"><select class="form-control form-select" id="counselMidwife" onchange="loadCounselingActivities()"><option value="">All Midwives</option></select></div>
                    </div>
                </div>
                <div class="card"><div class="card-body" style="padding:0;"><div style="overflow-x:auto;"><table class="table monitoring-table" style="margin:0;"><thead><tr><th>Date & Time</th><th>Midwife</th><th>Patient / Details</th><th>Location</th><th>Status</th><th>Actions</th></tr></thead><tbody id="counseling-table"><tr><td colspan="6" class="text-center" style="padding:2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr></tbody></table></div></div></div>
            </div>

            <!-- HEALTH EDUCATION SESSIONS -->
            <div id="health-education" class="content-section" style="display:none;">
                <div class="d-flex justify-between align-center" style="margin-bottom:1.25rem;">
                    <h2>Health Education Sessions</h2>
                    <button class="btn btn-primary" onclick="loadHealthEducationActivities()"><i class="fas fa-sync"></i> Refresh</button>
                </div>
                <div class="filter-bar">
                    <div class="row">
                        <div class="col-3"><input type="text" class="form-control" id="heKeyword" placeholder="Search topic, location..." oninput="debounceHealthEducation()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="heDateFrom" onchange="loadHealthEducationActivities()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="heDateTo" onchange="loadHealthEducationActivities()"></div>
                        <div class="col-2"><select class="form-control form-select" id="heStatus" onchange="loadHealthEducationActivities()"><option value="">All Status</option><option value="completed">Completed</option><option value="pending">Pending</option><option value="in_progress">In Progress</option></select></div>
                        <div class="col-3"><select class="form-control form-select" id="heMidwife" onchange="loadHealthEducationActivities()"><option value="">All Midwives</option></select></div>
                    </div>
                </div>
                <div class="card"><div class="card-body" style="padding:0;"><div style="overflow-x:auto;"><table class="table monitoring-table" style="margin:0;"><thead><tr><th>Date & Time</th><th>Midwife</th><th>Topic / Details</th><th>Location</th><th>Status</th><th>Actions</th></tr></thead><tbody id="health-education-table"><tr><td colspan="6" class="text-center" style="padding:2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr></tbody></table></div></div></div>
            </div>

            <!-- EMERGENCY RESPONSES -->
            <div id="emergency" class="content-section" style="display:none;">
                <div class="d-flex justify-between align-center" style="margin-bottom:1.25rem;">
                    <h2>Emergency Responses</h2>
                    <button class="btn btn-primary" onclick="loadEmergencyActivities()"><i class="fas fa-sync"></i> Refresh</button>
                </div>
                <div class="filter-bar">
                    <div class="row">
                        <div class="col-3"><input type="text" class="form-control" id="emergKeyword" placeholder="Search patient, emergency type..." oninput="debounceEmergency()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="emergDateFrom" onchange="loadEmergencyActivities()"></div>
                        <div class="col-2"><input type="date" class="form-control" id="emergDateTo" onchange="loadEmergencyActivities()"></div>
                        <div class="col-2"><select class="form-control form-select" id="emergStatus" onchange="loadEmergencyActivities()"><option value="">All Status</option><option value="completed">Completed</option><option value="pending">Pending</option><option value="in_progress">In Progress</option></select></div>
                        <div class="col-3"><select class="form-control form-select" id="emergMidwife" onchange="loadEmergencyActivities()"><option value="">All Midwives</option></select></div>
                    </div>
                </div>
                <div class="card"><div class="card-body" style="padding:0;"><div style="overflow-x:auto;"><table class="table monitoring-table" style="margin:0;"><thead><tr><th>Date & Time</th><th>Midwife</th><th>Patient / Details</th><th>Location</th><th>Priority</th><th>Status</th><th>Actions</th></tr></thead><tbody id="emergency-table"><tr><td colspan="7" class="text-center" style="padding:2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr></tbody></table></div></div></div>
            </div>

            <!-- MEETINGS -->
            <div id="meetings" class="content-section" style="display:none;">
                <h2 style="margin-bottom:1rem;">Meetings</h2>
                <div class="card"><div class="card-body"><p style="color:var(--text-muted);">Meeting tracking functionality will be implemented in the next phase. This will include staff meetings, community meetings, and training sessions.</p></div></div>
            </div>

            <!-- SETTINGS -->
            <div id="settings" class="content-section" style="display:none;">
                <h2 style="margin-bottom:1rem;">System Settings</h2>
                <div class="card"><div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <h4>Admin Settings</h4>
                            <p>Manage admin accounts and permissions.</p>
                            <button class="btn btn-primary">Manage Admins</button>
                        </div>
                        <div class="col-6">
                            <h4>System Configuration</h4>
                            <p>Configure system-wide settings and preferences.</p>
                            <button class="btn btn-secondary">System Config</button>
                        </div>
                    </div>
                </div></div>
            </div>

        </div>
    </div>

    <!-- Add Midwife Modal -->
    <div class="modal" id="addMidwifeModal">
        <div class="modal-content" style="max-width:600px;">
            <div class="modal-header">
                <h4>Add New Midwife</h4>
                <button type="button" class="modal-close" onclick="document.getElementById('addMidwifeModal').classList.remove('show')"><i class="fas fa-times"></i></button>
            </div>
            <form id="addMidwifeForm">
                <div class="row">
                    <div class="col-6"><div class="form-group"><label class="form-label">Full Name</label><input type="text" class="form-control" name="fullname" required></div></div>
                    <div class="col-6"><div class="form-group"><label class="form-label">Employee ID</label><input type="text" class="form-control" name="employee_id" required></div></div>
                </div>
                <div class="row">
                    <div class="col-6"><div class="form-group"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required></div></div>
                    <div class="col-6"><div class="form-group"><label class="form-label">Phone</label><input type="tel" class="form-control" name="phone"></div></div>
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
                <button type="button" class="modal-close" onclick="document.getElementById('activityDetailModal').classList.remove('show')"><i class="fas fa-times"></i></button>
            </div>
            <div id="activity-detail-body" style="padding:1.5rem;">Loading...</div>
        </div>
    </div>

    <script src="../js/page-transitions.js"></script>
    <script src="../js/theme-toggle.js"></script>
    <script src="../js/admin/admin-midwives.js"></script>
    <script src="../js/admin/admin-activities.js"></script>
    <script>
        let activityChartInstance = null;
        let performanceChartInstance = null;
        let themeObserver = null;
        let chartRefreshTimer = null;

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
            setupThemeAwareCharts();
            setupNavigation();
            checkAuthentication();
            setupSidebarToggle();
            setupProfileButtonNavigation();
        });

        // Sidebar Toggle Functionality
        function setupSidebarToggle() {
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const sidebar = document.getElementById('sidebar');
            const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            function openSidebar() {
                sidebar.classList.add('open');
                sidebarOverlay.classList.add('active');
                hamburgerBtn.classList.add('active');
            }

            function closeSidebar() {
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('active');
                hamburgerBtn.classList.remove('active');
            }

            hamburgerBtn.addEventListener('click', function() {
                if (sidebar.classList.contains('open')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });

            sidebarCloseBtn.addEventListener('click', closeSidebar);
            sidebarOverlay.addEventListener('click', closeSidebar);

            // Close sidebar when a menu link is clicked
            var menuLinks = document.querySelectorAll('.sidebar-menu a');
            menuLinks.forEach(function(link) {
                link.addEventListener('click', closeSidebar);
            });
        }

        // Profile Button Navigation - Opens Profile Section
        function setupProfileButtonNavigation() {
            const profileBtn = document.getElementById('navbarProfileBtn');
            const profileImg = document.getElementById('navbar-profile-pic');
            const contentSections = document.querySelectorAll('.content-section');
            const sidebarMenuLinks = document.querySelectorAll('.sidebar-menu a');
            const SECTION_TRANSITION_MS = 200;

            function navigateToProfile() {
                const profileSection = document.getElementById('profile');
                if (!profileSection) return;

                // Find currently visible section
                let currentSection = Array.from(contentSections).find(section =>
                    window.getComputedStyle(section).display !== 'none' && section.id !== 'profile'
                );

                // Hide all content sections
                contentSections.forEach(section => {
                    section.style.display = 'none';
                    section.classList.remove('section-slide-in', 'section-slide-out');
                });

                // Update sidebar active state
                sidebarMenuLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#profile') {
                        link.classList.add('active');
                    }
                });

                // Close sidebar if open (mobile)
                const sidebar = document.getElementById('sidebar');
                const sidebarOverlay = document.getElementById('sidebarOverlay');
                const hamburgerBtn = document.getElementById('hamburgerBtn');
                if (sidebar && sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                    if (sidebarOverlay) sidebarOverlay.classList.remove('active');
                    if (hamburgerBtn) hamburgerBtn.classList.remove('active');
                }

                // Show profile section with animation
                if (currentSection) {
                    currentSection.classList.add('section-slide-out');
                    setTimeout(() => {
                        profileSection.style.display = 'block';
                        profileSection.classList.add('section-slide-in');
                    }, SECTION_TRANSITION_MS);
                } else {
                    profileSection.style.display = 'block';
                    profileSection.classList.add('section-slide-in');
                }

                // Update URL hash
                window.location.hash = 'profile';
            }

            if (profileBtn) {
                profileBtn.addEventListener('click', navigateToProfile);
            }
            if (profileImg) {
                profileImg.addEventListener('click', function(e) {
                    e.stopPropagation();
                    navigateToProfile();
                });
            }
        }

        function setupThemeAwareCharts() {
            if (themeObserver) {
                return;
            }

            themeObserver = new MutationObserver(function(mutations) {
                const isThemeMutation = mutations.some(function(mutation) {
                    return mutation.type === 'attributes' && mutation.attributeName === 'data-theme';
                });

                if (!isThemeMutation) {
                    return;
                }

                clearTimeout(chartRefreshTimer);
                chartRefreshTimer = setTimeout(initializeCharts, 60);
            });

            themeObserver.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['data-theme']
            });
        }

        function checkAuthentication() {
            const adminUser = localStorage.getItem('admin_user');
            if (!adminUser) {
                localStorage.setItem('admin_user', JSON.stringify({name:'Dr. Sarah Johnson', moh_office:'MOH Colombo 01'}));
            }
            
            const user = JSON.parse(localStorage.getItem('admin_user'));
            document.querySelector('.admin-name').textContent = user.name || 'Admin User';
            
            // Extract area from moh_office field
            const adminArea = extractAreaFromMohOffice(user.moh_office);
            localStorage.setItem('admin_area', adminArea);
            
            // Load dashboard with area restriction
            loadAdminStatsWithArea(adminArea);
            
            // Update area section to show only admin's area
            updateAreaSectionForAdmin(adminArea);
        }

        function extractAreaFromMohOffice(mohOffice) {
            // Extract area name from MOH office string
            // Examples: "MOH Colombo 01" -> "Colombo", "MOH Kahambilihena" -> "Kahambilihena"
            if (!mohOffice) return 'Colombo'; // Default fallback
            
            // Remove "MOH" prefix and extract area name
            const parts = mohOffice.replace('MOH', '').trim().split(' ');
            return parts[0] || 'Colombo';
        }

        function loadAdminStatsWithArea(adminArea) {
            // Load stats filtered by admin's area
            fetch('../php/get_admin_stats.php?area=' + encodeURIComponent(adminArea))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateDashboardStats(data.data);
                    }
                })
                .catch(error => {
                    console.error('Error loading admin stats:', error);
                });
        }

        function updateDashboardStats(data) {
            // Update dashboard statistics cards
            document.getElementById('sc-total-midwives').textContent = data.total_midwives || 0;
            document.getElementById('sc-total-activities').textContent = data.total_activities || 0;
            document.getElementById('sc-activities-today').textContent = data.activities_today || 0;
            document.getElementById('sc-urgent-pending').textContent = data.urgent_pending || 0;
            
            // Update summary cards
            document.getElementById('sc-home-visits').textContent = data.home_visits_today || 0;
            document.getElementById('sc-vaccinations').textContent = data.vaccinations_week || 0;
            document.getElementById('sc-clinic-sessions').textContent = data.clinic_sessions_week || 0;
            document.getElementById('sc-pending-tasks').textContent = data.pending_reports || 0;
            
            // Update charts if available
            if (data.type_summary) {
                updateActivityChart(data.type_summary);
            }
            if (data.weekly_breakdown) {
                updateWeeklyChart(data.weekly_breakdown);
            }
        }

        function updateAreaSectionForAdmin(adminArea) {
            // Hide all area cards first
            const areaCards = document.querySelectorAll('.area-card');
            areaCards.forEach(card => {
                card.style.display = 'none';
            });
            
            // Show only the admin's assigned area card
            const adminAreaCard = document.querySelector(`[data-area="${adminArea}"]`);
            if (adminAreaCard) {
                adminAreaCard.style.display = 'block';
                adminAreaCard.classList.add('selected');
                
                // Auto-select and show admin's area details
                selectArea(adminArea);
            }
            
            // Update section title
            const sectionTitle = document.querySelector('.area-section h3');
            if (sectionTitle) {
                sectionTitle.textContent = `${adminArea} Area Dashboard`;
            }
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
                            
                            // Load data for the specific section
                            switch(targetSectionId) {
                                case 'dashboard': loadAdminStats(); break;
                                case 'areas': loadAreaOverview(); break;
                                case 'midwives': loadMidwivesGrid(); break;
                                case 'activities': loadAdminActivities(); break;
                                case 'clinics': loadClinicActivities(); break;
                                case 'vaccinations': loadVaccinationActivities(); break;
                                case 'home-visits': loadHomeVisitActivities(); break;
                                case 'counseling': loadCounselingActivities(); break;
                                case 'health-education': loadHealthEducationActivities(); break;
                                case 'emergency': loadEmergencyActivities(); break;
                                case 'meetings': loadMeetingActivities(); break;
                            }
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

            // Handle browser back/forward buttons
            window.addEventListener('hashchange', function() {
                const hash = window.location.hash;
                if (!hash || hash.length < 2) {
                    // If no hash, go to dashboard
                    const dashboardLink = document.querySelector('.sidebar-menu a[href="#dashboard"]');
                    if (dashboardLink) dashboardLink.click();
                    return;
                }
                const link = document.querySelector('.sidebar-menu a[href="' + hash + '"]');
                if (link) {
                    link.click();
                }
            });
        }

        function initializeCharts() {
            const rootStyles = getComputedStyle(document.documentElement);
            const isDarkTheme = document.documentElement.getAttribute('data-theme') === 'dark';
            const axisTextColor = (rootStyles.getPropertyValue('--text-secondary') || '#6c757d').trim();
            const gridColor = isDarkTheme ? 'rgba(203, 213, 225, 0.14)' : 'rgba(52, 58, 64, 0.12)';

            if (activityChartInstance) {
                activityChartInstance.destroy();
            }

            if (performanceChartInstance) {
                performanceChartInstance.destroy();
            }

            // Activity Chart
            const activityCtx = document.getElementById('activityChart').getContext('2d');
            activityChartInstance = new Chart(activityCtx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Home Visits',
                        data: [12, 19, 15, 17, 20, 18, 22],
                        borderColor: isDarkTheme ? '#60a5fa' : '#00509e',
                        backgroundColor: isDarkTheme ? 'rgba(96, 165, 250, 0.16)' : 'rgba(0, 80, 158, 0.14)',
                        pointBackgroundColor: isDarkTheme ? '#93c5fd' : '#00509e',
                        pointBorderColor: '#ffffff',
                        pointRadius: 3,
                        borderWidth: 3,
                        tension: 0.25
                    }, {
                        label: 'Vaccinations',
                        data: [8, 12, 10, 14, 16, 12, 18],
                        borderColor: isDarkTheme ? '#2dd4bf' : '#00897b',
                        backgroundColor: isDarkTheme ? 'rgba(45, 212, 191, 0.16)' : 'rgba(0, 137, 123, 0.14)',
                        pointBackgroundColor: isDarkTheme ? '#5eead4' : '#00897b',
                        pointBorderColor: '#ffffff',
                        pointRadius: 3,
                        borderWidth: 3,
                        tension: 0.25
                    }, {
                        label: 'Clinic Sessions',
                        data: [5, 7, 6, 8, 9, 7, 10],
                        borderColor: isDarkTheme ? '#f59e0b' : '#b45309',
                        backgroundColor: isDarkTheme ? 'rgba(245, 158, 11, 0.16)' : 'rgba(180, 83, 9, 0.14)',
                        pointBackgroundColor: isDarkTheme ? '#fcd34d' : '#b45309',
                        pointBorderColor: '#ffffff',
                        pointRadius: 3,
                        borderWidth: 3,
                        tension: 0.25
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: axisTextColor,
                                boxWidth: 36
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: axisTextColor
                            },
                            grid: {
                                color: gridColor
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: axisTextColor
                            },
                            grid: {
                                color: gridColor
                            }
                        }
                    }
                }
            });

            // Performance Chart
            const performanceCtx = document.getElementById('performanceChart').getContext('2d');
            performanceChartInstance = new Chart(performanceCtx, {
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
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: axisTextColor
                            }
                        }
                    }
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

        function loadAdminStats() {
            fetch('../php/get_admin_stats.php')
                .then(r => r.json())
                .then(function(data) {
                    if (!data.success) return;
                    const d = data.data;
                    // Banner stats
                    setText('top-total-midwives', d.total_midwives);
                    setText('top-today-activities', d.activities_today);
                    setText('top-coverage-rate', d.coverage_rate + '%');
                    // 4 Summary cards
                    setText('sc-home-visits', d.home_visits_today);
                    setText('sc-vaccinations', d.vaccinations_week);
                    setText('sc-clinic-sessions', d.clinic_sessions_week);
                    setText('sc-pending-tasks', d.urgent_pending);
                    // Charts
                    buildWeeklyChart(d.weekly_breakdown);
                    buildStatusChart(d.status_breakdown);
                    renderTopMidwives(d.top_midwives);
                    populateMidwifeDropdown(d.midwives_list);
                    loadRecentActivityFeed();
                })
                .catch(function(err) { console.error('Stats error:', err); });
        }

        function loadRecentActivityFeed() {
            fetch('../php/get_admin_activities.php?limit=6&sort=latest')
                .then(r => r.json())
                .then(function(data) {
                    var feed = document.getElementById('recent-activities-feed');
                    if (!data.success || !data.data.activities.length) {
                        feed.innerHTML = '<div class="activity-item"><div class="activity-details"><h4 style="color:var(--text-light);">No recent activities found.</h4></div></div>';
                        return;
                    }
                    // Icon mapping for new design
                    var iconMap = {
                        HOME_VISIT: { icon: 'home', cls: 'teal', title: 'Home Visit Completed' },
                        CLINIC_VISIT: { icon: 'clinic-medical', cls: 'blue', title: 'Clinic Session' },
                        VACCINATION: { icon: 'syringe', cls: 'teal', title: 'Vaccination Session' },
                        COUNSELING: { icon: 'comments', cls: 'purple', title: 'Counseling Session' },
                        HEALTH_EDUCATION: { icon: 'chalkboard-teacher', cls: 'blue', title: 'Health Education' },
                        EMERGENCY_RESPONSE: { icon: 'ambulance', cls: 'red', title: 'Emergency Response' },
                        PRENATAL_CARE: { icon: 'baby-carriage', cls: 'teal', title: 'Prenatal Care Visit' },
                        POSTNATAL_CARE: { icon: 'heart', cls: 'teal', title: 'Postnatal Care Visit' },
                        FAMILY_PLANNING: { icon: 'venus-mars', cls: 'orange', title: 'Family Planning' },
                        NUTRITION_COUNSELING: { icon: 'apple-alt', cls: 'teal', title: 'Nutrition Counseling' }
                    };
                    feed.innerHTML = data.data.activities.map(function(a) {
                        var info = iconMap[a.activity_type_code] || { icon: 'clipboard-list', cls: 'teal', title: a.activity_type_name };
                        // Compute relative time
                        var created = new Date(a.created_at);
                        var diffMins = Math.floor((Date.now() - created.getTime()) / 60000);
                        var relTime = diffMins < 60 ? diffMins + ' min ago'
                            : diffMins < 1440 ? Math.floor(diffMins / 60) + ' hours ago'
                            : Math.floor(diffMins / 1440) + ' days ago';
                        // Short midwife name: First initial + last name
                        var nameParts = (a.midwife_name || '').split(' ');
                        var shortName = nameParts.length > 1
                            ? nameParts[0].charAt(0) + '. ' + nameParts[nameParts.length - 1]
                            : a.midwife_name;
                        return '<div class="activity-item">'
                            + '<div class="activity-icon ' + info.cls + '"><i class="fas fa-' + info.icon + '"></i></div>'
                            + '<div class="activity-details">'
                            + '<h4>' + info.title + '</h4>'
                            + '<p>' + shortName + ' • ' + relTime + '</p>'
                            + '</div>'
                            + '</div>';
                    }).join('');
                }).catch(function() { });
        }

        function renderTopMidwives(list) {
            var el = document.getElementById('top-midwives-list');
            if (!list || !list.length) {
                el.innerHTML = '<div class="midwife-item"><div class="midwife-info"><h4 style="color:var(--text-light);">No activity data available.</h4></div></div>';
                return;
            }
            el.innerHTML = list.map(function(m) {
                var rate = m.completion_rate || 0;
                var badge = rate >= 95 ? 'excellent' : rate >= 90 ? 'very-good' : 'good';
                var label = rate >= 95 ? 'Excellent' : rate >= 90 ? 'Very Good' : 'Good';
                return '<div class="midwife-item">'
                    + '<div class="midwife-info">'
                    + '<h5>' + m.full_name + '</h5>'
                    + '<p>' + rate + '% completion rate</p>'
                    + '</div>'
                    + '<span class="performance-badge ' + badge + '">' + label + '</span>'
                    + '</div>';
            }).join('');
        }

        function populateMidwifeDropdown(list) {
            var dropdowns = ['actMidwife', 'clinicMidwife', 'vaccMidwife', 'hvMidwife', 'counselMidwife', 'heMidwife', 'emergMidwife'];
            if (!list) return;

            dropdowns.forEach(function(id) {
                var sel = document.getElementById(id);
                if (!sel) return;

                // Clear existing options except the first one
                while (sel.length > 1) {
                    sel.remove(1);
                }

                list.forEach(function(m) {
                    var opt = document.createElement('option');
                    opt.value = m.midwife_id;
                    opt.textContent = m.full_name + ' (' + m.employee_id + ')';
                    sel.appendChild(opt);
                });
            });
        }

        function debounceActivities() { clearTimeout(debTimer); debTimer = setTimeout(loadAdminActivities, 500); }

    
        function viewActivityDetail(id) {
            var modal = document.getElementById('activityDetailModal');
            var body = document.getElementById('activity-detail-body');
            modal.classList.add('show');
            body.innerHTML = '<p><i class="fas fa-spinner fa-spin"></i> Loading...</p>';
            fetch('../php/get_admin_activities.php?limit=200&sort=latest')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (!data.success) { body.innerHTML = 'Error loading record.'; return; }
                    var a = data.data.activities.find(function(x) { return x.activity_id === id; });
                    if (!a) { body.innerHTML = 'Record not found.'; return; }
                    body.innerHTML = '<table class="table" style="margin:0;">'
                        + '<tr><td><strong>Date</strong></td><td>' + a.date + ' ' + a.start_time + (a.end_time ? ' &ndash; ' + a.end_time : '') + '</td></tr>'
                        + '<tr><td><strong>Midwife</strong></td><td>' + a.midwife_name + ' (' + a.midwife_emp_id + ') &mdash; ' + a.assigned_area + '</td></tr>'
                        + '<tr><td><strong>Activity Type</strong></td><td>' + a.activity_type_name + '</td></tr>'
                        + '<tr><td><strong>Patient</strong></td><td>' + (a.patient_name || '&mdash;') + (a.patient_age ? ' (' + a.patient_age + 'y)' : '') + '</td></tr>'
                        + '<tr><td><strong>Location</strong></td><td>' + (a.location || '&mdash;') + '</td></tr>'
                        + '<tr><td><strong>Description</strong></td><td>' + (a.description || '&mdash;') + '</td></tr>'
                        + '<tr><td><strong>Observations</strong></td><td>' + (a.observations || '&mdash;') + '</td></tr>'
                        + '<tr><td><strong>Status</strong></td><td>' + a.status + '</td></tr>'
                        + '<tr><td><strong>Priority</strong></td><td>' + (a.priority_level || 'normal') + '</td></tr>'
                        + '<tr><td><strong>Follow-up</strong></td><td>' + (a.follow_up_required ? 'Yes &mdash; ' + (a.follow_up_date || 'date TBD') : 'No') + '</td></tr>'
                        + '<tr><td><strong>Logged At</strong></td><td>' + a.created_at + '</td></tr>'
                        + '</table>';
                })
                .catch(function() { body.innerHTML = 'Error loading record.'; });
        }

        function loadMidwivesGrid() {
            fetch('../php/get_admin_stats.php')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var grid = document.getElementById('midwivesGrid');
                    if (!data.success || !data.data.midwives_list.length) {
                        grid.innerHTML = '<div class="col-12 text-center" style="padding:2rem;color:var(--text-muted);">No midwives found.</div>';
                        return;
                    }
                    grid.innerHTML = data.data.midwives_list.map(function(m) {
                        return '<div class="col-6"><div class="card midwife-card"><div class="card-body">'
                            + '<div class="d-flex justify-between align-center">'
                            + '<div><h5>' + m.full_name + '</h5><p>ID: ' + m.employee_id + '</p><p>Area: ' + m.assigned_area + '</p></div>'
                            + '<div><span class="status-badge status-active">Active</span></div>'
                            + '</div></div></div></div>';
                    }).join('');
                })
                .catch(function(err) { console.error(err); });
        }

        function loadPerformanceData() {
            fetch('../php/get_admin_stats.php')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var el = document.getElementById('performance-body');
                    if (!data.success || !data.data.top_midwives.length) {
                        el.innerHTML = '<p style="color:var(--text-muted);">No performance data available.</p>';
                        return;
                    }
                    el.innerHTML = '<table class="table"><thead><tr><th>Midwife</th><th>Area</th><th>Activities (30d)</th><th>Completed</th><th>Completion Rate</th></tr></thead><tbody>'
                        + data.data.top_midwives.map(function(m) {
                            var rate = m.completion_rate || 0;
                            var badge = rate >= 90 ? 'excellent' : rate >= 75 ? 'good' : 'needs-imp';
                            return '<tr><td><strong>' + m.full_name + '</strong></td><td>' + m.assigned_area + '</td>'
                                + '<td>' + m.total_activities + '</td><td>' + m.completed + '</td>'
                                + '<td><span class="perf-badge ' + badge + '">' + rate + '%</span></td></tr>';
                        }).join('')
                        + '</tbody></table>';
                })
                .catch(function(err) { console.error(err); });
        }

        function loadSchedulesData() {
            fetch('../php/get_admin_activities.php?limit=20&sort=latest')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var el = document.getElementById('schedules-body');
                    if (!data.success || !data.data.activities.length) {
                        el.innerHTML = '<p style="color:var(--text-muted);">No schedule data available.</p>';
                        return;
                    }
                    el.innerHTML = '<p style="color:var(--text-muted);margin-bottom:1rem;">Showing most recent 20 logged activities as schedule reference.</p>'
                        + '<table class="table"><thead><tr><th>Date</th><th>Midwife</th><th>Activity</th><th>Patient</th><th>Location</th><th>Status</th></tr></thead><tbody>'
                        + data.data.activities.map(function(a) {
                            return '<tr><td>' + a.date + '</td><td>' + a.midwife_name + '</td><td>' + a.activity_type_name + '</td>'
                                + '<td>' + (a.patient_name || '&mdash;') + '</td><td>' + (a.location || '&mdash;') + '</td>'
                                + '<td><span class="status-badge ' + (a.status === 'completed' ? 'status-active' : 'status-on-leave') + '">' + a.status + '</span></td></tr>';
                        }).join('')
                        + '</tbody></table>';
                })
                .catch(function(err) { console.error(err); });
        }

        function setupThemeCharts() {
            if (themeObs) return;
            themeObs = new MutationObserver(function(mutations) {
                var changed = mutations.some(function(m) { return m.type === 'attributes' && m.attributeName === 'data-theme'; });
                if (changed) { clearTimeout(chartTimer); chartTimer = setTimeout(loadAdminStats, 80); }
            });
            themeObs.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
        }

        function buildWeeklyChart(breakdown) {
            var ctx = document.getElementById('activityChart');
            if (!ctx) return;
            var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            var textColor = isDark ? '#94a3b8' : '#6c757d';
            var gridColor = isDark ? 'rgba(148,163,184,0.12)' : 'rgba(0,0,0,0.07)';
            if (activityChartInst) activityChartInst.destroy();

            // Build Mon–Sun of current week
            var today = new Date();
            var dow = today.getDay(); // 0=Sun
            var monOffset = (dow === 0) ? -6 : 1 - dow; // Monday = start
            var weekDays = [];
            for (var i = 0; i < 7; i++) {
                var d = new Date(today);
                d.setDate(today.getDate() + monOffset + i);
                weekDays.push(d.toISOString().slice(0, 10));
            }
            var dayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

            // Map DB rows by date
            var dataMap = {};
            if (breakdown) {
                breakdown.forEach(function(row) { dataMap[row.day] = row; });
            }
            function ex(field) {
                return weekDays.map(function(d) {
                    return dataMap[d] ? (parseInt(dataMap[d][field]) || 0) : 0;
                });
            }

            activityChartInst = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dayLabels,
                    datasets: [
                        {
                            label: 'Home Visits',
                            data: ex('home_visits'),
                            borderColor:          '#00A699',
                            backgroundColor:      'rgba(0, 166, 153, 0.1)',
                            pointBackgroundColor: '#00A699',
                            pointBorderColor:     '#fff',
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            borderWidth: 2.5,
                            tension: 0.35,
                            fill: false
                        },
                        {
                            label: 'Vaccinations',
                            data: ex('vaccinations'),
                            borderColor:          '#00897b',
                            backgroundColor:      'rgba(0, 137, 123, 0.1)',
                            pointBackgroundColor: '#00897b',
                            pointBorderColor:     '#fff',
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            borderWidth: 2.5,
                            tension: 0.35,
                            fill: false
                        },
                        {
                            label: 'Clinic Sessions',
                            data: ex('clinic_visits'),
                            borderColor:          '#4DB6AC',
                            backgroundColor:      'rgba(77, 182, 172, 0.1)',
                            pointBackgroundColor: '#4DB6AC',
                            pointBorderColor:     '#fff',
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            borderWidth: 2.5,
                            tension: 0.35,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: '#6c757d',
                                boxWidth: 36,
                                boxHeight: 3,
                                padding: 20,
                                font: { size: 12 }
                            }
                        },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        x: {
                            ticks: { color: '#6c757d', font: { size: 12 } },
                            grid:  { color: 'rgba(0,0,0,0.07)' },
                            border: { dash: [4, 4] }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#6c757d', precision: 0, stepSize: 5 },
                            grid: { color: 'rgba(0,0,0,0.07)' },
                            border: { dash: [4, 4] }
                        }
                    }
                }
            });
        }

        function buildStatusChart(breakdown) {
            var ctx = document.getElementById('statusChart');
            if (!ctx) return;
            if (statusChartInst) statusChartInst.destroy();
            var isDark    = document.documentElement.getAttribute('data-theme') === 'dark';
            var textColor = isDark ? '#94a3b8' : '#6c757d';

            // Map DB status values to correct display labels and screenshot-matching colors
            var statusConfig = [
                { key: 'completed',   label: 'Completed',   color: '#00A699'  },
                { key: 'in_progress', label: 'In Progress', color: '#00897b'  },
                { key: 'pending',     label: 'Pending',     color: '#e67e22'  },
                { key: 'overdue',     label: 'Overdue',     color: '#dc3545'  }
            ];

            // Build a count map from breakdown
            var countMap = {};
            if (breakdown) {
                breakdown.forEach(function(r) { countMap[r.status] = parseInt(r.cnt) || 0; });
            }

            var labels = statusConfig.map(function(s) { return s.label; });
            var values = statusConfig.map(function(s) { return countMap[s.key] || 0; });
            var colors = statusConfig.map(function(s) { return s.color; });

            statusChartInst = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors,
                        borderWidth: 3,
                        borderColor: '#ffffff',
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: '#6c757d',
                                boxWidth: 14,
                                padding: 16,
                                font: { size: 12 }
                            }
                        }
                    }
                }
            });
        }

        // Specific activity type loading functions
        function loadClinicActivities() {
            loadFilteredActivities('CLINIC_VISIT', 'clinics-table', ['clinicKeyword', 'clinicDateFrom', 'clinicDateTo', 'clinicStatus', 'clinicMidwife']);
        }

        function loadVaccinationActivities() {
            loadFilteredActivities('VACCINATION', 'vaccinations-table', ['vaccKeyword', 'vaccDateFrom', 'vaccDateTo', 'vaccStatus', 'vaccMidwife']);
        }

     
        function loadCounselingActivities() {
            loadFilteredActivities('COUNSELING', 'counseling-table', ['counselKeyword', 'counselDateFrom', 'counselDateTo', 'counselStatus', 'counselMidwife']);
        }

        function loadHealthEducationActivities() {
            loadFilteredActivities('HEALTH_EDUCATION', 'health-education-table', ['heKeyword', 'heDateFrom', 'heDateTo', 'heStatus', 'heMidwife']);
        }

        function loadEmergencyActivities() {
            loadFilteredActivities('EMERGENCY_RESPONSE', 'emergency-table', ['emergKeyword', 'emergDateFrom', 'emergDateTo', 'emergStatus', 'emergMidwife'], true);
        }

        function renderClinicSampleRows() {
            return ''
                + '<tr>'
                + '<td><strong>09/05/2026 08:20</strong><br><small style="color:var(--text-muted);">08:20 – 09:00</small></td>'
                + '<td><strong>S. Fernando</strong><br><small style="color:var(--text-muted);">Central District</small></td>'
                + '<td><strong>Nadeesha Perera</strong> (28y)<br><small>Routine antenatal check and vitals review.</small></td>'
                + '<td><small>Galle Health Center</small></td>'
                + '<td><span class="status-badge status-active">Completed</span></td>'
                + '<td><button class="btn btn-sm btn-info">View Details</button></td>'
                + '</tr>'
                + '<tr>'
                + '<td><strong>09/05/2026 10:15</strong><br><small style="color:var(--text-muted);">10:15 – 11:00</small></td>'
                + '<td><strong>N. Jayasuriya</strong><br><small style="color:var(--text-muted);">Southern Zone</small></td>'
                + '<td><strong>Kamala Silva</strong> (35y)<br><small>Postnatal follow-up with breastfeeding counseling.</small></td>'
                + '<td><small>Matara Clinic</small></td>'
                + '<td><span class="status-badge status-on-leave">Pending</span></td>'
                + '<td><button class="btn btn-sm btn-info">View Details</button></td>'
                + '</tr>'
                + '<tr>'
                + '<td><strong>09/05/2026 13:40</strong><br><small style="color:var(--text-muted);">13:40 – 14:20</small></td>'
                + '<td><strong>P. Kumar</strong><br><small style="color:var(--text-muted);">Western Region</small></td>'
                + '<td><strong>Madhawa Senanayake</strong> (22y)<br><small>First clinic visit after referral from local midwife.</small></td>'
                + '<td><small>Colombo North Clinic</small></td>'
                + '<td><span class="status-badge status-inactive">In Progress</span></td>'
                + '<td><button class="btn btn-sm btn-info">View Details</button></td>'
                + '</tr>';
        }

        function loadFilteredActivities(activityType, tableId, fieldIds, includePriority = false) {
            var tbody = document.getElementById(tableId);
            if (!tbody) return;
            
            tbody.innerHTML = '<tr><td colspan="' + (includePriority ? '7' : '6') + '" class="text-center" style="padding:2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';
            
            var keyword = document.getElementById(fieldIds[0])?.value || '';
            var dateFrom = document.getElementById(fieldIds[1])?.value || '';
            var dateTo = document.getElementById(fieldIds[2])?.value || '';
            var status = document.getElementById(fieldIds[3])?.value || '';
            var midwife = document.getElementById(fieldIds[4])?.value || '';
            
            var url = '../php/get_admin_activities.php?activity_type=' + activityType + '&limit=100&sort=latest';
            if (keyword) url += '&keyword=' + encodeURIComponent(keyword);
            if (dateFrom) url += '&date_from=' + dateFrom;
            if (dateTo) url += '&date_to=' + dateTo;
            if (status) url += '&status=' + status;
            if (midwife) url += '&midwife_id=' + midwife;
            
            fetch(url)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (!data.success) {
                        tbody.innerHTML = '<tr><td colspan="' + (includePriority ? '7' : '6') + '" class="text-center" style="color:red;">Failed to load activities.</td></tr>';
                        return;
                    }
                    var acts = data.data.activities;
                    if (!acts.length) {
                        tbody.innerHTML = '<tr><td colspan="' + (includePriority ? '7' : '6') + '" class="text-center" style="padding:2rem;color:var(--text-muted);">No activities found.</td></tr>';
                        return;
                    }
                    
                    tbody.innerHTML = acts.map(function(a) {
                        var statusCls = a.status === 'completed' ? 'status-active' : a.status === 'pending' ? 'status-on-leave' : 'status-inactive';
                        var patient = a.patient_name ? ('<strong>' + a.patient_name + '</strong>' + (a.patient_age ? ' (' + a.patient_age + 'y)' : '') + '<br>') : '';
                        var desc = a.description ? a.description.substring(0, 55) + (a.description.length > 55 ? '&hellip;' : '') : '';
                        var priorityCol = '';
                        
                        if (includePriority) {
                            var priBadge = '';
                            if (a.priority_level && a.priority_level !== 'normal' && a.priority_level !== '') {
                                var priCls = (a.priority_level === 'critical') ? 'priority-critical' : 'priority-high';
                                priBadge = '<span class="status-badge ' + priCls + '" style="display:inline-block;">' + a.priority_level.toUpperCase() + '</span>';
                            }
                            priorityCol = '<td>' + (priBadge || '<small style="color:var(--text-muted);">Normal</small>') + '</td>';
                        }
                        
                        return '<tr>'
                            + '<td style="white-space:nowrap;"><strong>' + a.date + '</strong><br><small style="color:var(--text-muted);">' + a.start_time + (a.end_time ? ' &ndash; ' + a.end_time : '') + '</small></td>'
                            + '<td><strong>' + a.midwife_name + '</strong><br><small style="color:var(--text-muted);">' + a.assigned_area + '</small></td>'
                            + '<td>' + patient + '<small>' + desc + '</small></td>'
                            + '<td><small>' + (a.location || '&mdash;') + '</small></td>'
                            + (includePriority ? priorityCol : '')
                            + '<td><span class="status-badge ' + statusCls + '">' + a.status + '</span></td>'
                            + '<td><button class="btn btn-sm btn-info" onclick="viewActivityDetail(' + a.activity_id + ')" title="View Details"><i class="fas fa-eye"></i></button></td>'
                            + '</tr>';
                    }).join('');
                })
                .catch(function(err) {
                    console.error(err);
                    if (tableId === 'clinic-table' || tableId === 'clinics-table') {
                        tbody.innerHTML = renderClinicSampleRows();
                        return;
                    }
                    tbody.innerHTML = '<tr><td colspan="' + (includePriority ? '7' : '6') + '" class="text-center" style="color:red;">Network error.</td></tr>';
                });
        }

        // Debounce functions for search inputs
        function debounceClinics() { clearTimeout(debTimer); debTimer = setTimeout(loadClinicActivities, 500); }
        function debounceVaccinations() { clearTimeout(debTimer); debTimer = setTimeout(loadVaccinationActivities, 500); }
        function debounceHomeVisits() { clearTimeout(debTimer); debTimer = setTimeout(loadHomeVisitActivities, 500); }
        function debounceCounseling() { clearTimeout(debTimer); debTimer = setTimeout(loadCounselingActivities, 500); }
        function debounceHealthEducation() { clearTimeout(debTimer); debTimer = setTimeout(loadHealthEducationActivities, 500); }
        function debounceEmergency() { clearTimeout(debTimer); debTimer = setTimeout(loadEmergencyActivities, 500); }
        function debounceMeetings() { clearTimeout(debTimer); debTimer = setTimeout(loadMeetingActivities, 500); }

        // Load specific activity type functions
        function loadClinicActivities() { loadFilteredActivities('CLINIC_VISIT', 'clinic-table', ['clinicKeyword', 'clinicDateFrom', 'clinicDateTo', 'clinicStatus', 'clinicMidwife']); }
        function loadVaccinationActivities() { loadFilteredActivities('VACCINATION', 'vaccination-table', ['vaccKeyword', 'vaccDateFrom', 'vaccDateTo', 'vaccStatus', 'vaccMidwife']); }
        // function loadHomeVisitActivities() { loadFilteredActivities('HOME_VISIT', 'home-visit-table', ['hvKeyword', 'hvDateFrom', 'hvDateTo', 'hvStatus', 'hvMidwife']); }
        function loadCounselingActivities() { loadFilteredActivities('COUNSELING', 'counseling-table', ['counselKeyword', 'counselDateFrom', 'counselDateTo', 'counselStatus', 'counselMidwife']); }
        function loadHealthEducationActivities() { loadFilteredActivities('HEALTH_EDUCATION', 'health-education-table', ['heKeyword', 'heDateFrom', 'heDateTo', 'heStatus', 'heMidwife']); }
        function loadEmergencyActivities() { loadFilteredActivities('EMERGENCY_RESPONSE', 'emergency-table', ['emergKeyword', 'emergDateFrom', 'emergDateTo', 'emergStatus', 'emergMidwife'], true); }
        function loadMeetingActivities() { loadFilteredActivities('MEETING', 'meeting-table', ['meetingKeyword', 'meetingDateFrom', 'meetingDateTo', 'meetingStatus', 'meetingMidwife']); }

        // Area-based monitoring functions
        var currentArea = null;

        function openAreaDetails(areaName) {
            currentArea = areaName;
            
            // Hide areas section and show area details
            document.getElementById('areas').style.display = 'none';
            document.getElementById('area-details').style.display = 'block';
            
            // Update the title
            document.getElementById('area-details-title').textContent = areaName + ' - Area Details';
            
            // Load area data
            loadAreaData(areaName);
        }

        function backToAreas() {
            // Hide area details and show areas section
            document.getElementById('area-details').style.display = 'none';
            document.getElementById('areas').style.display = 'block';
            
            // Update sidebar active state
            var menuLinks = document.querySelectorAll('.sidebar-menu a');
            menuLinks.forEach(function(link) {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#areas') {
                    link.classList.add('active');
                }
            });
        }

        function loadAreaData(areaName) {
            // Show loading state
            var tbody = document.getElementById('area-activities-table');
            tbody.innerHTML = '<tr><td colspan="7" class="text-center" style="padding:2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';
            
            // Fetch area statistics from PHP endpoint
            fetch('../php/get_area_stats.php?area=' + encodeURIComponent(areaName))
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (!data.success) {
                        console.error('Failed to load area data:', data.message);
                        tbody.innerHTML = '<tr><td colspan="7" class="text-center" style="color:red;">Failed to load data.</td></tr>';
                        return;
                    }
                    
                    var stats = data.data;
                    
                    // Update statistics panel
                    document.getElementById('area-total-midwives').textContent = stats.active_midwives;
                    document.getElementById('area-today-activities').textContent = stats.today_activities;
                    document.getElementById('area-week-activities').textContent = stats.week_activities;
                    document.getElementById('area-completed-activities').textContent = stats.completed_activities;
                    document.getElementById('area-pending-activities').textContent = stats.pending_activities;
                    
                    // Update activity breakdown
                    var breakdownMap = {
                        'HOME_VISIT': 'area-home-visits',
                        'VACCINATION': 'area-vaccinations',
                        'CLINIC_VISIT': 'area-clinic-visits',
                        'COUNSELING': 'area-counseling',
                        'HEALTH_EDUCATION': 'area-health-education',
                        'EMERGENCY_RESPONSE': 'area-emergency',
                        'MEETING': 'area-meetings'
                    };
                    
                    // Reset all counts to 0
                    Object.values(breakdownMap).forEach(function(id) {
                        document.getElementById(id).textContent = '0';
                    });
                    
                    // Update counts from data
                    stats.activity_breakdown.forEach(function(activity) {
                        var elementId = breakdownMap[activity.type_code];
                        if (elementId) {
                            document.getElementById(elementId).textContent = activity.count;
                        }
                    });
                    
                    // Update activities table
                    renderAreaActivities(stats.recent_activities);
                })
                .catch(function(error) {
                    console.error('Error loading area data:', error);
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center" style="color:red;">Network error.</td></tr>';
                });
        }

        function renderAreaActivities(activities) {
            var tbody = document.getElementById('area-activities-table');
            
            if (!activities || activities.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center" style="padding:2rem;color:var(--text-muted);">No activities found for this area.</td></tr>';
                return;
            }
            
            tbody.innerHTML = activities.map(function(a) {
                var statusCls = a.status === 'completed' ? 'status-active' : a.status === 'pending' ? 'status-on-leave' : 'status-inactive';
                var priBadge = '';
                if (a.priority_level && a.priority_level !== 'normal' && a.priority_level !== '') {
                    var priCls = (a.priority_level === 'critical') ? 'priority-critical' : 'priority-high';
                    priBadge = '<span class="status-badge ' + priCls + '" style="display:inline-block;">' + a.priority_level.toUpperCase() + '</span>';
                }
                var patient = a.patient_name ? ('<strong>' + a.patient_name + '</strong>' + (a.patient_age ? ' (' + a.patient_age + 'y)' : '') + '<br>') : '';
                var desc = a.description ? a.description.substring(0, 55) + (a.description.length > 55 ? '&hellip;' : '') : '';
                
                return '<tr>'
                    + '<td style="white-space:nowrap;"><strong>' + a.activity_date + '</strong><br><small style="color:var(--text-muted);">' + a.start_time + (a.end_time ? ' &ndash; ' + a.end_time : '') + '</small></td>'
                    + '<td><strong>' + a.midwife_name + '</strong><br><small style="color:var(--text-muted);">' + a.assigned_area + '</small></td>'
                    + '<td><span class="type-pill ' + a.type_code + '">' + a.type_name + '</span></td>'
                    + '<td>' + patient + '<small>' + desc + '</small></td>'
                    + '<td><small>' + (a.location || '&mdash;') + '</small></td>'
                    + '<td><span class="status-badge ' + statusCls + '">' + a.status + '</span></td>'
                    + '<td><button class="btn btn-sm btn-info" onclick="viewActivityDetail(' + a.activity_id + ')" title="View Details"><i class="fas fa-eye"></i></button></td>'
                    + '</tr>';
            }).join('');
        }

        function loadAreaOverview() {
            var areas = ['Udathuththiripitiya', 'Kahambilihena', 'Opathella', 'Ambalangoda'];
            
            areas.forEach(function(area) {
                fetch('../php/get_area_stats.php?area=' + encodeURIComponent(area))
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        if (data.success) {
                            var stats = data.data;
                            var areaSlug = area.toLowerCase().replace(/[^a-z0-9]/g, '-');
                            
                            // Update area card statistics
                            var midwivesEl = document.getElementById('area-' + areaSlug + '-midwives');
                            var activitiesEl = document.getElementById('area-' + areaSlug + '-activities');
                            var lastActivityEl = document.getElementById('area-' + areaSlug + '-last');
                            
                            if (midwivesEl) midwivesEl.textContent = stats.active_midwives;
                            if (activitiesEl) activitiesEl.textContent = stats.today_activities;
                            if (lastActivityEl) lastActivityEl.textContent = stats.last_activity;
                        }
                    })
                    .catch(function(error) {
                        console.error('Error loading area overview for', area, ':', error);
                    });
            });
        }

        // Area-based activity section functions
        var selectedArea = null;

        function selectArea(areaName) {
            selectedArea = areaName;
            
            // Update card selection
            var cards = document.querySelectorAll('.area-card');
            cards.forEach(function(card) {
                card.classList.remove('selected');
            });
            
            var selectedCard = document.querySelector('[data-area="' + areaName + '"]');
            if (selectedCard) {
                selectedCard.classList.add('selected');
            }
            
            // Show area details panel
            var detailsPanel = document.getElementById('areaDetailsPanel');
            detailsPanel.style.display = 'block';
            
            // Update area title
            var areaTitle = document.getElementById('areaTitle');
            areaTitle.textContent = areaName + ' Area Schedule';
            
            // Load area activities
            loadAreaActivities(areaName);
        }

        function loadAreaActivities(areaName) {
            var tbody = document.getElementById('areaActivitiesBody');
            tbody.innerHTML = '<tr><td colspan="5" class="text-center" style="padding: 2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';
            
            // Fetch area activities from PHP endpoint
            fetch('../php/get_area_stats.php?area=' + encodeURIComponent(areaName))
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (!data.success) {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center" style="color: red;">Failed to load activities.</td></tr>';
                        return;
                    }
                    
                    var activities = data.data.recent_activities;
                    
                    if (!activities || activities.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center" style="padding: 2rem; color: var(--text-muted);">No activities found for this area.</td></tr>';
                        return;
                    }
                    
                    // Render activities in table format
                    tbody.innerHTML = activities.map(function(activity) {
                        var time = activity.start_time || 'N/A';
                        var activityName = activity.type_name || 'Unknown Activity';
                        var location = activity.location || 'N/A';
                        var status = activity.status || 'unknown';
                        var statusClass = getStatusClass(status);
                        
                        return '<tr>' +
                            '<td>' + formatTime(time) + '</td>' +
                            '<td>' + activityName + (activity.patient_name ? ' - ' + activity.patient_name : '') + '</td>' +
                            '<td>' + location + '</td>' +
                            '<td><span class="status-badge ' + statusClass + '">' + status.charAt(0).toUpperCase() + status.slice(1) + '</span></td>' +
                            '<td><button class="btn btn-sm btn-info" onclick="viewActivityDetail(' + activity.activity_id + ')">View</button></td>' +
                            '</tr>';
                    }).join('');
                })
                .catch(function(error) {
                    console.error('Error loading area activities:', error);
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center" style="color: red;">Network error.</td></tr>';
                });
        }

        function formatTime(time) {
            if (!time) return 'N/A';
            var [hours, minutes] = time.split(':');
            var hour = parseInt(hours);
            var ampm = hour >= 12 ? 'PM' : 'AM';
            hour = hour % 12;
            hour = hour ? hour : 12;
            return hour + ':' + minutes + ' ' + ampm;
        }

        function getStatusClass(status) {
            switch(status.toLowerCase()) {
                case 'completed': return 'completed';
                case 'pending': return 'pending';
                case 'in_progress': return 'scheduled';
                default: return 'scheduled';
            }
        }
    </script>

    <script src="../js/admin/admin-clinics.js"></script>
    <script src="../js/admin/admin-vaccinations.js"></script>
    <script src="../js/admin/admin-home-visits.js"></script>

    </body>
</html>