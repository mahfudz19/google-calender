(function initMazuUsersTable() {
    // Cari kontainer utama yang membawa data JSON
    const appContainer = document.getElementById('usersApp');
    
    // Jika elemen tidak ada (mungkin user pindah halaman), batalkan eksekusi
    if (!appContainer) return;

    // Cegah inisialisasi ganda jika SPA meload ulang script yang sama
    if (appContainer.dataset.initialized === "true") return;
    appContainer.dataset.initialized = "true";

    // Parse data dari Data Attribute HTML (Aman & SPA Friendly)
    let rawData = [];
    try {
        rawData = JSON.parse(appContainer.dataset.users || "[]");
    } catch (e) {
        console.error("Gagal mem-parsing data users", e);
    }
    
    const currentUserEmail = appContainer.dataset.email || '';

    // State Aplikasi
    let currentData = [...rawData]; 
    let currentPage = 1;
    let limit = 10;
    let sortColumn = 'name';
    let sortDirection = 'asc';
    let searchQuery = '';

    // Elemen DOM
    const tableBody = document.getElementById('tableBody');
    const searchInput = document.getElementById('searchInput');
    const limitSelect = document.getElementById('limitSelect');
    const tableInfo = document.getElementById('tableInfo');
    const paginationWrapper = document.getElementById('paginationWrapper');
    const sortableHeaders = document.querySelectorAll('th.sortable');

    // 1. FUNGSI RENDER TABEL
    function renderTable() {
        if(!tableBody) return;
        tableBody.innerHTML = ''; 
        
        let filtered = rawData.filter(user => {
            const term = searchQuery.toLowerCase();
            const nameMatch = (user.name || '').toLowerCase().includes(term);
            const emailMatch = (user.email || '').toLowerCase().includes(term);
            return nameMatch || emailMatch;
        });

        filtered.sort((a, b) => {
            let valA = (a[sortColumn] || '').toString().toLowerCase();
            let valB = (b[sortColumn] || '').toString().toLowerCase();
            
            if (sortColumn === 'is_registered') {
                valA = a.is_registered ? 1 : 0; valB = b.is_registered ? 1 : 0;
            }

            if (valA < valB) return sortDirection === 'asc' ? -1 : 1;
            if (valA > valB) return sortDirection === 'asc' ? 1 : -1;
            return 0;
        });

        currentData = filtered;

        const totalItems = currentData.length;
        const perPage = limit === 'all' ? totalItems : parseInt(limit);
        const totalPages = Math.ceil(totalItems / perPage) || 1;
        
        if (currentPage > totalPages) currentPage = totalPages;

        const startIndex = (currentPage - 1) * perPage;
        const endIndex = limit === 'all' ? totalItems : startIndex + perPage;
        const paginatedData = currentData.slice(startIndex, endIndex);

        if (paginatedData.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center" style="padding: 3rem; color: #94a3b8;">Tidak ada data ditemukan.</td></tr>`;
        } else {
            paginatedData.forEach(user => {
                const tr = document.createElement('tr');
                
                const avatarHtml = user.avatar 
                    ? `<img src="${user.avatar}" class="avatar" alt="Avatar">`
                    : `<div class="avatar">${(user.name || 'U').charAt(0).toUpperCase()}</div>`;
                
                let roleClass = 'bg-gray';
                if(user.role === 'admin') roleClass = 'bg-primary';
                if(user.role === 'approver') roleClass = 'bg-orange';
                if(user.role === 'user') roleClass = 'bg-blue';

                const statusBadge = user.is_registered 
                    ? `<span class="badge bg-green">Terdaftar</span>` 
                    : `<span class="badge bg-gray">G-Suite Saja</span>`;
                
                let actionHtml = '';
                
                if (user.email === currentUserEmail) {
                    actionHtml = `<span class="text-muted" style="font-style: italic;">(Anda)</span>`;
                } else if (user.is_registered) {
                    // Modal Hapus (SPA-Safe)
                    actionHtml = `
                        <div class="action-buttons">
                            <a data-spa href="/users/${user.id}" class="btn-act" title="Lihat">👁️</a>
                            <a data-spa href="/users/${user.id}/edit" class="btn-act" title="Edit">✏️</a>
                            
                            <button type="button" class="btn-act danger" title="Hapus" onclick="document.getElementById('modal-del-${user.id}').classList.add('show')">
                              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                            </button>

                            <div id="modal-del-${user.id}" class="css-modal">
                                <div class="modal-overlay" onclick="this.parentElement.classList.remove('show')"></div>
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h3 class="modal-title text-danger">Konfirmasi Hapus</h3>
                                        <button type="button" class="modal-close" onclick="document.getElementById('modal-del-${user.id}').classList.remove('show')">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Apakah anda yakin ingin menghapus akses untuk <strong>${user.name}</strong> (${user.email})?</p>
                                        <p class="text-muted" style="margin-top: 0.5rem; font-size: 0.85rem;">Data riwayat pengajuan tidak akan hilang, namun user tidak bisa login lagi.</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn-cancel" onclick="document.getElementById('modal-del-${user.id}').classList.remove('show')">Batal</button>
                                        <form action="/users/${user.id}/delete" data-spa method="POST" style="margin:0;">
                                            <button type="submit" class="btn-confirm danger">Ya, Hapus Akses</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    // Modal Beri Akses (SPA-Safe)
                    // Hapus spasi dan karakter aneh pada email untuk ID
                    const safeId = user.email.replace(/[^a-zA-Z0-9]/g, '');
                    actionHtml = `
                        <div class="action-buttons" style="justify-content: flex-end;">
                            <button type="button" class="btn-act success" title="Beri Akses Mazu" onclick="document.getElementById('modal-grant-${safeId}').classList.add('show')">+ Beri Akses</button>

                            <div id="modal-grant-${safeId}" class="css-modal">
                                <div class="modal-overlay" onclick="this.parentElement.classList.remove('show')"></div>
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h3 class="modal-title">Beri Akses Sistem</h3>
                                        <button type="button" class="modal-close" onclick="document.getElementById('modal-grant-${safeId}').classList.remove('show')">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Anda akan mendaftarkan <strong>${user.name}</strong> (${user.email}) ke dalam sistem Mazu Calendar.</p>
                                        <p class="text-muted" style="margin-top: 0.5rem; font-size: 0.85rem;">Secara default, user akan diberikan role <strong>Approver</strong>.</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn-cancel" onclick="document.getElementById('modal-grant-${safeId}').classList.remove('show')">Batal</button>
                                        <form action="/users/register-from-google" method="POST" data-spa style="margin:0;">
                                            <input type="hidden" name="email" value="${user.email}">
                                            <input type="hidden" name="name" value="${user.name}">
                                            <button type="submit" class="btn-confirm success">Konfirmasi</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }

                tr.innerHTML = `
                    <td>
                        <div class="user-profile">
                            ${avatarHtml}
                            <span class="user-name">${user.name || 'Tanpa Nama'}</span>
                        </div>
                    </td>
                    <td>${user.email}</td>
                    <td>${user.google_org_unit || '-'}</td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center"><span class="badge ${roleClass}">${(user.role || 'GUEST').toUpperCase()}</span></td>
                    <td class="text-right">${actionHtml}</td>
                `;
                tableBody.appendChild(tr);
            });
        }

        renderPagination(totalItems, perPage, totalPages);
        updateHeaders();
    }

    // 2. FUNGSI RENDER PAGINASI
    function renderPagination(total, perPage, totalPages) {
        if(!paginationWrapper) return;
        if (total === 0) {
            tableInfo.textContent = `Menampilkan 0 dari 0 pengguna`;
            paginationWrapper.innerHTML = '';
            return;
        }

        const start = (currentPage - 1) * perPage + 1;
        const end = Math.min(currentPage * perPage, total);
        if(tableInfo) tableInfo.textContent = `Menampilkan ${start} - ${end} dari total ${total} pengguna`;

        let pgHtml = '';
        pgHtml += `<button class="page-btn" ${currentPage === 1 ? 'disabled' : ''} data-page="${currentPage - 1}">«</button>`;
        
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);

        for (let i = startPage; i <= endPage; i++) {
            pgHtml += `<button class="page-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }

        pgHtml += `<button class="page-btn" ${currentPage === totalPages ? 'disabled' : ''} data-page="${currentPage + 1}">»</button>`;
        paginationWrapper.innerHTML = pgHtml;

        paginationWrapper.querySelectorAll('.page-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (!this.disabled) {
                    currentPage = parseInt(this.getAttribute('data-page'));
                    renderTable();
                }
            });
        });
    }

    // 3. FUNGSI UPDATE ICON HEADER
    function updateHeaders() {
        sortableHeaders.forEach(th => {
            const icon = th.querySelector('.sort-icon');
            if (th.getAttribute('data-sort') === sortColumn) {
                icon.textContent = sortDirection === 'asc' ? '▲' : '▼';
                th.style.color = 'var(--primary)';
            } else {
                icon.textContent = '⇅';
                th.style.color = '';
            }
        });
    }

    // EVENT LISTENERS (Hanya dipasang jika elemen ada)
    if(searchInput) {
        searchInput.addEventListener('input', (e) => {
            searchQuery = e.target.value;
            currentPage = 1;
            renderTable();
        });
    }

    if(limitSelect) {
        limitSelect.addEventListener('change', (e) => {
            limit = e.target.value;
            currentPage = 1;
            renderTable();
        });
    }

    sortableHeaders.forEach(th => {
        th.addEventListener('click', () => {
            const column = th.getAttribute('data-sort');
            if (sortColumn === column) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortColumn = column;
                sortDirection = 'asc';
            }
            renderTable();
        });
    });

    // Render Pertama Kali
    renderTable();
})();