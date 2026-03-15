// ==========================================
// MODAL CONTROLLER (Global Modal CSS)
// ==========================================
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) modal.classList.add("show");
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) modal.classList.remove("show");
}

// ==========================================
// APPROVAL ACTION LOGIC
// ==========================================
function handleApprove(id) {
  openModal(`approveModal_${id}`);
}

function submitApprove(id) {
  closeModal(`approveModal_${id}`);
  applyOptimisticLock(id);

  fetch(`${window.SWR_CONFIG.getBaseUrl}/approval/${id}/approve`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-Requested-With": "XMLHttpRequest",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        if (typeof window.mazuQueueTriggerFetch === "function") {
          window.mazuQueueTriggerFetch();
        }
      } else {
        showError(data.message || "Terjadi kesalahan saat menyetujui agenda.");
        removeOptimisticLock(id);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showError("Terjadi kesalahan jaringan. Periksa koneksi internet Anda.");
      removeOptimisticLock(id);
    });
}

function applyOptimisticLock(id) {
  // Menggunakan prefix class apv-card dan apv-actions yang baru
  const item = document.querySelector(`.apv-card[data-id="${id}"]`);
  if (!item) return;

  const actionPanel = item.querySelector(".apv-actions");
  if (actionPanel) {
    item.dataset.originalActions = actionPanel.innerHTML;
    actionPanel.innerHTML = `
      <span class="apv-status-badge processing">
        <svg class="apv-spinner" viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-linecap="round"></circle></svg>
        Memproses...
      </span>
    `;
  }
  item.style.opacity = "0.6";
  item.style.pointerEvents = "none";
}

function removeOptimisticLock(id) {
  const item = document.querySelector(`.apv-card[data-id="${id}"]`);
  if (!item) return;

  const actionPanel = item.querySelector(".apv-actions");
  if (actionPanel && item.dataset.originalActions) {
    actionPanel.innerHTML = item.dataset.originalActions;
  }
  item.style.opacity = "1";
  item.style.pointerEvents = "auto";
}

// ==========================================
// BACKGROUND SYNC (DOM Replacement)
// ==========================================
if (!window.mazuApprovalListInit) {
  window.mazuApprovalListInit = true;

  window.addEventListener("mazu:queue-synced", async () => {
    const container = document.getElementById("mazu-approval-inner");
    if (!container) return;

    try {
      const res = await fetch(window.location.href, {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });
      const html = await res.text();
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, "text/html");
      const newContainer = doc.getElementById("mazu-approval-inner");

      if (newContainer) {
        container.innerHTML = newContainer.innerHTML;
      }
    } catch (err) {
      console.error("Sync Error:", err);
    }
  });
}

// ==========================================
// ERROR HANDLING UTILS
// ==========================================
function showError(message) {
  let errorAlert = document.getElementById("globalErrorAlert");
  if (!errorAlert) {
    errorAlert = document.createElement("div");
    errorAlert.id = "globalErrorAlert";
    errorAlert.className = "apv-alert-error";

    const container = document.getElementById("mazu-approval-inner");
    if (container) container.prepend(errorAlert);
  }

  errorAlert.innerHTML = `
    <div class="apv-alert-icon">⚠️</div>
    <div class="apv-alert-body">
        <strong>Terjadi Kesalahan</strong>
        <p>${message}</p>
    </div>
    <button type="button" class="apv-alert-close" onclick="this.closest('.apv-alert-error').remove()">
      <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </button>
  `;
  errorAlert.scrollIntoView({ behavior: "smooth", block: "start" });
}