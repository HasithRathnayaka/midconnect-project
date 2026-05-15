let allAdminMidwives = [];

document.addEventListener('DOMContentLoaded', function () {
    loadAdminMidwives();
    setupAdminMidwifeFilters();
    bindEditMidwifeForm();
});

function loadAdminMidwives() {
    fetch('../php/admin/get_midwives.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.text();
        })
        .then(function (text) {
            console.log('Admin midwives raw response:', text);

            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error('Invalid midwives JSON:', text);
                renderMidwivesError('Invalid server response.');
                return;
            }

            if (!data.success) {
                renderMidwivesError(data.message || 'Failed to load midwives.');
                return;
            }

            allAdminMidwives = data.midwives || [];

            populateAdminAreaFilter();
            renderAdminMidwives();
        })
        .catch(function (error) {
            console.error('Admin Midwife Load Error:', error);
            renderMidwivesError('Server error while loading midwives.');
        });
}

function setupAdminMidwifeFilters() {
    const searchInput = document.getElementById('searchMidwives');
    const statusFilter = document.getElementById('filterStatus');
    const areaFilter = document.getElementById('filterArea');

    if (searchInput) {
        searchInput.addEventListener('input', renderAdminMidwives);
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', renderAdminMidwives);
    }

    if (areaFilter) {
        areaFilter.addEventListener('change', renderAdminMidwives);
    }
}

function populateAdminAreaFilter() {
    const areaFilter = document.getElementById('filterArea');

    if (!areaFilter) return;

    const selectedValue = areaFilter.value;

    const uniqueAreas = [...new Set(
        allAdminMidwives
            .map(function (midwife) {
                return midwife.assigned_area || '';
            })
            .filter(function (area) {
                return area.trim() !== '';
            })
    )];

    areaFilter.innerHTML = '<option value="">All Areas</option>';

    uniqueAreas.forEach(function (area) {
        areaFilter.innerHTML += `
            <option value="${escapeAdminAttribute(area)}">
                ${escapeAdminHtml(formatAdminArea(area))}
            </option>
        `;
    });

    areaFilter.value = selectedValue;
}

