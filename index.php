<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Converter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- GOOGLE ADS SCRIPT GOES HERE -->
</head>
<body>
    <?php
    // Display debug info if any
    if (!empty($_SESSION['debug_info'])) {
        echo '<div class="container mt-5"><div class="alert alert-warning" role="alert">';
        echo '<strong>Debugging Information:</strong><br>';
        echo $_SESSION['debug_info'];
        echo '</div></div>';
        unset($_SESSION['debug_info']);
    }
    ?>
    <div class="container mt-5">
        <h1 class="text-center">PDF Converter</h1>
        <p class="text-center">Convert your documents to PDF and secure them with various options.</p>

        <?php
        // Display errors if any
        if (!empty($_SESSION['conversion_errors'])) {
            echo '<div class="alert alert-danger" role="alert">';
            echo '<strong>Conversion Errors:</strong><br>';
            foreach ($_SESSION['conversion_errors'] as $error) {
                echo $error . '<br>';
            }
            echo '</div>';
            unset($_SESSION['conversion_errors']);
        }

        // Display converted files
        if (!empty($_SESSION['converted_files'])) {
            echo '<div class="card mt-4">';
            echo '<div class="card-header">Converted Files</div>';
            echo '<div class="card-body">';
            echo '<ul class="list-group">';
            foreach ($_SESSION['converted_files'] as $file) {
                echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                echo htmlspecialchars($file);
                echo '<a href="download.php?file=' . urlencode($file) . '" class="btn btn-success btn-sm">Download</a>';
                echo '</li>';
            }
            echo '</ul>';
            echo '</div>';
            echo '</div>';
            unset($_SESSION['converted_files']);
        }
        ?>

        <!-- GOOGLE ADS BANNER GOES HERE -->
        <div class="my-4 text-center">
            <div style="width: 728px; height: 90px; background-color: #f0f0f0; border: 1px solid #ccc; margin: auto; line-height: 90px;">
                Google Ad Placeholder (728x90)
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <form action="convert.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="formFile" class="form-label">Select one or more files to convert</label>
                        <input class="form-control" type="file" id="formFile" name="fileToUpload[]" required multiple>
                        <div id="fileHelp" class="form-text">Supported formats: .txt, .jpg, .jpeg, .png, .doc, .docx, .xls, .xlsx, .ppt, .pptx</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Security Options</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="print" id="preventPrinting" name="permissions[]">
                            <label class="form-check-label" for="preventPrinting">
                                Prevent printing
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="copy" id="preventCopying" name="permissions[]">
                            <label class="form-check-label" for="preventCopying">
                                Prevent copying
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="modify" id="preventModification" name="permissions[]">
                            <label class="form-check-label" for="preventModification">
                                Prevent modification
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Convert to PDF</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
