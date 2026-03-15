(function initMazuUsersTable() {
  const appContainer = document.getElementById("usersApp");
  const baseUrl = appContainer?.dataset?.baseUrl === "/" ? "" : appContainer?.dataset?.baseUrl || "";

  if (!appContainer) return;
  if (appContainer.dataset.initialized === "true") return;
  appContainer.dataset.initialized = "true";

  let rawData = [];
  try {
    rawData = JSON.parse(appContainer.dataset.users || "[]");
  } catch (e) {
    console.error("Gagal mem-parsing data users", e);
  }

  const currentUserEmail = appContainer.dataset.email || "";

  let currentData = [...rawData];
  let currentPage = 1;
  let limit = 10;
  let sortColumn = "name";
  let sortDirection = "asc";
  let searchQuery = "";

  const tableBody = document.getElementById("tableBody");
  const searchInput = document.getElementById("searchInput");
  const limitSelect = document.getElementById("limitSelect");
  const tableInfo = document.getElementById("tableInfo");
  const paginationWrapper = document.getElementById("paginationWrapper");
  const sortableHeaders = document.querySelectorAll("th.usr-sortable");

  // Ikon SVG untuk digunakan berulang kali agar UI bersih
  const iconView = `<svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
  const iconEdit = `<svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>`;
  const iconTrash = `<svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>`;
  const iconClose = `<svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>`;

  function renderTable() {
    if (!tableBody) return;
    tableBody.innerHTML = "";

    let filtered = rawData.filter((user) => {
      const term = searchQuery.toLowerCase();
      const nameMatch = (user.name || "").toLowerCase().includes(term);
      const emailMatch = (user.email || "").toLowerCase().includes(term);
      return nameMatch || emailMatch;
    });

    filtered.sort((a, b) => {
      let valA = (a[sortColumn] || "").toString().toLowerCase();
      let valB = (b[sortColumn] || "").toString().toLowerCase();

      if (sortColumn === "is_registered") {
        valA = a.is_registered ? 1 : 0;
        valB = b.is_registered ? 1 : 0;
      }

      if (valA < valB) return sortDirection === "asc" ? -1 : 1;
      if (valA > valB) return sortDirection === "asc" ? 1 : -1;
      return 0;
    });

    currentData = filtered;

    const totalItems = currentData.length;
    const perPage = limit === "all" ? totalItems : parseInt(limit);
    const totalPages = Math.ceil(totalItems / perPage) || 1;

    if (currentPage > totalPages) currentPage = totalPages;

    const startIndex = (currentPage - 1) * perPage;
    const endIndex = limit === "all" ? totalItems : startIndex + perPage;
    const paginatedData = currentData.slice(startIndex, endIndex);

    if (paginatedData.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-secondary);">Tidak ada pengguna ditemukan.</td></tr>`;
    } else {
      paginatedData.forEach((user) => {
        const tr = document.createElement("tr");

        const avatarHtml = user.avatar
          ? `<img src="${user.avatar}" class="usr-avatar" alt="Avatar">`
          : `<div class="usr-avatar">${(user.name || "U").charAt(0).toUpperCase()}</div>`;

        let roleClass = "badge-gray";
        if (user.role === "admin") roleClass = "badge-blue";
        if (user.role === "approver") roleClass = "badge-orange";
        if (user.role === "user") roleClass = "badge-green";

        const statusBadge = user.is_registered
          ? `<span class="usr-badge badge-green">Mazu System</span>`
          : `<span class="usr-badge badge-gray">G-Suite Only</span>`;

        let actionHtml = "";

        if (user.email === currentUserEmail) {
          actionHtml = `<span style="color: var(--text-secondary); font-size: 13px; margin-right: 16px;">(Anda)</span>`;
        } else if (user.is_registered) {
          // Terintegrasi penuh dengan GLOBAL MODAL `.css-modal`
          actionHtml = `
            <div class="usr-actions">
              <a data-spa href="${baseUrl}/users/${user.id}" class="usr-btn-icon" title="Lihat Profil">${iconView}</a>
              <a data-spa href="${baseUrl}/users/${user.id}/edit" class="usr-btn-icon" title="Edit Akses">${iconEdit}</a>
              
              <button type="button" class="usr-btn-icon danger" title="Hapus Akses" onclick="document.getElementById('modal-del-${user.id}').classList.add('show')">
                ${iconTrash}
              </button>

              <div id="modal-del-${user.id}" class="css-modal">
                <div class="modal-overlay" onclick="this.parentElement.classList.remove('show')"></div>
                <div class="modal-content">
                  <div class="modal-header">
                    <h3 class="modal-title text-danger">Cabut Akses Pengguna</h3>
                    <button type="button" class="modal-close" onclick="document.getElementById('modal-del-${user.id}').classList.remove('show')">${iconClose}</button>
                  </div>
                  <div class="modal-body" style="text-align: left;">
                    <p>Apakah Anda yakin ingin mencabut hak akses sistem dari <strong>${user.name}</strong> (${user.email})?</p>
                    <p style="font-size: 0.85rem; color: var(--text-disabled); margin-top: 8px;">Catatan: Riwayat pengajuan jadwal dari pengguna ini akan tetap tersimpan, namun pengguna tidak akan bisa login ke dalam aplikasi lagi.</p>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="document.getElementById('modal-del-${user.id}').classList.remove('show')">Batal</button>
                    <form action="${baseUrl}/users/${user.id}/delete" data-spa method="POST" style="margin:0;">
                      <button type="submit" class="btn-confirm danger">Ya, Cabut Akses</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          `;
        } else {
          const safeId = user.email.replace(/[^a-zA-Z0-9]/g, "");
          actionHtml = `
            <div class="usr-actions">
              <button type="button" class="usr-btn-text success" onclick="document.getElementById('modal-grant-${safeId}').classList.add('show')">+ Beri Akses</button>

              <div id="modal-grant-${safeId}" class="css-modal">
                <div class="modal-overlay" onclick="this.parentElement.classList.remove('show')"></div>
                <div class="modal-content">
                  <div class="modal-header">
                    <h3 class="modal-title">Beri Akses Sistem</h3>
                    <button type="button" class="modal-close" onclick="document.getElementById('modal-grant-${safeId}').classList.remove('show')">${iconClose}</button>
                  </div>
                  <div class="modal-body" style="text-align: left;">
                    <p>Pengguna <strong>${user.name}</strong> (${user.email}) akan didaftarkan ke dalam database.</p>
                    <p style="font-size: 0.85rem; color: var(--text-disabled); margin-top: 8px;">Secara bawaan (default), pengguna ini akan diberikan hak akses sebagai <strong>Approver</strong>. Anda dapat mengubahnya nanti.</p>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="document.getElementById('modal-grant-${safeId}').classList.remove('show')">Batal</button>
                    <form action="${baseUrl}/users/register-from-google" method="POST" data-spa style="margin:0;">
                      <input type="hidden" name="email" value="${user.email}">
                      <input type="hidden" name="name" value="${user.name}">
                      <button type="submit" class="btn-confirm success">Daftarkan Sekarang</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          `;
        }

        tr.innerHTML = `
          <td>
            <div class="usr-profile">
              ${avatarHtml}
              <span class="usr-name">${user.name || "Tanpa Nama"}</span>
            </div>
          </td>
          <td>${user.email}</td>
          <td>${user.google_org_unit || "-"}</td>
          <td>${statusBadge}</td>
          <td><span class="usr-badge ${roleClass}">${(user.role || "GUEST").toUpperCase()}</span></td>
          <td class="usr-text-right">${actionHtml}</td>
        `;
        tableBody.appendChild(tr);
      });
    }

    renderPagination(totalItems, perPage, totalPages);
    updateHeaders();
  }

  function renderPagination(total, perPage, totalPages) {
    if (!paginationWrapper) return;
    if (total === 0) {
      tableInfo.textContent = `Menampilkan 0 dari 0 pengguna`;
      paginationWrapper.innerHTML = "";
      return;
    }

    const start = (currentPage - 1) * perPage + 1;
    const end = Math.min(currentPage * perPage, total);
    if (tableInfo) tableInfo.textContent = `Menampilkan ${start}-${end} dari ${total} pengguna`;

    let pgHtml = "";
    pgHtml += `<button class="usr-page-btn" ${currentPage === 1 ? "disabled" : ""} data-page="${currentPage - 1}">
      <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><polyline points="15 18 9 12 15 6"></polyline></svg>
    </button>`;

    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, startPage + 4);
    if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);

    for (let i = startPage; i <= endPage; i++) {
      pgHtml += `<button class="usr-page-btn ${i === currentPage ? "active" : ""}" data-page="${i}">${i}</button>`;
    }

    pgHtml += `<button class="usr-page-btn" ${currentPage === totalPages ? "disabled" : ""} data-page="${currentPage + 1}">
      <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </button>`;
    
    paginationWrapper.innerHTML = pgHtml;

    paginationWrapper.querySelectorAll(".usr-page-btn").forEach((btn) => {
      btn.addEventListener("click", function () {
        if (!this.disabled) {
          currentPage = parseInt(this.getAttribute("data-page"));
          renderTable();
        }
      });
    });
  }

  function updateHeaders() {
    sortableHeaders.forEach((th) => {
      let icon = th.querySelector(".usr-sort-icon");
      if(!icon) {
        icon = document.createElement('span');
        icon.className = 'usr-sort-icon';
        th.appendChild(icon);
      }
      
      if (th.getAttribute("data-sort") === sortColumn) {
        icon.innerHTML = sortDirection === "asc" 
          ? `<svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>` 
          : `<svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>`;
        th.style.color = "var(--text-primary)";
      } else {
        icon.innerHTML = "";
        th.style.color = "";
      }
    });
  }

  if (searchInput) {
    searchInput.addEventListener("input", (e) => {
      searchQuery = e.target.value;
      currentPage = 1;
      renderTable();
    });
  }

  if (limitSelect) {
    limitSelect.addEventListener("change", (e) => {
      limit = e.target.value;
      currentPage = 1;
      renderTable();
    });
  }

  sortableHeaders.forEach((th) => {
    th.addEventListener("click", () => {
      const column = th.getAttribute("data-sort");
      if (sortColumn === column) {
        sortDirection = sortDirection === "asc" ? "desc" : "asc";
      } else {
        sortColumn = column;
        sortDirection = "asc";
      }
      renderTable();
    });
  });

  renderTable();
})();