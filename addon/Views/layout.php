<!DOCTYPE html>
<html lang="id">

<head>
  <?= App\Core\View\View::renderMeta($meta) ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <?= App\Core\View\View::renderStyles() ?>
</head>

<body>
  <!-- Global Loading Progress Bar -->
  <div id="global-progress-bar" class="progress-bar-container">
    <div id="global-progress-bar-inner" class="progress-bar-fill"></div>
  </div>

  <!-- Content Injection -->
  <div id="app-content" data-layout="layout.php">
    <?= $children; ?>
  </div>

  <!-- SPA Script -->
  <?= App\Core\View\View::renderScripts() ?>
</body>

</html>