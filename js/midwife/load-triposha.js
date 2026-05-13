document.addEventListener('DOMContentLoaded', function () {
    loadTriposhaData();
});

let allTriposhaInventory = [];
let allTriposhaDistributions = [];
let selectedTriposhaArea = 'all';

const triposhaDutyAreas = [
    'uduthuththiripitiya',
    'kahabilihena',
    'opathella',
    'ambalangoda'
];

function loadTriposhaData() {
    fetch('../php/midwife/get_triposha_data.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            if (!data.success) {
                console.error(data.message || 'Failed to load Triposha data');
                allTriposhaInventory = [];
                allTriposhaDistributions = [];
                renderTriposha();
                return;
            }

            allTriposhaInventory = data.inventory || [];
            allTriposhaDistributions = data.distributions || [];

            renderTriposha();
        })
        .catch(function (error) {
            console.error('Triposha Load Error:', error);
            allTriposhaInventory = [];
            allTriposhaDistributions = [];
            renderTriposha();
        });
}

function renderTriposha() {
    renderTriposhaAreaButtons();
    renderTriposhaHeader();
    renderTriposhaStats();
    renderTriposhaDistributionRecords();
    renderTriposhaMonthlySummary();
}

function renderTriposhaAreaButtons() {
    const grid = document.getElementById('triposhaAreaGrid');
    if (!grid) return;

    let html = `
        <div class="duty-area-btn ${selectedTriposhaArea === 'all' ? 'active' : ''}" onclick="switchTriposhaArea('all')">
            <i class="fas fa-list"></i>
            <h5>All Areas</h5>
            <div class="area-count">${getCompletedDistributedPackets('all')} packets distributed</div>
        </div>
    `;

    html += triposhaDutyAreas.map(function (area) {
        return `
            <div class="duty-area-btn ${selectedTriposhaArea === area ? 'active' : ''}" onclick="switchTriposhaArea('${area}')">
                <i class="fas fa-map-marker-alt"></i>
                <h5>${formatTriposhaArea(area)}</h5>
                <div class="area-count">${getCompletedDistributedPackets(area)} packets distributed</div>
            </div>
        `;
    }).join('');

    grid.innerHTML = html;
}

function renderTriposhaHeader() {
    const title = document.getElementById('triposhaAreaTitle');
    const subtitle = document.getElementById('triposhaAreaSubtitle');

    if (!title || !subtitle) return;

    const areaName = selectedTriposhaArea === 'all'
        ? 'All Areas'
        : formatTriposhaArea(selectedTriposhaArea);

    title.innerHTML = `<i class="fas fa-map-marker-alt"></i> ${escapeHtmlTriposha(areaName)} - Triposha Distribution`;

    const recordsCount = getAreaTriposhaDistributions().length;
    subtitle.textContent = `${recordsCount} distribution record(s) found`;
}

function renderTriposhaStats() {
    const inventory = getAreaInventorySummary();

    const received = inventory.packets_received;
    const leftPrevious = inventory.packets_left_previous;
    const distributed = inventory.packets_distributed;
    const totalAvailable = received + leftPrevious;
    const remaining = Math.max(totalAvailable - distributed, 0);

    setTriposhaText('packets-received-month', received);
    setTriposhaText('packets-left-previous', leftPrevious);
    setTriposhaText('total-packets', totalAvailable);
    setTriposhaText('packets-distributed', distributed);
    setTriposhaText('remaining-packets', remaining + ' packets');
}

function renderTriposhaMonthlySummary() {
    const inventory = getAreaInventorySummary();

    setTriposhaText('pregnant-packets', inventory.pregnant_packets + ' packets');
    setTriposhaText('lactating-packets', inventory.lactating_packets + ' packets');
    setTriposhaText('children-packets', inventory.children_packets + ' packets');
    setTriposhaText('total-distributed-summary', inventory.packets_distributed + ' packets');

    const totalAvailable = inventory.packets_received + inventory.packets_left_previous;
    const remaining = Math.max(totalAvailable - inventory.packets_distributed, 0);

    setTriposhaText('remaining-packets', remaining + ' packets');
}

