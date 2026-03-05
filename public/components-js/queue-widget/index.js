(function initMazuQueueWidget() {
    // ==========================================
    // 0. MAZU SPA SAFEGUARD (Dieksekusi Hanya 1x Selamanya)
    // ==========================================
    if (!window.mazuQueueSWRInit) {
        window.mazuQueueSWRInit = true;

        // SPA Route Change - Bersihkan timer usang sebelum transisi pindah halaman
        window.addEventListener('spa:before-navigate', () => {
            document.getElementById('qw-toast-msg')?.remove();
            if (window.mazuQueueTimer) {
                clearTimeout(window.mazuQueueTimer);
                window.mazuQueueTimer = null;
            }
        });

        // SPA Route Change - Halaman baru selesai dimuat
        window.addEventListener('spa:navigated', () => {
            const widget = document.getElementById('queue-widget');
            if (widget) {
                widget.dataset.initialized = "false";
                // Panggil fetch dari closure yang paling baru
                if (typeof window.mazuQueueTriggerFetch === 'function') {
                    window.mazuQueueTriggerFetch();
                }
            }
        });

        // Visibility Change - Tab Pindah dalam 1 Browser
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && typeof window.mazuQueueVisibilityTrigger === 'function') {
                window.mazuQueueVisibilityTrigger();
            } else if (document.hidden && window.mazuQueueTimer) {
                clearTimeout(window.mazuQueueTimer); 
            }
        });

        // Window Kehilangan Fokus (Pindah Virtual Desktop, Minimize, atau Buka App OS lain)
        window.addEventListener('blur', () => {
            if (window.mazuQueueTimer) {
                clearTimeout(window.mazuQueueTimer);
            }
        });

        // Window Kembali Fokus
        window.addEventListener('focus', () => {
            if (!document.hidden && typeof window.mazuQueueVisibilityTrigger === 'function') {
                window.mazuQueueVisibilityTrigger();
            }
        });
    }

    // ==========================================
    // 1. INISIALISASI WIDGET
    // ==========================================
    const container = document.getElementById('queue-widget');
    if (!container) return;

    if (container.dataset.initialized === "true") return;
    container.dataset.initialized = "true";

    if (window.mazuQueueTimer) {
        clearTimeout(window.mazuQueueTimer);
    }

    let isWorkerActive = false;
    let isFetching = false;
    let queueData = JSON.parse(localStorage.getItem(SWR_CONFIG.cacheKey)) || [];

    // --- VARIABEL DEDUPLIKASI FETCH ---
    let lastFetchTime = 0;
    const DEDUPE_TIME = 2000; // Cooldown 2 detik untuk mencegah spam request

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
        <div class="widget-body" id="qw-list-body"></div>
    `;

    const listBody = document.getElementById('qw-list-body');
    const btnRefresh = document.getElementById('qw-btn-refresh');

    // ==========================================
    // 3. FUNGSI RENDER DOM LIST
    // ==========================================
    function renderListItems(showSkeleton = false) {
        if (showSkeleton && queueData.length === 0) {
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
            let retryHtml = '';

            const currentUnixTime = Math.floor(Date.now() / 1000);
            const isZombie = item.status === 'Processing' && 
                             item.reserved_at && 
                             (currentUnixTime - item.reserved_at > 3600);

            if (item.status === 'Processing' || item.status === 'Optimistic') {
                statusColor = 'var(--md-sys-color-primary)';
                icon = '⚙️';
                
                if (isZombie) {
                    statusColor = 'var(--md-sys-color-error)';
                    retryHtml = `
                        <button type="button" class="btn-retry" data-id="${item.id}" title="Paksa proses ulang (Macet > 1 Jam)" style="color: var(--md-sys-color-error); background: transparent; border: 1px dashed var(--md-sys-color-error); border-radius: 4px; padding: 2px 6px; font-size: 10px; cursor: pointer;">
                            ⚠️ Paksa Ulangi
                        </button>
                    `;
                }
            } else if (item.status === 'Failed') {
                statusColor = 'var(--md-sys-color-error)';
                icon = '❌';
                iconClass = 'icon-red';
                retryHtml = `
                    <button type="button" class="btn-retry" data-id="${item.id}" title="Coba proses ulang" style="cursor:pointer;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        Ulangi
                    </button>
                `;
            }

            let deleteHtml = `
                <button type="button" class="btn-delete" data-id="${item.id}" title="Hapus antrean" style="color: var(--md-sys-color-error); background: transparent; border: 1px solid var(--md-sys-color-error); border-radius: 4px; padding: 2px 6px; font-size: 10px; cursor: pointer; margin-left: 8px;">
                    🗑️ Hapus
                </button>
            `;

            const opacity = item.status === 'Optimistic' ? '0.6' : '1';

            return `
                <div class="queue-item ${item.status === 'Failed' ? 'failed' : ''}" style="opacity: ${opacity};">
                    <div class="queue-icon ${iconClass}">${icon}</div>
                    <div class="queue-content">
                        <h4 class="queue-title">${item.title}</h4>
                        <p class="queue-meta">
                            <span>${item.requester}</span>
                            <span style="color: ${statusColor}; font-weight: 700;">${item.status === 'Optimistic' ? 'Pending' : item.status}</span>
                        </p>
                        <div class="queue-action-row" style="display: flex; align-items: center; margin-top: 4px;">
                            <small style="color: var(--md-sys-color-outline-variant); flex-grow: 1;">${item.time}</small>
                            ${retryHtml}
                            ${deleteHtml}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    // ==========================================
    // 4. LOGIKA SWR POLLING & DEDUPLIKASI
    // ==========================================
    async function mutateAndRevalidate(forceRefreshUi = false) {
        // Rem Darurat 1: Cek eksistensi elemen, tab tersembunyi, ATAU window kehilangan fokus OS
        if (!document.getElementById('queue-widget') || document.hidden || !document.hasFocus()) {
            if (window.mazuQueueTimer) clearTimeout(window.mazuQueueTimer);
            return; 
        }

        const now = Date.now();
        
        // Rem Darurat 2 (Deduplikasi): Mencegah multiple request beruntun dari event SPA / Focus OS
        if (isFetching || (!forceRefreshUi && (now - lastFetchTime < DEDUPE_TIME))) {
            return;
        }
        
        isFetching = true;
        if (forceRefreshUi) btnRefresh.classList.add('spin');

        try {
            const res = await fetch(SWR_CONFIG.apiEndpoint, { headers: { 'Accept': 'application/json' } });
            
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
                
                queueData = newData;
                localStorage.setItem(SWR_CONFIG.cacheKey, JSON.stringify(newData));
                renderListItems();
            }
        } catch (error) {
            console.error("Queue Fetch Error:", error);
        } finally {
            isFetching = false;
            lastFetchTime = Date.now(); // Catat waktu kapan request selesai
            btnRefresh.classList.remove('spin');
            schedulePolling();
        }
    }

    function schedulePolling() {
        if (window.mazuQueueTimer) clearTimeout(window.mazuQueueTimer);
        // Daftarkan ulang timer HANYA JIKA window punya fokus dan elemen masih ada
        if (!document.hidden && document.hasFocus() && document.getElementById('queue-widget')) {
            window.mazuQueueTimer = setTimeout(() => mutateAndRevalidate(false), SWR_CONFIG.interval);
        }
    }

    window.mazuQueueTriggerFetch = () => mutateAndRevalidate(true);
    window.mazuQueueVisibilityTrigger = () => { setTimeout(() => mutateAndRevalidate(false), 500); };

    // ==========================================
    // 5. EKSEKUSI API: RETRY & DELETE
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
    // 6. EVENT LISTENERS LOKAL
    // ==========================================
    btnRefresh.addEventListener('click', () => {
        showToast("⏳ Sinkronisasi antrean manual...");
        mutateAndRevalidate(true);
    });

    listBody.addEventListener('click', function(e) {
        const btnRetry = e.target.closest('.btn-retry');
        const btnDelete = e.target.closest('.btn-delete');
        
        if (btnRetry) {
            executeItemRetry(btnRetry.getAttribute('data-id'), btnRetry);
        } else if (btnDelete) {
            executeItemDelete(btnDelete.getAttribute('data-id'), btnDelete);
        }
    });

    // ==========================================
    // 7. TOAST NOTIFICATION SYSTEM
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
    // 8. EKSEKUSI PERTAMA
    // ==========================================
    renderListItems(queueData.length === 0);
    mutateAndRevalidate(false);

})();