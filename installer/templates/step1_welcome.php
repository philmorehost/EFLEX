<h2>Step 1: Server Requirements Check</h2>
<p>Welcome to the installer. This first step will check if your server environment meets the minimum requirements for the application to run correctly.</p>

<?php
$min_php_version = '7.4';
$php_version_ok = version_compare(PHP_VERSION, $min_php_version, '>=');

$required_extensions = [
    'pdo_mysql' => 'for database interaction',
    'curl' => 'for API communications',
    'json' => 'for handling JSON data',
    'mbstring' => 'for multibyte string operations',
];

$extensions_ok = true;
$check_results = [];

foreach ($required_extensions as $ext => $reason) {
    $is_loaded = extension_loaded($ext);
    if (!$is_loaded) {
        $extensions_ok = false;
    }
    $check_results[$ext] = [
        'loaded' => $is_loaded,
        'reason' => $reason
    ];
}

$all_ok = $php_version_ok && $extensions_ok;
?>

<h4>PHP Version</h4>
<ul>
    <li>
        Required: PHP >= <?php echo $min_php_version; ?> |
        Current: <span class="badge <?php echo $php_version_ok ? 'badge-success' : 'badge-error'; ?>">
            <?php echo PHP_VERSION; ?>
        </span>
    </li>
</ul>

<h4>PHP Extensions</h4>
<ul>
    <?php foreach ($check_results as $ext => $result): ?>
    <li>
        <strong><?php echo $ext; ?></strong> (<?php echo $result['reason']; ?>):
        <span class="badge <?php echo $result['loaded'] ? 'badge-success' : 'badge-error'; ?>">
            <?php echo $result['loaded'] ? 'Installed' : 'Missing'; ?>
        </span>
    </li>
    <?php endforeach; ?>
</ul>

<div class="installer-footer">
    <?php if ($all_ok): ?>
        <a href="index.php?step=2" class="btn">Next Step</a>
    <?php else: ?>
        <p class="error">Please fix the server requirement errors before proceeding.</p>
        <button class="btn" disabled>Next Step</button>
    <?php endif; ?>
</div>