<?php
// The admin header contains all necessary includes and session handling.
require_once 'partials/admin_header.php';

$errors = [];
$success = null;

// Handle the form submission to update settings.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // A list of all setting keys from the form.
    $settings_to_update = [
        'site_title', 'site_description',
        'pwa_name', 'pwa_short_name', 'pwa_theme_color', 'pwa_background_color',
        'landing_headline', 'landing_subheadline'
    ];

    // Loop through the settings and update them in the database.
    foreach ($settings_to_update as $key) {
        if (isset($_POST[$key])) {
            update_setting($key, trim($_POST[$key]));
        }
    }
    $success = "Settings have been updated successfully!";
}

// Fetch all current settings from the database to populate the form fields.
$settings = [];
$all_settings_keys = [
    'site_title', 'site_description', 'pwa_name', 'pwa_short_name',
    'pwa_theme_color', 'pwa_background_color', 'landing_headline', 'landing_subheadline'
];
foreach ($all_settings_keys as $key) {
    $settings[$key] = get_setting($key);
}
?>

<h1 class="h3 mb-4 text-gray-800">Site Settings</h1>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<form method="POST">
    <!-- SEO Settings Card -->
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

    <!-- PWA Settings Card -->
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
                    <input type="color" class="form-control form-control-color" id="pwa_background_color" name="pwa_background_color" value="<?php echo htmlspecialchars($settings['pwa_background_color'] ?? '#ffffff'); ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Landing Page Settings Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Landing Page Content</h6>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="landing_headline" class="form-label">Main Headline</label>
                <input type="text" class="form-control" id="landing_headline" name="landing_headline" value="<?php echo htmlspecialchars($settings['landing_headline']); ?>">
                <small>This appears on the homepage banner.</small>
            </div>
            <div class="mb-3">
                <label for="landing_subheadline" class="form-label">Sub-headline</label>
                <textarea class="form-control" id="landing_subheadline" name="landing_subheadline" rows="3"><?php echo htmlspecialchars($settings['landing_subheadline']); ?></textarea>
                 <small>This appears under the main headline on the homepage.</small>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary mb-4">Save All Settings</button>
</form>

<?php
require_once 'partials/admin_footer.php';
?>