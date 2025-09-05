<?php
session_start();

// Define the path to the LibreOffice executable.
// You might need to change this depending on your server's configuration.
// Common paths: /usr/bin/libreoffice, /opt/libreoffice/program/soffice
define('LIBREOFFICE_PATH', '/usr/bin/libreoffice');

// Include the custom autoloader
require_once('autoloader.php');

use Fawno\FPDF\FawnoFPDF;

// Clear previous conversion data
$_SESSION['converted_files'] = [];
$_SESSION['conversion_errors'] = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['fileToUpload'])) {
    if (!is_dir('uploads')) mkdir('uploads');
    if (!is_dir('converted')) mkdir('converted');

    $upload_dir = "uploads/";
    $converted_dir = "converted/";

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
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['conversion_errors'][] = "Error uploading file: " . $file['name'];
            continue;
        }

        $target_file = $upload_dir . basename($file["name"]);
        $file_extension = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $supported_extensions = ['txt', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
        if (!in_array($file_extension, $supported_extensions)) {
            $_SESSION['conversion_errors'][] = "File type '." . $file_extension . "' is not supported for file '" . $file["name"] . "'.";
            continue;
        }

        $upload_success = (defined('TEST_MODE') && TEST_MODE) ?
            copy($file["tmp_name"], $target_file) :
            move_uploaded_file($file["tmp_name"], $target_file);

        if ($upload_success) {
            $output_filename = pathinfo($target_file, PATHINFO_FILENAME) . '.pdf';
            $output_path = $converted_dir . $output_filename;

            if (in_array($file_extension, ['txt', 'jpg', 'jpeg', 'png'])) {
                $pdf = new FawnoFPDF();
                $pdf->AddPage();
                $pdf->SetFont('Arial', '', 12);
                if ($file_extension == 'txt') {
                    $pdf->MultiCell(0, 5, file_get_contents($target_file));
                } else {
                    list($width, $height) = getimagesize($target_file);
                    $pageWidth = $pdf->GetPageWidth() - 20;
                    $pageHeight = $pdf->GetPageHeight() - 20;
                    $ratio = $width / $height;
                    if ($width > $pageWidth) { $width = $pageWidth; $height = $width / $ratio; }
                    if ($height > $pageHeight) { $height = $pageHeight; $width = $height * $ratio; }
                    $pdf->Image($target_file, 10, 10, $width, $height);
                }
                $pdf->Output('F', $output_path);
                $_SESSION['converted_files'][] = $output_filename;
            } elseif (in_array($file_extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'])) {
                $command = LIBREOFFICE_PATH . ' --headless --convert-to pdf "' . $target_file . '" --outdir "' . $converted_dir . '" 2>&1';
                $output = shell_exec($command);
                if (file_exists($output_path)) {
                    $_SESSION['converted_files'][] = $output_filename;
                } else {
                    $path_env = shell_exec('echo $PATH');
                    $find_output = shell_exec('find / -name "libreoffice" 2>/dev/null');
                    $_SESSION['conversion_errors'][] = "Error converting '" . $file["name"] . "'. LibreOffice output: <pre>" . htmlspecialchars($output) . "</pre>" .
                        "--- Debug Info ---" .
                        "<br>PATH environment variable: <pre>" . htmlspecialchars($path_env) . "</pre>" .
                        "<br>Result of `find / -name libreoffice`: <pre>" . htmlspecialchars($find_output) . "</pre>";
                }
            }
            unlink($target_file);
        } else {
            $_SESSION['conversion_errors'][] = "Error uploading file: " . $file["name"];
        }
    }

    // Apply security
    $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
    if (!empty($_SESSION['converted_files']) && !empty($permissions)) {
        require_once('SecureFPDI.php');
        foreach ($_SESSION['converted_files'] as $filename) {
            $file_path = $converted_dir . $filename;
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
}

header('Location: index.php');
exit();
