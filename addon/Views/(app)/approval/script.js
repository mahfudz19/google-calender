// ==========================================
// MODAL CONTROLLER (Menggantikan <dialog>)
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
// APROVAL ACTION LOGIC
// ==========================================
function handleApprove(id) {
  openModal(`approveModal_${id}`);
}

function submitApprove(id) {
  // 1. Tutup modal seketika (Jangan biarkan admin menatap modal membeku)
  closeModal(`approveModal_${id}`);

  // 2. Optimistic Lock: Ubah baris item menjadi state "Memproses" dan kunci UI
  applyOptimisticLock(id);

  // 3. Tembak API & Lupakan
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
        // 4. Triger Global Mutate: Beritahu Widget Antrean untuk fetch seketika
        if (typeof window.mazuQueueTriggerFetch === "function") {
          window.mazuQueueTriggerFetch();
        }
      } else {
        // Gagal validasi backend (Misal konflik jadwal terdeteksi saat hit API)
        showError(data.message || "Terjadi kesalahan saat menyetujui agenda.");
        removeOptimisticLock(id); // Kembalikan UI baris ke normal
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showError(
        "Terjadi kesalahan jaringan. Silakan periksa koneksi internet Anda.",
      );
      removeOptimisticLock(id);
    });
}

// Fungsi Membuka Kunci Baris UI (Jika API gagal)
function removeOptimisticLock(id) {
  const item = document.querySelector(`.modern-list-item[data-id="${id}"]`);
  if (!item) return;

  const actionPanel = item.querySelector(".item-actions-panel");
  if (actionPanel && item.dataset.originalActions) {
    actionPanel.innerHTML = item.dataset.originalActions;
  }

  item.style.opacity = "1";
  item.style.pointerEvents = "auto";
}

// Fungsi Mengunci Baris UI (Mencegah Race Condition Frontend)
function applyOptimisticLock(id) {
  const item = document.querySelector(`.modern-list-item[data-id="${id}"]`);
  if (!item) return;

  const actionPanel = item.querySelector(".item-actions-panel");
  if (actionPanel) {
    // Simpan tombol asli ke memory agar bisa dikembalikan jika API gagal
    item.dataset.originalActions = actionPanel.innerHTML;

    // Ganti tombol dengan status pill processing
    actionPanel.innerHTML = `
            <div class="status-pill processing">
                <span class="spinner-mini"></span> Memproses...
            </div>
        `;
  }

  // Redupkan dan matikan klik
  item.style.opacity = "0.6";
  item.style.pointerEvents = "none";
  item.style.transition = "all 0.3s ease";
}

function resetApproveButton(id) {
  const btn = document.getElementById(`approveBtn_${id}`);
  if (btn) {
    btn.disabled = false;
    btn.classList.remove("processing");
    btn.innerHTML = `<span id="approveBtnText_${id}">Setujui</span>`;
  }
}

// ==========================================
// POLLING LOGIC
// ==========================================
function pollApprovalStatus(id) {
  const checkStatus = async () => {
    try {
      const response = await fetch(
        `${window.SWR_CONFIG.getBaseUrl}/approval/${id}/status`,
      );
      const result = await response.json();

      if (result.status === "success") {
        const approvalStatus = result.data.approval_status;

        if (approvalStatus === "approved" || approvalStatus === "rejected") {
          // Jika sukses/ditolak, muat ulang halaman untuk memperbarui daftar
          window.location.reload();
        } else if (approvalStatus === "processing") {
          // Jika masih proses, cek lagi dalam 3 detik
          setTimeout(checkStatus, 3000);
        }
      } else {
        console.error("Status check error:", result.message);
        showError(result.message || "Gagal mengecek status persetujuan.");
        setTimeout(checkStatus, 5000);
      }
    } catch (error) {
      console.error("Error checking status:", error);
      showError("Terjadi kesalahan jaringan saat mengecek status.");
      setTimeout(checkStatus, 5000);
    }
  };

  setTimeout(checkStatus, 1000);
}

if (!window.mazuApprovalListInit) {
  window.mazuApprovalListInit = true;

  // Mendengarkan detak jantung dari index.js (Widget Antrean)
  window.addEventListener("mazu:queue-synced", async () => {
    const container = document.getElementById("mazu-approval-inner");

    // Jika admin tidak berada di halaman Approval, abaikan (mencegah memory leak)
    if (!container) return;

    try {
      // Diam-diam fetch data HTML halaman ini sendiri di background
      const res = await fetch(window.location.href, {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });
      const html = await res.text();

      // Ekstrak hanya elemen 'mazu-approval-inner' dari respon server
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, "text/html");
      const newContainer = doc.getElementById("mazu-approval-inner");

      if (newContainer) {
        // TUKAR DOM SECARA SILUMAN (Tanpa Reload, Tanpa Skeleton)
        container.innerHTML = newContainer.innerHTML;
      }
    } catch (err) {
      console.error("Gagal melakukan DOM Replacement siluman:", err);
    }
  });
}

// ==========================================
// ERROR HANDLING & UTILS
// ==========================================
function showError(message) {
  let errorAlert = document.getElementById("globalErrorAlert");
  if (!errorAlert) {
    errorAlert = document.createElement("div");
    errorAlert.id = "globalErrorAlert";
    errorAlert.className = "modern-alert error";

    const container = document.getElementById("mazu-approval-inner");
    if (container) container.prepend(errorAlert);
  }

  errorAlert.innerHTML = `
        <div class="alert-icon">⚠️</div>
        <div class="alert-body">
            <h4 class="alert-title">Terjadi Kesalahan</h4>
            <p class="alert-text">${message}</p>
        </div>
        <button class="alert-close" onclick="this.closest('.modern-alert').remove()">&times;</button>
    `;

  // Auto-scroll ke pesan error
  errorAlert.scrollIntoView({ behavior: "smooth", block: "start" });
}

// Auto-hide alert error yang ada di DOM awal setelah 10 detik
document.addEventListener("DOMContentLoaded", function () {
  const errorAlert = document.getElementById("globalErrorAlert");
  if (errorAlert) {
    setTimeout(() => {
      errorAlert.style.transition = "opacity 0.4s ease, transform 0.4s ease";
      errorAlert.style.opacity = "0";
      errorAlert.style.transform = "translateY(-10px)";
      setTimeout(() => errorAlert.remove(), 400);
    }, 10000);
  }
});
