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
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        .edit-hint, .auto-calc-hint {
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
        
        .completion-bar > div {
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
        
        .qualification-item, .training-item, .membership-item {
            margin-bottom: 0.5rem;
        }
        
        .qualification-item h6, .training-item h6, .membership-item h6 {
            color: var(--primary-blue);
            margin-bottom: 0.25rem;
        }
        
        .institution, .training-provider {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        
        .year, .training-date {
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
        
        input:checked + .slider {
            background-color: var(--accent-teal);
        }
        
        input:checked + .slider:before {
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
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        }

        .duty-area-btn.active {
            border-color: var(--secondary-green);
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3); }
            50% { box-shadow: 0 4px 16px rgba(76, 175, 80, 0.5); }
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
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
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
            box-shadow: 0 6px 14px rgba(0,0,0,0.08);
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
        .counseling-hero h1 { font-size: 1.5rem; margin-bottom: 0.35rem; }
        .counseling-hero p { opacity: 0.95; margin: 0; font-size: 0.95rem; }
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
        .status-badge.status-active { background-color: #d4edda; color: #155724; }

/* health-education-session.html styles */
.health-hero {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-dark-green));
            color: #ffffff;
            border-radius: var(--radius-lg);
            padding: 1.75rem 2rem;
            margin-bottom: 1.75rem;
        }
        .health-hero h1 { font-size: 1.5rem; margin-bottom: 0.35rem; color: #ffffff !important; text-shadow: 0 2px 8px rgba(0,0,0,0.35); }
        .health-hero p { opacity: 0.95; margin: 0; font-size: 0.95rem; color: rgba(255,255,255,0.95) !important; text-shadow: 0 1px 5px rgba(0,0,0,0.25); }
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
        .emergency-hero h1 { font-size: 1.5rem; margin-bottom: 0.35rem; }
        .emergency-hero p { opacity: 0.95; margin: 0; font-size: 0.95rem; }
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
        .emergency-tile strong { display: block; color: var(--text-primary); }
        .emergency-tile span { font-size: 0.9rem; color: var(--text-secondary); }
        .priority-critical { border-left: 5px solid #c0392b; }
        .priority-high { border-left: 5px solid #fd7e14; }
        .priority-moderate { border-left: 5px solid var(--primary-blue); }

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
                        <div class="col-8">
                            <h2 id="greeting-text">Good Morning, Madhavi!</h2>
                            <p>Ready to make a difference in your community today. You have 5 scheduled activities.</p>
                        </div>
                        <div class="col-4 text-center">
                            <div class="progress-ring">
                                <svg width="120" height="120">
                                    <circle class="background" cx="60" cy="60" r="50"></circle>
                                    <circle class="progress" cx="60" cy="60" r="50" 
                                            stroke-dasharray="314.16" 
                                            stroke-dashoffset="78.54"></circle>
                                </svg>
                                <div class="progress-text">75%</div>
                            </div>
                            <p>Daily Goals</p>
                        </div>
                    </div>
                </div>

                
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
</div>

            <!-- Log Activity Section -->
            <div id="log-activity" class="content-section" style="display: none;">
                <h2>Log New Activity</h2>
                <div class="activity-form">
                    <form action="../php/midwife/create_activity.php" method="POST" >
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
                        <button class="btn btn-primary" onclick="addScheduleItem()">
                            <i class="fas fa-plus"></i> Add Schedule Item
                        </button>
                        <button class="btn btn-info" onclick="showMyTimetable()">
                            <i class="fas fa-calendar-alt"></i> My Timetable
                        </button>
                    </div>
                </div>

                <!-- Duty Areas Selection -->
                <div class="duty-areas-container">
                    <div class="duty-areas-title">
                        <i class="fas fa-map-marker-alt"></i> Select Duty Area
                    </div>
                    <div class="duty-areas-grid">
                        <div class="duty-area-btn active" onclick="switchArea('schedule', 'uduthuththiripitiya')">
                            <i class="fas fa-home"></i>
                            <h5>Uduthuththiripitiya</h5>
                            <div class="area-count">4 appointments</div>
                        </div>
                        <div class="duty-area-btn" onclick="switchArea('schedule', 'kahabilihena')">
                            <i class="fas fa-hospital"></i>
                            <h5>Kahabilihena</h5>
                            <div class="area-count">3 appointments</div>
                        </div>
                        <div class="duty-area-btn" onclick="switchArea('schedule', 'opathella')">
                            <i class="fas fa-city"></i>
                            <h5>Opathella</h5>
                            <div class="area-count">2 appointments</div>
                        </div>
                        <div class="duty-area-btn" onclick="switchArea('schedule', 'ambalangoda')">
                            <i class="fas fa-tree"></i>
                            <h5>Ambalangoda</h5>
                            <div class="area-count">5 appointments</div>
                        </div>
                    </div>
                </div>

                <!-- Uduthuththiripitiya Area Content -->
                <div class="area-content-wrapper active" data-area="uduthuththiripitiya">
                    <div class="area-header">
                        <h4><i class="fas fa-map-marker-alt"></i> Uduthuththiripitiya Area Schedule</h4>
                        <p>Clinic Hours: 8:00 AM - 4:00 PM | Contact: +94 37 226 5432</p>
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
                            <tbody>
                                <tr>
                                    <td>09:00</td>
                                    <td>Home Visit - Mrs. K. Silva</td>
                                    <td>No. 45, Galle Road</td>
                                    <td><span class="status-badge status-active">Scheduled</span></td>
                                    <td>
                                        <button class="btn btn-success btn-sm">Complete</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>11:00</td>
                                    <td>Vaccination Session</td>
                                    <td>Clinic Center</td>
                                    <td><span class="status-badge status-active">Scheduled</span></td>
                                    <td>
                                        <button class="btn btn-success btn-sm">Complete</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>14:00</td>
                                    <td>Health Education Session</td>
                                    <td>Community Hall</td>
                                    <td><span class="status-badge status-pending">Pending</span></td>
                                    <td>
                                        <button class="btn btn-success btn-sm">Complete</button>
                                        <button class="btn btn-warning btn-sm">Reschedule</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>16:00</td>
                                    <td>Follow-up Visit - Mrs. R. Perera</td>
                                    <td>No. 12, Main Street</td>
                                    <td><span class="status-badge status-active">Scheduled</span></td>
                                    <td>
                                        <button class="btn btn-success btn-sm">Complete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                </div>

                <!-- Kahabilihena Area Content -->
                <div class="area-content-wrapper" data-area="kahabilihena">
                    <div class="area-header">
                        <h4><i class="fas fa-map-marker-alt"></i> Kahabilihena Area Schedule</h4>
                        <p>Clinic Hours: 8:30 AM - 3:30 PM | Contact: +94 37 205 6789</p>
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
                                <tbody>
                                    <tr>
                                        <td>08:30</td>
                                        <td>Clinic Opening</td>
                                        <td>Kahabilihena MOH Office</td>
                                        <td><span class="status-badge status-active">Scheduled</span></td>
                                        <td><button class="btn btn-success btn-sm">Complete</button></td>
                                    </tr>
                                    <tr>
                                        <td>10:00</td>
                                        <td>Home Visit - Mrs. A. Dissanayake</td>
                                        <td>Kahabilihena South</td>
                                        <td><span class="status-badge status-active">Scheduled</span></td>
                                        <td><button class="btn btn-success btn-sm">Complete</button></td>
                                    </tr>
                                    <tr>
                                        <td>13:00</td>
                                        <td>Vaccination Clinic</td>
                                        <td>Kahabilihena RH</td>
                                        <td><span class="status-badge status-pending">Pending</span></td>
                                        <td><button class="btn btn-success btn-sm">Complete</button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Opathella Area Content -->
                <div class="area-content-wrapper" data-area="opathella">
                    <div class="area-header">
                        <h4><i class="fas fa-map-marker-alt"></i> Opathella Area Schedule</h4>
                        <p>Clinic Hours: 8:00 AM - 5:00 PM | Contact: +94 37 222 3456</p>
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
                                <tbody>
                                    <tr>
                                        <td>09:30</td>
                                        <td>Antenatal Clinic</td>
                                        <td>Opathella PHC</td>
                                        <td><span class="status-badge status-active">Scheduled</span></td>
                                        <td><button class="btn btn-success btn-sm">Complete</button></td>
                                    </tr>
                                    <tr>
                                        <td>15:00</td>
                                        <td>Health Education Session</td>
                                        <td>City Community Center</td>
                                        <td><span class="status-badge status-active">Scheduled</span></td>
                                        <td><button class="btn btn-success btn-sm">Complete</button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Ambalangoda Area Content -->
                <div class="area-content-wrapper" data-area="ambalangoda">
                    <div class="area-header">
                        <h4><i class="fas fa-map-marker-alt"></i> Ambalangoda Area Schedule</h4>
                        <p>Clinic Hours: 7:30 AM - 4:00 PM | Contact: +94 37 267 8901</p>
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
                                <tbody>
                                    <tr>
                                        <td>07:30</td>
                                        <td>Mobile Clinic Setup</td>
                                        <td>Ambalangoda East</td>
                                        <td><span class="status-badge status-active">Scheduled</span></td>
                                        <td><button class="btn btn-success btn-sm">Complete</button></td>
                                    </tr>
                                    <tr>
                                        <td>09:00</td>
                                        <td>Home Visit - Mrs. S. Rajapaksha</td>
                                        <td>Ambalangoda Village</td>
                                        <td><span class="status-badge status-active">Scheduled</span></td>
                                        <td><button class="btn btn-success btn-sm">Complete</button></td>
                                    </tr>
                                    <tr>
                                        <td>11:30</td>
                                        <td>Postnatal Care Visit</td>
                                        <td>Ambalangoda North</td>
                                        <td><span class="status-badge status-pending">Pending</span></td>
                                        <td><button class="btn btn-success btn-sm">Complete</button></td>
                                    </tr>
                                    <tr>
                                        <td>14:00</td>
                                        <td>Triposha Distribution</td>
                                        <td>Ambalangoda DH</td>
                                        <td><span class="status-badge status-active">Scheduled</span></td>
                                        <td><button class="btn btn-success btn-sm">Complete</button></td>
                                    </tr>
                                    <tr>
                                        <td>16:00</td>
                                        <td>Follow-up Visit - Mrs. M. Bandara</td>
                                        <td>Ambalangoda South</td>
                                        <td><span class="status-badge status-active">Scheduled</span></td>
                                        <td><button class="btn btn-success btn-sm">Complete</button></td>
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
                            <div style="font-size: 1.75rem; font-weight: 700; color: var(--primary-blue);">14</div>
                            <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.35rem;">Sessions this month</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card" style="border-left: 4px solid var(--secondary-green); text-align: center; padding: 1.25rem;">
                            <div style="font-size: 1.75rem; font-weight: 700; color: var(--primary-blue);">5</div>
                            <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.35rem;">Follow-ups scheduled</div>
                        </div>
                    </div>
                </div>

                <div class="card" style="margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h4 class="card-title">Log counseling session</h4>
                    </div>
                    <div class="card-body">
                        <form id="counselingForm">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Date &amp; time</label>
                                        <input type="datetime-local" class="form-control" name="session_datetime" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Duration (minutes)</label>
                                        <input type="number" class="form-control" name="duration_mins" min="5" max="240" value="30" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Client identifier</label>
                                        <input type="text" class="form-control" name="client_ref" placeholder="e.g. initials or clinic number" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Session focus</label>
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
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Summary &amp; advice given</label>
                                <textarea class="form-control" name="notes" rows="4" placeholder="Brief notes (no unnecessary personal detail)" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Follow-up required</label>
                                <select class="form-control form-select" name="followup">
                                    <option value="no">No</option>
                                    <option value="yes">Yes — schedule</option>
                                    <option value="referral">Referral to specialist</option>
                                </select>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save session</button>
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
                                <tbody>
                                    <tr style="border-bottom: 1px solid #e9ecef;">
                                        <td style="padding: 0.75rem 1rem;">26 Mar 2026, 10:15</td>
                                        <td style="padding: 0.75rem 1rem;">Breastfeeding &amp; nutrition</td>
                                        <td style="padding: 0.75rem 1rem;">MOH Clinic</td>
                                        <td style="padding: 0.75rem 1rem;">45 min</td>
                                        <td style="padding: 0.75rem 1rem;"><span class="status-badge status-active">Scheduled</span></td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #e9ecef;">
                                        <td style="padding: 0.75rem 1rem;">24 Mar 2026, 14:00</td>
                                        <td style="padding: 0.75rem 1rem;">Antenatal care</td>
                                        <td style="padding: 0.75rem 1rem;">Home Visit</td>
                                        <td style="padding: 0.75rem 1rem;">30 min</td>
                                        <td style="padding: 0.75rem 1rem;"><span class="status-badge" style="background: #e9ecef; color: var(--text-primary);">None</span></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 0.75rem 1rem;">22 Mar 2026, 09:30</td>
                                        <td style="padding: 0.75rem 1rem;">Mental health support</td>
                                        <td style="padding: 0.75rem 1rem;">Community Center</td>
                                        <td style="padding: 0.75rem 1rem;">60 min</td>
                                        <td style="padding: 0.75rem 1rem;"><span class="status-badge" style="background: #fff3cd; color: #856404;">Referral</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
</div>

<!-- health-education-session Section -->
<div id="health-education-session" class="content-section" style="display: none;">
                <div class="health-hero">
                    <h1><i class="fas fa-chalkboard-teacher"></i> Health education sessions</h1>
                    <p>Group talks, demonstrations, and community awareness on maternal and child health topics.</p>
                </div>

                <div class="row" style="margin-bottom: 1.5rem;">
                    <div class="col-4">
                        <div class="card" style="border-left: 4px solid var(--accent-orange); text-align: center; padding: 1.1rem;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-blue);">8</div>
                            <div style="font-size: 0.7rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase;">Sessions (90 days)</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card" style="border-left: 4px solid var(--secondary-green); text-align: center; padding: 1.1rem;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-blue);">186</div>
                            <div style="font-size: 0.7rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase;">Participants reached</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card" style="border-left: 4px solid var(--primary-blue); text-align: center; padding: 1.1rem;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-blue);">4</div>
                            <div style="font-size: 0.7rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase;">Upcoming</div>
                        </div>
                    </div>
                </div>

                <div class="card" style="margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h4 class="card-title">Record health education session</h4>
                    </div>
                    <div class="card-body">
                        <form id="healthEdForm">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Session date</label>
                                        <input type="date" class="form-control" name="session_date" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Venue</label>
                                        <input type="text" class="form-control" name="venue" placeholder="e.g. MOH clinic hall, village temple" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Main topic</label>
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
                                        <label class="form-label">Approx. attendees</label>
                                        <input type="number" class="form-control" name="attendees" min="1" max="500" value="25" required>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label class="form-label">Duration (minutes)</label>
                                        <input type="number" class="form-control" name="duration_mins" min="15" max="180" value="45" required>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label class="form-label">Materials used</label>
                                        <input type="text" class="form-control" name="materials" placeholder="Flip chart, leaflets…">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Outcomes &amp; questions raised</label>
                                <textarea class="form-control" name="outcomes" rows="3" placeholder="Key messages delivered and follow-up needs"></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save session</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Recent &amp; planned sessions</h4>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div style="display: flex; align-items: flex-start; padding: 1rem; border: 1px solid #e9ecef; border-radius: var(--radius-md); border-left: 4px solid var(--secondary-green);">
                                <div style="min-width: 88px; font-weight: 600; color: var(--primary-blue);">28 Mar</div>
                                <div>
                                    <strong>Immunization schedule</strong>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary); margin-top: 0.25rem;">MOH waiting area · 32 participants · Flip chart</div>
                                    <div style="margin-top: 0.5rem;"><span class="topic-pill">Upcoming</span></div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: flex-start; padding: 1rem; border: 1px solid #e9ecef; border-radius: var(--radius-md); border-left: 4px solid var(--primary-blue);">
                                <div style="min-width: 88px; font-weight: 600; color: var(--primary-blue);">18 Mar</div>
                                <div>
                                    <strong>Pregnancy danger signs</strong>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary); margin-top: 0.25rem;">Community centre · 28 participants</div>
                                    <div style="margin-top: 0.5rem;"><span class="topic-pill">Completed</span></div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: flex-start; padding: 1rem; border: 1px solid #e9ecef; border-radius: var(--radius-md); border-left: 4px solid var(--primary-blue);">
                                <div style="min-width: 88px; font-weight: 600; color: var(--primary-blue);">05 Mar</div>
                                <div>
                                    <strong>Newborn care &amp; breastfeeding</strong>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary); margin-top: 0.25rem;">Clinic hall · 41 participants</div>
                                    <div style="margin-top: 0.5rem;"><span class="topic-pill">Completed</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
