/**
 * MidConnect - Main JavaScript Functionality
 * Digital Midwife Activity Tracking System
 * Version: 1.0
 */

// Global configuration
const CONFIG = {
    API_BASE_URL: 'php/',
    SESSION_TIMEOUT: 3600000, // 1 hour in milliseconds
    AUTO_SAVE_INTERVAL: 30000, // 30 seconds
    NOTIFICATION_DURATION: 5000, // 5 seconds
    CHART_COLORS: {
        primary: '#002E4F',
        success: '#00A699',
        warning: '#ffc107',
        danger: '#dc3545',
        info: '#00A699'
    }
};

// Utility functions
const Utils = {
    // Format date to local string
    formatDate: (date, includeTime = false) => {
        const options = {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        };
        
        if (includeTime) {
            options.hour = '2-digit';
            options.minute = '2-digit';
        }
        
        return new Date(date).toLocaleDateString('en-GB', options);
    },

    // Format time to 12-hour format
    formatTime: (time) => {
        const [hours, minutes] = time.split(':');
        const hour12 = hours % 12 || 12;
        const ampm = hours < 12 ? 'AM' : 'PM';
        return `${hour12}:${minutes} ${ampm}`;
    },

    // Validate email format
    isValidEmail: (email) => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    // Validate phone number (Sri Lankan format)
    isValidPhone: (phone) => {
        const phoneRegex = /^(\+94|0)?[1-9]\d{8}$/;
        return phoneRegex.test(phone.replace(/\s+/g, ''));
    },

    // Generate unique ID
    generateId: () => {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    },

    // Debounce function
    debounce: (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // Local storage helpers
    storage: {
        set: (key, value) => {
            try {
                localStorage.setItem(key, JSON.stringify(value));
                return true;
            } catch (e) {
                console.error('Failed to save to localStorage:', e);
                return false;
            }
        },
        get: (key) => {
            try {
                const item = localStorage.getItem(key);
                return item ? JSON.parse(item) : null;
            } catch (e) {
                console.error('Failed to read from localStorage:', e);
                return null;
            }
        },
        remove: (key) => {
            try {
                localStorage.removeItem(key);
                return true;
            } catch (e) {
                console.error('Failed to remove from localStorage:', e);
                return false;
            }
        }
    }
};

// API Service
const ApiService = {
    // Generic request method
    request: async (endpoint, options = {}) => {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        const config = { ...defaultOptions, ...options };
        
        try {
            const response = await fetch(`${CONFIG.API_BASE_URL}${endpoint}`, config);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return { success: true, data };
        } catch (error) {
            console.error('API request failed:', error);
            return { success: false, error: error.message };
        }
    },

    // Authentication methods
    auth: {
        login: async (credentials, userType) => {
            const endpoint = userType === 'admin' ? 'admin_login.php' : 'midwife_login.php';
            return await ApiService.request(endpoint, {
                method: 'POST',
                body: JSON.stringify(credentials)
            });
        },

        logout: async (userType) => {
            const endpoint = userType === 'admin' ? 'admin_logout.php' : 'midwife_logout.php';
            return await ApiService.request(endpoint, { method: 'POST' });
        },

        checkSession: async (userType) => {
            const endpoint = 'check_session.php';
            return await ApiService.request(endpoint, {
                method: 'POST',
                body: JSON.stringify({ user_type: userType })
            });
        }
    },

    // Activity methods
    activities: {
        create: async (activityData) => {
            return await ApiService.request('create_activity.php', {
                method: 'POST',
                body: JSON.stringify(activityData)
            });
        },

        update: async (activityId, activityData) => {
            return await ApiService.request('update_activity.php', {
                method: 'POST',
                body: JSON.stringify({ id: activityId, ...activityData })
            });
        },

        delete: async (activityId) => {
            return await ApiService.request('delete_activity.php', {
                method: 'POST',
                body: JSON.stringify({ id: activityId })
            });
        },

        getByMidwife: async (midwifeId, dateRange = {}) => {
            const params = new URLSearchParams({ midwife_id: midwifeId, ...dateRange });
            return await ApiService.request(`get_activities.php?${params}`);
        },

        getAll: async (filters = {}) => {
            const params = new URLSearchParams(filters);
            return await ApiService.request(`get_all_activities.php?${params}`);
        }
    },

    // Schedule methods
    schedules: {
        getByMidwife: async (midwifeId, date) => {
            const params = new URLSearchParams({ midwife_id: midwifeId, date });
            return await ApiService.request(`get_schedule.php?${params}`);
        },

        create: async (scheduleData) => {
            return await ApiService.request('create_schedule.php', {
                method: 'POST',
                body: JSON.stringify(scheduleData)
            });
        }
    },

    // Report methods
    reports: {
        generate: async (reportParams) => {
            return await ApiService.request('generate_report.php', {
                method: 'POST',
                body: JSON.stringify(reportParams)
            });
        },

        getList: async (userId, userType) => {
            const params = new URLSearchParams({ user_id: userId, user_type: userType });
            return await ApiService.request(`get_reports.php?${params}`);
        }
    }
};

// Notification System
const NotificationSystem = {
    container: null,

    init: () => {
        if (!NotificationSystem.container) {
            NotificationSystem.container = document.createElement('div');
            NotificationSystem.container.className = 'notification-container';
            NotificationSystem.container.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                z-index: 9999;
                max-width: 400px;
            `;
            document.body.appendChild(NotificationSystem.container);
        }
    },

    show: (message, type = 'info', duration = CONFIG.NOTIFICATION_DURATION) => {
        NotificationSystem.init();

        const notification = document.createElement('div');
        notification.className = `alert alert-${type} notification-item`;
        notification.style.cssText = `
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease-out;
            cursor: pointer;
        `;

        const icon = NotificationSystem.getIcon(type);
        notification.innerHTML = `
            <div style="display: flex; align-items: center;">
                <i class="fas ${icon}" style="margin-right: 10px;"></i>
                <span>${message}</span>
                <button type="button" class="close-btn" style="margin-left: auto; background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>
            </div>
        `;

        // Add close functionality
        const closeBtn = notification.querySelector('.close-btn');
        closeBtn.addEventListener('click', () => NotificationSystem.hide(notification));

        NotificationSystem.container.appendChild(notification);

        // Auto-hide after duration
        if (duration > 0) {
            setTimeout(() => NotificationSystem.hide(notification), duration);
        }

        return notification;
    },

    hide: (notification) => {
        if (notification && notification.parentNode) {
            notification.style.animation = 'slideOut 0.3s ease-in';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }
    },

    getIcon: (type) => {
        const icons = {
            success: 'fa-check-circle',
            warning: 'fa-exclamation-triangle',
            danger: 'fa-times-circle',
            error: 'fa-times-circle',
            info: 'fa-info-circle'
        };
        return icons[type] || icons.info;
    }
};

// Form Validation System
const FormValidator = {
    rules: {
        required: (value) => value.trim() !== '',
        email: (value) => Utils.isValidEmail(value),
        phone: (value) => Utils.isValidPhone(value),
        minLength: (value, min) => value.length >= min,
        maxLength: (value, max) => value.length <= max,
        numeric: (value) => !isNaN(value) && !isNaN(parseFloat(value)),
        date: (value) => !isNaN(Date.parse(value)),
        time: (value) => /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(value)
    },

    validate: (form, validationRules) => {
        const errors = {};
        let isValid = true;

        Object.keys(validationRules).forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (!field) return;

            const value = field.value;
            const rules = validationRules[fieldName];

            rules.forEach(rule => {
                if (typeof rule === 'string') {
                    // Simple rule like 'required'
                    if (!FormValidator.rules[rule](value)) {
                        errors[fieldName] = FormValidator.getErrorMessage(rule, fieldName);
                        isValid = false;
                    }
                } else if (typeof rule === 'object') {
                    // Rule with parameters like { rule: 'minLength', param: 6 }
                    if (!FormValidator.rules[rule.rule](value, rule.param)) {
                        errors[fieldName] = FormValidator.getErrorMessage(rule.rule, fieldName, rule.param);
                        isValid = false;
                    }
                }
            });
        });

        FormValidator.displayErrors(form, errors);
        return { isValid, errors };
    },

    displayErrors: (form, errors) => {
        // Clear previous errors
        form.querySelectorAll('.error-message').forEach(el => el.textContent = '');
        form.querySelectorAll('.form-control.error').forEach(el => el.classList.remove('error'));

        // Display new errors
        Object.keys(errors).forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            const errorElement = form.querySelector(`#${fieldName}_error`);
            
            if (field) {
                field.classList.add('error');
            }
            
            if (errorElement) {
                errorElement.textContent = errors[fieldName];
            }
        });
    },

    getErrorMessage: (rule, fieldName, param) => {
        const messages = {
            required: `${fieldName.replace('_', ' ')} is required`,
            email: 'Please enter a valid email address',
            phone: 'Please enter a valid phone number',
            minLength: `Minimum ${param} characters required`,
            maxLength: `Maximum ${param} characters allowed`,
            numeric: 'Please enter a valid number',
            date: 'Please enter a valid date',
            time: 'Please enter a valid time'
        };
        return messages[rule] || 'Invalid input';
    }
};

