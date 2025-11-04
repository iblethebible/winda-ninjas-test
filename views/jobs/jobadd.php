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
require '/var/www/html/vendor/autoload.php';

// use Aws\LocationService\LocationServiceClient;

// $locationClient = new LocationServiceClient([
//     'region'  => 'eu-west-2',
//     'version' => 'latest',
//     'key' => getenv('AWS_ACCESS_KEY_ID'),
//     'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
// ]);
date_default_timezone_set('GMT');
$org_id = $_SESSION['org_id'];

if (isset($_GET["job_add_submit"])) {
    $var_house_num = $_GET["house_num_form"];
    $var_street = $_GET["street_form"];
    $var_price = $_GET["price_form"];
    $var_frequency = $_GET["frequency_form"];
    $var_zone = $_GET["zone_form"];
    $var_payment = $_GET["paymentType_form"];
    $var_info = $_GET["info_form"];
    $var_postcode = $_GET["postcode_form"];
    $dateNextDue = date("Y-m-d H:i:s"); // get the current date/time

    $selected_zone_id = $var_zone;

    // Loop through the zone table to find the corresponding zone name
    $zone_name = '';
    $sql = "SELECT area FROM zone_org" . $org_id . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $selected_zone_id);
    $stmt->execute();
    $stmt->bind_result($zone_name);
    $stmt->fetch();
    $stmt->close();

//     // Sending the address information to geocoder.php
// // Sending the address information to geocoder.php
// $address = $var_house_num . ', ' . $var_street . ', ' . $zone_name . ' , ' . $var_postcode;
// echo "Address: " . $address;
// $geocodeURL = "https://windaninjas.ddns.net/views/maps/geocoder.php?address=" . urlencode($address);
// $geocodeResult = file_get_contents($geocodeURL);
// $geocodeData = json_decode($geocodeResult, true);

// // Initialize default values for latitude and longitude
// $latitude = 0;
// $longitude = 0;

// // Update latitude and longitude if geocoding is successful
// if ($geocodeData && $geocodeData['status'] === 'OK') {
//     $latitude = $geocodeData['latitude'] ?? $latitude;
//     $longitude = $geocodeData['longitude'] ?? $longitude;
// }

// // Proceed to add the job with either geocoded or default coordinates
// $stmtAddJob = $conn->prepare("INSERT INTO job_org" . $org_id . " (houseNumName, streetName, postcode, price, frequency, zone_id, paymentType_id, info, longitude, latitude, dateNextDue) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

// if (!$stmtAddJob) {
//     die("Prepare failed: (" . $conn->error . ") " . $conn->error);
// }

$stmtAddJob->bind_param("ssssissss", $var_house_num, $var_street, $var_postcode, $var_price, $var_frequency, $var_zone, $var_payment, $var_info, $dateNextDue);

if ($stmtAddJob->execute()) {
    echo '<div class="alert alert-success">
        <strong>Success!</strong> Job record added.
    </div>';
    header('Refresh: 1; URL=jobadd.php');
} else {
    echo "Error: " . $conn->error;
}
}

ob_end_flush();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> <!-- For hamburger menu -->

    <title>Winda Ninjas</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link href="/css/main.css" rel="stylesheet">
</head>

<body>
<div class="topnav">
        <a href="/views/dashboard.php" class="active">Winda Ninjas</a>
        <!-- Navigation links (hidden by default) -->
        <div id="myLinks">
            <a href="/views/jobs/jobs.php">All Jobs</a>
            <a href="/views/manager/logout.php">Logout</a>
        </div>
        <!-- "Hamburger menu" / "Bar icon" to toggle the navigation links -->
        <a href="javascript:void(0);" class="icon" onclick="myFunction()">
            <i class="fa fa-bars"></i>
        </a>
    </div>
    <div class="container mt-5">
        <div class="card card-custom">
        <div class="card-body">

        
        <div class="mb-3">
            <form action="jobadd.php" method="get">
                <input type="text" name="house_num_form" placeholder="House Number/Name" required>
        </div>
        <div class="mb-3">
            <input type="text" name="street_form" placeholder="Street Name" required>
        </div>
        <div class="mb-3">
            <input type="text" name="postcode_form" placeholder="Post Code">
        </div>
        <div class="mb-3">
            <input type="number" name="price_form" placeholder="Price">
        </div>
        <div class="mb-3">
            <input type="number" name="frequency_form" placeholder="Frequency in weeks(number)">
        </div>
        <div class="mb-3" id="areaselect">
            <?php
            $stmtZones = $conn->prepare("SELECT id, area FROM zone_org" . $org_id);
            $stmtZones->execute();
            $resultZones = $stmtZones->get_result();

            if ($resultZones->num_rows > 0) {
                echo '<select class="form-select" name="zone_form" required>';
                echo '<option value="" selected disabled>Select Zone</option>';

                while ($row = $resultZones->fetch_assoc()) {
                    $zoneId = $row['id'];
                    $zoneName = $row['area'];
                    echo '<option value="' . $zoneId . '">' . $zoneName . '</option>';
                }

                echo '</select>';
            } else {
                echo 'No zones found.';
            }
            ?>
        </div>
        <div class="mb-3">
            <label for="paymentType" class="form-label">Payment Type</label>
            <select class="form-select" id="paymentType" name="paymentType_form">
                <?php
                $sqlPaymentTypes = "SELECT id, paymentType FROM paymentType";
                $resultPaymentTypes = $conn->query($sqlPaymentTypes);

                if ($resultPaymentTypes->num_rows > 0) {
                    while ($row = $resultPaymentTypes->fetch_assoc()) {
                        $paymentTypeId = $row['id'];
                        $paymentType = $row['paymentType'];
                        echo '<option value="' . $paymentTypeId . '">' . $paymentType . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <input type="text" name="info_form" placeholder="Information">
        </div>
        <div class="mb-3">
            <input type="submit" name="job_add_submit" value="Add Job">
        </div>

    </form>
    </div>
        </div>
    </div>
    

    <!-- Display the recent jobs table -->
    <h3>Recently Added Jobs</h3>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>House Number/Name</th>
                <th>Street Name</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmtRecentJobs = $conn->prepare("SELECT * FROM job_org" . $org_id . " ORDER BY id DESC LIMIT 3");
            $stmtRecentJobs->execute();
            $resultRecentJobs = $stmtRecentJobs->get_result();

            while ($rowRecentJobs = $resultRecentJobs->fetch_assoc()) {
                $recentJobID = $rowRecentJobs['id']; // Retrieve the job ID
                $recentHouseNum = $rowRecentJobs['houseNumName'];
                $recentStreetName = $rowRecentJobs['streetName'];
                // Get other job details as needed

                // Create table row for each recent job with a link to job update page
                echo '<tr style="height:90px" onclick="location.href=\'jobupdate.php?id=' . $recentJobID . '\'">';
                echo '<td>' . $recentHouseNum . '</td>';
                echo '<td>' . $recentStreetName . '</td>';
                // Add more cells for other job details
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
<script>
    function myFunction() {
        var x = document.getElementById("myLinks");
        if (x.style.display === "block") { // If the navigation menu is displayed
            x.style.display = "none"; // Hide the navigation menu
        } else {
            x.style.display = "block"; // Display the navigation menu
        }
    }
    </script>
</html>