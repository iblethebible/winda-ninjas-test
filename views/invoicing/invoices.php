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

$org_id = $_SESSION['org_id'];

// Query to fetch invoices, customer names, and job addresses
$query = "SELECT i.invoice_id AS invoice_id, i.invoice_date, 
          jh.id AS job_history_id,  
          c.id AS customer_id,  
          CONCAT(c.forename, ' ', c.surname) AS customer_name,
          CONCAT(j.houseNumName, ', ', j.streetName, ', ', j.postcode) AS job_address
          FROM invoices_org" . $org_id . " i
          JOIN customer_org" . $org_id . " c ON i.customer_id = c.id
          JOIN job_history_org" . $org_id . " jh ON i.job_history_id = jh.id
          JOIN job_org" . $org_id . " j ON jh.job_id = j.id
          ORDER BY i.invoice_date DESC";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Invoice ID</th><th>Invoice Date</th><th>Customer Name</th><th>Job Address</th><th>View</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['invoice_id'] . "</td>";
        echo "<td>" . $row['invoice_date'] . "</td>";
        echo "<td>" . $row['customer_name'] . "</td>";
        echo "<td>" . $row['job_address'] . "</td>";
        // Pass both invoice_id and job_history_id as query parameters
        echo "<td><a href='viewInvoice.php?invoiceId=" . $row['invoice_id'] . "&jobHistoryId=" . $row['job_history_id'] . "&customerId=" . $row['customer_id'] . "'>View</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No invoices found.";
}

$conn->close();