function renderTriposhaDistributionRecords() {
    const tbody = document.getElementById('distribution-records');
    if (!tbody) return;

    const records = getAreaTriposhaDistributions();

    if (records.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-muted">No Triposha distribution records found.</td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = records.map(function (record) {
        return `
            <tr data-id="${record.distribution_id}">
                <td>${escapeHtmlTriposha(record.distribution_date)}</td>
                <td>${escapeHtmlTriposha(record.beneficiary_name)}</td>
                <td>${escapeHtmlTriposha(record.address || '-')}</td>
                <td>${escapeHtmlTriposha(record.packets)}</td>
                <td>${formatTriposhaCategory(record.category)}</td>
                <td>
                    <span class="status-badge ${record.status === 'completed' ? 'status-active' : 'status-pending'}">
                        ${formatTriposhaStatus(record.status)}
                    </span>
                </td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="deleteTriposhaDistribution(${record.distribution_id})" title="Remove this distribution">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function switchTriposhaArea(area) {
    selectedTriposhaArea = area;
    renderTriposha();
}

function openTriposhaInventoryModal() {
    const month = new Date().toISOString().slice(0, 7);
    const inventory = getAreaInventorySummary();

    const areaValue = selectedTriposhaArea === 'all'
        ? triposhaDutyAreas[0]
        : selectedTriposhaArea;

    setInputTriposhaValue('triposhaInventoryDutyArea', areaValue);
    setInputTriposhaValue('triposhaInventoryMonth', month);
    setInputTriposhaValue('triposhaPacketsReceived', inventory.packets_received);
    setInputTriposhaValue('triposhaPacketsLeftPrevious', inventory.packets_left_previous);
    setInputTriposhaValue('triposhaInventoryNotes', inventory.notes || '');

    $('#triposhaInventoryModal').modal('show');
}

function openTriposhaDistributionModal() {
    const areaValue = selectedTriposhaArea === 'all'
        ? triposhaDutyAreas[0]
        : selectedTriposhaArea;

    setInputTriposhaValue('triposhaDistributionDutyArea', areaValue);
    setInputTriposhaValue('triposhaDistributionDate', new Date().toISOString().split('T')[0]);

    $('#triposhaDistributionModal').modal('show');
}

document.addEventListener('DOMContentLoaded', function () {
    const inventoryForm = document.getElementById('triposhaInventoryForm');

    if (inventoryForm) {
        inventoryForm.addEventListener('submit', function (e) {
            e.preventDefault();

            submitTriposhaForm(
                inventoryForm,
                'Saving...',
                function () {
                    $('#triposhaInventoryModal').modal('hide');
                    loadTriposhaData();
                }
            );
        });
    }

    const distributionForm = document.getElementById('triposhaDistributionForm');

    if (distributionForm) {
        distributionForm.addEventListener('submit', function (e) {
            e.preventDefault();

            submitTriposhaForm(
                distributionForm,
                'Saving...',
                function () {
                    $('#triposhaDistributionModal').modal('hide');
                    distributionForm.reset();
                    loadTriposhaData();
                }
            );
        });
    }
});

function submitTriposhaForm(form, savingText, successCallback) {
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.innerHTML : '';

    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + savingText;
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
            console.error('Triposha Form Error:', error);
            alert('Server error while saving Triposha data.');

            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
}

function deleteTriposhaDistribution(distributionId) {
    if (!confirm('Remove this Triposha distribution record?')) {
        return;
    }

    const formData = new FormData();
    formData.append('distribution_id', distributionId);

    fetch('../php/midwife/delete_triposha_distribution.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            alert(data.message);

            if (data.success) {
                loadTriposhaData();
            }
        })
        .catch(function (error) {
            console.error('Delete Triposha Distribution Error:', error);
            alert('Server error while removing Triposha distribution.');
        });
}

function getCompletedDistributedPackets(area) {
    return allTriposhaDistributions
        .filter(function (record) {
            const areaMatches = area === 'all' || normalizeTriposhaArea(record.duty_area) === normalizeTriposhaArea(area);
            return areaMatches && record.status === 'completed';
        })
        .reduce(function (sum, record) {
            return sum + Number(record.packets || 0);
        }, 0);
}

function getAreaTriposhaDistributions() {
    if (selectedTriposhaArea === 'all') {
        return allTriposhaDistributions;
    }

    return allTriposhaDistributions.filter(function (record) {
        return normalizeTriposhaArea(record.duty_area) === normalizeTriposhaArea(selectedTriposhaArea);
    });
}

function getAreaInventorySummary() {
    const records = selectedTriposhaArea === 'all'
        ? allTriposhaInventory
        : allTriposhaInventory.filter(function (record) {
            return normalizeTriposhaArea(record.duty_area) === normalizeTriposhaArea(selectedTriposhaArea);
        });

    return records.reduce(function (summary, record) {
        summary.packets_received += Number(record.packets_received || 0);
        summary.packets_left_previous += Number(record.packets_left_previous || 0);
        summary.packets_distributed += Number(record.packets_distributed || 0);
        summary.pregnant_packets += Number(record.pregnant_packets || 0);
        summary.lactating_packets += Number(record.lactating_packets || 0);
        summary.children_packets += Number(record.children_packets || 0);
        summary.notes = record.notes || summary.notes;

        return summary;
    }, {
        packets_received: 0,
        packets_left_previous: 0,
        packets_distributed: 0,
        pregnant_packets: 0,
        lactating_packets: 0,
        children_packets: 0,
        notes: ''
    });
}

function normalizeTriposhaArea(value) {
    return String(value || '').trim().toLowerCase();
}

function formatTriposhaArea(value) {
    const map = {
        uduthuththiripitiya: 'Uduthuththiripitiya',
        kahabilihena: 'Kahabilihena',
        opathella: 'Opathella',
        ambalangoda: 'Ambalangoda'
    };

    const normalized = normalizeTriposhaArea(value);
    return map[normalized] || String(value || '-');
}

function formatTriposhaCategory(value) {
    const map = {
        pregnant: 'Pregnant Mother',
        lactating: 'Lactating Mother',
        children: 'Child (6-23 months)'
    };

    return map[value] || value || '-';
}

function formatTriposhaStatus(value) {
    return String(value || '')
        .replaceAll('_', ' ')
        .replaceAll('-', ' ')
        .replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });
}

function setTriposhaText(id, value) {
    const element = document.getElementById(id);

    if (element) {
        element.textContent = value;
    }
}

function setInputTriposhaValue(id, value) {
    const element = document.getElementById(id);

    if (element) {
        element.value = value;
    }
}

function escapeHtmlTriposha(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}