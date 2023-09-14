<?php
ob_start();
session_start();
error_reporting(0);
ini_set('display_errors', 0);

if (!isset($_SESSION['loggedin'])) {
    header('location: /index.html');
    exit;
}

include "../../includes/connectdb.php";
date_default_timezone_set('GMT');
$org_id = $_SESSION['org_id'];
$zone_id = $_SESSION['zone_id'];


//get zone name for title
$stmtZone = $conn->prepare("SELECT area FROM zone_org" . $org_id . " WHERE id = ?");
$stmtZone->bind_param("i", $zone_id);
$stmtZone->execute();
$resultZone = $stmtZone->get_result();
$zoneName = 'Unknown Zone';
if ($resultZone->num_rows > 0) {
    $rowZone = $resultZone->fetch_assoc();
    $zoneName = $rowZone['area'];
}

if (isset($_POST['zone'])) {
    $_SESSION['zone_id'] = $_POST['zone'];
    header("Location: " . $_SERVER['REQUEST_URI']); // Add this line to refresh the page    exit;
}
ob_end_flush();
?>
<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> <!-- For hamburger menu -->
    <link href="/css/main.css" rel="stylesheet">
    <style>
        tr:nth-child(even) {
            background-color: #D6EEEE;
        }

        .form-section {
            margin-top: 10px;
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
               /* Style the navigation menu */
               .topnav {
            overflow: hidden;
            background-color: #333;
            position: relative;
        }

        .zone-label {
            font-size: 22px;
        }

        .dropdown {
            width: 100%;
            height: 80px;
            border: 2px solid #007bff;
            border-radius: 5px;
            background: #ffffff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='4' height='5' viewBox='0 0 4 5'%3E%3Cpath fill='%23007bff' d='M2 0L0 2h4L2 0zm0 5L0 3h4l-2 2z'/%3E%3C/svg%3E") no-repeat right 0.75rem center;
            background-size: 1.5rem;
            padding-right: 2rem;
            /* space for the icon */
            color: #007bff;
            padding: 10px;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            font-size: 25px;
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
    <title>Winda Ninjas</title>
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
    <div class="container">
        <div class="row">
        </div>
        <div class="row">
        <div class="col-md-3">
                <div class="form-section">
                    <form action="" method="post" id="zone-form">
                        <?php
                        $stmtZones = $conn->prepare("SELECT id, area FROM zone_org" . $org_id);
                        $stmtZones->execute();
                        $resultZones = $stmtZones->get_result();

                        if ($resultZones->num_rows > 0) {
                            echo '<select name="zone" id="zone" class="form-control dropdown">';

                            while ($row = $resultZones->fetch_assoc()) {
                                $zoneId = $row['id'];
                                $zoneName = $row['area'];
                                if ($zoneId == $_SESSION['zone_id']) {
                                    echo '<option value="' . $zoneId . '" selected>' . $zoneName . '</option>';
                                } else {
                                    echo '<option value="' . $zoneId . '">' . $zoneName . '</option>';
                                }
                            }

                            echo '</select>';
                        } else {
                            echo 'No zones found.';
                        }
                        ?>
                    </form>
                </div>
            </div>
            <div class="col-md-8">
                <div>
                    <!-- First Form -->
                    <div class="form-section">
                        <form action="jobzone.php" method="get">
                            <input class="form-control" type="text" name="search" placeholder="Search by street/house name">
                            <input class="btn btn-primary" type="submit" name="submit_button" value="Search/Refresh search">
                        </form>
                    </div>
                    <!-- Second Form -->

                </div>
            </div>
            <div class="col-md-1">
                <!--spacer-->
            </div>
           
        </div>
        <br>
        <?php
        if (isset($_GET['submit_button'])) {
            $search = '%' . $_GET['search'] . '%';
            $stmtSearch = $conn->prepare("SELECT * FROM job_org" . $org_id . " WHERE (houseNumName LIKE ? OR streetName LIKE ?) AND zone_id = ? ORDER BY dateNextDue ASC");
            $stmtSearch->bind_param("ssi", $search, $search, $zone_id);
            $stmtSearch->execute();
            $resultSearch = $stmtSearch->get_result();

            if ($resultSearch->num_rows > 0) {
                echo '<div class="table-box">';
                echo '<table class="table table-hover">';
                echo '<tr>';
                echo '<th>House Name</th>';
                echo '<th>Price</th>';
                echo '<th><b>Next Due</b></th>';
                echo '</tr>';

                while ($row = $resultSearch->fetch_assoc()) {
                    echo '<tr style="height:90px" onclick="location.href=\'jobupdate.php?id=' . $row["id"] . '\'">';

                    echo '<td>' . $row["houseNumName"] . '<br>' . $row["streetName"] . '</td>';                    echo '<td>£' . $row["price"] . '</td>';
                    $dateNextDue = date_create($row["dateNextDue"]);
                    echo '<td><b>' . date_format($dateNextDue, 'd/m/Y') . '</b></td>';
                    echo '</tr>';
                }

                echo '</table>';
                echo '</table>';
                
            } else {
                echo 'No results found in selected zone <br> press search to refresh page.';
            }
        } else {
            $stmtJob = $conn->prepare("SELECT * FROM job_org" . $org_id . " WHERE zone_id = ? ORDER BY dateNextDue ASC");
            $stmtJob->bind_param("i", $zone_id);
            $stmtJob->execute();
            $resultJob = $stmtJob->get_result();

            if ($resultJob->num_rows > 0) {
                echo '<div class="table-box">';
                echo '<table class="table table-hover">';
                echo '<tr>';
                echo '<th>House</th>';
                echo '<th>Price</th>';
                echo '<th><b>Next Due</b></th>';
                echo '</tr>';

                while ($row = $resultJob->fetch_assoc()) {
                    echo '<tr style="height:90px" onclick="location.href=\'jobupdate.php?id=' . $row["id"] . '\'">';
                    echo '<td>' . $row["houseNumName"] . '<br>' . $row["streetName"] . '</td>';
                    echo '<td>£' . $row["price"] . '</td>';
                    $dateNextDue = date_create($row["dateNextDue"]);
                    echo '<td><b>' . date_format($dateNextDue, 'd/m/Y') . '</b></td>';
                    echo '</tr>';
                }

                echo '</table>';
                echo '</table>';
            } else {
                echo 'No jobs found.';
            }
        }

        ?>
    </div>

    <script>
        document.getElementById('zone').addEventListener('change', function() {
            document.getElementById('zone-form').submit();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
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
</body>

</html>