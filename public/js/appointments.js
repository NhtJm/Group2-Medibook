(function() {
  const API_URL = window.BASE_URL + 'index.php?page=appointment_api';
  const modal = document.getElementById('editModal');
  if (!modal) return; // Page sans modal
  
  const reschedulePanel = document.getElementById('reschedulePanel');
  const actionsPanel = document.querySelector('.modal__actions');
  const slotList = document.getElementById('slotList');
  const btnConfirm = document.getElementById('btnConfirmReschedule');
  
  let currentApptId = null;
  let selectedSlotId = null;

  // Open modal
  document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', () => {
      currentApptId = btn.dataset.id;
      const item = btn.closest('.appt-item');
      const doctor = item.querySelector('.doc h4')?.textContent || '';
      const date = item.querySelector('.kv span')?.textContent || '';
      
      document.getElementById('modalDoctor').textContent = doctor;
      document.getElementById('modalDate').textContent = date;
      
      // Reset state
      reschedulePanel.hidden = true;
      actionsPanel.style.display = '';
      selectedSlotId = null;
      btnConfirm.disabled = true;
      
      modal.hidden = false;
    });
  });

  // Close modal
  modal.querySelector('.modal__close').addEventListener('click', () => {
    modal.hidden = true;
  });
  modal.addEventListener('click', (e) => {
    if (e.target === modal) modal.hidden = true;
  });

  // Cancel appointment
  document.getElementById('btnCancel').addEventListener('click', async () => {
    if (!confirm('Are you sure you want to cancel this appointment?')) return;
    
    try {
      const res = await fetch(API_URL, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ action: 'cancel', appointment_id: currentApptId })
      });
      const text = await res.text();
      console.log('Response:', text);
      let data;
      try {
        data = JSON.parse(text);
      } catch(e) {
        alert('Server error: ' + text.substring(0, 200));
        return;
      }
      if (data.ok) {
        alert('Appointment cancelled successfully');
        location.reload();
      } else {
        alert(data.msg || 'Failed to cancel appointment');
      }
    } catch (err) {
      console.error(err);
      alert('An error occurred: ' + err.message);
    }
  });

  // Show reschedule panel
  document.getElementById('btnReschedule').addEventListener('click', async () => {
    actionsPanel.style.display = 'none';
    reschedulePanel.hidden = false;
    slotList.innerHTML = '<p class="loading">Loading available slots...</p>';
    
    try {
      const res = await fetch(API_URL, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ action: 'get_slots', appointment_id: currentApptId })
      });
      const data = await res.json();
      
      if (data.ok && data.slots.length > 0) {
        slotList.innerHTML = data.slots.map(s => `
          <div class="slot-item" data-slot="${s.slot_id}">
            <span class="slot-item__date">${s.date_label}</span>
            <span class="slot-item__time">${s.time_label}</span>
          </div>
        `).join('');
        
        // Slot selection
        slotList.querySelectorAll('.slot-item').forEach(item => {
          item.addEventListener('click', () => {
            slotList.querySelectorAll('.slot-item').forEach(i => i.classList.remove('is-selected'));
            item.classList.add('is-selected');
            selectedSlotId = item.dataset.slot;
            btnConfirm.disabled = false;
          });
        });
      } else {
        slotList.innerHTML = '<p class="no-slots">No available slots for this doctor</p>';
      }
    } catch (err) {
      slotList.innerHTML = '<p class="no-slots">Error loading slots</p>';
    }
  });

  // Back button
  document.getElementById('btnBack').addEventListener('click', () => {
    reschedulePanel.hidden = true;
    actionsPanel.style.display = '';
  });

  // Confirm reschedule
  btnConfirm.addEventListener('click', async () => {
    if (!selectedSlotId) return;
    
    try {
      const res = await fetch(API_URL, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ 
          action: 'reschedule', 
          appointment_id: currentApptId,
          new_slot_id: selectedSlotId
        })
      });
      const data = await res.json();
      if (data.ok) {
        alert('Appointment rescheduled successfully');
        location.reload();
      } else {
        alert(data.msg || 'Failed to reschedule appointment');
      }
    } catch (err) {
      console.error(err);
      alert('An error occurred: ' + err.message);
    }
  });
})();
