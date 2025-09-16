<?php
// src/controllers/ProductController.php

require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../lib/Session.php';

class ProductController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create() {
        $categoryModel = new Category($this->pdo);
        $data = [
            'categories' => $categoryModel->findAll()
        ];
        extract($data);
        require_once __DIR__ . '/../views/products/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); die('Invalid request method.');
        }

        $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
        $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $sellerId = Session::get('user_id');

        if (!$name || !$description || $price === false || !$categoryId) {
            Session::flash('error_message', 'Invalid input. Please check all fields.');
            header('Location: /products/create'); exit();
        }

        if (!isset($_FILES['product_file']) || $_FILES['product_file']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error_message', 'File upload error. Please try again.');
            header('Location: /products/create'); exit();
        }

        $file = $_FILES['product_file'];
        $uploadDir = __DIR__ . '/../../uploads/';

        if ($file['type'] !== 'application/zip') {
            Session::flash('error_message', 'Invalid file type. Only .zip files are allowed.');
            header('Location: /products/create'); exit();
        }

        $fileName = uniqid('prod_', true) . '.zip';
        $uploadFilePath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
            Session::flash('error_message', 'Failed to save uploaded file.');
            header('Location: /products/create'); exit();
        }

        $productModel = new Product($this->pdo);
        $success = $productModel->create($name, $description, $price, $categoryId, $sellerId, $fileName);

        if ($success) {
            Session::flash('success_message', 'Product uploaded successfully!');
            header('Location: /dashboard');
        } else {
            unlink($uploadFilePath);
            Session::flash('error_message', 'Failed to save product to the database.');
            header('Location: /products/create');
        }
        exit();
    }

    public function search() {
        $criteria = [
            'keyword' => filter_input(INPUT_GET, 'keyword', FILTER_SANITIZE_STRING),
            'category_id' => filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT),
            'min_rating' => filter_input(INPUT_GET, 'min_rating', FILTER_VALIDATE_FLOAT),
        ];

        $criteria = array_filter($criteria);

        $productModel = new Product($this->pdo);
        $products = $productModel->search($criteria);

        header('Content-Type: application/json');
        echo json_encode($products);
        exit();
    }

    /**
     * Display a single product.
     */
    public function show() {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(404);
            echo "<h1>404 Not Found</h1><p>Product not found.</p>";
            exit();
        }

        $productModel = new Product($this->pdo);
        $data['product'] = $productModel->findById($id);

        if (!$data['product']) {
            http_response_code(404);
            echo "<h1>404 Not Found</h1><p>Product not found.</p>";
            exit();
        }

        extract($data);
        require_once __DIR__ . '/../views/product_details.php';
    }
}
