<?php
// Initialize the session
session_start();

// Check if the user is logged in and is an admin.
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin'){
    header("location: ../index.php");
    exit;
}

// Include database connection file
require_once "../includes/db_connect.php";

$message = "";

// Handle Add Category
if(isset($_POST['add_category'])){
    $name = trim($_POST['name']);
    if(!empty($name)){
        $sql = "INSERT INTO categories (name) VALUES (?)";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("s", $name);
            if($stmt->execute()){
                $message = '<div class="alert alert-success">Category added successfully.</div>';
            } else {
                $message = '<div class="alert alert-danger">Error adding category.</div>';
            }
            $stmt->close();
        }
    } else {
        $message = '<div class="alert alert-danger">Category name cannot be empty.</div>';
    }
}

// Handle Delete Category
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    // First, check if any products are associated with this category
    $sql_check = "SELECT id FROM products WHERE category_id = ?";
    if($stmt_check = $mysqli->prepare($sql_check)){
        $stmt_check->bind_param("i", $id);
        $stmt_check->execute();
        $stmt_check->store_result();
        if($stmt_check->num_rows > 0){
            $message = '<div class="alert alert-danger">Cannot delete category. It is associated with existing products.</div>';
        } else {
            $sql = "DELETE FROM categories WHERE id = ?";
            if($stmt = $mysqli->prepare($sql)){
                $stmt->bind_param("i", $id);
                if($stmt->execute()){
                     header("location: manage_categories.php"); // Redirect to clean the URL
                     exit();
                } else {
                    $message = '<div class="alert alert-danger">Error deleting category.</div>';
                }
                $stmt->close();
            }
        }
        $stmt_check->close();
    }
}

// Handle Update Category
if(isset($_POST['update_category'])){
    $name = trim($_POST['name']);
    $id = trim($_POST['id']);
    if(!empty($name) && !empty($id)){
        $sql = "UPDATE categories SET name = ? WHERE id = ?";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("si", $name, $id);
            if($stmt->execute()){
                header("location: manage_categories.php");
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

// Check if we are in edit mode
$is_edit_mode = false;
$edit_name = "";
$edit_id = 0;
if(isset($_GET['edit'])){
    $is_edit_mode = true;
    $id = $_GET['edit'];
    $sql = "SELECT name FROM categories WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $id);
        if($stmt->execute()){
            $stmt->store_result();
            if($stmt->num_rows == 1){
                $stmt->bind_result($name);
                $stmt->fetch();
                $edit_name = $name;
                $edit_id = $id;
            }
        }
        $stmt->close();
    }
}

// Fetch all categories for display
$sql = "SELECT * FROM categories ORDER BY name ASC";
$result = $mysqli->query($sql);
$categories = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
             <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="manage_products.php">Products</a></li>
                <li class="nav-item"><a class="nav-link active" href="manage_categories.php">Categories</a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Manage Categories</h2>
    </div>

    <?php echo $message; ?>

    <!-- Form for Add/Edit -->
    <div class="card mb-4">
        <div class="card-header"><?php echo $is_edit_mode ? 'Edit Category' : 'Add New Category'; ?></div>
        <div class="card-body">
            <form action="manage_categories.php" method="post">
                <input type="hidden" name="id" value="<?php echo $edit_id; ?>">
                <div class="input-group">
                    <input type="text" name="name" class="form-control" placeholder="Category Name" value="<?php echo htmlspecialchars($edit_name); ?>" required>
                    <?php if($is_edit_mode): ?>
                        <button class="btn btn-primary" type="submit" name="update_category">Update Category</button>
                        <a href="manage_categories.php" class="btn btn-secondary">Cancel</a>
                    <?php else: ?>
                        <button class="btn btn-primary" type="submit" name="add_category">Add Category</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="card">
        <div class="card-header">Existing Categories</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
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
                                <a href="manage_categories.php?edit=<?php echo $category['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="manage_categories.php?delete=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3">No categories found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
