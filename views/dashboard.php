<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('location: /index.html');
    exit;
}
include "../includes/connectdb.php";
date_default_timezone_set('GMT');
$user = $_SESSION['name'];
$org_id = $_SESSION['org_id'];
$zone_id = $_SESSION['zone_id'];


if (!isset($_SESSION['zone_id'])) {
    // Get the zone ID from the zone table
    $sql = "SELECT id FROM zone_org" . $org_id . " ORDER BY id ASC LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Assign the zone ID to the session variable
        $_SESSION['zone_id'] = $row['id'];
    } else {
        echo "No records found in the zone table.";
        exit;
    }
}




// Define table suffix
$tableSuffix = "_org" . $org_id;
$org_id = $_SESSION['org_id'];

$role_id = $_SESSION['role_id'];



?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> <!-- For hamburger menu -->

    <style>
        .cards {
            border: 2px solid #007bff;
            border-radius: 5px;
            background-color: #e1eded;
        }

        /* Style the navigation menu */
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

</head>

<body>
    <container>
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
        <div class="container mt-5">
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-body cards">
                            <a href="jobs/jobzone.php" style="text-decoration: none;">
                                <h5 class="card-title"><b>Zone</b></h5>
                                <?php
                                $stmtZoneName = $conn->prepare("SELECT area FROM zone_org" . $org_id . " WHERE id = ?");
                                $stmtZoneName->bind_param("i", $_SESSION['zone_id']);
                                $stmtZoneName->execute();
                                $resultZoneName = $stmtZoneName->get_result();
                                $zoneName = '';

                                if ($resultZoneName->num_rows > 0) {
                                    $rowZoneName = $resultZoneName->fetch_assoc();
                                    $zoneName = $rowZoneName['area'];
                                }


                                echo '<h1>' . $zoneName . '</h1>'; // Echo the zone name retrieved from the database
                                ?>
                            </a>
                        </div>
                    </div>

                </div>

                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-body cards">
                            <a href="workday/workday.php" style="text-decoration: none; color: inherit;">
                                <h5 class="card-title"><b>Worked Today</b></h5>
                                <?php
                                $sqlTotalWork = "SELECT SUM(price) AS totalWork FROM job_history{$tableSuffix} WHERE dateDone = CURDATE()";
                                $stmtTotalWork = $conn->prepare($sqlTotalWork);
                                $stmtTotalWork->execute();
                                $resultTotalWork = $stmtTotalWork->get_result();
                                $rowTotalWork = $resultTotalWork->fetch_assoc();
                                $totalWork = $rowTotalWork['totalWork'] ?? 0;
                                echo "<h1>£ " . $totalWork . "</h1>";
                                ?>
                        </div>
                    </div>

                </div>

            </div>
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <a href="maps/map.php" style="text-decoration: none; color: inherit;">
                        <div class="card mb-4">
                            <div class="card-body cards">
                                <h5 class="card-title"><b>Map</b></h5>
                                <div id="preview-map" style="width: 100%; height: 150px;"></div>
                            </div>


                    </a>
                </div>
            </div>
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <script>
                var previewMap = L.map('preview-map', {
                    dragging: false,
                    touchZoom: false,
                    doubleClickZoom: false,
                    scrollWheelZoom: false,
                    boxZoom: false,
                    keyboard: false
                }).setView([52.697446, -2.7310085], 13); // set initial map center and zoom level

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 5,
                }).addTo(previewMap);
            </script>
        </div>
        <div class="row">
            <div class="col-md-6">
                <a href="manager/collect.php" style="text-decoration: none; color: inherit;">
                    <div class="card mb-4">
                        <div class="card-body cards">
                            <h5 class="card-title"><b>Collect</b></h5>
                            <?php
                            // Count the number of completed jobs that still need to be paid
                            $sqlUnpaidJobs = "SELECT COUNT(*) as unpaidJobs FROM job_history{$tableSuffix} WHERE paid = 0";
                            $stmtUnpaidJobs = $conn->prepare($sqlUnpaidJobs);
                            $stmtUnpaidJobs->execute();
                            $resultUnpaidJobs = $stmtUnpaidJobs->get_result();
                            $rowUnpaidJobs = $resultUnpaidJobs->fetch_assoc();
                            $unpaidJobs = $rowUnpaidJobs['unpaidJobs'];

                            // Now get the total price of the jobs that still need to be paid
                            $sqlUnpaidTotal = "SELECT SUM(price) as total FROM job_history{$tableSuffix} WHERE paid = 0";
                            $stmtUnpaidTotal = $conn->prepare($sqlUnpaidTotal);
                            $stmtUnpaidTotal->execute();
                            $resultUnpaidTotal = $stmtUnpaidTotal->get_result();
                            $rowUnpaidTotal = $resultUnpaidTotal->fetch_assoc();
                            $unpaidTotal = $rowUnpaidTotal['total'];

                            echo "<h1>£{$unpaidTotal} in {$unpaidJobs} jobs</h1>";
                            ?>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6">
                <a href="/views/manager/earningsreports.php" style="text-decoration: none; color: inherit;">
                    <div class="card mb-4">
                        <div class="card-body cards">
                            <h5><b>Metrics</b></h5>
                            <h1>Charts</h1>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        </div>

        <script src="https://unpkg.com/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js"></script>

        <script>
            var myCarousel = document.querySelector('#carousel1')
            var carousel = new bootstrap.Carousel(myCarousel)
        </script>
        <script>
            var myCarousel = document.querySelector('#carousel2')
            var carousel = new bootstrap.Carousel(myCarousel)
        </script>
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
        <?php include '../includes/footer.php'; ?>
    </container>
</body>
<!-- At the end of your body tag -->



</html>