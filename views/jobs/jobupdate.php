<?php
ob_start();
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('location: /index.html');
    exit;
}

include "../../includes/connectdb.php";
date_default_timezone_set('GMT');
$user = $_SESSION['name'];
$job_id = $_GET['id'];
$zone_id = $_GET['zone'];

$org_id = $_SESSION['org_id'];

// Get date of last clean from job_history
$stmtLastClean = $conn->prepare("SELECT * FROM job_history_org" . $org_id . " WHERE job_id = ?");
$stmtLastClean->bind_param("i", $job_id);
$stmtLastClean->execute();
$resultLastClean = $stmtLastClean->get_result();
$rowLastClean = $resultLastClean->fetch_assoc();
$dateLastDone = $rowLastClean["dateDone"];

// Get job details
$stmtJobData = $conn->prepare("SELECT * FROM job_org" . $org_id . " WHERE id = ?");
$stmtJobData->bind_param("i", $job_id);
$stmtJobData->execute();
$resultJobData = $stmtJobData->get_result();
$rowJobData = $resultJobData->fetch_assoc();

$job_id = $rowJobData["id"];
$house_num = $rowJobData["houseNumName"];
$street_name = $rowJobData["streetName"];
$price = $rowJobData["price"];
$dateNextDue = $rowJobData["dateNextDue"];
$job_frequency = $rowJobData["frequency"];
$info = $rowJobData["info"];
$latitude = $rowJobData["latitude"];
$longitude = $rowJobData["longitude"];

// Convert the date strings to English format
$dateNextDue = date("d/m/Y", strtotime($dateNextDue));
$dateLastDone = date("d/m/Y", strtotime($dateLastDone));

// Get zone name
$stmtZoneName = $conn->prepare("SELECT area FROM zone_org" . $org_id . " INNER JOIN job_org" . $org_id . " ON zone_org" . $org_id . ".id = job_org" . $org_id . ".zone_id WHERE job_org" . $org_id . ".id = ?");
$stmtZoneName->bind_param("i", $job_id);
$stmtZoneName->execute();
$resultZoneName = $stmtZoneName->get_result();
$rowZoneName = $resultZoneName->fetch_assoc();
$areanameprod = $rowZoneName["area"];

// Get payment type name
$stmtPaymentType = $conn->prepare("SELECT paymentType FROM paymentType WHERE id = ?");
$stmtPaymentType->bind_param("i", $rowJobData['paymentType_id']);
$stmtPaymentType->execute();
$resultPaymentType = $stmtPaymentType->get_result();
$rowPaymentType = $resultPaymentType->fetch_assoc();
$paymentType = $rowPaymentType['paymentType'];

if (isset($_GET['submit_button'])) {
    // Retrieve job frequency from the database
    $stmtFrequency = $conn->prepare("SELECT frequency FROM job_org" . $org_id . " WHERE id = ?");
    $stmtFrequency->bind_param("i", $job_id);
    $stmtFrequency->execute();
    $resultFrequency = $stmtFrequency->get_result();
    $rowFrequency = $resultFrequency->fetch_assoc();
    $job_frequency = $rowFrequency["frequency"];
    $job_frequency_days = $job_frequency * 7;

    // Update job status
    $stmtUpdateLastDone = $conn->prepare("UPDATE job_org" . $org_id . " SET dateLastDone = NOW() WHERE id = ?");
    $stmtUpdateLastDone->bind_param("i", $job_id);
    $stmtUpdateLastDone->execute();

    $stmtUpdateNextDue = $conn->prepare("UPDATE job_org" . $org_id . " SET dateNextDue = DATE_ADD(NOW(), INTERVAL ? DAY) WHERE id = ?");
    $stmtUpdateNextDue->bind_param("ii", $job_frequency_days, $job_id);
    $stmtUpdateNextDue->execute();

    // Insert job history (unpaid)
    $stmtInsertJobHistory = $conn->prepare("INSERT INTO job_history_org" . $org_id . " (job_id, dateDone, paid, price, completed_by, payment_type_id) VALUES (?, NOW(), 0, ?, ?, ?)");
    $stmtInsertJobHistory->bind_param("iisi", $job_id, $price, $user, $rowJobData['paymentType_id']);
    $stmtInsertJobHistory->execute();

    echo '<div class="alert alert-warning">
    <strong>Success!</strong> JOB COMPLETED UNPAID.
</div>';


    if (isset($_SESSION['zone_id'])) {
        $zone_id = $_SESSION['zone_id'];
        header("Refresh: 1; URL=jobzone.php?zone_id=" . $zone_id);
    } else {
        header("Refresh: 1; URL=jobs.php");
    }
}


