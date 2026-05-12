document.addEventListener('DOMContentLoaded', function () {
    const visitForm = document.getElementById('newVisitForm');

    if (!visitForm) {
        return;
    }

    visitForm.addEventListener('submit', function (e) {
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
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Home visit scheduled successfully.');

                    form.reset();

                    $('#scheduleVisitModal').modal('hide');

                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }

                    setTimeout(() => {
                        window.location.href = 'dashboard.php#home-visits';
                    }, 300);
                } else {
                    alert(data.message || 'Failed to schedule home visit.');

                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                }
            })
            .catch(error => {
                console.error('Home Visit Error:', error);
                alert('Server error while scheduling home visit.');

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
    });

    $('#scheduleVisitModal').on('shown.bs.modal', function () {
        const dateInput = visitForm.querySelector('input[name="visit_date"]');

        if (dateInput && !dateInput.value) {
            dateInput.value = new Date().toISOString().split('T')[0];
        }
    });
});