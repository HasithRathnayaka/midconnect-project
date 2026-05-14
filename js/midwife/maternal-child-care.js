

window.allMaternalCareRecords = [];
window.allChildCareRecords = [];

const careDutyAreas = [
    'uduthuththiripitiya',
    'kahabilihena',
    'opathella',
    'ambalangoda'
];

document.addEventListener('DOMContentLoaded', function () {
    setupMaternalChildForms();
    setupCareSearch();
    loadMaternalChildCareData();
});

function loadMaternalChildCareData() {
    fetch('../php/midwife/get_maternal_child_care.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            console.log('Maternal child care response:', data);

            if (!data.success) {
                console.error(data.message || 'Failed to load maternal and child care records.');
                window.allMaternalCareRecords = [];
                window.allChildCareRecords = [];
                renderMaternalChildCare();
                return;
            }

            window.allMaternalCareRecords = data.mothers || [];
            window.allChildCareRecords = data.children || [];

            renderMaternalChildCare();
        })
        .catch(function (error) {
            console.error('Maternal Child Care Load Error:', error);
            window.allMaternalCareRecords = [];
            window.allChildCareRecords = [];
            renderMaternalChildCare();
        });
}

function renderMaternalChildCare() {
    renderCareAreaButtons();
    updateCareHeader();
    showCurrentCareTabs();
    renderMotherTable();
    renderChildTable();
}

function renderCareAreaButtons() {
    const grid = document.getElementById('careAreaGrid');
    if (!grid) return;

    grid.innerHTML = careDutyAreas.map(function (area) {
        const mothersCount = window.allMaternalCareRecords.filter(function (record) {
            return normalizeCareArea(record.duty_area) === normalizeCareArea(area);
        }).length;

        const childrenCount = window.allChildCareRecords.filter(function (record) {
            return normalizeCareArea(record.duty_area) === normalizeCareArea(area);
        }).length;

        const totalCount = mothersCount + childrenCount;

        return `
            <div class="duty-area-btn ${selectedCareArea === area ? 'active' : ''}" onclick="switchMaternalChildArea('${area}')">
                <i class="fas fa-map-marker-alt"></i>
                <h5>${formatCareArea(area)}</h5>
                <div class="area-count">${totalCount} active records</div>
            </div>
        `;
    }).join('');
}

function switchMaternalChildArea(area) {
    selectedCareArea = area;

    selectedCareMainTab = 'mothers';
    selectedMotherTab = 'pregnant';
    selectedChildTab = 'newborns';

    renderMaternalChildCare();
}

function switchCareTab(tab, event) {
    if (event) event.preventDefault();

    selectedCareMainTab = tab;

    showCurrentCareTabs();
    renderMotherTable();
    renderChildTable();
}

function switchMotherTab(tab, event) {
    if (event) event.preventDefault();

    selectedMotherTab = tab;

    showCurrentCareTabs();
    renderMotherTable();
}

function switchChildrenTab(tab, event) {
    if (event) event.preventDefault();

    selectedChildTab = tab;

    showCurrentCareTabs();
    renderChildTable();
}

function showCurrentCareTabs() {
    const mothersTab = document.getElementById('mothers-tab');
    const childrenTab = document.getElementById('children-tab');

    if (mothersTab) {
        mothersTab.style.display = selectedCareMainTab === 'mothers' ? 'block' : 'none';
        mothersTab.classList.toggle('hidden', selectedCareMainTab !== 'mothers');
    }

    if (childrenTab) {
        childrenTab.style.display = selectedCareMainTab === 'children' ? 'block' : 'none';
        childrenTab.classList.toggle('hidden', selectedCareMainTab !== 'children');
    }

    document.querySelectorAll('[data-care-main-tab]').forEach(function (link) {
        link.classList.toggle('active', link.getAttribute('data-care-main-tab') === selectedCareMainTab);
    });

    document.querySelectorAll('[data-mother-tab]').forEach(function (link) {
        link.classList.toggle('active', link.getAttribute('data-mother-tab') === selectedMotherTab);
    });

    document.querySelectorAll('[data-child-tab]').forEach(function (link) {
        link.classList.toggle('active', link.getAttribute('data-child-tab') === selectedChildTab);
    });

    updateMotherHeader();
    updateChildHeader();
}

