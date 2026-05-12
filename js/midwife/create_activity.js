document.addEventListener('DOMContentLoaded', function () {
    const activityForm = document.querySelector('form[action="../php/midwife/create_activity.php"]');

    if (!activityForm) {
        console.error('Activity form not found.');
        return;
    }

    activityForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const form = this;
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn ? submitBtn.innerHTML : '';

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        }

        fetch(form.action, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(res => res.json())
            .then(data => {
                console.log('Create Activity Response:', data);

                if (data.success) {
                    alert(data.message || 'Activity logged successfully.');

                    form.reset();

                    // Use dashboard.php if your file is dashboard.php
                    window.location.href = 'dashboard.php';

                    // If your file is dashboard.php, use this instead:
                    // window.location.href = 'dashboard.php';

                } else {
                    alert(data.message || 'Failed to log activity.');

                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                }
            })
            .catch(err => {
                console.error('Create Activity Error:', err);
                alert('Server error while logging activity.');

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });
    });
});