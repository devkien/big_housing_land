<?php
// Reusable alert partial - reads common session flash keys and renders unified alerts
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$flashKeys = ['success', 'error', 'warning', 'info'];
$icons = [
    'success' => 'fa-check-circle',
    'error' => 'fa-exclamation-triangle',
    'warning' => 'fa-exclamation-circle',
    'info' => 'fa-info-circle',
];

$hasAny = false;
foreach ($flashKeys as $k) {
    if (!empty($_SESSION[$k])) {
        $hasAny = true;
        break;
    }
}

if ($hasAny):
    foreach ($flashKeys as $type):
        if (!empty($_SESSION[$type])):
            $message = $_SESSION[$type];
            unset($_SESSION[$type]);
?>
            <div class="alert-wrapper">
                <div class="alert alert--<?= htmlspecialchars($type) ?>" role="alert" aria-live="polite">
                    <div class="alert-inner">
                        <i class="fa <?= $icons[$type] ?? 'fa-info-circle' ?> alert-icon" aria-hidden="true"></i>
                        <div class="alert-message"><?= htmlspecialchars($message) ?></div>
                        <button type="button" class="alert-close" aria-label="Đóng thông báo" onclick="this.closest('.alert-wrapper').style.display='none'">&times;</button>
                    </div>
                </div>
            </div>
<?php
        endif;
    endforeach;
endif;

?>