if (isset($_GET['submit_two'])) {
    // Retrieve job frequency from the database
    $stmtFrequency = $conn->prepare("SELECT frequency FROM job_org" . $org_id . " WHERE id = ?");
    $stmtFrequency->bind_param("i", $job_id);
    $stmtFrequency->execute();
    $resultFrequency = $stmtFrequency->get_result();
    $rowFrequency = $resultFrequency->fetch_assoc();
    $job_frequency = $rowFrequency["frequency"];
    $job_frequency_days = $job_frequency * 7;

    // Update job status
    $stmtUpdateLastDone = $conn->prepare("UPDATE job_org" . $org_id . " SET dateLastDone = NOW() WHERE id = ?");
    $stmtUpdateLastDone->bind_param("i", $job_id);
    $stmtUpdateLastDone->execute();

    $stmtUpdateNextDue = $conn->prepare("UPDATE job_org" . $org_id . " SET dateNextDue = DATE_ADD(NOW(), INTERVAL ? DAY) WHERE id = ?");
    $stmtUpdateNextDue->bind_param("ii", $job_frequency_days, $job_id);
    $stmtUpdateNextDue->execute();

    // Insert job history (paid)
    $stmtInsertJobHistory = $conn->prepare("INSERT INTO job_history_org" . $org_id . " (job_id, dateDone, paid, price, completed_by, payment_type_id) VALUES (?, NOW(), 1, ?, ?, ?)");
    $stmtInsertJobHistory->bind_param("iisi", $job_id, $price, $user, $rowJobData['paymentType_id']);
    $stmtInsertJobHistory->execute();

    echo '<div class="alert alert-success">
    <strong>Success!</strong> JOB COMPLETED PAID.
</div>';


    if (isset($_SESSION['zone_id'])) {
        $zone_id = $_SESSION['zone_id'];
        header("Refresh: 1; URL=jobzone.php?zone_id=" . $zone_id);
    } else {
        header("Refresh: 1; URL=jobs.php");
    }
}


$conn->close();
ob_end_flush();
?>

<html>

<head>
    <meta charset="utf-8">
    <script type="text/javascript" src="/js/removeCompleteButtons.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"><!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> <!-- For hamburger menu -->

    <link href="/css/main.css" rel="stylesheet">
    <style>
        .card-custom {
            background-color: #f8f9fa; 
            border-color: #343a40;
        }
        .card-custom h1, h2 {
            color: #343a40;
        }
        .topnav {
            overflow: hidden;
            background-color: #333;
            position: relative;
        }

        /* Hide the links inside the navigation menu (except for logo/home) */
        .topnav #myLinks {
            display: none;
        }

        /* Style navigation menu links */
        .topnav a {
            color: white;
            padding: 14px 16px;
            text-decoration: none;
            font-size: 17px;
            display: block;
        }

        /* Style the hamburger menu */
        .topnav a.icon {
            background: black;
            display: block;
            position: absolute;
            right: 0;
            top: 0;
        }

        /* Add a grey background color on mouse-over */
        .topnav a:hover {
            background-color: #ddd;
            color: black;
        }

        /* Style the active link (or home/logo) */
        .active {
            /* background-color: #04AA6D; */
            color: white;
        }
    </style>

</head>

