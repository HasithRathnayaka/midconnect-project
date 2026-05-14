<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'midwife') {
    header('Location: ../midwife-login.html');
    exit;
}

echo '<script>';
echo 'console.log("Session:", ' . json_encode($_SESSION) . ');';
echo '</script>';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Midwife Dashboard - MidConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/midwife/midwife-dashbord.css">
    <style>
        .welcome-banner {
            background: linear-gradient(135deg, var(--accent-teal), var(--secondary-light-green));
            color: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .welcome-banner h2,
        .welcome-banner p,
        .welcome-banner .progress-text {
            color: #ffffff;
        }

        .welcome-banner .progress-ring .background {
            stroke: rgba(255, 255, 255, 0.35);
        }

        .welcome-banner .progress-ring .progress {
            stroke: #ffffff;
        }

        :root[data-theme='dark'] .welcome-banner {
            background: linear-gradient(135deg, #0f2d47, #146482);
        }

        .activity-form {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-light);
        }

        .quick-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.5rem;
            border: 2px solid #e9ecef;
            border-radius: var(--radius-lg);
            text-decoration: none;
            color: var(--text-primary);
            transition: var(--transition-medium);
            height: 120px;
            justify-content: center;
        }

        .quick-action-btn:hover {
            border-color: var(--primary-blue);
            background-color: var(--bg-secondary);
            transform: translateY(-2px);
        }

        .quick-action-btn i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--primary-blue);
        }

        .schedule-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-left: 4px solid var(--primary-blue);
            background: var(--bg-secondary);
            border-radius: 0 var(--radius-md) var(--radius-md) 0;
            margin-bottom: 0.5rem;
        }

        .schedule-time {
            font-weight: 600;
            color: var(--primary-blue);
            margin-right: 1rem;
            min-width: 80px;
        }

        .activity-log-item {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .activity-log-item:last-child {
            border-bottom: none;
        }

        .progress-ring {
            width: 120px;
            height: 120px;
            position: relative;
        }

        .progress-ring circle {
            fill: none;
            stroke-width: 8;
        }

        .progress-ring .background {
            stroke: #e9ecef;
        }

        .progress-ring .progress {
            stroke: var(--secondary-green);
            stroke-linecap: round;
            transition: stroke-dashoffset 0.5s ease;
        }

        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-blue);
        }

        /* Home Visits Styles */
        .visit-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .visit-item {
            display: flex;
            background: var(--white);
            border: 1px solid #e9ecef;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            transition: var(--transition-medium);
            position: relative;
        }

        .visit-item:hover {
            box-shadow: var(--shadow-medium);
        }

        .visit-item.priority-high {
            border-left: 5px solid #dc3545;
        }

        .visit-item.priority-normal {
            border-left: 5px solid var(--primary-blue);
        }

        .visit-time {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 100px;
            margin-right: 1.5rem;
            text-align: center;
        }

        .visit-time .time {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-blue);
        }

        .visit-time .duration {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .visit-details {
            flex: 1;
            margin-right: 1rem;
        }

        .visit-details h5 {
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .visit-details .address {
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .visit-details .address i {
            color: var(--accent-teal);
            margin-right: 0.5rem;
        }

        .visit-type {
            margin-bottom: 0.5rem;
        }

        .visit-details .notes {
            font-size: 0.9rem;
            color: var(--text-muted);
            font-style: italic;
        }

        .visit-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            min-width: 120px;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: var(--radius-sm);
            margin-right: 0.5rem;
        }

        .badge-urgent {
            background-color: #dc3545;
            color: white;
        }

        .badge-success {
            background-color: var(--secondary-green);
            color: white;
        }

        .badge-primary {
            background-color: var(--primary-blue);
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-info {
            background-color: var(--accent-teal);
            color: white;
        }

        .tab-container {
            margin-bottom: 2rem;
        }

        .nav-tabs {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            border-bottom: 2px solid #e9ecef;
        }

        .nav-item {
            margin-right: 1rem;
        }

        .nav-link {
            display: block;
            padding: 0.75rem 1rem;
            text-decoration: none;
            color: var(--text-muted);
            border-bottom: 3px solid transparent;
            transition: var(--transition-medium);
        }

        .nav-link.active {
            color: var(--primary-blue);
            border-bottom-color: var(--primary-blue);
            font-weight: 600;
        }

        .nav-link:hover {
            color: var(--primary-blue);
        }

        .tab-content {
            margin-top: 1rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .summary-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: var(--white);
            margin: 5% auto;
            padding: 0;
            border-radius: var(--radius-lg);
            width: 90%;
            max-width: 600px;
            box-shadow: var(--shadow-heavy);
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            color: var(--primary-blue);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .close {
            color: var(--text-muted);
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
        }

        .close:hover {
            color: var(--text-primary);
        }

        .duty-area-card {
            background: var(--white);
            border: 2px solid #e9ecef;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: var(--transition-medium);
            height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .duty-area-card:hover {
            border-color: var(--primary-blue);
            box-shadow: var(--shadow-light);
            transform: translateY(-2px);
        }

        .duty-area-card.active {
            border-color: var(--secondary-green);
            background: linear-gradient(135deg, #f8fff8, #e8f5e8);
        }

        .duty-area-icon {
            font-size: 2rem;
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
        }

        .duty-area-card.active .duty-area-icon {
            color: var(--secondary-green);
        }

        .duty-area-name {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .duty-area-status .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .status-badge.status-secondary {
            background: #6c757d;
            color: white;
        }

        .summary-stat {
            text-align: center;
            padding: 1rem;
            background: var(--bg-secondary);
            border-radius: var(--radius-md);
        }

        .summary-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-blue);
        }

        .summary-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        .editable-stat {
            cursor: pointer;
            position: relative;
            transition: var(--transition-medium);
        }

        .editable-stat:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .edit-hint,
        .auto-calc-hint {
            font-size: 0.7rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
            opacity: 0;
            transition: var(--transition-fast);
        }

        .editable-stat:hover .edit-hint {
            opacity: 1;
        }

        .auto-calc-hint {
            opacity: 0.6;
        }

        .editable-summary {
            cursor: pointer;
            padding: 0.75rem 0.5rem;
            margin: -0.25rem;
            border-radius: var(--radius-sm);
            transition: var(--transition-fast);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .editable-summary:hover {
            background-color: var(--bg-secondary);
        }

        .edit-icon {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-left: 0.5rem;
            opacity: 0;
            transition: var(--transition-fast);
        }

        .editable-summary:hover .edit-icon {
            opacity: 1;
        }

        .completed-visit-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .completed-visit-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--bg-secondary);
            border-radius: var(--radius-md);
            border-left: 4px solid var(--secondary-green);
        }

        .visit-timestamp {
            display: flex;
            flex-direction: column;
            min-width: 120px;
            margin-right: 1rem;
        }

        .visit-timestamp .date {
            font-weight: 600;
            color: var(--primary-blue);
        }

        .visit-timestamp .time {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .visit-summary {
            flex: 1;
        }

        .visit-summary h5 {
            margin-bottom: 0.25rem;
        }

        .visit-summary .visit-type {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .visit-summary .outcome {
            font-size: 0.9rem;
        }

        .route-map {
            height: 400px;
            border: 2px solid #e9ecef;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .map-placeholder {
            text-align: center;
            color: var(--text-muted);
        }

        .map-placeholder i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--accent-teal);
        }

        .route-summary {
            padding: 1rem;
        }

        .route-stats {
            margin-bottom: 2rem;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-item .label {
            color: var(--text-muted);
        }

        .stat-item .value {
            font-weight: 600;
            color: var(--primary-blue);
        }

        .route-order {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .route-stop {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            background: var(--bg-secondary);
            border-radius: var(--radius-md);
        }

        .stop-number {
            width: 30px;
            height: 30px;
            background: var(--primary-blue);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 1rem;
        }

        .stop-name {
            font-weight: 500;
        }

        /* Vaccination Styles */
        .vaccination-schedule {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .vaccination-item {
            display: flex;
            background: var(--white);
            border: 1px solid #e9ecef;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            transition: var(--transition-medium);
        }

        .vaccination-item:hover {
            box-shadow: var(--shadow-medium);
        }

        .vaccination-item.high-priority {
            border-left: 5px solid #dc3545;
        }

        .vaccination-item.normal-priority {
            border-left: 5px solid var(--primary-blue);
        }

        .vaccine-time {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 100px;
            margin-right: 1.5rem;
            text-align: center;
        }

        .vaccine-time .time {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-blue);
        }

        .vaccine-time .duration {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .vaccine-details {
            flex: 1;
            margin-right: 1rem;
        }

        .vaccine-details h5 {
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .patient-info {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .patient-info i {
            color: var(--accent-teal);
            margin-right: 0.25rem;
        }

        .vaccine-info {
            margin-bottom: 0.5rem;
        }

        .vaccine-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: var(--radius-sm);
            margin-right: 0.5rem;
        }

        .vaccine-badge.pediatric {
            background-color: #e3f2fd;
            color: #1976d2;
            border: 1px solid #bbdefb;
        }

        .vaccine-badge.maternal {
            background-color: #fce4ec;
            color: #c2185b;
            border: 1px solid #f8bbd9;
        }

        .vaccine-badge.adult {
            background-color: #e8f5e8;
            color: #388e3c;
            border: 1px solid #c8e6c9;
        }

        .vaccine-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            min-width: 120px;
        }

        .inventory-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .inventory-item {
            background: var(--white);
            border: 1px solid #e9ecef;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            text-align: center;
            transition: var(--transition-medium);
        }

        .inventory-item:hover {
            box-shadow: var(--shadow-medium);
        }

        .inventory-item.good-stock {
            border-left: 5px solid var(--secondary-green);
        }

        .inventory-item.low-stock {
            border-left: 5px solid #ffc107;
        }

        .inventory-item.critical-stock {
            border-left: 5px solid #dc3545;
        }

        .vaccine-icon {
            margin-bottom: 1rem;
        }

        .vaccine-icon i {
            font-size: 2rem;
            color: var(--primary-blue);
        }

        .vaccine-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .stock-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .stock-level {
            font-weight: 600;
            color: var(--primary-blue);
        }

        .expiry-date {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .stock-status {
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .stock-status.good {
            background-color: var(--secondary-green);
            color: white;
        }

        .stock-status.low {
            background-color: #ffc107;
            color: #212529;
        }

        .stock-status.critical {
            background-color: #dc3545;
            color: white;
        }

        .completion-bar {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .completion-bar>div {
            flex: 1;
            height: 20px;
            background: #e9ecef;
            border-radius: var(--radius-full);
            position: relative;
            overflow: hidden;
        }

        .completion-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--secondary-green), var(--accent-teal));
            border-radius: var(--radius-full);
            transition: width 0.3s ease;
        }

        .completion-bar span {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--primary-blue);
            min-width: 35px;
        }

        .due-soon {
            color: #ffc107;
            font-weight: 600;
        }

        .due-later {
            color: var(--secondary-green);
        }

        .overdue-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .overdue-item {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            background: #fff5f5;
            border: 1px solid #fed7d7;
            border-left: 5px solid #dc3545;
            border-radius: var(--radius-lg);
        }

        .overdue-info {
            flex: 1;
            margin-right: 1rem;
        }

        .overdue-info h5 {
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .vaccine-details {
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .overdue-duration {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .overdue-duration i {
            color: #dc3545;
        }

        .overdue-text {
            color: #dc3545;
            font-weight: 600;
        }

        .contact-info {
            margin-right: 1rem;
            min-width: 200px;
        }

        .contact-info p {
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .contact-info i {
            color: var(--accent-teal);
            margin-right: 0.5rem;
        }

        .overdue-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            min-width: 120px;
        }

        /* Profile Styles */
        .profile-picture-container {
            position: relative;
            display: inline-block;
            margin: 0 auto;
        }

        .profile-picture {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-teal), var(--primary-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            position: relative;
            overflow: hidden;
            cursor: zoom-in;
        }

        .profile-picture img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center 20%;
            border-radius: 50%;
        }

        .profile-picture-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--transition-medium);
            cursor: pointer;
            color: white;
        }

        .profile-picture:focus-visible {
            outline: 3px solid rgba(0, 166, 153, 0.65);
            outline-offset: 4px;
        }

        .profile-image-preview {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.92);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 3000;
            padding: 0;
            cursor: zoom-out;
        }

        .profile-image-preview.show {
            display: flex;
        }

        .profile-image-preview img {
            width: 100vw;
            height: 100vh;
            max-width: none;
            max-height: none;
            border-radius: 0;
            box-shadow: none;
            object-fit: contain;
            cursor: default;
        }

        .profile-image-preview .preview-close-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.45);
            background: rgba(0, 0, 0, 0.35);
            color: #fff;
            font-size: 1.6rem;
            line-height: 1;
            cursor: pointer;
        }

        .profile-image-preview .preview-close-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .profile-picture-container:hover .profile-picture-overlay {
            opacity: 1;
        }

        .profile-picture-overlay i {
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }

        .profile-picture-overlay span {
            font-size: 0.75rem;
        }

        .profile-badges {
            margin-top: 1rem;
        }

        .profile-badges .badge {
            margin: 0 0.25rem;
        }

        .stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .stat-row:last-child {
            border-bottom: none;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .stat-value {
            font-weight: 600;
            color: var(--primary-blue);
        }

        .qualification-item,
        .training-item,
        .membership-item {
            margin-bottom: 0.5rem;
        }

        .qualification-item h6,
        .training-item h6,
        .membership-item h6 {
            color: var(--primary-blue);
            margin-bottom: 0.25rem;
        }

        .institution,
        .training-provider {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .year,
        .training-date {
            color: var(--text-secondary);
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }

        .grade {
            font-weight: 600;
            color: var(--secondary-green);
            font-size: 0.9rem;
        }

        .performance-metric {
            background: var(--white);
            border: 1px solid #e9ecef;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            text-align: center;
            transition: var(--transition-medium);
        }

        .performance-metric:hover {
            box-shadow: var(--shadow-medium);
        }

        .metric-icon {
            margin-bottom: 1rem;
        }

        .metric-icon i {
            font-size: 2.5rem;
            color: var(--primary-blue);
        }

        .metric-info h3 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .metric-info span {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .metric-change {
            margin-top: 0.5rem;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .metric-change.positive {
            color: var(--secondary-green);
        }

        .metric-change i {
            margin-right: 0.25rem;
        }

        .performance-score {
            margin: 2rem 0;
        }

        .score-circle {
            width: 120px;
            height: 120px;
            border: 8px solid var(--bg-secondary);
            border-top: 8px solid var(--accent-teal);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            position: relative;
        }

        .score-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-blue);
        }

        .score-max {
            font-size: 1rem;
            color: var(--text-muted);
        }

        .rating-breakdown {
            margin-top: 1.5rem;
        }

        .rating-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .rating-item span {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .rating-stars {
            display: flex;
            gap: 0.25rem;
        }

        .rating-stars i {
            color: #ffc107;
            font-size: 0.9rem;
        }

        .setting-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 1rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .setting-item:last-child {
            border-bottom: none;
        }

        .setting-info {
            flex: 1;
            margin-right: 1rem;
        }

        .setting-info h6 {
            margin-bottom: 0.25rem;
            color: var(--text-primary);
        }

        .setting-info p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin: 0;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: var(--accent-teal);
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        /* Duty Area Selection Styles */
        .duty-areas-container {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .duty-areas-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .duty-areas-title i {
            color: var(--primary-blue);
        }

        .duty-areas-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        .duty-area-btn {
            background: linear-gradient(135deg, #fff, #f8f9fa);
            border: 2px solid #dee2e6;
            border-radius: var(--radius-md);
            padding: 1.5rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .duty-area-btn:hover {
            border-color: var(--primary-blue);
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        }

        .duty-area-btn.active {
            border-color: var(--secondary-green);
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
            }

            50% {
                box-shadow: 0 4px 16px rgba(76, 175, 80, 0.5);
            }
        }

        .duty-area-btn i {
            font-size: 2rem;
            color: var(--primary-blue);
        }

        .duty-area-btn.active i {
            color: var(--secondary-green);
        }

        .duty-area-btn h5 {
            font-size: 0.95rem;
            font-weight: 600;
            margin: 0;
            color: var(--text-primary);
        }

        .area-count {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .area-content-wrapper {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .area-content-wrapper.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .area-header {
            background: var(--bg-secondary);
            color: var(--text-primary);
            padding: 1.25rem 1.5rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--secondary-green);
        }

        .area-header h4 {
            margin: 0 0 0.5rem 0;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-primary);
        }

        .area-header h4 i {
            color: var(--secondary-green);
        }

        .area-header p {
            margin: 0;
            font-size: 0.95rem;
            color: var(--text-secondary);
        }

        :root[data-theme='dark'] .duty-areas-container {
            background: linear-gradient(135deg, #1b2638, #24364d);
            border: 1px solid #334a66;
        }

        :root[data-theme='dark'] .duty-areas-title {
            color: #e5edf8;
        }

        :root[data-theme='dark'] .duty-areas-title i {
            color: #66b3ff;
        }

        :root[data-theme='dark'] .duty-area-btn {
            background: linear-gradient(135deg, #2a3a52, #314762);
            border-color: #3e5777;
        }

        :root[data-theme='dark'] .duty-area-btn:hover {
            border-color: #66b3ff;
            background: linear-gradient(135deg, #314e71, #3a5a80);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35);
        }

        :root[data-theme='dark'] .duty-area-btn.active {
            border-color: #31c9bb;
            background: linear-gradient(135deg, #264a49, #2f5f5d);
            box-shadow: 0 4px 14px rgba(49, 201, 187, 0.28);
        }

        :root[data-theme='dark'] .duty-area-btn i {
            color: #7cbcff;
        }

        :root[data-theme='dark'] .duty-area-btn.active i {
            color: #31c9bb;
        }

        :root[data-theme='dark'] .duty-area-btn h5 {
            color: #e5edf8;
        }

        :root[data-theme='dark'] .area-count {
            color: #bfd0e5;
        }

        :root[data-theme='dark'] .area-header {
            background: linear-gradient(135deg, #17263a, #1f324a);
            border-left-color: #31c9bb;
        }

        :root[data-theme='dark'] .area-header h4 {
            color: #e5edf8;
        }

        :root[data-theme='dark'] .area-header p {
            color: #bfd0e5;
        }

        /* Modern Duty Area Cards */
        .duty-area-card-modern {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-light);
            border: 1px solid #e9ecef;
            transition: var(--transition-medium);
            height: 100%;
        }

        .duty-area-card-modern:hover {
            box-shadow: var(--shadow-medium);
            transform: translateY(-2px);
        }

        .duty-area-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f1f3f4;
        }

        .duty-area-icon-large {
            width: 60px;
            height: 60px;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-teal));
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .duty-area-icon-large i {
            font-size: 1.5rem;
            color: white;
        }

        .duty-area-info h4 {
            margin: 0 0 0.5rem 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .status-badge.status-primary {
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-teal));
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .duty-area-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        .stat-item {
            text-align: center;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: var(--radius-md);
            transition: var(--transition-medium);
        }

        .stat-item:hover {
            background: #e9ecef;
            transform: translateY(-1px);
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Compact Duty Area Cards */
        .duty-area-container {
            padding: 16px 0 20px;
        }

        .duty-areas-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        .duty-area-btn {
            background: #ffffff;
            border: 2px solid #dfe6ea;
            border-radius: 14px;
            height: 120px;
            transition: all 0.25s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .duty-area-btn i {
            font-size: 28px;
            color: #0b3b5a;
        }

        .duty-area-btn h5 {
            font-size: 15px;
            font-weight: 600;
            color: #0b3b5a;
            margin: 0;
        }

        .area-count {
            font-size: 13px;
            color: #7a8793;
        }

        .duty-area-btn:hover {
            border-color: #10b9a7;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
        }

        .duty-area-btn.active {
            background: #e7f6ec;
            border-color: #10b9a7;
        }

        .duty-area-btn.active i {
            color: #10b9a7;
        }

        @media (max-width: 1024px) {
            .duty-areas-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .duty-areas-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Dark theme for modern cards */
        :root[data-theme='dark'] .duty-area-card-modern {
            background: #1f324a;
            border-color: #2c3e50;
        }

        :root[data-theme='dark'] .duty-area-header {
            border-color: #2c3e50;
        }

        :root[data-theme='dark'] .duty-area-info h4 {
            color: #e5edf8;
        }

        :root[data-theme='dark'] .stat-item {
            background: #17263a;
        }

        :root[data-theme='dark'] .stat-item:hover {
            background: #0f1929;
        }

        :root[data-theme='dark'] .stat-number {
            color: #31c9bb;
        }

        :root[data-theme='dark'] .stat-label {
            color: #8892a6;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .duty-area-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }

            .duty-area-header {
                margin-bottom: 1rem;
            }

            .duty-area-icon-large {
                width: 50px;
                height: 50px;
            }

            .duty-area-icon-large i {
                font-size: 1.25rem;
            }

            .duty-area-info h4 {
                font-size: 1.1rem;
            }

            .stat-number {
                font-size: 1.25rem;
            }
        }

        @media (max-width: 480px) {
            .duty-area-card-modern {
                padding: 1rem;
            }

            .duty-area-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
            }

            .stat-item {
                padding: 0.5rem;
            }

            .stat-number {
                font-size: 1.1rem;
            }

            .stat-label {
                font-size: 0.75rem;
            }
        }

        /* Timetable Styles */
        .timetable-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .timetable-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--white);
            border: 1px solid #e9ecef;
            border-radius: var(--radius-lg);
            transition: var(--transition-medium);
            position: relative;
        }

        .timetable-item:hover {
            box-shadow: var(--shadow-medium);
            transform: translateY(-1px);
        }

        .timetable-item.priority-high {
            border-left: 4px solid #dc3545;
        }

        .timetable-item.priority-normal {
            border-left: 4px solid var(--primary-blue);
        }

        .timetable-item.priority-low {
            border-left: 4px solid var(--secondary-green);
        }

        .timetable-time {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 100px;
            margin-right: 1rem;
            text-align: center;
        }

        .timetable-time .time {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-blue);
        }

        .timetable-time .date {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .timetable-details {
            flex: 1;
            margin-right: 1rem;
        }

        .timetable-details h6 {
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .timetable-details h6 i {
            color: var(--primary-blue);
        }

        .timetable-details .location,
        .timetable-details .duration {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 0.25rem;
        }

        .timetable-details .location i,
        .timetable-details .duration i {
            margin-right: 0.5rem;
            width: 14px;
        }

        .timetable-actions {
            display: flex;
            gap: 0.5rem;
            min-width: 120px;
            justify-content: flex-end;
        }

        .timetable-controls {
            background: var(--bg-secondary);
            padding: 1rem;
            border-radius: var(--radius-md);
        }

        .timetable-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-teal));
            color: white;
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1rem;
            text-align: center;
        }

        .timetable-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .timetable-summary {
            background: var(--bg-secondary);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
        }

        .timetable-summary h5 {
            margin-bottom: 1rem;
            color: var(--text-primary);
        }

        .summary-card {
            text-align: center;
            padding: 1rem;
            background: var(--white);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-light);
        }

        .summary-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 0.25rem;
        }

        .summary-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        :root[data-theme='dark'] .timetable-item {
            background: #2a3a52;
            border-color: #3e5777;
        }

        :root[data-theme='dark'] .timetable-details h6 {
            color: #e5edf8;
        }

        :root[data-theme='dark'] .timetable-controls,
        :root[data-theme='dark'] .timetable-summary {
            background: #1f324a;
        }

        :root[data-theme='dark'] .summary-card {
            background: #2a3a52;
        }

        /* counseling-session.html styles */
        .counseling-hero {
            background: linear-gradient(135deg, var(--accent-teal), var(--secondary-light-green));
            color: white;
            border-radius: var(--radius-lg);
            padding: 1.75rem 2rem;
            margin-bottom: 1.75rem;
        }

        .counseling-hero h1 {
            font-size: 1.5rem;
            margin-bottom: 0.35rem;
        }

        .counseling-hero p {
            opacity: 0.95;
            margin: 0;
            font-size: 0.95rem;
        }

        :root[data-theme='dark'] .counseling-hero {
            background: linear-gradient(135deg, #0f2d47, #146482);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-badge.status-active {
            background-color: #d4edda;
            color: #155724;
        }

        /* health-education-session.html styles */
        .health-hero {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-dark-green));
            color: #ffffff;
            border-radius: var(--radius-lg);
            padding: 1.75rem 2rem;
            margin-bottom: 1.75rem;
        }

        .health-hero h1 {
            font-size: 1.5rem;
            margin-bottom: 0.35rem;
            color: #ffffff !important;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.35);
        }

        .health-hero p {
            opacity: 0.95;
            margin: 0;
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.95) !important;
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.25);
        }

        :root[data-theme='dark'] .health-hero {
            background: linear-gradient(135deg, #1e3a5f, #0d4a44);
        }

        .topic-pill {
            display: inline-block;
            padding: 0.25rem 0.6rem;
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            background: var(--bg-secondary);
            color: var(--text-primary);
            margin-right: 0.35rem;
        }

        /* emergency-responses.html styles */
        .emergency-hero {
            background: linear-gradient(135deg, #c0392b, #e74c3c);
            color: white;
            border-radius: var(--radius-lg);
            padding: 1.75rem 2rem;
            margin-bottom: 1.75rem;
        }

        .emergency-hero h1 {
            font-size: 1.5rem;
            margin-bottom: 0.35rem;
        }

        .emergency-hero p {
            opacity: 0.95;
            margin: 0;
            font-size: 0.95rem;
        }

        :root[data-theme='dark'] .emergency-hero {
            background: linear-gradient(135deg, #7f1d1d, #991b1b);
        }

        .emergency-tile {
            background: var(--white);
            border-radius: var(--radius-md);
            padding: 1rem 1.25rem;
            border: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }

        .emergency-tile i {
            font-size: 1.5rem;
            color: var(--accent-red);
            min-width: 2rem;
            text-align: center;
        }

        .emergency-tile strong {
            display: block;
            color: var(--text-primary);
        }

        .emergency-tile span {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .priority-critical {
            border-left: 5px solid #c0392b;
        }

        .priority-high {
            border-left: 5px solid #fd7e14;
        }

        .priority-moderate {
            border-left: 5px solid var(--primary-blue);
        }


        #addScheduleModal,
        #scheduleVisitModal,
        #timetableModal,
        #scheduleVaccinationModal,
        #updateVaccineInventoryModal,
        #triposhaInventoryModal,
        #triposhaDistributionModal,
        #pregnantMotherModal,
        #lactatingMotherModal,
        #postnatalMotherModal,
        #newbornModal,
        #youngChildModal,
        #childModal {
            z-index: 1060 !important;
        }

        #addScheduleModal .modal-dialog,
        #scheduleVisitModal .modal-dialog,
        #timetableModal .modal-dialog,
        #scheduleVaccinationModal .modal-dialog,
        #updateVaccineInventoryModal .modal-dialog,
        #triposhaInventoryModal .modal-dialog,
        #triposhaDistributionModal .modal-dialog,
        #pregnantMotherModal .modal-dialog,
        #lactatingMotherModal .modal-dialog,
        #postnatalMotherModal .modal-dialog,
        #newbornModal .modal-dialog,
        #youngChildModal .modal-dialog,
        #childModal .modal-dialog {
            z-index: 1070 !important;
            pointer-events: auto !important;
        }

        #addScheduleModal .modal-content,
        #scheduleVisitModal .modal-content,
        #timetableModal .modal-content,
        #scheduleVaccinationModal .modal-content,
        #updateVaccineInventoryModal .modal-content,
        #triposhaInventoryModal .modal-content,
        #triposhaDistributionModal .modal-content,
        #pregnantMotherModal .modal-content,
        #lactatingMotherModal .modal-content,
        #postnatalMotherModal .modal-content,
        #newbornModal .modal-content,
        #youngChildModal .modal-content,
        #childModal .modal-content {
            pointer-events: auto !important;
        }

        .modal-backdrop {
            z-index: 1050 !important;
        }

        body.modal-open {
            overflow: hidden;
        }

        .modal {
            pointer-events: auto !important;
        }

        /* ================================
   FIX: Modal label/input visibility
   Maternal & Child Care modals
================================ */

#pregnantMotherModal .modal-content,
#lactatingMotherModal .modal-content,
#postnatalMotherModal .modal-content,
#newbornModal .modal-content,
#youngChildModal .modal-content,
#childModal .modal-content {
    background: #111827 !important;
    color: #ffffff !important;
}

/* Modal headers */
#pregnantMotherModal .modal-header,
#lactatingMotherModal .modal-header,
#postnatalMotherModal .modal-header,
#newbornModal .modal-header,
#youngChildModal .modal-header,
#childModal .modal-header {
    background: #0f172a !important;
    border-bottom: 1px solid #334155 !important;
    color: #ffffff !important;
}

#pregnantMotherModal .modal-title,
#lactatingMotherModal .modal-title,
#postnatalMotherModal .modal-title,
#newbornModal .modal-title,
#youngChildModal .modal-title,
#childModal .modal-title {
    color: #ffffff !important;
    font-weight: 700 !important;
}

/* Labels */
#pregnantMotherModal label,
#lactatingMotherModal label,
#postnatalMotherModal label,
#newbornModal label,
#youngChildModal label,
#childModal label,
#pregnantMotherModal .form-label,
#lactatingMotherModal .form-label,
#postnatalMotherModal .form-label,
#newbornModal .form-label,
#youngChildModal .form-label,
#childModal .form-label {
    color: #e5e7eb !important;
    opacity: 1 !important;
    font-weight: 600 !important;
}

