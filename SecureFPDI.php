<?php
require_once __DIR__ . '/autoloader.php';
require_once __DIR__ . '/FPDI-2.6.4/src/autoload.php';

use setasign\Fpdi\Fpdi;
use FPDF\Scripts\PDFProtection\PDFProtectionTrait;

class SecureFPDI extends Fpdi
{
    use PDFProtectionTrait;
}