<body>
<!-- Top Navigation Menu -->
<div class="topnav" style="margin-bottom: 10px;">
        <a href="/views/dashboard.php" class="active">Winda Ninjas</a>
        <!-- Navigation links (hidden by default) -->
        <div id="myLinks">
            <a href="/views/jobs/jobs.php">All Jobs</a>
            <a href="/views/jobs/jobadd.php">Add Job</a>
            <a href="/views/manager/addzone.php">Add Zone</a>
            <a href="/views/manager/charts.php">Metrics</a>
            <a href="/views/manager/changepassword.php">Change Password</a>
            </a>
            </a>
            <a href="/views/manager/logout.php">Logout</a>
        </div>
        <!-- "Hamburger menu" / "Bar icon" to toggle the navigation links -->
        <a href="javascript:void(0);" class="icon" onclick="myFunction()">
            <i class="fa fa-bars"></i>
        </a>
    </div>
    <div class="row">
            <div class="col">
                    <h3><a href="jobhistory.php?id=<?php echo $job_id ?>"><i class="bi bi-calendar3" style="font-size: 3em; color: blue;"></i></a></h3>
                </div>
            <div class="col">
                <h3><a href="/views/maps/jobroute.php?id=<?php echo $job_id ?>"><i class="bi bi-geo-alt-fill" style="font-size: 3em; color: blue;"></i></a></h3>
            </div>
            <div class="col">
                <h3><a href="/views/customer/customer.php?id=<?php echo $job_id ?>"><i class="bi bi-person-fill" style="font-size: 3em; color: blue;"></i></a></h3>
            </div>
        </div>
        <div class="container">
            
            <div class="row">

                <div class="col-sm">
                    <?php
                    $jobcompleteunpaid = '<form action="jobupdate.php" method="get"><input type="hidden" name="id" value="' . $job_id . '"><input id="unpaid-button" class="btn btn-warning" type="submit" name="submit_button" value="JOB COMPLETE/UNPAID">';
                    echo $jobcompleteunpaid;
                    ?>
                    </form>
                </div>

                <div class="col-sm">
                    <div class="card-custom">
                        <div class="card-body" style="border: black">
                  

                        <h1><?php echo $house_num . " " . $street_name;?></h1>
                        <?php echo $latitude . $longitude?>
                        <table class="table table-hover">
                            <tr>
                                <th>Clean on:</th>
                                <td><?php echo $dateNextDue ?></td>
                            </tr>
                            <tr>
                                <th>Last cleaned:</th>
                                <td><?php echo $dateLastDone ?></td>
                            </tr>
                            <tr>
                                <th>Zone:</th>
                                <td><?php echo $areanameprod ?></td>
                            </tr>
                            <tr>
                                <th>Price:</th>
                                <td><?php echo "£" . $price ?></td>
                            </tr>
                            <tr>
                                <th>Frequency:</th>
                                <td><?php echo $job_frequency . " weeks" ?></td>
                            </tr>
                            <tr>
                                <th>Payment Type:</th>
                                <td><?php echo $paymentType ?></td>
                            </tr>
                            <tr>
                                <th>Job info:</th>
                                <td><?php echo $info ?></td>
                            </tr>
                        </table>
                    
                </div>
</div>
</div>
                <div class="col-sm">
                    <?php
                    $jobcompletepaid = '<form action="jobupdate.php" method="get"><input type="hidden" name="id" value="' . $job_id . '"><input class="btn btn-success" type="submit" name="submit_two" value="JOB COMPLETE/PAID"></form>';
                    echo $jobcompletepaid;
                    ?>
                </div>
            </div>
            <button onClick="location.href = 'jobedit.php?id=<?php echo $job_id ?>'; " class="btn btn-danger" type="button">EDIT JOB</button>
        </div>
</body>
<script>
    /* Toggle between showing and hiding the navigation menu links when the user clicks on the hamburger menu / bar icon */
    function myFunction() {
        var x = document.getElementById("myLinks");
        if (x.style.display === "block") {
            x.style.display = "none";
            document.getElementById("unpaid-button").style.display = "block";
        } else {
            x.style.display = "block";
            document.getElementById("unpaid-button").style.display = "none";
        }
    }
    </script>

</html>