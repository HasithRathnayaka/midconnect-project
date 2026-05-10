// document.addEventListener('DOMContentLoaded', function() {
//     initializeMidwifeLogin();
// });

// function initializeMidwifeLogin() {
//     const form = document.getElementById('midwifeLoginForm');
//     const forgotPasswordLink = document.getElementById('forgot-password-link');

//     form.addEventListener('submit', handleMidwifeLogin);

//     forgotPasswordLink.addEventListener('click', function(e) {
//         e.preventDefault();
//         showForgotPasswordModal();
//     });

//     const remembered = localStorage.getItem('remembered_credentials');
//     if (remembered) {
//         try {
//             const creds = JSON.parse(remembered);
//             document.getElementById('employee_id').value = creds.username || '';
//             document.getElementById('remember_me').checked = true;
//         } catch (e) {}
//     }
// }

// function handleMidwifeLogin(e) {
//     e.preventDefault();

//     const formData = new FormData(e.target);
//     const employeeId = formData.get('employee_id');
//     const password = formData.get('password');
//     const rememberMe = formData.get('remember_me') === 'on';

//     clearErrors();

//     if (!validateMidwifeLogin(employeeId, password)) {
//         return;
//     }

//     const submitBtn = e.target.querySelector('button[type="submit"]');
//     const originalText = submitBtn.innerHTML;

//     submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Authenticating...';
//     submitBtn.disabled = true;

//     setTimeout(() => {
//         const validCredentials = [
//             {
//                 employee_id: 'MW001',
//                 password: 'password123',
//                 name: 'Madhavi Perera',
//                 email: 'madhavi.perera@health.gov.lk',
//                 area: 'Colombo Central',
//                 moh: 'MOH Colombo 01'
//             },
//             {
//                 employee_id: 'MW002',
//                 password: 'password123',
//                 name: 'Kumari Silva',
//                 email: 'kumari.silva@health.gov.lk',
//                 area: 'Colombo North',
//                 moh: 'MOH Colombo 01'
//             },
//             {
//                 employee_id: 'MW003',
//                 password: 'password123',
//                 name: 'Anura Fernando',
//                 email: 'anura.fernando@health.gov.lk',
//                 area: 'Colombo South',
//                 moh: 'MOH Colombo 02'
//             }
//         ];

//         const validUser = validCredentials.find(cred =>
//             cred.employee_id.toLowerCase() === employeeId.toLowerCase() &&
//             cred.password === password
//         );

//         if (validUser) {
//             if (rememberMe) {
//                 localStorage.setItem('remembered_credentials', JSON.stringify({
//                     username: validUser.employee_id,
//                     timestamp: Date.now()
//                 }));
//             } else {
//                 localStorage.removeItem('remembered_credentials');
//             }

//             const midwifeData = {
//                 id: validUser.employee_id,
//                 name: validUser.name,
//                 email: validUser.email,
//                 employee_id: validUser.employee_id,
//                 area: validUser.area,
//                 moh_office: validUser.moh,
//                 role: 'midwife',
//                 loginTime: new Date().toISOString()
//             };

//             localStorage.setItem('midwife_user', JSON.stringify(midwifeData));

//             submitBtn.innerHTML = '<i class="fas fa-check"></i> Success!';

//             setTimeout(() => {
//                 window.location.href = 'midwife_dashboard.php';
//             }, 500);
//         } else {
//             showError('Invalid Employee ID or password. Please check your credentials.');
//             submitBtn.innerHTML = originalText;
//             submitBtn.disabled = false;
//         }
//     }, 1000);
// }

// function validateMidwifeLogin(employeeId, password) {
//     let isValid = true;

//     if (!employeeId || employeeId.length < 3) {
//         showFieldError('employee_id_error', 'Employee ID must be at least 3 characters');
//         isValid = false;
//     }

//     if (!password || password.length < 6) {
//         showFieldError('password_error', 'Password must be at least 6 characters');
//         isValid = false;
//     }

//     return isValid;
// }

// function togglePassword(inputId) {
//     const input = document.getElementById(inputId);
//     if (!input) return;

//     const inputGroup = input.parentNode;
//     const button = inputGroup.querySelector('.password-toggle');
//     if (!button) return;

//     const icon = button.querySelector('i');

//     if (input.type === 'password') {
//         input.type = 'text';
//         icon.classList.remove('fa-eye');
//         icon.classList.add('fa-eye-slash');
//     } else {
//         input.type = 'password';
//         icon.classList.remove('fa-eye-slash');
//         icon.classList.add('fa-eye');
//     }
// }

