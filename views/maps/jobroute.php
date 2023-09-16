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
$stmt = $conn->prepare("SELECT latitude, longitude FROM job_org" . $org_id . " WHERE id = ?");

// Bind variables to a prepared statement as parameters
$stmt->bind_param("i", $job_id); // 'i' specifies the variable type => 'integer'

echo "Job ID: " . $job_id . "<br>";
echo "Org ID: " . $org_id . "<br>";

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
    echo "Job Latitude in PHP: " . $job_latitude . "<br>";
    echo "Job Longitude in PHP: " . $job_longitude . "<br>";
    $job_location = [$job_latitude, $job_longitude];
} else {
    echo "No results found for the job ID.";
}

// Close the prepared statement
$stmt->close();


?>
<html>

<head>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Job Route</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    
    <!-- Leaflet CSS -->
    <link href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" rel="stylesheet" />

    <!-- Leaflet Routing Machine CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />

    <!-- Geocoder CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    
    <!-- jQuery and other JS libraries -->
    <script src="jquery-3.6.0.min.js"></script>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

    <!-- Leaflet Routing Machine JS -->
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

    <!-- Geocoder JS -->
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

    <!-- Your Custom Scripts -->
    <link href="/css/main.css" rel="stylesheet">

    <!-- Inline JS -->
    <script>
        // Your inline JavaScript code here
    </script>

    <!-- Additional Styles -->
    <style>
        #mapid {
            width: 100%;
            height: 70vh;
        }
    </style>


    <script>
        var job_coordinates = [<?php echo $job_latitude; ?>, <?php echo $job_longitude; ?>];
        console.log("Echoed Job Coordinates: ", job_coordinates);

        function initMap() {
            var map = L.map('mapid').setView([0, 0], 13); // Initialized at [0,0], will be set to user location later

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
            }).addTo(map);
            // Get the current location of the user
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var current_lat = position.coords.latitude;
                    var current_lon = position.coords.longitude;

                    // Do something with the coordinates
                    console.log("Current coordinates: ", current_lat, current_lon);
                    console.log("Job coordinates: ", job_coordinates);
                    // Move map to current location
                    map.setView(new L.LatLng(current_lat, current_lon), 13);

                    L.Routing.control({
                        waypoints: [
                            L.latLng(current_lat, current_lon),
                            L.latLng(job_coordinates[0], job_coordinates[1])
                        ],
                        routeWhileDragging: true,
                        geocoder: L.Control.Geocoder.nominatim(),
                        serviceUrl: 'https://your-routing-service.com/route/v2',
                        profile: 'driving'
                    }).addTo(map);
                });
            }
        }
    </script>
</head>

<body onload="initMap()">
    <div id="mapid"></div>
</body>

</html>