function updateCareHeader() {
    const title = document.getElementById('careAreaTitle');
    const subtitle = document.getElementById('careAreaSubtitle');

    if (!title || !subtitle) return;

    const areaName = formatCareArea(selectedCareArea);

    const mothers = getFilteredMothersByArea();
    const children = getFilteredChildrenByArea();

    const pregnant = mothers.filter(r => r.category === 'pregnant').length;
    const lactating = mothers.filter(r => r.category === 'lactating').length;
    const postnatal = mothers.filter(r => r.category === 'postnatal').length;

    title.innerHTML = `<i class="fas fa-map-marker-alt"></i> ${escapeHtmlCare(areaName)} Area - Maternal &amp; Child Care`;
    subtitle.textContent = `Pregnant: ${pregnant}, Lactating: ${lactating}, Postnatal: ${postnatal}, Children: ${children.length}`;
}

function updateMotherHeader() {
    const title = document.getElementById('motherTableTitle');
    const buttonText = document.getElementById('motherAddButtonText');

    const labels = {
        pregnant: 'Pregnant Mothers',
        lactating: 'Lactating Mothers',
        postnatal: 'Postnatal Mothers'
    };

    if (title) title.textContent = labels[selectedMotherTab] || 'Mothers';
    if (buttonText) buttonText.textContent = 'Add ' + (labels[selectedMotherTab] || 'Mother');

    renderMotherTableHead();
}

function updateChildHeader() {
    const title = document.getElementById('childTableTitle');
    const buttonText = document.getElementById('childAddButtonText');

    const labels = {
        newborns: 'Newborns',
        young: 'Young Children',
        childs: 'Childs'
    };

    if (title) title.textContent = labels[selectedChildTab] || 'Children';
    if (buttonText) buttonText.textContent = 'Add ' + (labels[selectedChildTab] || 'Child');

    renderChildTableHead();
}

function renderMotherTableHead() {
    const thead = document.getElementById('motherTableHead');
    if (!thead) return;

    if (selectedMotherTab === 'pregnant') {
        thead.innerHTML = `
            <tr>
                <th>Mother's Name</th>
                <th>Age</th>
                <th>Weeks Pregnant</th>
                <th>Last Visit</th>
                <th>Next Appointment</th>
                <th>Risk Level</th>
                <th>Actions</th>
            </tr>
        `;
        return;
    }

    if (selectedMotherTab === 'lactating') {
        thead.innerHTML = `
            <tr>
                <th>Mother's Name</th>
                <th>Age</th>
                <th>Baby's Age</th>
                <th>Breastfeeding Status</th>
                <th>Last Visit</th>
                <th>Support Level</th>
                <th>Actions</th>
            </tr>
        `;
        return;
    }

    thead.innerHTML = `
        <tr>
            <th>Mother's Name</th>
            <th>Age</th>
            <th>Delivery Date</th>
            <th>Delivery Type</th>
            <th>Recovery Status</th>
            <th>Last Visit</th>
            <th>Actions</th>
        </tr>
    `;
}

function renderMotherTable() {
    const tbody = document.getElementById('motherTableBody');
    if (!tbody) return;

    const records = getFilteredMotherRecords();

    if (records.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-muted">No records found.</td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = records.map(function (record) {
        if (selectedMotherTab === 'pregnant') {
            return `
                <tr>
                    <td>${escapeHtmlCare(record.mother_name)}</td>
                    <td>${escapeHtmlCare(record.age || '-')}</td>
                    <td>${escapeHtmlCare(record.weeks_pregnant ? record.weeks_pregnant + ' weeks' : '-')}</td>
                    <td>${formatCareDate(record.last_visit)}</td>
                    <td>${formatCareDate(record.next_appointment)}</td>
                    <td>${renderCareBadge(record.risk_level || 'Low Risk')}</td>
                    <td>
                        <button class="btn btn-info btn-sm" onclick="viewMotherRecord(${record.mother_id})">View</button>
                    </td>
                </tr>
            `;
        }

        if (selectedMotherTab === 'lactating') {
            return `
                <tr>
                    <td>${escapeHtmlCare(record.mother_name)}</td>
                    <td>${escapeHtmlCare(record.age || '-')}</td>
                    <td>${escapeHtmlCare(record.baby_age || '-')}</td>
                    <td>${escapeHtmlCare(record.breastfeeding_status || '-')}</td>
                    <td>${formatCareDate(record.last_visit)}</td>
                    <td>${renderCareBadge(record.support_level || 'Good Support')}</td>
                    <td>
                        <button class="btn btn-info btn-sm" onclick="viewMotherRecord(${record.mother_id})">View</button>
                    </td>
                </tr>
            `;
        }

        return `
            <tr>
                <td>${escapeHtmlCare(record.mother_name)}</td>
                <td>${escapeHtmlCare(record.age || '-')}</td>
                <td>${formatCareDate(record.delivery_date)}</td>
                <td>${escapeHtmlCare(record.delivery_type || '-')}</td>
                <td>${escapeHtmlCare(record.recovery_status || '-')}</td>
                <td>${formatCareDate(record.last_visit)}</td>
                <td>
                    <button class="btn btn-info btn-sm" onclick="viewMotherRecord(${record.mother_id})">View</button>
                </td>
            </tr>
        `;
    }).join('');
}

