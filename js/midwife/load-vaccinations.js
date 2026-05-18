document.addEventListener('DOMContentLoaded', function () {
    loadVaccinations();
});

let allVaccinations = [];
let allVaccineInventory = [];
let selectedVaccinationArea = 'all';
let selectedVaccinationCategory = 'all';

const fixedVaccinationAreas = [
    'uduthuththiripitiya',
    'kahabilihena',
    'opathella',
    'ambalangoda'
];

function loadVaccinations() {
    fetch('../php/midwife/get_vaccinations.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            console.log('Vaccination response:', data);

            if (!data.success) {
                console.error(data.message || 'Failed to load vaccinations');
                allVaccinations = [];
                allVaccineInventory = [];
                renderVaccinations();
                return;
            }

            allVaccinations = data.vaccinations || [];
            allVaccineInventory = data.inventory || [];

            renderVaccinations();
        })
        .catch(function (error) {
            console.error('Vaccination Load Error:', error);
            allVaccinations = [];
            allVaccineInventory = [];
            renderVaccinations();
        });
}

function renderVaccinations() {
    renderVaccinationAreaButtons();
    renderVaccinationHeader();
    renderVaccinationStats();
    renderScheduledVaccinations();
    renderVaccineInventory();
    renderVaccinationPatientRecords();
    renderOverdueVaccinations();
}

function renderVaccinationAreaButtons() {
    const grid = document.getElementById('vaccinationAreaGrid');

    if (!grid) return;

    let html = `
        <div class="duty-area-btn ${selectedVaccinationArea === 'all' ? 'active' : ''}" onclick="switchVaccinationArea('all')">
            <i class="fas fa-list"></i>
            <h5>All Areas</h5>
            <div class="area-count">${allVaccinations.length} record(s)</div>
        </div>
    `;

    html += fixedVaccinationAreas.map(function (area) {
        const count = allVaccinations.filter(function (record) {
            return normalizeText(record.duty_area) === normalizeText(area);
        }).length;

        return `
            <div class="duty-area-btn ${normalizeText(selectedVaccinationArea) === normalizeText(area) ? 'active' : ''}" onclick="switchVaccinationArea('${area}')">
                <i class="fas fa-map-marker-alt"></i>
                <h5>${formatAreaName(area)}</h5>
                <div class="area-count">${count} record(s)</div>
            </div>
        `;
    }).join('');

    const extraAreas = getExtraVaccinationAreas();

    html += extraAreas.map(function (area) {
        const count = allVaccinations.filter(function (record) {
            return normalizeText(record.duty_area) === normalizeText(area);
        }).length;

        return `
            <div class="duty-area-btn ${normalizeText(selectedVaccinationArea) === normalizeText(area) ? 'active' : ''}" onclick="switchVaccinationArea('${escapeJs(area)}')">
                <i class="fas fa-map-marker-alt"></i>
                <h5>${escapeHtml(formatAreaName(area))}</h5>
                <div class="area-count">${count} record(s)</div>
            </div>
        `;
    }).join('');

    grid.innerHTML = html;
}

function renderVaccinationHeader() {
    const title = document.getElementById('vaccinationAreaTitle');
    const subtitle = document.getElementById('vaccinationAreaSubtitle');

    if (!title || !subtitle) return;

    const areaName = selectedVaccinationArea === 'all'
        ? 'All Areas'
        : formatAreaName(selectedVaccinationArea);

    const records = getAreaFilteredVaccinations();

    const pediatricCount = records.filter(function (record) {
        return record.category === 'pediatric';
    }).length;

    const maternalCount = records.filter(function (record) {
        return record.category === 'maternal';
    }).length;

    title.innerHTML = `<i class="fas fa-map-marker-alt"></i> ${escapeHtml(areaName)} - Vaccinations`;
    subtitle.textContent = `Records: ${records.length} | Pediatric: ${pediatricCount}, Maternal: ${maternalCount}`;
}

