# Panduan Penggunaan `setup-workers.php`

Script `setup-workers.php` adalah alat bantu untuk mengelola layanan worker antrian anda di lingkungan Linux dengan `systemd`, serta membantu dalam pengembangan lokal.

## Lokasi Script

`scripts/setup-workers.php`

## Prasyarat

- **PHP CLI:** Script ini dijalankan melalui command-line PHP.
- **`systemd`:** Di lingkungan produksi (Linux), `systemd` harus tersedia dan berjalan.
- **`sudo`:** Di lingkungan produksi, anda memerlukan hak akses `sudo` untuk berinteraksi dengan `systemd`.

## Cara Menjalankan Script

### 1. Di Lingkungan Produksi (Linux/VPS)
Script ini dirancang khusus untuk server Linux yang menggunakan `systemd`.

```bash
# Setup dan jalankan worker otomatis
sudo php scripts/setup-workers.php setup all
```

### 2. Di Lingkungan Pengembangan (Local/Mac/Windows)
**JANGAN** gunakan script `setup-workers.php` di local development karena script ini mencoba memanipulasi `systemd` yang mungkin tidak ada atau berbeda di Mac/Windows.

Cukup jalankan worker secara manual di terminal terpisah:

```bash
# Jalankan worker default
php mazu queue:work default --daemon

# Atau jika ingin melihat log error langsung
php mazu queue:work default
```

_(Pastikan anda berada di root project `/Applications/MAMP/htdocs/sub-sistem-talent`)_

## Aksi yang Tersedia

Berikut adalah daftar aksi yang bisa anda gunakan:

### 1. `setup` (Default)

Membuat atau memperbarui file layanan `systemd` untuk worker, mengaktifkannya, dan memulai/memulai ulang layanan.

- **Penggunaan:**

  ```bash
  # Setup semua worker (default)
  sudo php scripts/setup-workers.php setup all
  # Atau cukup
  sudo php scripts/setup-workers.php

  # Setup worker spesifik (misal: default)
  sudo php scripts/setup-workers.php setup default
  ```

- **Catatan:** Jika file layanan sudah ada dan tidak ada perubahan konten, script akan melewatkannya.

### 2. `delete`

Menghentikan, menonaktifkan, dan menghapus file layanan `systemd` untuk worker.

- **Penggunaan:**

  ```bash
  # Menghapus semua worker
  sudo php scripts/setup-workers.php delete all

  # Menghapus worker spesifik (misal: mahasiswa_sync)
  sudo php scripts/setup-workers.php delete mahasiswa_sync
  ```

### 3. `restart`

Me-restart layanan worker yang sedang berjalan. Berguna setelah melakukan perubahan pada kode worker.

- **Penggunaan:**

  ```bash
  # Me-restart semua worker
  sudo php scripts/setup-workers.php restart all

  # Me-restart worker spesifik (misal: default)
  sudo php scripts/setup-workers.php restart default
  ```

### 4. `start`

Memulai layanan worker yang sedang berhenti.

- **Penggunaan:**

  ```bash
  # Memulai semua worker
  sudo php scripts/setup-workers.php start all

  # Memulai worker spesifik (misal: mahasiswa_sync)
  sudo php scripts/setup-workers.php start mahasiswa_sync
  ```

### 5. `stop`

Menghentikan layanan worker yang sedang berjalan.

- **Penggunaan:**

  ```bash
  # Menghentikan semua worker
  sudo php scripts/setup-workers.php stop all

  # Menghentikan worker spesifik (misal: default)
  sudo php scripts/setup-workers.php stop default
  ```

### 6. `status`

Menampilkan status layanan worker (apakah aktif, berhenti, atau gagal).

- **Penggunaan:**

  ```bash
  # Melihat status semua worker
  sudo php scripts/setup-workers.php status all

  # Melihat status worker spesifik (misal: default)
  sudo php scripts/setup-workers.php status default
  ```

### 7. `logs`

Menampilkan log utama dan log error worker secara real-time menggunakan `tail -f`.

- **Penggunaan:**
  ```bash
  # Melihat log worker spesifik (misal: mahasiswa_sync)
  sudo php scripts/setup-workers.php logs mahasiswa_sync
  ```
- **Catatan:** Tekan `Ctrl+C` untuk keluar dari tampilan log `tail -f`.

## Lingkungan Pengembangan (Mac/MAMP)

Seperti disebutkan di atas, untuk development, anda **tidak perlu** menggunakan script ini. Cukup buka terminal baru dan jalankan:

```bash
php mazu queue:work default --daemon
```

Ini akan menjalankan worker di foreground terminal anda, memudahkan debugging dan stop/start (`Ctrl+C`).

---

Dengan kedua file ini, anda memiliki alat manajemen worker yang kuat dan dokumentasi yang jelas untuk menggunakannya. Sekarang, anda bisa melanjutkan ke **Step 5: Tingkatkan Logika Worker**.# Panduan Penggunaan `setup-workers.php`