/* Inputs, selects, textareas */
#pregnantMotherModal .form-control,
#lactatingMotherModal .form-control,
#postnatalMotherModal .form-control,
#newbornModal .form-control,
#youngChildModal .form-control,
#childModal .form-control,
#pregnantMotherModal .form-select,
#lactatingMotherModal .form-select,
#postnatalMotherModal .form-select,
#newbornModal .form-select,
#youngChildModal .form-select,
#childModal .form-select {
    background-color: #0b1220 !important;
    color: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    opacity: 1 !important;
}

/* Placeholder text */
#pregnantMotherModal .form-control::placeholder,
#lactatingMotherModal .form-control::placeholder,
#postnatalMotherModal .form-control::placeholder,
#newbornModal .form-control::placeholder,
#youngChildModal .form-control::placeholder,
#childModal .form-control::placeholder {
    color: #94a3b8 !important;
    opacity: 1 !important;
}

/* Select dropdown option text */
#pregnantMotherModal select option,
#lactatingMotherModal select option,
#postnatalMotherModal select option,
#newbornModal select option,
#youngChildModal select option,
#childModal select option {
    background-color: #0b1220 !important;
    color: #ffffff !important;
}

/* Textarea */
#pregnantMotherModal textarea,
#lactatingMotherModal textarea,
#postnatalMotherModal textarea,
#newbornModal textarea,
#youngChildModal textarea,
#childModal textarea {
    background-color: #0b1220 !important;
    color: #ffffff !important;
}

/* Close button */
#pregnantMotherModal .close,
#lactatingMotherModal .close,
#postnatalMotherModal .close,
#newbornModal .close,
#youngChildModal .close,
#childModal .close {
    color: #ffffff !important;
    opacity: 1 !important;
    text-shadow: none !important;
}

/* Footer */
#pregnantMotherModal .modal-footer,
#lactatingMotherModal .modal-footer,
#postnatalMotherModal .modal-footer,
#newbornModal .modal-footer,
#youngChildModal .modal-footer,
#childModal .modal-footer {
    border-top: 1px solid #334155 !important;
    background: #111827 !important;
}

/* Modal z-index / disabled issue protection */
#pregnantMotherModal,
#lactatingMotherModal,
#postnatalMotherModal,
#newbornModal,
#youngChildModal,
#childModal {
    z-index: 1060 !important;
}

#pregnantMotherModal .modal-dialog,
#lactatingMotherModal .modal-dialog,
#postnatalMotherModal .modal-dialog,
#newbornModal .modal-dialog,
#youngChildModal .modal-dialog,
#childModal .modal-dialog {
    z-index: 1070 !important;
    pointer-events: auto !important;
}

#pregnantMotherModal .modal-content,
#lactatingMotherModal .modal-content,
#postnatalMotherModal .modal-content,
#newbornModal .modal-content,
#youngChildModal .modal-content,
#childModal .modal-content {
    pointer-events: auto !important;
}

.modal-backdrop {
    z-index: 1050 !important;
}

body.modal-open {
    overflow: hidden;
}


    </style>
</head>

<body class="dashboard-with-sidebar">
    <!-- START: navbar.php -->
    <header class="header">
        <div class="container">
            <div class="header-left">

                <div class="logo">
                    <img src="../images/logoimage.png" alt="MidConnect Logo">
                    <span>MidConnect</span>
                </div>
            </div>
            <nav>
                <ul class="nav-menu">
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
            <div class="sidebar-menu">
                <ul>
                    <li><a href="#dashboard" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="#log-activity"><i class="fas fa-plus-circle"></i> Log Activity</a></li>
                    <li><a href="#schedule"><i class="fas fa-calendar-alt"></i> My Schedule</a></li>
                    <li><a href="#home-visits"><i class="fas fa-home"></i> Home Visits</a></li>
                    <li><a href="#vaccinations"><i class="fas fa-syringe"></i> Vaccinations</a></li>
                    <li><a href="#triposha"><i class="fas fa-box"></i> Triposha Distribution</a></li>
                    <li><a href="#counseling-session"><i class="fas fa-comments"></i> Counseling Sessions</a></li>
                    <li><a href="#health-education-session"><i class="fas fa-chalkboard-teacher"></i> Health Education Session</a></li>
                    <li><a href="#patients"><i class="fas fa-heart"></i> Maternal & Child Care</a></li>
                    <li><a href="#profile"><i class="fas fa-user"></i> My Profile</a></li>
                </ul>
            </div>
        </div>
        <!-- END: sidebar.php -->

        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <div class="content-with-sidebar">
            <!-- Dashboard Overview -->
