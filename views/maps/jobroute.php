<?php
error_reporting(E_ALL); // Enable error reporting for debugging
ob_start();
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('location: /index.html');
    exit;
}

include "../../includes/connectdb.php";
date_default_timezone_set('GMT');
$org_id = $_SESSION['org_id'];


$job_id = $_GET['id'];
// Fetch the job location (latitude and longitude) from the database

// Prepare an SQL statement for execution
$stmt = $conn->prepare("SELECT latitude, longitude, houseNumName, streetName FROM job_org" . $org_id . " WHERE id = ?");

// Bind variables to a prepared statement as parameters
$stmt->bind_param("i", $job_id); // 'i' specifies the variable type => 'integer'

// echo "Job ID: " . $job_id . "<br>";
// echo "Org ID: " . $org_id . "<br>";

// Execute prepared statement
$stmt->execute();

// Get the result of the query
$result = $stmt->get_result();

// Check the result
if ($result->num_rows > 0) {
    // Fetch the coordinates of the job
    $row = $result->fetch_assoc();
    $job_latitude = $row['latitude'];
    $job_longitude = $row['longitude'];
    $job_houseNumName = $row['houseNumName'];
    $job_streetName = $row['streetName'];
    // echo "Job Latitude in PHP: " . $job_latitude . "<br>";
    // echo "Job Longitude in PHP: " . $job_longitude . "<br>";
    $job_location = [$job_latitude, $job_longitude];
} else {
    echo "No results found for the job ID.";
}

// Close the prepared statement
$stmt->close();


?>
<!DOCTYPE html>
<html>

<head>
    <title>Job Router</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> <!-- For hamburger menu -->
    <link href="/css/main.css" rel="stylesheet">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <style>
        #map {
            width: 100%;
            height: 70vh;
        }

        .card-custom {
            background-color: #f8f9fa;
            border-color: #343a40;
            border: 2px solid #007bff;
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

</head>

<body>
    <div class="container">
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
        <h1>Route too <?php echo $job_houseNumName . " " . $job_streetName?></h1>
        <!-- Create a container for the map and pass AWS credentials as data attributes -->

        <div class="card card-custom">
            <div class="card-body">

                <div id="map" style="height: 400px;" data-aws-region="<?php echo getenv('AWS_REGION'); ?>" data-aws-access-key-id="<?php echo getenv('AWS_ACCESS_KEY_ID'); ?>" data-aws-secret-access-key="<?php echo getenv('AWS_SECRET_ACCESS_KEY'); ?>"></div>
            </div>
        </div>

        <!-- Include Leaflet JS file -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://sdk.amazonaws.com/js/aws-sdk-2.975.0.min.js"></script> <!-- Replace with the appropriate version -->

        <script>
            // Get AWS credentials from data attributes in HTML
            var awsRegion = document.getElementById('map').getAttribute('data-aws-region');
            var awsAccessKeyId = document.getElementById('map').getAttribute('data-aws-access-key-id');
            var awsSecretAccessKey = document.getElementById('map').getAttribute('data-aws-secret-access-key');

            // Debugging: Log fetched AWS credentials
            console.log('Fetched AWS Region:', awsRegion);
            console.log('Fetched AWS Access Key ID:', awsAccessKeyId);
            console.log('Fetched AWS Secret Access Key:', awsSecretAccessKey);

            // Pass the PHP job coordinates to JavaScript
            var job_latitude = parseFloat(<?php echo json_encode($job_latitude); ?>);
            var job_longitude = parseFloat(<?php echo json_encode($job_longitude); ?>);

            console.log('Job Latitude in JS:', job_latitude);
            console.log('Job Longitude in JS:', job_longitude);


            // Initialize the map
            var map = L.map('map').setView([52.697605, -2.729776], 13);

            // Add a tile layer using OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Function to calculate route and add polylines to the map
            function calculateRoute(departure, destination) {
                // Debugging: Log request parameters
                console.log('Request Params: Departure:', departure, ' Destination:', destination);

                // Swap the order of coordinates for AWS Location Service
                var awsDeparture = [departure[1], departure[0]];
                var awsDestination = [destination[1], destination[0]];


                var client = new AWS.Location({
                    region: awsRegion,
                    accessKeyId: awsAccessKeyId,
                    secretAccessKey: awsSecretAccessKey
                });

                var params = {
                    CalculatorName: 'Window_Routing',
                    DeparturePosition: awsDeparture,
                    DestinationPosition: awsDestination,
                    IncludeLegGeometry: true
                };

                client.calculateRoute(params, function(err, data) {
                    if (err) {
                        console.log("Error calculating route:", err);
                    } else {
                        console.log("Route data:", data);

                        // Create a LatLng array for the route
                        var route = [];

                        // Get the route geometry from the AWS response
                        data.Legs.forEach(leg => {
                            leg.Geometry.LineString.forEach(point => {
                                // AWS Location service returns longitude first, swap order for Leaflet
                                route.push([point[1], point[0]]);
                            });
                        });

                        // Add markers for departure and arrival locations
                        //L.marker(departure).addTo(map);
                        L.marker(destination).addTo(map);


                        // Add the route polyline
                        var routePolyline = L.polyline(route, {
                            color: 'blue',
                            weight: 5
                        }).addTo(map);
                    }
                });
            }



            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var lat = position.coords.latitude;
                    var lon = position.coords.longitude;
                    map.setView([lat, lon], 14);

                    // Create a custom icon
                    var youAreHereIcon = L.icon({
                        iconUrl: '/imgs/logo.png', // Path to your image
                        iconSize: [30, 50], // Size of the icon. Adjust as needed.
                        iconAnchor: [22, 94], // Point of the icon which will correspond to marker's location.
                        popupAnchor: [-3, -76] // Point from which the popup should open relative to the iconAnchor.
                    });

                    // Create a new marker using the custom icon
                    var youAreHereMarker = L.marker([lat, lon], {
                        icon: youAreHereIcon
                    }).addTo(map);
                    youAreHereMarker.bindPopup("<b>You are here</b>").openPopup();

                    // Use the latitude and longitude as the departure point
                    var departure = [lat, lon];

                    // You can call your calculateRoute function here if desired
                    var destination = [job_latitude, job_longitude];
                    calculateRoute(departure, destination);
                });
            }
        </script>
<script>
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