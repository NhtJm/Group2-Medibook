document.addEventListener('DOMContentLoaded', () => {
    const doctorId = window.DOCTOR_SCHEDULE_CONFIG.doctorId;
    const apiUrl = window.DOCTOR_SCHEDULE_CONFIG.apiUrl;

    // Toggle slot on click
    document.querySelectorAll('.slot-btn:not(:disabled)').forEach(btn => {
        btn.addEventListener('click', async () => {
            const date = btn.dataset.date;
            const time = btn.dataset.time;
            const startTime = `${date} ${time}:00`;

            btn.disabled = true;

            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'toggle_slot',
                        doctor_id: doctorId,
                        start_time: startTime,
                        duration: 30
                    })
                });

                const result = await response.json();

                if (result.success) {
                    btn.classList.toggle('slot-btn--active');
                    if (result.slot_id) {
                        btn.dataset.slotId = result.slot_id;
                    } else {
                        delete btn.dataset.slotId;
                    }
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('An error occurred');
                console.error(error);
            }

            btn.disabled = false;
        });
    });

    // Bulk add modal
    const modal = document.getElementById('bulkAddModal');
    const bulkBtn = document.getElementById('bulkAddBtn');
    const cancelBtn = document.getElementById('cancelBulkAdd');
    const closeBtn = modal.querySelector('.modal__close');
    const backdrop = modal.querySelector('.modal__backdrop');
    const form = document.getElementById('bulkAddForm');

    bulkBtn.addEventListener('click', () => {
        modal.style.display = 'flex';
    });

    const closeModal = () => {
        modal.style.display = 'none';
        form.reset();
    };

    cancelBtn.addEventListener('click', closeModal);
    closeBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(form);
        const date = formData.get('date');
        const startTime = formData.get('start_time');
        const endTime = formData.get('end_time');
        
        // Fixed 30-minute interval
        const interval = 30;

        // Generate time slots
        const slots = [];
        let current = startTime;
        while (current < endTime) {
            slots.push(current);
            const [h, m] = current.split(':').map(Number);
            const totalMinutes = h * 60 + m + interval;
            const newH = Math.floor(totalMinutes / 60);
            const newM = totalMinutes % 60;
            current = `${String(newH).padStart(2, '0')}:${String(newM).padStart(2, '0')}`;
        }

        if (slots.length === 0) {
            alert('No slots to add. Check your time range.');
            return;
        }

        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'bulk_add',
                    doctor_id: doctorId,
                    date: date,
                    slots: slots
                })
            });

            const result = await response.json();

            if (result.success) {
                alert(`Added ${result.added} slots successfully!`);
                window.location.reload();
            } else {
                alert('Error: ' + result.error);
            }
        } catch (error) {
            alert('An error occurred');
            console.error(error);
        }
    });
});
