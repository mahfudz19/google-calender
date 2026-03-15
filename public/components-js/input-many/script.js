export function initCsvUploader(
  AutocompleteClass,
  currentUser,
  apiCheckDbUrl,
  apiUploadUrl,
  apiRuanganUrl,
) {
  const STORAGE_KEY = "mazu_csv_agenda_data";
  const ROOM_STORAGE_KEY = "mazu_room_data_cache";
  const PROGRESS_KEY = "mazu_csv_check_progress";

  const uploadSection = document.getElementById("upload-section");
  const previewSection = document.getElementById("preview-section");
  if (!uploadSection || !previewSection) return;

  const uploadZone = document.getElementById("csv-upload-zone");
  const fileInput = document.getElementById("csv-file-input");
  const tbody = document.getElementById("csv-preview-tbody");
  const rowCountSpan = document.getElementById("preview-count");
  const btnReset = document.getElementById("btn-reset-data");

  const stepRuangan = document.getElementById("step-ruangan");
  const btnCheckRuangan = document.getElementById("btn-check-ruangan");
  const stepInternal = document.getElementById("step-internal");
  const btnCheckInternal = document.getElementById("btn-check-internal");
  const stepDb = document.getElementById("step-db");
  const btnCheckDb = document.getElementById("btn-check-db");
  const btnUploadAll = document.getElementById("btn-upload-all");

  const iconUpload = document.getElementById("csv-upload-icon");
  const spinnerUpload = document.getElementById("csv-upload-spinner");
  const mainTextUpload = document.getElementById("csv-upload-main-text");
  const subTextUpload = document.getElementById("csv-upload-subtext");
  const btnLabelUpload = document.getElementById("csv-upload-btn-label");

  const modalEl = document.getElementById("csvGlobalModal");
  const modalOverlay = document.getElementById("csvModalOverlay");
  const modalCloseBtn = document.getElementById("csvModalCloseBtn");
  const modalTitle = document.getElementById("csvModalTitle");
  const modalIcon = document.getElementById("csvModalIcon");
  const modalText = document.getElementById("csvModalText");
  const modalCancelBtn = document.getElementById("csvModalCancelBtn");
  const modalConfirmBtn = document.getElementById("csvModalConfirmBtn");

  // ==========================================
  // UTILS: MODAL GLOBAL CSS
  // ==========================================
  function showModal({ title, message, type = "alert", onConfirm = null }) {
    if (!modalEl) return;
    modalTitle.textContent = title;
    modalText.innerHTML = message;
    
    // Reset Kelas
    modalTitle.className = "modal-title";
    modalIcon.className = "modal-icon-wrapper";
    modalIcon.style.display = "flex";
    modalConfirmBtn.className = "btn-confirm";

    if (type === "error") {
      modalTitle.classList.add("text-danger");
      modalIcon.classList.add("danger");
      modalIcon.innerHTML = `<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`;
      modalConfirmBtn.classList.add("danger");
      modalCancelBtn.style.display = "none";
      modalConfirmBtn.textContent = "Tutup";
    } else if (type === "confirm") {
      modalIcon.classList.add("danger");
      modalIcon.innerHTML = `<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>`;
      modalConfirmBtn.classList.add("danger");
      modalCancelBtn.style.display = "inline-block";
      modalConfirmBtn.textContent = "Ya, Lanjutkan";
    } else {
      modalIcon.classList.add("success");
      modalIcon.innerHTML = `✨`;
      modalCancelBtn.style.display = "none";
      modalConfirmBtn.textContent = "OK";
      modalConfirmBtn.classList.add("success");
    }

    const closeHandler = () => {
      modalEl.classList.remove("show");
    };
    modalCancelBtn.onclick = closeHandler;
    modalCloseBtn.onclick = closeHandler;
    modalOverlay.onclick = closeHandler;
    modalConfirmBtn.onclick = () => {
      closeHandler();
      if (typeof onConfirm === "function") onConfirm();
    };
    
    modalEl.classList.add("show");
  }

  // ==========================================
  // STATE MANAGEMENT
  // ==========================================
  function getProgress() {
    return (
      JSON.parse(localStorage.getItem(PROGRESS_KEY)) || {
        roomPassed: false,
        internalPassed: false,
        dbPassed: false,
      }
    );
  }

  function setProgress(state) {
    const current = getProgress();
    const newState = { ...current, ...state };
    localStorage.setItem(PROGRESS_KEY, JSON.stringify(newState));
    updateSidebarUI(newState);
  }

  function updateSidebarUI(state = getProgress()) {
    const hasData = !!localStorage.getItem(STORAGE_KEY);

    btnCheckRuangan.className = "blk-btn-step";
    btnCheckInternal.className = "blk-btn-step";
    btnCheckDb.className = "blk-btn-step";

    if (!hasData) {
      stepRuangan.classList.add("disabled");
      btnCheckRuangan.disabled = true;
      btnCheckRuangan.textContent = "Check";
      stepInternal.classList.add("disabled");
      btnCheckInternal.disabled = true;
      btnCheckInternal.textContent = "Check";
      stepDb.classList.add("disabled");
      btnCheckDb.disabled = true;
      btnCheckDb.textContent = "Check";
      btnUploadAll.disabled = true;
      return;
    }

    stepRuangan.classList.remove("disabled");
    btnCheckRuangan.disabled = false;

    const csv = JSON.parse(localStorage.getItem(STORAGE_KEY) || "[]");

    if (state.roomPassed) {
      btnCheckRuangan.textContent = "Lolos";
      btnCheckRuangan.classList.add("success");
      stepInternal.classList.remove("disabled");
      btnCheckInternal.disabled = false;
    } else {
      if (csv.some((r) => r._roomError)) {
        btnCheckRuangan.textContent = "Perbaiki";
        btnCheckRuangan.classList.add("error");
      } else {
        btnCheckRuangan.textContent = "Check Ulang";
      }
      stepInternal.classList.add("disabled");
      btnCheckInternal.disabled = true;
      btnCheckInternal.textContent = "Check";
    }

    if (state.roomPassed && state.internalPassed) {
      btnCheckInternal.textContent = "Lolos";
      btnCheckInternal.classList.add("success");
      stepDb.classList.remove("disabled");
      btnCheckDb.disabled = false;
    } else if (state.roomPassed) {
      if (csv.some((r) => r._timeError)) {
        btnCheckInternal.textContent = "Perbaiki";
        btnCheckInternal.classList.add("error");
      } else {
        btnCheckInternal.textContent = "Check";
      }
      stepDb.classList.add("disabled");
      btnCheckDb.disabled = true;
      btnCheckDb.textContent = "Check";
    }

    if (state.roomPassed && state.internalPassed && state.dbPassed) {
      btnCheckDb.textContent = "Lolos";
      btnCheckDb.classList.add("success");
      btnUploadAll.disabled = false;
    } else if (state.roomPassed && state.internalPassed) {
      if (csv.some((r) => r._dbConflict)) {
        btnCheckDb.textContent = "Perbaiki";
        btnCheckDb.classList.add("error");
      } else {
        btnCheckDb.textContent = "Check";
      }
      btnUploadAll.disabled = true;
    } else {
      btnCheckDb.textContent = "Check";
      btnUploadAll.disabled = true;
    }
  }

  // ==========================================
  // API: CHECK RUANGAN
  // ==========================================
  async function fetchRuangan() {
    try {
      const res = await fetch(`${apiRuanganUrl}?perPage=9999`);
      const result = await res.json();
      if (result.status === "success" && result.data) {
        localStorage.setItem(ROOM_STORAGE_KEY, JSON.stringify(result.data));
        return result.data;
      }
      throw new Error("Format API tidak sesuai");
    } catch (e) {
      return JSON.parse(localStorage.getItem(ROOM_STORAGE_KEY) || "null");
    }
  }

  async function processCheckRuangan() {
    btnCheckRuangan.disabled = true;
    btnCheckRuangan.innerHTML = '<span class="spinner-mini"></span>';

    const rooms = await fetchRuangan();
    if (!rooms) {
      showModal({
        title: "Gagal",
        message: "Gagal mengambil data ruangan dari server.",
        type: "error",
      });
      btnCheckRuangan.disabled = false;
      btnCheckRuangan.textContent = "Check";
      return;
    }

    let csvData = JSON.parse(localStorage.getItem(STORAGE_KEY) || "[]");
    let errorCount = 0;

    csvData = csvData.map((row) => {
      const rId = parseInt(row.ruangan_id);
      const rName = String(row.ruangan_name || "").trim().toLowerCase();

      let matchedRoom = rooms.find((r) => parseInt(r.ID_ruangan) === rId);
      if (!matchedRoom && rName !== "") {
        matchedRoom = rooms.find((r) => String(r.name || "").trim().toLowerCase() === rName);
      }

      if (matchedRoom) {
        row.ruangan_id = matchedRoom.ID_ruangan;
        row.ruangan_name = matchedRoom.name;
        row.ruangan_capacity = matchedRoom.capacity;
        row._roomError = false;
      } else {
        row._roomError = true;
        errorCount++;
      }
      return row;
    });

    localStorage.setItem(STORAGE_KEY, JSON.stringify(csvData));
    renderTable(csvData);

    if (errorCount > 0) {
      setProgress({ roomPassed: false, internalPassed: false, dbPassed: false });
      showModal({
        title: "Ditemukan Kesalahan",
        message: `Ditemukan <strong>${errorCount}</strong> baris ruangan yang tidak valid.`,
        type: "error",
      });
    } else {
      setProgress({ roomPassed: true });
    }
  }

  if (btnCheckRuangan && !btnCheckRuangan.dataset.handled) {
    btnCheckRuangan.addEventListener("click", processCheckRuangan);
    btnCheckRuangan.dataset.handled = "true";
  }

  // ==========================================
  // LOGIKA: CHECK AGENDA (INTERNAL CSV)
  // ==========================================
  function processCheckInternal() {
    btnCheckInternal.disabled = true;
    btnCheckInternal.innerHTML = '<span class="spinner-mini"></span>';

    let csvData = JSON.parse(localStorage.getItem(STORAGE_KEY) || "[]");
    let errorCount = 0;

    csvData.forEach((row) => {
      row._timeError = false;
      row._conflictWith = [];
    });

    for (let i = 0; i < csvData.length; i++) {
      for (let j = i + 1; j < csvData.length; j++) {
        let r1 = csvData[i];
        let r2 = csvData[j];

        if (r1.ruangan_id && r1.ruangan_id === r2.ruangan_id) {
          let start1 = new Date(r1.start_time.replace(" ", "T")).getTime();
          let end1 = new Date(r1.end_time.replace(" ", "T")).getTime();
          let start2 = new Date(r2.start_time.replace(" ", "T")).getTime();
          let end2 = new Date(r2.end_time.replace(" ", "T")).getTime();

          if (start1 < end2 && end1 > start2) {
            r1._timeError = true;
            if (!r1._conflictWith.includes(j + 1)) r1._conflictWith.push(j + 1);
            r2._timeError = true;
            if (!r2._conflictWith.includes(i + 1)) r2._conflictWith.push(i + 1);
            errorCount++;
          }
        }
      }
    }

    localStorage.setItem(STORAGE_KEY, JSON.stringify(csvData));
    renderTable(csvData);

    if (errorCount > 0) {
      setProgress({ internalPassed: false, dbPassed: false });
      showModal({
        title: "Jadwal Bertabrakan",
        message: "Terdapat agenda yang jadwal dan ruangannya bentrok di dalam file CSV.",
        type: "error",
      });
    } else {
      setProgress({ internalPassed: true });
    }
  }

  if (btnCheckInternal && !btnCheckInternal.dataset.handled) {
    btnCheckInternal.addEventListener("click", processCheckInternal);
    btnCheckInternal.dataset.handled = "true";
  }

  // ==========================================
  // API: CHECK DATABASE
  // ==========================================
  async function processCheckDb() {
    btnCheckDb.disabled = true;
    btnCheckDb.innerHTML = '<span class="spinner-mini"></span>';

    let csvData = JSON.parse(localStorage.getItem(STORAGE_KEY) || "[]");

    const payload = {
      agendas: csvData.map((row) => ({
        _rowId: row._rowId,
        start_time: row.start_time,
        end_time: row.end_time,
        ruangan_id: row.ruangan_id,
      })),
    };

    try {
      const res = await fetch(apiCheckDbUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      const result = await res.json();

      if (result.status === "success") {
        let errorCount = 0;
        const conflicts = result.conflicts || {};

        csvData = csvData.map((row) => {
          if (conflicts[row._rowId]) {
            row._dbConflict = conflicts[row._rowId];
            errorCount++;
          } else {
            row._dbConflict = null;
          }
          return row;
        });

        localStorage.setItem(STORAGE_KEY, JSON.stringify(csvData));
        renderTable(csvData);

        if (errorCount > 0) {
          setProgress({ dbPassed: false });
          showModal({
            title: "Bentrok Server",
            message: `Ditemukan <strong>${errorCount}</strong> agenda yang bertabrakan dengan jadwal paten di Database.`,
            type: "error",
          });
        } else {
          setProgress({ dbPassed: true });
          showModal({
            title: "Data Lolos Uji",
            message: "Luar Biasa! Semua agenda valid dan siap untuk diunggah.",
            type: "alert",
          });
        }
      } else {
        showModal({
          title: "Error API",
          message: result.message || "Gagal memeriksa database.",
          type: "error",
        });
        setProgress({ dbPassed: false });
        updateSidebarUI();
      }
    } catch (e) {
      console.error(e);
      showModal({
        title: "Koneksi Gagal",
        message: "Gagal terhubung ke server Mazu.",
        type: "error",
      });
      setProgress({ dbPassed: false });
      updateSidebarUI();
    }
  }

  if (btnCheckDb && !btnCheckDb.dataset.handled) {
    btnCheckDb.addEventListener("click", processCheckDb);
    btnCheckDb.dataset.handled = "true";
  }

  // ==========================================
  // ACTION: UPLOAD SEMUA DATA
  // ==========================================
  if (btnUploadAll && !btnUploadAll.dataset.handled) {
    btnUploadAll.addEventListener("click", async () => {
      const state = getProgress();
      if (!state.roomPassed || !state.internalPassed || !state.dbPassed) {
        showModal({
          title: "Aksi Ditolak",
          message: "Harap selesaikan semua tahapan pemeriksaan terlebih dahulu.",
          type: "alert",
        });
        return;
      }

      btnUploadAll.disabled = true;
      const originalText = btnUploadAll.textContent;

      btnUploadAll.innerHTML = '<span class="spinner-mini" style="border-top-color: white; width: 14px; height: 14px; border-width: 2px;"></span> Mengunggah...';

      try {
        let csvData = JSON.parse(localStorage.getItem(STORAGE_KEY) || "[]");

        const cleanData = csvData.map((row) => {
          const { _rowId, _roomError, _timeError, _dbConflict, _conflictWith, ...cleanRow } = row;
          return cleanRow;
        });

        const res = await fetch(apiUploadUrl, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ agendas: cleanData }),
        });

        const result = await res.json();

        if (res.ok && result.status === "success") {
          window.dispatchEvent(new CustomEvent("swr:mutate", { detail: { key: "mazu_qw_cache" } }));
          window.dispatchEvent(new Event("swr:mutate:mazu_qw_cache"));

          if (typeof window.mazuQueueTriggerFetch === "function") {
            window.mazuQueueTriggerFetch();
          }

          localStorage.removeItem(STORAGE_KEY);
          localStorage.removeItem(PROGRESS_KEY);

          updateSidebarUI();
          showUpload();

          showModal({
            title: "Upload Berhasil!",
            message: "Data agenda berhasil dikirim dan ditambahkan ke dalam antrean sinkronisasi server.",
            type: "alert",
          });
        } else {
          throw new Error(result.message || "Gagal mengunggah data");
        }
      } catch (e) {
        console.error(e);
        showModal({
          title: "Upload Gagal",
          message: e.message || "Terjadi kesalahan koneksi. Silakan coba lagi.",
          type: "error",
        });

        btnUploadAll.disabled = false;
        btnUploadAll.textContent = originalText;
      }
    });
    btnUploadAll.dataset.handled = "true";
  }

  // ==========================================
  // DATA & FILE HANDLING
  // ==========================================
  function parseCSV(csvText) {
    const lines = csvText.split(/\r\n|\n/);
    if (lines.length < 2) return [];
    const headers = lines[0].split(",").map((h) => h.trim().replace(/^"|"$/g, ""));
    const result = [];

    for (let i = 1; i < lines.length; i++) {
      if (!lines[i].trim()) continue;
      const currentLine = lines[i].split(/,(?=(?:(?:[^"]*"){2})*[^"]*$)/);
      const obj = {};
      for (let j = 0; j < headers.length; j++) {
        let val = currentLine[j] ? currentLine[j].trim() : "";
        obj[headers[j]] = val.replace(/^"|"$/g, "");
      }
      if (currentUser) {
        obj.requester_name = currentUser.name;
        obj.requester_email = currentUser.email;
        obj.requester_role = currentUser.role;
        obj.requester_avatar = currentUser.avatar;
      }
      obj._rowId = i;
      result.push(obj);
    }
    return result;
  }

  function setUploadLoadingState(isLoading) {
    if (isLoading) {
      uploadZone.style.pointerEvents = "none";
      uploadZone.style.opacity = "0.7";
      iconUpload.style.display = "none";
      spinnerUpload.style.display = "block";
      mainTextUpload.textContent = "Memproses Data...";
      subTextUpload.style.display = "none";
      btnLabelUpload.style.display = "none";
    } else {
      uploadZone.style.pointerEvents = "auto";
      uploadZone.style.opacity = "1";
      iconUpload.style.display = "block";
      spinnerUpload.style.display = "none";
      mainTextUpload.textContent = "Seret dan lepas file CSV di sini";
      subTextUpload.style.display = "block";
      btnLabelUpload.style.display = "inline-block";
      fileInput.value = "";
    }
  }

  function processFile(file) {
    if (!file || (file.type !== "text/csv" && !file.name.endsWith(".csv"))) {
      showModal({
        title: "Format Salah",
        message: "Harap unggah file dengan format <strong>.csv</strong>",
        type: "error",
      });
      return;
    }
    setUploadLoadingState(true);
    setTimeout(() => {
      const reader = new FileReader();
      reader.onload = function (e) {
        const jsonData = parseCSV(e.target.result);
        if (jsonData.length > 0) {
          localStorage.setItem(STORAGE_KEY, JSON.stringify(jsonData));
          localStorage.removeItem(PROGRESS_KEY);
          updateSidebarUI();
          renderTable(jsonData);
          setTimeout(() => {
            setUploadLoadingState(false);
            showPreview();
          }, 500);
        } else {
          setUploadLoadingState(false);
          showModal({
            title: "File Kosong",
            message: "File CSV kosong atau header kolom tidak ditemukan.",
            type: "error",
          });
        }
      };
      reader.readAsText(file);
    }, 100);
  }

  if (fileInput && !fileInput.dataset.handled) {
    fileInput.addEventListener("change", (e) => {
      if (e.target.files.length > 0) processFile(e.target.files[0]);
    });
    fileInput.dataset.handled = "true";
  }

  if (uploadZone && !uploadZone.dataset.handled) {
    uploadZone.addEventListener("dragover", (e) => {
      e.preventDefault();
      uploadZone.classList.add("dragover");
    });
    uploadZone.addEventListener("dragleave", () => {
      uploadZone.classList.remove("dragover");
    });
    uploadZone.addEventListener("drop", (e) => {
      e.preventDefault();
      uploadZone.classList.remove("dragover");
      if (e.dataTransfer.files.length > 0) processFile(e.dataTransfer.files[0]);
    });
    uploadZone.dataset.handled = "true";
  }

  if (btnReset && !btnReset.dataset.handled) {
    btnReset.addEventListener("click", () => {
      showModal({
        title: "Konfirmasi Hapus",
        message: "Yakin menghapus data preview saat ini? Seluruh progress check juga akan direset.",
        type: "confirm",
        onConfirm: () => {
          localStorage.removeItem(STORAGE_KEY);
          localStorage.removeItem(PROGRESS_KEY);
          updateSidebarUI();
          showUpload();
        },
      });
    });
    btnReset.dataset.handled = "true";
  }

  function showUpload() {
    uploadSection.style.display = "block";
    previewSection.style.display = "none";
  }
  function showPreview() {
    uploadSection.style.display = "none";
    previewSection.style.display = "block";
  }

  function toDatetimeLocal(sqlDateStr) {
    if (!sqlDateStr) return "";
    return sqlDateStr.replace(" ", "T").slice(0, 16);
  }

  function formatDateTime(datetimeStr) {
    if (!datetimeStr) return { date: "-", time: "-" };
    const d = new Date(datetimeStr.replace(" ", "T"));
    if (isNaN(d.getTime())) return { date: datetimeStr, time: "" };
    const dateOpt = { day: "numeric", month: "short", year: "numeric" };
    const timeOpt = { hour: "2-digit", minute: "2-digit" };
    return {
      date: d.toLocaleDateString("id-ID", dateOpt),
      time: d.toLocaleTimeString("id-ID", timeOpt) + " WITA",
    };
  }

  // ==========================================
  // RENDERING TABEL & WIDGET EDIT INLINE
  // ==========================================
  function renderTable(data) {
    tbody.innerHTML = "";
    rowCountSpan.textContent = data.length;
    const roomCache = JSON.parse(localStorage.getItem(ROOM_STORAGE_KEY) || "[]");

    data.forEach((row, index) => {
      const startStr = formatDateTime(row.start_time);
      const endStr = formatDateTime(row.end_time);
      const avatarInitial = row.requester_name ? row.requester_name.charAt(0).toUpperCase() : "U";
      const avatarHtml = row.requester_avatar ? `<img src="${row.requester_avatar}" alt="Ava">` : avatarInitial;
      let roleClass = row.requester_role === "admin" ? "role-admin" : row.requester_role === "approver" ? "role-approver" : "role-user";

      let timeTdClass = row._dbConflict ? "blk-cell-error" : row._timeError ? "blk-cell-warning" : "";

      let conflictHtml = "";
      if (row._dbConflict) {
        conflictHtml = `
                    <div class="blk-error-msg danger">
                        Bentrok Database Server
                    </div>
                    <button type="button" class="btn-show-conflict blk-btn-inline danger" data-index="${index}">🔍 Lihat Detail</button>`;
      } else if (row._timeError) {
        conflictHtml = `
                    <div class="blk-error-msg warning">
                        Bentrok Baris CSV: ${row._conflictWith.join(", ")}
                    </div>`;
      }

      let timeHtml = `
                <div id="time-card-${index}">
                    <div class="blk-time-info">
                        <span class="date">${startStr.date}</span>
                        <span class="time">${startStr.time} - ${endStr.time}</span>
                        ${conflictHtml}
                    </div>
                    <button type="button" class="btn-edit-time blk-btn-inline outline" data-index="${index}">✏️ Edit Waktu</button>
                </div>
                <div class="blk-edit-container" id="time-edit-${index}" style="display: none;">
                    <div class="blk-edit-group">
                        <label>Mulai:</label>
                        <input type="datetime-local" id="time-start-${index}" class="blk-edit-input" value="${toDatetimeLocal(row.start_time)}">
                    </div>
                    <div class="blk-edit-group">
                        <label>Selesai:</label>
                        <input type="datetime-local" id="time-end-${index}" class="blk-edit-input" value="${toDatetimeLocal(row.end_time)}">
                    </div>
                    <div class="blk-edit-actions">
                        <button type="button" class="btn-save-time blk-btn-inline primary" data-index="${index}">Simpan</button>
                        <button type="button" class="btn-cancel-time blk-btn-inline outline" data-index="${index}">Batal</button>
                    </div>
                </div>
            `;

      let roomTdClass = row._roomError ? "blk-cell-error" : "";
      let optionsHtml = '<option value="">Pilih Ruangan...</option>';
      roomCache.forEach((r) => {
        const isSelected = !row._roomError && parseInt(row.ruangan_id) === parseInt(r.ID_ruangan) ? "selected" : "";
        optionsHtml += `<option value="${r.ID_ruangan}" data-name="${r.name}" data-capacity="${r.capacity}" ${isSelected}>ID ${r.ID_ruangan} - ${r.name}</option>`;
      });

      const cardContent = row._roomError
        ? `
                <span class="blk-room-name">${row.ruangan_name || `ID: ${row.ruangan_id || "-"}`}</span>
                <span class="blk-room-meta" style="text-decoration: line-through;">Kapasitas: ${row.ruangan_capacity || "?"} org</span>
                <div class="blk-error-msg danger">Tak terdaftar!</div>
                <button type="button" class="btn-fix-room blk-btn-inline danger" data-index="${index}">Perbaiki</button>
            `
        : `
                <span class="blk-room-name">${row.ruangan_name || `ID: ${row.ruangan_id}`}</span>
                <span class="blk-room-meta">Kapasitas: ${row.ruangan_capacity || "?"} org</span>
                <button type="button" class="btn-fix-room blk-btn-inline outline" data-index="${index}">✏️ Edit Ruangan</button>
            `;

      let roomHtml = `
                <div class="blk-room-card" id="room-card-${index}">
                    ${cardContent}
                </div>
                <div class="blk-edit-container" id="room-edit-${index}" style="display: none;">
                    <div class="autocomplete-container" data-placeholder="Ketik nama ruangan...">
                        <select id="select-room-${index}" class="form-select">${optionsHtml}</select>
                    </div>
                    <div class="blk-edit-actions">
                        <button type="button" class="btn-save-room blk-btn-inline primary" data-index="${index}">Simpan</button>
                        <button type="button" class="btn-cancel-room blk-btn-inline outline" data-index="${index}">Batal</button>
                    </div>
                </div>
            `;

      const tr = document.createElement("tr");
      tr.innerHTML = `
                <td>${index + 1}</td>
                <td>
                  <div class="blk-agenda-info">
                    <strong>${row.title || "Tanpa Judul"}</strong>
                    <span class="blk-agenda-desc">${row.description || "-"}</span>
                    ${row.location ? `<span class="blk-agenda-loc">📍 ${row.location}</span>` : ''}
                  </div>
                </td>
                <td class="${timeTdClass}">${timeHtml}</td>
                <td>
                  <div class="blk-req-card">
                    <div class="blk-req-avatar">${avatarHtml}</div>
                    <div class="blk-req-details">
                      <span class="blk-req-name">${row.requester_name || "Unknown"}</span>
                      <span class="blk-req-email">${row.requester_email || "-"}</span>
                      <span class="blk-req-role ${roleClass}">${row.requester_role || "user"}</span>
                    </div>
                  </div>
                </td>
                <td class="${roomTdClass}">${roomHtml}</td>
            `;
      tbody.appendChild(tr);
    });

    attachActionListeners();
  }

  function attachActionListeners() {
    document.querySelectorAll(".btn-show-conflict").forEach((btn) => {
      btn.addEventListener("click", function () {
        const idx = this.getAttribute("data-index");
        let csvData = JSON.parse(localStorage.getItem(STORAGE_KEY));
        let conflicts = csvData[idx]._dbConflict;

        if (conflicts && conflicts.length > 0) {
          let detailHtml = '<div style="text-align: left; margin-top: 10px;">';
          detailHtml +=
            '<p style="margin-bottom: 12px; font-size: 0.95rem; color: var(--text-secondary);">Agenda Anda bertabrakan dengan jadwal berikut yang sudah ada di database:</p>';
          detailHtml +=
            '<ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px;">';

          conflicts.forEach((c) => {
            const startFmt = formatDateTime(c.start_time);
            const endFmt = formatDateTime(c.end_time);
            detailHtml += `
                            <li style="background: var(--bg-default); padding: 12px; border-radius: 8px; border-left: 4px solid var(--error-main);">
                                <strong style="display: block; color: var(--text-primary); font-size: 1rem; margin-bottom: 4px;">${c.title}</strong>
                                <span style="display: block; font-size: 0.85rem; color: var(--text-secondary);">📅 ${startFmt.date}</span>
                                <span style="display: block; font-size: 0.85rem; color: var(--text-secondary);">⏰ ${startFmt.time} - ${endFmt.time}</span>
                            </li>
                        `;
          });

          detailHtml += "</ul></div>";

          showModal({
            title: "Detail Konflik Database",
            message: detailHtml,
            type: "error",
          });
        }
      });
    });

    document.querySelectorAll(".btn-fix-room").forEach((btn) => {
      btn.addEventListener("click", function () {
        const idx = this.getAttribute("data-index");
        document.getElementById(`room-card-${idx}`).style.display = "none";
        document.getElementById(`room-edit-${idx}`).style.display = "block";
        const autoContainer = document
          .getElementById(`room-edit-${idx}`)
          .querySelector(".autocomplete-container");
        if (!autoContainer.dataset.initialized) {
          new AutocompleteClass(autoContainer);
          autoContainer.dataset.initialized = "true";
        }
      });
    });

    document.querySelectorAll(".btn-cancel-room").forEach((btn) => {
      btn.addEventListener("click", function () {
        const idx = this.getAttribute("data-index");
        document.getElementById(`room-card-${idx}`).style.display = "flex";
        document.getElementById(`room-edit-${idx}`).style.display = "none";
      });
    });

    document.querySelectorAll(".btn-save-room").forEach((btn) => {
      btn.addEventListener("click", function () {
        const idx = this.getAttribute("data-index");
        const selectEl = document.getElementById(`select-room-${idx}`);
        if (!selectEl.value) {
          showModal({
            title: "Perhatian",
            message: "Pilih ruangan terlebih dahulu.",
            type: "alert",
          });
          return;
        }
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        let csvData = JSON.parse(localStorage.getItem(STORAGE_KEY));

        csvData[idx].ruangan_id = selectEl.value;
        csvData[idx].ruangan_name = selectedOption.getAttribute("data-name");
        csvData[idx].ruangan_capacity = selectedOption.getAttribute("data-capacity");
        csvData[idx]._roomError = false;
        csvData[idx]._timeError = false;
        csvData[idx]._dbConflict = null;

        setProgress({ internalPassed: false, dbPassed: false });

        localStorage.setItem(STORAGE_KEY, JSON.stringify(csvData));
        renderTable(csvData);
      });
    });

    document.querySelectorAll(".btn-edit-time").forEach((btn) => {
      btn.addEventListener("click", function () {
        const idx = this.getAttribute("data-index");
        document.getElementById(`time-card-${idx}`).style.display = "none";
        document.getElementById(`time-edit-${idx}`).style.display = "block";
      });
    });

    document.querySelectorAll(".btn-cancel-time").forEach((btn) => {
      btn.addEventListener("click", function () {
        const idx = this.getAttribute("data-index");
        document.getElementById(`time-card-${idx}`).style.display = "block";
        document.getElementById(`time-edit-${idx}`).style.display = "none";
      });
    });

    document.querySelectorAll(".btn-save-time").forEach((btn) => {
      btn.addEventListener("click", function () {
        const idx = this.getAttribute("data-index");
        const startVal = document.getElementById(`time-start-${idx}`).value;
        const endVal = document.getElementById(`time-end-${idx}`).value;

        if (!startVal || !endVal) {
          showModal({
            title: "Perhatian",
            message: "Waktu mulai dan selesai harus diisi.",
            type: "alert",
          });
          return;
        }
        if (endVal <= startVal) {
          showModal({
            title: "Waktu Tidak Valid",
            message: "Waktu selesai harus setelah waktu mulai.",
            type: "alert",
          });
          return;
        }

        let csvData = JSON.parse(localStorage.getItem(STORAGE_KEY));

        csvData[idx].start_time = startVal.replace("T", " ") + ":00";
        csvData[idx].end_time = endVal.replace("T", " ") + ":00";
        csvData[idx]._timeError = false;
        csvData[idx]._dbConflict = null;

        setProgress({ internalPassed: false, dbPassed: false });

        localStorage.setItem(STORAGE_KEY, JSON.stringify(csvData));
        renderTable(csvData);
      });
    });
  }

  function checkExistingData() {
    updateSidebarUI();
    const storedData = localStorage.getItem(STORAGE_KEY);
    if (storedData) {
      try {
        const parsedData = JSON.parse(storedData);
        if (parsedData && parsedData.length > 0) {
          renderTable(parsedData);
          showPreview();
          return;
        }
      } catch (e) {
        localStorage.removeItem(STORAGE_KEY);
      }
    }
    showUpload();
  }

  checkExistingData();
}