function renderVaccinationStats() {
    const records = getAreaFilteredVaccinations();
    const today = getTodayDate();

    const todayCount = records.filter(function (record) {
        return record.vaccination_date === today;
    }).length;

    const completedCount = records.filter(function (record) {
        return record.status === 'completed';
    }).length;

    const overdueCount = records.filter(function (record) {
        return isVaccinationOverdue(record);
    }).length;

    const stockCount = allVaccineInventory.reduce(function (total, item) {
        return total + Number(item.stock_quantity || 0);
    }, 0);

    setText('vaccinationTodayCount', todayCount);
    setText('vaccinationCompletedCount', completedCount);
    setText('vaccinationOverdueCount', overdueCount);
    setText('vaccinationStockCount', stockCount);
}

function renderScheduledVaccinations() {
    const container = document.getElementById('scheduledVaccinationList');

    if (!container) return;

    const records = getFilteredVaccinations().filter(function (record) {
        return record.status !== 'completed' && !isVaccinationOverdue(record);
    });

    if (records.length === 0) {
        container.innerHTML = `<p class="text-muted">No scheduled vaccinations found.</p>`;
        return;
    }

    container.innerHTML = records.map(function (record) {
        const priorityClass = record.category === 'pediatric' ? 'high-priority' : 'normal-priority';

        return `
            <div class="vaccination-item ${priorityClass}">
                <div class="vaccine-time">
                    <span class="time">${formatTime(record.vaccination_time)}</span>
                    <span class="duration">15 min</span>
                </div>

                <div class="vaccine-details">
                    <h5>${escapeHtml(record.patient_name)} ${record.patient_age ? '(' + escapeHtml(record.patient_age) + ' years)' : ''}</h5>

                    <div class="patient-info">
                        <span class="contact">
                            <i class="fas fa-phone"></i> ${escapeHtml(record.contact_number || '-')}
                        </span>

                        <span class="contact">
                            <i class="fas fa-map-marker-alt"></i> ${escapeHtml(formatAreaName(record.duty_area))}
                        </span>
                    </div>

                    <div class="vaccine-info">
                        <span class="vaccine-badge ${escapeHtml(record.category || 'adult')}">
                            ${escapeHtml(record.vaccine_name)}
                        </span>

                        ${record.dose_number ? `<span class="vaccine-badge ${escapeHtml(record.category || 'adult')}">${escapeHtml(record.dose_number)}</span>` : ''}
                    </div>

                    <p class="notes">${escapeHtml(record.notes || record.location || '')}</p>
                </div>

                <div class="vaccine-actions">
                    <button class="btn btn-success btn-sm" onclick="administerVaccine(${record.vaccination_id})">
                        <i class="fas fa-syringe"></i> Administer
                    </button>

                    <button class="btn btn-info btn-sm" onclick="viewVaccineHistory(${record.vaccination_id})">
                        <i class="fas fa-history"></i> History
                    </button>

                    <button class="btn btn-warning btn-sm" onclick="rescheduleVaccine(${record.vaccination_id})">
                        <i class="fas fa-calendar-alt"></i> Reschedule
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

function renderVaccineInventory() {
    const container = document.getElementById('vaccineInventoryGrid');

    if (!container) return;

    if (allVaccineInventory.length === 0) {
        container.innerHTML = `<p class="text-muted">No vaccine inventory found.</p>`;
        return;
    }

    container.innerHTML = allVaccineInventory.map(function (item) {
        const stockClass = getStockClass(item);
        const stockLabel = getStockLabel(item);

        return `
            <div class="inventory-item ${stockClass}">
                <div class="vaccine-icon">
                    <i class="fas fa-vial"></i>
                </div>

                <div class="vaccine-name">${escapeHtml(item.vaccine_name)}</div>

                <div class="stock-info">
                    <span class="stock-level">${escapeHtml(item.stock_quantity)} doses</span>
                    <span class="expiry-date">Exp: ${formatDate(item.expiry_date)}</span>
                </div>

                <div class="stock-status ${stockClass.replace('-stock', '')}">
                    ${stockLabel}
                </div>
            </div>
        `;
    }).join('');
}

function renderVaccinationPatientRecords() {
    const tbody = document.getElementById('vaccinationPatientRecordBody');

    if (!tbody) return;

    const searchInput = document.getElementById('vaccinationPatientSearch');
    const search = normalizeText(searchInput ? searchInput.value : '');

    let records = getAreaFilteredVaccinations();

    if (search !== '') {
        records = records.filter(function (record) {
            return normalizeText(record.patient_name).includes(search);
        });
    }

    if (records.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6">No patient vaccination records found.</td></tr>`;
        return;
    }

    const grouped = {};

    records.forEach(function (record) {
        const key = normalizeText(record.patient_name);

        if (!grouped[key]) {
            grouped[key] = {
                patient_name: record.patient_name,
                patient_age: record.patient_age,
                records: []
            };
        }

        grouped[key].records.push(record);
    });

    tbody.innerHTML = Object.values(grouped).map(function (patient) {
        const sorted = patient.records.sort(function (a, b) {
            return new Date(b.vaccination_date) - new Date(a.vaccination_date);
        });

        const lastRecord = sorted[0];

        return `
            <tr>
                <td>${escapeHtml(patient.patient_name)}</td>
                <td>${patient.patient_age ? escapeHtml(patient.patient_age) + ' years' : '-'}</td>
                <td>${escapeHtml(lastRecord.vaccine_name)} ${lastRecord.dose_number ? '(' + escapeHtml(lastRecord.dose_number) + ')' : ''}</td>
                <td>${lastRecord.next_due_date ? formatDate(lastRecord.next_due_date) : '-'}</td>
                <td>
                    <span class="status-badge ${lastRecord.status === 'completed' ? 'status-success' : 'status-active'}">
                        ${escapeHtml(formatText(lastRecord.status))}
                    </span>
                </td>
                <td>
                    <button class="btn btn-info btn-sm" onclick="viewVaccineHistory(${lastRecord.vaccination_id})">
                        View Card
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function renderOverdueVaccinations() {
    const container = document.getElementById('overdueVaccinationList');

    if (!container) return;

    const overdueRecords = getAreaFilteredVaccinations().filter(function (record) {
        return isVaccinationOverdue(record);
    });

    if (overdueRecords.length === 0) {
        container.innerHTML = `<p class="text-muted">No overdue vaccinations found.</p>`;
        return;
    }

    container.innerHTML = overdueRecords.map(function (record) {
        const days = getDaysOverdue(record);

        return `
            <div class="overdue-item urgent">
                <div class="overdue-info">
                    <h5>${escapeHtml(record.patient_name)}</h5>

                    <p class="vaccine-details">
                        ${escapeHtml(record.vaccine_name)} ${record.dose_number ? '- ' + escapeHtml(record.dose_number) : ''}
                    </p>

                    <p class="overdue-duration">
                        <i class="fas fa-clock"></i>
                        <span class="overdue-text">${days} day(s) overdue</span>
                    </p>
                </div>

                <div class="contact-info">
                    <p><i class="fas fa-map-marker-alt"></i> ${escapeHtml(formatAreaName(record.duty_area))}</p>
                    <p><i class="fas fa-phone"></i> ${escapeHtml(record.contact_number || '-')}</p>
                </div>

                <div class="overdue-actions">
                    <button class="btn btn-danger btn-sm" onclick="contactPatient('${escapeJs(record.contact_number || '')}')">
                        <i class="fas fa-phone"></i> Call Now
                    </button>

                    <button class="btn btn-primary btn-sm" onclick="rescheduleVaccine(${record.vaccination_id})">
                        <i class="fas fa-calendar-plus"></i> Reschedule
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

function switchVaccinationArea(area) {
    selectedVaccinationArea = area;
    renderVaccinations();
}

function switchVaccinationTab(tabName, event) {
    if (event) {
        event.preventDefault();
    }

    const tabs = [
        'scheduled',
        'inventory',
        'records',
        'overdue'
    ];

    tabs.forEach(function (tab) {
        const section = document.getElementById(tab + '-vaccinations');

        if (section) {
            section.style.display = tab === tabName ? 'block' : 'none';
        }
    });

    const tabLinks = document.querySelectorAll('#vaccinations .nav-tabs .nav-link');

    tabLinks.forEach(function (link) {
        link.classList.remove('active');
    });

    if (event && event.target) {
        event.target.classList.add('active');
    }
}

function filterVaccinations(category) {
    selectedVaccinationCategory = category;
    renderScheduledVaccinations();
}

function administerVaccine(vaccinationId) {
    if (!confirm('Mark this vaccination as administered?')) {
        return;
    }

    const formData = new FormData();
    formData.append('vaccination_id', vaccinationId);

    fetch('../php/midwife/administer_vaccination.php', {
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
                loadVaccinations();
            }
        })
        .catch(function (error) {
            console.error('Administer Vaccine Error:', error);
            alert('Server error while administering vaccine.');
        });
}

function viewVaccineHistory(vaccinationId) {
    const record = allVaccinations.find(function (item) {
        return Number(item.vaccination_id) === Number(vaccinationId);
    });

    if (!record) {
        alert('Vaccination record not found.');
        return;
    }

    alert(
        'Patient: ' + record.patient_name + '\n' +
        'Vaccine: ' + record.vaccine_name + '\n' +
        'Date: ' + record.vaccination_date + '\n' +
        'Time: ' + formatTime(record.vaccination_time) + '\n' +
        'Status: ' + record.status
    );
}

function rescheduleVaccine(vaccinationId) {
    alert('Reschedule function can be connected next. Vaccination ID: ' + vaccinationId);
}

function quickVaccinationLog() {
    alert('Quick vaccination log can be connected next.');
}



function checkExpiring() {
    const today = new Date();
    const nextThirtyDays = new Date();
    nextThirtyDays.setDate(today.getDate() + 30);

    const expiring = allVaccineInventory.filter(function (item) {
        if (!item.expiry_date) return false;

        const expiryDate = new Date(item.expiry_date);
        return expiryDate >= today && expiryDate <= nextThirtyDays;
    });

    if (expiring.length === 0) {
        alert('No vaccines expiring within the next 30 days.');
        return;
    }

    alert(
        'Expiring vaccines:\n' +
        expiring.map(function (item) {
            return item.vaccine_name + ' - Exp: ' + item.expiry_date;
        }).join('\n')
    );
}



function printSchedule() {
    window.print();
}

function contactPatient(phoneNumber) {
    if (!phoneNumber) {
        alert('Contact number not available.');
        return;
    }

    alert('Call patient: ' + phoneNumber);
}

function getFilteredVaccinations() {
    let records = getAreaFilteredVaccinations();

    if (selectedVaccinationCategory !== 'all') {
        records = records.filter(function (record) {
            return record.category === selectedVaccinationCategory;
        });
    }

    return records;
}

function getAreaFilteredVaccinations() {
    if (selectedVaccinationArea === 'all') {
        return allVaccinations;
    }

    return allVaccinations.filter(function (record) {
        return normalizeText(record.duty_area) === normalizeText(selectedVaccinationArea);
    });
}

function getExtraVaccinationAreas() {
    const fixed = fixedVaccinationAreas.map(function (area) {
        return normalizeText(area);
    });

    const dbAreas = allVaccinations
        .map(function (record) {
            return record.duty_area;
        })
        .filter(Boolean);

    const unique = [...new Set(dbAreas)];

    return unique.filter(function (area) {
        return !fixed.includes(normalizeText(area));
    });
}

function isVaccinationOverdue(record) {
    if (record.status === 'completed') return false;

    const checkDate = record.next_due_date || record.vaccination_date;

    if (!checkDate) return false;

    const today = new Date(getTodayDate());
    const date = new Date(checkDate);

    return date < today;
}

function getDaysOverdue(record) {
    const checkDate = record.next_due_date || record.vaccination_date;
    const today = new Date(getTodayDate());
    const date = new Date(checkDate);

    const diff = today - date;

    return Math.floor(diff / (1000 * 60 * 60 * 24));
}

function getStockClass(item) {
    const stock = Number(item.stock_quantity || 0);
    const min = Number(item.minimum_stock_level || 10);

    if (stock <= 3) return 'critical-stock';
    if (stock <= min) return 'low-stock';

    return 'good-stock';
}

function getStockLabel(item) {
    const stockClass = getStockClass(item);

    if (stockClass === 'critical-stock') return 'CRITICAL';
    if (stockClass === 'low-stock') return 'LOW STOCK';

    return 'GOOD STOCK';
}

function formatAreaName(value) {
    const areaMap = {
        'uduthuththiripitiya': 'Uduthuththiripitiya',
        'kahabilihena': 'Kahabilihena',
        'opathella': 'Opathella',
        'ambalangoda': 'Ambalangoda'
    };

    const normalized = normalizeText(value);
    return areaMap[normalized] || formatText(value);
}

function formatText(value) {
    return String(value || '-')
        .replaceAll('-', ' ')
        .replaceAll('_', ' ')
        .replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });
}

function formatDate(dateString) {
    if (!dateString) return '-';

    const date = new Date(dateString);

    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatTime(timeString) {
    if (!timeString) return '-';
    return String(timeString).substring(0, 5);
}

function getTodayDate() {
    return new Date().toISOString().split('T')[0];
}

function setText(id, value) {
    const element = document.getElementById(id);

    if (element) {
        element.textContent = value;
    }
}

function normalizeText(value) {
    return String(value || '').trim().toLowerCase();
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function escapeJs(value) {
    return String(value ?? '').replaceAll("'", "\\'");
}



function updateInventory() {
    populateInventoryUpdateModal();

    if (typeof $ !== 'undefined') {
        $('#updateVaccineInventoryModal').modal('show');
    } else {
        alert('Bootstrap modal is not available. Please check jQuery and Bootstrap JS.');
    }
}

function populateInventoryUpdateModal() {
    const vaccineSelect = document.getElementById('inventoryVaccineSelect');

    if (!vaccineSelect) {
        console.error('inventoryVaccineSelect not found');
        return;
    }

    if (!allVaccineInventory || allVaccineInventory.length === 0) {
        vaccineSelect.innerHTML = '<option value="">No vaccines found</option>';
        return;
    }

    vaccineSelect.innerHTML = '<option value="">Select Vaccine</option>';

    allVaccineInventory.forEach(function (vaccine) {
        const option = document.createElement('option');

        option.value = vaccine.vaccine_id;
        option.textContent = vaccine.vaccine_name + ' (' + vaccine.vaccine_code + ')';

        vaccineSelect.appendChild(option);
    });

    clearInventoryUpdateFields();
}

function clearInventoryUpdateFields() {
    setInputValue('inventoryStockQuantity', '');
    setInputValue('inventoryMinimumStock', '');
    setInputValue('inventoryBatchNumber', '');
    setInputValue('inventoryExpiryDate', '');
    setInputValue('inventoryStatus', 'available');
}

function fillInventoryUpdateFields(vaccineId) {
    const vaccine = allVaccineInventory.find(function (item) {
        return Number(item.vaccine_id) === Number(vaccineId);
    });

    if (!vaccine) {
        clearInventoryUpdateFields();
        return;
    }

    setInputValue('inventoryStockQuantity', vaccine.stock_quantity || 0);
    setInputValue('inventoryMinimumStock', vaccine.minimum_stock_level || 10);
    setInputValue('inventoryBatchNumber', vaccine.batch_number || '');
    setInputValue('inventoryExpiryDate', vaccine.expiry_date || '');
    setInputValue('inventoryStatus', vaccine.status || 'available');
}

function setInputValue(id, value) {
    const element = document.getElementById(id);

    if (element) {
        element.value = value;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const vaccineSelect = document.getElementById('inventoryVaccineSelect');

    if (vaccineSelect) {
        vaccineSelect.addEventListener('change', function () {
            fillInventoryUpdateFields(this.value);
        });
    }

    const inventoryForm = document.getElementById('updateVaccineInventoryForm');

    if (inventoryForm) {
        inventoryForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const form = this;
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

                    if (data.success) {
                        if (typeof $ !== 'undefined') {
                            $('#updateVaccineInventoryModal').modal('hide');
                        }

                        form.reset();

                        if (typeof loadVaccinations === 'function') {
                            loadVaccinations();
                        }

                        switchVaccinationTab('inventory');
                    }

                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                })
                .catch(function (error) {
                    console.error('Update Inventory Error:', error);
                    alert('Server error while updating inventory.');

                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                });
        });
    }
});