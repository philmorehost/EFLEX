<?php
// Initialize session and connect to DB. This must be at the top.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db_connect.php';
require_once '../includes/helpers.php';

// Check if the user is logged in and is an admin.
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin'){
    header("location: ../index.php");
    exit;
}

// Define variables and initialize
$name = $description = $price = $category_id = "";
$google_drive_file_ids = ""; // Changed from folder_id
$is_featured = $is_top_seller = 0;
$name_err = $description_err = $price_err = $category_id_err = $image_err = "";
$message = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate form fields
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price = trim($_POST["price"]);
    $category_id = $_POST["category_id"];
    // Changed from google_drive_folder_id to google_drive_file_ids
    $google_drive_file_ids = trim($_POST['google_drive_file_ids']);
    $duration_days = (int)$_POST['duration_days'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_top_seller = isset($_POST['is_top_seller']) ? 1 : 0;

    if(empty($name)) $name_err = "Please enter a product name.";
    if(empty($description)) $description_err = "Please enter a description.";
    if(empty($price)) $price_err = "Please enter a price.";
    if(empty($category_id)) $category_id_err = "Please select a category.";

    // Handle image upload
    $image_filename = "default.jpg";
    if(isset($_FILES["image"]) && $_FILES["image"]["error"] == 0){
        $allowed = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
        $filename = $_FILES["image"]["name"];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(array_key_exists($ext, $allowed)){
            $new_filename = uniqid() . "." . $ext;
            if(move_uploaded_file($_FILES["image"]["tmp_name"], "../uploads/" . $new_filename)){
                $image_filename = $new_filename;
            }
        }
    }

    // Check input errors before inserting in database
    if(empty($name_err) && empty($description_err) && empty($price_err) && empty($category_id_err) && empty($image_err)){

        // Removed google_drive_folder_id from the insert
        $sql = "INSERT INTO products (name, description, price, duration_days, category_id, image, is_featured, is_top_seller) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("ssdiisii", $name, $description, $price, $duration_days, $category_id, $image_filename, $is_featured, $is_top_seller);

            if($stmt->execute()){
                $product_id = $stmt->insert_id;

                // Now, handle the Google Drive files
                if(!empty($google_drive_file_ids)) {
                    $file_ids = explode(',', $google_drive_file_ids);
                    $sql_drive = "INSERT INTO product_google_drive_files (product_id, google_drive_file_id) VALUES (?, ?)";
                    if($stmt_drive = $mysqli->prepare($sql_drive)) {
                        foreach($file_ids as $file_id) {
                            $stmt_drive->bind_param("is", $product_id, $file_id);
                            $stmt_drive->execute();
                        }
                        $stmt_drive->close();
                    }
                }

                $_SESSION['product_added'] = "Product successfully added.";
                header("location: manage_products.php");
                exit();
            } else{
                $message = '<div class="alert alert-danger">Oops! Something went wrong. Please try again later.</div>';
            }
            $stmt->close();
        }
    } else {
        $message = '<div class="alert alert-danger">Please correct the errors and try again.</div>';
    }
}

// Now that all PHP logic is done, we can start sending HTML.
include 'includes/header.php';

