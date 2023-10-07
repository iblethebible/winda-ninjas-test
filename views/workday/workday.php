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

if (isset($_POST['mark_paid'])) {
    $history_id = $_POST['history_id'];

    // SQL query to mark the job as paid
    $sqlMarkPaid = "UPDATE job_history_org" . $org_id . " SET paid = 1 WHERE id = ?";

    $stmtMarkPaid = $conn->prepare($sqlMarkPaid);
    $stmtMarkPaid->bind_param("i", $history_id);

    if ($stmtMarkPaid->execute()) {
        // Job marked as paid successfully
        echo '<div class="alert alert-success">Job marked as paid.</div>';
        header("Refresh: 0; URL=workday.php");
    } else {
        // Error marking job as paid
        echo '<div class="alert alert-danger">Error marking job as paid: ' . $stmtMarkPaid->error . '</div>';
    }

    $stmtMarkPaid->close();
}

$date = new DateTime();
if (isset($_GET['date'])) {
    $date = DateTime::createFromFormat('Y-m-d', $_GET['date']);
}
$currentDate = $date->format('Y-m-d');

$sqlJobs = "SELECT job_org" . $org_id . ".houseNumName, job_org" . $org_id . ".streetName, job_history_org" . $org_id . ".price, job_history_org" . $org_id . ".paid, job_history_org" . $org_id . ".dateDone, job_history_org" . $org_id . ".id as history_id
            FROM job_history_org" . $org_id . "
            INNER JOIN job_org" . $org_id . " ON job_history_org" . $org_id . ".job_id = job_org" . $org_id . ".id
            WHERE dateDone = ?
            ORDER BY dateDone ASC";

$stmtJobs = $conn->prepare($sqlJobs);
$stmtJobs->bind_param("s", $currentDate);
$stmtJobs->execute();
$resultJobs = $stmtJobs->get_result();

$jobs = [];

while ($row = $resultJobs->fetch_assoc()) {
    $jobs[] = $row;
}
ob_end_flush();
?>
<html>

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> <!-- For hamburger menu -->


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <link href="/css/main.css" rel="stylesheet">
    <style>
        tr:nth-child(even) {
            background-color: #D6EEEE;
        }
    </style>
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
            <a href="/views/manager/logout.php">Logout</a>
        </div>
        <!-- "Hamburger menu" / "Bar icon" to toggle the navigation links -->
        <a href="javascript:void(0);" class="icon" onclick="myFunction()">
            <i class="fa fa-bars"></i>
        </a>
    </div>

    <div class="container mt-5">
        <form action="workday.php" method="get">
            <label for="date">Choose a date:</label>
            <input type="date" id="date" name="date" value="<?php echo $currentDate; ?>">
            <input type="submit" value="Submit">
        </form>

        <h1 class="mb-4">Jobs done on <?php echo date('d M, Y', strtotime($currentDate)); ?></h1>

        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">Address</th>
                    <th scope="col">Price</th>
                    <th scope="col">Paid</th>
                    <th scope="col">Invoice</th> 
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job) { ?>
                    <tr style="height:90px" onclick="location.href='workdayjobedit.php?history_id=<?php echo $job['history_id']; ?>'">
                        <td><?php echo $job['houseNumName']; ?><br>
                        <?php echo $job['streetName']; ?></td>
                        <td>£ <?php echo $job['price']; ?></td>
                        <td>
                            <?php if ($job['paid']) { ?>
                                <span class="text-success">&#10004;</span>
                            <?php } else { ?>
                                <form action="workday.php" method="post">
                                    <input type="hidden" name="history_id" value="<?php echo $job['history_id']; ?>">
                                    <input type="submit" name="mark_paid" value="Mark as Paid" class="btn btn-primary">
                                </form>
                            <?php } ?>
                        </td>
                        <td><a href="../invoicing/createInvoice.php?jobHistoryId=<?php echo $job['history_id']; ?>">Create Invoice</a></td>

                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Calculate and display the total value -->
        <?php
        $totalValue = 0;
        foreach ($jobs as $job) {
            $totalValue += $job['price'];
        }
        ?>

        <h2>Total Value: £<?php echo $totalValue; ?></h2>
    </div>
</body>
<script>
    /* Toggle between showing and hiding the navigation menu links when the user clicks on the hamburger menu / bar icon */
    function myFunction() {
        var x = document.getElementById("myLinks");
        if (x.style.display === "block") {
            x.style.display = "none";
        } else {
            x.style.display = "block";
        }
    }
</script>

</html>