<div id="dashboard" class="content-section">
    <div class="welcome-banner">
        <div class="row">
            <div class="col-10">
                <h2 id="greeting-text">Good Morning, Madhavi!</h2>
                <p>
                    Ready to make a difference in your community today.
                    You have <span id="dashboardScheduledCount">0</span> scheduled activities.
                </p>
            </div>
        </div>
    </div>

    <!-- Dynamic Dashboard Widgets -->
    <div class="row" style="margin-bottom: 2rem;" id="dynamic-widgets-container">

        <!-- 1. Urgent Meetings -->
        <div class="col-6" style="margin-bottom: 1.5rem;">
            <div class="card" style="border-top: 4px solid var(--accent-red); height: 100%;">
                <div class="card-header">
                    <h4 class="card-title" style="color: var(--accent-red);">
                        <i class="fas fa-exclamation-triangle"></i> Urgent Meetings
                    </h4>
                </div>

                <div class="card-body" id="urgentMeetingsList" style="max-height: 250px; overflow-y: auto;">
                    <div style="text-align: center; color: var(--text-muted); padding: 1rem;">
                        <i class="fas fa-spinner fa-spin"></i> Loading...
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Upcoming Clinics -->
        <div class="col-6" style="margin-bottom: 1.5rem;">
            <div class="card" style="border-top: 4px solid var(--primary-blue); height: 100%;">
                <div class="card-header">
                    <h4 class="card-title" style="color: var(--primary-blue);">
                        <i class="fas fa-hospital"></i> Upcoming Clinics
                    </h4>
                </div>

                <div class="card-body" id="upcomingClinicsList" style="max-height: 250px; overflow-y: auto;">
                    <div style="text-align: center; color: var(--text-muted); padding: 1rem;">
                        <i class="fas fa-spinner fa-spin"></i> Loading...
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Today's Time Table -->
        <div class="col-6" style="margin-bottom: 1.5rem;">
            <div class="card" style="border-top: 4px solid var(--secondary-green); height: 100%;">
                <div class="card-header">
                    <h4 class="card-title" style="color: var(--secondary-green);">
                        <i class="fas fa-clock"></i> Today's Time Table
                    </h4>
                </div>

                <div class="card-body" id="todaysTimetableList" style="max-height: 250px; overflow-y: auto;">
                    <div style="text-align: center; color: var(--text-muted); padding: 1rem;">
                        <i class="fas fa-spinner fa-spin"></i> Loading...
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

            <!-- Log Activity Section -->
            <div id="log-activity" class="content-section" style="display: none;">
                <h2>Log New Activity</h2>
                <div class="activity-form">
                    <form id="createActivityForm" action="../php/midwife/create_activity.php" method="POST">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">Activity Type</label>
                                    <select class="form-control form-select" name="activity_type" required>
                                        <option value="">Select activity type</option>
                                        <option value="home_visit">Home Visit</option>
                                        <option value="clinic_visit">Clinic Visit</option>
                                        <option value="vaccination">Vaccination</option>
                                        <option value="counseling">Counseling Session</option>
                                        <option value="health_education">Health Education</option>
                                        <option value="emergency_response">Emergency Response</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">Date & Time</label>
                                    <input type="datetime-local" class="form-control" name="activity_datetime" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">Patient/Client Name</label>
                                    <input type="text" class="form-control" name="patient_name" placeholder="Enter patient name" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">Location</label>
                                    <input type="text" class="form-control" name="location" placeholder="Enter location" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Activity Description</label>
                            <textarea class="form-control" name="description" rows="4"
                                placeholder="Describe the activity, procedures performed, observations, etc."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="form-label">Duration (minutes)</label>
                                    <input type="number" class="form-control" name="duration" min="1" max="480" required>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="form-label">Follow-up Required</label>
                                    <select class="form-control form-select" name="followup_required">
                                        <option value="no">No</option>
                                        <option value="yes">Yes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="form-label">Priority Level</label>
                                    <select class="form-control form-select" name="priority">
                                        <option value="normal">Normal</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save"></i> Log Activity
                            </button>
                            <button type="reset" class="btn btn-secondary btn-lg">
                                <i class="fas fa-undo"></i> Reset Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Schedule Section -->
            <div id="schedule" class="content-section" style="display: none;">
                <div class="d-flex justify-between align-center mb-3">
                    <h2>My Schedule</h2>
                    <div>
                        <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#addScheduleModal">
                            <i class="fas fa-plus"></i> Add Schedule Item
                        </button>
                        <!-- if needed, you can uncomment the following button to enable the timetable modal for viewing detailed schedules. Make sure to implement the modal functionality in your JavaScript code to display the timetable when this button is clicked. -->
                        <!--                     
                        <button class="btn btn-info" type="button" data-toggle="modal" data-target="#timetableModal">
                            <i class="fas fa-calendar-alt"></i> My Timetable
                        </button> -->
                    </div>
                </div>



                <div class="duty-areas-container">
                    <div class="duty-areas-title">
                        <i class="fas fa-map-marker-alt"></i> Select Duty Area
                    </div>

                    <div class="duty-areas-grid">
                        <div class="duty-area-btn active" data-area="all" onclick="switchScheduleArea('all')">
                            <i class="fas fa-list"></i>
                            <h5>All Areas</h5>
                            <div class="area-count">All schedules</div>
                        </div>

                        <div class="duty-area-btn" data-area="Uduthuththiripitiya" onclick="switchScheduleArea('Uduthuththiripitiya')">
                            <i class="fas fa-home"></i>
                            <h5>Uduthuththiripitiya</h5>
                            <div class="area-count">View schedules</div>
                        </div>

                        <div class="duty-area-btn" data-area="Kahabilihena" onclick="switchScheduleArea('Kahabilihena')">
                            <i class="fas fa-hospital"></i>
                            <h5>Kahabilihena</h5>
                            <div class="area-count">View schedules</div>
                        </div>

                        <div class="duty-area-btn" data-area="Opathella" onclick="switchScheduleArea('Opathella')">
                            <i class="fas fa-city"></i>
                            <h5>Opathella</h5>
                            <div class="area-count">View schedules</div>
                        </div>

                        <div class="duty-area-btn" data-area="Ambalangoda" onclick="switchScheduleArea('Ambalangoda')">
                            <i class="fas fa-tree"></i>
                            <h5>Ambalangoda</h5>
                            <div class="area-count">View schedules</div>
                        </div>
                    </div>
                </div>

                <div class="area-content-wrapper active">
                    <div class="area-header">
                        <h4>
                            <i class="fas fa-map-marker-alt"></i>
                            <span id="selectedAreaTitle">All Area Schedule</span>
                        </h4>
                        <p id="selectedAreaInfo">Loading schedules...</p>
                    </div>

                    <div class="card">
                        <div class="card-body">
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

                                <tbody id="scheduleTableBody">
                                    <tr>
                                        <td colspan="5" style="text-align:center;">Loading schedules...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maternal and Child Care Section -->

            <!-- counseling-session Section -->
            <div id="counseling-session" class="content-section" style="display: none;">
                <div class="counseling-hero">
                    <h1><i class="fas fa-comments"></i> Counseling Sessions</h1>
                    <p>Record one-to-one or family counseling for antenatal, postnatal, and psychosocial support.</p>
                </div>

                <div class="row" style="margin-bottom: 1.5rem;">
                    <div class="col-6">
                        <div class="card" style="border-left: 4px solid var(--primary-blue); text-align: center; padding: 1.25rem;">
                            <div id="counselingSessionsThisMonth" style="font-size: 1.75rem; font-weight: 700; color: var(--primary-blue);">0</div>
                            <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.35rem;">
                                Sessions this month
                            </div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="card" style="border-left: 4px solid var(--secondary-green); text-align: center; padding: 1.25rem;">
                            <div id="counselingFollowupsScheduled" style="font-size: 1.75rem; font-weight: 700; color: var(--primary-blue);">0</div>
                            <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.35rem;">
                                Follow-ups scheduled
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card" style="margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h4 class="card-title">Log counseling session</h4>
                    </div>

                    <div class="card-body">
                        <form id="counselingForm" action="../php/midwife/create_counseling_session.php" method="POST">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Date &amp; time *</label>
                                        <input type="datetime-local" class="form-control" name="session_datetime" required>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Duration (minutes) *</label>
                                        <input type="number" class="form-control" name="duration_mins" min="5" max="240" value="30" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Client identifier *</label>
                                        <input type="text" class="form-control" name="client_ref" placeholder="e.g. initials or clinic number" required>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Session focus *</label>
                                        <select class="form-control form-select" name="focus" required>
                                            <option value="">Select</option>
                                            <option value="antenatal">Antenatal care &amp; birth planning</option>
                                            <option value="postnatal">Postnatal &amp; recovery</option>
                                            <option value="breastfeeding">Breastfeeding &amp; nutrition</option>
                                            <option value="family_planning">Family planning</option>
                                            <option value="mental_health">Mental health &amp; emotional support</option>
                                            <option value="gbv">Gender-based violence / safety</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Location</label>
                                <select class="form-control form-select" name="location_type">
                                    <option value="clinic">MOH clinic</option>
                                    <option value="home">Home visit</option>
                                    <option value="phone">Telephone</option>
                                    <option value="community">Community Center</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Summary &amp; advice given *</label>
                                <textarea class="form-control" name="notes" rows="4" placeholder="Brief notes (no unnecessary personal detail)" required></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Follow-up required</label>
                                <select class="form-control form-select" name="followup" id="counselingFollowupSelect">
                                    <option value="no">No</option>
                                    <option value="yes">Yes — schedule</option>
                                    <option value="referral">Referral to specialist</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Follow-up Date</label>
                                <input type="date" class="form-control" name="followup_date">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Referral Details</label>
                                <textarea class="form-control" name="referral_details" rows="2" placeholder="Add referral details if applicable"></textarea>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save session
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Recent counseling sessions</h4>
                    </div>

                    <div class="card-body" style="padding: 0;">
                        <div style="overflow-x: auto;">
                            <table class="table" style="width: 100%; margin: 0; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: var(--bg-secondary); text-align: left;">
                                        <th style="padding: 0.75rem 1rem;">Date</th>
                                        <th style="padding: 0.75rem 1rem;">Focus</th>
                                        <th style="padding: 0.75rem 1rem;">Location</th>
                                        <th style="padding: 0.75rem 1rem;">Duration</th>
                                        <th style="padding: 0.75rem 1rem;">Follow-up</th>
                                    </tr>
                                </thead>

                                <tbody id="counselingSessionTableBody">
                                    <tr>
                                        <td colspan="5" class="text-muted" style="padding: 0.75rem 1rem;">
                                            Loading counseling sessions...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- health-education-session Section -->
            <div id="health-education-session" class="content-section section-slide-in" style="display: none;">
                <div class="health-hero">
                    <h1><i class="fas fa-chalkboard-teacher"></i> Health education sessions</h1>
                    <p>Group talks, demonstrations, and community awareness on maternal and child health topics.</p>
                </div>

                <div class="row" style="margin-bottom: 1.5rem;">
                    <div class="col-4">
                        <div class="card" style="border-left: 4px solid var(--accent-orange); text-align: center; padding: 1.1rem;">
                            <div id="healthEdSessions90Days" style="font-size: 1.5rem; font-weight: 700; color: var(--primary-blue);">0</div>
                            <div style="font-size: 0.7rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase;">
                                Sessions (90 days)
                            </div>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="card" style="border-left: 4px solid var(--secondary-green); text-align: center; padding: 1.1rem;">
                            <div id="healthEdParticipantsReached" style="font-size: 1.5rem; font-weight: 700; color: var(--primary-blue);">0</div>
                            <div style="font-size: 0.7rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase;">
                                Participants reached
                            </div>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="card" style="border-left: 4px solid var(--primary-blue); text-align: center; padding: 1.1rem;">
                            <div id="healthEdUpcomingCount" style="font-size: 1.5rem; font-weight: 700; color: var(--primary-blue);">0</div>
                            <div style="font-size: 0.7rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase;">
                                Upcoming
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card" style="margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h4 class="card-title">Record health education session</h4>
                    </div>

                    <div class="card-body">
                        <form id="healthEdForm" action="../php/midwife/create_health_education_session.php" method="POST">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Session date *</label>
                                        <input type="date" class="form-control" name="session_date" required>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Venue *</label>
                                        <input type="text" class="form-control" name="venue" placeholder="e.g. MOH clinic hall, village temple" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Main topic *</label>
                                        <select class="form-control form-select" name="topic" required>
                                            <option value="">Select topic</option>
                                            <option value="nutrition">Maternal nutrition &amp; iron</option>
                                            <option value="danger_signs">Pregnancy danger signs</option>
                                            <option value="newborn">Newborn care &amp; warmth</option>
                                            <option value="immunization">Immunization schedule</option>
                                            <option value="fp">Family planning methods</option>
                                            <option value="dengue">Dengue &amp; environmental health</option>
                                            <option value="mental">Perinatal mental wellbeing</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Audience</label>
                                        <select class="form-control form-select" name="audience">
                                            <option value="antenatal">Antenatal mothers</option>
                                            <option value="postnatal">Postnatal mothers</option>
                                            <option value="mixed">Mixed community</option>
                                            <option value="adolescent">Adolescents</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label class="form-label">Approx. attendees *</label>
                                        <input type="number" class="form-control" name="attendees" min="1" max="500" value="25" required>
                                    </div>
                                </div>

                                <div class="col-4">
                                    <div class="form-group">
                                        <label class="form-label">Duration (minutes) *</label>
                                        <input type="number" class="form-control" name="duration_mins" min="15" max="180" value="45" required>
                                    </div>
                                </div>

                                <div class="col-4">
                                    <div class="form-group">
                                        <label class="form-label">Materials used</label>
                                        <input type="text" class="form-control" name="materials" placeholder="Flip chart, leaflets...">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Outcomes &amp; questions raised</label>
                                <textarea class="form-control" name="outcomes" rows="3" placeholder="Key messages delivered and follow-up needs"></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select class="form-control form-select" name="status">
                                    <option value="completed">Completed</option>
                                    <option value="upcoming">Upcoming</option>
                                    <option value="planned">Planned</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save session
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Recent &amp; planned sessions</h4>
                    </div>

                    <div class="card-body">
                        <div id="healthEducationSessionList" style="display: flex; flex-direction: column; gap: 1rem;">
                            <p class="text-muted">Loading health education sessions...</p>
                        </div>
                    </div>
                </div>
            </div>







            <div id="patients" class="content-section section-slide-in" style="display: none;">
                <div class="d-flex justify-between align-center mb-3">
                <h2>Maternal and Child Care</h2>

                <input
                    type="text"
                    class="form-control"
                    id="careSearchInput"
                    placeholder="Search records..."
                    style="max-width: 300px;"
                >
            </div>

    <!-- Duty Areas Selection -->
    <div class="duty-areas-container">
        <div class="duty-areas-title">
            <i class="fas fa-map-marker-alt"></i> Select Duty Area
        </div>

        <!-- JS will load real area counts here -->
        <div class="duty-areas-grid" id="careAreaGrid">
            <div class="duty-area-btn active" onclick="switchMaternalChildArea('uduthuththiripitiya')">
                <i class="fas fa-home"></i>
                <h5>Uduthuththiripitiya</h5>
                <div class="area-count">Loading...</div>
            </div>

            <div class="duty-area-btn" onclick="switchMaternalChildArea('kahabilihena')">
                <i class="fas fa-hospital"></i>
                <h5>Kahabilihena</h5>
                <div class="area-count">Loading...</div>
            </div>

            <div class="duty-area-btn" onclick="switchMaternalChildArea('opathella')">
                <i class="fas fa-city"></i>
                <h5>Opathella</h5>
                <div class="area-count">Loading...</div>
            </div>

            <div class="duty-area-btn" onclick="switchMaternalChildArea('ambalangoda')">
                <i class="fas fa-tree"></i>
                <h5>Ambalangoda</h5>
                <div class="area-count">Loading...</div>
            </div>
        </div>
    </div>

    <!-- Selected Area Content -->
 <div class="area-content-wrapper active">
    <div class="area-header">
        <h4 id="careAreaTitle">
            <i class="fas fa-map-marker-alt"></i> Maternal &amp; Child Care
        </h4>
        <p id="careAreaSubtitle">Loading records...</p>
    </div>

    <!-- Main Tabs -->
    <div class="tab-container">
        <ul class="nav nav-tabs" style="width: 100%;">
            <li class="nav-item" style="flex: 1; margin-right: 0;">
                <a
                    class="nav-link active"
                    href="#"
                    data-care-main-tab="mothers"
                    onclick="switchCareTab('mothers', event)"
                    style="text-align: center;"
                >
                    <i class="fas fa-female"></i> Mothers
                </a>
            </li>

            <li class="nav-item" style="flex: 1; margin-right: 0;">
                <a
                    class="nav-link"
                    href="#"
                    data-care-main-tab="children"
                    onclick="switchCareTab('children', event)"
                    style="text-align: center;"
                >
                    <i class="fas fa-child"></i> Children
                </a>
            </li>
        </ul>
    </div>

    <!-- Mothers Tab -->
    <div id="mothers-tab" class="tab-content" style="display: block;">
        <div class="tab-container" style="margin-top: 1rem;">
            <ul class="nav nav-tabs" style="width: 100%;">
                <li class="nav-item" style="flex: 1; margin-right: 0;">
                    <a
                        class="nav-link active"
                        href="#"
                        data-mother-tab="pregnant"
                        onclick="switchMotherTab('pregnant', event); updateMotherAddButton();"
                        style="text-align: center;"
                    >
                        <i class="fas fa-baby"></i> Pregnant Mothers
                    </a>
                </li>

                <li class="nav-item" style="flex: 1; margin-right: 0;">
                    <a
                        class="nav-link"
                        href="#"
                        data-mother-tab="lactating"
                        onclick="switchMotherTab('lactating', event); updateMotherAddButton();"
                        style="text-align: center;"
                    >
                        <i class="fas fa-child"></i> Lactating Mothers
                    </a>
                </li>

                <li class="nav-item" style="flex: 1; margin-right: 0;">
                    <a
                        class="nav-link"
                        href="#"
                        data-mother-tab="postnatal"
                        onclick="switchMotherTab('postnatal', event); updateMotherAddButton();"
                        style="text-align: center;"
                    >
                        <i class="fas fa-procedures"></i> Postnatal Mothers
                    </a>
                </li>
            </ul>
        </div>

        <div class="card" style="margin-top: 1rem;">
            <div class="card-header d-flex justify-between align-center">
                <h5 class="card-title" id="motherTableTitle" style="margin: 0;">
                    Pregnant Mothers
                </h5>

                <button
                    id="motherAddButton"
                    class="btn btn-primary btn-sm"
                    type="button"
                    data-toggle="modal"
                    data-target="#pregnantMotherModal"
                    onclick="preparePregnantMotherModal()"
                >
                    <i class="fas fa-plus"></i>
                    <span id="motherAddButtonText">Add Pregnant Mother</span>
                </button>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead id="motherTableHead">
                            <tr>
                                <th>Mother's Name</th>
                                <th>Age</th>
                                <th>Weeks Pregnant</th>
                                <th>Last Visit</th>
                                <th>Next Appointment</th>
                                <th>Risk Level</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody id="motherTableBody">
                            <tr>
                                <td colspan="7" class="text-muted">Loading records...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Children Tab -->
    <div id="children-tab" class="tab-content hidden" style="display: none;">
        <div class="tab-container" style="margin-top: 1rem;">
            <ul class="nav nav-tabs" style="width: 100%;">
                <li class="nav-item" style="flex: 1; margin-right: 0;">
                    <a
                        class="nav-link active"
                        href="#"
                        data-child-tab="newborns"
                        onclick="switchChildrenTab('newborns', event); updateChildAddButton();"
                        style="text-align: center;"
                    >
                        <i class="fas fa-baby"></i> Newborns
                    </a>
                </li>

                <li class="nav-item" style="flex: 1; margin-right: 0;">
                    <a
                        class="nav-link"
                        href="#"
                        data-child-tab="young"
                        onclick="switchChildrenTab('young', event); updateChildAddButton();"
                        style="text-align: center;"
                    >
                        <i class="fas fa-baby-carriage"></i> Young Children
                    </a>
                </li>

                <li class="nav-item" style="flex: 1; margin-right: 0;">
                    <a
                        class="nav-link"
                        href="#"
                        data-child-tab="childs"
                        onclick="switchChildrenTab('childs', event); updateChildAddButton();"
                        style="text-align: center;"
                    >
                        <i class="fas fa-child"></i> Childs
                    </a>
                </li>
            </ul>
        </div>

        <div class="card" style="margin-top: 1rem;">
            <div class="card-header d-flex justify-between align-center">
                <h5 class="card-title" id="childTableTitle" style="margin: 0;">
                    Newborns
                </h5>

                <button
                    id="childAddButton"
                    class="btn btn-primary btn-sm"
                    type="button"
                    data-toggle="modal"
                    data-target="#newbornModal"
                    onclick="prepareNewbornModal()"
                >
                    <i class="fas fa-plus"></i>
                    <span id="childAddButtonText">Add Newborn</span>
                </button>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead id="childTableHead">
                            <tr>
                                <th>Baby's Name</th>
                                <th>Mother's Name</th>
                                <th>Date of Birth</th>
                                <th>Birth Weight</th>
                                <th>Last Check-up</th>
                                <th>Health Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody id="childTableBody">
                            <tr>
                                <td colspan="7" class="text-muted">Loading records...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>


            <!-- Home Visits Section -->
            <div id="home-visits" class="content-section section-slide-in" style="display: none;">
                <div class="d-flex justify-between align-center mb-3">
                    <h2>Home Visits Management</h2>
                    <div>
                        <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#scheduleVisitModal">
                            <i class="fas fa-plus"></i> Schedule New Visit
                        </button>

                        <button class="btn btn-success" onclick="quickVisitLog()">
                            <i class="fas fa-clipboard-check"></i> Quick Visit Log
                        </button>
                    </div>
                </div>

                <!-- Duty Areas Selection -->
                <div class="duty-areas-container">
                    <div class="duty-areas-title">
                        <i class="fas fa-map-marker-alt"></i> Select Duty Area
                    </div>

                    <div class="duty-areas-grid" id="homeVisitAreaGrid">
                        <div class="duty-area-btn active">
                            <i class="fas fa-spinner fa-spin"></i>
                            <h5>Loading...</h5>
                            <div class="area-count">Please wait</div>
                        </div>
                    </div>
                </div>

                <!-- Selected Area Content -->
                <div class="area-content-wrapper active">
                    <div class="area-header">
                        <h4 id="homeVisitAreaTitle">
                            <i class="fas fa-map-marker-alt"></i> Home Visits
                        </h4>
                        <p id="homeVisitAreaSubtitle">Loading home visits...</p>
                    </div>

                    <!-- Visit Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="stat-info">
                                    <h3 id="homeVisitTodayCount">0</h3>
                                    <span>Today's Visits</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-info">
                                    <h3 id="homeVisitPendingCount">0</h3>
                                    <span>Pending Visits</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stat-info">
                                    <h3 id="homeVisitCompletedCount">0</h3>
                                    <span>Completed</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="stat-info">
                                    <h3 id="homeVisitUrgentCount">0</h3>
                                    <span>Urgent Follow-ups</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs for different views -->
                    <div class="tab-container">
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" href="#" onclick="switchVisitTab('scheduled', event)">Scheduled Visits</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" onclick="switchVisitTab('completed', event)">Completed Visits</a>
                            </li>
                        </ul>
                    </div>

                    <!-- Scheduled Visits Tab -->
                    <div id="scheduled-visits" class="tab-content" style="display: block;">
                        <div class="card">
                            <div class="card-header d-flex justify-between align-center">
                                <h4 class="card-title" id="scheduledVisitsTitle">Scheduled Visits</h4>
                                <div class="d-flex gap-2">
                                    <select class="form-control" id="homeVisitDateFilter" style="width: 150px;" onchange="filterHomeVisitsByDate(this.value)">
                                        <option value="all">All</option>
                                        <option value="today">Today</option>
                                        <option value="tomorrow">Tomorrow</option>
                                        <option value="week">This Week</option>
                                        <option value="month">This Month</option>
                                    </select>

                                    <button class="btn btn-outline-primary" onclick="optimizeRoute()">
                                        <i class="fas fa-route"></i> Optimize Route
                                    </button>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="visit-list" id="scheduledHomeVisitList">
                                    <p class="text-muted">Loading scheduled visits...</p>
                                </div>
                            </div>

                            <!-- Home visit detailed record section -->
                            <div id="home-visit-details-record" class="card mt-4" style="display: none;">
                                <div class="card-header">
                                    <h4 class="card-title">Visit Details Record</h4>
                                </div>
                                <div class="card-body" id="home-visit-details-body">
                                    <p>Select a visit and click "View Details" to see full patient record here.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Visits Tab -->
                    <div id="completed-visits" class="tab-content" style="display: none;">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Recently Completed Visits</h4>
                            </div>

                            <div class="card-body">
                                <div class="completed-visit-list" id="completedHomeVisitList">
                                    <p class="text-muted">Loading completed visits...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vaccinations Section -->
            <div id="vaccinations" class="content-section section-slide-in" style="display: none;">
                <div class="d-flex justify-between align-center mb-3">
                    <h2>Vaccination Management</h2>
                    <div>
                        <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#scheduleVaccinationModal">
                            <i class="fas fa-plus"></i> Schedule Vaccination
                        </button>

                    

                        <button class="btn btn-info" onclick="updateInventory()">
                            <i class="fas fa-boxes"></i> Update Inventory
                        </button>
                    </div>
                </div>

                <!-- Duty Areas Selection -->
                <div class="duty-areas-container">
                    <div class="duty-areas-title">
                        <i class="fas fa-map-marker-alt"></i> Select Duty Area
                    </div>

                    <div class="duty-areas-grid" id="vaccinationAreaGrid">
                        <div class="duty-area-btn active">
                            <i class="fas fa-spinner fa-spin"></i>
                            <h5>Loading...</h5>
                            <div class="area-count">Please wait</div>
                        </div>
                    </div>
                </div>

                <!-- Selected Area Content -->
                <div class="area-content-wrapper active">
                    <div class="area-header">
                        <h4 id="vaccinationAreaTitle">
                            <i class="fas fa-map-marker-alt"></i> Vaccinations
                        </h4>
                        <p id="vaccinationAreaSubtitle">Loading vaccination data...</p>
                    </div>

                    <!-- Vaccination Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="stat-info">
                                    <h3 id="vaccinationTodayCount">0</h3>
                                    <span>Today's Schedule</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-syringe"></i>
                                </div>
                                <div class="stat-info">
                                    <h3 id="vaccinationCompletedCount">0</h3>
                                    <span>Completed</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                                <div class="stat-info">
                                    <h3 id="vaccinationOverdueCount">0</h3>
                                    <span>Overdue</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <div class="stat-info">
                                    <h3 id="vaccinationStockCount">0</h3>
                                    <span>Vaccines in Stock</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="tab-container">
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" href="#" onclick="switchVaccinationTab('scheduled', event)">Today's Schedule</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" onclick="switchVaccinationTab('inventory', event)">Vaccine Inventory</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" onclick="switchVaccinationTab('records', event)">Patient Records</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" onclick="switchVaccinationTab('overdue', event)">Overdue Vaccines</a>
                            </li>
                        </ul>
                    </div>

                    <!-- Today's Schedule Tab -->
                    <div id="scheduled-vaccinations" class="tab-content" style="display: block;">
                        <div class="card">
                            <div class="card-header d-flex justify-between align-center">
                                <h4 class="card-title">Today's Vaccination Schedule</h4>

                                <div class="d-flex gap-2">
                                    <select class="form-control" id="vaccinationCategoryFilter" style="width: 180px;" onchange="filterVaccinations(this.value)">
                                        <option value="all">All Vaccines</option>
                                        <option value="pediatric">Pediatric</option>
                                        <option value="maternal">Maternal</option>
                                        <option value="adult">Adult</option>
                                    </select>

                                    <button class="btn btn-outline-primary" onclick="printSchedule()">
                                        <i class="fas fa-print"></i> Print Schedule
                                    </button>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="vaccination-schedule" id="scheduledVaccinationList">
                                    <p class="text-muted">Loading scheduled vaccinations...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vaccine Inventory Tab -->
                    <div id="inventory-vaccinations" class="tab-content" style="display: none;">
                        <div class="card">
                            <div class="card-header d-flex justify-between align-center">
                                <h4 class="card-title">Vaccine Inventory Status</h4>

                                <div class="d-flex gap-2">
                                    <button class="btn btn-warning" onclick="checkExpiring()">
                                        <i class="fas fa-exclamation-triangle"></i> Check Expiring
                                    </button>

                                    <button class="btn btn-primary" onclick="orderSupplies()">
                                        <i class="fas fa-shopping-cart"></i> Order Supplies
                                    </button>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="inventory-grid" id="vaccineInventoryGrid">
                                    <p class="text-muted">Loading vaccine inventory...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Patient Records Tab -->
                    <div id="records-vaccinations" class="tab-content" style="display: none;">
                        <div class="card">
                            <div class="card-header d-flex justify-between align-center">
                                <h4 class="card-title">Patient Vaccination Records</h4>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="vaccinationPatientSearch"
                                    placeholder="Search patient..."
                                    style="max-width: 300px;"
                                    onkeyup="renderVaccinationPatientRecords()">
                            </div>

                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Patient Name</th>
                                            <th>Age</th>
                                            <th>Last Vaccine</th>
                                            <th>Next Due</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody id="vaccinationPatientRecordBody">
                                        <tr>
                                            <td colspan="6">Loading records...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Overdue Vaccines Tab -->
                    <div id="overdue-vaccinations" class="tab-content" style="display: none;">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Overdue Vaccinations</h4>
                            </div>

                            <div class="card-body">
                                <div class="overdue-list" id="overdueVaccinationList">
                                    <p class="text-muted">Loading overdue vaccinations...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Section -->
