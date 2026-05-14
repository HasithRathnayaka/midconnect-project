document.addEventListener('DOMContentLoaded', function () {
    const vaccineCategory = document.getElementById('vaccineCategory');
    const vaccinationForm = document.getElementById('newVaccinationForm');

    if (vaccineCategory) {
        vaccineCategory.addEventListener('change', function () {
            updateVaccineOptions(this.value);
        });
    }

    if (vaccinationForm) {
        vaccinationForm.addEventListener('submit', function (e) {
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
                        form.reset();
                        updateVaccineOptions('');

                        if (typeof $ !== 'undefined') {
                            $('#scheduleVaccinationModal').modal('hide');
                        }

                        if (typeof loadVaccinations === 'function') {
                            loadVaccinations();
                        }
                    }

                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                })
                .catch(function (error) {
                    console.error('Vaccination Schedule Error:', error);
                    alert('Server error while scheduling vaccination.');

                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                });
        });
    }
});

function updateVaccineOptions(category) {
    const vaccineSelect = document.getElementById('vaccineOptions');

    if (!vaccineSelect) {
        console.error('vaccineOptions dropdown not found');
        return;
    }

    const vaccines = {
        pediatric: [
            { value: 'DPT', text: 'DPT - Diphtheria, Pertussis, Tetanus' },
            { value: 'OPV', text: 'OPV - Oral Polio Vaccine' },
            { value: 'HEPB', text: 'Hepatitis B' },
            { value: 'MMR', text: 'MMR - Measles, Mumps, Rubella' }
        ],
        maternal: [
            { value: 'TT', text: 'Tetanus Toxoid' }
        ],
        adult: [
            { value: 'FLU', text: 'Influenza Vaccine' },
            { value: 'PCV', text: 'Pneumococcal Vaccine' },
            { value: 'TT', text: 'Tetanus Toxoid' }
        ]
    };

    vaccineSelect.innerHTML = '<option value="">Select Vaccine</option>';

    if (!vaccines[category]) {
        vaccineSelect.innerHTML = '<option value="">Select category first</option>';
        return;
    }

    vaccines[category].forEach(function (vaccine) {
        const option = document.createElement('option');
        option.value = vaccine.value;
        option.textContent = vaccine.text;
        vaccineSelect.appendChild(option);
    });
}