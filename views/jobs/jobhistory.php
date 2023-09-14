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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
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
                    echo '</tr>';

                    while ($row = $result->fetch_assoc()) {
                        $agoodvariable = $row["id"];
                        echo '<tr>';
                        echo '<td>' . $row["dateDone"] . '</td>';
                        echo '<td>' . '£' . $row["price"] . '</td>';
                        echo '<td>' . ($row["paid"] ? 'Yes' : 'No') . '</td>';
                        echo '<td>' . getPaymentInfo($agoodvariable, $org_id, $conn) . '</td>'; // Display the Payment Type
                        if ($row["paid"] == 0) {
                            $jobpaid = '<form action="jobhistory.php" method="get"><input type="hidden" name="id" value="' . $jobtableid . '"><input type="hidden" name="jobHistoryId" value="' . $agoodvariable . '"><input class="btn btn-success" type="submit" name="submit_paid_' . $agoodvariable . '" value="PAID"></form>';
                            echo '<td>' . $jobpaid . '</td>';
                        }
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

                        if ($stmtPayJob->execute()) {
                            echo '<div class="alert alert-success">
                        <strong>Success!</strong> JOB COMPLETED PAID.
                      </div>';
                            header("Refresh: 1; URL=jobhistory.php?id=" . $job_id);
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

</body>

</html>