<div id="profile" class="content-section section-slide-in" style="display: none;">
    <div class="d-flex justify-between align-center mb-3">
        <h2>My Profile</h2>

        <div>
             <!-- <button

            class="btn btn-primary"

            type="button"

            data-toggle="modal"

            data-target="#editProfileModal"

        >

            <i class="fas fa-edit"></i> Edit Profile

        </button>

        <button

            class="btn btn-info"

            type="button"

            data-toggle="modal"

            data-target="#changePasswordModal"

        >

            <i class="fas fa-key"></i> Change Password

        </button> -->
        </div>
    </div>

    <div class="row">
        <!-- Left Profile Summary -->
        <div class="col-4">
            <div class="card text-center">
                <div class="card-body">
                    <img
                        id="profileImage"
                        src="../images/profile picture.png"
                        alt="Profile Image"
                        style="width: 160px; height: 160px; border-radius: 50%; object-fit: cover; margin-bottom: 1rem;"
                    >

                    <h3 id="profileFullName">Loading...</h3>
                    <p class="text-muted">Registered Midwife</p>

                    <p>
                        <strong>Employee ID:</strong>
                        <span id="profileEmployeeId">Loading...</span>
                    </p>

                    <span class="status-badge status-active" id="profileStatus">Loading...</span>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title">Quick Details</h4>
                </div>

                <div class="card-body">
                    <div class="summary-item">
                        <span>Assigned Area:</span>
                        <strong id="quickAssignedArea">Loading...</strong>
                    </div>

                    <div class="summary-item">
                        <span>MOH Office:</span>
                        <strong id="quickMohOffice">Loading...</strong>
                    </div>

                    <div class="summary-item">
                        <span>Experience:</span>
                        <strong id="quickExperienceYears">Loading...</strong>
                    </div>

                    <div class="summary-item">
                        <span>Last Login:</span>
                        <strong id="quickLastLogin">Loading...</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Profile Details -->
        <div class="col-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Personal Information</h4>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="profile-info-item">
                                <label>Full Name</label>
                                <p id="profileInfoFullName">Loading...</p>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="profile-info-item">
                                <label>Date of Birth</label>
                                <p id="profileInfoBirthDate">Loading...</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="profile-info-item">
                                <label>Email Address</label>
                                <p id="profileInfoEmail">Loading...</p>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="profile-info-item">
                                <label>Phone Number</label>
                                <p id="profileInfoPhone">Loading...</p>
                            </div>
                        </div>
                    </div>

                    <div class="profile-info-item">
                        <label>Address</label>
                        <p id="profileInfoAddress">Loading...</p>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title">Professional Details</h4>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="profile-info-item">
                                <label>Hire Date</label>
                                <p id="profileInfoHireDate">Loading...</p>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="profile-info-item">
                                <label>Experience</label>
                                <p id="profileInfoExperienceYears">Loading...</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="profile-info-item">
                                <label>Assigned Area</label>
                                <p id="profileInfoAssignedArea">Loading...</p>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="profile-info-item">
                                <label>MOH Office</label>
                                <p id="profileInfoMohOffice">Loading...</p>
                            </div>
                        </div>
                    </div>

                    <div class="profile-info-item">
                        <label>Last Login</label>
                        <p id="profileInfoLastLogin">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

            <!-- Triposha Distribution Section -->
            <div id="triposha" class="content-section section-slide-in" style="display: none;">
                <div class="page-header">
                    <h2><i class="fas fa-box"></i> Triposha Distribution Management</h2>
                    <p>Manage Triposha packet distribution and inventory tracking</p>
                </div>

                <div class="duty-areas-container">
                    <div class="duty-areas-title">
                        <i class="fas fa-map-marker-alt"></i> Select Duty Area
                    </div>

                    <div class="duty-areas-grid" id="triposhaAreaGrid">
                        <div class="duty-area-btn active">
                            <i class="fas fa-spinner fa-spin"></i>
                            <h5>Loading...</h5>
                            <div class="area-count">Please wait</div>
                        </div>
                    </div>
                </div>

                <div class="area-content-wrapper active">
                    <div class="area-header">
                        <h4 id="triposhaAreaTitle">
                            <i class="fas fa-map-marker-alt"></i> Triposha Distribution
                        </h4>
                        <p id="triposhaAreaSubtitle">Loading Triposha data...</p>
                    </div>

                    <div class="dashboard-stats">
                        <div class="stat-card info editable-stat" onclick="openTriposhaInventoryModal()">
                            <div class="stat-number" id="packets-received-month">0</div>
                            <div class="stat-label">Packets Received This Month</div>
                            <div class="edit-hint"><i class="fas fa-edit"></i> Click to edit</div>
                        </div>

                        <div class="stat-card warning editable-stat" onclick="openTriposhaInventoryModal()">
                            <div class="stat-number" id="packets-left-previous">0</div>
                            <div class="stat-label">Packets Left from Previous Month</div>
                            <div class="edit-hint"><i class="fas fa-edit"></i> Click to edit</div>
                        </div>

                        <div class="stat-card success">
                            <div class="stat-number" id="total-packets">0</div>
                            <div class="stat-label">Total Packets Available</div>
                            <div class="auto-calc-hint"><i class="fas fa-calculator"></i> Auto-calculated</div>
                        </div>

                        <div class="stat-card editable-stat" onclick="openTriposhaInventoryModal()">
                            <div class="stat-number" id="packets-distributed">0</div>
                            <div class="stat-label">Packets Distributed</div>
                            <div class="edit-hint"><i class="fas fa-edit"></i> Click to edit</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-8">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Distribution Records</h4>
                                </div>

                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Beneficiary</th>
                                                    <th>Address</th>
                                                    <th>Packets</th>
                                                    <th>Category</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>

                                            <tbody id="distribution-records">
                                                <tr>
                                                    <td colspan="7">Loading records...</td>
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
                                    <h4 class="card-title">Quick Actions</h4>
                                </div>

                                <div class="card-body">
                                    <button class="btn btn-primary btn-block mb-3" type="button" data-toggle="modal" data-target="#triposhaDistributionModal">
                                        <i class="fas fa-plus"></i> Record Distribution
                                    </button>

                                    <button class="btn btn-secondary btn-block mb-3" onclick="openTriposhaInventoryModal()">
                                        <i class="fas fa-box-open"></i> Update Inventory
                                    </button>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h4 class="card-title">Monthly Summary</h4>
                                </div>

                                <div class="card-body">
                                    <div class="summary-item">
                                        <span>Pregnant Mothers:</span>
                                        <div>
                                            <strong id="pregnant-packets">0 packets</strong>
                                        </div>
                                    </div>

                                    <div class="summary-item">
                                        <span>Lactating Mothers:</span>
                                        <div>
                                            <strong id="lactating-packets">0 packets</strong>
                                        </div>
                                    </div>

                                    <div class="summary-item">
                                        <span>Children (6-23m):</span>
                                        <div>
                                            <strong id="children-packets">0 packets</strong>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="summary-item">
                                        <span><strong>Total Distributed:</strong></span>
                                        <strong class="text-success" id="total-distributed-summary">0 packets</strong>
                                    </div>

                                    <div class="summary-item">
                                        <span><strong>Remaining:</strong></span>
                                        <strong class="text-warning" id="remaining-packets">0 packets</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Triposha Inventory Modal -->
                <div class="modal fade" id="triposhaInventoryModal" tabindex="-1" role="dialog" aria-labelledby="triposhaInventoryModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title" id="triposhaInventoryModalLabel">Update Triposha Inventory</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <form id="triposhaInventoryForm" action="../php/midwife/save_triposha_inventory.php" method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="duty_area" id="triposhaInventoryDutyArea">

                                    <div class="form-group">
                                        <label class="form-label">Inventory Month *</label>
                                        <input type="month" class="form-control" name="inventory_month" id="triposhaInventoryMonth" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Packets Received This Month *</label>
                                        <input type="number" class="form-control" name="packets_received" id="triposhaPacketsReceived" min="0" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Packets Left from Previous Month *</label>
                                        <input type="number" class="form-control" name="packets_left_previous" id="triposhaPacketsLeftPrevious" min="0" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Notes</label>
                                        <textarea class="form-control" name="notes" id="triposhaInventoryNotes" rows="3"></textarea>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Inventory
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>

                <!-- Triposha Distribution Modal -->
                <div class="modal fade" id="triposhaDistributionModal" tabindex="-1" role="dialog" aria-labelledby="triposhaDistributionModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title" id="triposhaDistributionModalLabel">Record Triposha Distribution</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <form id="triposhaDistributionForm" action="../php/midwife/create_triposha_distribution.php" method="POST">
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label class="form-label">Duty Area *</label>
                                        <input type="text" class="form-control" name="duty_area" id="triposhaDistributionDutyArea" required>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="form-label">Date *</label>
                                                <input type="date" class="form-control" name="distribution_date" id="triposhaDistributionDate" required>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="form-label">Category *</label>
                                                <select class="form-control" name="category" required>
                                                    <option value="">Select category</option>
                                                    <option value="pregnant">Pregnant Mother</option>
                                                    <option value="lactating">Lactating Mother</option>
                                                    <option value="children">Child (6-23 months)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Beneficiary Name *</label>
                                        <input type="text" class="form-control" name="beneficiary_name" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Address</label>
                                        <input type="text" class="form-control" name="address">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Packets *</label>
                                        <input type="number" class="form-control" name="packets" min="1" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Status</label>
                                        <select class="form-control" name="status">
                                            <option value="completed">Completed</option>
                                            <option value="pending">Pending</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Notes</label>
                                        <textarea class="form-control" name="notes" rows="3"></textarea>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Distribution
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
            <!-- Remove Distribution Modal -->
            <div id="removeDistributionModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Remove Triposha Distribution</h3>
                        <span class="close" onclick="closeRemoveDistributionModal()">&times;</span>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> This action will permanently remove the distribution record and adjust packet counts.
                        </div>
                        <form id="removeDistributionForm">
                            <div class="form-group">
                                <label>Select Distribution to Remove</label>
                                <select name="distribution_record" id="distributionSelect" required>
                                    <option value="">Select a distribution record to remove</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Reason for Removal (Optional)</label>
                                <select name="removal_reason">
                                    <option value="accidental">Accidental Distribution</option>
                                    <option value="incorrect_beneficiary">Incorrect Beneficiary</option>
                                    <option value="wrong_quantity">Wrong Quantity</option>
                                    <option value="data_entry_error">Data Entry Error</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Additional Notes (Optional)</label>
                                <textarea name="removal_notes" rows="3" placeholder="Additional details about the removal"></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="closeRemoveDistributionModal()">Cancel</button>
                                <button type="submit" class="btn btn-danger">Remove Distribution</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            <div id="distributionModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Record Triposha Distribution</h3>
                        <span class="close" onclick="closeDistributionModal()">&times;</span>
                    </div>
                    <div class="modal-body">
                        <form id="distributionForm">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" name="distribution_date" required>
                            </div>
                            <div class="form-group">
                                <label>Beneficiary Name</label>
                                <input type="text" name="beneficiary_name" placeholder="Enter full name" required>
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category" required>
                                    <option value="">Select category</option>
                                    <option value="pregnant">Pregnant Mother</option>
                                    <option value="lactating">Lactating Mother</option>
                                    <option value="child_6_23">Child (6-23 months)</option>
                                    <option value="child_24_59">Child (24-59 months)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Number of Packets</label>
                                <input type="number" name="packet_count" min="1" max="10" required>
                            </div>
                            <div class="form-group">
                                <label>Notes (Optional)</label>
                                <textarea name="notes" rows="3" placeholder="Additional notes"></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="closeDistributionModal()">Cancel</button>
                                <button type="submit" class="btn btn-primary">Record Distribution</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!--  addScheduleModal -->

            <div class="modal fade" id="addScheduleModal" tabindex="-1" role="dialog" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title" id="addScheduleModalLabel">Add New Schedule Item</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form id="scheduleItemForm" action="../php/midwife/create_schedule.php" method="POST">
                            <div class="modal-body">

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Date *</label>
                                            <input type="date" class="form-control" name="scheduled_date" required>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Time *</label>
                                            <input type="time" class="form-control" name="start_time" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Activity Type *</label>
                                            <select class="form-control" name="activity_type" required>
                                                <option value="">Select Type</option>
                                                <option value="HOME_VISIT">Home Visit</option>
                                                <option value="CLINIC_VISIT">Clinic Session</option>
                                                <option value="VACCINATION">Vaccination</option>
                                                <option value="COUNSELING">Counseling</option>
                                                <option value="MEETING">Meeting</option>
                                                <option value="HEALTH_EDUCATION">Health Education</option>
                                                <option value="EMERGENCY_RESPONSE">Emergency Response</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Duration</label>
                                            <select class="form-control" name="duration">
                                                <option value="30">30 minutes</option>
                                                <option value="45">45 minutes</option>
                                                <option value="60">1 hour</option>
                                                <option value="90">1.5 hours</option>
                                                <option value="120">2 hours</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Title / Description *</label>
                                    <input type="text" class="form-control" name="description" placeholder="Brief description of the activity" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Patient Name</label>
                                    <input type="text" class="form-control" name="patient_name" placeholder="Patient name if applicable">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Location *</label>
                                    <input type="text" class="form-control" name="location" placeholder="Where will this take place?" required>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Priority</label>
                                            <select class="form-control" name="priority_level">
                                                <option value="normal">Normal</option>
                                                <option value="high">High</option>
                                                <option value="urgent">Urgent</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Duty Area / Working Area</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                name="duty_area"
                                                placeholder="Enter working area"
                                                required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control" name="notes" rows="3" placeholder="Additional notes or instructions"></textarea>
                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Add to Schedule</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            <!-- timetableModal -->

            <div class="modal fade" id="timetableModal" tabindex="-1" role="dialog" aria-labelledby="timetableModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title" id="timetableModalLabel">My Timetable</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="timetable-controls mb-3">
                                <div class="d-flex justify-content-start mb-3" role="tablist" style="gap: 0.5rem;">
                                    <button class="btn btn-outline-primary active" id="tab-year" onclick="setTimetableTab('year')" type="button">Year</button>
                                    <button class="btn btn-outline-primary" id="tab-month" onclick="setTimetableTab('month')" type="button">Month</button>
                                    <button class="btn btn-outline-primary" id="tab-day" onclick="setTimetableTab('day')" type="button">Day</button>
                                </div>

                                <div class="row" id="timetable-selectors">
                                    <div class="col-4">
                                        <label class="form-label">Year</label>
                                        <select class="form-control" id="timetableYear" onchange="syncTimetableInputs(); loadTimetableData();">
                                            <option value="2024">2024</option>
                                            <option value="2025">2025</option>
                                            <option value="2026" selected>2026</option>
                                            <option value="2027">2027</option>
                                            <option value="2028">2028</option>
                                        </select>
                                    </div>

                                    <div class="col-4" id="timetable-month-wrapper">
                                        <label class="form-label">Month</label>
                                        <select class="form-control" id="timetableMonth" onchange="syncTimetableInputs(); loadTimetableData();">
                                            <option value="01">January</option>
                                            <option value="02">February</option>
                                            <option value="03" selected>March</option>
                                            <option value="04">April</option>
                                            <option value="05">May</option>
                                            <option value="06">June</option>
                                            <option value="07">July</option>
                                            <option value="08">August</option>
                                            <option value="09">September</option>
                                            <option value="10">October</option>
                                            <option value="11">November</option>
                                            <option value="12">December</option>
                                        </select>
                                    </div>

                                    <div class="col-4" id="timetable-day-wrapper">
                                        <label class="form-label">Day</label>
                                        <select class="form-control" id="timetableDay" onchange="syncTimetableInputs(); loadTimetableData();">
                                            <option value="01">1</option>
                                            <option value="02">2</option>
                                            <option value="03">3</option>
                                            <option value="04">4</option>
                                            <option value="05">5</option>
                                            <option value="06">6</option>
                                            <option value="07">7</option>
                                            <option value="08">8</option>
                                            <option value="09">9</option>
                                            <option value="10">10</option>
                                            <option value="11">11</option>
                                            <option value="12">12</option>
                                            <option value="13">13</option>
                                            <option value="14">14</option>
                                            <option value="15">15</option>
                                            <option value="16">16</option>
                                            <option value="17">17</option>
                                            <option value="18">18</option>
                                            <option value="19">19</option>
                                            <option value="20">20</option>
                                            <option value="21">21</option>
                                            <option value="22">22</option>
                                            <option value="23">23</option>
                                            <option value="24">24</option>
                                            <option value="25">25</option>
                                            <option value="26">26</option>
                                            <option value="27" selected>27</option>
                                            <option value="28">28</option>
                                            <option value="29">29</option>
                                            <option value="30">30</option>
                                            <option value="31">31</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div id="timetableContent">
                                <div id="timetableHeader" class="timetable-header" style="display: none;">
                                    <h4 id="monthTitle"></h4>
                                </div>

                                <div id="timetableBody">
                                    <p class="text-muted mb-0">Select year, month, or day to view timetable.</p>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="editTimetable()">Edit Timetable</button>
                        </div>

                    </div>
                </div>
            </div>


            <!-- scheduleVisitModal -->

            <div class="modal fade" id="scheduleVisitModal" tabindex="-1" role="dialog" aria-labelledby="scheduleVisitModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title" id="scheduleVisitModalLabel">Schedule New Home Visit</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form id="newVisitForm" action="../php/midwife/create_home_visit.php" method="POST">
                            <div class="modal-body">

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Patient Name *</label>
                                            <input type="text" class="form-control" name="patient_name" placeholder="Enter patient name" required>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Contact Number</label>
                                            <input type="tel" class="form-control" name="contact_number" placeholder="+94 XX XXX XXXX">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Address *</label>
                                    <input type="text" class="form-control" name="address" placeholder="Enter patient address" required>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Visit Type *</label>
                                            <select class="form-control" name="visit_type" required>
                                                <option value="antenatal">Antenatal Visit</option>
                                                <option value="postnatal">Postnatal Visit</option>
                                                <option value="family-planning">Family Planning</option>
                                                <option value="emergency">Emergency Follow-up</option>
                                                <option value="routine">Routine Visit</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Priority</label>
                                            <select class="form-control" name="priority">
                                                <option value="normal">Normal</option>
                                                <option value="high">High</option>
                                                <option value="urgent">Urgent</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label class="form-label">Date *</label>
                                            <input type="date" class="form-control" name="visit_date" required>
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="form-group">
                                            <label class="form-label">Time *</label>
                                            <input type="time" class="form-control" name="start_time" required>
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="form-group">
                                            <label class="form-label">Duration (min)</label>
                                            <input type="number" class="form-control" name="duration_minutes" value="45" min="15" max="180">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Duty Area / Working Area *</label>
                                    <input type="text" class="form-control" name="duty_area" placeholder="Enter working area" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Reason for Visit</label>
                                    <textarea class="form-control" name="reason" rows="2" placeholder="Reason for the home visit..."></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control" name="notes" rows="2" placeholder="Additional notes..."></textarea>
                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Schedule Visit</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            <!-- Schedule New Vaccination -->


            <div class="modal fade" id="scheduleVaccinationModal" tabindex="-1" role="dialog" aria-labelledby="scheduleVaccinationModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title" id="scheduleVaccinationModalLabel">Schedule New Vaccination</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form id="newVaccinationForm" action="../php/midwife/create_vaccination.php" method="POST">
                            <div class="modal-body">

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Patient Name *</label>
                                            <input type="text" class="form-control" name="patient_name" placeholder="Enter patient name" required>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Patient Age</label>
                                            <input type="number" class="form-control" name="patient_age" min="0" max="120" placeholder="Age">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Contact Number</label>
                                            <input type="tel" class="form-control" name="contact_number" placeholder="+94 XX XXX XXXX">
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Duty Area *</label>
                                            <input type="text" class="form-control" name="duty_area" placeholder="Enter duty area" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Address</label>
                                    <input type="text" class="form-control" name="address" placeholder="Enter patient address">
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Vaccine Category *</label>
                                            <select class="form-control" name="vaccine_category" id="vaccineCategory" required>
                                                <option value="">Select Category</option>
                                                <option value="pediatric">Pediatric Vaccines</option>
                                                <option value="maternal">Maternal Vaccines</option>
                                                <option value="adult">Adult Vaccines</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Vaccine *</label>
                                            <select class="form-control" name="vaccine_code" id="vaccineOptions" required>
                                                <option value="">Select category first</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Date *</label>
                                            <input type="date" class="form-control" name="vaccination_date" required>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Time *</label>
                                            <input type="time" class="form-control" name="vaccination_time" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Location *</label>
                                    <input type="text" class="form-control" name="location" placeholder="Clinic / home / community location" required>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Dose Number</label>
                                            <input type="text" class="form-control" name="dose_number" placeholder="Example: 1st dose, 2nd dose, Booster">
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Next Due Date</label>
                                            <input type="date" class="form-control" name="next_due_date">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Special Instructions / Notes</label>
                                    <textarea class="form-control" name="notes" rows="3" placeholder="Any special instructions or notes..."></textarea>
                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Schedule Vaccination
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>


            <!-- updatevaccinationinventorymodel -->

            <div class="modal fade" id="updateVaccineInventoryModal" tabindex="-1" role="dialog" aria-labelledby="updateVaccineInventoryModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title" id="updateVaccineInventoryModalLabel">
                                Update Vaccine Inventory
                            </h5>

                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form id="updateVaccineInventoryForm" action="../php/midwife/update_vaccine_inventory.php" method="POST">
                            <div class="modal-body">

                                <div class="form-group">
                                    <label class="form-label">Select Vaccine *</label>
                                    <select class="form-control" name="vaccine_id" id="inventoryVaccineSelect" required>
                                        <option value="">Loading vaccines...</option>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Stock Quantity *</label>
                                            <input type="number" class="form-control" name="stock_quantity" id="inventoryStockQuantity" min="0" required>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Minimum Stock Level *</label>
                                            <input type="number" class="form-control" name="minimum_stock_level" id="inventoryMinimumStock" min="0" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Batch Number</label>
                                            <input type="text" class="form-control" name="batch_number" id="inventoryBatchNumber" placeholder="Example: DPT-2026-001">
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-label">Expiry Date</label>
                                            <input type="date" class="form-control" name="expiry_date" id="inventoryExpiryDate">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Status *</label>
                                    <select class="form-control" name="status" id="inventoryStatus" required>
                                        <option value="available">Available</option>
                                        <option value="low_stock">Low Stock</option>
                                        <option value="expired">Expired</option>
                                        <option value="unavailable">Unavailable</option>
                                    </select>
                                </div>

                                <div class="alert alert-info">
                                    Select a vaccine first. Existing inventory values will automatically fill into the form.
                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    Cancel
                                </button>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Inventory
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>


