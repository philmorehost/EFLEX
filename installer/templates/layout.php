<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VTU Website Installer</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f0f2f5;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .installer-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 600px;
            max-width: 90%;
            overflow: hidden;
        }
        .installer-header {
            background-color: #4a90e2;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .installer-header h1 {
            margin: 0;
            font-size: 1.5em;
        }
        .installer-content {
            padding: 30px;
        }
        .installer-footer {
            background-color: #f7f7f7;
            padding: 15px 30px;
            text-align: right;
            border-top: 1px solid #e0e0e0;
        }
        .btn {
            background-color: #4a90e2;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1em;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #357abd;
        }
        .btn[disabled] {
            background-color: #a0c7e4;
            cursor: not-allowed;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        li:last-child {
            border-bottom: none;
        }
        .success {
            color: #28a745;
        }
        .error {
            color: #dc3545;
        }
        .badge {
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 4px;
            color: white;
        }
        .badge-success {
            background-color: #28a745;
        }
        .badge-error {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <div class="installer-header">
            <h1>VTU Website Installer</h1>
        </div>
        <div class="installer-content">
            <?php
            // The step-specific template will be included here
            if (isset($template) && file_exists($template)) {
                include $template;
            } else {
                echo "<p class='error'>Error: Could not load installer step.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>