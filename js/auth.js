/**
 * MidConnect - Authentication JavaScript
 * Handles login, logout, and session management
 */

// Authentication specific functions
const AuthManager = {
    // Initialize authentication system
    init: () => {
        AuthManager.setupPasswordToggle();
        AuthManager.setupRememberMe();
        AuthManager.checkExistingSession();
    },

    // Setup password visibility toggle
    setupPasswordToggle: () => {
        const toggleButtons = document.querySelectorAll('.password-toggle');
        toggleButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const input = button.previousElementSibling;
                const icon = button.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    },

    // Setup remember me functionality
    setupRememberMe: () => {
        const rememberCheckbox = document.querySelector('#remember_me');
        const form = document.querySelector('form');
        
        if (rememberCheckbox && form) {
            // Load saved credentials if remember me was checked
            const savedCredentials = MidConnect.Utils.storage.get('remembered_credentials');
            if (savedCredentials) {
                const usernameField = form.querySelector('input[type="text"], input[name*="username"], input[name*="employee_id"]');
                if (usernameField) {
                    usernameField.value = savedCredentials.username;
                    rememberCheckbox.checked = true;
                }
            }
        }
    },

    // Check for existing session
    checkExistingSession: () => {
        const currentPage = window.location.pathname;
        
        // Check for admin session
        const adminUser = MidConnect.Utils.storage.get('admin_user');
        if (adminUser && currentPage.includes('admin-login')) {
            window.location.href = 'admin/dashboard.php';
        }
        
        // Check for midwife session
        const midwifeUser = MidConnect.Utils.storage.get('midwife_user');
        if (midwifeUser && currentPage.includes('midwife-login')) {
            window.location.href = 'midwife_dashboard.php';
        }
    },

    // Validate login form
    validateLoginForm: (formData, userType) => {
        const errors = {};
        let isValid = true;

        if (userType === 'admin') {
            // Admin validation
            if (!formData.username || formData.username.length < 3) {
                errors.username = 'Username must be at least 3 characters';
                isValid = false;
            }

            if (!formData.password || formData.password.length < 8) {
                errors.password = 'Password must be at least 8 characters';
                isValid = false;
            }

            if (!formData.moh_office) {
                errors.moh_office = 'Please select your MOH office';
                isValid = false;
            }
        } else {
            // Midwife validation
            if (!formData.employee_id || formData.employee_id.length < 3) {
                errors.employee_id = 'Employee ID must be at least 3 characters';
                isValid = false;
            }

            if (!formData.password || formData.password.length < 6) {
                errors.password = 'Password must be at least 6 characters';
                isValid = false;
            }
        }

        return { isValid, errors };
    },

    // Display form errors
    displayErrors: (errors) => {
        // Clear previous errors
        document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
        document.querySelectorAll('.form-control.error').forEach(el => el.classList.remove('error'));

        // Display new errors
        Object.keys(errors).forEach(fieldName => {
            const errorElement = document.getElementById(`${fieldName}_error`);
            const inputElement = document.querySelector(`[name="${fieldName}"]`);
            
            if (errorElement) {
                errorElement.textContent = errors[fieldName];
            }
            
            if (inputElement) {
                inputElement.classList.add('error');
            }
        });
    },

    // Clear all errors
    clearErrors: () => {
        const loginError = document.getElementById('login-error');
        if (loginError) {
            loginError.style.display = 'none';
        }
        
        document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
        document.querySelectorAll('.form-control.error').forEach(el => el.classList.remove('error'));
    },

    // Show login error message
    showLoginError: (message) => {
        const errorDiv = document.getElementById('login-error');
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }
    },

    // Handle remember me functionality
    handleRememberMe: (formData, remember) => {
        if (remember) {
            // Save credentials (username only, never password)
            const credentials = {
                username: formData.username || formData.employee_id,
                timestamp: Date.now()
            };
            MidConnect.Utils.storage.set('remembered_credentials', credentials);
        } else {
            // Remove saved credentials
            MidConnect.Utils.storage.remove('remembered_credentials');
        }
    },

    // Process successful login
    processSuccessfulLogin: (userData, userType, rememberMe) => {
        // Store user data
        MidConnect.Utils.storage.set(`${userType}_user`, userData);
        
        // Handle remember me
        if (rememberMe && userType === 'midwife') {
            AuthManager.handleRememberMe({ employee_id: userData.employee_id }, rememberMe);
        } else if (rememberMe && userType === 'admin') {
            AuthManager.handleRememberMe({ username: userData.username }, rememberMe);
        }

        // Initialize session management
        MidConnect.SessionManager.init(userType);

        // Redirect to appropriate dashboard
        const redirectUrl = userType === 'admin' ? 'admin/dashboard.php' : 'midwife_dashboard.php';
        
        // Show success message briefly before redirect
        MidConnect.NotificationSystem.show('Login successful! Redirecting...', 'success', 1500);
        
        setTimeout(() => {
            window.location.href = redirectUrl;
        }, 1500);
    },

    // Set button loading state
    setButtonLoading: (button, loading = true) => {
        if (loading) {
            button.dataset.originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
            button.disabled = true;
        } else {
            button.innerHTML = button.dataset.originalText || button.innerHTML;
            button.disabled = false;
        }
    },

    // Logout function
    logout: async (userType) => {
        try {
            // Call logout API
            const result = await MidConnect.ApiService.auth.logout(userType);
            
            // Clear local storage
            MidConnect.Utils.storage.remove(`${userType}_user`);
            
            // Clear session timers
            if (MidConnect.SessionManager.sessionTimer) {
                clearTimeout(MidConnect.SessionManager.sessionTimer);
            }
            if (MidConnect.SessionManager.warningTimer) {
                clearTimeout(MidConnect.SessionManager.warningTimer);
            }
            
            // Show logout message
            MidConnect.NotificationSystem.show('Logged out successfully', 'info', 2000);
            
            // Redirect to login page
            setTimeout(() => {
                const loginPage = userType === 'admin' ? 'admin-login.html' : 'midwife-login.html';
                window.location.href = loginPage;
            }, 1000);
            
        } catch (error) {
            console.error('Logout error:', error);
            // Force redirect even if API call fails
            const loginPage = userType === 'admin' ? 'admin-login.html' : 'midwife-login.html';
            window.location.href = loginPage;
        }
    }
};