<!-- =========================================================
     1. Pregnant Mother Modal
     Table: maternal_care_records
     Category: pregnant
========================================================= -->
<div class="modal fade" id="pregnantMotherModal" tabindex="-1" role="dialog" aria-labelledby="pregnantMotherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="pregnantMotherForm" action="../php/midwife/create_pregnant_mother.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="pregnantMotherModalLabel">Add Pregnant Mother</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="duty_area" id="pregnantMotherDutyArea">
                    <input type="hidden" name="category" value="pregnant">

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Mother's Name *</label>
                                <input type="text" class="form-control" name="mother_name" required>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Age</label>
                                <input type="number" class="form-control" name="age" min="12" max="60">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Weeks Pregnant</label>
                                <input type="number" class="form-control" name="weeks_pregnant" min="1" max="42">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Risk Level</label>
                                <select class="form-control" name="risk_level">
                                    <option value="Low Risk">Low Risk</option>
                                    <option value="Medium Risk">Medium Risk</option>
                                    <option value="High Risk">High Risk</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Last Visit</label>
                                <input type="date" class="form-control" name="last_visit">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Next Appointment</label>
                                <input type="date" class="form-control" name="next_appointment">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Contact Number</label>
                                <input type="text" class="form-control" name="contact_number">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select class="form-control" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Pregnant Mother
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- =========================================================
     2. Lactating Mother Modal
     Table: maternal_care_records
     Category: lactating
========================================================= -->
<div class="modal fade" id="lactatingMotherModal" tabindex="-1" role="dialog" aria-labelledby="lactatingMotherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="lactatingMotherForm" action="../php/midwife/create_lactating_mother.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="lactatingMotherModalLabel">Add Lactating Mother</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="duty_area" id="lactatingMotherDutyArea">
                    <input type="hidden" name="category" value="lactating">

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Mother's Name *</label>
                                <input type="text" class="form-control" name="mother_name" required>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Age</label>
                                <input type="number" class="form-control" name="age" min="12" max="60">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Baby's Age</label>
                                <input type="text" class="form-control" name="baby_age" placeholder="Example: 2 months">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Breastfeeding Status</label>
                                <input type="text" class="form-control" name="breastfeeding_status" placeholder="Example: Exclusive breastfeeding">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Last Visit</label>
                                <input type="date" class="form-control" name="last_visit">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Support Level</label>
                                <select class="form-control" name="support_level">
                                    <option value="Good Support">Good Support</option>
                                    <option value="Needs Support">Needs Support</option>
                                    <option value="High Support Required">High Support Required</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Health Status</label>
                                <input type="text" class="form-control" name="health_status" value="Healthy">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Contact Number</label>
                                <input type="text" class="form-control" name="contact_number">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Lactating Mother
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- =========================================================
     3. Postnatal Mother Modal
     Table: maternal_care_records
     Category: postnatal
========================================================= -->
<div class="modal fade" id="postnatalMotherModal" tabindex="-1" role="dialog" aria-labelledby="postnatalMotherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="postnatalMotherForm" action="../php/midwife/create_postnatal_mother.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="postnatalMotherModalLabel">Add Postnatal Mother</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="duty_area" id="postnatalMotherDutyArea">
                    <input type="hidden" name="category" value="postnatal">

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Mother's Name *</label>
                                <input type="text" class="form-control" name="mother_name" required>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Age</label>
                                <input type="number" class="form-control" name="age" min="12" max="60">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Delivery Date</label>
                                <input type="date" class="form-control" name="delivery_date">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Delivery Type</label>
                                <select class="form-control" name="delivery_type">
                                    <option value="">Select Delivery Type</option>
                                    <option value="Normal Delivery">Normal Delivery</option>
                                    <option value="C-Section">C-Section</option>
                                    <option value="Assisted Delivery">Assisted Delivery</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Recovery Status</label>
                                <input type="text" class="form-control" name="recovery_status" value="Good Recovery">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Last Visit</label>
                                <input type="date" class="form-control" name="last_visit">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Next Appointment</label>
                                <input type="date" class="form-control" name="next_appointment">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Contact Number</label>
                                <input type="text" class="form-control" name="contact_number">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Postnatal Mother
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- =========================================================
     4. Newborn Modal
     Table: child_care_records
     child_category: newborns
========================================================= -->
<div class="modal fade" id="newbornModal" tabindex="-1" role="dialog" aria-labelledby="newbornModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="newbornForm" action="../php/midwife/create_newborn.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="newbornModalLabel">Add Newborn</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="duty_area" id="newbornDutyArea">
                    <input type="hidden" name="child_category" value="newborns">

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Baby's Name *</label>
                                <input type="text" class="form-control" name="child_name" required>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Mother's Name</label>
                                <input type="text" class="form-control" name="mother_name">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" name="date_of_birth">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Birth Weight (kg)</label>
                                <input type="number" step="0.01" class="form-control" name="birth_weight">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Last Check-up</label>
                                <input type="date" class="form-control" name="last_checkup">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Health Status</label>
                                <input type="text" class="form-control" name="health_status" value="Healthy">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Newborn
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- =========================================================
     5. Young Child Modal
     Table: child_care_records
     child_category: young
========================================================= -->
<div class="modal fade" id="youngChildModal" tabindex="-1" role="dialog" aria-labelledby="youngChildModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="youngChildForm" action="../php/midwife/create_young_child.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="youngChildModalLabel">Add Young Child</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="duty_area" id="youngChildDutyArea">
                    <input type="hidden" name="child_category" value="young">

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Child's Name *</label>
                                <input type="text" class="form-control" name="child_name" required>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Mother / Guardian</label>
                                <input type="text" class="form-control" name="mother_name">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Age</label>
                                <input type="text" class="form-control" name="age_label" placeholder="Example: 2 years">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Current Weight (kg)</label>
                                <input type="number" step="0.01" class="form-control" name="current_weight">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Height (cm)</label>
                                <input type="number" step="0.01" class="form-control" name="height_cm">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Last Check-up</label>
                                <input type="date" class="form-control" name="last_checkup">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Development Status</label>
                                <input type="text" class="form-control" name="development_status" value="Normal">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Health Status</label>
                                <input type="text" class="form-control" name="health_status" value="Healthy">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Young Child
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- =========================================================
     6. Child Modal
     Table: child_care_records
     child_category: childs
========================================================= -->
<div class="modal fade" id="childModal" tabindex="-1" role="dialog" aria-labelledby="childModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="childForm" action="../php/midwife/create_child.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="childModalLabel">Add Child</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="duty_area" id="childDutyArea">
                    <input type="hidden" name="child_category" value="childs">

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Child's Name *</label>
                                <input type="text" class="form-control" name="child_name" required>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Mother / Guardian</label>
                                <input type="text" class="form-control" name="mother_name">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Age</label>
                                <input type="text" class="form-control" name="age_label" placeholder="Example: 5 years">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">School</label>
                                <input type="text" class="form-control" name="school">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Last Visit / Last Check-up</label>
                                <input type="date" class="form-control" name="last_checkup">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Health Status</label>
                                <input type="text" class="form-control" name="health_status" value="Healthy">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Child
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>





<!-- Edit Profile Bootstrap Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <form id="editProfileForm" action="../php/midwife/update_profile.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">
                        <i class="fas fa-edit"></i> Edit Profile
                    </h5>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Full Name *</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="full_name"
                                    id="editFullName"
                                    required
                                >
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="phone"
                                    id="editPhone"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea
                            class="form-control"
                            name="address"
                            id="editAddress"
                            rows="3"
                        ></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


