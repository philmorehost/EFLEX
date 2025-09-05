<?php
// Include the custom autoloader
require_once('autoloader.php');

use Fawno\FPDF\FawnoFPDF;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['fileToUpload'])) {
    // Re-create uploads directory if it doesn't exist
    if (!is_dir('uploads')) {
        mkdir('uploads');
    }

    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $file_extension = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file is a valid type
    $supported_extensions = ['txt', 'jpg', 'jpeg', 'png'];
    if (!in_array($file_extension, $supported_extensions)) {
        die("Error: Only .txt, .jpg, .jpeg, and .png files are supported at the moment.");
    }

    if ((defined('TEST_MODE') && TEST_MODE) ? copy($_FILES["fileToUpload"]["tmp_name"], $target_file) : move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        // Create new PDF document using FawnoFPDF
        $pdf = new FawnoFPDF();

        // Set document information
        $pdf->SetCreator('PDF Converter');
        $pdf->SetAuthor('PDF Converter');
        $pdf->SetTitle('Converted Document');
        $pdf->SetSubject('A document converted to PDF');

        // Add a page
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);

        // Handle different file types
        if ($file_extension == 'txt') {
            $content = file_get_contents($target_file);
            $pdf->MultiCell(0, 5, $content);
        } elseif (in_array($file_extension, ['jpg', 'jpeg', 'png'])) {
            // Get image dimensions
            list($width, $height) = getimagesize($target_file);
            // Calculate width and height to fit the page
            $pageWidth = $pdf->GetPageWidth() - 20; // 10mm margin on each side
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

        // Get permissions from the form
        $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];

        // Set the protection
        $pdf->SetProtection($permissions);

        // Close and output PDF document
        $output_filename = pathinfo($target_file, PATHINFO_FILENAME) . '.pdf';
        $pdf->Output('D', $output_filename);

        // Clean up the uploaded file
        unlink($target_file);

    } else {
        echo "Sorry, there was an error uploading your file.";
    }
} else {
    echo "No file uploaded.";
}
