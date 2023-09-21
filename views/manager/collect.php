<?php
ob_start();
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('location: /index.html');
    exit;
}

include "../../includes/connectdb.php";
$org_id = $_SESSION['org_id'];
date_default_timezone_set('GMT');
$sqlTotalUnpaid = "SELECT SUM(job_history_org" . $org_id . ".price) as totalUnpaid
                    FROM job_history_org" . $org_id . "
                    JOIN job_org" . $org_id . " ON job_org" . $org_id . ".id = job_history_org" . $org_id . ".job_id
                    WHERE job_history_org" . $org_id . ".paid = 0";

$stmtTotalUnpaid = $conn->prepare($sqlTotalUnpaid);
$stmtTotalUnpaid->execute();
$resultTotalUnpaid = $stmtTotalUnpaid->get_result();

if ($resultTotalUnpaid->num_rows > 0) {
    $rowTotalUnpaid = $resultTotalUnpaid->fetch_assoc();
    $totalUnpaid = $rowTotalUnpaid['totalUnpaid'];
} else {
    $totalUnpaid = 0;
}




?>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> <!-- For hamburger menu -->
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <link href="/css/main.css" rel="stylesheet">
    <style>
        tr:nth-child(even) {
            background-color: #D6EEEE;
        }

        .table-box {
            border: 1px solid #000;
            /* Border color */
            padding: 10px;
            /* Space between border and table */
            border-radius: 5px;
            /* Rounded corners */
            margin-bottom: 20px;
            /* Space beneath the box */
        }
              
    </style>
    <title>Unpaid Jobs</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>

<body>
    <!-- Top Navigation Menu -->
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
    <h1>£<?php echo $totalUnpaid; ?> to Collect</h1>
    <?php
    // Check if a job has been marked as paid
    if (isset($_GET['pay_job_id'])) {
        $jobHistoryId = $_GET['pay_job_id'];
        $sql = "UPDATE job_history_org" . $org_id . " SET paid = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $jobHistoryId);
        if ($stmt->execute()) {
            echo '<div class="alert alert-success">
                    <strong>Success!</strong> Job marked as paid.
                  </div>';
            header("Refresh: 1; URL=collect.php");
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }

    $sql = "SELECT job_history_org" . $org_id . ".id, job_org" . $org_id . ".id as job_id, job_org" . $org_id . ".houseNumName, job_org" . $org_id . ".streetName, job_history_org" . $org_id . ".dateDone, job_history_org" . $org_id . ".price, paymentType.paymentType, customer_org" . $org_id . ".phoneNumber, job_org" . $org_id . ".cust_id
    FROM job_history_org" . $org_id . "
    JOIN job_org" . $org_id . " ON job_org" . $org_id . ".id = job_history_org" . $org_id . ".job_id
    LEFT JOIN customer_org" . $org_id . " ON job_org" . $org_id . ".cust_id = customer_org" . $org_id . ".id
    LEFT JOIN paymentType ON job_org" . $org_id . ".paymentType_id = paymentType.id
    WHERE job_history_org" . $org_id . ".paid = 0";





    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo '<div class="table-box">';
        echo '<table class="table table-hover">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Job</th>';
        echo '<th>Date Completed</th>';
        echo '<th>Price</th>';
        echo '<th>Payment Type</th>';
        echo '<th>Actions</th>';
        echo '<th>Contact Customer</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        while ($row = $result->fetch_assoc()) {
            echo '<tr style="height:90px">';
            echo '<td>' . $row["houseNumName"] . '<br> ' . $row["streetName"] . '</td>';
            echo '<td>' . $row["dateDone"] . '</td>';
            echo '<td>£' . $row["price"] . '</td>';
            echo '<td>' . $row["paymentType"] . '</td>';
            echo '<td><a href="collect.php?pay_job_id=' . $row["id"] . '" class="btn btn-success">Mark as Paid</a></td>';
            echo '<td><a href="../customer/customer.php?id=' . $row["job_id"] . '" class="btn btn-warning">Customer </a></td>';



            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div> ';
    } else {
        echo 'No unpaid jobs found';
    }

    $stmt->close();
    $conn->close();
    ob_end_flush();
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script language="javascript">
        function myFunction() {
            var x = document.getElementById("myLinks");
            if (x.style.display === "block") {
                x.style.display = "none";

            } else {
                x.style.display = "block";
            }
        }
    </script>
</body>

</html>