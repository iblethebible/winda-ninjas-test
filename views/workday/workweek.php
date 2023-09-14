<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('location: /index.html');
    exit;
}

include "../../includes/connectdb.php";
$org_id = $_SESSION['org_id'];
date_default_timezone_set('GMT');
$date = new DateTime();

// get start of the week
$startOfWeek = $date->modify('Monday this week')->format('Y-m-d');

$sqlJobs = "SELECT job_org" . $org_id . ".houseNumName, job_org" . $org_id . ".streetName, job_history_org" . $org_id . ".price, job_history_org" . $org_id . ".paid, job_history_org" . $org_id . ".dateDone, job_history_org" . $org_id . ".id as history_id
            FROM job_history_org" . $org_id . "
            INNER JOIN job_org" . $org_id . " ON job_history_org" . $org_id . ".job_id = job_org" . $org_id . ".id
            WHERE dateDone BETWEEN ? AND CURDATE()
            ORDER BY dateDone ASC";

$stmtJobs = $conn->prepare($sqlJobs);
$stmtJobs->bind_param("s", $startOfWeek);
$stmtJobs->execute();
$resultJobs = $stmtJobs->get_result();

$jobs = [];

while ($row = $resultJobs->fetch_assoc()) {
    $jobs[] = $row;
}

?>

<html>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <link href="/css/main.css" rel="stylesheet">
    <style>
        tr:nth-child(even) {
            background-color: #D6EEEE;
        }
    </style>
</head>

<body>
    <div class="d-grid gap-3">
        <button onclick="location.href = '/views/dashboard.php' ; " class="btn btn-primary" type="button">Dashboard</button>
    </div>

    <div class="container mt-5">

        <h1 class="mb-4">Jobs done since the start of the week from <?php echo date('d M, Y', strtotime($startOfWeek)); ?></h1>

        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">House Num/Name</th>
                    <th scope="col">Street Name</th>
                    <th scope="col">Price</th>
                    <th scope="col">Paid</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($jobs as $job) { ?>
    <tr style="height:90px" onclick="location.href='workdayjobedit.php?history_id=<?php echo $job['history_id']; ?>'">
        <td><?php echo $job['houseNumName']; ?></td>
        <td><?php echo $job['streetName']; ?></td>
        <td>£ <?php echo $job['price']; ?></td>
        <td>
            <?php if ($job['paid']) { ?>
                <span class="text-success">&#10004;</span>
            <?php } else { ?>
                <form action="workweek.php" method="post">
                    <input type="hidden" name="history_id" value="<?php echo $job['history_id']; ?>">
                    <input type="submit" name="mark_paid" value="Mark as Paid" class="btn btn-primary">
                </form>
            <?php } ?>
        </td>
    </tr>
<?php } ?>

<?php
if (isset($_POST['mark_paid'])) {
    $history_id = $_POST['history_id'];

    // SQL query to mark the job as paid
    $sqlMarkPaid = "UPDATE job_history_org" . $org_id . " SET paid = 1 WHERE id = ?";

    $stmtMarkPaid = $conn->prepare($sqlMarkPaid);
    $stmtMarkPaid->bind_param("i", $history_id);

    if ($stmtMarkPaid->execute()) {
        // Job marked as paid successfully
        echo '<div class="alert alert-success">Job marked as paid.</div>';
        header("Refresh: 1; URL=workweek.php");
    } else {
        // Error marking job as paid
        echo '<div class="alert alert-danger">Error marking job as paid: ' . $stmtMarkPaid->error . '</div>';
    }

    $stmtMarkPaid->close();
}
?>

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

</html>
