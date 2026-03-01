function handleAction(action, id) {
  const card = document.querySelector(`.approval-card[data-id="${id}"]`);

  if (action === "approve") {
    if (confirm("Apakah Anda yakin ingin menyetujui agenda ini?")) {
      // Simulasi sukses
      card.style.transition = "all 0.5s";
      card.style.transform = "translateX(100px)";
      card.style.opacity = "0";

      setTimeout(() => {
        card.remove();
        checkEmptyState();
      }, 500);

      console.log(`Approved agenda ID: ${id}`);
    }
  } else if (action === "reject") {
    const reason = prompt("Masukkan alasan penolakan:");
    if (reason !== null) {
      // Simulasi reject
      card.style.transition = "all 0.5s";
      card.style.transform = "translateX(-100px)";
      card.style.opacity = "0";

      setTimeout(() => {
        card.remove();
        checkEmptyState();
      }, 500);

      console.log(`Rejected agenda ID: ${id}. Reason: ${reason}`);
    }
  }
}

function checkEmptyState() {
  const list = document.querySelector(".approval-list");
  if (list.children.length === 0) {
    list.innerHTML = `
      <div class="empty-state">
        <div class="empty-icon">🎉</div>
        <h3>Semua bersih!</h3>
        <p>Tidak ada permintaan agenda yang perlu persetujuan saat ini.</p>
      </div>
    `;
  }
}

// Auto-hide error setelah 10 detik
document.addEventListener('DOMContentLoaded', function() {
    const errorAlert = document.querySelector('.error-alert');
    if (errorAlert) {
        setTimeout(() => {
            errorAlert.style.transition = 'opacity 0.3s';
            errorAlert.style.opacity = '0';
            setTimeout(() => errorAlert.remove(), 300);
        }, 10000);
    }
});

function handleApprove(id) {
  // Show modal
  document.getElementById(`approveModal_${id}`).showModal();
}

function submitApprove(id) {
  // 1. Close modal
  document.getElementById(`approveModal_${id}`).close();
  
  // 2. Update UI immediately to processing state
  const btn = document.getElementById(`approveBtn_${id}`);
  const btnText = document.getElementById(`approveBtnText_${id}`);
  
  btn.disabled = true;
  btn.classList.add('processing');
  btnText.textContent = 'Processing...';
  
  // 3. Submit via AJAX instead of form submit
  fetch(`/approval/${id}/approve`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === 'success') {
      // Start polling for status updates
      pollApprovalStatus(id);
    } else {
      // Handle error
      showError(data.message || 'Terjadi kesalahan');
      resetApproveButton(id);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showError('Terjadi kesalahan jaringan');
    resetApproveButton(id);
  });
}

function resetApproveButton(id) {
  const btn = document.getElementById(`approveBtn_${id}`);
  const btnText = document.getElementById(`approveBtnText_${id}`);
  
  btn.disabled = false;
  btn.classList.remove('processing');
  btnText.textContent = 'Setujui';
}

function showError(message) {
  // Create or update error alert
  let errorAlert = document.querySelector('.error-alert');
  if (!errorAlert) {
    errorAlert = document.createElement('div');
    errorAlert.className = 'error-alert';
    document.querySelector('.approval-container').prepend(errorAlert);
  }
  
  errorAlert.innerHTML = `
    <div class="error-icon">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="15" y1="9" x2="9" y2="15"></line>
        <line x1="9" y1="9" x2="15" y2="15"></line>
      </svg>
    </div>
    <div class="error-content">
      <div class="error-title">Terjadi Kesalahan</div>
      <div class="error-message">${message}</div>
    </div>
    <button class="error-close" onclick="this.parentElement.remove()" title="Tutup">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    </button>
  `;
}

function hideProgressIndicator(id) {
  const card = document.querySelector(`[data-id="${id}"]`);
  const progressIndicator = document.getElementById(`progressIndicator_${id}`);
  
  card.classList.remove('processing');
  progressIndicator.style.display = 'none';
}

function resetApproveButton(id) {
  const btn = document.getElementById(`approveBtn_${id}`);
  const btnText = document.getElementById(`approveBtnText_${id}`);
  
  btn.disabled = false;
  btn.classList.remove('processing');
  btnText.textContent = 'Setujui';
}

function showError(message) {
  // Create or update error alert
  let errorAlert = document.querySelector('.error-alert');
  if (!errorAlert) {
    errorAlert = document.createElement('div');
    errorAlert.className = 'error-alert';
    document.querySelector('.approval-container').prepend(errorAlert);
  }
  
  errorAlert.innerHTML = `
    <div class="error-icon">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="15" y1="9" x2="9" y2="15"></line>
        <line x1="9" y1="9" x2="15" y2="15"></line>
      </svg>
    </div>
    <div class="error-content">
      <div class="error-title">Terjadi Kesalahan</div>
      <div class="error-message">${message}</div>
    </div>
    <button class="error-close" onclick="this.parentElement.remove()" title="Tutup">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    </button>
  `;
}

function pollApprovalStatus(id) {
  const checkStatus = async () => {
    try {
      const response = await fetch(`/approval/${id}/status`);
      const result = await response.json();
      
      if (result.status === 'success') {
        const approvalStatus = result.data.approval_status;
        
        if (approvalStatus === 'approved') {
          window.location.reload();
        } else if (approvalStatus === 'rejected') {
          window.location.reload();
        } else if (approvalStatus === 'processing') {
          setTimeout(checkStatus, 3000);
        }
      } else {
        console.error('Status check error:', result.message);
        showError(result.message || 'Error checking status');
        setTimeout(checkStatus, 5000);
      }
    } catch (error) {
      console.error('Error checking status:', error);
      showError('Terjadi kesalahan jaringan');
      setTimeout(checkStatus, 5000);
    }
  };
  
  setTimeout(checkStatus, 1000);
}