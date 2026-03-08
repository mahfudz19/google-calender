<?php

/**
 * Script Manager Worker Systemd
 * Menghasilkan dan mengelola file service systemd untuk worker antrian Mazu.
 *
 * Usage: sudo php scripts/setup-workers.php [action] [queue_name]
 */

if (php_sapi_name() !== 'cli') {
  exit("Script ini hanya dapat dijalankan melalui CLI.\n");
}

// Konfigurasi
$projectRoot = realpath(__DIR__ . '/..');
$mazuPath = $projectRoot . '/mazu';
$phpPath = PHP_BINARY;

// Default queue yang digunakan aplikasi saat ini
// Code di FeederController mendispatch ke 'default', jadi kita hanya butuh ini.
$queues = ['default'];

// Helper untuk warna output
function color($text, $color)
{
  $colors = [
    'green' => "\033[32m",
    'red' => "\033[31m",
    'yellow' => "\033[33m",
    'reset' => "\033[0m",
  ];
  return ($colors[$color] ?? '') . $text . $colors['reset'];
}

// Cek root privilege (setup service butuh root)
$isRoot = posix_geteuid() === 0;

// Ambil argumen
$action = $argv[1] ?? 'help';
$targetQueue = $argv[2] ?? 'all';

// Validasi queue (jika user ingin menambah queue lain manual, kita perbolehkan)
if ($targetQueue !== 'all' && !in_array($targetQueue, $queues)) {
  // Warning saja, siapa tahu user memang menambah queue baru di kodingan
  echo color("Info: Queue '{$targetQueue}' tidak ada di list default (" . implode(', ', $queues) . "). Tetap memproses...\n", 'yellow');
  $queues[] = $targetQueue;
}

$selectedQueues = ($targetQueue === 'all') ? $queues : [$targetQueue];

// Detect User & Group (menggunakan pemilik file mazu agar permission sesuai)
$fileOwnerUid = fileowner($mazuPath);
$fileOwnerGid = filegroup($mazuPath);
$userInfo = posix_getpwuid($fileOwnerUid);
$groupInfo = posix_getgrgid($fileOwnerGid);
$serviceUser = $userInfo['name'];
$serviceGroup = $groupInfo['name'];

if ($action !== 'help') {
  echo color("Mazu Worker Manager\n", 'green');
  echo "Project Root: {$projectRoot}\n";
  echo "Service User: {$serviceUser}:{$serviceGroup}\n";
  echo "Action: " . color(strtoupper($action), 'yellow') . "\n\n";
}

$projectName = basename($projectRoot);
switch ($action) {
  case 'setup':
    if (!$isRoot) exit(color("Error: Aksi 'setup' membutuhkan akses root (sudo).\n", 'red'));

    foreach ($selectedQueues as $queue) {
      $serviceName = "mazu-worker-{$projectName}-{$queue}";
      $serviceFile = "/etc/systemd/system/{$serviceName}.service";

      echo "Konfigurasi {$serviceName}...\n";

      // Kita tambahkan timeout=0 dan memory limit yang cukup untuk worker
      $content = <<<EOT
[Unit]
Description=Mazu Queue Worker: {$queue}
After=network.target mysql.service redis-server.service

[Service]
User={$serviceUser}
Group={$serviceGroup}
WorkingDirectory={$projectRoot}
# Menjalankan queue:work untuk antrian spesifik
ExecStart={$phpPath} {$mazuPath} queue:work {$queue} --daemon
Restart=always
RestartSec=5
StartLimitInterval=0

[Install]
WantedBy=multi-user.target
EOT;

      file_put_contents($serviceFile, $content);
      echo "  - File service dibuat: {$serviceFile}\n";

      system("systemctl enable {$serviceName}");
      system("systemctl start {$serviceName}");
      echo "  - Service di-enable dan di-start.\n";
    }

    system("systemctl daemon-reload");
    echo color("\nSetup selesai! Worker berjalan di background.\n", 'green');
    break;

  case 'delete':
    if (!$isRoot) exit(color("Error: Aksi 'delete' membutuhkan akses root (sudo).\n", 'red'));

    foreach ($selectedQueues as $queue) {
      $serviceName = "mazu-worker-{$projectName}-{$queue}";
      $serviceFile = "/etc/systemd/system/{$serviceName}.service";

      echo "Menghapus {$serviceName}...\n";

      system("systemctl stop {$serviceName}");
      system("systemctl disable {$serviceName}");

      if (file_exists($serviceFile)) {
        unlink($serviceFile);
        echo "  - File service dihapus.\n";
      }
    }

    system("systemctl daemon-reload");
    echo color("\nDelete selesai.\n", 'green');
    break;

  case 'start':
  case 'stop':
  case 'restart':
    if (!$isRoot) exit(color("Error: Aksi '{$action}' membutuhkan akses root (sudo).\n", 'red'));

    foreach ($selectedQueues as $queue) {
      $serviceName = "mazu-worker-{$projectName}-{$queue}";
      echo ucfirst($action) . "ing {$serviceName}...\n";
      system("systemctl {$action} {$serviceName}");
    }
    echo color("\nSelesai.\n", 'green');
    break;

  case 'status':
    foreach ($selectedQueues as $queue) {
      $serviceName = "mazu-worker-{$projectName}-{$queue}";
      echo color("=== Status {$serviceName} ===\n", 'yellow');
      system("systemctl status {$serviceName} --no-pager");
      echo "\n";
    }
    break;

  case 'logs':
    if ($targetQueue === 'all') {
      // Jika all, kita ambil yang pertama saja atau default
      $targetQueue = 'default';
    }
    $serviceName = "mazu-worker-{$projectName}-{$targetQueue}";
    echo color("Menampilkan log untuk {$serviceName} (Ctrl+C untuk keluar)...\n", 'green');
    system("journalctl -u {$serviceName} -f");
    break;

  default:
    echo color("Usage: sudo php scripts/setup-workers.php [action] [queue_name]\n", 'yellow');
    echo "Actions:\n";
    echo "  setup   : Create and start systemd services\n";
    echo "  delete  : Stop and remove systemd services\n";
    echo "  start   : Start services\n";
    echo "  stop    : Stop services\n";
    echo "  restart : Restart services\n";
    echo "  status  : Show status\n";
    echo "  logs    : Show logs (tail -f)\n";
    break;
}
