<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Converter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">PDF Converter</h1>
        <p class="text-center">Convert your documents to PDF and secure them with various options.</p>
        <div class="card">
            <div class="card-body">
                <form action="convert.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="formFile" class="form-label">Select a file to convert</label>
                        <input class="form-control" type="file" id="formFile" name="fileToUpload" required>
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
