<?php
ob_start();
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('location: /index.html');
    exit;
}

?>
<?php

$job_id = $_GET['id'];
$delete = $_GET["delete"];
date_default_timezone_set('GMT');
include "../../includes/connectdb.php";
$org_id = $_SESSION['org_id'];
if ($delete == "true") {
    $stmtDeleteJob = $conn->prepare("DELETE FROM job_org" . $_SESSION['org_id'] . " WHERE id = ?");
    $stmtDeleteJob->bind_param("i", $job_id);

    if ($stmtDeleteJob->execute()) {
        echo '<div class="alert alert-success">
                <strong>Success!</strong> JOB DELETED.
              </div>';
              if(isset($_SESSION['zone_id'])) {
                $zone_id = $_SESSION['zone_id'];
                header("Refresh: 1; URL=jobzone.php?zone_id=".$zone_id);
            } else {
                header("Refresh: 1; URL=jobs.php");
            }
    } else {
        echo "ERROR: " . $stmtDeleteJob->error . "</p>";
    }
}
ob_end_flush();
?>
<html>

<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <link href="/css/main.css" rel="stylesheet">
    <title>Winda Ninjas</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>

<body>
    <div class="d-grid gap-3">
        <button onClick="location.href = 'dashboard.php' ; " class="btn btn-primary" type="button">Dashboard</button>

    </div>
    <?php
    $stmtJob = $conn->prepare("SELECT houseNumName, streetName FROM job_org" . $_SESSION['org_id'] . " WHERE id = ?");
    $stmtJob->bind_param("i", $job_id);
    $stmtJob->execute();
    $resultJob = $stmtJob->get_result();
    $row = $resultJob->fetch_assoc();
    ?>
    <h1>Delete job: <?php echo $row["houseNumName"] . " " . $row["streetName"] ?></h1>
    <div class="alert alert-danger" role="alert">
        Are you sure? This is permanent!
    </div>

    <br>
    <a href="jobdelete.php?id=<?php echo $job_id ?>&delete=true">DELETE JOB</a>
</body>

</html>
