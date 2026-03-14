document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('addDoctorModal');
    const addBtn = document.getElementById('addDoctorBtn');
    const cancelBtn = document.getElementById('cancelAddDoctor');
    const closeBtn = modal.querySelector('.modal__close');
    const backdrop = modal.querySelector('.modal__backdrop');
    const form = document.getElementById('addDoctorForm');

    const photoInput = document.getElementById('doctorPhotoInput');
    const photoPreview = document.getElementById('doctorPhotoPreview');
    const photoPlaceholder = document.getElementById('photoPlaceholder');
    const photoPreviewImg = document.getElementById('photoPreviewImg');
    const removePhotoBtn = document.getElementById('removePhotoBtn');

    const apiUrl = window.OFFICE_DASHBOARD_CONFIG.apiUrl;

    photoInput.addEventListener('change', () => {
        const file = photoInput.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                photoPreviewImg.src = e.target.result;
                photoPreviewImg.style.display = 'block';
                photoPlaceholder.style.display = 'none';
                removePhotoBtn.style.display = 'inline-block';
            };
            reader.readAsDataURL(file);
        }
    });

    removePhotoBtn.addEventListener('click', () => {
        photoInput.value = '';
        photoPreviewImg.src = '';
        photoPreviewImg.style.display = 'none';
        photoPlaceholder.style.display = 'block';
        removePhotoBtn.style.display = 'none';
    });

    addBtn.addEventListener('click', () => {
        modal.style.display = 'flex';
    });

    const closeModal = () => {
        modal.style.display = 'none';
        form.reset();
        photoPreviewImg.src = '';
        photoPreviewImg.style.display = 'none';
        photoPlaceholder.style.display = 'block';
        removePhotoBtn.style.display = 'none';
    };

    cancelBtn.addEventListener('click', closeModal);
    closeBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(form);

        try {
            const response = await fetch(apiUrl + '&action=add_doctor', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alert('Doctor added successfully!');
                window.location.reload();
            } else {
                alert('Error: ' + result.error);
            }
        } catch (error) {
            alert('An error occurred. Please try again.');
            console.error(error);
        }
    });

    document.querySelectorAll('.delete-doctor-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const doctorId = btn.dataset.id;
            
            if (!confirm('Are you sure you want to delete this doctor?\n\n⚠️ WARNING: All appointment slots and booked appointments for this doctor will also be permanently deleted.\n\nThis action cannot be undone.')) {
                return;
            }

            try {
                const response = await fetch(apiUrl + '&action=delete_doctor', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ doctor_id: doctorId })
                });

                const result = await response.json();

                if (result.success) {
                    window.location.reload();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
                console.error(error);
            }
        });
    });
});