// function showForgotPasswordModal() {
//     document.getElementById('forgotPasswordModal').classList.add('show');
// }

// function closeForgotPasswordModal() {
//     document.getElementById('forgotPasswordModal').classList.remove('show');
// }

// function showError(message) {
//     const errorDiv = document.getElementById('login-error');
//     errorDiv.textContent = message;
//     errorDiv.style.display = 'block';
// }

// function showFieldError(elementId, message) {
//     const errorDiv = document.getElementById(elementId);
//     if (errorDiv) {
//         errorDiv.textContent = message;
//     }
// }

// function clearErrors() {
//     document.getElementById('login-error').style.display = 'none';
//     document.getElementById('employee_id_error').textContent = '';
//     document.getElementById('password_error').textContent = '';
// }

// document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
//     e.preventDefault();

//     setTimeout(() => {
//         alert('Password reset request submitted. Your supervising MOH office will respond within 24-48 hours.');
//         closeForgotPasswordModal();
//     }, 1000);
// });

// document.getElementById('forgotPasswordModal').addEventListener('click', function(e) {
//     if (e.target === this) {
//         closeForgotPasswordModal();
//     }
// });




document.addEventListener('DOMContentLoaded', function () {
    initializeMidwifeLogin();
});

function initializeMidwifeLogin() {
    const form = document.getElementById('midwifeLoginForm');
    const forgotPasswordLink = document.getElementById('forgot-password-link');

    form.addEventListener('submit', handleMidwifeLogin);

    forgotPasswordLink.addEventListener('click', function (e) {
        e.preventDefault();
        showForgotPasswordModal();
    });

    const remembered = localStorage.getItem('remembered_employee_id');

    if (remembered) {
        document.getElementById('employee_id').value = remembered;
        document.getElementById('remember_me').checked = true;
    }
}

function handleMidwifeLogin(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const employeeId = formData.get('employee_id');
    const password = formData.get('password');
    const rememberMe = formData.get('remember_me') === 'on';

    clearErrors();

    if (!validateMidwifeLogin(employeeId, password)) {
        return;
    }

    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Authenticating...';
    submitBtn.disabled = true;

    fetch('php/midwife/midwife_login.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            console.log('Login Response:', data);

            if (data.success) {
                if (rememberMe) {
                    localStorage.setItem('remembered_employee_id', employeeId);
                } else {
                    localStorage.removeItem('remembered_employee_id');
                }

                localStorage.setItem('midwife_user', JSON.stringify(data.user));

                submitBtn.innerHTML = '<i class="fas fa-check"></i> Success!';

                setTimeout(() => {
                    window.location.href = 'midwife_dashboard.php';
                }, 500);
            } else {
                showError(data.message || 'Invalid Employee ID or password');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Login Error:', error);
            showError('Server error. Please try again.');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
}

function validateMidwifeLogin(employeeId, password) {
    let isValid = true;

    if (!employeeId || employeeId.length < 3) {
        showFieldError('employee_id_error', 'Employee ID must be at least 3 characters');
        isValid = false;
    }

    if (!password || password.length < 6) {
        showFieldError('password_error', 'Password must be at least 6 characters');
        isValid = false;
    }

    return isValid;
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const inputGroup = input.parentNode;
    const button = inputGroup.querySelector('.password-toggle');
    if (!button) return;

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
}

function showForgotPasswordModal() {
    document.getElementById('forgotPasswordModal').classList.add('show');
}

function closeForgotPasswordModal() {
    document.getElementById('forgotPasswordModal').classList.remove('show');
}

function showError(message) {
    const errorDiv = document.getElementById('login-error');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

function showFieldError(elementId, message) {
    const errorDiv = document.getElementById(elementId);

    if (errorDiv) {
        errorDiv.textContent = message;
    }
}

function clearErrors() {
    document.getElementById('login-error').style.display = 'none';
    document.getElementById('employee_id_error').textContent = '';
    document.getElementById('password_error').textContent = '';
}

document.getElementById('forgotPasswordForm').addEventListener('submit', function (e) {
    e.preventDefault();

    setTimeout(() => {
        alert('Password reset request submitted. Your supervising MOH office will respond within 24-48 hours.');
        closeForgotPasswordModal();
    }, 1000);
});

document.getElementById('forgotPasswordModal').addEventListener('click', function (e) {
    if (e.target === this) {
        closeForgotPasswordModal();
    }
});