</div>

            <div id="patients" class="content-section" style="display: none;">
                <div class="d-flex justify-between align-center mb-3">
                    <h2>Maternal and Child Care</h2>
                    <input type="text" class="form-control" placeholder="Search records..." style="max-width: 300px;">
                </div>

                <!-- Duty Areas Selection -->
                <div class="duty-areas-container">
                    <div class="duty-areas-title">
                        <i class="fas fa-map-marker-alt"></i> Select Duty Area
                    </div>
                    <div class="duty-areas-grid">
                        <div class="duty-area-btn active" onclick="switchArea('patients', 'uduthuththiripitiya')">
                            <i class="fas fa-home"></i>
                            <h5>Uduthuththiripitiya</h5>
                            <div class="area-count">45 active patients</div>
                        </div>
                        <div class="duty-area-btn" onclick="switchArea('patients', 'kahabilihena')">
                            <i class="fas fa-hospital"></i>
                            <h5>Kahabilihena</h5>
                            <div class="area-count">38 active patients</div>
                        </div>
                        <div class="duty-area-btn" onclick="switchArea('patients', 'opathella')">
                            <i class="fas fa-city"></i>
                            <h5>Opathella</h5>
                            <div class="area-count">28 active patients</div>
                        </div>
                        <div class="duty-area-btn" onclick="switchArea('patients', 'ambalangoda')">
                            <i class="fas fa-tree"></i>
                            <h5>Ambalangoda</h5>
                            <div class="area-count">52 active patients</div>
                        </div>
                    </div>
                </div>

                <!-- Uduthuththiripitiya Area Content -->
                <div class="area-content-wrapper active" data-area="uduthuththiripitiya">
                    <div class="area-header">
                        <h4><i class="fas fa-map-marker-alt"></i> Uduthuththiripitiya Area - Maternal & Child Care</h4>
                        <p>Coverage: 15 villages | Pregnant: 12, Lactating: 18, Children: 45 | Clinic: Uduthuththiripitiya CHC</p>
                    </div>

                <!-- Tabs for Mothers and Children -->
                <div class="tab-container">
                    <ul class="nav nav-tabs" style="width: 100%;">
                        <li class="nav-item" style="flex: 1; margin-right: 0;">
                            <a class="nav-link active" href="#" onclick="switchCareTab('mothers')" style="text-align: center;">
                                <i class="fas fa-female"></i> Mothers
                            </a>
                        </li>
                        <li class="nav-item" style="flex: 1; margin-right: 0;">
                            <a class="nav-link" href="#" onclick="switchCareTab('children')" style="text-align: center;">
                                <i class="fas fa-child"></i> Children
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Mothers Tab -->
                <div id="mothers-tab" class="tab-content">
                    <!-- Sub-tabs for different mother categories -->
                    <div class="tab-container" style="margin-top: 1rem;">
                        <ul class="nav nav-tabs" style="width: 100%;">
                            <li class="nav-item" style="flex: 1; margin-right: 0;">
                                <a class="nav-link active" href="#" onclick="switchMotherTab('pregnant')" style="text-align: center;">
                                    <i class="fas fa-baby"></i> Pregnant Mothers
                                </a>
                            </li>
                            <li class="nav-item" style="flex: 1; margin-right: 0;">
                                <a class="nav-link" href="#" onclick="switchMotherTab('lactating')" style="text-align: center;">
                                    <i class="fas fa-child"></i> Lactating Mothers
                                </a>
                            </li>
                            <li class="nav-item" style="flex: 1; margin-right: 0;">
                                <a class="nav-link" href="#" onclick="switchMotherTab('postnatal')" style="text-align: center;">
                                    <i class="fas fa-procedures"></i> Postnatal Mothers
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Pregnant Mothers Sub-tab -->
                    <div id="pregnant-mothers" class="tab-content">
                        <div class="card">
                            <div class="card-header d-flex justify-between align-center">
                                <h5 class="card-title" style="margin: 0;">Pregnant Mothers</h5>
                                <button class="btn btn-primary btn-sm" onclick="addPregnantMother()">
                                    <i class="fas fa-plus"></i> Add Pregnant Mother
                                </button>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
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
                                    <tbody>
                                        <tr>
                                            <td>Mrs. Kumari Silva</td>
                                            <td>28</td>
                                            <td>32 weeks</td>
                                            <td>2026-02-03</td>
                                            <td>2026-02-10</td>
                                            <td><span class="status-badge status-success">Low Risk</span></td>
                                            <td>
                                                <button class="btn btn-info btn-sm">View</button>
                                                <button class="btn btn-warning btn-sm" onclick="updateMother('pregnant', 1, 'Mrs. Kumari Silva', 28, '32', '2026-02-03', '2026-02-10', 'Low Risk')">Update</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Mrs. Priyanka Perera</td>
                                            <td>26</td>
                                            <td>24 weeks</td>
                                            <td>2026-01-30</td>
                                            <td>2026-02-13</td>
                                            <td><span class="status-badge status-success">Low Risk</span></td>
                                            <td>
                                                <button class="btn btn-info btn-sm">View</button>
                                                <button class="btn btn-warning btn-sm" onclick="updateMother('pregnant', 2, 'Mrs. Priyanka Perera', 26, '24', '2026-01-30', '2026-02-13', 'Low Risk')">Update</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Mrs. Sanduni Wickramasinghe</td>
                                            <td>35</td>
                                            <td>38 weeks</td>
                                            <td>2026-02-01</td>
                                            <td>2026-02-08</td>
                                            <td><span class="status-badge status-danger">High Risk</span></td>
                                            <td>
                                                <button class="btn btn-info btn-sm">View</button>
                                                <button class="btn btn-warning btn-sm" onclick="updateMother('pregnant', 3, 'Mrs. Sanduni Wickramasinghe', 35, '38', '2026-02-01', '2026-02-08', 'High Risk')">Update</button>
                                                <button class="btn btn-danger btn-sm">Urgent</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Mrs. Chamika Rajapaksa</td>
                                            <td>29</td>
                                            <td>16 weeks</td>
                                            <td>2026-01-28</td>
                                            <td>2026-02-25</td>
                                            <td><span class="status-badge status-success">Low Risk</span></td>
                                            <td>
                                                <button class="btn btn-info btn-sm">View</button>
                                                <button class="btn btn-warning btn-sm" onclick="updateMother('pregnant', 4, 'Mrs. Chamika Rajapaksa', 29, '16', '2026-01-28', '2026-02-25', 'Low Risk')">Update</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Lactating Mothers Sub-tab -->
                    <div id="lactating-mothers" class="tab-content hidden">
                        <div class="card">
                            <div class="card-header d-flex justify-between align-center">
                                <h5 class="card-title" style="margin: 0;">Lactating Mothers</h5>
                                <button class="btn btn-primary btn-sm" onclick="addLactatingMother()">
                                    <i class="fas fa-plus"></i> Add Lactating Mother
                                </button>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Mother's Name</th>
                                            <th>Age</th>
                                            <th>Baby's Age</th>
                                            <th>Breastfeeding Status</th>
                                            <th>Last Visit</th>
                                            <th>Support Level</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Mrs. Anura Fernando</td>
                                            <td>32</td>
                                            <td>2 weeks</td>
                                            <td>Exclusive breastfeeding</td>
                                            <td>2026-01-28</td>
                                            <td><span class="status-badge status-success">Good Support</span></td>
                                            <td>
                                                <button class="btn btn-info btn-sm">View</button>
                                                <button class="btn btn-warning btn-sm" onclick="updateMother('lactating', 5, 'Mrs. Anura Fernando', 32, '2 weeks', '2026-01-28', 'Exclusive breastfeeding', 'Good Support')">Update</button>
                                                <button class="btn btn-success btn-sm">Support</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Mrs. Nishani Gamage</td>
                                            <td>27</td>
                                            <td>6 weeks</td>
                                            <td>Mixed feeding</td>
                                            <td>2026-02-02</td>
                                            <td><span class="status-badge status-warning">Needs Support</span></td>
                                            <td>
                                                <button class="btn btn-info btn-sm">View</button>
                                                <button class="btn btn-warning btn-sm" onclick="updateMother('lactating', 6, 'Mrs. Nishani Gamage', 27, '6 weeks', '2026-02-02', 'Mixed feeding', 'Needs Support')">Update</button>
                                                <button class="btn btn-primary btn-sm">Counsel</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Mrs. Shalika Mendis</td>
                                            <td>30</td>
                                            <td>4 weeks</td>
                                            <td>Exclusive breastfeeding</td>
                                            <td>2026-01-31</td>
                                            <td><span class="status-badge status-success">Good Support</span></td>
                                            <td>
                                                <button class="btn btn-info btn-sm">View</button>
                                                <button class="btn btn-warning btn-sm" onclick="updateMother('lactating', 7, 'Mrs. Shalika Mendis', 30, '4 weeks', '2026-01-31', 'Exclusive breastfeeding', 'Good Support')">Update</button>
                                                <button class="btn btn-success btn-sm">Support</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Postnatal Mothers Sub-tab -->
                    <div id="postnatal-mothers" class="tab-content hidden">
                        <div class="card">
                            <div class="card-header d-flex justify-between align-center">
                                <h5 class="card-title" style="margin: 0;">Postnatal Mothers</h5>
                                <button class="btn btn-primary btn-sm" onclick="addPostnatalMother()">
                                    <i class="fas fa-plus"></i> Add Postnatal Mother
                                </button>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Mother's Name</th>
                                            <th>Age</th>
                                            <th>Delivery Date</th>
                                            <th>Delivery Type</th>
                                            <th>Recovery Status</th>
                                            <th>Last Visit</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Mrs. Dilani Perera</td>
                                            <td>31</td>
                                            <td>2026-01-15</td>
                                            <td>Normal Delivery</td>
                                            <td>Good Recovery</td>
                                            <td>2026-02-01</td>
                                            <td>
                                                <button class="btn btn-info btn-sm">View</button>
                                                <button class="btn btn-warning btn-sm" onclick="updateMother('postnatal', 8, 'Mrs. Dilani Perera', 31, '2026-01-15', '2026-02-01', 'Normal Delivery', 'Good Recovery')">Update</button>
                                                <button class="btn btn-success btn-sm">Follow-up</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Mrs. Malika Jayasinghe</td>
                                            <td>33</td>
                                            <td>2026-01-08</td>
                                            <td>C-Section</td>
                                            <td>Slow Recovery</td>
                                            <td>2026-02-03</td>
                                            <td>
                                                <button class="btn btn-info btn-sm">View</button>
                                                <button class="btn btn-warning btn-sm" onclick="updateMother('postnatal', 9, 'Mrs. Malika Jayasinghe', 33, '2026-01-08', '2026-02-03', 'C-Section', 'Slow Recovery')">Update</button>
                                                <button class="btn btn-primary btn-sm">Monitor</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Mrs. Chathurika Silva</td>
                                            <td>28</td>
                                            <td>2025-12-20</td>
                                            <td>Normal Delivery</td>
                                            <td>Excellent Recovery</td>
                                            <td>2026-01-25</td>
                                            <td>
                                                <button class="btn btn-info btn-sm">View</button>
                                                <button class="btn btn-warning btn-sm" onclick="updateMother('postnatal', 10, 'Mrs. Chathurika Silva', 28, '2025-12-20', '2026-01-25', 'Normal Delivery', 'Excellent Recovery')">Update</button>
                                                <button class="btn btn-success btn-sm">Complete</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Mrs. Roshani Fernando</td>
                                            <td>25</td>
                                            <td>2026-01-28</td>
                                            <td>Normal Delivery</td>
                                            <td>Good Recovery</td>
                                            <td>2026-02-04</td>
                                            <td>
                                                <button class="btn btn-info btn-sm">View</button>
                                                <button class="btn btn-warning btn-sm" onclick="updateMother('postnatal', 11, 'Mrs. Roshani Fernando', 25, '2026-01-28', '2026-02-04', 'Normal Delivery', 'Good Recovery')">Update</button>
                                                <button class="btn btn-success btn-sm">Follow-up</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Children Tab -->
                <div id="children-tab" class="tab-content hidden">
                    <!-- Sub-tabs for different children categories -->
                    <div class="tab-container" style="margin-top: 1rem;">
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" href="#" onclick="switchChildrenTab('newborns')">
                                    <i class="fas fa-baby"></i> Newborns
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" onclick="switchChildrenTab('young')">
                                    <i class="fas fa-baby-carriage"></i> Young Children
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" onclick="switchChildrenTab('childs')">
                                    <i class="fas fa-child"></i> Childs
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Newborns Sub-tab -->
                    <div id="newborns-children" class="tab-content">
                        <div class="card">
                            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                <h5 class="card-title" style="margin: 0;">Newborns</h5>
                                <button class="btn btn-primary btn-sm" onclick="addNewborn()">
                                    <i class="fas fa-plus"></i> Add Newborn
                                </button>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
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
                                    <tbody>
                                        <tr>
                                            <td>Baby Fernando</td>
                                            <td>Mrs. Anura Fernando</td>
                                            <td>2026-01-22</td>
                                            <td>3.2 kg</td>
                                            <td>2026-02-01</td>
                                            <td><span class="status-badge status-success">Healthy</span></td>
                                            <td>
                                                <button class="btn btn-info btn-sm">View</button>
                                                <button class="btn btn-warning btn-sm">Update</button>
                                                <button class="btn btn-success btn-sm">Vaccinate</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Baby Rajapaksa</td>
                                            <td>Mrs. Nishani Rajapaksa</td>
                                            <td>2026-01-15</td>
                                            <td>2.8 kg</td>
                                            <td>2026-01-29</td>
                                            <td><span class="status-badge status-warning">Monitoring</span></td>
                                            <td>
                                                <button class="btn btn-info btn-sm">View</button>
                                                <button class="btn btn-warning btn-sm">Update</button>
                                                <button class="btn btn-primary btn-sm">Follow-up</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Baby Gamage</td>
                                            <td>Mrs. Shalika Gamage</td>
                                            <td>2025-12-28</td>
                                            <td>3.5 kg</td>
                                            <td>2026-01-25</td>
                                            <td><span class="status-badge status-success">Healthy</span></td>
                                            <td>
                                                <button class="btn btn-info btn-sm">View</button>
                                                <button class="btn btn-warning btn-sm">Update</button>
                                                <button class="btn btn-success btn-sm">Vaccinate</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Young Children Sub-tab -->
                    <div id="young-children" class="tab-content hidden">
                        <div class="card">
                            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                <h5 class="card-title" style="margin: 0;">Young Children</h5>
                                <button class="btn btn-primary btn-sm" onclick="addYoungChild()">
                                    <i class="fas fa-plus"></i> Add Young Child
                                </button>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Child's Name</th>
                                            <th>Age</th>
                                            <th>Parent/Guardian</th>
                                            <th>Weight</th>
                                            <th>Height</th>
                                            <th>Last Check-up</th>
                                            <th>Development Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Sahan Perera</td>
                                            <td>2 years</td>
                                            <td>Mrs. Priyanka Perera</td>
                                            <td>12.5 kg</td>
                                            <td>85 cm</td>
                                            <td>2026-02-03</td>
                                            <td><span class="status-badge status-success">Normal</span></td>
                                            <td>
                                                <button class="btn btn-info btn-sm">View</button>
                                                <button class="btn btn-warning btn-sm">Update</button>
                                                <button class="btn btn-success btn-sm">Vaccinate</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Nimali Silva</td>
                                            <td>3 years</td>
                                            <td>Mrs. Kumari Silva</td>
                                            <td>14.2 kg</td>
                                            <td>95 cm</td>
                                            <td>2026-01-28</td>
                                            <td><span class="status-badge status-success">Normal</span></td>
                                            <td>
                                                <button class="btn btn-info btn-sm">View</button>
                                                <button class="btn btn-warning btn-sm">Update</button>
                                                <button class="btn btn-info btn-sm">Assessment</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Kasun Mendis</td>
                                            <td>18 months</td>
                                            <td>Mrs. Chamani Mendis</td>
                                            <td>10.8 kg</td>
                                            <td>78 cm</td>
                                            <td>2026-02-01</td>
                                            <td><span class="status-badge status-warning">Delayed</span></td>
                                            <td>
                                                <button class="btn btn-info btn-sm">View</button>
                                                <button class="btn btn-warning btn-sm">Update</button>
                                                <button class="btn btn-primary btn-sm">Therapy</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Childs Sub-tab -->
                    <div id="childs-children" class="tab-content hidden">
                        <div class="card">
                            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                <h5 class="card-title" style="margin: 0;">Childs</h5>
                                <button class="btn btn-primary btn-sm" onclick="addChild()">
                                    <i class="fas fa-plus"></i> Add Child
                                </button>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Child's Name</th>
                                            <th>Age</th>
                                            <th>Parent/Guardian</th>
                                            <th>School</th>
                                            <th>Last Health Check</th>
                                            <th>Health Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Amal Wickramasinghe</td>
                                            <td>5 years</td>
                                            <td>Mrs. Sanduni Wickramasinghe</td>
                                            <td>Sunshine Pre-School</td>
                                            <td>2026-01-25</td>
                                            <td><span class="status-badge status-success">Healthy</span></td>
                                            <td>
                                                <button class="btn btn-info btn-sm">View</button>
                                                <button class="btn btn-warning btn-sm">Update</button>
                                                <button class="btn btn-success btn-sm">Check-up</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Tharindu Fernando</td>
                                            <td>6 years</td>
                                            <td>Mrs. Anura Fernando</td>
                                            <td>Little Stars School</td>
                                            <td>2026-02-02</td>
                                            <td><span class="status-badge status-success">Healthy</span></td>
                                            <td>
                                                <button class="btn btn-info btn-sm">View</button>
                                                <button class="btn btn-warning btn-sm">Update</button>
                                                <button class="btn btn-info btn-sm">Dental</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Sachini Gamage</td>
                                            <td>4 years</td>
                                            <td>Mrs. Shalika Gamage</td>
                                            <td>Rainbow Kindergarten</td>
                                            <td>2026-01-30</td>
                                            <td><span class="status-badge status-warning">Vision Issue</span></td>
                                            <td>
                                                <button class="btn btn-info btn-sm">View</button>
                                                <button class="btn btn-warning btn-sm">Update</button>
                                                <button class="btn btn-danger btn-sm">Referral</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                </div>

                <!-- Kahabilihena Area Content -->
                <div class="area-content-wrapper" data-area="kahabilihena">
                    <div class="area-header">
                        <h4><i class="fas fa-map-marker-alt"></i> Kahabilihena Area - Maternal & Child Care</h4>
                        <p>Coverage: 12 villages | Pregnant: 10, Lactating: 15, Children: 38 | Clinic: Kahabilihena RH</p>
                    </div>

                    <!-- Tabs for Mothers and Children -->
                    <div class="tab-container">
                        <ul class="nav nav-tabs" style="width: 100%;">
                            <li class="nav-item" style="flex: 1; margin-right: 0;">
                                <a class="nav-link active" href="#" onclick="switchCareTab('mothers')" style="text-align: center;">
                                    <i class="fas fa-female"></i> Mothers
                                </a>
                            </li>
                            <li class="nav-item" style="flex: 1; margin-right: 0;">
                                <a class="nav-link" href="#" onclick="switchCareTab('children')" style="text-align: center;">
                                    <i class="fas fa-child"></i> Children
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Mothers Tab -->
                    <div id="mothers-tab" class="tab-content">
                        <!-- Sub-tabs for different mother categories -->
                        <div class="tab-container" style="margin-top: 1rem;">
                            <ul class="nav nav-tabs" style="width: 100%;">
                                <li class="nav-item" style="flex: 1; margin-right: 0;">
                                    <a class="nav-link active" href="#" onclick="switchMotherTab('pregnant')" style="text-align: center;">
                                        <i class="fas fa-baby"></i> Pregnant Mothers
                                    </a>
                                </li>
                                <li class="nav-item" style="flex: 1; margin-right: 0;">
                                    <a class="nav-link" href="#" onclick="switchMotherTab('lactating')" style="text-align: center;">
                                        <i class="fas fa-child"></i> Lactating Mothers
                                    </a>
                                </li>
                                <li class="nav-item" style="flex: 1; margin-right: 0;">
                                    <a class="nav-link" href="#" onclick="switchMotherTab('postnatal')" style="text-align: center;">
                                        <i class="fas fa-procedures"></i> Postnatal Mothers
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Pregnant Mothers Sub-tab -->
                        <div id="pregnant-mothers" class="tab-content">
                            <div class="card">
                                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                    <h5 class="card-title" style="margin: 0;">Pregnant Mothers</h5>
                                    <button class="btn btn-primary btn-sm" onclick="addPregnantMother()">
                                        <i class="fas fa-plus"></i> Add Pregnant Mother
                                    </button>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
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
                                        <tbody>
                                            <tr>
                                                <td>Mrs. Nishani Rajapaksha</td>
                                                <td>27</td>
                                                <td>28 weeks</td>
                                                <td>2026-02-08</td>
                                                <td>2026-02-15</td>
                                                <td><span class="status-badge status-success">Low Risk</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-warning btn-sm">Update</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Mrs. Ayesha Dissanayake</td>
                                                <td>25</td>
                                                <td>20 weeks</td>
                                                <td>2026-02-06</td>
                                                <td>2026-02-20</td>
                                                <td><span class="status-badge status-success">Low Risk</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-warning btn-sm">Update</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Lactating Mothers Sub-tab -->
                        <div id="lactating-mothers" class="tab-content hidden">
                            <div class="card">
                                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                    <h5 class="card-title" style="margin: 0;">Lactating Mothers</h5>
                                    <button class="btn btn-primary btn-sm" onclick="addLactatingMother()">
                                        <i class="fas fa-plus"></i> Add Lactating Mother
                                    </button>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Mother's Name</th>
                                                <th>Age</th>
                                                <th>Baby's Age</th>
                                                <th>Last Visit</th>
                                                <th>Health Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Mrs. Darshika Perera</td>
                                                <td>29</td>
                                                <td>3 months</td>
                                                <td>2026-02-10</td>
                                                <td><span class="status-badge status-success">Healthy</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-warning btn-sm">Update</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Postnatal Mothers Sub-tab -->
                        <div id="postnatal-mothers" class="tab-content hidden">
                            <div class="card">
                                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                    <h5 class="card-title" style="margin: 0;">Postnatal Mothers</h5>
                                    <button class="btn btn-primary btn-sm" onclick="addPostnatalMother()">
                                        <i class="fas fa-plus"></i> Add Postnatal Mother
                                    </button>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Mother's Name</th>
                                                <th>Age</th>
                                                <th>Delivery Date</th>
                                                <th>Delivery Type</th>
                                                <th>Recovery Status</th>
                                                <th>Next Check-up</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Mrs. Dilini Wickramasinghe</td>
                                                <td>26</td>
                                                <td>2026-02-01</td>
                                                <td>Normal Delivery</td>
                                                <td>Good Recovery</td>
                                                <td>2026-02-08</td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-success btn-sm">Follow-up</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Children Tab -->
                    <div id="children-tab" class="tab-content hidden">
                        <!-- Sub-tabs for different children categories -->
                        <div class="tab-container" style="margin-top: 1rem;">
                            <ul class="nav nav-tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#" onclick="switchChildrenTab('newborns')">
                                        <i class="fas fa-baby"></i> Newborns
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" onclick="switchChildrenTab('young')">
                                        <i class="fas fa-baby-carriage"></i> Young Children
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" onclick="switchChildrenTab('childs')">
                                        <i class="fas fa-child"></i> Childs
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Newborns Sub-tab -->
                        <div id="newborns-children" class="tab-content">
                            <div class="card">
                                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                    <h5 class="card-title" style="margin: 0;">Newborns</h5>
                                    <button class="btn btn-primary btn-sm" onclick="addNewborn()">
                                        <i class="fas fa-plus"></i> Add Newborn
                                    </button>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
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
                                        <tbody>
                                            <tr>
                                                <td>Baby Perera</td>
                                                <td>Mrs. Darshika Perera</td>
                                                <td>2025-11-15</td>
                                                <td>3.1 kg</td>
                                                <td>2026-02-10</td>
                                                <td><span class="status-badge status-success">Healthy</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-success btn-sm">Vaccinate</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Young Children Sub-tab -->
                        <div id="young-children" class="tab-content hidden">
                            <div class="card">
                                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                    <h5 class="card-title" style="margin: 0;">Young Children</h5>
                                    <button class="btn btn-primary btn-sm" onclick="addYoungChild()">
                                        <i class="fas fa-plus"></i> Add Young Child
                                    </button>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Child's Name</th>
                                                <th>Age</th>
                                                <th>Mother's Name</th>
                                                <th>Last Check-up</th>
                                                <th>Weight</th>
                                                <th>Health Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Sanduni Rajapaksha</td>
                                                <td>2 years</td>
                                                <td>Mrs. Nishani Rajapaksha</td>
                                                <td>2026-02-05</td>
                                                <td>12.5 kg</td>
                                                <td><span class="status-badge status-success">Healthy</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-success btn-sm">Check-up</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Childs Sub-tab -->
                        <div id="childs-children" class="tab-content hidden">
                            <div class="card">
                                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                    <h5 class="card-title" style="margin: 0;">Childs</h5>
                                    <button class="btn btn-primary btn-sm" onclick="addChild()">
                                        <i class="fas fa-plus"></i> Add Child
                                    </button>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Child's Name</th>
                                                <th>Age</th>
                                                <th>Mother's Name</th>
                                                <th>School</th>
                                                <th>Last Visit</th>
                                                <th>Health Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Kavindu Dissanayake</td>
                                                <td>5 years</td>
                                                <td>Mrs. Ayesha Dissanayake</td>
                                                <td>Sunshine School</td>
                                                <td>2026-02-03</td>
                                                <td><span class="status-badge status-success">Healthy</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-warning btn-sm">Update</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Opathella Area Content -->
                <div class="area-content-wrapper" data-area="opathella">
                    <div class="area-header">
                        <h4><i class="fas fa-map-marker-alt"></i> Opathella Area - Maternal & Child Care</h4>
                        <p>Coverage: 8 urban wards | Pregnant: 8, Lactating: 12, Children: 28 | Clinic: Opathella PHC</p>
                    </div>

                    <!-- Tabs for Mothers and Children -->
                    <div class="tab-container">
                        <ul class="nav nav-tabs" style="width: 100%;">
                            <li class="nav-item" style="flex: 1; margin-right: 0;">
                                <a class="nav-link active" href="#" onclick="switchCareTab('mothers')" style="text-align: center;">
                                    <i class="fas fa-female"></i> Mothers
                                </a>
                            </li>
                            <li class="nav-item" style="flex: 1; margin-right: 0;">
                                <a class="nav-link" href="#" onclick="switchCareTab('children')" style="text-align: center;">
                                    <i class="fas fa-child"></i> Children
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Mothers Tab -->
                    <div id="mothers-tab" class="tab-content">
                        <!-- Sub-tabs for different mother categories -->
                        <div class="tab-container" style="margin-top: 1rem;">
                            <ul class="nav nav-tabs" style="width: 100%;">
                                <li class="nav-item" style="flex: 1; margin-right: 0;">
                                    <a class="nav-link active" href="#" onclick="switchMotherTab('pregnant')" style="text-align: center;">
                                        <i class="fas fa-baby"></i> Pregnant Mothers
                                    </a>
                                </li>
                                <li class="nav-item" style="flex: 1; margin-right: 0;">
                                    <a class="nav-link" href="#" onclick="switchMotherTab('lactating')" style="text-align: center;">
                                        <i class="fas fa-child"></i> Lactating Mothers
                                    </a>
                                </li>
                                <li class="nav-item" style="flex: 1; margin-right: 0;">
                                    <a class="nav-link" href="#" onclick="switchMotherTab('postnatal')" style="text-align: center;">
                                        <i class="fas fa-procedures"></i> Postnatal Mothers
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Pregnant Mothers Sub-tab -->
                        <div id="pregnant-mothers" class="tab-content">
                            <div class="card">
                                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                    <h5 class="card-title" style="margin: 0;">Pregnant Mothers</h5>
                                    <button class="btn btn-primary btn-sm" onclick="addPregnantMother()">
                                        <i class="fas fa-plus"></i> Add Pregnant Mother
                                    </button>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
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
                                        <tbody>
                                            <tr>
                                                <td>Mrs. Chamari Silva</td>
                                                <td>24</td>
                                                <td>20 weeks</td>
                                                <td>2026-02-09</td>
                                                <td>2026-02-16</td>
                                                <td><span class="status-badge status-success">Low Risk</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-warning btn-sm">Update</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Mrs. Tharanga Jayawardena</td>
                                                <td>30</td>
                                                <td>32 weeks</td>
                                                <td>2026-02-10</td>
                                                <td>2026-02-17</td>
                                                <td><span class="status-badge status-success">Low Risk</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-warning btn-sm">Update</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Lactating Mothers Sub-tab -->
                        <div id="lactating-mothers" class="tab-content hidden">
                            <div class="card">
                                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                    <h5 class="card-title" style="margin: 0;">Lactating Mothers</h5>
                                    <button class="btn btn-primary btn-sm" onclick="addLactatingMother()">
                                        <i class="fas fa-plus"></i> Add Lactating Mother
                                    </button>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Mother's Name</th>
                                                <th>Age</th>
                                                <th>Baby's Age</th>
                                                <th>Last Visit</th>
                                                <th>Health Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Mrs. Kumari Jayasinghe</td>
                                                <td>28</td>
                                                <td>2 months</td>
                                                <td>2026-02-08</td>
                                                <td><span class="status-badge status-success">Healthy</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-warning btn-sm">Update</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Postnatal Mothers Sub-tab -->
                        <div id="postnatal-mothers" class="tab-content hidden">
                            <div class="card">
                                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                    <h5 class="card-title" style="margin: 0;">Postnatal Mothers</h5>
                                    <button class="btn btn-primary btn-sm" onclick="addPostnatalMother()">
                                        <i class="fas fa-plus"></i> Add Postnatal Mother
                                    </button>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Mother's Name</th>
                                                <th>Age</th>
                                                <th>Delivery Date</th>
                                                <th>Delivery Type</th>
                                                <th>Recovery Status</th>
                                                <th>Next Check-up</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Mrs. Malini Wickramasinghe</td>
                                                <td>31</td>
                                                <td>2026-02-03</td>
                                                <td>C-Section</td>
                                                <td>Good Recovery</td>
                                                <td>2026-02-14</td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-success btn-sm">Follow-up</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Children Tab -->
                    <div id="children-tab" class="tab-content hidden">
                        <!-- Sub-tabs for different children categories -->
                        <div class="tab-container" style="margin-top: 1rem;">
                            <ul class="nav nav-tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#" onclick="switchChildrenTab('newborns')">
                                        <i class="fas fa-baby"></i> Newborns
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" onclick="switchChildrenTab('young')">
                                        <i class="fas fa-baby-carriage"></i> Young Children
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" onclick="switchChildrenTab('childs')">
                                        <i class="fas fa-child"></i> Childs
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Newborns Sub-tab -->
                        <div id="newborns-children" class="tab-content">
                            <div class="card">
                                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                    <h5 class="card-title" style="margin: 0;">Newborns</h5>
                                    <button class="btn btn-primary btn-sm" onclick="addNewborn()">
                                        <i class="fas fa-plus"></i> Add Newborn
                                    </button>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
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
                                        <tbody>
                                            <tr>
                                                <td>Baby Jayasinghe</td>
                                                <td>Mrs. Kumari Jayasinghe</td>
                                                <td>2025-12-10</td>
                                                <td>3.3 kg</td>
                                                <td>2026-02-08</td>
                                                <td><span class="status-badge status-success">Healthy</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-success btn-sm">Vaccinate</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Young Children Sub-tab -->
                        <div id="young-children" class="tab-content hidden">
                            <div class="card">
                                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                    <h5 class="card-title" style="margin: 0;">Young Children</h5>
                                    <button class="btn btn-primary btn-sm" onclick="addYoungChild()">
                                        <i class="fas fa-plus"></i> Add Young Child
                                    </button>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Child's Name</th>
                                                <th>Age</th>
                                                <th>Mother's Name</th>
                                                <th>Last Check-up</th>
                                                <th>Weight</th>
                                                <th>Health Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Thisara Silva</td>
                                                <td>18 months</td>
                                                <td>Mrs. Chamari Silva</td>
                                                <td>2026-02-07</td>
                                                <td>11.2 kg</td>
                                                <td><span class="status-badge status-success">Healthy</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-success btn-sm">Check-up</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Childs Sub-tab -->
                        <div id="childs-children" class="tab-content hidden">
                            <div class="card">
                                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                    <h5 class="card-title" style="margin: 0;">Childs</h5>
                                    <button class="btn btn-primary btn-sm" onclick="addChild()">
                                        <i class="fas fa-plus"></i> Add Child
                                    </button>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Child's Name</th>
                                                <th>Age</th>
                                                <th>Mother's Name</th>
                                                <th>School</th>
                                                <th>Last Visit</th>
                                                <th>Health Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Dineth Jayawardena</td>
                                                <td>6 years</td>
                                                <td>Mrs. Tharanga Jayawardena</td>
                                                <td>Central School</td>
                                                <td>2026-02-05</td>
                                                <td><span class="status-badge status-success">Healthy</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-warning btn-sm">Update</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ambalangoda Area Content -->
                <div class="area-content-wrapper" data-area="ambalangoda">
                    <div class="area-header">
                        <h4><i class="fas fa-map-marker-alt"></i> Ambalangoda Area - Maternal & Child Care</h4>
                        <p>Coverage: 18 villages | Pregnant: 15, Lactating: 20, Children: 52 | Clinic: Ambalangoda DH</p>
                    </div>

                    <!-- Tabs for Mothers and Children -->
                    <div class="tab-container">
                        <ul class="nav nav-tabs" style="width: 100%;">
                            <li class="nav-item" style="flex: 1; margin-right: 0;">
                                <a class="nav-link active" href="#" onclick="switchCareTab('mothers')" style="text-align: center;">
                                    <i class="fas fa-female"></i> Mothers
                                </a>
                            </li>
                            <li class="nav-item" style="flex: 1; margin-right: 0;">
                                <a class="nav-link" href="#" onclick="switchCareTab('children')" style="text-align: center;">
                                    <i class="fas fa-child"></i> Children
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Mothers Tab -->
                    <div id="mothers-tab" class="tab-content">
                        <!-- Sub-tabs for different mother categories -->
                        <div class="tab-container" style="margin-top: 1rem;">
                            <ul class="nav nav-tabs" style="width: 100%;">
                                <li class="nav-item" style="flex: 1; margin-right: 0;">
                                    <a class="nav-link active" href="#" onclick="switchMotherTab('pregnant')" style="text-align: center;">
                                        <i class="fas fa-baby"></i> Pregnant Mothers
                                    </a>
                                </li>
                                <li class="nav-item" style="flex: 1; margin-right: 0;">
                                    <a class="nav-link" href="#" onclick="switchMotherTab('lactating')" style="text-align: center;">
                                        <i class="fas fa-child"></i> Lactating Mothers
                                    </a>
                                </li>
                                <li class="nav-item" style="flex: 1; margin-right: 0;">
                                    <a class="nav-link" href="#" onclick="switchMotherTab('postnatal')" style="text-align: center;">
                                        <i class="fas fa-procedures"></i> Postnatal Mothers
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Pregnant Mothers Sub-tab -->
                        <div id="pregnant-mothers" class="tab-content">
                            <div class="card">
                                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                    <h5 class="card-title" style="margin: 0;">Pregnant Mothers</h5>
                                    <button class="btn btn-primary btn-sm" onclick="addPregnantMother()">
                                        <i class="fas fa-plus"></i> Add Pregnant Mother
                                    </button>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
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
                                        <tbody>
                                            <tr>
                                                <td>Mrs. Sandya Fernando</td>
                                                <td>26</td>
                                                <td>36 weeks</td>
                                                <td>2026-02-11</td>
                                                <td>2026-02-14</td>
                                                <td><span class="status-badge status-warning">High Risk</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-warning btn-sm">Update</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Mrs. Nimalika Silva</td>
                                                <td>26</td>
                                                <td>32 weeks</td>
                                                <td>2026-02-09</td>
                                                <td>2026-02-16</td>
                                                <td><span class="status-badge status-success">Low Risk</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-warning btn-sm">Update</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Lactating Mothers Sub-tab -->
                        <div id="lactating-mothers" class="tab-content hidden">
                            <div class="card">
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Mother's Name</th>
                                                <th>Age</th>
                                                <th>Baby's Age</th>
                                                <th>Last Visit</th>
                                                <th>Health Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Mrs. Ruvini Bandara</td>
                                                <td>28</td>
                                                <td>6 months</td>
                                                <td>2026-02-10</td>
                                                <td><span class="status-badge status-success">Healthy</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-warning btn-sm">Update</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Mrs. Chamari Rajapaksha</td>
                                                <td>29</td>
                                                <td>4 months</td>
                                                <td>2026-02-11</td>
                                                <td><span class="status-badge status-success">Healthy</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-warning btn-sm">Update</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Lactating Mothers Sub-tab -->
                        <div id="lactating-mothers" class="tab-content hidden">
                            <div class="card">
                                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                    <h5 class="card-title" style="margin: 0;">Lactating Mothers</h5>
                                    <button class="btn btn-primary btn-sm" onclick="addLactatingMother()">
                                        <i class="fas fa-plus"></i> Add Lactating Mother
                                    </button>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Mother's Name</th>
                                                <th>Age</th>
                                                <th>Baby's Age</th>
                                                <th>Last Visit</th>
                                                <th>Health Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Mrs. Chamari Rajapaksha</td>
                                                <td>29</td>
                                                <td>4 months</td>
                                                <td>2026-02-11</td>
                                                <td><span class="status-badge status-success">Healthy</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-warning btn-sm">Update</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Postnatal Mothers Sub-tab -->
                        <div id="postnatal-mothers" class="tab-content hidden">
                            <div class="card">
                                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                    <h5 class="card-title" style="margin: 0;">Postnatal Mothers</h5>
                                    <button class="btn btn-primary btn-sm" onclick="addPostnatalMother()">
                                        <i class="fas fa-plus"></i> Add Postnatal Mother
                                    </button>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Mother's Name</th>
                                                <th>Age</th>
                                                <th>Delivery Date</th>
                                                <th>Delivery Type</th>
                                                <th>Recovery Status</th>
                                                <th>Next Check-up</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Mrs. Menaka Liyanage</td>
                                                <td>27</td>
                                                <td>2026-01-30</td>
                                                <td>Normal Delivery</td>
                                                <td>Excellent Recovery</td>
                                                <td>2026-02-13</td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-success btn-sm">Follow-up</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Children Tab -->
                    <div id="children-tab" class="tab-content hidden">
                        <!-- Sub-tabs for different children categories -->
                        <div class="tab-container" style="margin-top: 1rem;">
                            <ul class="nav nav-tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#" onclick="switchChildrenTab('newborns')">
                                        <i class="fas fa-baby"></i> Newborns
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" onclick="switchChildrenTab('young')">
                                        <i class="fas fa-baby-carriage"></i> Young Children
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" onclick="switchChildrenTab('childs')">
                                        <i class="fas fa-child"></i> Childs
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Newborns Sub-tab -->
                        <div id="newborns-children" class="tab-content">
                            <div class="card">
                                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                    <h5 class="card-title" style="margin: 0;">Newborns</h5>
                                    <button class="btn btn-primary btn-sm" onclick="addNewborn()">
                                        <i class="fas fa-plus"></i> Add Newborn
                                    </button>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
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
                                        <tbody>
                                            <tr>
                                                <td>Baby Tharusha Bandara</td>
                                                <td>Mrs. Ruvini Bandara</td>
                                                <td>2025-08-15</td>
                                                <td>3.4 kg</td>
                                                <td>2026-02-09</td>
                                                <td><span class="status-badge status-success">Healthy</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-success btn-sm">Vaccinate</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Baby Rajapaksha</td>
                                                <td>Mrs. Chamari Rajapaksha</td>
                                                <td>2025-10-20</td>
                                                <td>3.0 kg</td>
                                                <td>2026-02-11</td>
                                                <td><span class="status-badge status-success">Healthy</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-success btn-sm">Vaccinate</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Young Children Sub-tab -->
                        <div id="young-children" class="tab-content hidden">
                            <div class="card">
                                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                    <h5 class="card-title" style="margin: 0;">Young Children</h5>
                                    <button class="btn btn-primary btn-sm" onclick="addYoungChild()">
                                        <i class="fas fa-plus"></i> Add Young Child
                                    </button>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Child's Name</th>
                                                <th>Age</th>
                                                <th>Mother's Name</th>
                                                <th>Last Check-up</th>
                                                <th>Weight</th>
                                                <th>Health Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Hasini Fernando</td>
                                                <td>2.5 years</td>
                                                <td>Mrs. Sandya Fernando</td>
                                                <td>2026-02-08</td>
                                                <td>13.1 kg</td>
                                                <td><span class="status-badge status-success">Healthy</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-success btn-sm">Check-up</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Childs Sub-tab -->
                        <div id="childs-children" class="tab-content hidden">
                            <div class="card">
                                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                    <h5 class="card-title" style="margin: 0;">Childs</h5>
                                    <button class="btn btn-primary btn-sm" onclick="addChild()">
                                        <i class="fas fa-plus"></i> Add Child
                                    </button>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Child's Name</th>
                                                <th>Age</th>
                                                <th>Mother's Name</th>
                                                <th>School</th>
                                                <th>Last Visit</th>
                                                <th>Health Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Lakshitha Silva</td>
                                                <td>7 years</td>
                                                <td>Mrs. Nimalika Silva</td>
                                                <td>Seaside School</td>
                                                <td>2026-02-04</td>
                                                <td><span class="status-badge status-success">Healthy</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-warning btn-sm">Update</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Piyumi Liyanage</td>
                                                <td>5 years</td>
                                                <td>Mrs. Menaka Liyanage</td>
                                                <td>Ocean View Kindergarten</td>
                                                <td>2026-02-06</td>
                                                <td><span class="status-badge status-success">Healthy</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm">View</button>
                                                    <button class="btn btn-warning btn-sm">Update</button>
                                                </td>
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
            <div id="home-visits" class="content-section" style="display: none;">
                <div class="d-flex justify-between align-center mb-3">
                    <h2>Home Visits Management</h2>
                    <div>
                        <button class="btn btn-primary" onclick="scheduleNewVisit()">
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
                    <div class="duty-areas-grid">
                        <div class="duty-area-btn active" onclick="switchArea('home-visits', 'uduthuththiripitiya')">
                            <i class="fas fa-home"></i>
                            <h5>Uduthuththiripitiya</h5>
                            <div class="area-count">8 visits today</div>
                        </div>
                        <div class="duty-area-btn" onclick="switchArea('home-visits', 'kahabilihena')">
                            <i class="fas fa-hospital"></i>
                            <h5>Kahabilihena</h5>
                            <div class="area-count">5 visits today</div>
                        </div>
                        <div class="duty-area-btn" onclick="switchArea('home-visits', 'opathella')">
                            <i class="fas fa-city"></i>
                            <h5>Opathella</h5>
                            <div class="area-count">3 visits today</div>
                        </div>
                        <div class="duty-area-btn" onclick="switchArea('home-visits', 'ambalangoda')">
                            <i class="fas fa-tree"></i>
                            <h5>Ambalangoda</h5>
                            <div class="area-count">6 visits today</div>
                        </div>
                    </div>
                </div>

                <!-- Uduthuththiripitiya Area Content -->
                <div class="area-content-wrapper active" data-area="uduthuththiripitiya">
                    <div class="area-header">
                        <h4><i class="fas fa-map-marker-alt"></i> Uduthuththiripitiya Area - Home Visits</h4>
                        <p>Coverage: 15 villages, 450+ families | PHM Office: +94 37 226 5432</p>
                    </div>

                <!-- Visit Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-info">
                                <h3>8</h3>
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
                                <h3>3</h3>
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
                                <h3>5</h3>
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
                                <h3>2</h3>
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
                <div id="scheduled-visits" class="tab-content">
                    <div class="card">
                        <div class="card-header d-flex justify-between align-center">
                            <h4 class="card-title">Today's Scheduled Visits</h4>
                            <div class="d-flex gap-2">
                                <select class="form-control" style="width: 150px;" onchange="filterVisitsByDate(this.value)">
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
                            <div class="visit-list">
                                <div class="visit-item priority-high">
                                    <div class="visit-time">
                                        <span class="time">09:00</span>
                                        <span class="duration">45 min</span>
                                    </div>
                                    <div class="visit-details">
                                        <h5>Mrs. Nirmala Fernando</h5>
                                        <p class="address">
                                            <i class="fas fa-map-marker-alt"></i>
                                            No. 45, Galle Road, Mount Lavinia
                                        </p>
                                        <p class="visit-type">
                                            <span class="badge badge-urgent">Postnatal Visit - Day 3</span>
                                            <span class="badge badge-info">First Baby</span>
                                        </p>
                                        <p class="notes">Follow-up on breastfeeding issues and jaundice monitoring</p>
                                    </div>
                                    <div class="visit-actions">
                                        <button class="btn btn-success btn-sm" onclick="startVisit(1)">
                                            <i class="fas fa-play"></i> Start Visit
                                        </button>
                                        <button class="btn btn-info btn-sm" onclick="viewPatientDetails(1)">
                                            <i class="fas fa-eye"></i> View Details
                                        </button>
                                        <button class="btn btn-warning btn-sm" onclick="rescheduleVisit(1)">
                                            <i class="fas fa-calendar-alt"></i> Reschedule
                                        </button>
                                        <button class="btn btn-secondary btn-sm" onclick="getDirections(1)">
                                            <i class="fas fa-directions"></i> Directions
                                        </button>
                                    </div>
                                </div>

                                <div class="visit-item priority-normal">
                                    <div class="visit-time">
                                        <span class="time">10:30</span>
                                        <span class="duration">30 min</span>
                                    </div>
                                    <div class="visit-details">
                                        <h5>Mrs. Kamani Wickramasinghe</h5>
                                        <p class="address">
                                            <i class="fas fa-map-marker-alt"></i>
                                            No. 78, Temple Road, Dehiwala
                                        </p>
                                        <p class="visit-type">
                                            <span class="badge badge-success">Antenatal Visit - 32 weeks</span>
                                            <span class="badge badge-warning">High Risk</span>
                                        </p>
                                        <p class="notes">Routine checkup, monitor blood pressure and fetal growth</p>
                                    </div>
                                    <div class="visit-actions">
                                        <button class="btn btn-success btn-sm" onclick="startVisit(2)">
                                            <i class="fas fa-play"></i> Start Visit
                                        </button>
                                        <button class="btn btn-info btn-sm" onclick="viewPatientDetails(2)">
                                            <i class="fas fa-eye"></i> View Details
                                        </button>
                                        <button class="btn btn-warning btn-sm" onclick="rescheduleVisit(2)">
                                            <i class="fas fa-calendar-alt"></i> Reschedule
                                        </button>
                                        <button class="btn btn-secondary btn-sm" onclick="getDirections(2)">
                                            <i class="fas fa-directions"></i> Directions
                                        </button>
                                    </div>
                                </div>

                                <div class="visit-item priority-normal">
                                    <div class="visit-time">
                                        <span class="time">14:00</span>
                                        <span class="duration">40 min</span>
                                    </div>
                                    <div class="visit-details">
                                        <h5>Mrs. Sandya Peris</h5>
                                        <p class="address">
                                            <i class="fas fa-map-marker-alt"></i>
                                            No. 23, Flower Road, Colombo 7
                                        </p>
                                        <p class="visit-type">
                                            <span class="badge badge-primary">Postnatal Visit - Day 14</span>
                                        </p>
                                        <p class="notes">Check healing progress, discuss family planning</p>
                                    </div>
                                    <div class="visit-actions">
                                        <button class="btn btn-success btn-sm" onclick="startVisit(3)">
                                            <i class="fas fa-play"></i> Start Visit
                                        </button>
                                        <button class="btn btn-info btn-sm" onclick="viewPatientDetails(3)">
                                            <i class="fas fa-eye"></i> View Details
                                        </button>
                                        <button class="btn btn-warning btn-sm" onclick="rescheduleVisit(3)">
                                            <i class="fas fa-calendar-alt"></i> Reschedule
                                        </button>
                                        <button class="btn btn-secondary btn-sm" onclick="getDirections(3)">
                                            <i class="fas fa-directions"></i> Directions
                                        </button>
                                    </div>
                                </div>
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
                            <div class="completed-visit-list">
                                <div class="completed-visit-item">
                                    <div class="visit-timestamp">
                                        <span class="date">Dec 15, 2024</span>
                                        <span class="time">08:30 - 09:15</span>
                                    </div>
                                    <div class="visit-summary">
                                        <h5>Mrs. Priyani Silva</h5>
                                        <p class="visit-type">Antenatal Visit - 28 weeks</p>
                                        <p class="outcome">
                                            <span class="status-badge status-success">Completed</span>
                                            Normal progression, all vitals stable
                                        </p>
                                    </div>
                                    <div class="visit-actions">
                                        <button class="btn btn-outline-primary btn-sm" onclick="viewVisitReport(1)">
                                            <i class="fas fa-file-alt"></i> View Report
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>

                <!-- Kahabilihena Area Content -->
                <div class="area-content-wrapper" data-area="kahabilihena">
                    <div class="area-header">
                        <h4><i class="fas fa-map-marker-alt"></i> Kahabilihena Area - Home Visits</h4>
                        <p>Coverage: 12 villages, 380+ families | PHM Office: +94 37 205 6789</p>
                    </div>
                    <div class="row mb-4">
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                                <div class="stat-info"><h3>5</h3><span>Today's Visits</span></div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                                <div class="stat-info"><h3>2</h3><span>Pending Visits</span></div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                                <div class="stat-info"><h3>3</h3><span>Completed</span></div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                                <div class="stat-info"><h3>1</h3><span>Urgent Follow-ups</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Today's Scheduled Visits - Kahabilihena</h4>
                        </div>
                        <div class="card-body">
                            <div class="visit-list">
                                <div class="visit-item">
                                    <div class="visit-time">
                                        <span class="time">10:00</span>
                                        <span class="duration">30 min</span>
                                    </div>
                                    <div class="visit-details">
                                        <h5>Mrs. Ayesha Dissanayake</h5>
                                        <p class="address"><i class="fas fa-map-marker-alt"></i> Kahabilihena South</p>
                                        <p class="visit-type"><span class="badge badge-info">Antenatal Check - 28 weeks</span></p>
                                    </div>
                                    <div class="visit-actions">
                                        <button class="btn btn-success btn-sm"><i class="fas fa-play"></i> Start Visit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Opathella Area Content -->
                <div class="area-content-wrapper" data-area="opathella">
                    <div class="area-header">
                        <h4><i class="fas fa-map-marker-alt"></i> Opathella Area - Home Visits</h4>
                        <p>Coverage: 8 urban wards, 280+ families | PHM Office: +94 37 222 3456</p>
                    </div>
                    <div class="row mb-4">
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                                <div class="stat-info"><h3>3</h3><span>Today's Visits</span></div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                                <div class="stat-info"><h3>1</h3><span>Pending Visits</span></div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                                <div class="stat-info"><h3>2</h3><span>Completed</span></div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                                <div class="stat-info"><h3>0</h3><span>Urgent Follow-ups</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Today's Scheduled Visits - Opathella</h4>
                        </div>
                        <div class="card-body">
                            <div class="visit-list">
                                <div class="visit-item">
                                    <div class="visit-time">
                                        <span class="time">14:00</span>
                                        <span class="duration">40 min</span>
                                    </div>
                                    <div class="visit-details">
                                        <h5>Mrs. Kamali Jayasinghe</h5>
                                        <p class="address"><i class="fas fa-map-marker-alt"></i> Opathella Village</p>
                                        <p class="visit-type"><span class="badge badge-info">Postnatal Visit - Day 7</span></p>
                                    </div>
                                    <div class="visit-actions">
                                        <button class="btn btn-success btn-sm"><i class="fas fa-play"></i> Start Visit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ambalangoda Area Content -->
                <div class="area-content-wrapper" data-area="ambalangoda">
                    <div class="area-header">
                        <h4><i class="fas fa-map-marker-alt"></i> Ambalangoda Area - Home Visits</h4>
                        <p>Coverage: 18 villages, 520+ families | PHM Office: +94 37 267 8901</p>
                    </div>
                    <div class="row mb-4">
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                                <div class="stat-info"><h3>6</h3><span>Today's Visits</span></div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                                <div class="stat-info"><h3>3</h3><span>Pending Visits</span></div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                                <div class="stat-info"><h3>3</h3><span>Completed</span></div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                                <div class="stat-info"><h3>2</h3><span>Urgent Follow-ups</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Today's Scheduled Visits - Ambalangoda</h4>
                        </div>
                        <div class="card-body">
                            <div class="visit-list">
                                <div class="visit-item priority-high">
                                    <div class="visit-time">
                                        <span class="time">09:00</span>
                                        <span class="duration">45 min</span>
                                    </div>
                                    <div class="visit-details">
                                        <h5>Mrs. Sanduni Rajapaksha</h5>
                                        <p class="address"><i class="fas fa-map-marker-alt"></i> Ambalangoda Village</p>
                                        <p class="visit-type"><span class="badge badge-urgent">Urgent - Postnatal Complication</span></p>
                                    </div>
                                    <div class="visit-actions">
                                        <button class="btn btn-success btn-sm"><i class="fas fa-play"></i> Start Visit</button>
                                    </div>
                                </div>
                                <div class="visit-item">
                                    <div class="visit-time">
                                        <span class="time">11:00</span>
                                        <span class="duration">30 min</span>
                                    </div>
                                    <div class="visit-details">
                                        <h5>Mrs. Menaka Bandara</h5>
                                        <p class="address"><i class="fas fa-map-marker-alt"></i> Ambalangoda South</p>
                                        <p class="visit-type"><span class="badge badge-info">Antenatal - 32 weeks</span></p>
                                    </div>
                                    <div class="visit-actions">
                                        <button class="btn btn-success btn-sm"><i class="fas fa-play"></i> Start Visit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vaccinations Section -->
            <div id="vaccinations" class="content-section" style="display: none;">
                <div class="d-flex justify-between align-center mb-3">
                    <h2>Vaccination Management</h2>
                    <div>
                        <button class="btn btn-primary" onclick="scheduleVaccination()">
                            <i class="fas fa-plus"></i> Schedule Vaccination
                        </button>
                        <button class="btn btn-success" onclick="quickVaccinationLog()">
                            <i class="fas fa-syringe"></i> Quick Vaccination Log
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
                    <div class="duty-areas-grid">
                        <div class="duty-area-btn active" onclick="switchArea('vaccinations', 'uduthuththiripitiya')">
                            <i class="fas fa-home"></i>
                            <h5>Uduthuththiripitiya</h5>
                            <div class="area-count">12 scheduled today</div>
                        </div>
                        <div class="duty-area-btn" onclick="switchArea('vaccinations', 'kahabilihena')">
                            <i class="fas fa-hospital"></i>
                            <h5>Kahabilihena</h5>
                            <div class="area-count">8 scheduled today</div>
                        </div>
                        <div class="duty-area-btn" onclick="switchArea('vaccinations', 'opathella')">
                            <i class="fas fa-city"></i>
                            <h5>Opathella</h5>
                            <div class="area-count">6 scheduled today</div>
                        </div>
                        <div class="duty-area-btn" onclick="switchArea('vaccinations', 'ambalangoda')">
                            <i class="fas fa-tree"></i>
                            <h5>Ambalangoda</h5>
                            <div class="area-count">10 scheduled today</div>
                        </div>
                    </div>
                </div>

                <!-- Uduthuththiripitiya Area Content -->
                <div class="area-content-wrapper active" data-area="uduthuththiripitiya">
                    <div class="area-header">
                        <h4><i class="fas fa-map-marker-alt"></i> Uduthuththiripitiya Area - Vaccinations</h4>
                        <p>Coverage: 15 villages | Pediatric: 8, Maternal: 4 | Clinic: Uduthuththiripitiya CHC</p>
                    </div>

                <!-- Vaccination Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-info">
                                <h3>12</h3>
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
                                <h3>8</h3>
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
                                <h3>3</h3>
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
                                <h3>15</h3>
                                <span>Vaccines in Stock</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs for different vaccination views -->
                <div class="tab-container">
                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" onclick="switchVaccinationTab('scheduled')">Today's Schedule</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="switchVaccinationTab('inventory')">Vaccine Inventory</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="switchVaccinationTab('records')">Patient Records</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="switchVaccinationTab('overdue')">Overdue Vaccines</a>
                        </li>
                    </ul>
                </div>

                <!-- Today's Schedule Tab -->
                <div id="scheduled-vaccinations" class="tab-content">
                    <div class="card">
                        <div class="card-header d-flex justify-between align-center">
                            <h4 class="card-title">Today's Vaccination Schedule</h4>
                            <div class="d-flex gap-2">
                                <select class="form-control" style="width: 180px;" onchange="filterVaccinations(this.value)">
                                    <option value="all">All Vaccines</option>
                                    <option value="pediatric">Pediatric</option>
                                    <option value="maternal">Maternal</option>
                                    <option value="routine">Routine Adult</option>
                                </select>
                                <button class="btn btn-outline-primary" onclick="printSchedule()">
                                    <i class="fas fa-print"></i> Print Schedule
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="vaccination-schedule">
                                <div class="vaccination-item high-priority">
                                    <div class="vaccine-time">
                                        <span class="time">09:00</span>
                                        <span class="duration">15 min</span>
                                    </div>
                                    <div class="vaccine-details">
                                        <h5>Baby Kamal Silva (2 months)</h5>
                                        <div class="patient-info">
                                            <span class="mother-name"><i class="fas fa-user"></i> Mother: Mrs. Nayani Silva</span>
                                            <span class="contact"><i class="fas fa-phone"></i> +94 77 555 0123</span>
                                        </div>
                                        <div class="vaccine-info">
                                            <span class="vaccine-badge pediatric">DPT-1</span>
                                            <span class="vaccine-badge pediatric">OPV-1</span>
                                            <span class="vaccine-badge pediatric">Hep B-1</span>
                                        </div>
                                        <p class="notes">First dose of routine pediatric series. Check weight and temperature.</p>
                                    </div>
                                    <div class="vaccine-actions">
                                        <button class="btn btn-success btn-sm" onclick="administerVaccine(1)">
                                            <i class="fas fa-syringe"></i> Administer
                                        </button>
                                        <button class="btn btn-info btn-sm" onclick="viewVaccineHistory(1)">
                                            <i class="fas fa-history"></i> History
                                        </button>
                                        <button class="btn btn-warning btn-sm" onclick="rescheduleVaccine(1)">
                                            <i class="fas fa-calendar-alt"></i> Reschedule
                                        </button>
                                    </div>
                                </div>

                                <div class="vaccination-item normal-priority">
                                    <div class="vaccine-time">
                                        <span class="time">10:30</span>
                                        <span class="duration">10 min</span>
                                    </div>
                                    <div class="vaccine-details">
                                        <h5>Mrs. Priyanka Fernando (28 years)</h5>
                                        <div class="patient-info">
                                            <span class="pregnancy-status"><i class="fas fa-baby"></i> 28 weeks pregnant</span>
                                            <span class="contact"><i class="fas fa-phone"></i> +94 71 444 5678</span>
                                        </div>
                                        <div class="vaccine-info">
                                            <span class="vaccine-badge maternal">Tetanus Toxoid - 2nd dose</span>
                                        </div>
                                        <p class="notes">Second TT dose for pregnancy. Check previous reaction history.</p>
                                    </div>
                                    <div class="vaccine-actions">
                                        <button class="btn btn-success btn-sm" onclick="administerVaccine(2)">
                                            <i class="fas fa-syringe"></i> Administer
                                        </button>
                                        <button class="btn btn-info btn-sm" onclick="viewVaccineHistory(2)">
                                            <i class="fas fa-history"></i> History
                                        </button>
                                        <button class="btn btn-warning btn-sm" onclick="rescheduleVaccine(2)">
                                            <i class="fas fa-calendar-alt"></i> Reschedule
                                        </button>
                                    </div>
                                </div>

                                <div class="vaccination-item normal-priority">
                                    <div class="vaccine-time">
                                        <span class="time">14:00</span>
                                        <span class="duration">20 min</span>
                                    </div>
                                    <div class="vaccine-details">
                                        <h5>Mrs. Kumari Wickramasinghe (35 years)</h5>
                                        <div class="patient-info">
                                            <span class="condition"><i class="fas fa-heart"></i> Diabetic patient</span>
                                            <span class="contact"><i class="fas fa-phone"></i> +94 76 333 9876</span>
                                        </div>
                                        <div class="vaccine-info">
                                            <span class="vaccine-badge adult">Influenza Vaccine</span>
                                            <span class="vaccine-badge adult">Pneumococcal</span>
                                        </div>
                                        <p class="notes">Annual flu vaccine + pneumococcal for high-risk patient.</p>
                                    </div>
                                    <div class="vaccine-actions">
                                        <button class="btn btn-success btn-sm" onclick="administerVaccine(3)">
                                            <i class="fas fa-syringe"></i> Administer
                                        </button>
                                        <button class="btn btn-info btn-sm" onclick="viewVaccineHistory(3)">
                                            <i class="fas fa-history"></i> History
                                        </button>
                                        <button class="btn btn-warning btn-sm" onclick="rescheduleVaccine(3)">
                                            <i class="fas fa-calendar-alt"></i> Reschedule
                                        </button>
                                    </div>
                                </div>
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
                            <div class="inventory-grid">
                                <div class="inventory-item good-stock">
                                    <div class="vaccine-icon">
                                        <i class="fas fa-vial"></i>
                                    </div>
                                    <div class="vaccine-name">DPT Vaccine</div>
                                    <div class="stock-info">
                                        <span class="stock-level">25 doses</span>
                                        <span class="expiry-date">Exp: Jun 2025</span>
                                    </div>
                                    <div class="stock-status good">Good Stock</div>
                                </div>

                                <div class="inventory-item low-stock">
                                    <div class="vaccine-icon">
                                        <i class="fas fa-vial"></i>
                                    </div>
                                    <div class="vaccine-name">OPV (Oral Polio)</div>
                                    <div class="stock-info">
                                        <span class="stock-level">8 doses</span>
                                        <span class="expiry-date">Exp: Mar 2025</span>
                                    </div>
                                    <div class="stock-status low">Low Stock</div>
                                </div>

                                <div class="inventory-item good-stock">
                                    <div class="vaccine-icon">
                                        <i class="fas fa-vial"></i>
                                    </div>
                                    <div class="vaccine-name">Hepatitis B</div>
                                    <div class="stock-info">
                                        <span class="stock-level">18 doses</span>
                                        <span class="expiry-date">Exp: Aug 2025</span>
                                    </div>
                                    <div class="stock-status good">Good Stock</div>
                                </div>

                                <div class="inventory-item critical-stock">
                                    <div class="vaccine-icon">
                                        <i class="fas fa-vial"></i>
                                    </div>
                                    <div class="vaccine-name">Tetanus Toxoid</div>
                                    <div class="stock-info">
                                        <span class="stock-level">3 doses</span>
                                        <span class="expiry-date">Exp: Apr 2025</span>
                                    </div>
                                    <div class="stock-status critical">Critical</div>
                                </div>

                                <div class="inventory-item good-stock">
                                    <div class="vaccine-icon">
                                        <i class="fas fa-vial"></i>
                                    </div>
                                    <div class="vaccine-name">MMR Vaccine</div>
                                    <div class="stock-info">
                                        <span class="stock-level">12 doses</span>
                                        <span class="expiry-date">Exp: Jul 2025</span>
                                    </div>
                                    <div class="stock-status good">Good Stock</div>
                                </div>

                                <div class="inventory-item low-stock">
                                    <div class="vaccine-icon">
                                        <i class="fas fa-vial"></i>
                                    </div>
                                    <div class="vaccine-name">Influenza</div>
                                    <div class="stock-info">
                                        <span class="stock-level">6 doses</span>
                                        <span class="expiry-date">Exp: Feb 2025</span>
                                    </div>
                                    <div class="stock-status low">Low Stock</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Patient Records Tab -->
                <div id="records-vaccinations" class="tab-content" style="display: none;">
                    <div class="card">
                        <div class="card-header d-flex justify-between align-center">
                            <h4 class="card-title">Patient Vaccination Records</h4>
                            <input type="text" class="form-control" placeholder="Search patient..." style="max-width: 300px;">
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Patient Name</th>
                                        <th>Age/DOB</th>
                                        <th>Last Vaccine</th>
                                        <th>Next Due</th>
                                        <th>Completion %</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Baby Amara Silva</td>
                                        <td>4 months</td>
                                        <td>DPT-2, OPV-2 (Nov 15)</td>
                                        <td><span class="due-soon">DPT-3 (Dec 20)</span></td>
                                        <td>
                                            <div class="completion-bar">
                                                <div class="completion-fill" style="width: 60%"></div>
                                                <span>60%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-info btn-sm">View Card</button>
                                            <button class="btn btn-success btn-sm">Schedule</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Baby Sahan Peris</td>
                                        <td>6 months</td>
                                        <td>DPT-3, OPV-3 (Dec 10)</td>
                                        <td><span class="due-later">MMR (Mar 15, 2025)</span></td>
                                        <td>
                                            <div class="completion-bar">
                                                <div class="completion-fill" style="width: 75%"></div>
                                                <span>75%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-info btn-sm">View Card</button>
                                            <button class="btn btn-success btn-sm">Schedule</button>
                                        </td>
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
                            <div class="overdue-list">
                                <div class="overdue-item urgent">
                                    <div class="overdue-info">
                                        <h5>Baby Nimal Fernando</h5>
                                        <p class="vaccine-details">DPT-2, OPV-2, Hep B-2</p>
                                        <p class="overdue-duration">
                                            <i class="fas fa-clock"></i> 
                                            <span class="overdue-text">15 days overdue</span>
                                        </p>
                                    </div>
                                    <div class="contact-info">
                                        <p><i class="fas fa-user"></i> Mother: Mrs. Sandya Fernando</p>
                                        <p><i class="fas fa-phone"></i> +94 77 123 4567</p>
                                    </div>
                                    <div class="overdue-actions">
                                        <button class="btn btn-danger btn-sm" onclick="contactPatient(1)">
                                            <i class="fas fa-phone"></i> Call Now
                                        </button>
                                        <button class="btn btn-primary btn-sm" onclick="scheduleOverdue(1)">
                                            <i class="fas fa-calendar-plus"></i> Schedule
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>

                <!-- Kahabilihena Area Content -->
                <div class="area-content-wrapper" data-area="kahabilihena">
                    <div class="area-header">
                        <h4><i class="fas fa-map-marker-alt"></i> Kahabilihena Area - Vaccinations</h4>
                        <p>Coverage: 12 villages | Pediatric: 6, Maternal: 2 | Clinic: Kahabilihena RH</p>
                    </div>
                    <div class="row mb-4">
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                                <div class="stat-info"><h3>8</h3><span>Today's Schedule</span></div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-syringe"></i></div>
                                <div class="stat-info"><h3>6</h3><span>Completed</span></div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-exclamation-circle"></i></div>
                                <div class="stat-info"><h3>2</h3><span>Overdue</span></div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-boxes"></i></div>
                                <div class="stat-info"><h3>12</h3><span>Vaccines in Stock</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Today's Vaccination Schedule - Kahabilihena</h4>
                        </div>
                        <div class="card-body">
                            <div class="vaccination-schedule">
                                <div class="vaccination-item">
                                    <div class="vaccine-time">
                                        <span class="time">10:00</span>
                                        <span class="duration">15 min</span>
                                    </div>
                                    <div class="vaccine-details">
                                        <h5>Baby Sithum Perera (4 months)</h5>
                                        <div class="patient-info">
                                            <span class="mother-name"><i class="fas fa-user"></i> Mother: Mrs. Dilini Perera</span>
                                        </div>
                                        <div class="vaccine-info">
                                            <span class="vaccine-badge pediatric">DPT-2</span>
                                            <span class="vaccine-badge pediatric">OPV-2</span>
                                        </div>
                                    </div>
                                    <div class="vaccine-actions">
                                        <button class="btn btn-success btn-sm"><i class="fas fa-syringe"></i> Administer</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Opathella Area Content -->
                <div class="area-content-wrapper" data-area="opathella">
                    <div class="area-header">
                        <h4><i class="fas fa-map-marker-alt"></i> Opathella Area - Vaccinations</h4>
                        <p>Coverage: 8 urban wards | Pediatric: 4, Maternal: 2 | Clinic: Opathella PHC</p>
                    </div>
                    <div class="row mb-4">
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                                <div class="stat-info"><h3>6</h3><span>Today's Schedule</span></div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-syringe"></i></div>
                                <div class="stat-info"><h3>5</h3><span>Completed</span></div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-exclamation-circle"></i></div>
                                <div class="stat-info"><h3>1</h3><span>Overdue</span></div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-boxes"></i></div>
                                <div class="stat-info"><h3>18</h3><span>Vaccines in Stock</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Today's Vaccination Schedule - Opathella</h4>
                        </div>
                        <div class="card-body">
                            <div class="vaccination-schedule">
                                <div class="vaccination-item">
                                    <div class="vaccine-time">
                                        <span class="time">11:00</span>
                                        <span class="duration">10 min</span>
                                    </div>
                                    <div class="vaccine-details">
                                        <h5>Mrs. Kumari Jayawardena (30 years)</h5>
                                        <div class="patient-info">
                                            <span class="pregnancy-status"><i class="fas fa-baby"></i> 24 weeks pregnant</span>
                                        </div>
                                        <div class="vaccine-info">
                                            <span class="vaccine-badge maternal">TT-1</span>
                                        </div>
                                    </div>
                                    <div class="vaccine-actions">
                                        <button class="btn btn-success btn-sm"><i class="fas fa-syringe"></i> Administer</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ambalangoda Area Content -->
                <div class="area-content-wrapper" data-area="ambalangoda">
                    <div class="area-header">
                        <h4><i class="fas fa-map-marker-alt"></i> Ambalangoda Area - Vaccinations</h4>
                        <p>Coverage: 18 villages | Pediatric: 7, Maternal: 3 | Clinic: Ambalangoda DH</p>
                    </div>
                    <div class="row mb-4">
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                                <div class="stat-info"><h3>10</h3><span>Today's Schedule</span></div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-syringe"></i></div>
                                <div class="stat-info"><h3>7</h3><span>Completed</span></div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-exclamation-circle"></i></div>
                                <div class="stat-info"><h3>4</h3><span>Overdue</span></div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-boxes"></i></div>
                                <div class="stat-info"><h3>14</h3><span>Vaccines in Stock</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Today's Vaccination Schedule - Ambalangoda</h4>
                        </div>
                        <div class="card-body">
                            <div class="vaccination-schedule">
                                <div class="vaccination-item high-priority">
                                    <div class="vaccine-time">
                                        <span class="time">09:30</span>
                                        <span class="duration">15 min</span>
                                    </div>
                                    <div class="vaccine-details">
                                        <h5>Baby Tharusha Bandara (6 months)</h5>
                                        <div class="patient-info">
                                            <span class="mother-name"><i class="fas fa-user"></i> Mother: Mrs. Chamari Bandara</span>
                                        </div>
                                        <div class="vaccine-info">
                                            <span class="vaccine-badge pediatric">DPT-3</span>
                                            <span class="vaccine-badge pediatric">OPV-3</span>
                                            <span class="vaccine-badge pediatric">Hep B-3</span>
                                        </div>
                                    </div>
                                    <div class="vaccine-actions">
                                        <button class="btn btn-success btn-sm"><i class="fas fa-syringe"></i> Administer</button>
                                    </div>
                                </div>
                                <div class="vaccination-item">
                                    <div class="vaccine-time">
                                        <span class="time">14:00</span>
                                        <span class="duration">10 min</span>
                                    </div>
                                    <div class="vaccine-details">
                                        <h5>Mrs. Nimalika Silva (26 years)</h5>
                                        <div class="patient-info">
                                            <span class="pregnancy-status"><i class="fas fa-baby"></i> 32 weeks pregnant</span>
                                        </div>
                                        <div class="vaccine-info">
                                            <span class="vaccine-badge maternal">TT-2</span>
                                        </div>
                                    </div>
                                    <div class="vaccine-actions">
                                        <button class="btn btn-success btn-sm"><i class="fas fa-syringe"></i> Administer</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Section -->
            <div id="profile" class="content-section" style="display: none;">
                <!-- My Duty Areas Section -->
                <h2 style="margin-bottom: 1.5rem; color: var(--text-primary); font-size: 2rem; font-weight: 600;">My Duty Areas</h2>
                <div class="duty-area-container">
                    <div class="duty-areas-grid">
                        <div class="duty-area-btn">
                            <i class="fas fa-home"></i>
                            <h5>Uduthuththiripitiya</h5>
                            <div class="area-count">4 appointments</div>
                        </div>
                        <div class="duty-area-btn">
                            <i class="fas fa-hospital"></i>
                            <h5>Kahabilihena</h5>
                            <div class="area-count">3 appointments</div>
                        </div>
                        <div class="duty-area-btn">
                            <i class="fas fa-city"></i>
                            <h5>Opathella</h5>
                            <div class="area-count">2 appointments</div>
                        </div>
                        <div class="duty-area-btn">
                            <i class="fas fa-tree"></i>
                            <h5>Ambalangoda</h5>
                            <div class="area-count">5 appointments</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-between align-center mb-3">
                    <h2>My Profile</h2>
                    <div>
                        <button class="btn btn-primary" onclick="editProfile()">
                            <i class="fas fa-edit"></i> Edit Profile
                        </button>
                        <button class="btn btn-info" onclick="changePassword()">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </div>
                </div>

                <!-- Profile Tabs -->
                <div class="tab-container">
                    <ul class="nav nav-tabs" style="width: 100%;">
                        <li class="nav-item" style="flex: 1; margin-right: 0;">
                            <a class="nav-link active" href="#" onclick="switchProfileTab('personal')" style="text-align: center;">Personal Info</a>
                        </li>
                        <li class="nav-item" style="flex: 1; margin-right: 0;">
                            <a class="nav-link" href="#" onclick="switchProfileTab('professional')" style="text-align: center;">Professional Details</a>
                        </li>
                        <li class="nav-item" style="flex: 1; margin-right: 0;">
                            <a class="nav-link" href="#" onclick="switchProfileTab('performance')" style="text-align: center;">Performance</a>
                        </li>
                        <li class="nav-item" style="flex: 1; margin-right: 0;">
                            <a class="nav-link" href="#" onclick="switchProfileTab('settings')" style="text-align: center;">Settings</a>
                        </li>
                    </ul>
                </div>

                <!-- Personal Info Tab -->
                <div id="personal-profile" class="tab-content">
                    <div class="row">
                        <div class="col-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <div class="profile-picture-container">
                                        <div class="profile-picture" id="profileImage" role="button" tabindex="0" aria-label="View profile photo" onclick="openProfileImagePreview()" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openProfileImagePreview();}">
                                            <img src="../images/profile%20picture.png" alt="Midwife Profile Picture">
                                        </div>
                                    </div>
                                    <h4 class="mt-3">Mrs. Madhavi Jayawardene</h4>
                                    <p class="text-muted">Registered Midwife</p>
                                    <p class="employee-id">Employee ID: MW001</p>
                                    <div class="profile-badges">
                                        <span class="badge badge-success">Active</span>
                                        <span class="badge badge-info">Certified</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Stats Card -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="card-title">Quick Stats</h5>
                                </div>
                                <div class="card-body">
                                    <div class="stat-row">
                                        <span class="stat-label">Years of Service</span>
                                        <span class="stat-value">5.2 years</span>
                                    </div>
                                    <div class="stat-row">
                                        <span class="stat-label">Patients Served</span>
                                        <span class="stat-value">1,248</span>
                                    </div>
                                    <div class="stat-row">
                                        <span class="stat-label">Deliveries Assisted</span>
                                        <span class="stat-value">324</span>
                                    </div>
                                    <div class="stat-row">
                                        <span class="stat-label">Success Rate</span>
                                        <span class="stat-value">98.5%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-8">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Personal Information</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Full Name:</strong>
                                            <p>Madhavi Jayawardene</p>
                                        </div>
                                        <div class="col-6">
                                            <strong>Date of Birth:</strong>
                                            <p>June 03, 1980</p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>NIC Number:</strong>
                                            <p>199007500123</p>
                                        </div>
                                        <div class="col-6">
                                            <strong>Gender:</strong>
                                            <p>Female</p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Marital Status:</strong>
                                            <p>Married</p>
                                        </div>
                                        <div class="col-6">
                                            <strong>Languages:</strong>
                                            <p>Sinhala, English</p>
                                        </div>
                                    </div>

                                    <h5 class="mt-4 mb-3">Contact Information</h5>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Email Address:</strong>
                                            <p>Madhavi.Jayawardene@health.gov.lk</p>
                                        </div>
                                        <div class="col-6">
                                            <strong>Personal Email:</strong>
                                            <p>madhavi.jayawardene@gmail.com</p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Mobile Phone:</strong>
                                            <p>+94 77 123 4567</p>
                                        </div>
                                        <div class="col-6">
                                            <strong>Home Phone:</strong>
                                            <p>+94 11 234 5678</p>
                                        </div>
                                    </div>

                                    <h5 class="mt-4 mb-3">Address Information</h5>
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <strong>Home Address:</strong>
                                            <p>57/1/A Pitipana, Homagama </p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Postal Code:</strong>
                                            <p>11104</p>
                                        </div>
                                        <div class="col-6">
                                            <strong>District:</strong>
                                            <p>Colombo</p>
                                        </div>
                                    </div>

                                    <h5 class="mt-4 mb-3">Emergency Contact</h5>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Contact Name:</strong>
                                            <p>Sunil Perera (Husband)</p>
                                        </div>
                                        <div class="col-6">
                                            <strong>Contact Number:</strong>
                                            <p>+94 71 987 6543</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Professional Details Tab -->
                <div id="professional-profile" class="tab-content" style="display: none;">
                    <div class="row">
                        <div class="col-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Professional Qualifications</h4>
                                </div>
                                <div class="card-body">
                                    <div class="qualification-item">
                                        <h6>Diploma in Midwifery</h6>
                                        <p class="institution">University of Colombo - Faculty of Medicine</p>
                                        <p class="year">Graduated: 2019</p>
                                        <p class="grade">Grade: First Class</p>
                                    </div>
                                    <hr>
                                    <div class="qualification-item">
                                        <h6>Certificate in Maternal & Child Health</h6>
                                        <p class="institution">Ministry of Health, Sri Lanka</p>
                                        <p class="year">Completed: 2020</p>
                                        <p class="grade">Grade: Distinction</p>
                                    </div>
                                    <hr>
                                    <div class="qualification-item">
                                        <h6>Basic Life Support (BLS) Certification</h6>
                                        <p class="institution">Sri Lankan Heart Association</p>
                                        <p class="year">Valid until: December 2025</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h4 class="card-title">Professional Memberships</h4>
                                </div>
                                <div class="card-body">
                                    <div class="membership-item">
                                        <h6>Sri Lanka Nursing Council</h6>
                                        <p>License No: MW-2020-001234</p>
                                        <p>Valid until: January 2026</p>
                                        <span class="badge badge-success">Active</span>
                                    </div>
                                    <hr>
                                    <div class="membership-item">
                                        <h6>Midwives Association of Sri Lanka</h6>
                                        <p>Member since: 2020</p>
                                        <span class="badge badge-info">Member</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Work Assignment</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Department:</strong>
                                            <p>Community Health Services</p>
                                        </div>
                                        <div class="col-6">
                                            <strong>Position:</strong>
                                            <p>Registered Midwife</p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Assigned Area:</strong>
                                            <p>Udathuthththiripitiya</p>
                                        </div>
                                        <div class="col-6">
                                            <strong>Coverage Population:</strong>
                                            <p>~3,600 residents</p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Start Date:</strong>
                                            <p>January 15, 2020</p>
                                        </div>
                                        <div class="col-6">
                                            <strong>Employment Type:</strong>
                                            <p>Permanent Full-time</p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>MOH Office:</strong>
                                            <p>MOH Attanagalla</p>
                                        </div>
                                        <div class="col-6">
                                            <strong>Work Schedule:</strong>
                                            <p>Mon-Fri, 8:00 AM - 4:30 PM</p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Supervisor:</strong>
                                            <p>Dr. Nayani Fernando</p>
                                        </div>
                                        <div class="col-6">
                                            <strong>Working Area:</strong>
                                            <p>Udathuthththiripitiya</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h4 class="card-title">Recent Training & Development</h4>
                                </div>
                                <div class="card-body">
                                    <div class="training-item">
                                        <h6>Advanced Neonatal Resuscitation</h6>
                                        <p class="training-date">Completed: November 2024</p>
                                        <p class="training-provider">Perinatal Society of Sri Lanka</p>
                                        <span class="badge badge-primary">16 Hours</span>
                                    </div>
                                    <hr>
                                    <div class="training-item">
                                        <h6>Digital Health Records Management</h6>
                                        <p class="training-date">Completed: September 2024</p>
                                        <p class="training-provider">Ministry of Health</p>
                                        <span class="badge badge-info">8 Hours</span>
                                    </div>
                                    <hr>
                                    <div class="training-item">
                                        <h6>Mental Health First Aid</h6>
                                        <p class="training-date">Completed: July 2024</p>
                                        <p class="training-provider">National Institute of Mental Health</p>
                                        <span class="badge badge-success">12 Hours</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Tab -->
                <div id="performance-profile" class="tab-content" style="display: none;">
                    <div class="row mb-4">
                        <div class="col-3">
                            <div class="performance-metric">
                                <div class="metric-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="metric-info">
                                    <h3>1,248</h3>
                                    <span>Patients Served</span>
                                    <div class="metric-change positive">
                                        <i class="fas fa-arrow-up"></i> +15% this year
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="performance-metric">
                                <div class="metric-icon">
                                    <i class="fas fa-baby"></i>
                                </div>
                                <div class="metric-info">
                                    <h3>324</h3>
                                    <span>Deliveries Assisted</span>
                                    <div class="metric-change positive">
                                        <i class="fas fa-arrow-up"></i> +8% this year
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="performance-metric">
                                <div class="metric-icon">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="metric-info">
                                    <h3>892</h3>
                                    <span>Home Visits</span>
                                    <div class="metric-change positive">
                                        <i class="fas fa-arrow-up"></i> +12% this year
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="performance-metric">
                                <div class="metric-icon">
                                    <i class="fas fa-syringe"></i>
                                </div>
                                <div class="metric-info">
                                    <h3>567</h3>
                                    <span>Vaccinations Given</span>
                                    <div class="metric-change positive">
                                        <i class="fas fa-arrow-up"></i> +20% this year
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-8">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Performance Trends</h4>
                                </div>
                                <div class="card-body">
                                    <canvas id="performanceChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Performance Rating</h4>
                                </div>
                                <div class="card-body text-center">
                                    <div class="performance-score">
                                        <div class="score-circle">
                                            <span class="score-number">9.2</span>
                                            <span class="score-max">/10</span>
                                        </div>
                                    </div>
                                    <h5 class="mt-3">Excellent Performance</h5>
                                    <p class="text-muted">Based on patient feedback, supervisor evaluation, and key metrics</p>
                                    
                                    <div class="rating-breakdown">
                                        <div class="rating-item">
                                            <span>Patient Care Quality</span>
                                            <div class="rating-stars">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                            </div>
                                        </div>
                                        <div class="rating-item">
                                            <span>Professional Knowledge</span>
                                            <div class="rating-stars">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                            </div>
                                        </div>
                                        <div class="rating-item">
                                            <span>Communication Skills</span>
                                            <div class="rating-stars">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="far fa-star"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Tab -->
                <div id="settings-profile" class="tab-content" style="display: none;">
                    <div class="row">
                        <div class="col-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Notification Preferences</h4>
                                </div>
                                <div class="card-body">
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <h6>Email Notifications</h6>
                                            <p>Receive updates and reminders via email</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" checked>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <h6>SMS Alerts</h6>
                                            <p>Get urgent notifications via SMS</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" checked>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <h6>Push Notifications</h6>
                                            <p>Browser push notifications for updates</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <h6>Weekly Report</h6>
                                            <p>Receive weekly performance summary</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" checked>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">System Preferences</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="form-label">Language</label>
                                        <select class="form-control">
                                            <option value="en" selected>English</option>
                                            <option value="si">Sinhala</option>
                                            <option value="ta">Tamil</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Date Format</label>
                                        <select class="form-control">
                                            <option value="dd/mm/yyyy" selected>DD/MM/YYYY</option>
                                            <option value="mm/dd/yyyy">MM/DD/YYYY</option>
                                            <option value="yyyy-mm-dd">YYYY-MM-DD</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Time Format</label>
                                        <select class="form-control">
                                            <option value="24h" selected>24 Hour (14:30)</option>
                                            <option value="12h">12 Hour (2:30 PM)</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Dashboard Theme</label>
                                        <select class="form-control">
                                            <option value="light" selected>Light Theme</option>
                                            <option value="dark">Dark Theme</option>
                                            <option value="auto">Auto (System)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h4 class="card-title">Privacy & Security</h4>
                                </div>
                                <div class="card-body">
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <h6>Two-Factor Authentication</h6>
                                            <p>Add extra security to your account</p>
                                        </div>
                                        <button class="btn btn-outline-primary btn-sm">Enable</button>
                                    </div>
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <h6>Session Timeout</h6>
                                            <p>Auto-logout after inactivity</p>
                                        </div>
                                        <select class="form-control" style="width: 120px;">
                                            <option value="30">30 minutes</option>
                                            <option value="60" selected>1 hour</option>
                                            <option value="120">2 hours</option>
                                        </select>
                                    </div>
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <h6>Data Export</h6>
                                            <p>Download your activity data</p>
                                        </div>
                                        <button class="btn btn-outline-info btn-sm" onclick="exportData()">Export</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Triposha Distribution Section -->
            <div id="triposha" class="content-section" style="display: none;">
                <div class="page-header">
                    <h2><i class="fas fa-box"></i> Triposha Distribution Management</h2>
                    <p>Manage Triposha packet distribution and inventory tracking</p>
                </div>

                <!-- Duty Areas Selection -->
                <div class="duty-areas-container">
                    <div class="duty-areas-title">
                        <i class="fas fa-map-marker-alt"></i> Select Duty Area
                    </div>
                    <div class="duty-areas-grid">
                        <div class="duty-area-btn active" onclick="switchArea('triposha', 'uduthuththiripitiya')">
                            <i class="fas fa-home"></i>
                            <h5>Uduthuththiripitiya</h5>
                            <div class="area-count">98 packets distributed</div>
                        </div>
                        <div class="duty-area-btn" onclick="switchArea('triposha', 'kahabilihena')">
                            <i class="fas fa-hospital"></i>
                            <h5>Kahabilihena</h5>
                            <div class="area-count">75 packets distributed</div>
                        </div>
                        <div class="duty-area-btn" onclick="switchArea('triposha', 'opathella')">
                            <i class="fas fa-city"></i>
                            <h5>Opathella</h5>
                            <div class="area-count">56 packets distributed</div>
                        </div>
                        <div class="duty-area-btn" onclick="switchArea('triposha', 'ambalangoda')">
                            <i class="fas fa-tree"></i>
                            <h5>Ambalangoda</h5>
                            <div class="area-count">105 packets distributed</div>
                        </div>
                    </div>
                </div>

                <!-- Uduthuththiripitiya Area Content -->
                <div class="area-content-wrapper active" data-area="uduthuththiripitiya">
                    <div class="area-header">
                        <h4><i class="fas fa-map-marker-alt"></i> Uduthuththiripitiya Area - Triposha Distribution</h4>
                        <p>Coverage: 15 villages | Beneficiaries: 85 families | Distribution Center: Uduthuththiripitiya CHC</p>
                    </div>

                <!-- Statistics Cards -->
                <div class="dashboard-stats">
                    <div class="stat-card info editable-stat" onclick="editPacketsReceived()">
                        <div class="stat-number" id="packets-received-month">150</div>
                        <div class="stat-label">Packets Received This Month</div>
                        <div class="edit-hint"><i class="fas fa-edit"></i> Click to edit</div>
                    </div>
                    <div class="stat-card warning editable-stat" onclick="editPacketsLeftPrevious()">
                        <div class="stat-number" id="packets-left-previous">25</div>
                        <div class="stat-label">Packets Left from Previous Month</div>
                        <div class="edit-hint"><i class="fas fa-edit"></i> Click to edit</div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-number" id="total-packets">175</div>
                        <div class="stat-label">Total Packets Available</div>
                        <div class="auto-calc-hint"><i class="fas fa-calculator"></i> Auto-calculated</div>
                    </div>
                    <div class="stat-card editable-stat" onclick="editPacketsDistributed()">
                        <div class="stat-number" id="packets-distributed">98</div>
                        <div class="stat-label">Packets Distributed</div>
                        <div class="edit-hint"><i class="fas fa-edit"></i> Click to edit</div>
                    </div>
                </div>

                <!-- Distribution Management -->
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
                                            <tr data-id="1">
                                                <td>2026-02-05</td>
                                                <td>Mrs. K. Silva</td>
                                                <td>45, Main Street, Uduthuththiripitiya</td>
                                                <td>2</td>
                                                <td>Pregnant Mother</td>
                                                <td><span class="status-badge status-active">Completed</span></td>
                                                <td>
                                                    <button class="btn btn-danger btn-sm" onclick="removeDistributionRecord(this, 2, 'pregnant')" title="Remove this distribution">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr data-id="2">
                                                <td>2026-02-05</td>
                                                <td>Mrs. A. Fernando</td>
                                                <td>12, Temple Road, Uduthuththiripitiya</td>
                                                <td>3</td>
                                                <td>Lactating Mother</td>
                                                <td><span class="status-badge status-active">Completed</span></td>
                                                <td>
                                                    <button class="btn btn-danger btn-sm" onclick="removeDistributionRecord(this, 3, 'lactating')" title="Remove this distribution">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr data-id="3">
                                                <td>2026-02-04</td>
                                                <td>Mrs. D. Jayawardene</td>
                                                <td>8, School Lane, Uduthuththiripitiya</td>
                                                <td>2</td>
                                                <td>Child (6-23 months)</td>
                                                <td><span class="status-badge status-active">Completed</span></td>
                                                <td>
                                                    <button class="btn btn-danger btn-sm" onclick="removeDistributionRecord(this, 2, 'children')" title="Remove this distribution">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr data-id="4">
                                                <td>2026-02-04</td>
                                                <td>Mrs. P. Perera</td>
                                                <td>23, Station Road, Uduthuththiripitiya</td>
                                                <td>1</td>
                                                <td>Pregnant Mother</td>
                                                <td><span class="status-badge status-pending">Pending</span></td>
                                                <td>
                                                    <button class="btn btn-danger btn-sm" onclick="removeDistributionRecord(this, 1, 'pregnant')" title="Remove this distribution">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
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
                                <h4 class="card-title">Quick Actions</h4>
                            </div>
                            <div class="card-body">
                                <button class="btn btn-primary btn-block mb-3" onclick="showDistributionForm()">
                                    <i class="fas fa-plus"></i> Record Distribution
                                </button>
                                <button class="btn btn-secondary btn-block mb-3" onclick="updateInventory()">
                                    <i class="fas fa-box-open"></i> Update Inventory
                                </button>
                                <button class="btn btn-danger btn-block mb-3" onclick="showRemoveDistributionForm()">
                                    <i class="fas fa-minus-circle"></i> Remove Distribution
                                </button>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">
                                <h4 class="card-title">Monthly Summary</h4>
                            </div>
                            <div class="card-body">
                                <div class="summary-item editable-summary" onclick="editCategorySummary('pregnant')">
                                    <span>Pregnant Mothers:</span>
                                    <div>
                                        <strong id="pregnant-packets">35 packets</strong>
                                        <i class="fas fa-edit edit-icon"></i>
                                    </div>
                                </div>
                                <div class="summary-item editable-summary" onclick="editCategorySummary('lactating')">
                                    <span>Lactating Mothers:</span>
                                    <div>
                                        <strong id="lactating-packets">42 packets</strong>
                                        <i class="fas fa-edit edit-icon"></i>
                                    </div>
                                </div>
                                <div class="summary-item editable-summary" onclick="editCategorySummary('children')">
                                    <span>Children (6-23m):</span>
                                    <div>
                                        <strong id="children-packets">21 packets</strong>
                                        <i class="fas fa-edit edit-icon"></i>
                                    </div>
                                </div>
                                <hr>
                                <div class="summary-item">
                                    <span><strong>Total Distributed:</strong></span>
                                    <strong class="text-success" id="total-distributed-summary">98 packets</strong>
                                </div>
                                <div class="summary-item">
                                    <span><strong>Remaining:</strong></span>
                                    <strong class="text-warning" id="remaining-packets">77 packets</strong>
                                </div>
                            </div>
                        </div>
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

    <script src="../js/page-transitions.js"></script>
    <script src="../js/theme-toggle.js"></script>
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

        function checkAuthentication() {
            const midwifeUser = localStorage.getItem('midwife_user');
            if (!midwifeUser) {
                window.location.href = '../midwife-login.html';
                return;
            }
            
            const user = JSON.parse(midwifeUser);
            document.querySelector('.midwife-name').textContent = user.name || 'Midwife User';
        }

        
        function loadDashboardWidgets() {
            // Static data for dashboard widgets
            const widgetData = {
                urgent_meetings: [
                    {
                        description: "High-risk pregnancy follow-up",
                        scheduled_date: "31 Mar 2026",
                        start_time: "09:00",
                        location: "MOH Clinic - Room 3"
                    },
                    {
                        description: "Postnatal emergency review",
                        scheduled_date: "31 Mar 2026",
                        start_time: "11:30",
                        location: "Uduthuththiripitiya CHC"
                    }
                ],
                upcoming_clinics: [
                    {
                        description: "Antenatal Clinic - Routine Check",
                        scheduled_date: "01 Apr 2026",
                        start_time: "08:30",
                        location: "Kahabilihena RH"
                    },
                    {
                        description: "Child Growth Monitoring",
                        scheduled_date: "02 Apr 2026",
                        start_time: "09:00",
                        location: "Opathella PHC"
                    },
                    {
                        description: "Family Planning Session",
                        scheduled_date: "03 Apr 2026",
                        start_time: "10:00",
                        location: "Ambalangoda DH"
                    }
                ],
                timetable: [
                    {
                        start_time: "08:00",
                        estimated_end_time: "09:00",
                        description: "Home Visit - Mrs. K. Silva",
                        location: "Uduthuththiripitiya Village",
                        patient_name: "Pregnant - 32 weeks"
                    },
                    {
                        start_time: "10:00",
                        estimated_end_time: "10:30",
                        description: "Vaccination Session",
                        location: "MOH Clinic",
                        patient_name: "Pediatric vaccines"
                    },
                    {
                        start_time: "14:00",
                        estimated_end_time: "15:00",
                        description: "Counseling - Breastfeeding support",
                        location: "Community Center",
                        patient_name: "Mrs. A. Fernando"
                    }
                ],
                notifications: [
                    {
                        title: "Vaccine Stock Alert",
                        message: "DPT-3 vaccine stock is running low. Please reorder before Friday."
                    },
                    {
                        title: "Monthly Report Due",
                        message: "Submit your monthly activity report by April 5th."
                    },
                    {
                        title: "Training Session",
                        message: "Newborn care training scheduled for April 2nd at 2 PM."
                    }
                ]
            };

            const d = widgetData;

            // Urgent Meetings
            const wu = document.getElementById('widget-urgent');
            if (d.urgent_meetings.length === 0) {
                wu.innerHTML = '<div style="text-align: center; color: var(--text-muted); padding: 1rem;">No urgent meetings.</div>';
            } else {
                wu.innerHTML = d.urgent_meetings.map(m => `
                    <div style="padding: 0.75rem; border-bottom: 1px solid #e9ecef;">
                        <div style="font-weight: 600; color: var(--text-primary);">${m.description}</div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">
                            <i class="fas fa-calendar-alt"></i> ${m.scheduled_date} ${m.start_time} | <i class="fas fa-map-marker-alt"></i> ${m.location}
                        </div>
                    </div>
                `).join('');
            }

            // Clinics
            const wc = document.getElementById('widget-clinics');
            if (d.upcoming_clinics.length === 0) {
                wc.innerHTML = '<div style="text-align: center; color: var(--text-muted); padding: 1rem;">No upcoming clinics scheduled.</div>';
            } else {
                wc.innerHTML = d.upcoming_clinics.map(m => `
                    <div style="padding: 0.75rem; border-bottom: 1px solid #e9ecef;">
                        <div style="font-weight: 600; color: var(--text-primary);">${m.description}</div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">
                            <i class="fas fa-calendar-alt"></i> ${m.scheduled_date} ${m.start_time} | <i class="fas fa-map-marker-alt"></i> ${m.location}
                        </div>
                    </div>
                `).join('');
            }

            // Time Table
            const wt = document.getElementById('widget-timetable');
            if (d.timetable.length === 0) {
                wt.innerHTML = '<div style="text-align: center; color: var(--text-muted); padding: 1rem;">No schedule for today.</div>';
            } else {
                wt.innerHTML = d.timetable.map(m => `
                    <div style="padding: 0.75rem; border-left: 4px solid var(--secondary-green); margin-bottom: 0.5rem; background: #f8f9fa;">
                        <div style="font-weight: 600; color: var(--text-primary);">${m.start_time} - ${m.estimated_end_time}</div>
                        <div style="font-size: 0.9rem; color: var(--text-dark); margin-top: 0.15rem;">${m.description}</div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary);">${m.patient_name ? m.patient_name + ' | ' : ''}${m.location}</div>
                    </div>
                `).join('');
            }

            // Notifications
            const wn = document.getElementById('widget-notifications');
            if (d.notifications.length === 0) {
                wn.innerHTML = '<div style="text-align: center; color: var(--text-muted); padding: 1rem;">No new notifications.</div>';
            } else {
                wn.innerHTML = d.notifications.map(n => `
                    <div style="padding: 0.75rem; border-bottom: 1px solid #e9ecef; position: relative;">
                        <div style="font-weight: 600; color: var(--text-primary);">${n.title}</div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">${n.message}</div>
                        <span class="badge" style="position: absolute; top: 0.75rem; right: 0.75rem; background: var(--accent-orange);">New</span>
                    </div>
                `).join('');
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

        function addScheduleItem() {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Add New Schedule Item</h3>
                        <button onclick="closeModal()" class="close-btn">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="scheduleItemForm">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Date *</label>
                                        <input type="date" class="form-control" name="scheduleDate" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Time *</label>
                                        <input type="time" class="form-control" name="scheduleTime" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Activity Type *</label>
                                        <select class="form-control" name="activityType" required>
                                            <option value="">Select Type</option>
                                            <option value="home-visit">Home Visit</option>
                                            <option value="clinic">Clinic Session</option>
                                            <option value="vaccination">Vaccination</option>
                                            <option value="counseling">Counseling</option>
                                            <option value="meeting">Meeting</option>
                                            <option value="training">Training</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Duration (minutes)</label>
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
                                <label class="form-label">Title/Description *</label>
                                <input type="text" class="form-control" name="title" placeholder="Brief description of the activity" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control" name="location" placeholder="Where will this take place?">
                            </div>
                            <div class="row">
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
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Duty Area</label>
                                        <select class="form-control" name="dutyArea">
                                            <option value="uduthuththiripitiya">Uduthuththiripitiya</option>
                                            <option value="kahabilihena">Kahabilihena</option>
                                            <option value="opathella">Opathella</option>
                                            <option value="ambalangoda">Ambalangoda</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" rows="3" placeholder="Additional notes or instructions"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                        <button type="submit" form="scheduleItemForm" class="btn btn-primary">Add to Schedule</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            // Set default date to today
            const today = new Date().toISOString().split('T')[0];
            document.querySelector('input[name="scheduleDate"]').value = today;

            document.getElementById('scheduleItemForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(e.target);
                const scheduleData = Object.fromEntries(formData);
                alert('Schedule item added successfully!\\n' +
                      'Date: ' + scheduleData.scheduleDate + '\\n' +
                      'Time: ' + scheduleData.scheduleTime + '\\n' +
                      'Activity: ' + scheduleData.title);
                closeModal();
                // Here you would typically save to database or localStorage
            });
        }

        function showMyTimetable() {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content" style="max-width: 900px;">
                    <div class="modal-header">
                        <h3>My Timetable</h3>
                        <button onclick="closeModal()" class="close-btn">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="timetable-controls mb-3">
                            <div class="d-flex justify-content-start gap-2 mb-3" role="tablist">
                                <button class="btn btn-outline-primary active" id="tab-year" onclick="setTimetableTab('year')" type="button">Year</button>
                                <button class="btn btn-outline-primary" id="tab-month" onclick="setTimetableTab('month')" type="button">Month</button>
                                <button class="btn btn-outline-primary" id="tab-day" onclick="setTimetableTab('day')" type="button">Day</button>
                            </div>

                            <div class="row" id="timetable-selectors">
                                <div class="col-4">
                                    <label class="form-label">Year</label>
                                    <select class="form-control" id="timetableYear" onchange="syncTimetableInputs(); loadTimetableData()">
                                        <option value="2024">2024</option>
                                        <option value="2025">2025</option>
                                        <option value="2026" selected>2026</option>
                                        <option value="2027">2027</option>
                                        <option value="2028">2028</option>
                                    </select>
                                </div>
                                <div class="col-4" id="timetable-month-wrapper">
                                    <label class="form-label">Month</label>
                                    <select class="form-control" id="timetableMonth" onchange="syncTimetableInputs(); loadTimetableData()">
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
                                    <select class="form-control" id="timetableDay" onchange="syncTimetableInputs(); loadTimetableData()">
                                        ${Array.from({ length: 31 }, (_, idx) => `<option value="${String(idx + 1).padStart(2, '0')}" ${idx + 1 === 27 ? 'selected' : ''}>${idx + 1}</option>`).join('')}
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="timetableContent">
                            <!-- Timetable will be loaded here -->
                            <div id="timetableHeader" class="timetable-header" style="display: none;">
                                <h4 id="monthTitle"></h4>
                            </div>
                        </div>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Close</button>
                        <button type="button" class="btn btn-primary" onclick="editTimetable()">Edit Timetable</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            // Set default selected values
            document.getElementById('timetableYear').value = '2026';
            document.getElementById('timetableMonth').value = '03';
            document.getElementById('timetableDay').value = '27';
            setTimetableTab('month');

            // Load initial timetable data
            loadTimetableData();
        }

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

        function loadTimetableData() {
            const selectedYear = document.getElementById('timetableYear').value;
            const selectedMonth = document.getElementById('timetableMonth').value;
            const selectedDay = document.getElementById('timetableDay').value;
            const selectedDate = `${selectedYear}-${selectedMonth}-${selectedDay}`;

            // Sample timetable data for each month of 2026 - in real implementation, this would come from database
            const sampleData = {
                '2026-01': [ // January
                    { date: '2026-01-05', time: '09:00', activity: 'Home Visit - Mrs. Silva (Postnatal)', type: 'home-visit', duration: '45 min', location: 'Uduthuththiripitiya', priority: 'high' },
                    { date: '2026-01-07', time: '10:30', activity: 'Antenatal Clinic', type: 'clinic', duration: '2 hours', location: 'PHM Office', priority: 'normal' },
                    { date: '2026-01-12', time: '14:00', activity: 'Vaccination Session - BCG', type: 'vaccination', duration: '1.5 hours', location: 'Clinic Center', priority: 'normal' },
                    { date: '2026-01-15', time: '09:30', activity: 'Home Visit - Mrs. Perera (28 weeks)', type: 'home-visit', duration: '40 min', location: 'Kahabilihena', priority: 'normal' },
                    { date: '2026-01-20', time: '11:00', activity: 'Family Planning Counseling', type: 'counseling', duration: '30 min', location: 'Community Center', priority: 'normal' },
                    { date: '2026-01-25', time: '15:00', activity: 'Emergency Home Visit', type: 'home-visit', duration: '1 hour', location: 'Opathella', priority: 'urgent' },
                    { date: '2026-01-28', time: '10:00', activity: 'Nutrition Education Workshop', type: 'training', duration: '2 hours', location: 'Ambalangoda', priority: 'normal' }
                ],
                '2026-02': [ // February
                    { date: '2026-02-03', time: '09:15', activity: 'Home Visit - Mrs. Fernando (Postnatal)', type: 'home-visit', duration: '50 min', location: 'Uduthuththiripitiya', priority: 'high' },
                    { date: '2026-02-06', time: '11:00', activity: 'Prenatal Checkup Clinic', type: 'clinic', duration: '2.5 hours', location: 'PHM Office', priority: 'normal' },
                    { date: '2026-02-10', time: '13:30', activity: 'DPT Vaccination Campaign', type: 'vaccination', duration: '1 hour', location: 'Clinic Center', priority: 'high' },
                    { date: '2026-02-14', time: '10:00', activity: 'Home Visit - Mrs. Wickramasinghe (32 weeks)', type: 'home-visit', duration: '45 min', location: 'Kahabilihena', priority: 'normal' },
                    { date: '2026-02-18', time: '14:30', activity: 'Maternal Health Seminar', type: 'training', duration: '1.5 hours', location: 'Community Center', priority: 'normal' },
                    { date: '2026-02-22', time: '09:45', activity: 'Follow-up Visit - High Risk Case', type: 'home-visit', duration: '1 hour', location: 'Opathella', priority: 'urgent' },
                    { date: '2026-02-26', time: '15:30', activity: 'Monthly Staff Meeting', type: 'meeting', duration: '45 min', location: 'PHM Office', priority: 'normal' }
                ],
                '2026-03': [ // March
                    { date: '2026-03-02', time: '09:00', activity: 'Home Visit - Mrs. Rajapaksa (Newborn)', type: 'home-visit', duration: '45 min', location: 'Uduthuththiripitiya', priority: 'urgent' },
                    { date: '2026-03-05', time: '10:30', activity: 'Well Baby Clinic', type: 'clinic', duration: '2 hours', location: 'PHM Office', priority: 'normal' },
                    { date: '2026-03-10', time: '14:00', activity: 'Measles Vaccination Drive', type: 'vaccination', duration: '1.5 hours', location: 'Clinic Center', priority: 'high' },
                    { date: '2026-03-15', time: '11:15', activity: 'Home Visit - Mrs. Kumari (36 weeks)', type: 'home-visit', duration: '40 min', location: 'Kahabilihena', priority: 'high' },
                    { date: '2026-03-20', time: '13:45', activity: 'Breastfeeding Support Group', type: 'counseling', duration: '1 hour', location: 'Community Center', priority: 'normal' },
                    { date: '2026-03-25', time: '09:30', activity: 'Emergency Prenatal Care', type: 'home-visit', duration: '1.5 hours', location: 'Opathella', priority: 'urgent' },
                    { date: '2026-03-28', time: '15:00', activity: 'Health Education Session', type: 'training', duration: '1.5 hours', location: 'Ambalangoda', priority: 'normal' }
                ],
                '2026-04': [ // April
                    { date: '2026-04-01', time: '09:30', activity: 'Home Visit - Mrs. Sanduni (Postnatal)', type: 'home-visit', duration: '45 min', location: 'Uduthuththiripitiya', priority: 'normal' },
                    { date: '2026-04-05', time: '11:00', activity: 'Growth Monitoring Clinic', type: 'clinic', duration: '2.5 hours', location: 'PHM Office', priority: 'normal' },
                    { date: '2026-04-10', time: '14:30', activity: 'Polio Vaccination Round', type: 'vaccination', duration: '1 hour', location: 'Clinic Center', priority: 'high' },
                    { date: '2026-04-15', time: '10:15', activity: 'Home Visit - Mrs. Chamika (24 weeks)', type: 'home-visit', duration: '35 min', location: 'Kahabilihena', priority: 'normal' },
                    { date: '2026-04-20', time: '13:00', activity: 'Contraceptive Counseling', type: 'counseling', duration: '45 min', location: 'Community Center', priority: 'normal' },
                    { date: '2026-04-25', time: '09:45', activity: 'High Risk Pregnancy Monitoring', type: 'home-visit', duration: '1 hour', location: 'Opathella', priority: 'urgent' },
                    { date: '2026-04-28', time: '15:30', activity: 'Nutrition Workshop', type: 'training', duration: '1.5 hours', location: 'Ambalangoda', priority: 'normal' }
                ],
                '2026-05': [ // May
                    { date: '2026-05-03', time: '09:00', activity: 'Home Visit - Mrs. Nirmala (Newborn)', type: 'home-visit', duration: '50 min', location: 'Uduthuththiripitiya', priority: 'high' },
                    { date: '2026-05-07', time: '10:45', activity: 'Immunization Clinic', type: 'clinic', duration: '2 hours', location: 'PHM Office', priority: 'normal' },
                    { date: '2026-05-12', time: '14:15', activity: 'Vitamin A Supplementation', type: 'vaccination', duration: '1.5 hours', location: 'Clinic Center', priority: 'high' },
                    { date: '2026-05-17', time: '11:30', activity: 'Home Visit - Mrs. Kamani (28 weeks)', type: 'home-visit', duration: '40 min', location: 'Kahabilihena', priority: 'normal' },
                    { date: '2026-05-22', time: '13:45', activity: 'Postpartum Depression Screening', type: 'counseling', duration: '1 hour', location: 'Community Center', priority: 'normal' },
                    { date: '2026-05-27', time: '09:15', activity: 'Emergency Delivery Assistance', type: 'home-visit', duration: '2 hours', location: 'Opathella', priority: 'urgent' },
                    { date: '2026-05-30', time: '15:00', activity: 'Child Development Workshop', type: 'training', duration: '1.5 hours', location: 'Ambalangoda', priority: 'normal' }
                ],
                '2026-06': [ // June
                    { date: '2026-06-02', time: '09:30', activity: 'Home Visit - Mrs. Sandya (Postnatal)', type: 'home-visit', duration: '45 min', location: 'Uduthuththiripitiya', priority: 'normal' },
                    { date: '2026-06-06', time: '11:15', activity: 'School Health Program', type: 'clinic', duration: '2.5 hours', location: 'PHM Office', priority: 'normal' },
                    { date: '2026-06-11', time: '14:45', activity: 'MMR Vaccination Campaign', type: 'vaccination', duration: '1 hour', location: 'Clinic Center', priority: 'high' },
                    { date: '2026-06-16', time: '10:00', activity: 'Home Visit - Mrs. Priyanka (32 weeks)', type: 'home-visit', duration: '45 min', location: 'Kahabilihena', priority: 'high' },
                    { date: '2026-06-21', time: '13:30', activity: 'Adolescent Health Education', type: 'counseling', duration: '1.5 hours', location: 'Community Center', priority: 'normal' },
                    { date: '2026-06-26', time: '09:45', activity: 'Multiple Pregnancy Monitoring', type: 'home-visit', duration: '1 hour', location: 'Opathella', priority: 'urgent' },
                    { date: '2026-06-29', time: '15:30', time: '16:00', activity: 'Staff Training Session', type: 'training', duration: '2 hours', location: 'PHM Office', priority: 'normal' }
                ],
                '2026-07': [ // July
                    { date: '2026-07-01', time: '09:00', activity: 'Home Visit - Mrs. Madhavi (Newborn)', type: 'home-visit', duration: '50 min', location: 'Uduthuththiripitiya', priority: 'high' },
                    { date: '2026-07-05', time: '10:30', activity: 'Maternal & Child Health Clinic', type: 'clinic', duration: '2 hours', location: 'PHM Office', priority: 'normal' },
                    { date: '2026-07-10', time: '14:00', activity: 'Hepatitis B Vaccination', type: 'vaccination', duration: '1.5 hours', location: 'Clinic Center', priority: 'high' },
                    { date: '2026-07-15', time: '11:45', activity: 'Home Visit - Mrs. Kumari (36 weeks)', type: 'home-visit', duration: '40 min', location: 'Kahabilihena', priority: 'urgent' },
                    { date: '2026-07-20', time: '13:15', activity: 'Family Planning Workshop', type: 'counseling', duration: '1 hour', location: 'Community Center', priority: 'normal' },
                    { date: '2026-07-25', time: '09:30', activity: 'Preterm Labor Assessment', type: 'home-visit', duration: '1.5 hours', location: 'Opathella', priority: 'urgent' },
                    { date: '2026-07-28', time: '15:00', activity: 'Emergency Preparedness Training', type: 'training', duration: '1.5 hours', location: 'Ambalangoda', priority: 'normal' }
                ],
                '2026-08': [ // August
                    { date: '2026-08-03', time: '09:15', activity: 'Home Visit - Mrs. Sanduni (Postnatal)', type: 'home-visit', duration: '45 min', location: 'Uduthuththiripitiya', priority: 'normal' },
                    { date: '2026-08-07', time: '11:00', activity: 'Nutrition Assessment Clinic', type: 'clinic', duration: '2.5 hours', location: 'PHM Office', priority: 'normal' },
                    { date: '2026-08-12', time: '14:30', activity: 'Japanese Encephalitis Vaccination', type: 'vaccination', duration: '1 hour', location: 'Clinic Center', priority: 'high' },
                    { date: '2026-08-17', time: '10:45', activity: 'Home Visit - Mrs. Chamika (28 weeks)', type: 'home-visit', duration: '35 min', location: 'Kahabilihena', priority: 'normal' },
                    { date: '2026-08-22', time: '13:00', activity: 'Mental Health Awareness', type: 'counseling', duration: '45 min', location: 'Community Center', priority: 'normal' },
                    { date: '2026-08-27', time: '09:15', activity: 'Gestational Diabetes Monitoring', type: 'home-visit', duration: '1 hour', location: 'Opathella', priority: 'urgent' },
                    { date: '2026-08-30', time: '15:30', activity: 'Infant Care Workshop', type: 'training', duration: '1.5 hours', location: 'Ambalangoda', priority: 'normal' }
                ],
                '2026-09': [ // September
                    { date: '2026-09-02', time: '09:00', activity: 'Home Visit - Mrs. Nirmala (Newborn)', type: 'home-visit', duration: '50 min', location: 'Uduthuththiripitiya', priority: 'high' },
                    { date: '2026-09-06', time: '10:30', activity: 'Developmental Assessment Clinic', type: 'clinic', duration: '2 hours', location: 'PHM Office', priority: 'normal' },
                    { date: '2026-09-11', time: '14:15', activity: 'DPT Booster Campaign', type: 'vaccination', duration: '1.5 hours', location: 'Clinic Center', priority: 'high' },
                    { date: '2026-09-16', time: '11:30', activity: 'Home Visit - Mrs. Kamani (32 weeks)', type: 'home-visit', duration: '40 min', location: 'Kahabilihena', priority: 'normal' },
                    { date: '2026-09-21', time: '13:45', activity: 'Domestic Violence Support', type: 'counseling', duration: '1 hour', location: 'Community Center', priority: 'normal' },
                    { date: '2026-09-26', time: '09:45', activity: 'Preeclampsia Screening', type: 'home-visit', duration: '1.5 hours', location: 'Opathella', priority: 'urgent' },
                    { date: '2026-09-29', time: '15:00', activity: 'First Aid Training', type: 'training', duration: '2 hours', location: 'Ambalangoda', priority: 'normal' }
                ],
                '2026-10': [ // October
                    { date: '2026-10-01', time: '09:30', activity: 'Home Visit - Mrs. Sandya (Postnatal)', type: 'home-visit', duration: '45 min', location: 'Uduthuththiripitiya', priority: 'normal' },
                    { date: '2026-10-05', time: '11:15', activity: 'Oral Health Clinic', type: 'clinic', duration: '2.5 hours', location: 'PHM Office', priority: 'normal' },
                    { date: '2026-10-10', time: '14:45', activity: 'Influenza Vaccination', type: 'vaccination', duration: '1 hour', location: 'Clinic Center', priority: 'high' },
                    { date: '2026-10-15', time: '10:00', activity: 'Home Visit - Mrs. Priyanka (36 weeks)', type: 'home-visit', duration: '45 min', location: 'Kahabilihena', priority: 'urgent' },
                    { date: '2026-10-20', time: '13:30', activity: 'HIV/AIDS Awareness', type: 'counseling', duration: '1.5 hours', location: 'Community Center', priority: 'normal' },
                    { date: '2026-10-25', time: '09:15', activity: 'Anemia Treatment Follow-up', type: 'home-visit', duration: '1 hour', location: 'Opathella', priority: 'urgent' },
                    { date: '2026-10-28', time: '15:30', activity: 'Community Health Meeting', type: 'meeting', duration: '1 hour', location: 'PHM Office', priority: 'normal' }
                ],
                '2026-11': [ // November
                    { date: '2026-11-03', time: '09:00', activity: 'Home Visit - Mrs. Madhavi (Newborn)', type: 'home-visit', duration: '50 min', location: 'Uduthuththiripitiya', priority: 'high' },
                    { date: '2026-11-07', time: '10:45', activity: 'Eye Health Screening', type: 'clinic', duration: '2 hours', location: 'PHM Office', priority: 'normal' },
                    { date: '2026-11-12', time: '14:00', activity: 'Typhoid Vaccination', type: 'vaccination', duration: '1.5 hours', location: 'Clinic Center', priority: 'high' },
                    { date: '2026-11-17', time: '11:30', activity: 'Home Visit - Mrs. Kumari (28 weeks)', type: 'home-visit', duration: '40 min', location: 'Kahabilihena', priority: 'normal' },
                    { date: '2026-11-22', time: '13:15', activity: 'Reproductive Health Education', type: 'counseling', duration: '1 hour', location: 'Community Center', priority: 'normal' },
                    { date: '2026-11-27', time: '09:30', activity: 'Thyroid Disorder Monitoring', type: 'home-visit', duration: '1.5 hours', location: 'Opathella', priority: 'urgent' },
                    { date: '2026-11-30', time: '15:00', activity: 'Disaster Preparedness Workshop', type: 'training', duration: '1.5 hours', location: 'Ambalangoda', priority: 'normal' }
                ],
                '2026-12': [ // December
                    { date: '2026-12-02', time: '09:15', activity: 'Home Visit - Mrs. Sanduni (Postnatal)', type: 'home-visit', duration: '45 min', location: 'Uduthuththiripitiya', priority: 'normal' },
                    { date: '2026-12-06', time: '11:00', activity: 'Year-End Health Review', type: 'clinic', duration: '2.5 hours', location: 'PHM Office', priority: 'normal' },
                    { date: '2026-12-11', time: '14:30', activity: 'COVID-19 Booster Campaign', type: 'vaccination', duration: '1 hour', location: 'Clinic Center', priority: 'high' },
                    { date: '2026-12-16', time: '10:45', activity: 'Home Visit - Mrs. Chamika (32 weeks)', type: 'home-visit', duration: '35 min', location: 'Kahabilihena', priority: 'normal' },
                    { date: '2026-12-21', time: '13:00', activity: 'Holiday Health Safety', type: 'counseling', duration: '45 min', location: 'Community Center', priority: 'normal' },
                    { date: '2026-12-26', time: '09:45', activity: 'Post-Holiday Health Check', type: 'home-visit', duration: '1 hour', location: 'Opathella', priority: 'urgent' },
                    { date: '2026-12-29', time: '15:30', activity: 'Annual Performance Review', type: 'meeting', duration: '1.5 hours', location: 'PHM Office', priority: 'normal' }
                ]
            };

            let data = [];
            let viewType = '';

            if (currentTimetableTab === 'year') {
                Object.values(sampleData).forEach(monthItems => data.push(...monthItems));
                viewType = 'year';
            } else if (currentTimetableTab === 'month') {
                const monthKey = selectedMonth || '2026-03';
                data = sampleData[monthKey] || [];
                viewType = monthKey;
            } else {
                if (selectedDate) {
                    data = Object.values(sampleData).flat().filter(item => item.date === selectedDate);
                } else {
                    data = [];
                }
                viewType = 'day';
            }

            renderTimetable(data, viewType);
            updateTimetableSummary(data);
        }

        function renderTimetable(data, viewType) {
            const content = document.getElementById('timetableContent');
            const header = document.getElementById('timetableHeader');
            const monthTitle = document.getElementById('monthTitle');

            // Show month header for monthly views
            if (viewType && viewType.startsWith('2026-')) {
                const monthNames = {
                    '2026-01': 'January 2026',
                    '2026-02': 'February 2026',
                    '2026-03': 'March 2026',
                    '2026-04': 'April 2026',
                    '2026-05': 'May 2026',
                    '2026-06': 'June 2026',
                    '2026-07': 'July 2026',
                    '2026-08': 'August 2026',
                    '2026-09': 'September 2026',
                    '2026-10': 'October 2026',
                    '2026-11': 'November 2026',
                    '2026-12': 'December 2026'
                };
                monthTitle.textContent = monthNames[viewType] || 'Timetable';
                header.style.display = 'block';
            } else {
                header.style.display = 'none';
            }

            if (data.length === 0) {
                content.innerHTML = '<div class="text-center text-muted"><i class="fas fa-calendar-times fa-3x mb-3"></i><p>No scheduled activities found for this month.</p></div>';
                return;
            }

            let html = '<div class="timetable-list">';

            data.forEach((item, index) => {
                const activityIcon = getActivityIcon(item.type);
                const priorityClass = item.priority === 'urgent' ? 'priority-high' :
                                    item.priority === 'high' ? 'priority-normal' : 'priority-low';

                html += `
                    <div class="timetable-item ${priorityClass}">
                        <div class="timetable-time">
                            <div class="time">${item.time}</div>
                            <div class="date">${formatDate(item.date)}</div>
                        </div>
                        <div class="timetable-details">
                            <h6><i class="${activityIcon}"></i> ${item.activity}</h6>
                            <p class="location"><i class="fas fa-map-marker-alt"></i> ${item.location}</p>
                            <p class="duration"><i class="fas fa-clock"></i> ${item.duration}</p>
                        </div>
                        <div class="timetable-actions">
                            <button class="btn btn-sm btn-info" onclick="viewTimetableItem(${index})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="editTimetableItem(${index})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteTimetableItem(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            content.innerHTML = html;
        }

        function updateTimetableSummary(data) {
            const totalActivities = data.length;
            const homeVisits = data.filter(item => item.type === 'home-visit').length;
            const clinicSessions = data.filter(item => item.type === 'clinic').length;
            const totalHours = data.reduce((sum, item) => {
                const duration = parseInt(item.duration.split(' ')[0]);
                return sum + (item.duration.includes('hour') ? duration : duration / 60);
            }, 0);

            document.getElementById('totalActivities').textContent = totalActivities;
            document.getElementById('homeVisits').textContent = homeVisits;
            document.getElementById('clinicSessions').textContent = clinicSessions;
            document.getElementById('totalHours').textContent = totalHours.toFixed(1);
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
            return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
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
        
        async function loadHomeVisits() {
            const user = JSON.parse(localStorage.getItem('midwife_user') || '{}');
            const midwifeId = user.id || 1;
            
            try {
                const response = await fetch(`../php/home_visits.php?action=get&midwife_id=${midwifeId}&area=${currentArea}`);
                const result = await response.json();
                
                if (result.success) {
                    currentHomeVisits = result.data;
                    renderHomeVisits();
                    renderCompletedVisits();
                    updateHomeVisitStats();
                }
            } catch (error) {
                console.error('Error loading home visits:', error);
            }
        }
        
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
        
        async function completeVisit(visitId) {
            const notes = prompt('Enter visit notes/summary:');
            if (notes === null) return;
            
            try {
                const response = await fetch(`../php/home_visits.php?action=complete&id=${visitId}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ notes: notes, end_time: new Date().toTimeString().slice(0, 5) })
                });
                const result = await response.json();
                
                if (result.success) {
                    alert('Visit marked as completed!');
                    await loadHomeVisits();
                    switchVisitTab('completed');
                }
            } catch (error) {
                console.error('Error completing visit:', error);
                alert('Error completing visit');
            }
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
        
        function scheduleNewVisit() {
            const user = JSON.parse(localStorage.getItem('midwife_user') || '{}');
            
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Schedule New Home Visit</h3>
                        <button onclick="closeModal()" class="close-btn">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="newVisitForm">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Patient Name *</label>
                                        <input type="text" class="form-control" id="visit_patient_name" placeholder="Enter patient name" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Contact Number</label>
                                        <input type="tel" class="form-control" id="visit_contact" placeholder="+94 XX XXX XXXX">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Address *</label>
                                <input type="text" class="form-control" id="visit_address" placeholder="Enter patient address" required>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Visit Type *</label>
                                        <select class="form-control" id="visit_type" required>
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
                                        <select class="form-control" id="visit_priority">
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
                                        <input type="date" class="form-control" id="visit_date" required>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label class="form-label">Time *</label>
                                        <input type="time" class="form-control" id="visit_time" required>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label class="form-label">Duration (min)</label>
                                        <input type="number" class="form-control" id="visit_duration" value="45" min="15" max="180">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Reason for Visit</label>
                                <textarea class="form-control" id="visit_reason" rows="2" placeholder="Reason for the home visit..."></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" id="visit_notes" rows="2" placeholder="Additional notes..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="saveNewVisit()">Schedule Visit</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            
            document.getElementById('visit_date').valueAsDate = new Date();
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
                    headers: { 'Content-Type': 'application/json' },
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
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ notes: notes, end_time: endTime })
                    });
                } else {
                    response = await fetch(`../php/home_visits.php?action=update&id=${visitId}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ status: status, notes: notes })
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

        function switchVisitTab(tabName, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            const navLinks = document.querySelectorAll('#home-visits .tab-container .nav-link');
            navLinks.forEach(link => link.classList.remove('active'));

            const tabContents = document.querySelectorAll('#home-visits .tab-content');
            tabContents.forEach(content => content.style.display = 'none');

            const selectedTab = document.getElementById(tabName + '-visits');
            if (selectedTab) {
                selectedTab.style.display = 'block';
            }

            let activeLink = event?.target;
            if (!activeLink) {
                activeLink = document.querySelector(`#home-visits .tab-container .nav-link[onclick*="switchVisitTab('${tabName}'"]`);
            }
            if (activeLink) {
                activeLink.classList.add('active');
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

        // Vaccination Management Functions
        function scheduleVaccination() {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Schedule New Vaccination</h3>
                        <button onclick="closeModal()" class="close-btn">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="newVaccinationForm">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Patient *</label>
                                        <select class="form-control" required>
                                            <option value="">Select Patient</option>
                                            <option value="1">Baby Amara Silva (4 months)</option>
                                            <option value="2">Mrs. Nayani Perera (Pregnant)</option>
                                            <option value="3">Baby Sahan Fernando (6 months)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Vaccine Category *</label>
                                        <select class="form-control" required onchange="updateVaccineOptions(this.value)">
                                            <option value="">Select Category</option>
                                            <option value="pediatric">Pediatric Vaccines</option>
                                            <option value="maternal">Maternal Vaccines</option>
                                            <option value="adult">Adult Vaccines</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Vaccines to Administer *</label>
                                <div class="vaccine-checkboxes" id="vaccineOptions">
                                    <p>Select a category first</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Date *</label>
                                        <input type="date" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Time *</label>
                                        <input type="time" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Special Instructions</label>
                                <textarea class="form-control" rows="3" placeholder="Any special instructions or notes..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                        <button type="submit" form="newVaccinationForm" class="btn btn-primary">Schedule Vaccination</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            document.getElementById('newVaccinationForm').addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Vaccination scheduled successfully!');
                closeModal();
            });
        }

        function updateVaccineOptions(category) {
            const vaccineOptions = document.getElementById('vaccineOptions');
            let options = '';

            if (category === 'pediatric') {
                options = `
                    <label><input type="checkbox" value="dpt"> DPT (Diphtheria, Pertussis, Tetanus)</label>
                    <label><input type="checkbox" value="opv"> OPV (Oral Polio Vaccine)</label>
                    <label><input type="checkbox" value="hepatitis-b"> Hepatitis B</label>
                    <label><input type="checkbox" value="mmr"> MMR (Measles, Mumps, Rubella)</label>
                    <label><input type="checkbox" value="hib"> Hib (Haemophilus influenzae type b)</label>
                `;
            } else if (category === 'maternal') {
                options = `
                    <label><input type="checkbox" value="tetanus-toxoid"> Tetanus Toxoid</label>
                    <label><input type="checkbox" value="influenza"> Influenza</label>
                    <label><input type="checkbox" value="pertussis"> Tdap (Tetanus, Diphtheria, Pertussis)</label>
                `;
            } else if (category === 'adult') {
                options = `
                    <label><input type="checkbox" value="influenza"> Influenza</label>
                    <label><input type="checkbox" value="pneumococcal"> Pneumococcal</label>
                    <label><input type="checkbox" value="hepatitis-b"> Hepatitis B</label>
                    <label><input type="checkbox" value="tetanus"> Tetanus/Diphtheria</label>
                `;
            }

            vaccineOptions.innerHTML = options;
        }

        function quickVaccinationLog() {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Quick Vaccination Log</h3>
                        <button onclick="closeModal()" class="close-btn">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="quickVaccineLogForm">
                            <div class="form-group">
                                <label class="form-label">Patient *</label>
                                <select class="form-control" required>
                                    <option value="">Select from today's schedule</option>
                                    <option value="1">Baby Kamal Silva - DPT-1, OPV-1, Hep B-1</option>
                                    <option value="2">Mrs. Priyanka Fernando - Tetanus Toxoid</option>
                                    <option value="3">Mrs. Kumari Wickramasinghe - Influenza, Pneumococcal</option>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Administration Status *</label>
                                        <select class="form-control" required>
                                            <option value="completed">All vaccines administered</option>
                                            <option value="partial">Partially administered</option>
                                            <option value="refused">Patient refused</option>
                                            <option value="contraindicated">Medically contraindicated</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Adverse Reaction</label>
                                        <select class="form-control">
                                            <option value="none">No reaction</option>
                                            <option value="mild">Mild (local redness/swelling)</option>
                                            <option value="moderate">Moderate (fever, irritability)</option>
                                            <option value="severe">Severe (requires follow-up)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Batch Numbers</label>
                                        <input type="text" class="form-control" placeholder="Enter batch numbers">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Next Appointment</label>
                                        <input type="date" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" rows="3" placeholder="Any observations or notes about the vaccination..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                        <button type="submit" form="quickVaccineLogForm" class="btn btn-success">Log Vaccination</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            document.getElementById('quickVaccineLogForm').addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Vaccination logged successfully!');
                closeModal();
            });
        }

        function updateInventory() {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Update Vaccine Inventory</h3>
                        <button onclick="closeModal()" class="close-btn">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="inventoryUpdateForm">
                            <div class="form-group">
                                <label class="form-label">Vaccine Type *</label>
                                <select class="form-control" required>
                                    <option value="">Select Vaccine</option>
                                    <option value="dpt">DPT Vaccine</option>
                                    <option value="opv">OPV (Oral Polio)</option>
                                    <option value="hepatitis-b">Hepatitis B</option>
                                    <option value="tetanus">Tetanus Toxoid</option>
                                    <option value="mmr">MMR Vaccine</option>
                                    <option value="influenza">Influenza</option>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Action Type *</label>
                                        <select class="form-control" required>
                                            <option value="received">Stock Received</option>
                                            <option value="used">Stock Used</option>
                                            <option value="expired">Mark as Expired</option>
                                            <option value="adjustment">Stock Adjustment</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Quantity *</label>
                                        <input type="number" class="form-control" min="1" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Batch Number</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Expiry Date</label>
                                        <input type="date" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" rows="3" placeholder="Additional notes about this inventory update..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                        <button type="submit" form="inventoryUpdateForm" class="btn btn-info">Update Inventory</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            document.getElementById('inventoryUpdateForm').addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Inventory updated successfully!');
                closeModal();
            });
        }

        function switchVaccinationTab(tabName) {
            // Remove active class from all nav links
            document.querySelectorAll('#vaccinations .nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Hide all tab contents in vaccination section
            document.querySelectorAll('#vaccinations .tab-content').forEach(content => {
                content.style.display = 'none';
            });
            
            // Show selected tab content
            const selectedTab = document.getElementById(tabName + '-vaccinations');
            if (selectedTab) {
                selectedTab.style.display = 'block';
            }
            
            // Add active class to clicked nav link
            if (event && event.target) {
                event.target.classList.add('active');
            }
        }

        function filterVaccinations(category) {
            alert(`Filtering vaccinations by category: ${category}`);
        }

        function printSchedule() {
            alert('Printing vaccination schedule...');
        }

        function administerVaccine(patientId) {
            if (confirm('Are you ready to administer this vaccine?')) {
                alert(`Administering vaccine for patient ID: ${patientId}`);
            }
        }

        function viewVaccineHistory(patientId) {
            alert(`Viewing vaccination history for patient ID: ${patientId}`);
        }

        function rescheduleVaccine(patientId) {
            alert(`Rescheduling vaccination for patient ID: ${patientId}`);
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

        // Add CSS styles for vaccination checkboxes
        const vaccinationStyles = `
            <style>
                .vaccine-checkboxes {
                    display: flex;
                    flex-direction: column;
                    gap: 0.5rem;
                    padding: 1rem;
                    background: var(--bg-secondary);
                    border-radius: var(--radius-md);
                }
                
                .vaccine-checkboxes label {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    cursor: pointer;
                    padding: 0.5rem;
                    border-radius: var(--radius-sm);
                    transition: var(--transition-medium);
                }
                
                .vaccine-checkboxes label:hover {
                    background: var(--white);
                }
                
                .vaccine-checkboxes input[type="checkbox"] {
                    margin: 0;
                }
            </style>
        `;
        
        if (!document.querySelector('#vaccination-styles')) {
            const styleElement = document.createElement('div');
            styleElement.id = 'vaccination-styles';
            styleElement.innerHTML = vaccinationStyles;
            document.head.appendChild(styleElement);
        }

        // Profile Management Functions
        function editProfile() {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content" style="max-width: 800px;">
                    <div class="modal-header">
                        <h3>Edit Profile Information</h3>
                        <button onclick="closeModal()" class="close-btn">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3" style="text-align: right;">
                            <button type="button" class="btn btn-info" onclick="changeProfilePicture()">
                                <i class="fas fa-camera"></i> Change Photo
                            </button>
                        </div>
                        <form id="editProfileForm">
                            <h5>Personal Information</h5>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" value="Madhavi Kumari Perera" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control" value="1990-03-15">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Mobile Phone *</label>
                                        <input type="tel" class="form-control" value="+94 77 123 4567" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Home Phone</label>
                                        <input type="tel" class="form-control" value="+94 11 234 5678">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Personal Email</label>
                                        <input type="email" class="form-control" value="madhavi.perera@gmail.com">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Marital Status</label>
                                        <select class="form-control">
                                            <option value="single">Single</option>
                                            <option value="married" selected>Married</option>
                                            <option value="divorced">Divorced</option>
                                            <option value="widowed">Widowed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <h5 class="mt-4">Address Information</h5>
                            <div class="form-group">
                                <label class="form-label">Home Address</label>
                                <textarea class="form-control" rows="2">No. 123, Galle Road, Mount Lavinia, Colombo</textarea>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Postal Code</label>
                                        <input type="text" class="form-control" value="10370">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">District</label>
                                        <select class="form-control">
                                            <option value="colombo" selected>Colombo</option>
                                            <option value="gampaha">Gampaha</option>
                                            <option value="kalutara">Kalutara</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <h5 class="mt-4">Emergency Contact</h5>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Contact Name</label>
                                        <input type="text" class="form-control" value="Sunil Perera (Husband)">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Contact Number</label>
                                        <input type="tel" class="form-control" value="+94 71 987 6543">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                        <button type="submit" form="editProfileForm" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            document.getElementById('editProfileForm').addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Profile updated successfully!');
                closeModal();
            });
        }

        function changePassword() {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Change Password</h3>
                        <button onclick="closeModal()" class="close-btn">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="changePasswordForm">
                            <div class="form-group">
                                <label class="form-label">Current Password *</label>
                                <input type="password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">New Password *</label>
                                <input type="password" class="form-control" required minlength="8">
                                <small class="form-text">Password must be at least 8 characters long</small>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Confirm New Password *</label>
                                <input type="password" class="form-control" required minlength="8">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                        <button type="submit" form="changePasswordForm" class="btn btn-primary">Change Password</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Password changed successfully!');
                closeModal();
            });
        }

        function changeProfilePicture() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const profileImage = document.getElementById('profileImage');
                        profileImage.innerHTML = `<img src="${e.target.result}" alt="Profile Picture">`;
                        alert('Profile picture updated successfully!');
                    };
                    reader.readAsDataURL(file);
                }
            });
            input.click();
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

        // Tab switching function for Maternal and Child Care
        function switchCareTab(tabName) {
            event.preventDefault();
            event.stopPropagation();
            // Remove active class from all nav links
            const navLinks = document.querySelectorAll('#patients .nav-link');
            navLinks.forEach(link => link.classList.remove('active'));
            
            // Hide all tab contents
            const tabContents = document.querySelectorAll('#patients .tab-content');
            tabContents.forEach(content => content.classList.add('hidden'));
            
            // Add active class to clicked nav link
            event.target.classList.add('active');
            
            // Show selected tab content
            if (tabName === 'mothers') {
                document.getElementById('mothers-tab').classList.remove('hidden');
                // Show pregnant mothers by default
                document.getElementById('pregnant-mothers').classList.remove('hidden');
            } else if (tabName === 'children') {
                document.getElementById('children-tab').classList.remove('hidden');
            }
            return false;
        }

        // Tab switching function for Mother categories
        function switchMotherTab(tabName) {
            event.preventDefault();
            event.stopPropagation();
            // Remove active class from all mother sub-nav links
            const motherNavLinks = document.querySelectorAll('#mothers-tab .nav-link');
            motherNavLinks.forEach(link => link.classList.remove('active'));
            
            // Hide all mother tab contents
            document.getElementById('pregnant-mothers').classList.add('hidden');
            document.getElementById('lactating-mothers').classList.add('hidden');
            document.getElementById('postnatal-mothers').classList.add('hidden');
            
            // Add active class to clicked nav link
            event.target.classList.add('active');
            
            // Show selected mother tab content
            if (tabName === 'pregnant') {
                document.getElementById('pregnant-mothers').classList.remove('hidden');
            } else if (tabName === 'lactating') {
                document.getElementById('lactating-mothers').classList.remove('hidden');
            } else if (tabName === 'postnatal') {
                document.getElementById('postnatal-mothers').classList.remove('hidden');
            }
            return false;
        }

        // Tab switching function for Children categories
        function switchChildrenTab(tabName) {
            event.preventDefault();
            event.stopPropagation();
            // Remove active class from all children sub-nav links
            const childrenNavLinks = document.querySelectorAll('#children-tab .nav-link');
            childrenNavLinks.forEach(link => link.classList.remove('active'));
            
            // Hide all children tab contents
            document.getElementById('newborns-children').classList.add('hidden');
            document.getElementById('young-children').classList.add('hidden');
            document.getElementById('childs-children').classList.add('hidden');
            
            // Add active class to clicked nav link
            event.target.classList.add('active');
            
            // Show selected children tab content
            if (tabName === 'newborns') {
                document.getElementById('newborns-children').classList.remove('hidden');
            } else if (tabName === 'young') {
                document.getElementById('young-children').classList.remove('hidden');
            } else if (tabName === 'childs') {
                document.getElementById('childs-children').classList.remove('hidden');
            }
            return false;
        }

        // Update Mother Details Functions
        function updateMother(type, id, name, age, ...additionalData) {
            // Show comprehensive profile modal instead of basic update
            showComprehensiveMotherProfile(type, id, name, age, ...additionalData);
        }

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
                'pregnant': { id: 'pregnant-packets', label: 'Pregnant Mothers' },
                'lactating': { id: 'lactating-packets', label: 'Lactating Mothers' },
                'children': { id: 'children-packets', label: 'Children (6-23 months)' }
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

        function updateInventory() {
            editPacketsReceived();
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
                records: [
                    { id: 1, date: "2026-02-05", beneficiary: "Mrs. K. Silva", packets: 2, category: "Pregnant Mother", status: "Completed" },
                    { id: 2, date: "2026-02-05", beneficiary: "Mrs. A. Fernando", packets: 3, category: "Lactating Mother", status: "Completed" },
                    { id: 3, date: "2026-02-04", beneficiary: "Mrs. D. Jayawardene", packets: 2, category: "Child (6-23 months)", status: "Completed" },
                    { id: 4, date: "2026-02-04", beneficiary: "Mrs. P. Perera", packets: 1, category: "Pregnant Mother", status: "Pending" }
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
                records: [
                    { id: 1, date: "2026-02-11", beneficiary: "Mrs. D. Perera", packets: 2, category: "Lactating Mother", status: "Completed" },
                    { id: 2, date: "2026-02-10", beneficiary: "Mrs. N. Fernando", packets: 3, category: "Pregnant Mother", status: "Completed" },
                    { id: 3, date: "2026-02-09", beneficiary: "Mrs. K. Silva", packets: 2, category: "Child (6-23 months)", status: "Completed" },
                    { id: 4, date: "2026-02-08", beneficiary: "Mrs. R. Gunawardena", packets: 1, category: "Pregnant Mother", status: "Pending" }
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
                records: [
                    { id: 1, date: "2026-02-11", beneficiary: "Mrs. S. Jayawardena", packets: 2, category: "Child (6-23 months)", status: "Completed" },
                    { id: 2, date: "2026-02-10", beneficiary: "Mrs. M. Silva", packets: 1, category: "Pregnant Mother", status: "Completed" },
                    { id: 3, date: "2026-02-09", beneficiary: "Mrs. A. Perera", packets: 3, category: "Lactating Mother", status: "Completed" },
                    { id: 4, date: "2026-02-08", beneficiary: "Mrs. L. Fernando", packets: 2, category: "Child (6-23 months)", status: "Pending" }
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
                records: [
                    { id: 1, date: "2026-02-11", beneficiary: "Mrs. R. Bandara", packets: 3, category: "Lactating Mother", status: "Completed" },
                    { id: 2, date: "2026-02-11", beneficiary: "Mrs. T. Fernando", packets: 2, category: "Pregnant Mother", status: "Completed" },
                    { id: 3, date: "2026-02-10", beneficiary: "Mrs. K. Rajapaksha", packets: 2, category: "Child (6-23 months)", status: "Completed" },
                    { id: 4, date: "2026-02-09", beneficiary: "Mrs. S. Silva", packets: 3, category: "Lactating Mother", status: "Pending" }
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
                `}).join('');
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
(function(){if(!window.chatbase||window.chatbase("getState")!=="initialized"){window.chatbase=(...arguments)=>{if(!window.chatbase.q){window.chatbase.q=[]}window.chatbase.q.push(arguments)};window.chatbase=new Proxy(window.chatbase,{get(target,prop){if(prop==="q"){return target.q}return(...args)=>target(prop,...args)}})}const onLoad=function(){const script=document.createElement("script");script.src="https://www.chatbase.co/embed.min.js";script.id="mkSuvkG19NuJ50hkdCPlJ";script.domain="www.chatbase.co";document.body.appendChild(script)};if(document.readyState==="complete"){onLoad()}else{window.addEventListener("load",onLoad)}})();
</script>
</body>
</html>
</html>


