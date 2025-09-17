<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Marketplace Installer - Step 1</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Welcome to the Marketplace Installer</h1>
        <p>This wizard will guide you through the installation process. First, let's check the server requirements.</p>

        <h2>Server Requirements</h2>
        <table class="requirements-table">
            <thead>
                <tr>
                    <th>Requirement</th>
                    <th>Current</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requirements as $req): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($req['name']); ?></td>
                        <td><?php echo htmlspecialchars($req['current']); ?></td>
                        <td class="<?php echo $req['check'] ? 'status-ok' : 'status-fail'; ?>">
                            <?php echo $req['check'] ? 'OK' : 'FAIL'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($all_ok): ?>
            <p class="status-ok">Congratulations! Your server meets all the requirements.</p>
            <a href="index.php?step=2" class="btn">Next Step</a>
        <?php else: ?>
            <p class="status-fail">Your server does not meet all the requirements. Please resolve the issues above before proceeding.</p>
            <a href="#" class="btn btn-disabled" onclick="return false;">Next Step</a>
        <?php endif; ?>
    </div>
</body>
</html>