function renderChildTableHead() {
    const thead = document.getElementById('childTableHead');
    if (!thead) return;

    if (selectedChildTab === 'newborns') {
        thead.innerHTML = `
            <tr>
                <th>Baby's Name</th>
                <th>Mother's Name</th>
                <th>Date of Birth</th>
                <th>Birth Weight</th>
                <th>Last Check-up</th>
                <th>Health Status</th>
                <th>Actions</th>
            </tr>
        `;
        return;
    }

    if (selectedChildTab === 'young') {
        thead.innerHTML = `
            <tr>
                <th>Child's Name</th>
                <th>Age</th>
                <th>Mother's Name</th>
                <th>Weight</th>
                <th>Height</th>
                <th>Development Status</th>
                <th>Actions</th>
            </tr>
        `;
        return;
    }

    thead.innerHTML = `
        <tr>
            <th>Child's Name</th>
            <th>Age</th>
            <th>Mother's Name</th>
            <th>School</th>
            <th>Last Visit</th>
            <th>Health Status</th>
            <th>Actions</th>
        </tr>
    `;
}

function renderChildTable() {
    const tbody = document.getElementById('childTableBody');
    if (!tbody) return;

    const records = getFilteredChildRecords();

    if (records.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-muted">No records found.</td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = records.map(function (record) {
        if (selectedChildTab === 'newborns') {
            return `
                <tr>
                    <td>${escapeHtmlCare(record.child_name)}</td>
                    <td>${escapeHtmlCare(record.mother_name || '-')}</td>
                    <td>${formatCareDate(record.date_of_birth)}</td>
                    <td>${escapeHtmlCare(record.birth_weight ? record.birth_weight + ' kg' : '-')}</td>
                    <td>${formatCareDate(record.last_checkup)}</td>
                    <td>${renderCareBadge(record.health_status || 'Healthy')}</td>
                    <td>
                        <button class="btn btn-info btn-sm" onclick="viewChildRecord(${record.child_id})">View</button>
                    </td>
                </tr>
            `;
        }

        if (selectedChildTab === 'young') {
            return `
                <tr>
                    <td>${escapeHtmlCare(record.child_name)}</td>
                    <td>${escapeHtmlCare(record.age_label || '-')}</td>
                    <td>${escapeHtmlCare(record.mother_name || '-')}</td>
                    <td>${escapeHtmlCare(record.current_weight ? record.current_weight + ' kg' : '-')}</td>
                    <td>${escapeHtmlCare(record.height_cm ? record.height_cm + ' cm' : '-')}</td>
                    <td>${renderCareBadge(record.development_status || 'Normal')}</td>
                    <td>
                        <button class="btn btn-info btn-sm" onclick="viewChildRecord(${record.child_id})">View</button>
                    </td>
                </tr>
            `;
        }

        return `
            <tr>
                <td>${escapeHtmlCare(record.child_name)}</td>
                <td>${escapeHtmlCare(record.age_label || '-')}</td>
                <td>${escapeHtmlCare(record.mother_name || '-')}</td>
                <td>${escapeHtmlCare(record.school || '-')}</td>
                <td>${formatCareDate(record.last_checkup)}</td>
                <td>${renderCareBadge(record.health_status || 'Healthy')}</td>
                <td>
                    <button class="btn btn-info btn-sm" onclick="viewChildRecord(${record.child_id})">View</button>
                </td>
            </tr>
        `;
    }).join('');
}

