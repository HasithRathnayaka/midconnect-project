/* maternal-child-care-tab-fix.js */
/* Paste this AFTER your main maternal-child-care.js file */

let selectedCareArea = window.selectedCareArea || 'uduthuththiripitiya';
let selectedCareMainTab = window.selectedCareMainTab || 'mothers';
let selectedMotherTab = window.selectedMotherTab || 'pregnant';
let selectedChildTab = window.selectedChildTab || 'newborns';

function switchCareTab(tab, eventObj) {
    if (eventObj) eventObj.preventDefault();

    selectedCareMainTab = tab;
    window.selectedCareMainTab = tab;

    const mothersTab = document.getElementById('mothers-tab');
    const childrenTab = document.getElementById('children-tab');

    if (mothersTab) {
        mothersTab.style.display = tab === 'mothers' ? 'block' : 'none';
        mothersTab.classList.toggle('hidden', tab !== 'mothers');
    }

    if (childrenTab) {
        childrenTab.style.display = tab === 'children' ? 'block' : 'none';
        childrenTab.classList.toggle('hidden', tab !== 'children');
    }

    document.querySelectorAll('#patients [data-care-main-tab]').forEach(function (link) {
        link.classList.remove('active');
    });

    const activeLink = document.querySelector(`#patients [data-care-main-tab="${tab}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }

    if (tab === 'mothers') {
        renderMotherTable();
    } else {
        renderChildTable();
    }
}

function switchMotherTab(tab, eventObj) {
    if (eventObj) eventObj.preventDefault();

    selectedMotherTab = tab;
    window.selectedMotherTab = tab;

    document.querySelectorAll('#patients [data-mother-tab]').forEach(function (link) {
        link.classList.remove('active');
    });

    const activeLink = document.querySelector(`#patients [data-mother-tab="${tab}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }

    renderMotherTable();
}

function switchChildrenTab(tab, eventObj) {
    if (eventObj) eventObj.preventDefault();

    selectedChildTab = tab;
    window.selectedChildTab = tab;

    document.querySelectorAll('#patients [data-child-tab]').forEach(function (link) {
        link.classList.remove('active');
    });

    const activeLink = document.querySelector(`#patients [data-child-tab="${tab}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }

    renderChildTable();
}

function switchMaternalChildArea(area) {
    selectedCareArea = area;
    window.selectedCareArea = area;

    document.querySelectorAll('#careAreaGrid .duty-area-btn').forEach(function (btn) {
        btn.classList.remove('active');
    });

    if (typeof event !== 'undefined' && event.currentTarget) {
        event.currentTarget.classList.add('active');
    }

    updateCareHeader();

    if (selectedCareMainTab === 'mothers') {
        renderMotherTable();
    } else {
        renderChildTable();
    }
}

function updateCareHeader() {
    const title = document.getElementById('careAreaTitle');
    const subtitle = document.getElementById('careAreaSubtitle');

    const areaLabel = formatCareAreaName(selectedCareArea);

    if (title) {
        title.innerHTML = `<i class="fas fa-map-marker-alt"></i> ${escapeCareHtml(areaLabel)} Area - Maternal & Child Care`;
    }

    if (!subtitle) return;

    const mothers = getFilteredMotherRecords();
    const children = getFilteredChildRecords();

    const pregnant = mothers.filter(r => r.category === 'pregnant').length;
    const lactating = mothers.filter(r => r.category === 'lactating').length;
    const postnatal = mothers.filter(r => r.category === 'postnatal').length;

    subtitle.textContent =
        `Pregnant: ${pregnant}, Lactating: ${lactating}, Postnatal: ${postnatal}, Children: ${children.length}`;
}

function renderMotherTable() {
    const title = document.getElementById('motherTableTitle');
    const addText = document.getElementById('motherAddButtonText');
    const head = document.getElementById('motherTableHead');
    const body = document.getElementById('motherTableBody');

    if (!head || !body) return;

    const config = {
        pregnant: {
            title: 'Pregnant Mothers',
            addText: 'Add Pregnant Mother',
            headers: `
                <tr>
                    <th>Mother's Name</th>
                    <th>Age</th>
                    <th>Weeks Pregnant</th>
                    <th>Last Visit</th>
                    <th>Next Appointment</th>
                    <th>Risk Level</th>
                    <th>Actions</th>
                </tr>
            `
        },
        lactating: {
            title: 'Lactating Mothers',
            addText: 'Add Lactating Mother',
            headers: `
                <tr>
                    <th>Mother's Name</th>
                    <th>Age</th>
                    <th>Baby's Age</th>
                    <th>Breastfeeding Status</th>
                    <th>Last Visit</th>
                    <th>Support Level</th>
                    <th>Actions</th>
                </tr>
            `
        },
        postnatal: {
            title: 'Postnatal Mothers',
            addText: 'Add Postnatal Mother',
            headers: `
                <tr>
                    <th>Mother's Name</th>
                    <th>Age</th>
                    <th>Delivery Date</th>
                    <th>Delivery Type</th>
                    <th>Recovery Status</th>
                    <th>Last Visit</th>
                    <th>Actions</th>
                </tr>
            `
        }
    };

    const current = config[selectedMotherTab] || config.pregnant;

    if (title) title.textContent = current.title;
    if (addText) addText.textContent = current.addText;

    head.innerHTML = current.headers;

    const records = getFilteredMotherRecords().filter(function (record) {
        return record.category === selectedMotherTab;
    });

    if (records.length === 0) {
        body.innerHTML = `
            <tr>
                <td colspan="7" class="text-muted">No ${current.title.toLowerCase()} found.</td>
            </tr>
        `;
        return;
    }

    body.innerHTML = records.map(function (record) {
        if (selectedMotherTab === 'pregnant') {
            return `
                <tr>
                    <td>${escapeCareHtml(record.mother_name)}</td>
                    <td>${escapeCareHtml(record.age || '-')}</td>
                    <td>${escapeCareHtml(record.weeks_pregnant || '-')} weeks</td>
                    <td>${formatCareDate(record.last_visit)}</td>
                    <td>${formatCareDate(record.next_appointment)}</td>
                    <td>${renderCareBadge(record.risk_level || 'Low Risk')}</td>
                    <td>
                        <button class="btn btn-info btn-sm" onclick="viewMotherRecord(${record.mother_id})">View</button>
                        <button class="btn btn-warning btn-sm" onclick="editMotherRecord(${record.mother_id})">Update</button>
                    </td>
                </tr>
            `;
        }

        if (selectedMotherTab === 'lactating') {
            return `
                <tr>
                    <td>${escapeCareHtml(record.mother_name)}</td>
                    <td>${escapeCareHtml(record.age || '-')}</td>
                    <td>${escapeCareHtml(record.baby_age || '-')}</td>
                    <td>${escapeCareHtml(record.breastfeeding_status || '-')}</td>
                    <td>${formatCareDate(record.last_visit)}</td>
                    <td>${renderCareBadge(record.support_level || record.health_status || '-')}</td>
                    <td>
                        <button class="btn btn-info btn-sm" onclick="viewMotherRecord(${record.mother_id})">View</button>
                        <button class="btn btn-warning btn-sm" onclick="editMotherRecord(${record.mother_id})">Update</button>
                    </td>
                </tr>
            `;
        }

        return `
            <tr>
                <td>${escapeCareHtml(record.mother_name)}</td>
                <td>${escapeCareHtml(record.age || '-')}</td>
                <td>${formatCareDate(record.delivery_date)}</td>
                <td>${escapeCareHtml(record.delivery_type || '-')}</td>
                <td>${renderCareBadge(record.recovery_status || '-')}</td>
                <td>${formatCareDate(record.last_visit)}</td>
                <td>
                    <button class="btn btn-info btn-sm" onclick="viewMotherRecord(${record.mother_id})">View</button>
                    <button class="btn btn-warning btn-sm" onclick="editMotherRecord(${record.mother_id})">Update</button>
                </td>
            </tr>
        `;
    }).join('');
}

function renderChildTable() {
    const title = document.getElementById('childTableTitle');
    const addText = document.getElementById('childAddButtonText');
    const head = document.getElementById('childTableHead');
    const body = document.getElementById('childTableBody');

    if (!head || !body) return;

    const config = {
        newborns: {
            title: 'Newborns',
            addText: 'Add Newborn',
            headers: `
                <tr>
                    <th>Baby's Name</th>
                    <th>Mother's Name</th>
                    <th>Date of Birth</th>
                    <th>Birth Weight</th>
                    <th>Last Check-up</th>
                    <th>Health Status</th>
                    <th>Actions</th>
                </tr>
            `
        },
        young: {
            title: 'Young Children',
            addText: 'Add Young Child',
            headers: `
                <tr>
                    <th>Child's Name</th>
                    <th>Age</th>
                    <th>Mother's Name</th>
                    <th>Weight</th>
                    <th>Height</th>
                    <th>Development Status</th>
                    <th>Actions</th>
                </tr>
            `
        },
        childs: {
            title: 'Childs',
            addText: 'Add Child',
            headers: `
                <tr>
                    <th>Child's Name</th>
                    <th>Age</th>
                    <th>Mother's Name</th>
                    <th>School</th>
                    <th>Last Visit</th>
                    <th>Health Status</th>
                    <th>Actions</th>
                </tr>
            `
        }
    };

    const current = config[selectedChildTab] || config.newborns;

    if (title) title.textContent = current.title;
    if (addText) addText.textContent = current.addText;

    head.innerHTML = current.headers;

    const records = getFilteredChildRecords().filter(function (record) {
        return record.child_category === selectedChildTab;
    });

    if (records.length === 0) {
        body.innerHTML = `
            <tr>
                <td colspan="7" class="text-muted">No ${current.title.toLowerCase()} found.</td>
            </tr>
        `;
        return;
    }

    body.innerHTML = records.map(function (record) {
        if (selectedChildTab === 'newborns') {
            return `
                <tr>
                    <td>${escapeCareHtml(record.child_name)}</td>
                    <td>${escapeCareHtml(record.mother_name || '-')}</td>
                    <td>${formatCareDate(record.date_of_birth)}</td>
                    <td>${record.birth_weight ? escapeCareHtml(record.birth_weight) + ' kg' : '-'}</td>
                    <td>${formatCareDate(record.last_checkup)}</td>
                    <td>${renderCareBadge(record.health_status || 'Healthy')}</td>
                    <td>
                        <button class="btn btn-info btn-sm" onclick="viewChildRecord(${record.child_id})">View</button>
                        <button class="btn btn-warning btn-sm" onclick="editChildRecord(${record.child_id})">Update</button>
                    </td>
                </tr>
            `;
        }

        if (selectedChildTab === 'young') {
            return `
                <tr>
                    <td>${escapeCareHtml(record.child_name)}</td>
                    <td>${escapeCareHtml(record.age_label || '-')}</td>
                    <td>${escapeCareHtml(record.mother_name || '-')}</td>
                    <td>${record.current_weight ? escapeCareHtml(record.current_weight) + ' kg' : '-'}</td>
                    <td>${record.height_cm ? escapeCareHtml(record.height_cm) + ' cm' : '-'}</td>
                    <td>${renderCareBadge(record.development_status || record.health_status || '-')}</td>
                    <td>
                        <button class="btn btn-info btn-sm" onclick="viewChildRecord(${record.child_id})">View</button>
                        <button class="btn btn-warning btn-sm" onclick="editChildRecord(${record.child_id})">Update</button>
                    </td>
                </tr>
            `;
        }

        return `
            <tr>
                <td>${escapeCareHtml(record.child_name)}</td>
                <td>${escapeCareHtml(record.age_label || '-')}</td>
                <td>${escapeCareHtml(record.mother_name || '-')}</td>
                <td>${escapeCareHtml(record.school || '-')}</td>
                <td>${formatCareDate(record.last_checkup)}</td>
                <td>${renderCareBadge(record.health_status || 'Healthy')}</td>
                <td>
                    <button class="btn btn-info btn-sm" onclick="viewChildRecord(${record.child_id})">View</button>
                    <button class="btn btn-warning btn-sm" onclick="editChildRecord(${record.child_id})">Update</button>
                </td>
            </tr>
        `;
    }).join('');
}

function getFilteredMotherRecords() {
    const records = window.allMaternalCareRecords || window.allMothers || [];

    const searchInput = document.getElementById('careSearchInput');
    const search = searchInput ? searchInput.value.trim().toLowerCase() : '';

    return records.filter(function (record) {
        const areaMatch = selectedCareArea === 'all' ||
            normalizeCareValue(record.duty_area) === normalizeCareValue(selectedCareArea);

        const searchMatch = search === '' ||
            String(record.mother_name || '').toLowerCase().includes(search) ||
            String(record.contact_number || '').toLowerCase().includes(search) ||
            String(record.address || '').toLowerCase().includes(search);

        return areaMatch && searchMatch;
    });
}

function getFilteredChildRecords() {
    const records = window.allChildCareRecords || window.allChildren || [];

    const searchInput = document.getElementById('careSearchInput');
    const search = searchInput ? searchInput.value.trim().toLowerCase() : '';

    return records.filter(function (record) {
        const areaMatch = selectedCareArea === 'all' ||
            normalizeCareValue(record.duty_area) === normalizeCareValue(selectedCareArea);

        const searchMatch = search === '' ||
            String(record.child_name || '').toLowerCase().includes(search) ||
            String(record.mother_name || '').toLowerCase().includes(search) ||
            String(record.school || '').toLowerCase().includes(search);

        return areaMatch && searchMatch;
    });
}

function renderCareBadge(value) {
    const text = String(value || '-');
    const normalized = text.toLowerCase();

    let badgeClass = 'status-success';

    if (
        normalized.includes('high') ||
        normalized.includes('danger') ||
        normalized.includes('slow') ||
        normalized.includes('delayed') ||
        normalized.includes('issue')
    ) {
        badgeClass = 'status-danger';
    } else if (
        normalized.includes('need') ||
        normalized.includes('monitor') ||
        normalized.includes('warning')
    ) {
        badgeClass = 'status-warning';
    }

    return `<span class="status-badge ${badgeClass}">${escapeCareHtml(text)}</span>`;
}

function formatCareDate(value) {
    if (!value) return '-';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return escapeCareHtml(value);
    }

    return date.toISOString().split('T')[0];
}

function formatCareAreaName(value) {
    const map = {
        uduthuththiripitiya: 'Uduthuththiripitiya',
        kahabilihena: 'Kahabilihena',
        opathella: 'Opathella',
        ambalangoda: 'Ambalangoda'
    };

    return map[normalizeCareValue(value)] || value || '-';
}

function normalizeCareValue(value) {
    return String(value || '').trim().toLowerCase();
}

function escapeCareHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('careSearchInput');

    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            if (selectedCareMainTab === 'mothers') {
                renderMotherTable();
            } else {
                renderChildTable();
            }
        });
    }

    switchCareTab('mothers');
    switchMotherTab('pregnant');
});