// Fetch categories for the dropdown (needed for the form)
$sql_categories = "SELECT * FROM categories ORDER BY name ASC";
$result_categories = $mysqli->query($sql_categories);
$categories = $result_categories->fetch_all(MYSQLI_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Add New Subscription Package</h1>
    <a href="manage_products.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Packages</a>
</div>

<?php echo $message; ?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-plus-circle"></i> New Package Details
    </div>
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" name="name" id="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                        <span class="invalid-feedback"><?php echo $name_err; ?></span>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control <?php echo (!empty($description_err)) ? 'is-invalid' : ''; ?>" rows="5"><?php echo $description; ?></textarea>
                        <span class="invalid-feedback"><?php echo $description_err; ?></span>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text"><?php echo get_app_setting('currency_symbol', '$'); ?></span>
                                    <input type="number" name="price" id="price" class="form-control <?php echo (!empty($price_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $price; ?>" step="0.01">
                                    <span class="invalid-feedback"><?php echo $price_err; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                             <div class="mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select name="category_id" id="category_id" class="form-select <?php echo (!empty($category_id_err)) ? 'is-invalid' : ''; ?>">
                                    <option value="">Select a category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo ($category_id == $category['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="invalid-feedback"><?php echo $category_id_err; ?></span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="duration_days" class="form-label">Duration</label>
                                <input type="number" name="duration_days" id="duration_days" class="form-control" value="365">
                                <div class="form-text">In days.</div>
                            </div>
                        </div>
                    </div>
                     <div class="mb-3">
                        <label for="google_drive_files" class="form-label">Google Drive Files</label>
                        <input type="hidden" name="google_drive_file_ids" id="google_drive_file_ids" value="<?php echo htmlspecialchars($google_drive_file_ids); ?>">
                        <div class="input-group">
                            <input type="text" id="google_drive_files_display" class="form-control" placeholder="No files selected" readonly>
                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#driveBrowserModal">Browse Drive</button>
                        </div>
                        <div class="form-text">Link this package to specific Google Drive files.</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="image" class="form-label">Product Image</label>
                        <input type="file" name="image" id="image" class="form-control <?php echo (!empty($image_err)) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $image_err; ?></span>
                        <div class="form-text">Max file size: 5MB. Allowed formats: JPG, JPEG, PNG, GIF.</div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" value="1" <?php echo ($is_featured) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_featured">Featured Product</label>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_top_seller" class="form-check-input" id="is_top_seller" value="1" <?php echo ($is_top_seller) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_top_seller">Top Seller</label>
                    </div>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-end">
                <a href="manage_products.php" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Add Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Google Drive Browser Modal -->
<div class="modal fade" id="driveBrowserModal" tabindex="-1" aria-labelledby="driveBrowserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="driveBrowserModalLabel">Browse Google Drive</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="drive-browser-content">
        <!-- AJAX content will be loaded here -->
        <p>Loading...</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveDriveSelection">Save Selection</button>
      </div>
    </div>
  </div>
</div>

<?php
// Include admin footer
include 'includes/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('driveBrowserModal');
    const modalBody = document.getElementById('drive-browser-content');
    const fileIdsInput = document.getElementById('google_drive_file_ids');
    const fileDisplayInput = document.getElementById('google_drive_files_display');
    const saveButton = document.getElementById('saveDriveSelection');
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modal);

    // Keep track of selected files in the modal to handle pagination/browsing without losing selections
    let selectedFilesInModal = [];

    function updateDisplay() {
        const fileIds = fileIdsInput.value.split(',').filter(id => id.trim() !== '');
        if (fileIds.length > 0) {
            fileDisplayInput.value = `${fileIds.length} file(s) selected`;
        } else {
            fileDisplayInput.value = 'No files selected';
        }
    }

    function loadDriveBrowser(folderId = 'root') {
        modalBody.innerHTML = '<p>Loading...</p>';
        // Pass currently saved file IDs to pre-check boxes
        const selectedFiles = fileIdsInput.value;
        fetch(`ajax_drive_browser.php?folder=${folderId}&selected_files=${selectedFiles}`)
            .then(response => response.text())
            .then(html => {
                modalBody.innerHTML = html;
                // Sync the modal's selection state with the main page's state
                selectedFilesInModal = fileIdsInput.value.split(',').filter(id => id.trim() !== '');
            })
            .catch(error => {
                modalBody.innerHTML = '<p class="text-danger">Failed to load content.</p>';
                console.error('Error:', error);
            });
    }

    // Load initial content when modal is shown
    modal.addEventListener('show.bs.modal', function () {
        loadDriveBrowser();
    });

    // Handle clicks inside the modal for navigation and live selection changes
    modalBody.addEventListener('click', function(event) {
        const target = event.target;

        if (target.classList.contains('drive-browse-btn')) {
            event.preventDefault();
            const folderId = target.dataset.folderId;
            loadDriveBrowser(folderId);
        }

        if (target.classList.contains('drive-file-checkbox')) {
            const fileId = target.value;
            if (target.checked) {
                if (!selectedFilesInModal.includes(fileId)) {
                    selectedFilesInModal.push(fileId);
                }
            } else {
                selectedFilesInModal = selectedFilesInModal.filter(id => id !== fileId);
            }
        }
    });

    // Handle Save Selection button click
    saveButton.addEventListener('click', function() {
        fileIdsInput.value = selectedFilesInModal.join(',');
        updateDisplay();
        modalInstance.hide();
    });

    // Initial display update on page load
    updateDisplay();
});
</script>
