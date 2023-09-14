<?php
ob_start();
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('location: /index.html');
    exit;
}

include "../../includes/connectdb.php";
date_default_timezone_set('GMT');
$org_id = $_SESSION['org_id'];
$zone_id = $_SESSION['zone_id'];

// SQL query                                                        
$sql = "SELECT id, latitude, longitude FROM job_org" . $org_id . " WHERE dateNextDue <= CURDATE() AND latitude IS NOT NULL AND longitude IS NOT NULL AND zone_id = $zone_id";


// Execute query and collect results
$result = $conn->query($sql);

// Array to store the coordinates
$coordinates = array();

if ($result->num_rows > 0) {
    // Output data for each row
    while ($row = $result->fetch_assoc()) {
        array_push($coordinates, array('id' => $row['id'], 'lat' => $row['latitude'], 'lng' => $row['longitude']));
    }
} else {
    echo "0 results";
}

$conn->close();

// Now, $coordinates is an array containing the latitude and longitude of all jobs that are due.
// You can use it in the script that generates the map.

ob_end_flush();

?>

<!DOCTYPE html>
<html>

<head>
    <title>Waypoints Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        #mapid {
            width: 100%;
            height: 70vh;
        }
    </style>
    <meta charset="utf-8">
    <script type="text/javascript" src="/js/removeCompleteButtons.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"><!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <script src="jquery-3.6.0.min.js"></script>
    <link href="/css/main.css" rel="stylesheet">
</head>

<body>
    <div class="d-grid gap-3">
        <button onclick="location.href = '/views/dashboard.php';" class="btn btn-primary" type="button">Dashboard</button>
    </div>
    <div id="mapid"></div>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var map = L.map('mapid').setView([52.697446, -2.7310085], 14); // set initial map center and zoom level

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {

            maxZoom: 45,
        }).addTo(map);


        // Attempt to update the map center with user's current location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var lat = position.coords.latitude;
                var lon = position.coords.longitude;
                map.setView([lat, lon], 14);
            });
        }

        var waypoints = <?php echo json_encode($coordinates); ?>; // this is your PHP array encoded to JSON



        console.log(waypoints);

        function onMarkerClick(id) {
            return function() {
                window.location.href = "../jobs/jobupdate.php?id=" + id;
            }
        }

        for (var i = 0; i < waypoints.length; i++) {
            var marker = L.marker([waypoints[i].lat, waypoints[i].lng]).addTo(map);
            console.log('Created marker:', marker); // add this line
            marker.on('click', onMarkerClick(waypoints[i].id));
        }
    </script>
</body>

</html>