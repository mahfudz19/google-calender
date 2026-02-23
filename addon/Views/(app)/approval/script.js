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
