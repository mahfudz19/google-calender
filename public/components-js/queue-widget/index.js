(function initMazuQueueWidget() {
    const container = document.getElementById('queue-widget');
    if (!container) return;

    if (container.dataset.initialized === "true") return;
    container.dataset.initialized = "true";

    // ==========================================
    // 1. SWR CORE CONFIGURATION
    // ==========================================
    const SWR_CONFIG = {
        interval: 10000,      // 10 detik
        cacheKey: 'mazu_qw_cache',
        etagKey: 'mazu_qw_etag',
        apiEndpoint: '/queue', // -> ENDPOINT ASLI (Mendapatkan list & status worker)
    };

    let isWorkerActive = false;
    let isFetching = false;
    let pollingTimer = null;
    let isTabVisible = !document.hidden;

    // State Data: Coba ambil dari localStorage (Cache) untuk kecepatan instan
    let queueData = JSON.parse(localStorage.getItem(SWR_CONFIG.cacheKey)) || [];

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
    // 3. FUNGSI RENDER DOM
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

            if (item.status === 'Processing' || item.status === 'Optimistic') {
                statusColor = 'var(--md-sys-color-primary)';
                icon = '⚙️';
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

            // Tombol Delete untuk setiap antrean (opsional: bisa juga disembunyikan jika status Processing)
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
    // 4. LOGIKA SWR (FETCH DARI BACKEND ASLI)
    // ==========================================
    async function mutateAndRevalidate(forceRefreshUi = false) {
        if (isFetching || !isTabVisible) return;
        
        isFetching = true;
        if (forceRefreshUi) btnRefresh.classList.add('spin');

        try {
            // Fetch ke endpoint GET /queue yang mengembalikan stats, jobs, & worker_active
            const res = await fetch(SWR_CONFIG.apiEndpoint, { 
                headers: { 'Accept': 'application/json' }
            });
            
            if (res.ok) {
                const data = await res.json();
                
                // 1. Update Worker Status
                isWorkerActive = data.worker_active;
                const dot = document.getElementById('qw-worker-dot');
                const txt = document.getElementById('qw-worker-text');
                if (dot && txt) {
                    dot.className = `status-dot ${isWorkerActive ? 'active' : ''}`;
                    txt.textContent = `Worker: ${isWorkerActive ? 'Aktif' : 'Mati'}`;
                }

                // 2. Mapping Data Database ke Format UI
                const newData = (data.jobs || []).map(job => {
                    let payloadObj = {};
                    try { 
                        // Payload disimpan sebagai string JSON di database MySQL
                        payloadObj = JSON.parse(job.payload); 
                    } catch(e) {}

                    // Ekstrak nama class job sebagai judul (misal: "App\Jobs\SendEmail" jadi "SendEmail")
                    let jobTitle = payloadObj.job_class ? payloadObj.job_class.split('\\').pop() : 'Tugas Sistem';
                    
                    return {
                        id: job.id,
                        title: jobTitle,
                        requester: `Attempt: ${job.attempts}`, // Bisa diganti data dinamis dari payload jika ada
                        time: job.created_at || 'Baru Saja',
                        // Kapitalisasi huruf pertama status (pending -> Pending)
                        status: job.status.charAt(0).toUpperCase() + job.status.slice(1)
                    };
                });
                
                // 3. Simpan dan Render
                queueData = newData;
                localStorage.setItem(SWR_CONFIG.cacheKey, JSON.stringify(newData));
                renderListItems();
            }
        } catch (error) {
            console.error("Queue Fetch Error:", error);
        } finally {
            isFetching = false;
            btnRefresh.classList.remove('spin');
            schedulePolling();
        }
    }

    function schedulePolling() {
        clearTimeout(pollingTimer);
        if (isTabVisible && document.getElementById('queue-widget')) {
            pollingTimer = setTimeout(() => mutateAndRevalidate(false), SWR_CONFIG.interval);
        }
    }

    // ==========================================
    // 5. EKSEKUSI API: RETRY & DELETE
    // ==========================================
    
    // --> FUNGSI RETRY (POST /queue/:id/retry)
    async function executeItemRetry(itemId, btnEl) {
        btnEl.disabled = true;
        showToast(`⏳ Meminta proses ulang Job #${itemId}...`);
        
        try {
            const res = await fetch(`/queue/${itemId}/retry`, {
                method: 'POST',
                headers: { 'Accept': 'application/json' }
            });
            const result = await res.json();

            if (res.ok) {
                showToast(`✅ ${result.message}`);
                mutateAndRevalidate(true); // Langsung fetch data terbaru dari DB
            } else {
                showToast(`❌ ${result.error || 'Gagal mereset job'}`);
                btnEl.disabled = false;
            }
        } catch (err) {
            showToast("❌ Terjadi kesalahan jaringan.");
            btnEl.disabled = false;
        }
    }

    // --> FUNGSI DELETE (POST /queue/:id/delete)
    async function executeItemDelete(itemId, btnEl) {
        if (!confirm(`Yakin ingin menghapus antrean #${itemId}?`)) return;

        btnEl.disabled = true;
        showToast(`⏳ Menghapus Job #${itemId}...`);

        try {
            const res = await fetch(`/queue/${itemId}/delete`, {
                method: 'POST',
                headers: { 'Accept': 'application/json' }
            });
            const result = await res.json();

            if (res.ok) {
                showToast(`✅ ${result.message}`);
                mutateAndRevalidate(true); // Fetch ulang list terbaru
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
    // 6. EVENT LISTENERS & LIFECYCLES
    // ==========================================
    
    btnRefresh.addEventListener('click', () => {
        showToast("⏳ Sinkronisasi antrean manual...");
        mutateAndRevalidate(true);
    });

    // Menggunakan Event Delegation untuk tombol Retry dan Delete
    listBody.addEventListener('click', function(e) {
        const btnRetry = e.target.closest('.btn-retry');
        const btnDelete = e.target.closest('.btn-delete');
        
        if (btnRetry) {
            const itemId = btnRetry.getAttribute('data-id');
            executeItemRetry(itemId, btnRetry);
        } else if (btnDelete) {
            const itemId = btnDelete.getAttribute('data-id');
            executeItemDelete(itemId, btnDelete);
        }
    });

    // Revalidate on Focus
    document.addEventListener('visibilitychange', () => {
        isTabVisible = !document.hidden;
        if (isTabVisible) {
            setTimeout(() => mutateAndRevalidate(false), 500); 
        } else {
            clearTimeout(pollingTimer);
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
    // 8. GLOBAL MUTATE RECEIVER
    // ==========================================
    window.addEventListener('mazu:queue-mutate', () => {
        if(isTabVisible) mutateAndRevalidate(true);
    });

    // ==========================================
    // 9. EKSEKUSI PERTAMA
    // ==========================================
    renderListItems(queueData.length === 0);
    mutateAndRevalidate(false);

})();

// ==========================================
// MAZU SPA SAFEGUARD
// ==========================================
if (!window.mazuQueueSWRInit) {
    window.addEventListener('spa:before-navigate', () => {
        document.getElementById('qw-toast-msg')?.remove();
    });

    window.addEventListener('spa:navigated', () => {
        if (document.getElementById('queue-widget')) {
            document.getElementById('queue-widget').dataset.initialized = "false";
            window.dispatchEvent(new Event('mazu:queue-mutate'));
        }
    });

    window.mazuQueueSWRInit = true;
}