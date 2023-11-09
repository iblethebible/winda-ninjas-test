<?php

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
?>

<html>

<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> <!-- For hamburger menu -->

    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
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

        .topnav {
            overflow: hidden;
            background-color: #333;
            position: relative;
        }

        /* Hide the links inside the navigation menu (except for logo/home) */
        .topnav #myLinks {
            display: none;
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
            <a href="/views/manager/logout.php">Logout</a>
        </div>
        <!-- "Hamburger menu" / "Bar icon" to toggle the navigation links -->
        <a href="javascript:void(0);" class="icon" onclick="myFunction()">
            <i class="fa fa-bars"></i>
        </a>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-sm" id="search">
                <form action="jobs.php" method="get">
                    <input class="form-control" type="text" name="search" placeholder="Search by street/house name">
                    <input class="btn btn-primary" type="submit" name="submit_button" value="Search/Refresh search">
                </form>
            </div>

        </div>
        <br>
        <?php
        if (isset($_GET['submit_button'])) {
            $search = '%' . $_GET['search'] . '%';
            $stmtSearch = $conn->prepare("SELECT * FROM job_org" . $org_id . " WHERE houseNumName LIKE ? OR streetName LIKE ? ORDER BY dateNextDue ASC");
            $stmtSearch->bind_param("ss", $search, $search);
            $stmtSearch->execute();
            $resultSearch = $stmtSearch->get_result();

            if ($resultSearch->num_rows > 0) {
                echo '<table class="table-box">';
                echo '<table class="table table-hover">';
                echo '<tr>';
                echo '<th>House</th>';
                echo '<th>Price</th>';
                echo '<th><b>Next Due</b></th>';
                echo '</tr>';

                while ($row = $resultSearch->fetch_assoc()) {
                    echo '<tr style="height:90px" onclick="location.href=\'jobupdate.php?id=' . $row["id"] . '\'">';
                    echo '<td>' . $row["houseNumName"] . '<br>';
                    echo $row["streetName"] . '</td>';
                    echo '<td>£' . $row["price"] . '</td>';
                    echo '<td>' . $row["frequency"] . ' Weeks</td>';
                    echo '<td>' . date_format(date_create_from_format('Y-m-d', $row["dateNextDue"]), 'd/m/Y') . '</td>';                    echo '</tr>';
                }
                echo '</table>';
                echo '</table>';
            } else {
                echo '<p>No results found.</p>';
            }
        }



        $stmtJobs = $conn->prepare("SELECT * FROM job_org" . $org_id . " ORDER BY dateNextDue ASC");
        $stmtJobs->execute();
        $resultJobs = $stmtJobs->get_result();

        if ($resultJobs->num_rows > 0) {
            echo '<table class="table-box">';
            echo '<table class="table table-hover">';
            echo '<tr>';
            echo '<th>House</th>';
            echo '<th>Price</th>';
            echo '<th><b>Next Due</b></th>';

            echo '</tr>';

            while ($row = $resultJobs->fetch_assoc()) {
                echo '<tr style="height:90px" onclick="location.href=\'jobupdate.php?id=' . $row["id"] . '\'">';
                echo '<td>' . $row["houseNumName"] . '<br>';
                echo $row["streetName"] . '</td>';
                echo '<td>£' . $row["price"] . '</td>';
                echo '<td>' . date_format(date_create_from_format('Y-m-d', $row["dateNextDue"]), 'd/m/Y') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</table>';
        } else {
            echo '<p>No jobs found.</p>';
        }
        ?>

    </div>

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