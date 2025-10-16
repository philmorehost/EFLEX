<?php
$pageTitle = "Manage Landing Page";
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';

// Handle form submission
if (isset($_POST['save_changes'])) {
    // Update text content
    $stmt = $conn->prepare("UPDATE landing_page_content SET content = ? WHERE section_name = ?");
    $stmt->bind_param("ss", $content_value, $section_name);

    $sections = ['hero_title', 'hero_subtitle', 'testimonial_1_name', 'testimonial_1_school', 'testimonial_1_quote', 'testimonial_2_name', 'testimonial_2_school', 'testimonial_2_quote', 'testimonial_3_name', 'testimonial_3_school', 'testimonial_3_quote'];
    foreach ($sections as $section) {
        $content_value = $_POST[$section];
        $section_name = $section;
        $stmt->execute();
    }
    $stmt->close();

    // Handle image uploads
    for ($i = 1; $i <= 3; $i++) {
        if (isset($_FILES["testimonial_{$i}_image"]) && $_FILES["testimonial_{$i}_image"]["error"] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($_FILES["testimonial_{$i}_image"]["type"], $allowed_types) && $_FILES["testimonial_{$i}_image"]["size"] < 2000000) {
                $target_dir = __DIR__ . "/../assets/img/";
                $target_file = $target_dir . basename($_FILES["testimonial_{$i}_image"]["name"]);
                move_uploaded_file($_FILES["testimonial_{$i}_image"]["tmp_name"], $target_file);

                $db_path = "assets/img/" . basename($_FILES["testimonial_{$i}_image"]["name"]);
                $stmt = $conn->prepare("UPDATE landing_page_content SET content = ? WHERE section_name = ?");
                $section_name = "testimonial_{$i}_image";
                $stmt->bind_param("ss", $db_path, $section_name);
                $stmt->execute();
                $stmt->close();
            } else {
                echo '<div class="alert alert-danger">Invalid file type or size for Testimonial ' . $i . ' image.</div>';
            }
        }
    }

    echo '<div class="alert alert-success">Content updated successfully!</div>';
}

// Fetch current content
$content_sql = "SELECT section_name, content FROM landing_page_content";
$content_result = $conn->query($content_sql);
$content = [];
while ($row = $content_result->fetch_assoc()) {
    $content[$row['section_name']] = $row['content'];
}
?>

<div class="container mt-5">
    <h2>Manage Landing Page Content</h2>
    <hr>

    <form action="manage_landing_page.php" method="POST" enctype="multipart/form-data">
        <!-- Hero Section -->
        <h4>Hero Section</h4>
        <div class="mb-3">
            <label for="hero_title" class="form-label">Hero Title</label>
            <input type="text" class="form-control" id="hero_title" name="hero_title" value="<?php echo htmlspecialchars($content['hero_title']); ?>">
        </div>
        <div class="mb-3">
            <label for="hero_subtitle" class="form-label">Hero Subtitle</label>
            <textarea class="form-control" id="hero_subtitle" name="hero_subtitle" rows="3"><?php echo htmlspecialchars($content['hero_subtitle']); ?></textarea>
        </div>
        <hr>

        <!-- Testimonials -->
        <h4>Testimonials</h4>
        <!-- Testimonial 1 -->
        <h5>Testimonial 1</h5>
        <div class="mb-3">
            <label for="testimonial_1_name" class="form-label">Name</label>
            <input type="text" class="form-control" id="testimonial_1_name" name="testimonial_1_name" value="<?php echo htmlspecialchars($content['testimonial_1_name']); ?>">
        </div>
        <div class="mb-3">
            <label for="testimonial_1_school" class="form-label">School</label>
            <input type="text" class="form-control" id="testimonial_1_school" name="testimonial_1_school" value="<?php echo htmlspecialchars($content['testimonial_1_school']); ?>">
        </div>
        <div class="mb-3">
            <label for="testimonial_1_quote" class="form-label">Quote</label>
            <textarea class="form-control" id="testimonial_1_quote" name="testimonial_1_quote" rows="3"><?php echo htmlspecialchars($content['testimonial_1_quote']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="testimonial_1_image" class="form-label">Image</label>
            <input type="file" class="form-control" id="testimonial_1_image" name="testimonial_1_image">
            <small class="form-text">Current: <img src="../<?php echo htmlspecialchars($content['testimonial_1_image']); ?>" width="50"></small>
        </div>
        <hr>

        <!-- Testimonial 2 -->
        <h5>Testimonial 2</h5>
        <div class="mb-3">
            <label for="testimonial_2_name" class="form-label">Name</label>
            <input type="text" class="form-control" id="testimonial_2_name" name="testimonial_2_name" value="<?php echo htmlspecialchars($content['testimonial_2_name']); ?>">
        </div>
        <div class="mb-3">
            <label for="testimonial_2_school" class="form-label">School</label>
            <input type="text" class="form-control" id="testimonial_2_school" name="testimonial_2_school" value="<?php echo htmlspecialchars($content['testimonial_2_school']); ?>">
        </div>
        <div class="mb-3">
            <label for="testimonial_2_quote" class="form-label">Quote</label>
            <textarea class="form-control" id="testimonial_2_quote" name="testimonial_2_quote" rows="3"><?php echo htmlspecialchars($content['testimonial_2_quote']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="testimonial_2_image" class="form-label">Image</label>
            <input type="file" class="form-control" id="testimonial_2_image" name="testimonial_2_image">
            <small class="form-text">Current: <img src="../<?php echo htmlspecialchars($content['testimonial_2_image']); ?>" width="50"></small>
        </div>
        <hr>

        <!-- Testimonial 3 -->
        <h5>Testimonial 3</h5>
        <div class="mb-3">
            <label for="testimonial_3_name" class="form-label">Name</label>
            <input type="text" class="form-control" id="testimonial_3_name" name="testimonial_3_name" value="<?php echo htmlspecialchars($content['testimonial_3_name']); ?>">
        </div>
        <div class="mb-3">
            <label for="testimonial_3_school" class="form-label">School</label>
            <input type="text" class="form-control" id="testimonial_3_school" name="testimonial_3_school" value="<?php echo htmlspecialchars($content['testimonial_3_school']); ?>">
        </div>
        <div class="mb-3">
            <label for="testimonial_3_quote" class="form-label">Quote</label>
            <textarea class="form-control" id="testimonial_3_quote" name="testimonial_3_quote" rows="3"><?php echo htmlspecialchars($content['testimonial_3_quote']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="testimonial_3_image" class="form-label">Image</label>
            <input type="file" class="form-control" id="testimonial_3_image" name="testimonial_3_image">
            <small class="form-text">Current: <img src="../<?php echo htmlspecialchars($content['testimonial_3_image']); ?>" width="50"></small>
        </div>
        <hr>

        <button type="submit" name="save_changes" class="btn btn-primary">Save Changes</button>
    </form>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
