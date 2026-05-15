document.addEventListener('DOMContentLoaded', function () {
    initializeAdminLogin();
    loadMohOffices();
});

function initializeAdminLogin() {
    const form = document.getElementById('adminLoginForm');

    if (form) {
        form.addEventListener('submit', handleAdminLogin);
    }

    const forgotPasswordForm = document.getElementById('forgotPasswordForm');

    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', handleForgotPasswordSubmit);
    }

    const forgotPasswordModal = document.getElementById('forgotPasswordModal');

    if (forgotPasswordModal) {
        forgotPasswordModal.addEventListener('click', function (event) {
            if (event.target === forgotPasswordModal) {
                closeForgotPasswordModal();
            }
        });
    }
}

function loadMohOffices() {
    fetch('php/admin/get_moh_offices.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.text();
        })
        .then(function (text) {
            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error('MOH office non-JSON response:', text);
                fillMohOfficeSelects([]);
                return;
            }

            if (!data.success) {
                console.error(data.message || 'Failed to load MOH offices.');
                fillMohOfficeSelects([]);
                return;
            }

            fillMohOfficeSelects(data.offices || []);
        })
        .catch(function (error) {
            console.error('MOH Office Load Error:', error);
            fillMohOfficeSelects([]);
        });
}

function fillMohOfficeSelects(offices) {
    const loginSelect = document.getElementById('moh_office');
    const resetSelect = document.getElementById('reset_moh_office');

    const options = ['<option value="">Select your MOH office</option>']
        .concat(
            offices.map(function (office) {
                return `<option value="${escapeAdminHtml(office)}">${escapeAdminHtml(office)}</option>`;
            })
        )
        .join('');

    if (loginSelect) {
        loginSelect.innerHTML = options;
    }

    if (resetSelect) {
        resetSelect.innerHTML = options;
    }
}

function handleAdminLogin(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);

    const username = String(formData.get('username') || '').trim();
    const password = String(formData.get('password') || '').trim();
    const mohOffice = String(formData.get('moh_office') || '').trim();

    clearErrors();

    if (!validateAdminLogin(username, password, mohOffice)) {
        return;
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.innerHTML : '';

    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Authenticating...';
    }

    fetch('php/admin/admin_login.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.text();
        })
        .then(function (text) {
            console.log('Admin login raw response:', text);

            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error('Invalid admin login JSON:', text);
                showError('Login failed. PHP returned non-JSON response. Check php_errors.log.');
                resetSubmitButton(submitBtn, originalText);
                return;
            }

            if (data.success === true) {
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-check"></i> Success!';
                }

                sessionStorage.setItem('admin_user', JSON.stringify(data.admin));

                window.location.href = data.redirect || 'admin/dashboard.php';
                return;
            }

            showError(data.message || 'Login failed.');
            resetSubmitButton(submitBtn, originalText);
        })
        .catch(function (error) {
            console.error('Admin Login Error:', error);
            showError('Server error while logging in.');
            resetSubmitButton(submitBtn, originalText);
        });
}

function validateAdminLogin(username, password, mohOffice) {
    let isValid = true;

    if (!username || username.length < 3) {
        showFieldError('username_error', 'Username must be at least 3 characters.');
        isValid = false;
    }

    if (!password || password.length < 6) {
        showFieldError('password_error', 'Password must be at least 6 characters.');
        isValid = false;
    }

    if (!mohOffice) {
        showFieldError('moh_office_error', 'Please select your MOH office.');
        isValid = false;
    }

    return isValid;
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);

    if (!input) return;

    const inputGroup = input.parentNode;
    const button = inputGroup ? inputGroup.querySelector('.password-toggle') : null;
    const icon = button ? button.querySelector('i') : null;

    if (input.type === 'password') {
        input.type = 'text';

        if (icon) {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    } else {
        input.type = 'password';

        if (icon) {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
}

function showForgotPasswordModal() {
    const modal = document.getElementById('forgotPasswordModal');

    if (modal) {
        modal.classList.add('show');
    }
}

function closeForgotPasswordModal() {
    const modal = document.getElementById('forgotPasswordModal');

    if (modal) {
        modal.classList.remove('show');
    }
}

function handleForgotPasswordSubmit(event) {
    event.preventDefault();

    alert('Password reset request submitted. System administrator will review and respond.');

    closeForgotPasswordModal();
}

function showError(message) {
    const errorDiv = document.getElementById('login-error');

    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }
}

function showFieldError(elementId, message) {
    const errorDiv = document.getElementById(elementId);

    if (errorDiv) {
        errorDiv.textContent = message;
    }
}

function clearErrors() {
    const loginError = document.getElementById('login-error');

    if (loginError) {
        loginError.textContent = '';
        loginError.style.display = 'none';
    }

    ['username_error', 'password_error', 'moh_office_error'].forEach(function (id) {
        const element = document.getElementById(id);

        if (element) {
            element.textContent = '';
        }
    });
}

function resetSubmitButton(button, originalText) {
    if (button) {
        button.disabled = false;
        button.innerHTML = originalText;
    }
}

function escapeAdminHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}