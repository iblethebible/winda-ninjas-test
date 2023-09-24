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


$jobHistoryId = $_GET['jobHistoryId'];

// SQL query to retrieve customer and job data
$query = "SELECT 
            c.id as customerId, c.forename, c.surname, c.email, c.phoneNumber,
            j.id as jobId, j.houseNumName, j.streetName, j.postcode,
            jh.price as jobHistoryPrice
          FROM job_history_org" . $org_id . " jh
          JOIN job_org" . $org_id . " j ON jh.job_id = j.id
          JOIN customer_org" . $org_id . " c ON j.cust_id = c.id
          WHERE jh.id = ?";



// Prepare the statement
$stmt = $conn->prepare($query);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

// Bind the job history ID parameter
$stmt->bind_param("i", $jobHistoryId);

// Execute the query
$stmt->execute();

// Get the result
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Fetch the data into variables
    $row = $result->fetch_assoc();

    // Store customer data
    $customerId = $row['customerId'];
    $customerForename = $row['forename'];
    $customerSurname = $row['surname'];
    $customerEmail = $row['email'];
    $customerPhoneNumber = $row['phoneNumber'];

    // Store job data
    $jobId = $row['jobId'];
    $jobHouseNumName = $row['houseNumName'];
    $jobStreetName = $row['streetName'];
    $jobPrice = $row['jobHistoryPrice'];
    $jobPostcode = $row['postcode'];
    // Add more job data columns as needed

    // Now you have the data in PHP variables for further processing
} else {
    echo "No data found for the specified job history ID.";
}


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


//Insert a new invoice record into the database
$insertInvoiceSql = "INSERT INTO invoices_org" . $org_id . " (customer_id, job_history_id, invoice_date, price) VALUES (?, ?, ?, ?)";
$stmtInsertInvoice = $conn->prepare($insertInvoiceSql);

if ($stmtInsertInvoice === false) {
    die("Prepare failed: " . $conn->error);
}

// Bind the parameters
$invoiceDate = date("Y-m-d");
$stmtInsertInvoice->bind_param("iiss", $customerId, $jobHistoryId, $invoiceDate, $jobPrice);



// Execute the query
$stmtInsertInvoice->execute();
$invoiceId = $conn->insert_id;  // Retrieve the auto-generated ID



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
    ['item' => 'Window Cleaning', 'price' => utf8_decode("£") . $jobPrice, 'date' => date('d/m/Y')],
    
];

// Customer details
$pdf->SetFont('Arial', '', 10); // Change the font and size

$pdf->Cell(0, 6, "Customer:", 0, 1, 'L');
$pdf->Cell(0, 6, "$customerName", 0, 1, 'L');
$pdf->Cell(0, 6, "$jobHouseNumName" ,0, 1, 'L');
$pdf->Cell(0, 6, "$jobStreetName" ,0, 1, 'L');
$pdf->Cell(0, 6, "$jobPostcode" ,0, 1, 'L');
$pdf->Cell(0, 10, "Invoice #: $invoiceId", 0, 1, 'L');

// Line items and prices
$pdf->Cell(90, 10, 'Description', 1);
$pdf->Cell(40, 10, 'Price', 1);
$pdf->Cell(40, 10, 'Date', 1,1);//move to nextrow of chart

foreach ($items as $item) {
    $pdf->Cell(90, 10, $item['item'], 1);
    $pdf->Cell(40, 10, $item['price'], 1); 
    $pdf->Cell(40, 10, $item['date'], 1);
}

// Generate PDF
$pdf->Output()
?>