<!-- Change Password Bootstrap Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <form id="changePasswordForm" action="../php/midwife/change_password.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">
                        <i class="fas fa-key"></i> Change Password
                    </h5>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Current Password *</label>
                        <input
                            type="password"
                            class="form-control"
                            name="current_password"
                            id="currentPassword"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">New Password *</label>
                        <input
                            type="password"
                            class="form-control"
                            name="new_password"
                            id="newPassword"
                            minlength="8"
                            required
                        >
                        <small class="form-text text-muted">
                            Password must be at least 8 characters long.
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm New Password *</label>
                        <input
                            type="password"
                            class="form-control"
                            name="confirm_password"
                            id="confirmPassword"
                            minlength="8"
                            required
                        >
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


            <script src="../js/page-transitions.js"></script>
            <script src="../js/theme-toggle.js"></script>
            <script src="../js/midwife/create_activity.js"></script>
            <script src="../js/midwife/create-schedule.js"></script>
            <script src="../js/midwife/load-schedules.js"></script>
            <script src="../js/midwife/create-home-visit.js"></script>
            <script src="../js/midwife/load-home-visits.js"></script>
            <script src="../js/midwife/load-vaccinations.js"></script>
            <script src="../js/midwife/schedule-vaccination.js"></script>
            <script src="../js/midwife/load-triposha.js"></script>
            <script src="../js/midwife/load-health-education-sessions.js"></script>
            <script src="../js/midwife/load-counseling-sessions.js"></script>
            <script src="../js/midwife/maternal-child-care-tabs.js"></script>
            <script src="../js/midwife/maternal-child-care.js"></script>
            <script src="../js/midwife/midwife-profile.js"></script>
            <script src="../js/midwife/load-dashboard-widgets.js"></script>
            <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
            <script>
                $('#timetableModal').on('shown.bs.modal', function() {

                    if (typeof setTimetableTab === 'function') {

                        setTimetableTab('year');

                    }

                    if (typeof syncTimetableInputs === 'function') {

                        syncTimetableInputs();

                    }

                    if (typeof loadTimetableData === 'function') {

                        loadTimetableData();

                    }

                });
            </script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const params = new URLSearchParams(window.location.search);
                    const section = params.get('section');

                    if (section === 'profile') {
                        if (typeof showSection === 'function') {
                            showSection('profile');
                        } else {
                            document.querySelectorAll('.content-section').forEach(function (sectionElement) {
                                sectionElement.style.display = 'none';
                            });

                            const profileSection = document.getElementById('profile');
                            if (profileSection) {
                                profileSection.style.display = 'block';
                            }
                        }
                    }
                });
            </script>

            <script>
                // Initialize dashboard
                document.addEventListener('DOMContentLoaded', function() {
                    setupNavigation();
                    setupSidebarToggle();
                    setupProfileButtonNavigation();

                    applyHashSection();
                    loadDashboardWidgets();
                    loadHomeVisits();

                    const initialVisitTab = getVisitTabFromUrl();
                    if (initialVisitTab === 'completed') {
                        switchVisitTab('completed');
                    }

                    checkAuthentication();
                    initializeCharts();
                    setCurrentDateTime();
                    updateTotalPackets();
                    updateRemainingPackets();
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

           
                function checkAuthentication() {
                    const midwifeUser = localStorage.getItem('midwife_user');
                    if (!midwifeUser) {
                        window.location.href = '../midwife-login.html';
                        return;
                    }

                    const user = JSON.parse(midwifeUser);
                    document.querySelector('.midwife-name').textContent = user.name || 'Midwife User';
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
                            const href = this.getAttribute('href') || '';
                            if (!href.startsWith('#')) {
                                return;
                            }
                            e.preventDefault();
                            const targetSectionId = href.substring(1);
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

                    // ================= Added form logic ================= //
                    // Counseling session
                    const counselingForm = document.getElementById('counselingForm');
                    if (counselingForm) {
                        const cInput = counselingForm.querySelector('input[name="session_datetime"]');
                        if (cInput) cInput.value = new Date().toISOString().slice(0, 16);
                        counselingForm.addEventListener('submit', function(e) {
                            e.preventDefault();
                            alert('Counseling session saved successfully.');
                            e.target.reset();
                            if (cInput) cInput.value = new Date().toISOString().slice(0, 16);
                        });
                    }

                    // Health Education session
                    const healthEdForm = document.getElementById('healthEdForm');
                    if (healthEdForm) {
                        const hInput = healthEdForm.querySelector('input[name="session_date"]');
                        if (hInput) hInput.value = new Date().toISOString().slice(0, 10);
                        healthEdForm.addEventListener('submit', function(e) {
                            e.preventDefault();
                            alert('Health education session recorded successfully.');
                            e.target.reset();
                            if (hInput) hInput.value = new Date().toISOString().slice(0, 10);
                        });
                    }

                    // Emergency session
                    const emergencyForm = document.getElementById('emergencyForm');
                    if (emergencyForm) {
                        const eInput = emergencyForm.querySelector('input[name="incident_datetime"]');
                        if (eInput) eInput.value = new Date().toISOString().slice(0, 16);
                        emergencyForm.addEventListener('submit', function(e) {
                            e.preventDefault();
                            alert('Emergency response entry saved.');
                            e.target.reset();
                            if (eInput) eInput.value = new Date().toISOString().slice(0, 16);
                        });
                    }

                    document.getElementById('logout').addEventListener('click', function(e) {
                        e.preventDefault();
                        if (confirm('Are you sure you want to logout?')) {
                            localStorage.removeItem('midwife_user');
                            window.location.href = '../midwife-login.html';
                        }
                    });
                }

                function applyHashSection() {
                    const hash = window.location.hash;
                    if (!hash || hash.length < 2) return;
                    const link = document.querySelector('.sidebar-menu a[href="' + hash + '"]');
                    if (link) link.click();
                }

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

                function showLogActivity() {
                    document.querySelector('[href="#log-activity"]').click();
                }

                function showSchedule() {
                    document.querySelector('[href="#schedule"]').click();
                }

                function showPatients() {
                    document.querySelector('[href="#patients"]').click();
                }

                function showTriposha() {
                    document.querySelector('[href="#triposha"]').click();
                }

                function setCurrentDateTime() {
                    const now = new Date();
                    const datetime = now.toISOString().slice(0, 16);
                    const datetimeInput = document.querySelector('input[name="activity_datetime"]');
                    if (datetimeInput) {
                        datetimeInput.value = datetime;
                    }
                }

                function initializeCharts() {
                    const rootStyles = getComputedStyle(document.documentElement);
                    const isDarkTheme = document.documentElement.getAttribute('data-theme') === 'dark';
                    const axisTextColor = (rootStyles.getPropertyValue('--text-secondary') || '#6c757d').trim();
                    const gridColor = isDarkTheme ? 'rgba(203, 213, 225, 0.14)' : 'rgba(52, 58, 64, 0.12)';

                    // Weekly Performance Chart
                    const weeklyCtx = document.getElementById('weeklyChart');
                    if (weeklyCtx) {
                        new Chart(weeklyCtx.getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                                datasets: [{
                                    label: 'Activities Completed',
                                    data: [3, 5, 4, 6, 4, 2, 1],
                                    backgroundColor: isDarkTheme ? 'rgba(45, 212, 191, 0.75)' : 'rgba(0, 166, 153, 0.85)',
                                    borderColor: isDarkTheme ? '#2dd4bf' : '#00897b',
                                    borderWidth: 1.5,
                                    borderRadius: 6
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
                    }

                    // Activity Breakdown Chart
                    const activityCtx = document.getElementById('activityBreakdownChart');
                    if (activityCtx) {
                        new Chart(activityCtx.getContext('2d'), {
                            type: 'pie',
                            data: {
                                labels: ['Home Visits', 'Vaccinations', 'Counseling', 'Health Education'],
                                datasets: [{
                                    data: [40, 25, 20, 15],
                                    backgroundColor: ['#002E4F', '#00A699', '#00A699', '#ffc107']
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
                }

                // Handle activity form submission
                document.getElementById('activityForm').addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Get form data
                    const formData = new FormData(e.target);
                    const activityData = Object.fromEntries(formData);

                    // Show success message
                    alert('Activity logged successfully!');

                    // Reset form
                    e.target.reset();
                    setCurrentDateTime();

                    // Redirect to dashboard
                    document.querySelector('[href="#dashboard"]').click();
                });




                let currentTimetableTab = 'month';

                function syncTimetableInputs() {
                    const yearInput = document.getElementById('timetableYear');
                    const monthInput = document.getElementById('timetableMonth');
                    const dayInput = document.getElementById('timetableDay');
                    const selectedYear = yearInput.value || '2026';
                    const selectedMonth = monthInput.value || '03';
                    const selectedDay = dayInput.value || '01';

                    if (currentTimetableTab === 'year') {
                        monthInput.value = '01';
                        dayInput.value = '01';
                    } else if (currentTimetableTab === 'month') {
                        dayInput.value = '01';
                    } else if (currentTimetableTab === 'day') {
                        // keep all values as chosen
                    }

                    // Keep selected year aligned to actual data field
                    yearInput.value = selectedYear;
                    monthInput.value = selectedMonth;
                    dayInput.value = selectedDay;
                }

                function setTimetableTab(tabName) {
                    currentTimetableTab = tabName;

                    document.getElementById('tab-year').classList.toggle('active', tabName === 'year');
                    document.getElementById('tab-month').classList.toggle('active', tabName === 'month');
                    document.getElementById('tab-day').classList.toggle('active', tabName === 'day');

                    document.getElementById('timetable-month-wrapper').style.display = tabName === 'year' || tabName === 'month' ? 'block' : 'none';
                    document.getElementById('timetable-day-wrapper').style.display = tabName === 'day' || tabName === 'month' ? 'block' : 'none';

                    syncTimetableInputs();
                    loadTimetableData();
                }

              
                function getActivityIcon(type) {
                    const icons = {
                        'home-visit': 'fas fa-home',
                        'clinic': 'fas fa-hospital',
                        'vaccination': 'fas fa-syringe',
                        'counseling': 'fas fa-comments',
                        'meeting': 'fas fa-users',
                        'training': 'fas fa-graduation-cap',
                        'other': 'fas fa-calendar-check'
                    };
                    return icons[type] || 'fas fa-calendar-check';
                }

                function formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-US', {
                        weekday: 'short',
                        month: 'short',
                        day: 'numeric'
                    });
                }

                function viewTimetableItem(index) {
                    alert('Viewing detailed information for timetable item ' + (index + 1));
                }

                function editTimetableItem(index) {
                    alert('Editing timetable item ' + (index + 1));
                }

                function deleteTimetableItem(index) {
                    if (confirm('Are you sure you want to delete this timetable item?')) {
                        alert('Timetable item ' + (index + 1) + ' deleted');
                        loadTimetableData(); // Refresh the timetable
                    }
                }

                function editTimetable() {
                    alert('Opening timetable editor...');
                    // Could open a more comprehensive editing interface
                }

                function generateReport() {
                    alert('Generate report functionality would be implemented here');
                }

                // Home Visits Functions
                let currentHomeVisits = [];
                let currentArea = 'uduthuththiripitiya';



                function renderHomeVisits() {
                    const visitList = document.querySelector('#scheduled-visits .visit-list');
                    if (!visitList) return;

                    const scheduledVisits = currentHomeVisits.filter(v => v.status !== 'completed');

                    if (scheduledVisits.length === 0) {
                        visitList.innerHTML = '<div class="text-center p-4"><p class="text-muted">No scheduled visits. Click "Schedule New Visit" to add one.</p></div>';
                        return;
                    }

                    visitList.innerHTML = scheduledVisits.map((visit, index) => {
                        const priorityClass = visit.priority === 'high' || visit.priority === 'urgent' ? 'priority-high' : 'priority-normal';
                        const statusBadge = getStatusBadge(visit.status);

                        return `
                    <div class="visit-item ${priorityClass}">
                        <div class="visit-time">
                            <span class="time">${visit.start_time}</span>
                            <span class="duration">${visit.duration_minutes || 45} min</span>
                        </div>
                        <div class="visit-details">
                            <h5>${visit.patient_name}</h5>
                            <p class="address">
                                <i class="fas fa-map-marker-alt"></i>
                                ${visit.address || 'Address not specified'}
                            </p>
                            <p class="visit-type">
                                <span class="badge badge-${getVisitBadgeClass(visit.visit_type)}">${formatVisitType(visit.visit_type)}</span>
                                <span class="badge badge-${visit.priority === 'urgent' ? 'urgent' : visit.priority === 'high' ? 'warning' : 'info'}">${visit.priority}</span>
                            </p>
                            ${visit.notes ? `<p class="notes">${visit.notes}</p>` : ''}
                        </div>
                        <div class="visit-actions">
                            <button class="btn btn-success btn-sm" onclick="completeVisit(${visit.id})">
                                <i class="fas fa-check"></i> Complete
                            </button>
                            <button class="btn btn-info btn-sm" onclick="viewVisitDetails(${visit.id})">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteVisit(${visit.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                `;
                    }).join('');
                }

                function updateHomeVisitStats() {
                    const today = currentHomeVisits.filter(v => v.visit_date === new Date().toISOString().split('T')[0]);
                    const completed = today.filter(v => v.status === 'completed').length;
                    const pending = today.filter(v => v.status === 'scheduled' || v.status === 'in_progress').length;
                    const urgent = today.filter(v => v.priority === 'urgent' || v.priority === 'high').length;

                    const statCards = document.querySelectorAll('#home-visits .stat-card');
                    if (statCards.length >= 4) {
                        statCards[0].querySelector('h3').textContent = today.length;
                        statCards[1].querySelector('h3').textContent = pending;
                        statCards[2].querySelector('h3').textContent = completed;
                        statCards[3].querySelector('h3').textContent = urgent;
                    }
                }

                function getStatusBadge(status) {
                    const badges = {
                        'scheduled': '<span class="badge badge-primary">Scheduled</span>',
                        'in_progress': '<span class="badge badge-warning">In Progress</span>',
                        'completed': '<span class="badge badge-success">Completed</span>',
                        'cancelled': '<span class="badge badge-secondary">Cancelled</span>'
                    };
                    return badges[status] || badges['scheduled'];
                }

                function getVisitBadgeClass(type) {
                    const classes = {
                        'antenatal': 'success',
                        'postnatal': 'primary',
                        'family-planning': 'info',
                        'emergency': 'danger',
                        'routine': 'primary'
                    };
                    return classes[type] || 'primary';
                }

                function formatVisitType(type) {
                    const types = {
                        'antenatal': 'Antenatal Visit',
                        'postnatal': 'Postnatal Visit',
                        'family-planning': 'Family Planning',
                        'emergency': 'Emergency Follow-up',
                        'routine': 'Routine Visit'
                    };
                    return types[type] || type;
                }

                function viewVisitDetails(visitId) {
                    const visit = currentHomeVisits.find(v => v.id === visitId);
                    if (!visit) return;

                    const detailsCard = document.getElementById('home-visit-details-record');
                    const detailsBody = document.getElementById('home-visit-details-body');

                    detailsBody.innerHTML = `
                <table class="table table-bordered">
                    <tr><th>Patient Name</th><td>${visit.patient_name}</td></tr>
                    <tr><th>Contact</th><td>${visit.contact_number || 'Not provided'}</td></tr>
                    <tr><th>Address</th><td>${visit.address || 'Not provided'}</td></tr>
                    <tr><th>Visit Date</th><td>${visit.visit_date}</td></tr>
                    <tr><th>Time</th><td>${visit.start_time}</td></tr>
                    <tr><th>Duty Area</th><td>${visit.duty_area}</td></tr>
                    <tr><th>Visit Type</th><td>${formatVisitType(visit.visit_type)}</td></tr>
                    <tr><th>Priority</th><td>${visit.priority}</td></tr>
                    <tr><th>Status</th><td>${getStatusBadge(visit.status)}</td></tr>
                    <tr><th>Reason</th><td>${visit.reason || 'Not specified'}</td></tr>
                    <tr><th>Notes</th><td>${visit.notes || 'No notes'}</td></tr>
                    <tr><th>Created</th><td>${visit.created_at || 'N/A'}</td></tr>
                </table>
                <button class="btn btn-outline-secondary" onclick="document.getElementById('home-visit-details-record').style.display='none'">Close</button>
            `;
                    detailsCard.style.display = 'block';
                }


                async function deleteVisit(visitId) {
                    if (!confirm('Are you sure you want to delete this visit?')) return;

                    try {
                        const response = await fetch(`../php/home_visits.php?action=delete&id=${visitId}`);
                        const result = await response.json();

                        if (result.success) {
                            alert('Visit deleted!');
                            loadHomeVisits();
                        }
                    } catch (error) {
                        console.error('Error deleting visit:', error);
                    }
                }

                function renderCompletedVisits() {
                    const completedList = document.querySelector('#completed-visits .completed-visit-list');
                    if (!completedList) return;

                    const completedVisits = currentHomeVisits.filter(v => v.status === 'completed');

                    if (completedVisits.length === 0) {
                        completedList.innerHTML = '<div class="text-center p-4"><p class="text-muted">No completed visits yet.</p></div>';
                        return;
                    }

                    completedList.innerHTML = completedVisits.map(visit => `
                <div class="completed-visit-item">
                    <div class="visit-timestamp">
                        <span class="date">${visit.visit_date}</span>
                        <span class="time">${visit.start_time}${visit.end_time ? ' - ' + visit.end_time : ''}</span>
                    </div>
                    <div class="visit-summary">
                        <h5>${visit.patient_name}</h5>
                        <p class="visit-type">${formatVisitType(visit.visit_type)}</p>
                        <p class="outcome">
                            <span class="status-badge status-success">Completed</span>
                            ${visit.notes || ''}
                        </p>
                    </div>
                    <div class="visit-actions">
                        <button class="btn btn-outline-primary btn-sm" onclick="viewVisitDetails(${visit.id})">
                            <i class="fas fa-file-alt"></i> View Details
                        </button>
                    </div>
                </div>
            `).join('');
                }

                async function saveNewVisit() {
                    const user = JSON.parse(localStorage.getItem('midwife_user') || '{}');
                    const midwifeId = user.id || 1;

                    const visitData = {
                        midwife_id: midwifeId,
                        patient_name: document.getElementById('visit_patient_name').value,
                        contact_number: document.getElementById('visit_contact').value,
                        address: document.getElementById('visit_address').value,
                        visit_type: document.getElementById('visit_type').value,
                        priority: document.getElementById('visit_priority').value,
                        visit_date: document.getElementById('visit_date').value,
                        start_time: document.getElementById('visit_time').value,
                        duration_minutes: parseInt(document.getElementById('visit_duration').value),
                        duty_area: currentArea,
                        reason: document.getElementById('visit_reason').value,
                        notes: document.getElementById('visit_notes').value,
                        status: 'scheduled'
                    };

                    if (!visitData.patient_name || !visitData.visit_date || !visitData.start_time) {
                        alert('Please fill in all required fields (Patient Name, Date, Time)');
                        return;
                    }

                    try {
                        const response = await fetch('../php/home_visits.php?action=create', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(visitData)
                        });
                        const result = await response.json();

                        if (result.success) {
                            alert('Home visit scheduled successfully!');
                            closeModal();
                            loadHomeVisits();
                        } else {
                            alert('Error: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Error saving visit:', error);
                        alert('Error saving visit. Please try again.');
                    }
                }

                function quickVisitLog() {
                    const user = JSON.parse(localStorage.getItem('midwife_user') || '{}');
                    const pendingVisits = currentHomeVisits.filter(v => v.status === 'scheduled');

                    const modal = document.createElement('div');
                    modal.className = 'modal-overlay';
                    modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Quick Visit Log</h3>
                        <button onclick="closeModal()" class="close-btn">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="quickLogForm">
                            <div class="form-group">
                                <label class="form-label">Select Visit *</label>
                                <select class="form-control" id="quick_visit_id" required>
                                    <option value="">Select from today's visits</option>
                                    ${pendingVisits.map(v => `
                                        <option value="${v.id}">${v.patient_name} - ${v.start_time} (${formatVisitType(v.visit_type)})</option>
                                    `).join('')}
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Visit Status *</label>
                                        <select class="form-control" id="quick_status" required>
                                            <option value="completed">Completed Successfully</option>
                                            <option value="partial">Partially Completed</option>
                                            <option value="cancelled">Patient Not Available</option>
                                            <option value="rescheduled">Rescheduled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">End Time</label>
                                        <input type="time" class="form-control" id="quick_end_time" value="${new Date().toTimeString().slice(0, 5)}">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Visit Summary / Notes *</label>
                                <textarea class="form-control" id="quick_notes" rows="4" required placeholder="Brief summary of the visit, findings, and actions taken..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                        <button type="button" class="btn btn-success" onclick="saveQuickVisit()">Log Visit</button>
                    </div>
                </div>
            `;
                    document.body.appendChild(modal);
                }

                async function saveQuickVisit() {
                    const visitId = document.getElementById('quick_visit_id').value;
                    const status = document.getElementById('quick_status').value;
                    const endTime = document.getElementById('quick_end_time').value;
                    const notes = document.getElementById('quick_notes').value;

                    if (!visitId || !notes) {
                        alert('Please select a visit and enter notes');
                        return;
                    }

                    try {
                        let response;
                        if (status === 'completed') {
                            response = await fetch(`../php/home_visits.php?action=complete&id=${visitId}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    notes: notes,
                                    end_time: endTime
                                })
                            });
                        } else {
                            response = await fetch(`../php/home_visits.php?action=update&id=${visitId}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    status: status,
                                    notes: notes
                                })
                            });
                        }

                        const result = await response.json();

                        if (result.success) {
                            alert('Visit logged successfully!');
                            closeModal();
                            await loadHomeVisits();
                            if (status === 'completed') {
                                switchVisitTab('completed');
                            }
                        } else {
                            alert('Error: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Error logging visit:', error);
                        alert('Error logging visit');
                    }
                }


                function getVisitTabFromUrl() {
                    const params = new URLSearchParams(window.location.search);
                    const tab = params.get('visitTab') || params.get('tab') || 'scheduled';
                    return tab === 'completed' ? 'completed' : 'scheduled';
                }

                function filterVisitsByDate(period) {
                    alert(`Filtering visits by: ${period}`);
                    // Implementation would filter visit list based on selected period
                }

                function optimizeRoute() {
                    alert('Route optimization feature would analyze current visits and suggest the most efficient route');
                }

                function startVisit(visitId) {
                    if (confirm('Are you sure you want to start this visit?')) {
                        alert(`Starting visit for patient ID: ${visitId}`);
                        // Implementation would mark visit as in-progress
                    }
                }

                function viewPatientDetails(patientId) {
                    const patientRecords = {
                        1: {
                            name: 'Mrs. Nirmala Fernando',
                            age: 29,
                            visitType: 'Postnatal Visit - Day 3',
                            location: 'No. 45, Galle Road, Mount Lavinia',
                            time: '09:00',
                            duration: '45 min',
                            notes: 'Follow-up on breastfeeding issues and jaundice monitoring',
                            risk: 'Medium',
                            lastVisit: '2026-03-20',
                            doctorComments: 'Continue vitamin D and iron supplements'
                        },
                        2: {
                            name: 'Mrs. Kamani Wickramasinghe',
                            age: 32,
                            visitType: 'Antenatal Visit - 32 weeks',
                            location: 'No. 78, Temple Road, Dehiwala',
                            time: '10:30',
                            duration: '30 min',
                            notes: 'Routine checkup, monitor blood pressure and fetal growth',
                            risk: 'High',
                            lastVisit: '2026-03-19',
                            doctorComments: 'Schedule ultrasound and monitor blood sugar'
                        },
                        3: {
                            name: 'Mrs. Sandya Peris',
                            age: 26,
                            visitType: 'Postnatal Visit - Day 14',
                            location: 'No. 23, Flower Road, Colombo 7',
                            time: '14:00',
                            duration: '40 min',
                            notes: 'Check healing progress, discuss family planning',
                            risk: 'Low',
                            lastVisit: '2026-03-16',
                            doctorComments: 'All fine, continue current postpartum diet'
                        }
                    };

                    const record = patientRecords[patientId];
                    const detailsCard = document.getElementById('home-visit-details-record');
                    const detailsBody = document.getElementById('home-visit-details-body');

                    if (!record) {
                        detailsBody.innerHTML = '<p class="text-danger">No records found for this visit.</p>';
                        detailsCard.style.display = 'block';
                        return;
                    }

                    detailsBody.innerHTML = `
                <table class="table table-bordered">
                    <tr><th>Patient Name</th><td>${record.name}</td></tr>
                    <tr><th>Age</th><td>${record.age}</td></tr>
                    <tr><th>Visit Type</th><td>${record.visitType}</td></tr>
                    <tr><th>Location</th><td>${record.location}</td></tr>
                    <tr><th>Time</th><td>${record.time}</td></tr>
                    <tr><th>Duration</th><td>${record.duration}</td></tr>
                    <tr><th>Notes</th><td>${record.notes}</td></tr>
                    <tr><th>Risk Level</th><td>${record.risk}</td></tr>
                    <tr><th>Last Visit</th><td>${record.lastVisit}</td></tr>
                    <tr><th>Doctor Comments</th><td>${record.doctorComments}</td></tr>
                </table>
                <button class="btn btn-outline-secondary" onclick="document.getElementById('home-visit-details-record').style.display='none'">Close Details</button>
            `;
                    detailsCard.style.display = 'block';
                }

                function rescheduleVisit(visitId) {
                    alert(`Rescheduling visit ID: ${visitId}`);
                    // Implementation would open reschedule modal
                }

                function getDirections(visitId) {
                    alert(`Getting directions to visit location for visit ID: ${visitId}`);
                    // Implementation would open maps application or show directions
                }

                function viewVisitReport(reportId) {
                    alert(`Viewing visit report ID: ${reportId}`);
                    // Implementation would show detailed visit report
                }

                function loadMap() {
                    alert('Loading interactive route map...');
                    // Implementation would integrate with Google Maps or similar service
                }

                function closeModal() {
                    const modal = document.querySelector('.modal-overlay');
                    if (modal) {
                        document.body.removeChild(modal);
                    }
                }

                // Add modal styles
                const modalStyles = `
            <style>
                .modal-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 1000;
                }
                
                .modal-content {
                    background: white;
                    border-radius: var(--radius-lg);
                    max-width: 600px;
                    width: 90%;
                    max-height: 90%;
                    overflow-y: auto;
                }
                
                .modal-header {
                    padding: 1.5rem;
                    border-bottom: 1px solid #e9ecef;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                .modal-body {
                    padding: 1.5rem;
                }
                
                .modal-footer {
                    padding: 1rem 1.5rem;
                    border-top: 1px solid #e9ecef;
                    display: flex;
                    justify-content: flex-end;
                    gap: 1rem;
                }
                
                .close-btn {
                    background: none;
                    border: none;
                    font-size: 1.5rem;
                    cursor: pointer;
                    color: var(--text-muted);
                }
                
                .close-btn:hover {
                    color: var(--text-primary);
                }
            </style>
        `;

                if (!document.querySelector('#modal-styles')) {
                    const styleElement = document.createElement('div');
                    styleElement.id = 'modal-styles';
                    styleElement.innerHTML = modalStyles;
                    document.head.appendChild(styleElement);
                }


                function printSchedule() {
                    alert('Printing vaccination schedule...');
                }

                function administerVaccine(patientId) {
                    if (confirm('Are you ready to administer this vaccine?')) {
                        alert(`Administering vaccine for patient ID: ${patientId}`);
                    }
                }


                function checkExpiring() {
                    alert('Checking for vaccines expiring in the next 30 days...');
                }

                function orderSupplies() {
                    alert('Opening vaccine supply order form...');
                }

                function contactPatient(patientId) {
                    alert(`Contacting patient ID: ${patientId} about overdue vaccination`);
                }

                function scheduleOverdue(patientId) {
                    alert(`Scheduling overdue vaccination for patient ID: ${patientId}`);
                }


            
                function openProfileImagePreview() {
                    const profileImage = document.querySelector('#profileImage img');
                    if (!profileImage) {
                        return;
                    }

                    let previewModal = document.getElementById('profileImagePreview');
                    if (!previewModal) {
                        previewModal = document.createElement('div');
                        previewModal.id = 'profileImagePreview';
                        previewModal.className = 'profile-image-preview';
                        previewModal.setAttribute('aria-hidden', 'true');
                        previewModal.innerHTML = `
                    <button type="button" class="preview-close-btn" aria-label="Close image preview">&times;</button>
                    <img id="profileImagePreviewContent" src="" alt="Profile image full screen">
                `;
                        document.body.appendChild(previewModal);

                        previewModal.addEventListener('click', function() {
                            closeProfileImagePreview();
                        });

                        const previewImg = previewModal.querySelector('#profileImagePreviewContent');
                        if (previewImg) {
                            previewImg.addEventListener('click', function(event) {
                                event.stopPropagation();
                            });
                        }

                        const closeBtn = previewModal.querySelector('.preview-close-btn');
                        if (closeBtn) {
                            closeBtn.addEventListener('click', function(event) {
                                event.stopPropagation();
                                closeProfileImagePreview();
                            });
                        }
                    }

                    const previewContent = document.getElementById('profileImagePreviewContent');
                    if (!previewContent) {
                        return;
                    }

                    previewContent.src = profileImage.src;
                    previewContent.alt = profileImage.alt || 'Profile image full screen';
                    previewModal.classList.add('show');
                    previewModal.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                }

                function closeProfileImagePreview() {
                    const previewModal = document.getElementById('profileImagePreview');
                    if (!previewModal) {
                        return;
                    }

                    previewModal.classList.remove('show');
                    previewModal.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                }

                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape') {
                        closeProfileImagePreview();
                    }
                });

                function switchProfileTab(tabName) {
                    // Remove active class from all nav links in profile section
                    document.querySelectorAll('#profile .nav-link').forEach(link => {
                        link.classList.remove('active');
                    });

                    // Hide all tab contents in profile section
                    document.querySelectorAll('#profile .tab-content').forEach(content => {
                        content.style.display = 'none';
                    });

                    // Show selected tab content
                    const selectedTab = document.getElementById(tabName + '-profile');
                    if (selectedTab) {
                        selectedTab.style.display = 'block';
                    }

                    // Add active class to clicked nav link
                    if (event && event.target) {
                        event.target.classList.add('active');
                    }

                    // Initialize performance chart if performance tab is selected
                    if (tabName === 'performance') {
                        setTimeout(initializePerformanceChart, 100);
                    }
                }

                function initializePerformanceChart() {
                    const ctx = document.getElementById('performanceChart');
                    if (ctx && !ctx.chart) {
                        const rootStyles = getComputedStyle(document.documentElement);
                        const isDarkTheme = document.documentElement.getAttribute('data-theme') === 'dark';
                        const axisTextColor = (rootStyles.getPropertyValue('--text-secondary') || '#6c757d').trim();
                        const gridColor = isDarkTheme ? 'rgba(203, 213, 225, 0.14)' : 'rgba(52, 58, 64, 0.12)';

                        ctx.chart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                                datasets: [{
                                    label: 'Patients Served',
                                    data: [95, 102, 118, 89, 125, 134, 127, 142, 156, 163, 148, 152],
                                    borderColor: isDarkTheme ? '#2dd4bf' : '#00897b',
                                    backgroundColor: isDarkTheme ? 'rgba(45, 212, 191, 0.18)' : 'rgba(0, 137, 123, 0.12)',
                                    pointBackgroundColor: isDarkTheme ? '#5eead4' : '#00897b',
                                    pointBorderColor: '#ffffff',
                                    borderWidth: 3,
                                    pointRadius: 3,
                                    tension: 0.4,
                                    fill: true
                                }, {
                                    label: 'Home Visits',
                                    data: [68, 75, 82, 71, 88, 92, 85, 95, 104, 98, 89, 96],
                                    borderColor: isDarkTheme ? '#60a5fa' : '#00509e',
                                    backgroundColor: isDarkTheme ? 'rgba(96, 165, 250, 0.18)' : 'rgba(0, 80, 158, 0.12)',
                                    pointBackgroundColor: isDarkTheme ? '#93c5fd' : '#00509e',
                                    pointBorderColor: '#ffffff',
                                    borderWidth: 3,
                                    pointRadius: 3,
                                    tension: 0.4,
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
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
                                },
                                plugins: {
                                    legend: {
                                        position: 'top',
                                        labels: {
                                            color: axisTextColor
                                        }
                                    }
                                }
                            }
                        });
                    }
                }

                function exportData() {
                    alert('Generating data export... You will receive a download link via email within 24 hours.');
                }

     su
                // // Tab switching function for Mother categories
                // function switchMotherTab(tabName) {
                //     event.preventDefault();
                //     event.stopPropagation();
                //     // Remove active class from all mother sub-nav links
                //     const motherNavLinks = document.querySelectorAll('#mothers-tab .nav-link');
                //     motherNavLinks.forEach(link => link.classList.remove('active'));

                //     // Hide all mother tab contents
                //     document.getElementById('pregnant-mothers').classList.add('hidden');
                //     document.getElementById('lactating-mothers').classList.add('hidden');
                //     document.getElementById('postnatal-mothers').classList.add('hidden');

                //     // Add active class to clicked nav link
                //     event.target.classList.add('active');

                //     // Show selected mother tab content
                //     if (tabName === 'pregnant') {
                //         document.getElementById('pregnant-mothers').classList.remove('hidden');
                //     } else if (tabName === 'lactating') {
                //         document.getElementById('lactating-mothers').classList.remove('hidden');
                //     } else if (tabName === 'postnatal') {
                //         document.getElementById('postnatal-mothers').classList.remove('hidden');
                //     }
                //     return false;
                // }

       
                function showComprehensiveMotherProfile(type, id, name, age, ...additionalData) {
                    const modal = document.createElement('div');
                    modal.className = 'modal-overlay';
                    modal.id = 'comprehensiveMotherModal';
                    modal.innerHTML = `
                <div class="modal-content" style="max-width: 900px; max-height: 95vh; overflow-y: auto;">
                    <div class="modal-header">
                        <h3>Complete Profile - ${name}</h3>
                        <button onclick="closeComprehensiveModal()" class="close-btn">&times;</button>
                    </div>
                    <div class="modal-body" style="padding: 2rem;">
                        <form id="comprehensiveMotherForm">
                            <input type="hidden" id="motherType" value="${type}">
                            <input type="hidden" id="motherId" value="${id}">
                            
                            <!-- Personal Information Section -->
                            <h4 style="color: #002E4F; border-bottom: 2px solid #00A699; padding-bottom: 0.5rem; margin-bottom: 1rem;">Personal Information</h4>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Full Name *</label>
                                        <input type="text" id="fullName" class="form-control" value="${name}" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Date of Birth *</label>
                                        <input type="date" id="dateOfBirth" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Mobile Phone *</label>
                                        <input type="tel" id="mobilePhone" class="form-control" placeholder="+94 77 123 4567" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Home Phone</label>
                                        <input type="tel" id="homePhone" class="form-control" placeholder="+94 11 234 5678">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Personal Email</label>
                                        <input type="email" id="personalEmail" class="form-control" placeholder="example@gmail.com">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Marital Status *</label>
                                        <select id="maritalStatus" class="form-control" required>
                                            <option value="">Select Status</option>
                                            <option value="Single">Single</option>
                                            <option value="Married" selected>Married</option>
                                            <option value="Divorced">Divorced</option>
                                            <option value="Widowed">Widowed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Address Information Section -->
                            <h4 style="color: #002E4F; border-bottom: 2px solid #00A699; padding-bottom: 0.5rem; margin-bottom: 1rem; margin-top: 2rem;">Address Information</h4>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">Home Address *</label>
                                        <textarea id="homeAddress" class="form-control" rows="2" placeholder="No. 123, Galle Road, Mount Lavinia, Colombo" required></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Postal Code *</label>
                                        <input type="text" id="postalCode" class="form-control" placeholder="10370" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">District *</label>
                                        <select id="district" class="form-control" required>
                                            <option value="">Select District</option>
                                            <option value="Colombo" selected>Colombo</option>
                                            <option value="Gampaha">Gampaha</option>
                                            <option value="Kalutara">Kalutara</option>
                                            <option value="Kandy">Kandy</option>
                                            <option value="Matale">Matale</option>
                                            <option value="Nuwara Eliya">Nuwara Eliya</option>
                                            <option value="Galle">Galle</option>
                                            <option value="Matara">Matara</option>
                                            <option value="Hambantota">Hambantota</option>
                                            <option value="Kurunegala">Kurunegala</option>
                                            <option value="Puttalam">Puttalam</option>
                                            <option value="Anuradhapura">Anuradhapura</option>
                                            <option value="Polonnaruwa">Polonnaruwa</option>
                                            <option value="Badulla">Badulla</option>
                                            <option value="Moneragala">Moneragala</option>
                                            <option value="Ratnapura">Ratnapura</option>
                                            <option value="Kegalle">Kegalle</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency Contact Section -->
                            <h4 style="color: #002E4F; border-bottom: 2px solid #00A699; padding-bottom: 0.5rem; margin-bottom: 1rem; margin-top: 2rem;">Emergency Contact</h4>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Contact Name *</label>
                                        <input type="text" id="emergencyContactName" class="form-control" placeholder="Sunil Perera (Husband)" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Contact Number *</label>
                                        <input type="tel" id="emergencyContactNumber" class="form-control" placeholder="+94 71 987 6543" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Medical Information Section -->
                            <h4 style="color: #002E4F; border-bottom: 2px solid #00A699; padding-bottom: 0.5rem; margin-bottom: 1rem; margin-top: 2rem;">Medical Information</h4>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Blood Group</label>
                                        <select id="bloodGroup" class="form-control">
                                            <option value="">Select Blood Group</option>
                                            <option value="A+">A+</option>
                                            <option value="A-">A-</option>
                                            <option value="B+">B+</option>
                                            <option value="B-">B-</option>
                                            <option value="AB+">AB+</option>
                                            <option value="AB-">AB-</option>
                                            <option value="O+">O+</option>
                                            <option value="O-">O-</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Height (cm)</label>
                                        <input type="number" id="height" class="form-control" placeholder="160" min="100" max="250">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Weight (kg)</label>
                                        <input type="number" id="weight" class="form-control" placeholder="55" min="30" max="200">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Allergies</label>
                                        <input type="text" id="allergies" class="form-control" placeholder="None, or list allergies">
                                    </div>
                                </div>
                            </div>

                            ${getCategorySpecificFields(type, additionalData)}

                            <!-- Additional Information Section -->
                            <h4 style="color: #002E4F; border-bottom: 2px solid #00A699; padding-bottom: 0.5rem; margin-bottom: 1rem; margin-top: 2rem;">Additional Information</h4>
                            <div class="form-group">
                                <label class="form-label">Medical History</label>
                                <textarea id="medicalHistory" class="form-control" rows="2" placeholder="Previous medical conditions, surgeries, etc."></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Additional Notes</label>
                                <textarea id="additionalNotes" class="form-control" rows="3" placeholder="Any additional notes or observations..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeComprehensiveModal()">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="saveComprehensiveMotherUpdate()">Save All Changes</button>
                    </div>
                </div>
            `;
                    document.body.appendChild(modal);

                    // Set default values
                    setDefaultProfileValues(type, age, additionalData);
                }

                function getCategorySpecificFields(type, additionalData) {
                    if (type === 'pregnant') {
                        return `
                    <!-- Pregnancy Information Section -->
                    <h4 style="color: #002E4F; border-bottom: 2px solid #00A699; padding-bottom: 0.5rem; margin-bottom: 1rem; margin-top: 2rem;">Pregnancy Information</h4>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Weeks Pregnant *</label>
                                <input type="number" id="weeksPregnant" class="form-control" min="1" max="42" value="${additionalData[0] || ''}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Expected Due Date</label>
                                <input type="date" id="expectedDueDate" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Risk Level *</label>
                                <select id="riskLevel" class="form-control">
                                    <option value="Low Risk" ${additionalData[3] === 'Low Risk' ? 'selected' : ''}>Low Risk</option>
                                    <option value="Medium Risk" ${additionalData[3] === 'Medium Risk' ? 'selected' : ''}>Medium Risk</option>
                                    <option value="High Risk" ${additionalData[3] === 'High Risk' ? 'selected' : ''}>High Risk</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Number of Previous Pregnancies</label>
                                <input type="number" id="previousPregnancies" class="form-control" min="0" max="20">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Last Visit Date</label>
                                <input type="date" id="lastVisit" class="form-control" value="${additionalData[1] || ''}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Next Appointment</label>
                                <input type="date" id="nextAppointment" class="form-control" value="${additionalData[2] || ''}">
                            </div>
                        </div>
                    </div>
                `;
                    } else if (type === 'lactating') {
                        return `
                    <!-- Lactation Information Section -->
                    <h4 style="color: #002E4F; border-bottom: 2px solid #00A699; padding-bottom: 0.5rem; margin-bottom: 1rem; margin-top: 2rem;">Lactation Information</h4>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Baby's Age *</label>
                                <input type="text" id="babyAge" class="form-control" placeholder="e.g., 2 weeks, 3 months" value="${additionalData[0] || ''}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Baby's Name</label>
                                <input type="text" id="babyName" class="form-control" placeholder="Baby's name">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Breastfeeding Status *</label>
                                <select id="breastfeedingStatus" class="form-control">
                                    <option value="Exclusive breastfeeding" ${additionalData[2] === 'Exclusive breastfeeding' ? 'selected' : ''}>Exclusive Breastfeeding</option>
                                    <option value="Mixed feeding" ${additionalData[2] === 'Mixed feeding' ? 'selected' : ''}>Mixed Feeding</option>
                                    <option value="Formula feeding" ${additionalData[2] === 'Formula feeding' ? 'selected' : ''}>Formula Feeding</option>
                                    <option value="Weaning" ${additionalData[2] === 'Weaning' ? 'selected' : ''}>Weaning</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Support Level *</label>
                                <select id="supportLevel" class="form-control">
                                    <option value="Good Support" ${additionalData[3] === 'Good Support' ? 'selected' : ''}>Good Support</option>
                                    <option value="Needs Support" ${additionalData[3] === 'Needs Support' ? 'selected' : ''}>Needs Support</option>
                                    <option value="Critical Support" ${additionalData[3] === 'Critical Support' ? 'selected' : ''}>Critical Support</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Visit Date</label>
                        <input type="date" id="lastVisit" class="form-control" value="${additionalData[1] || ''}">
                    </div>
                `;
                    } else if (type === 'postnatal') {
                        return `
                    <!-- Delivery Information Section -->
                    <h4 style="color: #002E4F; border-bottom: 2px solid #00A699; padding-bottom: 0.5rem; margin-bottom: 1rem; margin-top: 2rem;">Delivery Information</h4>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Delivery Date *</label>
                                <input type="date" id="deliveryDate" class="form-control" value="${additionalData[0] || ''}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Delivery Type *</label>
                                <select id="deliveryType" class="form-control">
                                    <option value="Normal Delivery" ${additionalData[2] === 'Normal Delivery' ? 'selected' : ''}>Normal Delivery</option>
                                    <option value="C-Section" ${additionalData[2] === 'C-Section' ? 'selected' : ''}>C-Section</option>
                                    <option value="Assisted Delivery" ${additionalData[2] === 'Assisted Delivery' ? 'selected' : ''}>Assisted Delivery</option>
                                    <option value="Emergency C-Section" ${additionalData[2] === 'Emergency C-Section' ? 'selected' : ''}>Emergency C-Section</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Recovery Status *</label>
                                <select id="recoveryStatus" class="form-control">
                                    <option value="Excellent Recovery" ${additionalData[3] === 'Excellent Recovery' ? 'selected' : ''}>Excellent Recovery</option>
                                    <option value="Good Recovery" ${additionalData[3] === 'Good Recovery' ? 'selected' : ''}>Good Recovery</option>
                                    <option value="Slow Recovery" ${additionalData[3] === 'Slow Recovery' ? 'selected' : ''}>Slow Recovery</option>
                                    <option value="Complications" ${additionalData[3] === 'Complications' ? 'selected' : ''}>Complications</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Baby's Birth Weight (kg)</label>
                                <input type="number" id="babyBirthWeight" class="form-control" placeholder="3.2" step="0.1" min="0.5" max="8">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Visit Date</label>
                        <input type="date" id="lastVisit" class="form-control" value="${additionalData[1] || ''}">
                    </div>
                `;
                    }
                    return '';
                }

                function setDefaultProfileValues(type, age, additionalData) {
                    // Set some default values to simulate existing data
                    document.getElementById('mobilePhone').value = '+94 77 123 4567';
                    document.getElementById('homePhone').value = '+94 11 234 5678';
                    document.getElementById('personalEmail').value = 'example@gmail.com';
                    document.getElementById('homeAddress').value = 'No. 123, Galle Road, Mount Lavinia, Colombo';
                    document.getElementById('postalCode').value = '10370';
                    document.getElementById('emergencyContactName').value = 'Sunil Perera (Husband)';
                    document.getElementById('emergencyContactNumber').value = '+94 71 987 6543';
                    document.getElementById('bloodGroup').value = 'O+';
                    document.getElementById('height').value = '160';
                    document.getElementById('weight').value = '55';

                    // Set birth date based on age
                    const currentYear = new Date().getFullYear();
                    const birthYear = currentYear - parseInt(age);
                    document.getElementById('dateOfBirth').value = `${birthYear}-03-15`;
                }

                function closeComprehensiveModal() {
                    const modal = document.getElementById('comprehensiveMotherModal');
                    if (modal) {
                        document.body.removeChild(modal);
                    }
                }

                function saveComprehensiveMotherUpdate() {
                    const formData = {
                        type: document.getElementById('motherType').value,
                        id: document.getElementById('motherId').value,

                        // Personal Information
                        fullName: document.getElementById('fullName').value,
                        dateOfBirth: document.getElementById('dateOfBirth').value,
                        mobilePhone: document.getElementById('mobilePhone').value,
                        homePhone: document.getElementById('homePhone').value,
                        personalEmail: document.getElementById('personalEmail').value,
                        maritalStatus: document.getElementById('maritalStatus').value,

                        // Address Information
                        homeAddress: document.getElementById('homeAddress').value,
                        postalCode: document.getElementById('postalCode').value,
                        district: document.getElementById('district').value,

                        // Emergency Contact
                        emergencyContactName: document.getElementById('emergencyContactName').value,
                        emergencyContactNumber: document.getElementById('emergencyContactNumber').value,

                        // Medical Information
                        bloodGroup: document.getElementById('bloodGroup').value,
                        height: document.getElementById('height').value,
                        weight: document.getElementById('weight').value,
                        allergies: document.getElementById('allergies').value,
                        medicalHistory: document.getElementById('medicalHistory').value,
                        additionalNotes: document.getElementById('additionalNotes').value
                    };

                    // Add category-specific data
                    const type = formData.type;
                    if (type === 'pregnant') {
                        formData.weeksPregnant = document.getElementById('weeksPregnant').value;
                        formData.expectedDueDate = document.getElementById('expectedDueDate').value;
                        formData.riskLevel = document.getElementById('riskLevel').value;
                        formData.previousPregnancies = document.getElementById('previousPregnancies').value;
                        formData.lastVisit = document.getElementById('lastVisit').value;
                        formData.nextAppointment = document.getElementById('nextAppointment').value;
                    } else if (type === 'lactating') {
                        formData.babyAge = document.getElementById('babyAge').value;
                        formData.babyName = document.getElementById('babyName').value;
                        formData.breastfeedingStatus = document.getElementById('breastfeedingStatus').value;
                        formData.supportLevel = document.getElementById('supportLevel').value;
                        formData.lastVisit = document.getElementById('lastVisit').value;
                    } else if (type === 'postnatal') {
                        formData.deliveryDate = document.getElementById('deliveryDate').value;
                        formData.deliveryType = document.getElementById('deliveryType').value;
                        formData.recoveryStatus = document.getElementById('recoveryStatus').value;
                        formData.babyBirthWeight = document.getElementById('babyBirthWeight').value;
                        formData.lastVisit = document.getElementById('lastVisit').value;
                    }

                    // Here you would typically send the comprehensive data to the server
                    console.log('Saving comprehensive mother profile:', formData);

                    // Show success message and close modal
                    alert(`Successfully updated complete profile for ${formData.fullName}!\\n\\nUpdated Information:\\n• Personal details\\n• Address information\\n• Emergency contact\\n• Medical information\\n• Category-specific details`);
                    closeComprehensiveModal();

                    // In a real application, you would update the database and refresh the table
                }

                // Close modal when clicking outside of it
                document.addEventListener('click', function(event) {
                    const modal = document.getElementById('comprehensiveMotherModal');
                    if (event.target === modal) {
                        closeComprehensiveModal();
                    }
                });

                // Triposha Distribution Functions
                function showDistributionForm() {
                    document.getElementById('distributionModal').style.display = 'block';
                    // Set current date as default
                    const today = new Date().toISOString().split('T')[0];
                    document.querySelector('input[name="distribution_date"]').value = today;
                }

                function closeDistributionModal() {
                    document.getElementById('distributionModal').style.display = 'none';
                    document.getElementById('distributionForm').reset();
                }

                // Remove Distribution Functions
                function showRemoveDistributionForm() {
                    populateDistributionSelect();
                    document.getElementById('removeDistributionModal').style.display = 'block';
                }

                function closeRemoveDistributionModal() {
                    document.getElementById('removeDistributionModal').style.display = 'none';
                    document.getElementById('removeDistributionForm').reset();
                }

                function populateDistributionSelect() {
                    const select = document.getElementById('distributionSelect');
                    const tbody = document.getElementById('distribution-records');
                    const rows = tbody.querySelectorAll('tr');

                    // Clear existing options except the first one
                    select.innerHTML = '<option value="">Select a distribution record to remove</option>';

                    rows.forEach((row, index) => {
                        const cells = row.querySelectorAll('td');
                        if (cells.length >= 4) {
                            const date = cells[0].textContent;
                            const beneficiary = cells[1].textContent;
                            const packets = cells[2].textContent;
                            const category = cells[3].textContent;

                            const option = document.createElement('option');
                            option.value = index;
                            option.textContent = `${date} - ${beneficiary} (${packets} packets - ${category})`;
                            option.setAttribute('data-packets', packets);
                            option.setAttribute('data-category', category);
                            option.setAttribute('data-beneficiary', beneficiary);
                            select.appendChild(option);
                        }
                    });
                }

                function removeDistributionRecord(button, packets, categoryType) {
                    const row = button.closest('tr');
                    const beneficiary = row.cells[1].textContent;
                    const date = row.cells[0].textContent;

                    const confirmation = confirm(
                        `Remove Distribution Record?\n\n` +
                        `Date: ${date}\n` +
                        `Beneficiary: ${beneficiary}\n` +
                        `Packets: ${packets}\n` +
                        `Category: ${categoryType}\n\n` +
                        `This will:\n` +
                        `• Remove this record permanently\n` +
                        `• Reduce distributed packet count by ${packets}\n` +
                        `• Update category totals\n\n` +
                        `Continue with removal?`
                    );

                    if (confirmation) {
                        performDistributionRemoval(row, packets, categoryType, beneficiary);
                    }
                }

                function performDistributionRemoval(row, packets, categoryType, beneficiary) {
                    // Remove the row from table
                    row.remove();

                    // Update total distributed count
                    const currentDistributed = parseInt(document.getElementById('packets-distributed').textContent) || 0;
                    const newDistributed = Math.max(0, currentDistributed - parseInt(packets));
                    document.getElementById('packets-distributed').textContent = newDistributed;
                    document.getElementById('total-distributed-summary').textContent = newDistributed + ' packets';

                    // Update category-specific counts
                    updateCategoryAfterRemoval(categoryType, parseInt(packets));

                    // Update remaining packets
                    updateRemainingPackets();

                    // Show success message
                    alert(`Distribution removed successfully!\n\n` +
                        `Removed ${packets} packet(s) from ${beneficiary}\n` +
                        `Category: ${categoryType}\n` +
                        `Packet counts have been updated.`);

                    // Check if table is empty
                    const tbody = document.getElementById('distribution-records');
                    if (tbody.children.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No distribution records</td></tr>';
                    }
                }

                function updateCategoryAfterRemoval(categoryType, packets) {
                    let elementId;

                    if (categoryType === 'pregnant' || categoryType.includes('Pregnant')) {
                        elementId = 'pregnant-packets';
                    } else if (categoryType === 'lactating' || categoryType.includes('Lactating')) {
                        elementId = 'lactating-packets';
                    } else {
                        elementId = 'children-packets';
                    }

                    const currentElement = document.getElementById(elementId);
                    if (currentElement) {
                        const current = parseInt(currentElement.textContent) || 0;
                        const newValue = Math.max(0, current - packets);
                        currentElement.textContent = newValue + ' packets';
                    }
                }

                // Enhanced editable functions
                function editPacketsReceived() {
                    const current = document.getElementById('packets-received-month').textContent;
                    const newValue = prompt(`Enter number of packets received this month:\nCurrent: ${current}`, current);
                    if (newValue && !isNaN(newValue) && newValue >= 0) {
                        document.getElementById('packets-received-month').textContent = newValue;
                        updateTotalPackets();
                        updateRemainingPackets();
                    }
                }

                function editPacketsLeftPrevious() {
                    const current = document.getElementById('packets-left-previous').textContent;
                    const newValue = prompt(`Enter packets left from previous month:\nCurrent: ${current}`, current);
                    if (newValue && !isNaN(newValue) && newValue >= 0) {
                        document.getElementById('packets-left-previous').textContent = newValue;
                        updateTotalPackets();
                        updateRemainingPackets();
                    }
                }

                function editPacketsDistributed() {
                    const current = document.getElementById('packets-distributed').textContent;
                    const total = parseInt(document.getElementById('total-packets').textContent);
                    const newValue = prompt(`Enter total packets distributed this month:\nCurrent: ${current}\nAvailable: ${total}`, current);
                    if (newValue && !isNaN(newValue) && newValue >= 0) {
                        if (parseInt(newValue) <= total) {
                            document.getElementById('packets-distributed').textContent = newValue;
                            document.getElementById('total-distributed-summary').textContent = newValue + ' packets';
                            updateRemainingPackets();
                        } else {
                            alert('Cannot distribute more packets than available!');
                        }
                    }
                }

                function editCategorySummary(category) {
                    const categoryMap = {
                        'pregnant': {
                            id: 'pregnant-packets',
                            label: 'Pregnant Mothers'
                        },
                        'lactating': {
                            id: 'lactating-packets',
                            label: 'Lactating Mothers'
                        },
                        'children': {
                            id: 'children-packets',
                            label: 'Children (6-23 months)'
                        }
                    };

                    const config = categoryMap[category];
                    const current = document.getElementById(config.id).textContent.replace(' packets', '');
                    const newValue = prompt(`Enter packets distributed to ${config.label}:\nCurrent: ${current}`, current);

                    if (newValue && !isNaN(newValue) && newValue >= 0) {
                        document.getElementById(config.id).textContent = newValue + ' packets';
                        updateCategoryTotals();
                    }
                }

                function updateCategoryTotals() {
                    const pregnant = parseInt(document.getElementById('pregnant-packets').textContent) || 0;
                    const lactating = parseInt(document.getElementById('lactating-packets').textContent) || 0;
                    const children = parseInt(document.getElementById('children-packets').textContent) || 0;
                    const total = pregnant + lactating + children;

                    document.getElementById('packets-distributed').textContent = total;
                    document.getElementById('total-distributed-summary').textContent = total + ' packets';
                    updateRemainingPackets();
                }



                function updateTotalPackets() {
                    const received = parseInt(document.getElementById('packets-received-month').textContent) || 0;
                    const leftPrevious = parseInt(document.getElementById('packets-left-previous').textContent) || 0;
                    const total = received + leftPrevious;
                    document.getElementById('total-packets').textContent = total;
                }

                function updateRemainingPackets() {
                    const total = parseInt(document.getElementById('total-packets').textContent) || 0;
                    const distributed = parseInt(document.getElementById('packets-distributed').textContent) || 0;
                    const remaining = total - distributed;
                    document.getElementById('remaining-packets').textContent = remaining + ' packets';
                }

                function generateTriposhaPeport() {
                    alert('Triposha Distribution Report Generated!\n\nThis Month Summary:\n• Packets Received: ' +
                        document.getElementById('packets-received-month').textContent +
                        '\n• Packets Left from Previous: ' +
                        document.getElementById('packets-left-previous').textContent +
                        '\n• Total Available: ' +
                        document.getElementById('total-packets').textContent +
                        '\n• Packets Distributed: ' +
                        document.getElementById('packets-distributed').textContent);
                }

                // Handle distribution form submission
                document.addEventListener('DOMContentLoaded', function() {
                    const distributionForm = document.getElementById('distributionForm');
                    if (distributionForm) {
                        distributionForm.addEventListener('submit', function(e) {
                            e.preventDefault();

                            const formData = new FormData(this);
                            const distributionData = {
                                date: formData.get('distribution_date'),
                                beneficiary: formData.get('beneficiary_name'),
                                category: formData.get('category'),
                                packets: parseInt(formData.get('packet_count')),
                                notes: formData.get('notes')
                            };

                            // Add new row to distribution records
                            const tbody = document.getElementById('distribution-records');

                            // Remove 'no records' message if it exists
                            const noRecordsRow = tbody.querySelector('td[colspan]');
                            if (noRecordsRow) {
                                noRecordsRow.parentElement.remove();
                            }

                            const newRow = tbody.insertRow(0);
                            const rowId = Date.now(); // Simple ID generation
                            newRow.setAttribute('data-id', rowId);

                            // Determine category type for removal function
                            let categoryType = distributionData.category;
                            if (distributionData.category.includes('child')) {
                                categoryType = 'children';
                            }

                            newRow.innerHTML = `
                        <td>${distributionData.date}</td>
                        <td>${distributionData.beneficiary}</td>
                        <td>${distributionData.packets}</td>
                        <td>${getCategoryText(distributionData.category)}</td>
                        <td><span class="status-badge status-active">Completed</span></td>
                        <td>
                            <button class="btn btn-danger btn-sm" onclick="removeDistributionRecord(this, ${distributionData.packets}, '${categoryType}')" title="Remove this distribution">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;

                            // Update distributed count and category breakdown
                            const currentDistributed = parseInt(document.getElementById('packets-distributed').textContent);
                            const newTotal = currentDistributed + distributionData.packets;
                            document.getElementById('packets-distributed').textContent = newTotal;
                            document.getElementById('total-distributed-summary').textContent = newTotal + ' packets';

                            // Update category-specific counts
                            if (distributionData.category === 'pregnant') {
                                const current = parseInt(document.getElementById('pregnant-packets').textContent) || 0;
                                document.getElementById('pregnant-packets').textContent = (current + distributionData.packets) + ' packets';
                            } else if (distributionData.category === 'lactating') {
                                const current = parseInt(document.getElementById('lactating-packets').textContent) || 0;
                                document.getElementById('lactating-packets').textContent = (current + distributionData.packets) + ' packets';
                            } else if (distributionData.category.includes('child')) {
                                const current = parseInt(document.getElementById('children-packets').textContent) || 0;
                                document.getElementById('children-packets').textContent = (current + distributionData.packets) + ' packets';
                            }

                            updateRemainingPackets();

                            alert(`Successfully recorded distribution of ${distributionData.packets} packet(s) to ${distributionData.beneficiary}!`);
                            closeDistributionModal();
                        });
                    }
                });

                function getCategoryText(category) {
                    const categories = {
                        'pregnant': 'Pregnant Mother',
                        'lactating': 'Lactating Mother',
                        'child_6_23': 'Child (6-23 months)',
                        'child_24_59': 'Child (24-59 months)'
                    };
                    return categories[category] || category;
                }

                // Duty Area Functions
                function selectDutyArea(areaName) {
                    // Remove active class from all duty area cards
                    document.querySelectorAll('.duty-area-card').forEach(card => {
                        card.classList.remove('active');
                    });

                    // Add active class to selected area
                    const selectedCard = event.currentTarget;
                    selectedCard.classList.add('active');

                    // Update status badges
                    document.querySelectorAll('.duty-area-card .status-badge').forEach(badge => {
                        badge.textContent = 'Secondary';
                        badge.className = 'status-badge status-secondary';
                    });

                    // Set selected area as primary
                    const selectedBadge = selectedCard.querySelector('.status-badge');
                    selectedBadge.textContent = 'Primary';
                    selectedBadge.className = 'status-badge status-active';

                    // Show confirmation
                    const areaNames = {
                        'udathuththiripitiya': 'Udathuththiripitiya',
                        'kahabilihena': 'Kahabilihena',
                        'opathella': 'Opathella',
                        'ambalangoda': 'Ambalangoda'
                    };

                    alert(`Primary duty area changed to: ${areaNames[areaName]}`);
                }

                // Triposha Distribution Data for All Areas
                const triposhaData = {
                    uduthuththiripitiya: {
                        header: {
                            title: "Uduthuththiripitiya Area - Triposha Distribution",
                            details: "Coverage: 15 villages | Beneficiaries: 85 families | Distribution Center: Uduthuththiripitiya CHC"
                        },
                        stats: {
                            received: 150,
                            left: 25,
                            total: 175,
                            distributed: 98
                        },
                        records: [{
                                id: 1,
                                date: "2026-02-05",
                                beneficiary: "Mrs. K. Silva",
                                packets: 2,
                                category: "Pregnant Mother",
                                status: "Completed"
                            },
                            {
                                id: 2,
                                date: "2026-02-05",
                                beneficiary: "Mrs. A. Fernando",
                                packets: 3,
                                category: "Lactating Mother",
                                status: "Completed"
                            },
                            {
                                id: 3,
                                date: "2026-02-04",
                                beneficiary: "Mrs. D. Jayawardene",
                                packets: 2,
                                category: "Child (6-23 months)",
                                status: "Completed"
                            },
                            {
                                id: 4,
                                date: "2026-02-04",
                                beneficiary: "Mrs. P. Perera",
                                packets: 1,
                                category: "Pregnant Mother",
                                status: "Pending"
                            }
                        ],
                        summary: {
                            pregnant: 35,
                            lactating: 42,
                            children: 21,
                            totalDistributed: 98,
                            remaining: 77
                        }
                    },
                    kahabilihena: {
                        header: {
                            title: "Kahabilihena Area - Triposha Distribution",
                            details: "Coverage: 12 villages | Beneficiaries: 68 families | Distribution Center: Kahabilihena RH"
                        },
                        stats: {
                            received: 120,
                            left: 18,
                            total: 138,
                            distributed: 75
                        },
                        records: [{
                                id: 1,
                                date: "2026-02-11",
                                beneficiary: "Mrs. D. Perera",
                                packets: 2,
                                category: "Lactating Mother",
                                status: "Completed"
                            },
                            {
                                id: 2,
                                date: "2026-02-10",
                                beneficiary: "Mrs. N. Fernando",
                                packets: 3,
                                category: "Pregnant Mother",
                                status: "Completed"
                            },
                            {
                                id: 3,
                                date: "2026-02-09",
                                beneficiary: "Mrs. K. Silva",
                                packets: 2,
                                category: "Child (6-23 months)",
                                status: "Completed"
                            },
                            {
                                id: 4,
                                date: "2026-02-08",
                                beneficiary: "Mrs. R. Gunawardena",
                                packets: 1,
                                category: "Pregnant Mother",
                                status: "Pending"
                            }
                        ],
                        summary: {
                            pregnant: 28,
                            lactating: 32,
                            children: 15,
                            totalDistributed: 75,
                            remaining: 63
                        }
                    },
                    opathella: {
                        header: {
                            title: "Opathella Area - Triposha Distribution",
                            details: "Coverage: 8 urban wards | Beneficiaries: 52 families | Distribution Center: Opathella PHC"
                        },
                        stats: {
                            received: 90,
                            left: 12,
                            total: 102,
                            distributed: 56
                        },
                        records: [{
                                id: 1,
                                date: "2026-02-11",
                                beneficiary: "Mrs. S. Jayawardena",
                                packets: 2,
                                category: "Child (6-23 months)",
                                status: "Completed"
                            },
                            {
                                id: 2,
                                date: "2026-02-10",
                                beneficiary: "Mrs. M. Silva",
                                packets: 1,
                                category: "Pregnant Mother",
                                status: "Completed"
                            },
                            {
                                id: 3,
                                date: "2026-02-09",
                                beneficiary: "Mrs. A. Perera",
                                packets: 3,
                                category: "Lactating Mother",
                                status: "Completed"
                            },
                            {
                                id: 4,
                                date: "2026-02-08",
                                beneficiary: "Mrs. L. Fernando",
                                packets: 2,
                                category: "Child (6-23 months)",
                                status: "Pending"
                            }
                        ],
                        summary: {
                            pregnant: 18,
                            lactating: 24,
                            children: 14,
                            totalDistributed: 56,
                            remaining: 46
                        }
                    },
                    ambalangoda: {
                        header: {
                            title: "Ambalangoda Area - Triposha Distribution",
                            details: "Coverage: 18 villages | Beneficiaries: 95 families | Distribution Center: Ambalangoda DH"
                        },
                        stats: {
                            received: 180,
                            left: 30,
                            total: 210,
                            distributed: 105
                        },
                        records: [{
                                id: 1,
                                date: "2026-02-11",
                                beneficiary: "Mrs. R. Bandara",
                                packets: 3,
                                category: "Lactating Mother",
                                status: "Completed"
                            },
                            {
                                id: 2,
                                date: "2026-02-11",
                                beneficiary: "Mrs. T. Fernando",
                                packets: 2,
                                category: "Pregnant Mother",
                                status: "Completed"
                            },
                            {
                                id: 3,
                                date: "2026-02-10",
                                beneficiary: "Mrs. K. Rajapaksha",
                                packets: 2,
                                category: "Child (6-23 months)",
                                status: "Completed"
                            },
                            {
                                id: 4,
                                date: "2026-02-09",
                                beneficiary: "Mrs. S. Silva",
                                packets: 3,
                                category: "Lactating Mother",
                                status: "Pending"
                            }
                        ],
                        summary: {
                            pregnant: 38,
                            lactating: 45,
                            children: 22,
                            totalDistributed: 105,
                            remaining: 105
                        }
                    }
                };

                // Current active area for Triposha
                let currentTriposhaArea = 'uduthuththiripitiya';

                // Switch between duty areas in different sections
                function switchArea(sectionId, areaName, event) {
                    if (event) {
                        event.preventDefault();
                        event.stopPropagation();
                    }

                    const section = document.getElementById(sectionId);
                    if (!section) {
                        console.error('Section not found:', sectionId);
                        return;
                    }

                    const buttons = section.querySelectorAll('.duty-area-btn');
                    buttons.forEach(btn => btn.classList.remove('active'));

                    const clickedButton = Array.from(buttons).find(btn =>
                        btn.getAttribute('onclick')?.includes(`'${areaName}'`)
                    );
                    if (clickedButton) {
                        clickedButton.classList.add('active');
                    }

                    if (sectionId === 'triposha' && triposhaData[areaName]) {
                        currentTriposhaArea = areaName;
                        updateTriposhaContent(areaName);
                        return;
                    }

                    const contentWrappers = section.querySelectorAll('.area-content-wrapper');
                    contentWrappers.forEach(wrapper => {
                        wrapper.classList.remove('active');
                    });

                    const selectedWrapper = section.querySelector(`.area-content-wrapper[data-area="${areaName}"]`);
                    if (selectedWrapper) {
                        selectedWrapper.classList.add('active');
                    }

                    if (sectionId === 'home-visits') {
                        currentArea = areaName;
                        loadHomeVisits();
                    }
                }

                // Update Triposha content dynamically
                function updateTriposhaContent(areaName) {
                    const data = triposhaData[areaName];
                    if (!data) return;

                    // Update header
                    const headerTitle = document.querySelector('#triposha .area-header h4');
                    const headerDetails = document.querySelector('#triposha .area-header p');
                    if (headerTitle) headerTitle.innerHTML = `<i class="fas fa-map-marker-alt"></i> ${data.header.title}`;
                    if (headerDetails) headerDetails.textContent = data.header.details;

                    // Update stats
                    document.getElementById('packets-received-month').textContent = data.stats.received;
                    document.getElementById('packets-left-previous').textContent = data.stats.left;
                    document.getElementById('total-packets').textContent = data.stats.total;
                    document.getElementById('packets-distributed').textContent = data.stats.distributed;

                    // Update distribution records table
                    const tbody = document.getElementById('distribution-records');
                    if (tbody) {
                        tbody.innerHTML = data.records.map(record => {
                            const categoryType = record.category.toLowerCase().includes('pregnant') ? 'pregnant' :
                                record.category.toLowerCase().includes('lactating') ? 'lactating' : 'children';
                            return `
                    <tr data-id="${record.id}">
                        <td>${record.date}</td>
                        <td>${record.beneficiary}</td>
                        <td>${record.packets}</td>
                        <td>${record.category}</td>
                        <td><span class="status-badge ${record.status === 'Completed' ? 'status-active' : 'status-pending'}">${record.status}</span></td>
                        <td>
                            <button class="btn btn-danger btn-sm" onclick="removeDistributionRecord(this, ${record.packets}, '${categoryType}')" title="Remove this distribution">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `
                        }).join('');
                    }

                    // Update monthly summary
                    document.getElementById('pregnant-packets').textContent = data.summary.pregnant + ' packets';
                    document.getElementById('lactating-packets').textContent = data.summary.lactating + ' packets';
                    document.getElementById('children-packets').textContent = data.summary.children + ' packets';
                    document.getElementById('total-distributed-summary').textContent = data.summary.totalDistributed + ' packets';
                    document.getElementById('remaining-packets').textContent = data.summary.remaining + ' packets';
                }

                // Time-based greeting
                function updateGreeting() {
                    const hour = new Date().getHours();
                    let greeting;
                    if (hour < 12) {
                        greeting = 'Good Morning';
                    } else if (hour < 18) {
                        greeting = 'Good Afternoon';
                    } else {
                        greeting = 'Good Evening';
                    }
                    const el = document.getElementById('greeting-text');
                    if (el) {
                        el.textContent = greeting + ', Madhavi!';
                    }
                }

                // Run on load and update every minute
                updateGreeting();
                setInterval(updateGreeting, 60000);
            </script>

            <!-- Chatbot Widget -->
            <!--<div id="chatbot" class="chatbot">
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
    <button id="chatbot-toggle" class="chatbot-toggle">
        <i class="fas fa-comments"></i>
    </button> -->

            <!--<style>
        .chatbot-toggle {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--accent-teal);
            color: white;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 166, 153, 0.4);
            z-index: 99;
            transition: all 0.3s ease;
        }

        .chatbot-toggle:hover {
            background: var(--secondary-dark-green);
            box-shadow: 0 6px 16px rgba(0, 166, 153, 0.6);
            transform: scale(1.1);
        }

        .chatbot {
            position: fixed;
            bottom: 5.5rem;
            right: 2rem;
            width: 350px;
            height: 450px;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            display: none;
            flex-direction: column;
            z-index: 100;
            overflow: hidden;
        }

        .chatbot.active {
            display: flex;
        }

        .chatbot-header {
            background: var(--accent-teal);
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chatbot-header h4 {
            margin: 0;
            font-size: 1rem;
        }

        .chatbot-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
        }

        .chatbot-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            background: var(--bg-primary);
        }

        .chatbot-message {
            margin-bottom: 1rem;
            display: flex;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chatbot-message.user {
            justify-content: flex-end;
        }

        .chatbot-message-content {
            max-width: 80%;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            word-wrap: break-word;
        }

        .chatbot-message.bot .chatbot-message-content {
            background: #e9ecef;
            color: var(--text-primary);
        }

        .chatbot-message.user .chatbot-message-content {
            background: var(--accent-teal);
            color: white;
        }

        .chatbot-input-group {
            display: flex;
            padding: 1rem;
            border-top: 1px solid #e9ecef;
            background: white;
            gap: 0.5rem;
        }

        .chatbot-input {
            flex: 1;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 0.75rem;
            font-size: 0.9rem;
            font-family: inherit;
        }

        .chatbot-input:focus {
            outline: none;
            border-color: var(--accent-teal);
            box-shadow: 0 0 0 3px rgba(0, 166, 153, 0.1);
        }

        .chatbot-send {
            background: var(--accent-teal);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 0.75rem 1rem;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }

        .chatbot-send:hover {
            background: var(--secondary-dark-green);
        }

        :root[data-theme='dark'] .chatbot {
            background: var(--bg-card);
        }

        :root[data-theme='dark'] .chatbot-input {
            background: #081326;
            color: white;
            border-color: #5fb1ff;
        }

        :root[data-theme='dark'] .chatbot-message.bot .chatbot-message-content {
            background: #333;
            color: white;
        }

        :root[data-theme='dark'] .chatbot-input-group {
            background: var(--bg-secondary);
            border-top-color: #5fb1ff;
        }

        @media (max-width: 512px) {
            .chatbot {
                width: calc(100vw - 2rem);
                height: 400px;
                right: 1rem;
                bottom: calc(4.5rem + 1rem);
            }
        }
    </style> -->

            <!--<script>
        const chatbotToggle = document.getElementById('chatbot-toggle');
        const chatbot = document.getElementById('chatbot');
        const chatbotClose = document.getElementById('chatbot-close');
        const chatbotInput = document.getElementById('chatbot-input');
        const chatbotSend = document.getElementById('chatbot-send');
        const chatbotMessages = document.getElementById('chatbot-messages');

        chatbotToggle.addEventListener('click', () => {
            chatbot.classList.toggle('active');
            if (chatbot.classList.contains('active')) {
                chatbotInput.focus();
            }
        });

        chatbotClose.addEventListener('click', () => {
            chatbot.classList.remove('active');
        });

        function sendMessage() {
            const message = chatbotInput.value.trim();
            if (message === '') return;

            const userMessageDiv = document.createElement('div');
            userMessageDiv.className = 'chatbot-message user';
            userMessageDiv.innerHTML = `<div class="chatbot-message-content">${message}</div>`;
            chatbotMessages.appendChild(userMessageDiv);

            chatbotInput.value = '';
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;

            setTimeout(() => {
                const botMessageDiv = document.createElement('div');
                botMessageDiv.className = 'chatbot-message bot';
                const responses = [
                    'Thank you for your message. How can I assist you today?',
                    'I appreciate your inquiry. Please provide more details.',
                    'That\'s a great question! Can you tell me more?',
                    'I\'m here to help. What would you like to know about MidConnect?'
                ];
                const randomResponse = responses[Math.floor(Math.random() * responses.length)];
                botMessageDiv.innerHTML = `<div class="chatbot-message-content">${randomResponse}</div>`;
                chatbotMessages.appendChild(botMessageDiv);
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }, 500);
        }

        chatbotSend.addEventListener('click', sendMessage);
        chatbotInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });

        // Duty Area Cards Functionality - Disabled (non-clickable)
        // const dutyCards = document.querySelectorAll('.duty-area-btn');
        // dutyCards.forEach(card => {
        //     card.addEventListener('click', () => {
        //         dutyCards.forEach(c => c.classList.remove('active'));
        //         card.classList.add('active');
        //     });
        // });
    </script> -->
            <script>
                (function() {
                    if (!window.chatbase || window.chatbase("getState") !== "initialized") {
                        window.chatbase = (...arguments) => {
                            if (!window.chatbase.q) {
                                window.chatbase.q = []
                            }
                            window.chatbase.q.push(arguments)
                        };
                        window.chatbase = new Proxy(window.chatbase, {
                            get(target, prop) {
                                if (prop === "q") {
                                    return target.q
                                }
                                return (...args) => target(prop, ...args)
                            }
                        })
                    }
                    const onLoad = function() {
                        const script = document.createElement("script");
                        script.src = "https://www.chatbase.co/embed.min.js";
                        script.id = "mkSuvkG19NuJ50hkdCPlJ";
                        script.domain = "www.chatbase.co";
                        document.body.appendChild(script)
                    };
                    if (document.readyState === "complete") {
                        onLoad()
                    } else {
                        window.addEventListener("load", onLoad)
                    }
                })();
            </script>
</body>

</html>