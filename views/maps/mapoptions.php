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

// Define parameters
$zoneFilter = isset($_POST['zoneFilter']) ? $_POST['zoneFilter'] : null;
$dueFilter = isset($_POST['dueFilter']) ? $_POST['dueFilter'] : null;

// SQL query
$sql = "SELECT id, latitude, longitude FROM job_org" . $org_id;

if ($zoneFilter || $dueFilter) {
    $sql .= " WHERE";

    if ($zoneFilter) {
        $sql .= " zone_id = " . $zoneFilter;
    }
    if ($dueFilter) {
        if ($zoneFilter) {
            $sql .= " AND";
        }
        $sql .= " dateNextDue <= CURDATE()";
    }
}

$sql .= " AND latitude IS NOT NULL AND longitude IS NOT NULL";

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
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <script src="jquery-3.6.0.min.js"></script>
    <link href="/css/main.css" rel="stylesheet">
</head>

<body>
    <div class="d-grid gap-3">
        <button onclick="location.href = '/views/dashboard.php';" class="btn btn-primary" type="button">Dashboard</button>
    </div>
    <div class="d-grid gap-3">
        <label for="zoneFilter">Select a Zone:</label>
        <input type="number" id="zoneFilter" name="zoneFilter" placeholder="Zone ID">
        <label for="dateFilter">Select due jobs?</label>
        <input type="checkbox" id="dueFilter" name="dueFilter">
        <button class="btn btn-primary" type="button" id="applyFilter">Apply filter</button>
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

    // This list will hold all markers
    var markers = [];

    console.log(waypoints);

    function onMarkerClick(id) {
        return function() {
            window.location.href = "../jobs/jobupdate.php?id=" + id;
        }
    }

    // Add markers to the map and to the list
    for (var i = 0; i < waypoints.length; i++) {
        var marker = L.marker([waypoints[i].lat, waypoints[i].lng]).addTo(map);
        markers.push(marker);  // Add marker to list
        marker.on('click', onMarkerClick(waypoints[i].id));
    }

    // Handle filter button click
    document.getElementById('applyFilter').addEventListener('click', function() {
        var zoneFilter = document.getElementById('zoneFilter').value;
        var dueFilter = document.getElementById('dueFilter').checked;

        // Send AJAX request to the server
        $.ajax({
            url: 'mapoptions.php',
            method: 'POST',
            data: {
                zoneFilter: zoneFilter,
                dueFilter: dueFilter,
            },
            success: function(data) {
                // Update the map with new data
                var newWaypoints = JSON.parse(data);

                // When you want to remove all markers:
                for (var i = 0; i < markers.length; i++) {
                    markers[i].remove();
                }

                // Don't forget to clear the list itself:
                markers = [];

                // Adding new markers (when you receive new data):
                for (var i = 0; i < newWaypoints.length; i++) {
                    var marker = L.marker([newWaypoints[i].lat, newWaypoints[i].lng]).addTo(map);
                    markers.push(marker);
                    marker.on('click', onMarkerClick(newWaypoints[i].id));
                }
            },
        });
    });
</script>
</body>

</html>
