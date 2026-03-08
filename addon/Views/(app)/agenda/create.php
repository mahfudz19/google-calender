<div class="agenda-container">
  <a data-spa href="<?= getBaseUrl('/agenda') ?>" class="btn-back">← Kembali ke Daftar</a>

  <div class="form-card-wrapper">
    <div class="form-header">
      <h2>Buat Agenda Baru</h2>
      <p>Isi formulir di bawah ini untuk mengajukan jadwal ke sistem <?= env('APP_NAME') ?>.</p>
    </div>

    <?php
    $action = getBaseUrl('/agenda/store');

    include __DIR__ . '/form-agenda.php';
    ?>
  </div>
</div>