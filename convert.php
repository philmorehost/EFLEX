<?php
// Include the custom autoloader
require_once('autoloader.php');

use Fawno\FPDF\FawnoFPDF;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['fileToUpload'])) {
    // Create directories if they don't exist
    if (!is_dir('uploads')) {
        mkdir('uploads');
    }
    if (!is_dir('converted')) {
        mkdir('converted');
    }

    $upload_dir = "uploads/";
    $converted_dir = "converted/";
    $converted_files = [];

    // Re-organize the $_FILES array
    $files = [];
    if (isset($_FILES['fileToUpload']['name']) && is_array($_FILES['fileToUpload']['name'])) {
        foreach ($_FILES['fileToUpload'] as $key => $all) {
            foreach ($all as $i => $val) {
                $files[$i][$key] = $val;
            }
        }
    } else {
        $files[] = $_FILES['fileToUpload'];
    }


    foreach ($files as $file) {
        $target_file = $upload_dir . basename($file["name"]);
        $file_extension = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Supported extensions
        $fpdf_extensions = ['txt', 'jpg', 'jpeg', 'png'];
        $office_extensions = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
        $supported_extensions = array_merge($fpdf_extensions, $office_extensions);

        if (!in_array($file_extension, $supported_extensions)) {
            echo "Error: File type '." . $file_extension . "' is not supported for file '" . $file["name"] . "'.<br>";
            continue;
        }

        $upload_success = (defined('TEST_MODE') && TEST_MODE) ?
            copy($file["tmp_name"], $target_file) :
            move_uploaded_file($file["tmp_name"], $target_file);

        if ($upload_success) {
            $output_filename = pathinfo($target_file, PATHINFO_FILENAME) . '.pdf';
            $output_path = $converted_dir . $output_filename;

            if (in_array($file_extension, $fpdf_extensions)) {
                // Convert with FPDF
                $pdf = new FawnoFPDF();
                $pdf->AddPage();
                $pdf->SetFont('Arial', '', 12);

                if ($file_extension == 'txt') {
                    $content = file_get_contents($target_file);
                    $pdf->MultiCell(0, 5, $content);
                } else { // Image
                    list($width, $height) = getimagesize($target_file);
                    $pageWidth = $pdf->GetPageWidth() - 20;
                    $pageHeight = $pdf->GetPageHeight() - 20;
                    $ratio = $width / $height;
                    if ($width > $pageWidth) {
                        $width = $pageWidth;
                        $height = $width / $ratio;
                    }
                    if ($height > $pageHeight) {
                        $height = $pageHeight;
                        $width = $height * $ratio;
                    }
                    $pdf->Image($target_file, 10, 10, $width, $height);
                }
                $pdf->Output('F', $output_path);
                $converted_files[] = $output_path;

            } elseif (in_array($file_extension, $office_extensions)) {
                // Convert with LibreOffice
                $command = 'libreoffice --headless --convert-to pdf "' . $target_file . '" --outdir "' . $converted_dir . '" 2>&1';
                $output = shell_exec($command);

                // Check if the file was created
                if (file_exists($output_path)) {
                    $converted_files[] = $output_path;
                } else {
                    echo "Error converting file '" . $file["name"] . "' with LibreOffice.<br>";
                    echo "LibreOffice output: <pre>" . htmlspecialchars($output) . "</pre><br>";
                }
            }

            // Clean up the uploaded file
            unlink($target_file);
        } else {
            echo "Sorry, there was an error uploading your file: " . $file["name"] . "<br>";
        }
    }

    // Apply security to the converted files
    $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
    if (!empty($converted_files) && !empty($permissions)) {
        require_once('SecureFPDI.php');

        foreach ($converted_files as $file_path) {
            $pdf = new SecureFPDI();
            $pageCount = $pdf->setSourceFile($file_path);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);
                $pdf->AddPage($size['orientation'], $size);
                $pdf->useTemplate($templateId);
            }

            $pdf->SetProtection($permissions);
            $pdf->Output('F', $file_path);
        }
    }

    if (!empty($converted_files)) {
        $zip = new ZipArchive();
        $zip_name = "converted_files_" . time() . ".zip";
        $zip_path = $converted_dir . $zip_name;

        if ($zip->open($zip_path, ZipArchive::CREATE) === TRUE) {
            foreach ($converted_files as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();

            // Send the zip file to the user
            if (defined('TEST_MODE') && TEST_MODE) {
                readfile($zip_path);
            } else {
                header('Content-Type: application/zip');
                header('Content-disposition: attachment; filename=' . $zip_name);
                header('Content-Length: ' . filesize($zip_path));
                readfile($zip_path);
            }

            // Clean up the temporary files
            foreach ($converted_files as $file) {
                unlink($file);
            }
            unlink($zip_path);

        } else {
            echo 'Failed to create the zip file.';
        }
    } else {
        echo "No files were converted.";
    }

} else {
    echo "No file uploaded.";
}
