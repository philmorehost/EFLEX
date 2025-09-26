<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installer - Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h1>Welcome to the Installer</h1>
            </div>
            <div class="card-body">
                <p>This wizard will guide you through the installation of the script. Please ensure you have your database details ready.</p>

                <h2 class="mt-4">Server Requirements Check</h2>
                <ul class="list-group">
                    <?php
                    $php_version_required = '7.4';
                    $php_version_ok = version_compare(PHP_VERSION, $php_version_required, '>=');
                    ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        PHP Version >= <?php echo $php_version_required; ?>
                        <span class="badge bg-<?php echo $php_version_ok ? 'success' : 'danger'; ?>">
                            <?php echo $php_version_ok ? 'OK ('.PHP_VERSION.')' : 'Required: >= '.$php_version_required; ?>
                        </span>
                    </li>

                    <?php
                    $extensions = ['mysqli', 'session', 'json'];
                    $all_extensions_ok = true;
                    foreach ($extensions as $extension) {
                        $is_loaded = extension_loaded($extension);
                        if (!$is_loaded) {
                            $all_extensions_ok = false;
                        }
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo ucfirst($extension); ?> Extension
                            <span class="badge bg-<?php echo $is_loaded ? 'success' : 'danger'; ?>">
                                <?php echo $is_loaded ? 'OK' : 'Not Found'; ?>
                            </span>
                        </li>
                        <?php
                    }
                    ?>
                </ul>

                <div class="mt-4">
                    <?php if ($php_version_ok && $all_extensions_ok): ?>
                        <a href="index.php?stage=database" class="btn btn-primary">Start Installation</a>
                    <?php else: ?>
                        <div class="alert alert-danger">Your server does not meet the minimum requirements. Please fix the issues above before proceeding.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>