function renderAdminMidwives() {
    const grid = document.getElementById('midwivesGrid');

    if (!grid) return;

    const records = getFilteredAdminMidwives();

    if (!records.length) {
        grid.innerHTML = `
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center text-muted">
                        No midwives found.
                    </div>
                </div>
            </div>
        `;
        return;
    }

    grid.innerHTML = records.map(function (midwife) {
        return `
            <div class="col-6">
                <div class="card midwife-card">
                    <div class="card-body">
                        <div class="d-flex justify-between align-center">
                            <div>
                                <h5>${escapeAdminHtml(midwife.full_name || '-')}</h5>
                                <p>Employee ID: ${escapeAdminHtml(midwife.employee_id || '-')}</p>
                                <p>Area: ${escapeAdminHtml(formatAdminArea(midwife.assigned_area || '-'))}</p>
                                <p>MOH Office: ${escapeAdminHtml(midwife.moh_office || '-')}</p>
                            </div>

                            <div class="text-right">
                                ${renderAdminStatusBadge(midwife.status)}

                                <div class="mt-2">
                                    <button
                                        class="btn btn-info btn-sm"
                                        type="button"
                                        onclick="viewMidwifeDetails('${escapeAdminAttribute(midwife.employee_id)}')"
                                    >
                                        View
                                    </button>

                                    <button
                                        class="btn btn-warning btn-sm"
                                        type="button"
                                        onclick="editMidwife('${escapeAdminAttribute(midwife.employee_id)}')"
                                    >
                                        Edit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function getFilteredAdminMidwives() {
    const searchInput = document.getElementById('searchMidwives');
    const statusFilter = document.getElementById('filterStatus');
    const areaFilter = document.getElementById('filterArea');

    const search = searchInput ? searchInput.value.trim().toLowerCase() : '';
    const status = statusFilter ? statusFilter.value.trim().toLowerCase() : '';
    const area = areaFilter ? areaFilter.value.trim().toLowerCase() : '';

    return allAdminMidwives.filter(function (midwife) {
        const searchMatch =
            search === '' ||
            String(midwife.full_name || '').toLowerCase().includes(search) ||
            String(midwife.employee_id || '').toLowerCase().includes(search) ||
            String(midwife.email || '').toLowerCase().includes(search) ||
            String(midwife.phone || '').toLowerCase().includes(search) ||
            String(midwife.assigned_area || '').toLowerCase().includes(search);

        const statusMatch =
            status === '' ||
            String(midwife.status || '').toLowerCase() === status;

        const areaMatch =
            area === '' ||
            String(midwife.assigned_area || '').toLowerCase() === area;

        return searchMatch && statusMatch && areaMatch;
    });
}

function viewMidwifeDetails(employeeId) {
    const midwife = findMidwifeByEmployeeId(employeeId);

    if (!midwife) {
        alert('Midwife not found.');
        return;
    }

    setAdminText('viewMidwifeFullName', midwife.full_name);
    setAdminText('viewMidwifeEmployeeId', midwife.employee_id);
    setAdminText('viewMidwifeEmail', midwife.email);
    setAdminText('viewMidwifePhone', midwife.phone);
    setAdminText('viewMidwifeArea', formatAdminArea(midwife.assigned_area));
    setAdminText('viewMidwifeMohOffice', midwife.moh_office);
    setAdminText('viewMidwifeHireDate', formatAdminDate(midwife.hire_date));
    setAdminText('viewMidwifeStatus', formatAdminStatus(midwife.status));
    setAdminText('viewMidwifeAddress', midwife.address);
    setAdminText('viewMidwifeLastLogin', formatAdminDateTime(midwife.last_login));

    $('#viewMidwifeModal').modal('show');
}

function editMidwife(employeeId) {
    const midwife = findMidwifeByEmployeeId(employeeId);

    if (!midwife) {
        alert('Midwife not found.');
        return;
    }

    setAdminInputValue('editMidwifeId', midwife.midwife_id);
    setAdminInputValue('editMidwifeFullName', midwife.full_name);
    setAdminInputValue('editMidwifeEmployeeId', midwife.employee_id);
    setAdminInputValue('editMidwifeEmail', midwife.email);
    setAdminInputValue('editMidwifePhone', midwife.phone);
    setAdminInputValue('editMidwifeArea', midwife.assigned_area);
    setAdminInputValue('editMidwifeMohOffice', midwife.moh_office);
    setAdminInputValue('editMidwifeStatus', midwife.status || 'active');
    setAdminInputValue('editMidwifeExperienceYears', midwife.experience_years || 0);
    setAdminInputValue('editMidwifeAddress', midwife.address);

    $('#editMidwifeModal').modal('show');
}

function bindEditMidwifeForm() {
    const form = document.getElementById('editMidwifeForm');

    if (!form) return;

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.innerHTML : '';

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        }

        fetch(form.action, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(function (response) {
                return response.text();
            })
            .then(function (text) {
                console.log('Update midwife raw response:', text);

                let data;

                try {
                    data = JSON.parse(text);
                } catch (error) {
                    console.error('Invalid update midwife JSON:', text);
                    alert('Invalid server response.');
                    return;
                }

                alert(data.message);

                if (data.success) {
                    $('#editMidwifeModal').modal('hide');
                    loadAdminMidwives();
                }

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(function (error) {
                console.error('Update Midwife Error:', error);
                alert('Server error while updating midwife.');

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
    });
}

function findMidwifeByEmployeeId(employeeId) {
    return allAdminMidwives.find(function (midwife) {
        return String(midwife.employee_id) === String(employeeId);
    });
}

function renderMidwivesError(message) {
    const grid = document.getElementById('midwivesGrid');

    if (!grid) return;

    grid.innerHTML = `
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center text-muted">
                    ${escapeAdminHtml(message || 'Failed to load midwives.')}
                </div>
            </div>
        </div>
    `;
}

function renderAdminStatusBadge(status) {
    const normalized = String(status || 'active').toLowerCase();

    let className = 'status-badge status-active';

    if (normalized === 'inactive') {
        className = 'status-badge status-danger';
    }

    if (normalized === 'on-leave') {
        className = 'status-badge status-warning';
    }

    return `
        <div class="${className}">
            ${escapeAdminHtml(formatAdminStatus(status || 'active'))}
        </div>
    `;
}

function setAdminText(id, value) {
    const element = document.getElementById(id);

    if (element) {
        element.textContent = value && String(value).trim() !== '' ? value : '-';
    }
}

function setAdminInputValue(id, value) {
    const element = document.getElementById(id);

    if (element) {
        element.value = value ?? '';
    }
}

function formatAdminArea(value) {
    if (!value) return '-';

    return String(value)
        .replaceAll('_', ' ')
        .replaceAll('-', ' ')
        .replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });
}

function formatAdminStatus(value) {
    if (!value) return '-';

    return String(value)
        .replaceAll('_', ' ')
        .replaceAll('-', ' ')
        .replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });
}

function formatAdminDate(value) {
    if (!value) return '-';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toISOString().split('T')[0];
}

function formatAdminDateTime(value) {
    if (!value) return '-';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleString();
}

function escapeAdminHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function escapeAdminAttribute(value) {
    return escapeAdminHtml(value);
}