function getFilteredMothersByArea() {
    return window.allMaternalCareRecords.filter(function (record) {
        return normalizeCareArea(record.duty_area) === normalizeCareArea(selectedCareArea);
    });
}

function getFilteredChildrenByArea() {
    return window.allChildCareRecords.filter(function (record) {
        return normalizeCareArea(record.duty_area) === normalizeCareArea(selectedCareArea);
    });
}

function getFilteredMotherRecords() {
    const search = getCareSearchTerm();

    return getFilteredMothersByArea().filter(function (record) {
        const categoryMatches = record.category === selectedMotherTab;
        const searchMatches = search === '' ||
            String(record.mother_name || '').toLowerCase().includes(search) ||
            String(record.contact_number || '').toLowerCase().includes(search) ||
            String(record.address || '').toLowerCase().includes(search);

        return categoryMatches && searchMatches;
    });
}

function getFilteredChildRecords() {
    const search = getCareSearchTerm();

    return getFilteredChildrenByArea().filter(function (record) {
        const categoryMatches = record.child_category === selectedChildTab;
        const searchMatches = search === '' ||
            String(record.child_name || '').toLowerCase().includes(search) ||
            String(record.mother_name || '').toLowerCase().includes(search) ||
            String(record.school || '').toLowerCase().includes(search);

        return categoryMatches && searchMatches;
    });
}

function setupCareSearch() {
    const input = document.getElementById('careSearchInput');

    if (!input) return;

    input.addEventListener('input', function () {
        renderMotherTable();
        renderChildTable();
    });
}

function getCareSearchTerm() {
    const input = document.getElementById('careSearchInput');
    return input ? input.value.trim().toLowerCase() : '';
}

function openMaternalRecordModal() {
    const form = document.getElementById('maternalRecordForm');

    if (form) {
        form.reset();
    }

    setInputValue('maternalDutyArea', selectedCareArea);
    setInputValue('maternalCategory', selectedMotherTab);

    updateMaternalModalFields();

    const title = document.getElementById('maternalRecordModalLabel');
    if (title) {
        title.textContent = 'Add ' + getMotherTabLabel(selectedMotherTab);
    }

    $('#maternalRecordModal').modal('show');
}

function openChildRecordModal() {
    const form = document.getElementById('childRecordForm');

    if (form) {
        form.reset();
    }

    setInputValue('childDutyArea', selectedCareArea);
    setInputValue('childCategory', selectedChildTab);

    updateChildModalFields();

    const title = document.getElementById('childRecordModalLabel');
    if (title) {
        title.textContent = 'Add ' + getChildTabLabel(selectedChildTab);
    }

    $('#childRecordModal').modal('show');
}

function updateMaternalModalFields() {
    setBlockDisplay('pregnantMotherFields', selectedMotherTab === 'pregnant');
    setBlockDisplay('lactatingMotherFields', selectedMotherTab === 'lactating');
    setBlockDisplay('postnatalMotherFields', selectedMotherTab === 'postnatal');
}

function updateChildModalFields() {
    setBlockDisplay('newbornFields', selectedChildTab === 'newborns');
    setBlockDisplay('youngChildFields', selectedChildTab === 'young');
    setBlockDisplay('schoolChildFields', selectedChildTab === 'childs');
}

function setupMaternalChildForms() {
    const maternalForm = document.getElementById('maternalRecordForm');

    if (maternalForm) {
        maternalForm.addEventListener('submit', function (event) {
            event.preventDefault();

            submitCareForm(maternalForm, function () {
                $('#maternalRecordModal').modal('hide');
                maternalForm.reset();
                loadMaternalChildCareData();
            });
        });
    }

    const childForm = document.getElementById('childRecordForm');

    if (childForm) {
        childForm.addEventListener('submit', function (event) {
            event.preventDefault();

            submitCareForm(childForm, function () {
                $('#childRecordModal').modal('hide');
                childForm.reset();
                loadMaternalChildCareData();
            });
        });
    }
}

