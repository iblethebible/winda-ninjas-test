<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('location: /index.html');
    exit;
}

include "../../includes/connectdb.php";
require('../../vendor/autoload.php');

$org_id = $_SESSION['org_id'];



// Fetch organization details
$orgDetailsSql = "SELECT * FROM organisations WHERE id = ?"; 
$stmtOrgDetails = $conn->prepare($orgDetailsSql);

if ($stmtOrgDetails === false) {
    die("Prepare failed: " . $conn->error);
}

$stmtOrgDetails->bind_param("i", $org_id);
$stmtOrgDetails->execute();
$orgResult = $stmtOrgDetails->get_result();
$orgRow = $orgResult->fetch_assoc();

$org_name = $orgRow["org_name"];
$org_houseNumName = $orgRow["org_houseNumName"];
$orgStreetName = $orgRow["org_streetName"];
$orgTown = $orgRow["org_town"];
$orgCounty = $orgRow["org_county"];
$orgPostcode = $orgRow["org_postcode"];
$orgPhone = $orgRow["org_phone"];
$orgEmail = $orgRow["org_email"];




// Initialize PDF
$pdf = new FPDF('P', 'mm', 'A4'); // Portrait orientation, millimeters units, A4 page size
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Static Elements
// Adding a logo (logo.png should be in the same directory as this script)
$pdf->Image('/var/www/html/imgs/logo.png', 10, 10, 30);
$pdf->Cell(0, 10, $org_name, 0, 1, 'C');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 10, $org_houseNumName, 0, 1, 'C');
$pdf->Cell(0, 10, $orgStreetName, 0, 1, 'C');
$pdf->Cell(0, 10, $orgTown, 0, 1, 'C');
$pdf->Cell(0, 10, $orgCounty, 0, 1, 'C');
$pdf->Cell(0, 10, $orgPostcode, 0, 1, 'C');
$pdf->Cell(0, 10, $orgPhone, 0, 1, 'C');
$pdf->Cell(0, 10, $orgEmail, 0, 1, 'C');

// Dynamic Elements
// Assuming the dynamic data comes from some variables like:
$customerName = "John Doe";
$invoiceNumber = "123456";
$items = [
    ['item' => 'Window Cleaning', 'price' => 50],
    ['item' => 'Gutter Cleaning', 'price' => 25],
];

// Customer details
$pdf->Cell(0, 10, "Customer: $customerName", 0, 1, 'L');
$pdf->Cell(0, 10, "Date: " . date('d/m/Y'), 0, 1, 'L');
$pdf->Cell(0, 10, "Invoice #: $invoiceNumber", 0, 1, 'L');

// Line items and prices
$pdf->Cell(90, 10, 'Description', 1);
$pdf->Cell(40, 10, 'Price', 1, 1); // Move to next row

foreach ($items as $item) {
    $pdf->Cell(90, 10, $item['item'], 1);
    $pdf->Cell(40, 10, $item['price'], 1, 1); // Move to next row
}

// Generate PDF
$pdf->Output()
?>