// Session Management
const SessionManager = {
    userType: null,
    sessionTimer: null,
    warningTimer: null,

    init: (userType) => {
        SessionManager.userType = userType;
        SessionManager.startSessionTimer();
        SessionManager.setupActivityListeners();
    },

    startSessionTimer: () => {
        // Clear existing timers
        if (SessionManager.sessionTimer) clearTimeout(SessionManager.sessionTimer);
        if (SessionManager.warningTimer) clearTimeout(SessionManager.warningTimer);

        // Warning 5 minutes before timeout
        SessionManager.warningTimer = setTimeout(() => {
            SessionManager.showSessionWarning();
        }, CONFIG.SESSION_TIMEOUT - 300000);

        // Automatic logout
        SessionManager.sessionTimer = setTimeout(() => {
            SessionManager.logout();
        }, CONFIG.SESSION_TIMEOUT);
    },

    showSessionWarning: () => {
        const extend = confirm('Your session will expire in 5 minutes. Do you want to extend it?');
        if (extend) {
            SessionManager.extendSession();
        }
    },

    extendSession: async () => {
        const result = await ApiService.auth.checkSession(SessionManager.userType);
        if (result.success) {
            SessionManager.startSessionTimer();
            NotificationSystem.show('Session extended successfully', 'success');
        } else {
            SessionManager.logout();
        }
    },

    logout: async () => {
        await ApiService.auth.logout(SessionManager.userType);
        Utils.storage.remove(`${SessionManager.userType}_user`);
        
        const loginPage = SessionManager.userType === 'admin' ? 'admin-login.html' : 'midwife-login.html';
        window.location.href = loginPage;
    },

    setupActivityListeners: () => {
        // Reset timer on user activity
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        const resetTimer = Utils.debounce(() => {
            SessionManager.startSessionTimer();
        }, 1000);

        events.forEach(event => {
            document.addEventListener(event, resetTimer, true);
        });
    }
};

