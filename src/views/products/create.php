<?php
// The controller now handles session checks and data fetching.
// The $categories variable is made available by the controller.

require_once __DIR__ . '/../partials/header.php';
?>

<div class="page-header">
    <h1>Upload New Product</h1>
</div>

<div class="form-container">
    <?php if ($error_message = Session::flash('error_message')): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <form action="/products/create" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="8" required></textarea>
        </div>
        <div class="form-group">
            <label for="price">Price ($)</label>
            <input type="number" id="price" name="price" step="0.01" min="0" required>
        </div>
        <div class="form-group">
            <label for="category">Category</label>
            <select id="category" name="category_id" required>
                <option value="">-- Select a Category --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="product_file">Product File (.zip)</label>
            <input type="file" id="product_file" name="product_file" accept=".zip" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload Product</button>
    </form>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
