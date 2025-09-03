<?php
// We need to initialize the session and connect to the DB at the very top
// because we will be processing forms before any HTML is rendered.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db_connect.php';

// Check if the user is logged in and is an admin.
// This check must happen before any other logic.
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin'){
    header("location: ../index.php");
    exit;
}


$message = "";

// Handle Update Category
if(isset($_POST['update_category'])){
    $name = trim($_POST['name']);
    $id = trim($_POST['id']);
    if(!empty($name) && !empty($id)){
        $sql = "UPDATE categories SET name = ? WHERE id = ?";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("si", $name, $id);
            if($stmt->execute()){
                // This redirect will now work correctly.
                header("location: manage_categories.php?update_success=1");
                exit();
            } else {
                $message = '<div class="alert alert-danger">Error updating category.</div>';
            }
            $stmt->close();
        }
    } else {
        $message = '<div class="alert alert-danger">Category name or ID is invalid.</div>';
    }
}

// Handle Add Category
if(isset($_POST['add_category'])){
    $name = trim($_POST['name']);
    if(!empty($name)){
        $sql_check = "SELECT id FROM categories WHERE name = ?";
        if($stmt_check = $mysqli->prepare($sql_check)){
            $stmt_check->bind_param("s", $name);
            $stmt_check->execute();
            $stmt_check->store_result();
            if($stmt_check->num_rows > 0){
                $message = '<div class="alert alert-warning">A category with this name already exists.</div>';
            } else {
                $sql = "INSERT INTO categories (name) VALUES (?)";
                if($stmt = $mysqli->prepare($sql)){
                    $stmt->bind_param("s", $name);
                    if($stmt->execute()){
                        $message = '<div class="alert alert-success">Category added successfully.</div>';
                    } else {
                        $message = '<div class="alert alert-danger">Error: Could not add category.</div>';
                    }
                    $stmt->close();
                }
            }
            $stmt_check->close();
        }
    } else {
        $message = '<div class="alert alert-danger">Category name cannot be empty.</div>';
    }
}

// Handle Delete Category
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $sql_check = "SELECT COUNT(*) FROM products WHERE category_id = ?";
    if($stmt_check = $mysqli->prepare($sql_check)){
        $stmt_check->bind_param("i", $id);
        $stmt_check->execute();
        $stmt_check->bind_result($product_count);
        $stmt_check->fetch();
        $stmt_check->close();

        if($product_count > 0){
            $message = '<div class="alert alert-danger">Cannot delete category. It is associated with '.$product_count.' product(s).</div>';
        } else {
            $sql = "DELETE FROM categories WHERE id = ?";
            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("i", $id);
                if($stmt->execute()){
                     $message = '<div class="alert alert-success">Category deleted successfully.</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error deleting category.</div>';
                }
                $stmt->close();
            }
        }
    }
}


if(isset($_GET['update_success'])){
    $message = '<div class="alert alert-success">Category updated successfully.</div>';
}

// Now that all logic that might cause a redirect is done, we can include the header.
include 'includes/header.php';

// Check if we are in edit mode
$is_edit_mode = false;
$edit_name = "";
$edit_id = 0;
if(isset($_GET['edit'])){
    $is_edit_mode = true;
    $id = $_GET['edit'];
    $sql_edit = "SELECT name FROM categories WHERE id = ?";
    if($stmt_edit = $mysqli->prepare($sql_edit)){
        $stmt_edit->bind_param("i", $id);
        $stmt_edit->execute();
        $stmt_edit->bind_result($name);
        $stmt_edit->fetch();
        $edit_name = $name;
        $edit_id = $id;
        $stmt_edit->close();
    }
}

// Fetch all categories for display
$categories = $mysqli->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

?>

<h1>Manage Categories</h1>
<p class="lead">Add, edit, or remove product categories.</p>

<?php echo $message; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><i class="fas fa-tags"></i> Existing Categories</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($categories) > 0): ?>
                                <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?php echo $category['id']; ?></td>
                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td class="text-end">
                                        <a href="manage_categories.php?edit=<?php echo $category['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                        <a href="manage_categories.php?delete=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure? Deleting a category cannot be undone.')"><i class="fas fa-trash"></i> Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center">No categories found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="fas <?php echo $is_edit_mode ? 'fa-edit' : 'fa-plus'; ?>"></i> <?php echo $is_edit_mode ? 'Edit Category' : 'Add New Category'; ?>
            </div>
            <div class="card-body">
                <form action="manage_categories.php" method="post">
                    <input type="hidden" name="id" value="<?php echo $edit_id; ?>">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name</label>
                        <input type="text" id="categoryName" name="name" class="form-control" placeholder="e.g., Electronics" value="<?php echo htmlspecialchars($edit_name); ?>" required>
                    </div>
                    <?php if($is_edit_mode): ?>
                        <button class="btn btn-primary w-100" type="submit" name="update_category">Update Category</button>
                        <a href="manage_categories.php" class="btn btn-secondary w-100 mt-2">Cancel Edit</a>
                    <?php else: ?>
                        <button class="btn btn-success w-100" type="submit" name="add_category">Add Category</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>


<?php
// Include admin footer
include 'includes/footer.php';
?>
