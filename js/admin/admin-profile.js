document.addEventListener('DOMContentLoaded', function () {
    loadAdminProfile();
});

function loadAdminProfile() {
    showProfileLoading();

    fetch('../php/admin/get_admin_profile.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.text();
        })
        .then(function (text) {
            console.log('Admin profile raw response:', text);

            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error('Invalid admin profile JSON:', text);
                showProfileError('Invalid server response. Check PHP errors.');
                return;
            }

            if (data.success !== true) {
                showProfileError(data.message || 'Failed to load admin profile.');
                return;
            }

            renderAdminProfile(data.admin || {});
        })
        .catch(function (error) {
            console.error('Admin profile loading error:', error);
            showProfileError('Server error while loading admin profile.');
        });
}

function renderAdminProfile(admin) {
    const fullName = valueOrDash(admin.full_name);
    const username = valueOrDash(admin.username);
    const email = valueOrDash(admin.email);
    const phone = valueOrDash(admin.phone);
    const mohOffice = valueOrDash(admin.moh_office);
    const position = valueOrDash(admin.position);
    const isActive = Number(admin.is_active) === 1;

    setText('profileFullName', fullName);
    setText('profilePosition', position);

    setText('profileMohOfficeHeader', mohOffice);
    setText('profileUsernameHeader', username);

    setHtml('profileStatusHeader', renderStatusBadge(isActive));

    setText('profileFullNameInfo', fullName);
    setText('profileUsername', username);
    setText('profileEmail', email);
    setText('profilePhone', phone);

    setText('profileMohOffice', mohOffice);
    setText('profilePositionInfo', position);
    setHtml('profileStatus', renderStatusBadge(isActive));

    setText('profileCreatedAt', formatDateTime(admin.created_at));
    setText('profileUpdatedAt', formatDateTime(admin.updated_at));
    setText('profileLastLogin', formatDateTime(admin.last_login));

    hideProfileLoading();
}

function renderStatusBadge(isActive) {
    if (isActive) {
        return `
            <span class="status-pill active">
                <i class="fas fa-check-circle"></i> Active
            </span>
        `;
    }

    return `
        <span class="status-pill inactive">
            <i class="fas fa-times-circle"></i> Inactive
        </span>
    `;
}

function showProfileLoading() {
    const loading = document.getElementById('profileLoading');
    const error = document.getElementById('profileError');
    const content = document.getElementById('profileContent');

    if (loading) loading.style.display = 'block';
    if (error) error.style.display = 'none';
    if (content) content.style.display = 'none';
}

function hideProfileLoading() {
    const loading = document.getElementById('profileLoading');
    const error = document.getElementById('profileError');
    const content = document.getElementById('profileContent');

    if (loading) loading.style.display = 'none';
    if (error) error.style.display = 'none';
    if (content) content.style.display = 'block';
}

function showProfileError(message) {
    const loading = document.getElementById('profileLoading');
    const error = document.getElementById('profileError');
    const content = document.getElementById('profileContent');

    if (loading) loading.style.display = 'none';

    if (error) {
        error.style.display = 'block';
        error.textContent = message;
    }

    if (content) content.style.display = 'none';
}

function setText(id, value) {
    const element = document.getElementById(id);

    if (element) {
        element.textContent = value;
    }
}

function setHtml(id, value) {
    const element = document.getElementById(id);

    if (element) {
        element.innerHTML = value;
    }
}

function valueOrDash(value) {
    if (value === null || value === undefined) {
        return '-';
    }

    const text = String(value).trim();

    return text !== '' ? text : '-';
}

function formatDateTime(value) {
    if (!value) return '-';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return valueOrDash(value);
    }

    return date.toLocaleString('en-GB', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}