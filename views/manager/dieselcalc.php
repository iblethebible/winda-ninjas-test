<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Home</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link href="/css/main.css" rel="stylesheet">
    <style>
        .custom-lightblue-bg {
            background-color: #f2f7ff;
        }

        .custom-blue-text {
            color: #007bff;
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
            <a href="/views/manager/logout.php">Logout</a>
        </div>
        <!-- "Hamburger menu" / "Bar icon" to toggle the navigation links -->
        <a href="javascript:void(0);" class="icon" onclick="myFunction()">
            <i class="fa fa-bars"></i>
        </a>
    </div>
    <div class="container mt-5 custom-lightblue-bg p-4 rounded">
        <?php
        include '../../includes/fetch_diesel_price.php';

        if (isset($_POST['submit'])) {
            $distance = $_POST['distance'];
            $fuelEfficiencyMpg = $_POST['fuelEfficiency']; // Fuel efficiency in miles per gallon

            // Convert fuel efficiency to miles per liter
            $fuelEfficiencyMpl = $fuelEfficiencyMpg / 4.54609;

            // Convert price per litre to price per mile
            $pricePerMile = ($DieselPrice / 100) / $fuelEfficiencyMpl; // Convert pence to pounds

            // Calculate total cost
            $totalCost = $distance * $pricePerMile;

            echo "<p class='custom-blue-text'>Total diesel cost for $distance miles: £" . number_format($totalCost, 2) . "</p>";
        }
        ?>

        <form action="dieselcalc.php" method="post">
            <div class="form-group">
                <input type="text" class="form-control" name="distance" placeholder="Distance in miles">
            </div>
            <div class="form-group">
                <input type="text" class="form-control" name="fuelEfficiency" placeholder="Fuel efficiency (mpg)">
            </div>
            <button type="submit" class="btn btn-primary" name="submit">Calculate Cost</button>
        </form>
    </div>
    <!-- Include Bootstrap JS and its dependencies -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
