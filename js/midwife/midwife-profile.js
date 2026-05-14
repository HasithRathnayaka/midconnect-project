let currentMidwifeProfile = null;

document.addEventListener('DOMContentLoaded', function () {
    loadMidwifeProfile();
});

function loadMidwifeProfile() {
    fetch('../php/midwife/get_profile.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.text();
        })
        .then(function (text) {
            console.log('Profile raw response:', text);

            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error('Invalid profile JSON:', text);
                alert('Profile loading failed. PHP returned non-JSON response.');
                return;
            }

            if (!data.success) {
                console.error(data.message || 'Failed to load profile.');
                return;
            }

            currentMidwifeProfile = data.profile;
            renderMidwifeProfile(data.profile);
        })
        .catch(function (error) {
            console.error('Profile Load Error:', error);
        });
}

function renderMidwifeProfile(profile) {
    setProfileText('profileFullName', profile.full_name);
    setProfileText('profileEmployeeId', profile.employee_id);
    setProfileText('profileStatus', formatProfileStatus(profile.status));

    setProfileText('profileInfoFullName', profile.full_name);
    setProfileText('profileInfoBirthDate', formatProfileDate(profile.birth_date));
    setProfileText('profileInfoEmail', profile.email);
    setProfileText('profileInfoPhone', profile.phone);
    setProfileText('profileInfoAddress', profile.address);

    setProfileText('profileInfoAssignedArea', formatProfileArea(profile.assigned_area));
    setProfileText('profileInfoMohOffice', profile.moh_office);
    setProfileText('profileInfoHireDate', formatProfileDate(profile.hire_date));
    setProfileText('profileInfoExperienceYears', profile.experience_years ? profile.experience_years + ' years' : '-');
    setProfileText('profileInfoLastLogin', formatProfileDateTime(profile.last_login));

    setProfileText('quickAssignedArea', formatProfileArea(profile.assigned_area));
    setProfileText('quickMohOffice', profile.moh_office);
    setProfileText('quickExperienceYears', profile.experience_years ? profile.experience_years + ' years' : '-');
    setProfileText('quickLastLogin', formatProfileDateTime(profile.last_login));

    const profileImage = document.getElementById('profileImage');

    if (profileImage && profile.profile_image) {
        profileImage.src = profile.profile_image;
    }
}
function editProfile() {
    if (!currentMidwifeProfile) {
        alert('Profile data is still loading.');
        return;
    }

    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.id = 'editProfileOverlay';

    modal.innerHTML = `
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h3>Edit Profile Information</h3>
                <button type="button" onclick="closeProfileModal('editProfileOverlay')" class="close-btn">&times;</button>
            </div>

            <div class="modal-body">
                <form id="editProfileForm">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            name="full_name" 
                            value="${escapeProfileAttribute(currentMidwifeProfile.full_name)}" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            name="phone" 
                            value="${escapeProfileAttribute(currentMidwifeProfile.phone)}"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea 
                            class="form-control" 
                            name="address" 
                            rows="3"
                        >${escapeProfileHtml(currentMidwifeProfile.address)}</textarea>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeProfileModal('editProfileOverlay')">Cancel</button>
                <button type="submit" form="editProfileForm" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    const form = document.getElementById('editProfileForm');

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        submitProfileUpdate(form);
    });
}


function submitProfileUpdate(form) {
    const formData = new FormData(form);

    const submitBtn = document.querySelector('button[form="editProfileForm"]');
    const originalText = submitBtn ? submitBtn.innerHTML : '';

    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    }

    fetch('../php/midwife/update_profile.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.text();
        })
        .then(function (text) {
            console.log('Update profile raw response:', text);

            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error('Invalid update profile JSON:', text);
                alert('Profile update failed. PHP returned non-JSON response.');

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }

                return;
            }

            alert(data.message);

            if (data.success === true) {
                window.location.href = '/midwife/dashboard.php?section=profile&_=' + Date.now();
                return;
            }

            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(function (error) {
            console.error('Profile Update Error:', error);
            alert('Server error while updating profile.');

            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
}

function changePassword() {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.id = 'changePasswordOverlay';

    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Change Password</h3>
                <button type="button" onclick="closeProfileModal('changePasswordOverlay')" class="close-btn">&times;</button>
            </div>

            <div class="modal-body">
                <form id="changePasswordForm">
                    <div class="form-group">
                        <label class="form-label">Current Password *</label>
                        <input 
                            type="password" 
                            class="form-control" 
                            name="current_password" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">New Password *</label>
                        <input 
                            type="password" 
                            class="form-control" 
                            name="new_password" 
                            required 
                            minlength="8"
                        >
                        <small class="form-text">Password must be at least 8 characters long.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm New Password *</label>
                        <input 
                            type="password" 
                            class="form-control" 
                            name="confirm_password" 
                            required 
                            minlength="8"
                        >
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeProfileModal('changePasswordOverlay')">Cancel</button>
                <button type="submit" form="changePasswordForm" class="btn btn-primary">
                    <i class="fas fa-key"></i> Change Password
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    const form = document.getElementById('changePasswordForm');

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        submitPasswordChange(form);
    });
}

function submitPasswordChange(form) {
    const formData = new FormData(form);

    const submitBtn = document.querySelector('button[form="changePasswordForm"]');
    const originalText = submitBtn ? submitBtn.innerHTML : '';

    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Changing...';
    }

    fetch('../php/midwife/change_password.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.text();
        })
        .then(function (text) {
            console.log('Change password raw response:', text);

            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error('Invalid change password JSON:', text);
                alert('Password change failed. PHP returned non-JSON response.');

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }

                return;
            }

            alert(data.message);

            if (data.success === true) {
                window.location.href = '/midwife/dashboard.php?section=profile&_=' + Date.now();
                return;
            }

            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(function (error) {
            console.error('Change Password Error:', error);
            alert('Server error while changing password.');

            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
}

function closeProfileModal(id) {
    const modal = document.getElementById(id);

    if (modal) {
        modal.remove();
    }
}

function setProfileText(id, value) {
    const element = document.getElementById(id);

    if (element) {
        element.textContent = value && String(value).trim() !== '' ? value : '-';
    }
}

function formatProfileStatus(status) {
    if (!status) return '-';

    return String(status)
        .replaceAll('_', ' ')
        .replaceAll('-', ' ')
        .replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });
}

function formatProfileArea(value) {
    const map = {
        uduthuththiripitiya: 'Uduthuththiripitiya',
        kahabilihena: 'Kahabilihena',
        opathella: 'Opathella',
        ambalangoda: 'Ambalangoda'
    };

    const normalized = String(value || '').trim().toLowerCase();

    return map[normalized] || value || '-';
}

function formatProfileDate(value) {
    if (!value) return '-';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toISOString().split('T')[0];
}

function formatProfileDateTime(value) {
    if (!value) return '-';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleString();
}

function escapeProfileHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function escapeProfileAttribute(value) {
    return escapeProfileHtml(value);
}