function submitCareForm(form, successCallback) {
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
            return response.json();
        })
        .then(function (data) {
            alert(data.message);

            if (data.success && typeof successCallback === 'function') {
                successCallback();
            }

            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(function (error) {
            console.error('Care Form Error:', error);
            alert('Server error while saving record.');

            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
}

function viewMotherRecord(motherId) {
    const record = window.allMaternalCareRecords.find(function (item) {
        return Number(item.mother_id) === Number(motherId);
    });

    if (!record) {
        alert('Mother record not found.');
        return;
    }

    alert(
        'Mother: ' + (record.mother_name || '-') +
        '\nCategory: ' + getMotherTabLabel(record.category) +
        '\nContact: ' + (record.contact_number || '-') +
        '\nAddress: ' + (record.address || '-') +
        '\nNotes: ' + (record.notes || '-')
    );
}

function viewChildRecord(childId) {
    const record = window.allChildCareRecords.find(function (item) {
        return Number(item.child_id) === Number(childId);
    });

    if (!record) {
        alert('Child record not found.');
        return;
    }

    alert(
        'Child: ' + (record.child_name || '-') +
        '\nMother: ' + (record.mother_name || '-') +
        '\nCategory: ' + getChildTabLabel(record.child_category) +
        '\nHealth Status: ' + (record.health_status || '-') +
        '\nNotes: ' + (record.notes || '-')
    );
}

function getMotherTabLabel(tab) {
    const labels = {
        pregnant: 'Pregnant Mother',
        lactating: 'Lactating Mother',
        postnatal: 'Postnatal Mother'
    };

    return labels[tab] || 'Mother';
}

function getChildTabLabel(tab) {
    const labels = {
        newborns: 'Newborn',
        young: 'Young Child',
        childs: 'Child'
    };

    return labels[tab] || 'Child';
}

function formatCareArea(value) {
    const map = {
        uduthuththiripitiya: 'Uduthuththiripitiya',
        kahabilihena: 'Kahabilihena',
        opathella: 'Opathella',
        ambalangoda: 'Ambalangoda'
    };

    return map[normalizeCareArea(value)] || value || '-';
}

function normalizeCareArea(value) {
    return String(value || '').trim().toLowerCase();
}

function formatCareDate(dateString) {
    if (!dateString) return '-';

    const date = new Date(dateString);

    if (Number.isNaN(date.getTime())) {
        return '-';
    }

    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function renderCareBadge(value) {
    const text = escapeHtmlCare(value || '-');
    const normalized = String(value || '').toLowerCase();

    let className = 'status-badge status-success';

    if (
        normalized.includes('high') ||
        normalized.includes('critical') ||
        normalized.includes('delayed') ||
        normalized.includes('issue')
    ) {
        className = 'status-badge status-danger';
    } else if (
        normalized.includes('medium') ||
        normalized.includes('need') ||
        normalized.includes('monitor')
    ) {
        className = 'status-badge status-warning';
    }

    return `<span class="${className}">${text}</span>`;
}

function setInputValue(id, value) {
    const input = document.getElementById(id);

    if (input) {
        input.value = value;
    }
}

function setBlockDisplay(id, shouldShow) {
    const element = document.getElementById(id);

    if (element) {
        element.style.display = shouldShow ? 'block' : 'none';
    }
}

function escapeHtmlCare(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}



function prepareMaternalRecordModal() {
    const form = document.getElementById('maternalRecordForm');

    if (form) {
        form.reset();
    }

    setInputValue('maternalDutyArea', selectedCareArea);
    setInputValue('maternalCategory', selectedMotherTab);

    updateMaternalModalFields();

    const title = document.getElementById('maternalRecordModalLabel');
    if (title) {
        title.textContent = 'Add ' + getMotherTabLabel(selectedMotherTab);
    }
}

function prepareChildRecordModal() {
    const form = document.getElementById('childRecordForm');

    if (form) {
        form.reset();
    }

    setInputValue('childDutyArea', selectedCareArea);
    setInputValue('childCategory', selectedChildTab);

    updateChildModalFields();

    const title = document.getElementById('childRecordModalLabel');
    if (title) {
        title.textContent = 'Add ' + getChildTabLabel(selectedChildTab);
    }
}


function updateMotherAddButton() {
    const button = document.getElementById('motherAddButton');
    const text = document.getElementById('motherAddButtonText');

    if (!button || !text) return;

    if (selectedMotherTab === 'pregnant') {
        button.setAttribute('data-target', '#pregnantMotherModal');
        button.setAttribute('onclick', 'preparePregnantMotherModal()');
        text.textContent = 'Add Pregnant Mother';
    }

    if (selectedMotherTab === 'lactating') {
        button.setAttribute('data-target', '#lactatingMotherModal');
        button.setAttribute('onclick', 'prepareLactatingMotherModal()');
        text.textContent = 'Add Lactating Mother';
    }

    if (selectedMotherTab === 'postnatal') {
        button.setAttribute('data-target', '#postnatalMotherModal');
        button.setAttribute('onclick', 'preparePostnatalMotherModal()');
        text.textContent = 'Add Postnatal Mother';
    }
}

function updateChildAddButton() {
    const button = document.getElementById('childAddButton');
    const text = document.getElementById('childAddButtonText');

    if (!button || !text) return;

    if (selectedChildTab === 'newborns') {
        button.setAttribute('data-target', '#newbornModal');
        button.setAttribute('onclick', 'prepareNewbornModal()');
        text.textContent = 'Add Newborn';
    }

    if (selectedChildTab === 'young') {
        button.setAttribute('data-target', '#youngChildModal');
        button.setAttribute('onclick', 'prepareYoungChildModal()');
        text.textContent = 'Add Young Child';
    }

    if (selectedChildTab === 'childs') {
        button.setAttribute('data-target', '#childModal');
        button.setAttribute('onclick', 'prepareChildModal()');
        text.textContent = 'Add Child';
    }
}

function preparePregnantMotherModal() {
    resetFormById('pregnantMotherForm');
    setInputValue('pregnantMotherDutyArea', selectedCareArea);
}

function prepareLactatingMotherModal() {
    resetFormById('lactatingMotherForm');
    setInputValue('lactatingMotherDutyArea', selectedCareArea);
}

function preparePostnatalMotherModal() {
    resetFormById('postnatalMotherForm');
    setInputValue('postnatalMotherDutyArea', selectedCareArea);
}

function prepareNewbornModal() {
    resetFormById('newbornForm');
    setInputValue('newbornDutyArea', selectedCareArea);
}

function prepareYoungChildModal() {
    resetFormById('youngChildForm');
    setInputValue('youngChildDutyArea', selectedCareArea);
}

function prepareChildModal() {
    resetFormById('childForm');
    setInputValue('childDutyArea', selectedCareArea);
}

function resetFormById(formId) {
    const form = document.getElementById(formId);

    if (form) {
        form.reset();
    }
}

function setInputValue(id, value) {
    const element = document.getElementById(id);

    if (element) {
        element.value = value;
    }
}


document.addEventListener('DOMContentLoaded', function () {
    bindCareForm('pregnantMotherForm', '#pregnantMotherModal');
    bindCareForm('lactatingMotherForm', '#lactatingMotherModal');
    bindCareForm('postnatalMotherForm', '#postnatalMotherModal');
    bindCareForm('newbornForm', '#newbornModal');
    bindCareForm('youngChildForm', '#youngChildModal');
    bindCareForm('childForm', '#childModal');

    updateMotherAddButton();
    updateChildAddButton();
});

function bindCareForm(formId, modalId) {


    console.log('Binding form:', formId, 'to modal:', modalId);
    const form = document.getElementById(formId);

    if (!form) return;

    // Prevent duplicate event binding
    if (form.dataset.bound === 'true') return;
    form.dataset.bound = 'true';

    form.addEventListener('submit', function (e) {
        e.preventDefault();

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
                console.log('PHP raw response:', text);

                let data;

                try {
                    data = JSON.parse(text);
                } catch (error) {
                    console.error('Invalid JSON response:', text);
                    alert('Invalid server response. Check PHP errors.');
                    return;
                }

                alert(data.message);

                if (data.success === true) {
                    form.reset();

                    if (typeof $ !== 'undefined') {
                        $(modalId).modal('hide');
                    }

                    // Force reload dashboard and keep patients section
                    window.location.href = window.location.pathname + '?section=patients&_=' + Date.now();
                    return;
                }

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(function (error) {
                console.error('Care Form Submit Error:', error);
                alert('Server error while saving record.');

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
    });
}




function getSelectedCareAreaSafe() {
    return selectedCareArea || 'uduthuththiripitiya';
}

function setCareInputValue(id, value) {
    const el = document.getElementById(id);
    if (el) {
        el.value = value;
    }
}

function preparePregnantMotherModal() {
    setCareInputValue('pregnantMotherDutyArea', getSelectedCareAreaSafe());
}

function prepareLactatingMotherModal() {
    setCareInputValue('lactatingMotherDutyArea', getSelectedCareAreaSafe());
}

function preparePostnatalMotherModal() {
    setCareInputValue('postnatalMotherDutyArea', getSelectedCareAreaSafe());
}

function prepareNewbornModal() {
    setCareInputValue('newbornDutyArea', getSelectedCareAreaSafe());
}

function prepareYoungChildModal() {
    setCareInputValue('youngChildDutyArea', getSelectedCareAreaSafe());
}

function prepareChildModal() {
    setCareInputValue('childDutyArea', getSelectedCareAreaSafe());
}

function updateMotherAddButton() {
    const button = document.getElementById('motherAddButton');
    const text = document.getElementById('motherAddButtonText');

    if (!button || !text) return;

    if (selectedMotherTab === 'pregnant') {
        button.setAttribute('data-target', '#pregnantMotherModal');
        button.setAttribute('onclick', 'preparePregnantMotherModal()');
        text.textContent = 'Add Pregnant Mother';
    }

    if (selectedMotherTab === 'lactating') {
        button.setAttribute('data-target', '#lactatingMotherModal');
        button.setAttribute('onclick', 'prepareLactatingMotherModal()');
        text.textContent = 'Add Lactating Mother';
    }

    if (selectedMotherTab === 'postnatal') {
        button.setAttribute('data-target', '#postnatalMotherModal');
        button.setAttribute('onclick', 'preparePostnatalMotherModal()');
        text.textContent = 'Add Postnatal Mother';
    }
}

function updateChildAddButton() {
    const button = document.getElementById('childAddButton');
    const text = document.getElementById('childAddButtonText');

    if (!button || !text) return;

    if (selectedChildTab === 'newborns') {
        button.setAttribute('data-target', '#newbornModal');
        button.setAttribute('onclick', 'prepareNewbornModal()');
        text.textContent = 'Add Newborn';
    }

    if (selectedChildTab === 'young') {
        button.setAttribute('data-target', '#youngChildModal');
        button.setAttribute('onclick', 'prepareYoungChildModal()');
        text.textContent = 'Add Young Child';
    }

    if (selectedChildTab === 'childs') {
        button.setAttribute('data-target', '#childModal');
        button.setAttribute('onclick', 'prepareChildModal()');
        text.textContent = 'Add Child';
    }
}



function updateMotherAddButton() {
    const button = document.getElementById('motherAddButton');
    const buttonText = document.getElementById('motherAddButtonText');

    if (!button || !buttonText) return;

    if (selectedMotherTab === 'pregnant') {
        button.setAttribute('data-target', '#pregnantMotherModal');
        button.setAttribute('onclick', 'preparePregnantMotherModal()');
        buttonText.textContent = 'Add Pregnant Mother';
    }

    if (selectedMotherTab === 'lactating') {
        button.setAttribute('data-target', '#lactatingMotherModal');
        button.setAttribute('onclick', 'prepareLactatingMotherModal()');
        buttonText.textContent = 'Add Lactating Mother';
    }

    if (selectedMotherTab === 'postnatal') {
        button.setAttribute('data-target', '#postnatalMotherModal');
        button.setAttribute('onclick', 'preparePostnatalMotherModal()');
        buttonText.textContent = 'Add Postnatal Mother';
    }
}

function updateChildAddButton() {
    const button = document.getElementById('childAddButton');
    const buttonText = document.getElementById('childAddButtonText');

    if (!button || !buttonText) return;

    if (selectedChildTab === 'newborns') {
        button.setAttribute('data-target', '#newbornModal');
        button.setAttribute('onclick', 'prepareNewbornModal()');
        buttonText.textContent = 'Add Newborn';
    }

    if (selectedChildTab === 'young') {
        button.setAttribute('data-target', '#youngChildModal');
        button.setAttribute('onclick', 'prepareYoungChildModal()');
        buttonText.textContent = 'Add Young Child';
    }

    if (selectedChildTab === 'childs') {
        button.setAttribute('data-target', '#childModal');
        button.setAttribute('onclick', 'prepareChildModal()');
        buttonText.textContent = 'Add Child';
    }
}


function preparePregnantMotherModal() {
    const dutyArea = document.getElementById('pregnantMotherDutyArea');
    if (dutyArea) {
        dutyArea.value = selectedCareArea;
    }
}

function prepareLactatingMotherModal() {
    const dutyArea = document.getElementById('lactatingMotherDutyArea');
    if (dutyArea) {
        dutyArea.value = selectedCareArea;
    }
}

function preparePostnatalMotherModal() {
    const dutyArea = document.getElementById('postnatalMotherDutyArea');
    if (dutyArea) {
        dutyArea.value = selectedCareArea;
    }
}

function prepareNewbornModal() {
    const dutyArea = document.getElementById('newbornDutyArea');
    if (dutyArea) {
        dutyArea.value = selectedCareArea;
    }
}

function prepareYoungChildModal() {
    const dutyArea = document.getElementById('youngChildDutyArea');
    if (dutyArea) {
        dutyArea.value = selectedCareArea;
    }
}

function prepareChildModal() {
    const dutyArea = document.getElementById('childDutyArea');
    if (dutyArea) {
        dutyArea.value = selectedCareArea;
    }
}


function getSelectedCareArea() {
    if (typeof selectedCareArea !== 'undefined' && selectedCareArea) {
        return selectedCareArea;
    }

    if (typeof window.selectedCareArea !== 'undefined' && window.selectedCareArea) {
        return window.selectedCareArea;
    }

    return 'uduthuththiripitiya';
}

function preparePregnantMotherModal() {
    const dutyArea = document.getElementById('pregnantMotherDutyArea');
    if (dutyArea) {
        dutyArea.value = getSelectedCareArea();
    }
}

function prepareLactatingMotherModal() {
    const dutyArea = document.getElementById('lactatingMotherDutyArea');
    if (dutyArea) {
        dutyArea.value = getSelectedCareArea();
    }
}

function preparePostnatalMotherModal() {
    const dutyArea = document.getElementById('postnatalMotherDutyArea');
    if (dutyArea) {
        dutyArea.value = getSelectedCareArea();
    }
}

function prepareNewbornModal() {
    const dutyArea = document.getElementById('newbornDutyArea');
    if (dutyArea) {
        dutyArea.value = getSelectedCareArea();
    }
}

function prepareYoungChildModal() {
    const dutyArea = document.getElementById('youngChildDutyArea');
    if (dutyArea) {
        dutyArea.value = getSelectedCareArea();
    }
}

function prepareChildModal() {
    const dutyArea = document.getElementById('childDutyArea');
    if (dutyArea) {
        dutyArea.value = getSelectedCareArea();
    }
}


function updateMotherAddButton() {
    const button = document.getElementById('motherAddButton');
    const text = document.getElementById('motherAddButtonText');

    if (!button || !text) return;

    if (selectedMotherTab === 'pregnant') {
        button.setAttribute('data-target', '#pregnantMotherModal');
        button.setAttribute('onclick', 'preparePregnantMotherModal()');
        text.textContent = 'Add Pregnant Mother';
        return;
    }

    if (selectedMotherTab === 'lactating') {
        button.setAttribute('data-target', '#lactatingMotherModal');
        button.setAttribute('onclick', 'prepareLactatingMotherModal()');
        text.textContent = 'Add Lactating Mother';
        return;
    }

    if (selectedMotherTab === 'postnatal') {
        button.setAttribute('data-target', '#postnatalMotherModal');
        button.setAttribute('onclick', 'preparePostnatalMotherModal()');
        text.textContent = 'Add Postnatal Mother';
        return;
    }
}

function updateChildAddButton() {
    const button = document.getElementById('childAddButton');
    const text = document.getElementById('childAddButtonText');

    if (!button || !text) return;

    if (selectedChildTab === 'newborns') {
        button.setAttribute('data-target', '#newbornModal');
        button.setAttribute('onclick', 'prepareNewbornModal()');
        text.textContent = 'Add Newborn';
        return;
    }

    if (selectedChildTab === 'young') {
        button.setAttribute('data-target', '#youngChildModal');
        button.setAttribute('onclick', 'prepareYoungChildModal()');
        text.textContent = 'Add Young Child';
        return;
    }

    if (selectedChildTab === 'childs') {
        button.setAttribute('data-target', '#childModal');
        button.setAttribute('onclick', 'prepareChildModal()');
        text.textContent = 'Add Child';
        return;
    }
}