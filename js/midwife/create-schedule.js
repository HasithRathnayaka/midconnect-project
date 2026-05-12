document.addEventListener('DOMContentLoaded', function () {
    const scheduleForm = document.getElementById('scheduleItemForm');

    if (!scheduleForm) {
        return;
    }

    scheduleForm.addEventListener('submit', function (e) {
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
                    alert(data.message || 'Schedule item added successfully.');

                    form.reset();

                    $('#addScheduleModal').modal('hide');

                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }

                    setTimeout(() => {
                        window.location.href = 'dashboard.php#schedule';
                    }, 300);
                } else {
                    alert(data.message || 'Failed to add schedule item.');

                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                }
            })
            .catch(error => {
                console.error('Schedule Error:', error);
                alert('Server error while saving schedule item.');

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
    });
});