// Login form handlers
const LoginHandlers = {
    // Handle midwife login
    handleMidwifeLogin: async (event) => {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const loginData = Object.fromEntries(formData);
        const submitButton = form.querySelector('button[type="submit"]');

        // Clear previous errors
        AuthManager.clearErrors();

        // Validate form
        const validation = AuthManager.validateLoginForm(loginData, 'midwife');
        if (!validation.isValid) {
            AuthManager.displayErrors(validation.errors);
            return;
        }

        // Set loading state
        AuthManager.setButtonLoading(submitButton, true);

        try {
            // Simulate API call for demo (replace with actual API call)
            const response = await AuthManager.simulateMidwifeLogin(loginData);
            
            if (response.success) {
                AuthManager.processSuccessfulLogin(
                    response.user, 
                    'midwife', 
                    loginData.remember_me === 'on'
                );
            } else {
                AuthManager.showLoginError(response.message || 'Login failed. Please check your credentials.');
            }
        } catch (error) {
            console.error('Login error:', error);
            AuthManager.showLoginError('Connection error. Please try again.');
        } finally {
            AuthManager.setButtonLoading(submitButton, false);
        }
    },

    // Handle admin login
    handleAdminLogin: async (event) => {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const loginData = Object.fromEntries(formData);
        const submitButton = form.querySelector('button[type="submit"]');

        // Clear previous errors
        AuthManager.clearErrors();

        // Validate form
        const validation = AuthManager.validateLoginForm(loginData, 'admin');
        if (!validation.isValid) {
            AuthManager.displayErrors(validation.errors);
            return;
        }

        // Set loading state
        AuthManager.setButtonLoading(submitButton, true);

        try {
            // Simulate API call for demo (replace with actual API call)
            const response = await AuthManager.simulateAdminLogin(loginData);
            
            if (response.success) {
                AuthManager.processSuccessfulLogin(
                    response.user, 
                    'admin', 
                    loginData.secure_session === 'on'
                );
            } else {
                AuthManager.showLoginError(response.message || 'Login failed. Please verify your credentials.');
            }
        } catch (error) {
            console.error('Login error:', error);
            AuthManager.showLoginError('Connection error. Please try again.');
        } finally {
            AuthManager.setButtonLoading(submitButton, false);
        }
    }
};