// Chart Helper Functions
const ChartHelpers = {
    getDefaultOptions: () => ({
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }),

    createLineChart: (ctx, data, options = {}) => {
        const config = {
            type: 'line',
            data: data,
            options: { ...ChartHelpers.getDefaultOptions(), ...options }
        };
        return new Chart(ctx, config);
    },

    createBarChart: (ctx, data, options = {}) => {
        const config = {
            type: 'bar',
            data: data,
            options: { ...ChartHelpers.getDefaultOptions(), ...options }
        };
        return new Chart(ctx, config);
    },

    createDoughnutChart: (ctx, data, options = {}) => {
        const config = {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                },
                ...options
            }
        };
        return new Chart(ctx, config);
    }
};

// Data Export Functions
const DataExporter = {
    exportToCSV: (data, filename) => {
        const csvContent = DataExporter.arrayToCSV(data);
        DataExporter.downloadFile(csvContent, filename, 'text/csv');
    },

    exportToJSON: (data, filename) => {
        const jsonContent = JSON.stringify(data, null, 2);
        DataExporter.downloadFile(jsonContent, filename, 'application/json');
    },

    arrayToCSV: (data) => {
        if (!data.length) return '';
        
        const headers = Object.keys(data[0]);
        const csvRows = [headers.join(',')];
        
        data.forEach(row => {
            const values = headers.map(header => {
                const value = row[header];
                return typeof value === 'string' && value.includes(',') ? `"${value}"` : value;
            });
            csvRows.push(values.join(','));
        });
        
        return csvRows.join('\n');
    },

    downloadFile: (content, filename, mimeType) => {
        const blob = new Blob([content], { type: mimeType });
        const url = window.URL.createObjectURL(blob);
        
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        window.URL.revokeObjectURL(url);
    }
};

// Auto-save functionality
const AutoSave = {
    forms: new Map(),

    register: (form, saveCallback, interval = CONFIG.AUTO_SAVE_INTERVAL) => {
        const formId = form.id || Utils.generateId();
        
        if (AutoSave.forms.has(formId)) {
            clearInterval(AutoSave.forms.get(formId).timer);
        }

        const timer = setInterval(() => {
            AutoSave.saveForm(form, saveCallback);
        }, interval);

        AutoSave.forms.set(formId, { form, saveCallback, timer });
    },

    saveForm: (form, callback) => {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        // Only save if form has content
        if (Object.values(data).some(value => value.trim() !== '')) {
            callback(data);
        }
    },

    unregister: (formId) => {
        if (AutoSave.forms.has(formId)) {
            clearInterval(AutoSave.forms.get(formId).timer);
            AutoSave.forms.delete(formId);
        }
    }
};

// Initialize notification styles
const initStyles = () => {
    const styles = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        .notification-container {
            pointer-events: none;
        }

        .notification-item {
            pointer-events: auto;
        }
    `;

    const styleSheet = document.createElement('style');
    styleSheet.textContent = styles;
    document.head.appendChild(styleSheet);
};

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', initStyles);

// Export global objects
window.MidConnect = {
    CONFIG,
    Utils,
    ApiService,
    NotificationSystem,
    FormValidator,
    SessionManager,
    ChartHelpers,
    DataExporter,
    AutoSave
};