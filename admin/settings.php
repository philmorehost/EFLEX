<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();
protect_admin_page();

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and update settings
    $settings_to_update = [
        'site_title', 'site_description',
        'pwa_name', 'pwa_short_name', 'pwa_theme_color', 'pwa_background_color',
        'landing_headline', 'landing_subheadline'
    ];

    foreach ($settings_to_update as $key) {
        if (isset($_POST[$key])) {
            update_setting($key, trim($_POST[$key]));
        }
    }
    $success = "Settings have been updated successfully!";
}


// Fetch current settings to populate the form
$settings = [
    'site_title' => get_setting('site_title'),
    'site_description' => get_setting('site_description'),
    'pwa_name' => get_setting('pwa_name'),
    'pwa_short_name' => get_setting('pwa_short_name'),
    'pwa_theme_color' => get_setting('pwa_theme_color'),
    'pwa_background_color' => get_setting('pwa_background_color'),
    'landing_headline' => get_setting('landing_headline'),
    'landing_subheadline' => get_setting('landing_subheadline'),
];


require_once 'partials/admin_header.php';
?>

<h1 class="h3 mb-4 text-gray-800">Site Settings</h1>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<form method="POST">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">SEO & General Settings</h6>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="site_title" class="form-label">Site Title</label>
                <input type="text" class="form-control" id="site_title" name="site_title" value="<?php echo htmlspecialchars($settings['site_title']); ?>">
            </div>
            <div class="mb-3">
                <label for="site_description" class="form-label">Site Meta Description</label>
                <textarea class="form-control" id="site_description" name="site_description" rows="3"><?php echo htmlspecialchars($settings['site_description']); ?></textarea>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">PWA (Progressive Web App) Settings</h6>
        </div>
        <div class="card-body">
             <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="pwa_name" class="form-label">App Name</label>
                    <input type="text" class="form-control" id="pwa_name" name="pwa_name" value="<?php echo htmlspecialchars($settings['pwa_name']); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="pwa_short_name" class="form-label">App Short Name</label>
                    <input type="text" class="form-control" id="pwa_short_name" name="pwa_short_name" value="<?php echo htmlspecialchars($settings['pwa_short_name']); ?>">
                </div>
            </div>
             <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="pwa_theme_color" class="form-label">Theme Color</label>
                    <input type="color" class="form-control form-control-color" id="pwa_theme_color" name="pwa_theme_color" value="<?php echo htmlspecialchars($settings['pwa_theme_color'] ?? '#ffffff'); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="pwa_background_color" class="form-label">Background Color</label>
                    <input type="color" class="form-control form-control-color" id="pwa_background_color" name="p_background_color" value="<?php echo htmlspecialchars($settings['pwa_background_color'] ?? '#ffffff'); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Landing Page Content</h6>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="landing_headline" class="form-label">Main Headline</label>
                <input type="text" class="form-control" id="landing_headline" name="landing_headline" value="<?php echo htmlspecialchars($settings['landing_headline']); ?>">
            </div>
            <div class="mb-3">
                <label for="landing_subheadline" class="form-label">Sub-headline</label>
                <textarea class="form-control" id="landing_subheadline" name="landing_subheadline" rows="3"><?php echo htmlspecialchars($settings['landing_subheadline']); ?></textarea>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary mb-4">Save All Settings</button>
</form>

<?php
require_once 'partials/admin_footer.php';
?>