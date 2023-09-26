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
$invoiceId = $_GET['invoiceId'];
$jobHistoryId = $_GET['jobHistoryId'];
$customerId = $_GET['customerId'];

echo "invoice id: " . $invoiceId;

echo "job history id: " . $jobHistoryId;
echo "org id:" . $org_id;

// SQL query to retrieve customer details
$customerDetailsSql = "SELECT * FROM customer_org" . $org_id . " WHERE id = ?";
$stmtCustomerDetails = $conn->prepare($customerDetailsSql);

if ($stmtCustomerDetails === false) {
    die("Prepare failed: " . $conn->error);
}

$stmtCustomerDetails->bind_param("i", $customerId);
$stmtCustomerDetails->execute();
$customerResult = $stmtCustomerDetails->get_result();
$customerRow = $customerResult->fetch_assoc();

$customerForename = $customerRow["forename"];
$customerSurname = $customerRow["surname"];
$customername = $customerForename . " " . $customerSurname;
$customerEmail = $customerRow["email"];
$customerPhone = $customerRow["phoneNumber"];


// Fetch job details from job_history table
$jobDetailsSql = "SELECT * FROM job_history_org" . $org_id . " WHERE id = ?";
$stmtJobDetails = $conn->prepare($jobDetailsSql);

if ($stmtJobDetails === false) {
    die("Prepare failed: " . $conn->error);
}

$stmtJobDetails->bind_param("i", $jobHistoryId);
$stmtJobDetails->execute();
$jobResult = $stmtJobDetails->get_result();
$jobRow = $jobResult->fetch_assoc();

// Assuming the column that holds the job_id in job_history table is named job_id
$jobId = $jobRow['job_id'];

// Fetch the corresponding job details from job_org
$jobOrgDetailsSql = "SELECT * FROM job_org" . $org_id . " WHERE id = ?";
$stmtJobOrgDetails = $conn->prepare($jobOrgDetailsSql);

if ($stmtJobOrgDetails === false) {
    die("Prepare failed: " . $conn->error);
}

$stmtJobOrgDetails->bind_param("i", $jobId);
$stmtJobOrgDetails->execute();
$jobOrgResult = $stmtJobOrgDetails->get_result();
$jobOrgRow = $jobOrgResult->fetch_assoc();

// Now you have jobOrgRow that you can use to get more details.


$jobHouseNumName = $jobRow["houseNumName"];
$jobStreetName = $jobRow["streetName"];
$jobPostcode = $jobRow["postcode"];

//organization info
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

//sql to recieve invoice data
$invoiceSql = "SELECT * FROM invoices_org" . $org_id . " WHERE invoice_id = ?";
$stmtInvoice = $conn->prepare($invoiceSql);

if ($stmtInvoice === false) {
    die("Prepare failed: " . $conn->error);
}

$stmtInvoice->bind_param("i", $invoiceId);
$stmtInvoice->execute();

$stmtInvoice->execute();
$invoiceResult = $stmtInvoice->get_result();
$invoiceRow = $invoiceResult->fetch_assoc();

$invoiceDate = $invoiceRow["invoice_date"];
$invoiceJobHistoryId = $invoiceRow["job_history_id"];
$invoiceCustomerId = $invoiceRow["customer_id"];
$invoicePrice = $invoiceRow["price"];




// Initialize PDF
$pdf = new FPDF('P', 'mm', 'A4'); // Portrait orientation, millimeters units, A4 page size
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Static Elements
// Adding a logo (logo.png should be in the same directory as this script)
$pdf->Image('/var/www/html/imgs/logo.png', 10, 10, 30);

// Organization Details with adjusted spacing
$pdf->SetFont('Arial', 'B', 12); // Change the font and size
$pdf->Cell(0, 6, $org_name, 0, 1, 'C'); // Reduce the line height
$pdf->Cell(0, 10, "Invoice #: $invoiceId", 0, 1, 'R');
$pdf->SetFont('Arial', '', 10); // Change the font and size for address
$pdf->Cell(0, 6, $org_houseNumName, 0, 1, 'C'); // Reduce the line height
$pdf->Cell(0, 6, $orgStreetName, 0, 1, 'C'); // Reduce the line height
$pdf->Cell(0, 6, $orgTown, 0, 1, 'C'); // Reduce the line height
$pdf->Cell(0, 6, $orgCounty, 0, 1, 'C'); // Reduce the line height
$pdf->Cell(0, 6, $orgPostcode, 0, 1, 'C'); // Reduce the line height
$pdf->Cell(0, 6, $orgPhone, 0, 1, 'C'); // Reduce the line height
$pdf->Cell(0, 6, $orgEmail, 0, 1, 'C'); // Reduce the line height

// Dynamic Elements
// Assuming the dynamic data comes from some variables like:
$customerName = $customerForename . " " . $customerSurname;
$jobHouseNumName;
$jobStreetName;
$jobPostcode;
$jobPrice;

$items = [
    ['item' => 'Window Cleaning', 'price' => utf8_decode("£") . $invoicePrice, 'date' => $invoiceDate],

];

// Customer details
$pdf->SetFont('Arial', '', 10); // Change the font and size

$pdf->Cell(0, 6, "Customer:", 0, 1, 'L');
$pdf->Cell(0, 6, "$customerName", 0, 1, 'L');
$pdf->Cell(0, 6, "$jobHouseNumName", 0, 1, 'L');
$pdf->Cell(0, 6, "$jobStreetName", 0, 1, 'L');
$pdf->Cell(0, 6, "$jobPostcode", 0, 1, 'L');

// Line items and prices
$pdf->Cell(90, 10, 'Description', 1);
$pdf->Cell(40, 10, 'Price', 1);
$pdf->Cell(40, 10, 'Date', 1, 1); //move to nextrow of chart

foreach ($items as $item) {
    $pdf->Cell(90, 10, $item['item'], 1);
    $pdf->Cell(40, 10, $item['price'], 1);
    $pdf->Cell(40, 10, $item[$invoiceDate], 1);
}
$pdf->Output();
