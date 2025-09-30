<?php require_once APP_ROOT . '/app/views/includes/header.php'; ?>

<div class="admin-container">
    <h2><?php echo $data['title']; ?></h2>
    <p><?php echo $data['description']; ?></p>

    <?php flash('content_success'); ?>
    <?php flash('content_error'); ?>

    <form action="<?php echo BASE_URL; ?>/admin/landingPage" method="post" class="settings-form">

        <h3>Hero Section</h3>
        <div class="form-group">
            <label for="hero_title">Main Title</label>
            <input type="text" name="hero_title" value="<?php echo htmlspecialchars($data['content']['hero_title'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="hero_subtitle">Subtitle</label>
            <textarea name="hero_subtitle" rows="3"><?php echo htmlspecialchars($data['content']['hero_subtitle'] ?? ''); ?></textarea>
        </div>

        <h3>Services Section</h3>
        <div class="form-group">
            <label for="services_title">Section Title</label>
            <input type="text" name="services_title" value="<?php echo htmlspecialchars($data['content']['services_title'] ?? ''); ?>">
        </div>
         <div class="form-group">
            <label for="services_subtitle">Section Subtitle</label>
            <input type="text" name="services_subtitle" value="<?php echo htmlspecialchars($data['content']['services_subtitle'] ?? ''); ?>">
        </div>

        <h3>'Why Us' Section</h3>
        <div class="form-group">
            <label for="why_us_title">Section Title</label>
            <input type="text" name="why_us_title" value="<?php echo htmlspecialchars($data['content']['why_us_title'] ?? ''); ?>">
        </div>
        <div class="form-row">
            <div class="col">
                <div class="form-group">
                    <label for="why_us_item1_title">Item 1 Title</label>
                    <input type="text" name="why_us_item1_title" value="<?php echo htmlspecialchars($data['content']['why_us_item1_title'] ?? ''); ?>">
                </div>
                 <div class="form-group">
                    <label for="why_us_item1_text">Item 1 Text</label>
                    <input type="text" name="why_us_item1_text" value="<?php echo htmlspecialchars($data['content']['why_us_item1_text'] ?? ''); ?>">
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label for="why_us_item2_title">Item 2 Title</label>
                    <input type="text" name="why_us_item2_title" value="<?php echo htmlspecialchars($data['content']['why_us_item2_title'] ?? ''); ?>">
                </div>
                 <div class="form-group">
                    <label for="why_us_item2_text">Item 2 Text</label>
                    <input type="text" name="why_us_item2_text" value="<?php echo htmlspecialchars($data['content']['why_us_item2_text'] ?? ''); ?>">
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label for="why_us_item3_title">Item 3 Title</label>
                    <input type="text" name="why_us_item3_title" value="<?php echo htmlspecialchars($data['content']['why_us_item3_title'] ?? ''); ?>">
                </div>
                 <div class="form-group">
                    <label for="why_us_item3_text">Item 3 Text</label>
                    <input type="text" name="why_us_item3_text" value="<?php echo htmlspecialchars($data['content']['why_us_item3_text'] ?? ''); ?>">
                </div>
            </div>
        </div>


        <button type="submit" class="btn btn-success">Save Content</button>
    </form>
</div>

<style>
    /* Most styles inherited from settings page */
    .admin-container { width: 90%; max-width: 900px; margin: 20px auto; background: #fff; padding: 2rem; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .settings-form h3 { margin-top: 2rem; margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; }
    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-group input, .form-group textarea { width: 100%; padding: .5rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
    .btn { display: inline-block; font-weight: 400; padding: .5rem 1rem; font-size: 1rem; border-radius: .25rem; text-decoration: none; cursor: pointer; border: 1px solid transparent; }
    .btn-success { color: #fff; background-color: #28a745; border-color: #28a745; }
</style>

<?php require_once APP_ROOT . '/app/views/includes/footer.php'; ?>