// Simulate API responses for demo purposes
AuthManager.simulateMidwifeLogin = async (loginData) => {
    return new Promise((resolve) => {
        setTimeout(() => {
            // Demo credentials
            if (loginData.employee_id === 'MW001' && loginData.password === 'password123') {
                resolve({
                    success: true,
                    user: {
                        midwife_id: 1,
                        employee_id: 'MW001',
                        name: 'Madhavi Perera',
                        email: 'madhavi.perera@health.gov.lk',
                        assigned_area: 'Colombo Central',
                        moh_office: 'MOH Colombo 01'
                    }
                });
            } else {
                resolve({
                    success: false,
                    message: 'Invalid employee ID or password'
                });
            }
        }, 1500); // Simulate network delay
    });
};

AuthManager.simulateAdminLogin = async (loginData) => {
    return new Promise((resolve) => {
        setTimeout(() => {
            // Demo credentials
            if (loginData.username === 'admin001' && loginData.password === 'admin123456') {
                resolve({
                    success: true,
                    user: {
                        admin_id: 1,
                        username: 'admin001',
                        name: 'Dr. Sarah Johnson',
                        email: 'sarah.johnson@health.gov.lk',
                        moh_office: loginData.moh_office
                    }
                });
            } else {
                resolve({
                    success: false,
                    message: 'Invalid username, password, or MOH office selection'
                });
            }
        }, 2000); // Simulate network delay
    });
};

// Password reset functionality
const PasswordReset = {
    // Handle forgot password form
    handleForgotPassword: async (event, userType) => {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const resetData = Object.fromEntries(formData);
        const submitButton = form.querySelector('button[type="submit"]');

        // Set loading state
        AuthManager.setButtonLoading(submitButton, true);

        try {
            // Simulate password reset request
            await PasswordReset.simulatePasswordReset(resetData, userType);
            
            MidConnect.NotificationSystem.show(
                'Password reset request submitted successfully. Check your email for further instructions.',
                'success'
            );
            
            // Close modal
            const modal = form.closest('.modal');
            if (modal) {
                modal.classList.remove('show');
            }
            
            // Reset form
            form.reset();
            
        } catch (error) {
            console.error('Password reset error:', error);
            MidConnect.NotificationSystem.show(
                'Failed to submit password reset request. Please try again.',
                'error'
            );
        } finally {
            AuthManager.setButtonLoading(submitButton, false);
        }
    },

    // Simulate password reset API call
    simulatePasswordReset: async (resetData, userType) => {
        return new Promise((resolve) => {
            setTimeout(() => {
                console.log(`Password reset requested for ${userType}:`, resetData);
                resolve({ success: true });
            }, 1500);
        });
    }
};

// Initialize authentication when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    AuthManager.init();
    
    // Setup form handlers
    const midwifeLoginForm = document.getElementById('midwifeLoginForm');
    if (midwifeLoginForm) {
        midwifeLoginForm.addEventListener('submit', LoginHandlers.handleMidwifeLogin);
    }
    
    const adminLoginForm = document.getElementById('adminLoginForm');
    if (adminLoginForm) {
        adminLoginForm.addEventListener('submit', LoginHandlers.handleAdminLogin);
    }
    
    // Setup forgot password forms
    const forgotPasswordForms = document.querySelectorAll('#forgotPasswordForm');
    forgotPasswordForms.forEach(form => {
        form.addEventListener('submit', (event) => {
            const userType = form.closest('.modal').id.includes('admin') ? 'admin' : 'midwife';
            PasswordReset.handleForgotPassword(event, userType);
        });
    });
    
    // Setup modal close events
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.classList.remove('show');
            }
        });
    });
});

// Export authentication functions
window.AuthManager = AuthManager;
window.LoginHandlers = LoginHandlers;
window.PasswordReset = PasswordReset;