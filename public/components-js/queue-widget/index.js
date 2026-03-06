(function initMazuQueueWidget() {
    // ==========================================
    // 0. MAZU SPA SAFEGUARD (Dieksekusi Hanya 1x Selamanya)
    // ==========================================
    if (!window.mazuQueueSWRInit) {
        window.mazuQueueSWRInit = true;

        window.addEventListener('spa:before-navigate', () => {
            document.getElementById('qw-toast-msg')?.remove();
            document.getElementById('qw-detail-modal')?.remove(); // Bersihkan modal saat pindah
            if (window.mazuQueueTimer) {
                clearTimeout(window.mazuQueueTimer);
                window.mazuQueueTimer = null;
            }
            if (typeof window.mazuQueueDisableUI === 'function') window.mazuQueueDisableUI();
        });

        window.addEventListener('spa:navigated', () => {
            const widget = document.getElementById('queue-widget');
            if (widget) {
                widget.dataset.initialized = "false";
                if (typeof window.mazuQueueTriggerFetch === 'function') {
                    window.mazuQueueTriggerFetch();
                }
            }
        });

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                if (window.mazuQueueTimer) clearTimeout(window.mazuQueueTimer); 
                if (typeof window.mazuQueueDisableUI === 'function') window.mazuQueueDisableUI();
            } else {
                if (typeof window.mazuQueueVisibilityTrigger === 'function') window.mazuQueueVisibilityTrigger();
            }
        });

        window.addEventListener('blur', () => {
            if (window.mazuQueueTimer) clearTimeout(window.mazuQueueTimer);
            if (typeof window.mazuQueueDisableUI === 'function') window.mazuQueueDisableUI();
        });

        window.addEventListener('focus', () => {
            if (!document.hidden && typeof window.mazuQueueVisibilityTrigger === 'function') {
                window.mazuQueueVisibilityTrigger();
            }
        });
    }

    // ==========================================
    // 1. INISIALISASI WIDGET & CACHE STATE
    // ==========================================
    const container = document.getElementById('queue-widget');
    if (!container) return;

    if (container.dataset.initialized === "true") return;
    container.dataset.initialized = "true";

    if (window.mazuQueueTimer) {
        clearTimeout(window.mazuQueueTimer);
    }

    const CONFIG = typeof SWR_CONFIG !== 'undefined' ? SWR_CONFIG : {
        interval: 10000,      
        cacheKey: 'mazu_qw_cache',
        apiEndpoint: '/queue',
    };

    let hasCache = localStorage.getItem(CONFIG.cacheKey) !== null;
    let queueData = hasCache ? JSON.parse(localStorage.getItem(CONFIG.cacheKey)) : [];

    let isWorkerActive = false;
    let isFetching = false;
    let isInitialRender = true; 

    let lastFetchTime = 0;
    const DEDUPE_TIME = 2000;

    window.mazuQueueDisableUI = () => {
        const listBody = document.getElementById('qw-list-body');
        if (listBody) {
            listBody.style.opacity = '0.5';
            listBody.style.pointerEvents = 'none';
        }
    };

    // ==========================================
    // 2. RENDER KERANGKA UTAMA
    // ==========================================
    container.innerHTML = `
        <div class="widget-header">
            <div class="widget-title-group">
                <h3 class="widget-title">
                    <span style="color: var(--md-sys-color-primary);">⏳</span> Antrean Tugas
                </h3>
                <div class="worker-status">
                    <span id="qw-worker-dot" class="status-dot ${isWorkerActive ? 'active' : ''}"></span>
                    <span id="qw-worker-text">Worker: ${isWorkerActive ? 'Aktif' : 'Mati'}</span>
                </div>
            </div>
            <button type="button" class="btn-refresh" id="qw-btn-refresh" title="Refresh Data">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"></path><path d="M21 3v5h-5"></path></svg>
            </button>
        </div>
        <div class="widget-body" id="qw-list-body" style="transition: opacity 0.3s ease;"></div>
    `;

    const listBody = document.getElementById('qw-list-body');
    const btnRefresh = document.getElementById('qw-btn-refresh');

    // ==========================================
    // 3. FUNGSI RENDER DOM LIST
    // ==========================================
    function renderListItems(showSkeleton = false) {
        if (showSkeleton) {
            listBody.innerHTML = Array(3).fill(0).map(() => `
                <div class="queue-item">
                    <div class="skel skel-icon"></div>
                    <div class="queue-content" style="justify-content: center;">
                        <div class="skel skel-text"></div>
                        <div class="skel skel-text short"></div>
                    </div>
                </div>
            `).join('');
            return;
        }

        if (queueData.length === 0) {
            listBody.innerHTML = `<div style="text-align:center; padding: 2rem; color: var(--md-sys-color-outline);">Semua antrean bersih 🎉</div>`;
            return;
        }

        listBody.innerHTML = queueData.map(item => {
            let statusColor = 'var(--md-sys-color-outline)';
            let icon = '📄';
            let iconClass = 'icon-blue';
            let actionHtml = '';

            const currentUnixTime = Math.floor(Date.now() / 1000);
            const isZombie = item.status === 'Processing' && item.reserved_at && (currentUnixTime - item.reserved_at > 3600);

            // LOGIKA TOMBOL & STATUS
            if (item.status === 'Processing' || item.status === 'Optimistic') {
                statusColor = 'var(--md-sys-color-primary)';
                icon = '⚙️';
                
                // Jika Processing (terutama yang zombie), munculkan tombol Detail
                actionHtml += `
                    <button type="button" class="btn-detail" data-id="${item.id}" title="Lihat Detail Antrean" style="color: var(--md-sys-color-primary); background: transparent; border: 1px solid var(--md-sys-color-primary); border-radius: 4px; padding: 2px 6px; font-size: 10px; cursor: pointer; margin-right: 8px;">
                        ℹ️ Detail
                    </button>
                `;

                if (isZombie) {
                    statusColor = 'var(--md-sys-color-error)';
                    actionHtml += `
                        <button type="button" class="btn-retry" data-id="${item.id}" title="Paksa proses ulang (Macet > 1 Jam)" style="color: var(--md-sys-color-error); background: transparent; border: 1px dashed var(--md-sys-color-error); border-radius: 4px; padding: 2px 6px; font-size: 10px; cursor: pointer;">
                            ⚠️ Paksa Ulangi
                        </button>
                    `;
                }

                // Tombol Hapus TIDAK DIMUNCULKAN di sini (Hanya bisa dihapus dari Modal Detail jika terpaksa)

            } else if (item.status === 'Failed') {
                statusColor = 'var(--md-sys-color-error)';
                icon = '❌';
                iconClass = 'icon-red';
                
                // Failed: Munculkan Retry dan Hapus (Sesuai kesepakatan baru: Hapus boleh di Failed & Pending)
                actionHtml += `
                    <button type="button" class="btn-retry" data-id="${item.id}" title="Coba proses ulang" style="cursor:pointer; margin-right: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        Ulangi
                    </button>
                    <button type="button" class="btn-detail" data-id="${item.id}" title="Lihat Detail Antrean" ...>
                        ℹ️ Detail
                    </button>
                `;
            } else {
                // Status Pending biasa: Boleh dihapus
                actionHtml += `
                    <button type="button" class="btn-delete" data-id="${item.id}" title="Hapus antrean" style="color: var(--md-sys-color-error); background: transparent; border: 1px solid var(--md-sys-color-error); border-radius: 4px; padding: 2px 6px; font-size: 10px; cursor: pointer;">
                        🗑️ Hapus
                    </button>
                `;
            }

            return `
                <div class="queue-item ${item.status === 'Failed' ? 'failed' : ''}">
                    <div class="queue-icon ${iconClass}">${icon}</div>
                    <div class="queue-content">
                        <h4 class="queue-title">${item.title}</h4>
                        <p class="queue-meta">
                            <span>${item.requester}</span>
                            <span style="color: ${statusColor}; font-weight: 700;">${item.status}</span>
                        </p>
                        <div class="queue-action-row" style="display: flex; align-items: center; margin-top: 4px;">
                            <small style="color: var(--md-sys-color-outline-variant); flex-grow: 1;">${item.time}</small>
                            ${actionHtml}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    // ==========================================
    // 4. LOGIKA SWR POLLING & ETAG
    // ==========================================
    async function mutateAndRevalidate(forceRefreshUi = false) {
        if (!document.getElementById('queue-widget') || document.hidden || !document.hasFocus()) {
            if (window.mazuQueueTimer) clearTimeout(window.mazuQueueTimer);
            return; 
        }

        const now = Date.now();
        
        if (isFetching || (!forceRefreshUi && (now - lastFetchTime < DEDUPE_TIME))) {
            if (!isFetching && document.getElementById('qw-list-body')) {
                document.getElementById('qw-list-body').style.opacity = '1';
                document.getElementById('qw-list-body').style.pointerEvents = 'auto';
            }
            return;
        }
        
        isFetching = true;
        if (forceRefreshUi) btnRefresh.classList.add('spin');

        if (!hasCache && isInitialRender) {
            renderListItems(true); 
        } else {
            window.mazuQueueDisableUI();
        }

        try {
            const res = await fetch(CONFIG.apiEndpoint, { headers: { 'Accept': 'application/json' } });
            
            if (res.ok) {
                const data = await res.json();
                
                isWorkerActive = data.worker_active;
                const dot = document.getElementById('qw-worker-dot');
                const txt = document.getElementById('qw-worker-text');
                if (dot && txt) {
                    dot.className = `status-dot ${isWorkerActive ? 'active' : ''}`;
                    txt.textContent = `Worker: ${isWorkerActive ? 'Aktif' : 'Mati'}`;
                }

                const newData = (data.jobs || []).map(job => {
                    let payloadObj = {};
                    try { payloadObj = JSON.parse(job.payload); } catch(e) {}
                    let jobTitle = payloadObj.job_class ? payloadObj.job_class.split('\\').pop() : 'Tugas Sistem';
                    
                    return {
                        id: job.id,
                        title: jobTitle,
                        requester: `Attempt: ${job.attempts}`, 
                        time: job.created_at || 'Baru Saja',
                        status: job.status.charAt(0).toUpperCase() + job.status.slice(1),
                        reserved_at: job.reserved_at 
                    };
                });
                
                const newDataStr = JSON.stringify(newData);
                const oldDataStr = JSON.stringify(queueData);

                if (newDataStr !== oldDataStr || isInitialRender) {
                    queueData = newData;
                    localStorage.setItem(CONFIG.cacheKey, newDataStr);
                    hasCache = true; 
                    renderListItems(false); 
                    window.dispatchEvent(new CustomEvent('mazu:queue-synced'));
                }
            }
        } catch (error) {
            console.error("Queue Fetch Error:", error);
        } finally {
            isFetching = false;
            isInitialRender = false; 
            lastFetchTime = Date.now();
            btnRefresh.classList.remove('spin');
            
            const listBodyFinal = document.getElementById('qw-list-body');
            if (listBodyFinal) {
                listBodyFinal.style.opacity = '1';
                listBodyFinal.style.pointerEvents = 'auto';
            }
            
            schedulePolling();
        }
    }

    function schedulePolling() {
        if (window.mazuQueueTimer) clearTimeout(window.mazuQueueTimer);
        if (!document.hidden && document.hasFocus() && document.getElementById('queue-widget')) {
            window.mazuQueueTimer = setTimeout(() => mutateAndRevalidate(false), CONFIG.interval);
        }
    }

    window.mazuQueueTriggerFetch = () => mutateAndRevalidate(true);
    window.mazuQueueVisibilityTrigger = () => { setTimeout(() => mutateAndRevalidate(false), 500); };


    // ==========================================
    // 5. DETAIL MODAL LOGIC (FITUR BARU)
    // ==========================================
    
    // Injeksi Kerangka Modal ke Body (Hanya 1 kali)
    function injectDetailModal() {
        if (document.getElementById('qw-detail-modal')) return;
        
        const modalHtml = `
            <div id="qw-detail-modal" class="css-modal">
                <div class="modal-overlay" onclick="closeQueueDetailModal()"></div>
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">🔍 Detail Antrean</h3>
                        <button type="button" class="modal-close" onclick="closeQueueDetailModal()">&times;</button>
                    </div>
                    <div class="modal-body" style="text-align: left; max-height: 400px; overflow-y: auto;">
                        <div id="qw-detail-loading" style="text-align: center; padding: 2rem;">
                            <span class="spinner-mini"></span> Mengambil data...
                        </div>
                        <div id="qw-detail-content" style="display: none;">
                            </div>
                    </div>
                    <div class="modal-footer" style="justify-content: space-between;">
                        <button type="button" id="btn-modal-refresh" class="btn-cancel" style="border-color: var(--md-sys-color-primary); color: var(--md-sys-color-primary);">🔄 Refresh Status</button>
                        <div style="display: flex; gap: 0.5rem;">
                            <button type="button" class="btn-cancel" onclick="closeQueueDetailModal()">Tutup</button>
                            <button type="button" id="btn-modal-delete" class="btn-confirm danger" style="display: none;">Hapus Paksa</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }

    window.closeQueueDetailModal = () => {
        const m = document.getElementById('qw-detail-modal');
        if (m) m.classList.remove('show');
    };

    async function loadQueueDetail(id) {
        injectDetailModal();
        const modal = document.getElementById('qw-detail-modal');
        const loading = document.getElementById('qw-detail-loading');
        const content = document.getElementById('qw-detail-content');
        const btnDelete = document.getElementById('btn-modal-delete');
        const btnRefresh = document.getElementById('btn-modal-refresh');

        modal.classList.add('show');
        loading.style.display = 'block';
        content.style.display = 'none';
        btnDelete.style.display = 'none';

        try {
            const res = await fetch(`/queue/${id}`, { headers: { 'Accept': 'application/json' } });
            
            if (res.ok) {
                const data = await res.json();
                
                // Format waktu
                const createdTime = new Date(data.created_at).toLocaleString('id-ID');
                const reservedTime = data.reserved_at ? new Date(data.reserved_at * 1000).toLocaleString('id-ID') : 'Belum diambil';
                
                // Format error log (Jika ada)
                const errorLog = data.error_message 
                    ? `<div style="background: var(--md-sys-color-error-container); color: var(--md-sys-color-error); padding: 8px; border-radius: 6px; margin-top: 8px; font-size: 0.85rem; font-family: monospace; overflow-x: auto;">${data.error_message}</div>`
                    : '<span style="color: var(--md-sys-color-primary);">Aman (Tidak ada error)</span>';

                content.innerHTML = `
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr style="border-bottom: 1px solid var(--md-sys-color-outline-variant);">
                            <td style="padding: 8px 0; font-weight: 600; width: 40%;">ID Antrean</td>
                            <td style="padding: 8px 0;">#${data.id}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--md-sys-color-outline-variant);">
                            <td style="padding: 8px 0; font-weight: 600;">Status</td>
                            <td style="padding: 8px 0; font-weight: 700; text-transform: uppercase;">${data.status}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--md-sys-color-outline-variant);">
                            <td style="padding: 8px 0; font-weight: 600;">Percobaan (Attempts)</td>
                            <td style="padding: 8px 0;">${data.attempts}x</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--md-sys-color-outline-variant);">
                            <td style="padding: 8px 0; font-weight: 600;">Waktu Dibuat</td>
                            <td style="padding: 8px 0;">${createdTime}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--md-sys-color-outline-variant);">
                            <td style="padding: 8px 0; font-weight: 600;">Mulai Diproses</td>
                            <td style="padding: 8px 0;">${reservedTime}</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding: 8px 0;">
                                <div style="font-weight: 600; margin-bottom: 4px;">Catatan Sistem / Error Log:</div>
                                ${errorLog}
                            </td>
                        </tr>
                    </table>
                `;

                // Update aksi tombol Modal
                btnRefresh.onclick = () => loadQueueDetail(id);
                
                // Tombol Hapus Paksa di dalam modal jika admin yakin mau kill job
                btnDelete.style.display = 'block';
                btnDelete.onclick = () => {
                    closeQueueDetailModal();
                    executeItemDelete(id, document.createElement('button')); 
                };

                loading.style.display = 'none';
                content.style.display = 'block';
            } else {
                content.innerHTML = `<div style="color: red; text-align: center;">Antrean tidak ditemukan. Mungkin sudah selesai atau terhapus.</div>`;
                loading.style.display = 'none';
                content.style.display = 'block';
            }
        } catch (err) {
            content.innerHTML = `<div style="text-align: center;">Gagal mengambil data jaringan.</div>`;
            loading.style.display = 'none';
            content.style.display = 'block';
        }
    }


    // ==========================================
    // 6. EKSEKUSI API: RETRY & DELETE
    // ==========================================
    async function executeItemRetry(itemId, btnEl) {
        btnEl.disabled = true;
        showToast(`⏳ Meminta proses ulang Job #${itemId}...`);
        try {
            const res = await fetch(`/queue/${itemId}/retry`, { method: 'POST', headers: { 'Accept': 'application/json' } });
            const result = await res.json();
            if (res.ok) {
                showToast(`✅ ${result.message}`);
                mutateAndRevalidate(true); 
            } else {
                showToast(`❌ ${result.error || 'Gagal mereset job'}`);
                btnEl.disabled = false;
            }
        } catch (err) {
            showToast("❌ Terjadi kesalahan jaringan.");
            btnEl.disabled = false;
        }
    }

    async function executeItemDelete(itemId, btnEl) {
        if (!confirm(`Yakin ingin menghapus antrean #${itemId}?`)) return;
        btnEl.disabled = true;
        showToast(`⏳ Menghapus Job #${itemId}...`);
        try {
            const res = await fetch(`/queue/${itemId}/delete`, { method: 'POST', headers: { 'Accept': 'application/json' } });
            const result = await res.json();
            if (res.ok) {
                showToast(`✅ ${result.message}`);
                mutateAndRevalidate(true); 
            } else {
                showToast(`❌ ${result.error || 'Gagal menghapus job'}`);
                btnEl.disabled = false;
            }
        } catch (err) {
            showToast("❌ Terjadi kesalahan jaringan.");
            btnEl.disabled = false;
        }
    }

    // ==========================================
    // 7. EVENT LISTENERS LOKAL
    // ==========================================
    btnRefresh.addEventListener('click', () => {
        showToast("⏳ Sinkronisasi antrean manual...");
        mutateAndRevalidate(true);
    });

    listBody.addEventListener('click', function(e) {
        const btnRetry = e.target.closest('.btn-retry');
        const btnDelete = e.target.closest('.btn-delete');
        const btnDetail = e.target.closest('.btn-detail'); // Listener baru
        
        if (btnRetry) {
            executeItemRetry(btnRetry.getAttribute('data-id'), btnRetry);
        } else if (btnDelete) {
            executeItemDelete(btnDelete.getAttribute('data-id'), btnDelete);
        } else if (btnDetail) {
            loadQueueDetail(btnDetail.getAttribute('data-id'));
        }
    });

    // ==========================================
    // 8. TOAST NOTIFICATION SYSTEM
    // ==========================================
    let toastTimeout;
    function showToast(message) {
        let toast = document.getElementById('qw-toast-msg');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'qw-toast-msg';
            toast.className = 'qw-toast';
            document.body.appendChild(toast);
        }
        toast.innerHTML = message;
        toast.classList.remove('show');
        void toast.offsetWidth;
        toast.classList.add('show');
        clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => toast.classList.remove('show'), 3500);
    }

    // ==========================================
    // 9. EKSEKUSI PERTAMA
    // ==========================================
    if (hasCache) {
        renderListItems(false); 
    }
    mutateAndRevalidate(false);

})();