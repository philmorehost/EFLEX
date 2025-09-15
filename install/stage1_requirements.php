<?php
// Stage 1: Requirements Check

$pageTitle = "Stage 1: Server Requirements";

$requirements = [
    'mysqli' => [
        'name' => 'MySQLi Extension',
        'required' => true,
        'status' => extension_loaded('mysqli'),
        'help' => 'The MySQLi extension is required for database communication. Please enable it in your php.ini file.'
    ],
    'session' => [
        'name' => 'Session Support',
        'required' => true,
        'status' => function_exists('session_start'),
        'help' => 'Session support is required for user authentication and installer state management.'
    ]
];

$all_ok = true;
foreach ($requirements as $req) {
    if ($req['required'] && !$req['status']) {
        $all_ok = false;
    }
}

?>

<h1>Stage 1: Server Requirements</h1>
<p>This first step will check if your server environment meets the minimum requirements to run the CBT Platform.</p>
<hr>

<ul>
    <?php foreach ($requirements as $req): ?>
        <li class="<?php echo $req['status'] ? 'pass' : 'fail'; ?>">
            <strong><?php echo $req['name']; ?>:</strong>
            <?php echo $req['status'] ? 'OK' : 'Not Found'; ?>
            <?php if (!$req['status'] && $req['required']): ?>
                <p style="margin: 5px 0 0; font-size: 0.9em;"><?php echo $req['help']; ?></p>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>

<div style="margin-top: 20px; text-align: right;">
    <?php if ($all_ok): ?>
        <a href="index.php?action=next_stage" class="btn">Next Step &raquo;</a>
    <?php else: ?>
        <p class="alert alert-danger">One or more critical requirements are not met. Please fix the issues and try again.</p>
        <a href="index.php?action=reset" class="btn" style="background-color: #6c757d;">Retry Check</a>
    <?php endif; ?>
</div>
