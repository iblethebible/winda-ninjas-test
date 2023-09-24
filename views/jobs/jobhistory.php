<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('location: /index.html');
    exit;
}

include "../../includes/connectdb.php";
$org_id = $_SESSION['org_id'];

date_default_timezone_set('GMT');
$jobtableid = $_GET["id"];
$zone_id = $_GET['zone'];

$jobdatasql = "SELECT * FROM job_org" . $_SESSION['org_id'] . " WHERE id = ?";
$stmtJobData = $conn->prepare($jobdatasql);
$stmtJobData->bind_param("i", $jobtableid);
$stmtJobData->execute();
$result = $stmtJobData->get_result();
$row = $result->fetch_assoc();
$house_num = $row["houseNumName"];
$street_name = $row["streetName"];
$job_id = $row["id"];

function getPaymentInfo($jobHistoryId, $org_id, $conn)
{
    $sql = "SELECT paymentType.paymentType
            FROM job_history_org{$org_id}
            LEFT JOIN paymentType ON job_history_org{$org_id}.payment_type_id = paymentType.id
            WHERE job_history_org{$org_id}.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $jobHistoryId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['paymentType'];
    }

    return "N/A"; // Return "N/A" if payment information is not found
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <script type="text/javascript" src="/js/removeCompleteButtons.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <script src="jquery-3.6.0.min.js"></script>
    <link href="/css/main.css" rel="stylesheet">
    <style>
    tr:nth-child(even) {
        background-color: #D6EEEE;
    }

    .card-custom {
        background-color: #f8f9fa;
        border-color: #343a40;
    }

    .card-custom h1,
    h2 {
        color: #343a40;
    }
    </style>
    <title>Winda Ninjas</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>

<body>
<div class="topnav">
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
    <div class="container mt-5">
        <div class="card card-custom">
            <div class="card-body">
                <h1>Job history <br><?php echo $house_num . " " . $street_name ?></h1>

                <?php
                $jobhistorysql = "SELECT * FROM job_history_org" . $org_id . " WHERE job_id = ?";
                $stmtJobHistory = $conn->prepare($jobhistorysql);
                $stmtJobHistory->bind_param("i", $job_id);
                $stmtJobHistory->execute();
                $result = $stmtJobHistory->get_result();

                if ($result->num_rows > 0) {
                    echo '<div class="table-responsive">'; // Add a div around the table to make it scrollable on mobile devices
                    echo '<table class="table table-hover">';
                    echo '<tr>';
                    echo '<th>Date Completed</th>';
                    echo '<th>Price</th>';
                    echo '<th>Paid</th>';
                    echo '<th>Payment Type</th>'; // Add a new column for Payment Type
                    echo '<th>Create Invoice</th>';
                    echo '</tr>';

                    while ($row = $result->fetch_assoc()) {
                        $agoodvariable = $row["id"];
                        echo '<tr>';
                        echo '<td>' . $row["dateDone"] . '</td>';
                        echo '<td>' . '£' . $row["price"] . '</td>';
                        echo '<td>';

                        if ($row["paid"]) {
                            // Display a tick mark or any other symbol to indicate the job has been paid.
                            echo '&#10004;'; // This is a checkmark symbol
                        } else {
                            // Display the "Paid" button
                            $jobpaid = '<form action="jobhistory.php" method="get"><input type="hidden" name="id" value="' . $jobtableid . '"><input type="hidden" name="jobHistoryId" value="' . $agoodvariable . '"><input class="btn btn-success" type="submit" name="submit_paid_' . $agoodvariable . '" value="PAID"></form>';
                            echo $jobpaid;
                        }

                        echo '</td>';
                        echo '<td>' . getPaymentInfo($agoodvariable, $org_id, $conn) . '</td>'; // Display the Payment Type

                        // Add a button to create an invoice
                        echo '<td><a href="../invoicing/createInvoice.php?jobHistoryId=' . $agoodvariable . '">Create Invoice</a></td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                    echo '</div>';
                } else {
                    echo '0 results or error';
                }

                foreach ($_GET as $key => $value) {
                    if (strpos($key, 'submit_paid_') === 0) {
                        $jobHistoryId = $_GET['jobHistoryId'];
                        $sqltopayforjob = "UPDATE job_history_org" . $org_id . " SET paid=1 WHERE id = ?";
                        $stmtPayJob = $conn->prepare($sqltopayforjob);
                        $stmtPayJob->bind_param("i", $jobHistoryId);


                        $refreshed = false; // Set the variable to false to indicate that the page hasn't been refreshed yet


                        if ($stmtPayJob->execute()) {
                            echo '<div class="alert alert-success">
                        <strong>Success!</strong> JOB COMPLETED PAID.
                      </div>';
                        }
                        // Check if the page hasn't been refreshed yet
                        if (!$refreshed) {
                            echo '<script>
                    setTimeout(function() {
                        window.location.href = "jobhistory.php?id=' . $job_id . '";
                    }, 1000);
                </script>';
                            $refreshed = true; // Set the variable to true to indicate that the refresh has occurred
                        } else {
                            echo "You've messed this up";
                        }
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <h2><a href="jobupdate.php?id=<?php echo $jobtableid ?>">Return</a></h2>

    <?php
    $conn->close();
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>

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
