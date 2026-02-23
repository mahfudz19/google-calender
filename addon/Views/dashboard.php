<?php
/**
 * Dashboard view default untuk Mazu Google Auth scaffold.
 * Variabel tersedia:
 * - $user: array data user dari session
 * - $role: string role user
 */
?>
<div class="mazu-container">
  <section class="mazu-hero">
    <h1 class="mazu-title">Dashboard</h1>
    <p class="mazu-subtitle">
      Anda berhasil login dengan Google. Berikut informasi akun Anda.
    </p>
  </section>

  <div class="mazu-grid">
    <div class="mazu-card">
      <div class="mazu-card-icon">
        <i class="bi bi-person-circle"></i>
      </div>
      <h3 class="mazu-card-title">Profil Pengguna</h3>
      <p class="mazu-card-desc">
        <strong>Nama:</strong> <?= htmlspecialchars($user['name'] ?? 'Guest', ENT_QUOTES, 'UTF-8') ?><br>
        <strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? '-', ENT_QUOTES, 'UTF-8') ?><br>
        <strong>Role:</strong> <?= htmlspecialchars($role ?? 'GUEST', ENT_QUOTES, 'UTF-8') ?>
      </p>
    </div>
  </div>
</div>