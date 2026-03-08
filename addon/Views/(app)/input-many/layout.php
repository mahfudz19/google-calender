<div class="input-many-layout-wrapper">
  <aside class="input-many-sidebar">
    <link rel="stylesheet" href="<?= getBaseUrl('/components-js/queue-widget/style.css') ?>">
    <div id="queue-widget" class="queue-widget"></div>
    <script>
      window.SWR_CONFIG = {
        interval: 10000,
        cacheKey: 'mazu_qw_cache',
        apiEndpoint: '<?= getBaseUrl('/queue') ?>',
        getBaseUrl: '<?= getBaseUrl() ?>',
      };
    </script>
    <script src="<?= getBaseUrl('/components-js/queue-widget/index.js') ?>"></script>
  </aside>

  <div class="input-many-main" data-layout="addon/Views/(app)/input-many/layout.php">
    <?= $children ?>
  </div>

</div>