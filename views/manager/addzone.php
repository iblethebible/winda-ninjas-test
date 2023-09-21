<?php
ob_start();
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('location: /index.html');
    exit;
}

include "../../includes/connectdb.php";
$org_id = $_SESSION['org_id'];

if (isset($_POST["add_zone_submit"])) {
    $zone_name = $_POST["zone_name"];

    $stmtAddZone = $conn->prepare("INSERT INTO zone_org" . $org_id . " (area) VALUES (?)");
    $stmtAddZone->bind_param("s", $zone_name);

    if ($stmtAddZone->execute()) {
        echo '<div class="alert alert-success">
            <strong>Success!</strong> Zone added.
          </div>';
        header('Refresh: 1; URL=addzone.php');
    } else {
        echo "Error: " . $conn->error;
    }
}
ob_end_flush();
?>

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
                <form action="addzone.php" method="post">
                    <div class="mb-3">
                        <input type="text" name="zone_name" class="form-control" placeholder="Zone Name" required>
                    </div>
                    <div class="mb-3">
                        <input type="submit" name="add_zone_submit" value="Add Zone" class="btn btn-primary">
                    </div>
                </form>
            </div>
        </div>



    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
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