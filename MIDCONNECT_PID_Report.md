# Project Initiation Document (PID)
**Project Title:** MidConnect - Digital Midwife Activity Tracking System  
**Module:** PUSL3190  
**Date:** 2026-01-13  

---

## 1. Executive Summary
MidConnect is a web-based information management system designed to digitize the workflow of Public Health Midwives (PHM) in Sri Lanka. By replacing the traditional paper-based reporting system with a centralized digital platform, MidConnect aims to enhance the efficiency of maternal coordination, improve data accuracy, and provide Medical Officers of Health (MOH) with real-time insights into community health activities.

## 2. Introduction & Background
Public Health Midwives play a crucial role in Sri Lanka's preventive healthcare sector, specifically in maternal and child health. Currently, the majority of their administrative work—ranging from daily activity logs to monthly summary reports—rest on manual, paper-based processes. This traditional approach is labor-intensive, prone to human error, and leads to significant delays in data aggregation and decision-making at the MOH level.

MidConnect proposes a digital transformation of this process, offering a dual-interface system: a mobile-responsive portal for midwives to log field activities and a comprehensive dashboard for administrators to monitor performance and health metrics.

## 3. Problem Statement
The current manual system faces several critical issues:
*   **Inefficiency:** Midwives spend a significant portion of their time (estimated 20-30%) on manual record-keeping rather than patient care.
*   **Data Latency:** Physical reports must be transported to MOH offices, causing delays in data availability.
*   **Data Inaccuracy:** Manual tabulation risks calculation errors and illegible handwriting issues.
*   **Lack of Visibility:** Supervisors cannot monitor field activities in real-time, making performance evaluation difficult.
*   **Storage Costs:** Physical archiving of innovative volumes of paper records is costly and unsustainable.

## 4. Project Objectives
### 4.1. Primary Objectives
*   To develop a web-based activity tracking system that eliminates 100% of manual paper reporting for daily logs.
*   To reduce the time spent on administrative tasks by midwives by at least 40%.
*   To ensure 99% data accuracy by implementing input validation and automated calculations.

### 4.2. Secondary Objectives
*   To provide MOH administrators with real-time visualization of health statistics (e.g., vaccination coverage, home visit completion rates).
*   To implement a secure role-based access control system to protect sensitive patient data.
*   To create a scalable architecture that can be expanded to include GPS tracking and mobile apps in the future.

## 5. Project Scope

### 5.1. In-Scope (Deliverables)
**For Midwives (User Portal):**
*   **Secure Authentication:** Individual login with Encrypted credentials.
*   **Activity Logging:** Interface to record:
    *   Home visits (Prenatal/Postnatal)
    *   Clinic sessions
    *   Vaccinations administered
    *   Family planning counseling
*   **Schedule Management:** Calendar view of upcoming clinics and required visits.
*   **Patient Database:** Basic CRUD operations for patient profiles within their area.

**For Administrators (MOH Portal):**
*   **Executive Dashboard:** Graphs and charts showing real-time statistics.
*   **Staff Management:** Tools to add/edit midwife profiles and assign service areas.
*   **Reports:** Automated generation of:
    *   Monthly consolidated reports (H500/H524 formats).
    *   Performance evaluation reports.
*   **Audit Trails:** Logs of all system activities for accountability.

### 5.2. Out-of-Scope
*   Offline mobile app functionality (Planned for Phase 2).
*   Integration with central government birth/death registration databases.
*   GPS location tracking of field staff (Privacy policy pending).
*   Patient-facing portal.

## 6. Project Methodology
The project will follow an **Agile Scrum** methodology to allow for iterative development and frequent feedback.
*   **Sprint 1:** Requirement gathering & UI/UX Design.
*   **Sprint 2:** Database Design & Authentication Module.
*   **Sprint 3:** Midwife Activity Logging Features.
*   **Sprint 4:** Admin Dashboard & Reporting Logic.
*   **Sprint 5:** Testing (Unit, Integration, UAT) & Deployment.

## 7. Technical Architecture

### 7.1. Technology Stack
*   **Frontend:** HTML5, CSS3 (Custom Design System), JavaScript (ES6+), Chart.js (Data Visualization).
*   **Backend:** PHP 8.0+ (Object-Oriented).
*   **Database:** MySQL 8.0 (Relational Database Management System).
*   **Server:** Apache Web Server.

### 7.2. Security Measures
*   **Password Security:** Bcrypt hashing algorithm.
*   **Data Integrity:** PDO Prepared Statements to prevent SQL Injection.
*   **Session Management:** Secure session handling with timeouts and regeneration.
*   **Input Handling:** Strict server-side validation and XSS sanitization.

## 8. Resource Requrements
### 8.1. Hardware
*   Development PC (Windows/Mac/Linux).
*   Server environment for hosting (DigitalOcean/AWS or Local WAMP/XAMPP for dev).
*   Mobile devices (Tablets/Smartphones) for responsiveness testing.

### 8.2. Software
*   VS Code (IDE).
*   XAMPP/WAMP (Local Server).
*   Git (Version Control).
*   Web Browsers (Chrome, Firefox, Edge).

## 9. Risk Management
| Risk ID | Risk Description | Probability | Impact | Mitigation Strategy |
|:---:|:---|:---:|:---:|:---|
| R01 | Scope Creep | High | High | Strict adherence to the finalized requirements document; Change Request process. |
| R02 | Data Loss | Low | Critical | Regular automated backups of the MySQL database. |
| R03 | User Resistance | Medium | Medium | Conduct training sessions; Design an intuitive, simple UI familiar to non-tech users. |
| R04 | Security Breach | Low | Critical | Regular security audits; Implementation of industry-standard security practices. |

## 10. Timeline (High Level)
*   **Week 1-2:** Project Proposal & Requirement Analysis.
*   **Week 3-4:** System & Database Design (ER Diagrams).
*   **Week 5-8:** Implementation (Coding Phase).
*   **Week 9:** Testing & Quality Assurance.
*   **Week 10:** Documentation & Final Presentation.

---
**Prepared By:** [Your Name/ID]  
**Reviewed By:** [Supervisor Name]
  
