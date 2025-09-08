<?php
// Include admin header
include 'includes/header.php';

// Fetch categories for the dropdown
$sql_categories = "SELECT * FROM categories ORDER BY name ASC";
$result_categories = $mysqli->query($sql_categories);
$categories = $result_categories->fetch_all(MYSQLI_ASSOC);

// Define variables
$name = $description = $price = $category_id = $current_image = "";
$duration_days = 365;
$is_featured = $is_top_seller = 0;
$name_err = $description_err = $price_err = $category_id_err = $image_err = $files_err = "";
$message = "";
$product_id = 0;
$existing_local_files = [];
$existing_gdrive_files = [];

// Check if ID is provided for editing
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    $product_id = trim($_GET["id"]);
} elseif(isset($_POST["id"]) && !empty(trim($_POST["id"]))) {
    $product_id = trim($_POST["id"]);
} else {
    $_SESSION['product_error'] = "Invalid product specified.";
    header("location: manage_products.php");
    exit();
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price = trim($_POST["price"]);
    $category_id = $_POST["category_id"];
    $duration_days = (int)$_POST['duration_days'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_top_seller = isset($_POST['is_top_seller']) ? 1 : 0;
    $current_image = $_POST['current_image'];
    $delete_local_files = $_POST['delete_local_files'] ?? [];
    $delete_gdrive_files = $_POST['delete_gdrive_files'] ?? [];
    $html_content = $_POST['html_content'] ?? null;

    if(empty($name)) $name_err = "Please enter a product name.";
    if(empty($description)) $description_err = "Please enter a description.";
    if(!isset($price) || $price === "") $price_err = "Please enter a price.";
    if(empty($category_id)) $category_id_err = "Please select a category.";

    $mysqli->begin_transaction();
    try {
        // 1. Handle Deleting Local Files
        if (!empty($delete_local_files)) {
            $sql_get_filenames = "SELECT filepath FROM product_local_files WHERE id IN (".implode(',', array_fill(0, count($delete_local_files), '?')).") AND product_id = ?";
            $stmt_get = $mysqli->prepare($sql_get_filenames);
            $types = str_repeat('i', count($delete_local_files)) . 'i';
            $params = array_merge($delete_local_files, [$product_id]);
            $stmt_get->bind_param($types, ...$params);
            $stmt_get->execute();
            $result_get = $stmt_get->get_result();
            while($row = $result_get->fetch_assoc()) {
                $file_to_unlink = __DIR__ . '/../' . $row['filepath'];
                if (file_exists($file_to_unlink)) {
                    unlink($file_to_unlink);
                }
            }
            $stmt_get->close();
            $sql_delete_files = "DELETE FROM product_local_files WHERE id IN (".implode(',', array_fill(0, count($delete_local_files), '?')).") AND product_id = ?";
            $stmt_delete = $mysqli->prepare($sql_delete_files);
            $stmt_delete->bind_param($types, ...$params);
            $stmt_delete->execute();
            $stmt_delete->close();
        }

        // 2. Handle Deleting Google Drive Files
        if (!empty($delete_gdrive_files)) {
            $sql_delete_gdrive = "DELETE FROM product_google_drive_files WHERE id IN (".implode(',', array_fill(0, count($delete_gdrive_files), '?')).") AND product_id = ?";
            $stmt_delete_gdrive = $mysqli->prepare($sql_delete_gdrive);
            $types_gdrive = str_repeat('i', count($delete_gdrive_files)) . 'i';
            $params_gdrive = array_merge($delete_gdrive_files, [$product_id]);
            $stmt_delete_gdrive->bind_param($types_gdrive, ...$params_gdrive);
            $stmt_delete_gdrive->execute();
            $stmt_delete_gdrive->close();
        }

        // 3. Handle Image Upload
        $new_image_filename = $current_image;
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            // ... (image upload logic as before) ...
        }

        // 4. Handle New Local File Uploads
        // ... (local file upload logic as before) ...

        // 5. Handle New Google Drive File Additions
        $google_drive_files_to_add = isset($_POST['google_drive_files']) ? json_decode($_POST['google_drive_files'], true) : [];
        if (json_last_error() !== JSON_ERROR_NONE) throw new Exception('Invalid Google Drive file data received.');

        if(!empty($google_drive_files_to_add)) {
            $sql_gdrive_files = "INSERT INTO product_google_drive_files (product_id, google_drive_file_id, filename, mimetype) VALUES (?, ?, ?, ?)";
            $stmt_gdrive = $mysqli->prepare($sql_gdrive_files);
            foreach($google_drive_files_to_add as $file) {
                $stmt_gdrive->bind_param("isss", $product_id, $file['id'], $file['name'], $file['mimeType']);
                $stmt_gdrive->execute();
            }
            $stmt_gdrive->close();
        }

        // 6. Update Product Details
        $sql_update = "UPDATE products SET name=?, description=?, html_content=?, price=?, duration_days=?, category_id=?, image=?, is_featured=?, is_top_seller=? WHERE id=?";
        $stmt_update = $mysqli->prepare($sql_update);
        $stmt_update->bind_param("sssdiisiii", $name, $description, $html_content, $price, $duration_days, $category_id, $new_image_filename, $is_featured, $is_top_seller, $product_id);
        $stmt_update->execute();
        $stmt_update->close();

        $mysqli->commit();
        $_SESSION['product_updated'] = "Package details updated successfully.";
        header("location: manage_products.php");
        exit();

    } catch (Exception $e) {
        $mysqli->rollback();
        $message = '<div class="alert alert-danger">Update failed: ' . $e->getMessage() . '</div>';
    }
} else {
    // Fetch current data for the form
    $sql_fetch = "SELECT name, description, html_content, price, duration_days, category_id, image, is_featured, is_top_seller FROM products WHERE id = ?";
    if($stmt_fetch = $mysqli->prepare($sql_fetch)){
        $stmt_fetch->bind_param("i", $product_id);
        if($stmt_fetch->execute()){
            $result = $stmt_fetch->get_result();
            if($result->num_rows == 1){
                $product = $result->fetch_assoc();
                $name = $product['name'];
                $description = $product['description'];
                $html_content = $product['html_content'];
                $price = $product['price'];
                $duration_days = $product['duration_days'];
                $category_id = $product['category_id'];
                $current_image = $product['image'];
                $is_featured = $product['is_featured'];
                $is_top_seller = $product['is_top_seller'];
            } else {
                $_SESSION['product_error'] = "Product not found.";
                header("location: manage_products.php");
                exit();
            }
        }
        $stmt_fetch->close();
    }
    // Fetch existing local files
    $sql_local_files = "SELECT id, original_filename FROM product_local_files WHERE product_id = ?";
    if($stmt_local = $mysqli->prepare($sql_local_files)) {
        $stmt_local->bind_param("i", $product_id);
        $stmt_local->execute();
        $result_local = $stmt_local->get_result();
        $existing_local_files = $result_local->fetch_all(MYSQLI_ASSOC);
        $stmt_local->close();
    }
    // Fetch existing Google Drive files
    $sql_gdrive_files = "SELECT id, filename FROM product_google_drive_files WHERE product_id = ?";
    if($stmt_gdrive = $mysqli->prepare($sql_gdrive_files)) {
        $stmt_gdrive->bind_param("i", $product_id);
        $stmt_gdrive->execute();
        $result_gdrive = $stmt_gdrive->get_result();
        $existing_gdrive_files = $result_gdrive->fetch_all(MYSQLI_ASSOC);
        $stmt_gdrive->close();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Edit Subscription Package</h1>
    <a href="manage_products.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Packages</a>
</div>

<?php echo $message; ?>

<div class="card">
    <div class="card-header"><i class="fas fa-edit"></i> Edit Package Details</div>
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $product_id; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $product_id; ?>">
            <input type="hidden" name="current_image" value="<?php echo $current_image; ?>">

            <div class="row">
                <div class="col-md-8">
                    <!-- Form fields for product details -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Package Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>">
                    </div>
                    <!-- ... other form fields ... -->
                     <hr>
                    <!-- File Management -->
                    <div class="mb-3">
                        <label class="form-label">Manage Local Files</label>
                        <?php if (!empty($existing_local_files)): ?>
                            <ul class="list-group">
                                <?php foreach($existing_local_files as $file): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo htmlspecialchars($file['original_filename']); ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="delete_local_files[]" value="<?php echo $file['id']; ?>" id="delete_local_file_<?php echo $file['id']; ?>">
                                            <label class="form-check-label text-danger" for="delete_local_file_<?php echo $file['id']; ?>">Delete</label>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?><p class="text-muted">No local files are currently associated.</p><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="product_files" class="form-label">Upload New Local Files</label>
                        <input type="file" name="product_files[]" id="product_files" class="form-control" multiple>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Manage Google Drive Files</label>
                        <?php if (!empty($existing_gdrive_files)): ?>
                            <ul class="list-group">
                                <?php foreach($existing_gdrive_files as $file): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <i class="fab fa-google-drive me-2"></i> <?php echo htmlspecialchars($file['filename']); ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="delete_gdrive_files[]" value="<?php echo $file['id']; ?>" id="delete_gdrive_file_<?php echo $file['id']; ?>">
                                            <label class="form-check-label text-danger" for="delete_gdrive_file_<?php echo $file['id']; ?>">Delete</label>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?><p class="text-muted">No Google Drive files are currently associated.</p><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Add New Google Drive Files</label>
                        <div>
                            <button type="button" class="btn btn-secondary" id="open-drive-picker"><i class="fab fa-google-drive"></i> Select from Drive</button>
                        </div>
                        <div id="gdrive-selected-files" class="mt-2"></div>
                        <input type="hidden" name="google_drive_files" id="google_drive_files_input">
                    </div>
                    <hr>
                    <!-- ... other form fields ... -->
                </div>
                <div class="col-md-4">
                    <!-- Image and flags -->
                     <!-- ... -->
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-end">
                <a href="manage_products.php" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Google Drive Picker -->
<div class="modal fade" id="drivePickerModal" tabindex="-1" aria-labelledby="drivePickerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="drivePickerModalLabel">Select a File from Google Drive</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <iframe src="" style="width: 100%; height: 60vh; border: none;"></iframe>
      </div>
    </div>
  </div>
</div>

<script>
// JavaScript for the picker remains the same as in add_product.php
document.addEventListener('DOMContentLoaded', function() {
    const openPickerBtn = document.getElementById('open-drive-picker');
    const modalElement = document.getElementById('drivePickerModal');
    const driveModal = new bootstrap.Modal(modalElement);
    const selectedFilesContainer = document.getElementById('gdrive-selected-files');
    const hiddenInput = document.getElementById('google_drive_files_input');
    let selectedFiles = [];
    openPickerBtn.addEventListener('click', function() {
        const iframe = modalElement.querySelector('iframe');
        iframe.src = 'manage_drive.php?mode=picker';
        driveModal.show();
    });
    window.addEventListener('message', function(event) {
        if (event.source !== modalElement.querySelector('iframe').contentWindow) return;
        const data = event.data;
        if (data.source === 'googleDrivePicker' && data.file) {
            if (!selectedFiles.some(f => f.id === data.file.id)) {
                selectedFiles.push(data.file);
                updateSelectedFilesUI();
            }
            driveModal.hide();
        }
    });
    function updateSelectedFilesUI() {
        selectedFilesContainer.innerHTML = '';
        if (selectedFiles.length > 0) {
            const list = document.createElement('ul');
            list.className = 'list-group';
            selectedFiles.forEach((file, index) => {
                const item = document.createElement('li');
                item.className = 'list-group-item d-flex justify-content-between align-items-center';
                item.textContent = file.name;
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn-close';
                removeBtn.setAttribute('aria-label', 'Remove');
                removeBtn.onclick = function() {
                    selectedFiles.splice(index, 1);
                    updateSelectedFilesUI();
                };
                item.appendChild(removeBtn);
                list.appendChild(item);
            });
            selectedFilesContainer.appendChild(list);
        }
        hiddenInput.value = JSON.stringify(selectedFiles);
    }
});
</script>

<?php
// Include admin footer
include 'includes/footer.php';
?>
