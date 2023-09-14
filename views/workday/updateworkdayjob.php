<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('location: /index.html');
    exit;
}

include "../../includes/connectdb.php";
$org_id = $_SESSION['org_id'];

date_default_timezone_set('GMT');
$history_id = $_POST['history_id'];
$price = $_POST['price'];
$paid = isset($_POST['paid']) ? 1 : 0; // check if checkbox 'paid' is checked
$dateDone = $_POST['dateDone'];

$sql = "UPDATE job_history_org" . $org_id . " SET price = ?, paid = ? WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $price, $paid, $history_id);

if ($stmt->execute()) {
    $dateDone = date('Y-m-d', strtotime($_POST['dateDone']));
    header("location: workday.php?date={$dateDone}");
} else {
    echo "Error updating record: " . $conn->error;
}
$stmt->close();
$conn->close();