Script `setup-workers.php` adalah alat bantu untuk mengelola layanan worker antrian anda di lingkungan Linux dengan `systemd`, serta membantu dalam pengembangan lokal.

## Lokasi Script

`scripts/setup-workers.php`

## Prasyarat

- **PHP CLI:** Script ini dijalankan melalui command-line PHP.
- **`systemd`:** Di lingkungan produksi (Linux), `systemd` harus tersedia dan berjalan.
- **`sudo`:** Di lingkungan produksi, anda memerlukan hak akses `sudo` untuk berinteraksi dengan `systemd`.

## Cara Menjalankan Script

Gunakan perintah `php` diikuti dengan path ke script dan argumen yang diperlukan.

**Di Lingkungan Produksi (Linux):**

```bash
sudo /usr/bin/php /home/coadmin/sub-sistem-talent/scripts/setup-workers.php <action> [queue_name|all]
```

**Di Lingkungan Pengembangan (Mac/MAMP):**

```bash
/Applications/MAMP/bin/php/php8.4.1/bin/php /Applications/MAMP/htdocs/sub-sistem-talent/scripts/setup-workers.php <action> [queue_name|all]
```

_(Sesuaikan `/Applications/MAMP/bin/php/php8.4.1/bin/php` dengan path PHP MAMP anda yang sebenarnya.)_

## Aksi yang Tersedia

Berikut adalah daftar aksi yang bisa anda gunakan:

### 1. `setup` (Default)

Membuat atau memperbarui file layanan `systemd` untuk worker, mengaktifkannya, dan memulai/memulai ulang layanan.

- **Penggunaan:**

  ```bash
  # Setup semua worker (default)
  sudo php scripts/setup-workers.php setup all
  # Atau cukup
  sudo php scripts/setup-workers.php

  # Setup worker spesifik (misal: default)
  sudo php scripts/setup-workers.php setup default
  ```

- **Catatan:** Jika file layanan sudah ada dan tidak ada perubahan konten, script akan melewatkannya.

### 2. `delete`

Menghentikan, menonaktifkan, dan menghapus file layanan `systemd` untuk worker.

- **Penggunaan:**

  ```bash
  # Menghapus semua worker
  sudo php scripts/setup-workers.php delete all

  # Menghapus worker spesifik (misal: mahasiswa_sync)
  sudo php scripts/setup-workers.php delete mahasiswa_sync
  ```

### 3. `restart`

Me-restart layanan worker yang sedang berjalan. Berguna setelah melakukan perubahan pada kode worker.

- **Penggunaan:**

  ```bash
  # Me-restart semua worker
  sudo php scripts/setup-workers.php restart all

  # Me-restart worker spesifik (misal: default)
  sudo php scripts/setup-workers.php restart default
  ```

### 4. `start`

Memulai layanan worker yang sedang berhenti.

- **Penggunaan:**

  ```bash
  # Memulai semua worker
  sudo php scripts/setup-workers.php start all

  # Memulai worker spesifik (misal: mahasiswa_sync)
  sudo php scripts/setup-workers.php start mahasiswa_sync
  ```

### 5. `stop`

Menghentikan layanan worker yang sedang berjalan.

- **Penggunaan:**

  ```bash
  # Menghentikan semua worker
  sudo php scripts/setup-workers.php stop all

  # Menghentikan worker spesifik (misal: default)
  sudo php scripts/setup-workers.php stop default
  ```

### 6. `status`

Menampilkan status layanan worker (apakah aktif, berhenti, atau gagal).

- **Penggunaan:**

  ```bash
  # Melihat status semua worker
  sudo php scripts/setup-workers.php status all

  # Melihat status worker spesifik (misal: default)
  sudo php scripts/setup-workers.php status default
  ```

### 7. `logs`

Menampilkan log utama dan log error worker secara real-time menggunakan `tail -f`.

- **Penggunaan:**
  ```bash
  # Melihat log worker spesifik (misal: mahasiswa_sync)
  sudo php scripts/setup-workers.php logs mahasiswa_sync
  ```
- **Catatan:** Tekan `Ctrl+C` untuk keluar dari tampilan log `tail -f`.

## Lingkungan Pengembangan (Mac/MAMP)

Di lingkungan pengembangan, script ini akan membuat file `.service` di direktori `.systemd-services-dev` di root proyek anda untuk tujuan review. Namun, perintah `systemctl` tidak akan dijalankan. Anda harus menjalankan worker secara manual di terminal seperti yang diinstruksikan oleh script.

---

Dengan kedua file ini, anda memiliki alat manajemen worker yang kuat dan dokumentasi yang jelas untuk menggunakannya. Sekarang, anda bisa melanjutkan ke **Step 5: Tingkatkan Logika Worker**.
