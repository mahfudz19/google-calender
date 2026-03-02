document.addEventListener('DOMContentLoaded', () => {
    // Mengambil data yang dilempar dari PHP (via Window Object)
    const rawData = window.MAZU_USERS_DATA || [];
    const currentUserEmail = window.MAZU_LOGGED_IN_EMAIL || '';

    // State Aplikasi (Paginasi, Sort, Filter)
    let currentData = [...rawData]; // Copy data asli
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
        tableBody.innerHTML = ''; // Bersihkan tabel
        
        // Eksekusi proses data berurutan: Filter -> Sort -> Paginate
        let filtered = rawData.filter(user => {
            const term = searchQuery.toLowerCase();
            const nameMatch = (user.name || '').toLowerCase().includes(term);
            const emailMatch = (user.email || '').toLowerCase().includes(term);
            return nameMatch || emailMatch;
        });

        filtered.sort((a, b) => {
            let valA = (a[sortColumn] || '').toString().toLowerCase();
            let valB = (b[sortColumn] || '').toString().toLowerCase();
            
            // Khusus sorting bolean status registered
            if (sortColumn === 'is_registered') {
                valA = a.is_registered ? 1 : 0; valB = b.is_registered ? 1 : 0;
            }

            if (valA < valB) return sortDirection === 'asc' ? -1 : 1;
            if (valA > valB) return sortDirection === 'asc' ? 1 : -1;
            return 0;
        });

        currentData = filtered;

        // Paginasi Array
        const totalItems = currentData.length;
        const perPage = limit === 'all' ? totalItems : parseInt(limit);
        const totalPages = Math.ceil(totalItems / perPage) || 1;
        
        if (currentPage > totalPages) currentPage = totalPages;

        const startIndex = (currentPage - 1) * perPage;
        const endIndex = limit === 'all' ? totalItems : startIndex + perPage;
        const paginatedData = currentData.slice(startIndex, endIndex);

        // Cetak ke HTML
        if (paginatedData.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center" style="padding: 3rem; color: #94a3b8;">Tidak ada data ditemukan.</td></tr>`;
        } else {
            paginatedData.forEach(user => {
                const tr = document.createElement('tr');
                
                // Avatar Logic
                const avatarHtml = user.avatar 
                    ? `<img src="${user.avatar}" class="avatar" alt="Avatar">`
                    : `<div class="avatar">${(user.name || 'U').charAt(0).toUpperCase()}</div>`;
                
                // Role Badge Logic
                let roleClass = 'bg-gray';
                if(user.role === 'admin') roleClass = 'bg-primary';
                if(user.role === 'approver') roleClass = 'bg-orange';
                if(user.role === 'user') roleClass = 'bg-blue';

                // Status Badge
                const statusBadge = user.is_registered 
                    ? `<span class="badge bg-green">Terdaftar</span>` 
                    : `<span class="badge bg-gray">G-Suite Saja</span>`;

                // Action Buttons HTML
                let actionHtml = '';
                if (user.email === currentUserEmail) {
                    actionHtml = `<span class="text-muted" style="font-style: italic;">(Anda)</span>`;
                } else if (user.is_registered) {
                    actionHtml = `
                        <div class="action-buttons">
                            <a href="/users/${user.id}" class="btn-act" title="Lihat">👁️</a>
                            <a href="/users/${user.id}/edit" class="btn-act" title="Edit">✏️</a>
                            <form action="/users/${user.id}/delete" method="POST" style="display:inline;" onsubmit="return confirm('Hapus akses user ini?');">
                                <button type="submit" class="btn-act danger" title="Hapus">🗑️</button>
                            </form>
                        </div>
                    `;
                } else {
                    actionHtml = `
                        <form action="/users/register-from-google" method="POST" style="display: flex; justify-content: flex-end;">
                            <input type="hidden" name="email" value="${user.email}">
                            <input type="hidden" name="name" value="${user.name}">
                            <button type="submit" class="btn-act success" title="Beri Akses Mazu">+ Beri Akses</button>
                        </form>
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
                    <td class="text-center"><span class="badge ${roleClass}">${user.role || 'GUEST'}</span></td>
                    <td class="text-right">${actionHtml}</td>
                `;
                tableBody.appendChild(tr);
            });
        }

        // Update Info dan Paginasi
        renderPagination(totalItems, perPage, totalPages);
        updateHeaders();
    }

    // 2. FUNGSI RENDER PAGINASI & INFO BAWAH
    function renderPagination(total, perPage, totalPages) {
        if (total === 0) {
            tableInfo.textContent = `Menampilkan 0 dari 0 pengguna`;
            paginationWrapper.innerHTML = '';
            return;
        }

        const start = (currentPage - 1) * perPage + 1;
        const end = Math.min(currentPage * perPage, total);
        tableInfo.textContent = `Menampilkan ${start} - ${end} dari total ${total} pengguna`;

        let pgHtml = '';
        
        // Tombol Prev
        pgHtml += `<button class="page-btn" ${currentPage === 1 ? 'disabled' : ''} data-page="${currentPage - 1}">«</button>`;
        
        // Menampilkan maksimal 5 tombol angka di tengah (Sederhana)
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);

        for (let i = startPage; i <= endPage; i++) {
            pgHtml += `<button class="page-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }

        // Tombol Next
        pgHtml += `<button class="page-btn" ${currentPage === totalPages ? 'disabled' : ''} data-page="${currentPage + 1}">»</button>`;
        
        paginationWrapper.innerHTML = pgHtml;

        // Pasang Event Listener ke tombol paginasi yang baru di-render
        paginationWrapper.querySelectorAll('.page-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (!this.disabled) {
                    currentPage = parseInt(this.getAttribute('data-page'));
                    renderTable();
                }
            });
        });
    }

    // 3. FUNGSI MENGATUR ICON SORTING DI HEADER
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

    // ================= EVENT LISTENERS ================= //

    // Input Pencarian
    searchInput.addEventListener('input', (e) => {
        searchQuery = e.target.value;
        currentPage = 1; // Reset ke hal 1 setiap mencari
        renderTable();
    });

    // Pilihan Limit
    limitSelect.addEventListener('change', (e) => {
        limit = e.target.value;
        currentPage = 1;
        renderTable();
    });

    // Klik Header untuk Sorting
    sortableHeaders.forEach(th => {
        th.addEventListener('click', () => {
            const column = th.getAttribute('data-sort');
            if (sortColumn === column) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'; // Balik arah
            } else {
                sortColumn = column;
                sortDirection = 'asc'; // Default arah saat ganti kolom
            }
            renderTable();
        });
    });

    // Eksekusi Render Pertama Kali
    renderTable();
});