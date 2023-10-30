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
$role_id = $_SESSION['role_id']; 

$jobType = $_POST['jobType'] ?? 'due'; // Default to 'due'

if ($jobType === 'all') {
    $sql = "SELECT id, latitude, longitude FROM job_org" . $org_id . " WHERE latitude IS NOT NULL AND longitude IS NOT NULL";
} elseif ($jobType === 'due') {
    $sql = "SELECT id, latitude, longitude FROM job_org" . $org_id . " WHERE dateNextDue <= CURDATE() AND latitude IS NOT NULL AND longitude IS NOT NULL";
}

$result = $conn->query($sql);

// Always check for query errors
if (!$result) {
    die("Query failed: " . $conn->error);
}

$coordinates = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_push($coordinates, array('id' => $row['id'], 'lat' => $row['latitude'], 'lng' => $row['longitude']));
    }
} 


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

        .card-custom {
            background-color: #f8f9fa;
            border-color: #343a40;
            border: 2px solid #007bff;
        }


    </style>
    <meta charset="utf-8">
    <script type="text/javascript" src="/js/removeCompleteButtons.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> <!-- For hamburger menu -->
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <script src="jquery-3.6.0.min.js"></script>
    <link href="/css/main.css" rel="stylesheet">
</head>
<div class="container">
    <div class="topnav">
        <a href="/views/dashboard.php" class="active">Winda Ninjas</a>
        <!-- Navigation links (hidden by default) -->
        <div id="myLinks">
            <a href="/views/jobs/jobs.php">All Jobs</a>
            <?php if($role_id == 1): ?>
            <a href="/views/jobs/jobadd.php">Add Job</a>
            <a href="/views/manager/addzone.php">Add Zone</a>
            <a href="/views/manager/charts.php">Metrics</a
            <?php endif; ?>
            <a href="/views/manager/logout.php">Logout</a>
        </div>
        <!-- "Hamburger menu" / "Bar icon" to toggle the navigation links -->
        <a href="javascript:void(0);" class="icon" onclick="myFunction()">
            <i class="fa fa-bars"></i>
        </a>
    </div>
    <!-- Add this within your form or anywhere appropriate in your HTML -->
    <form id="myForm" action="" method="post">
        <label for="jobType">Select Job Type:</label>
        <select id="jobType" name="jobType">
            <option value="due" <?php if ($jobType === 'due') echo 'selected="selected"'; ?>>Due Jobs</option>
            <option value="all" <?php if ($jobType === 'all') echo 'selected="selected"'; ?>>All Jobs</option>
        </select>
    </form>
    <?php echo "<div>Jobs: " . $result->num_rows . "</div>";
    ?>
    <div class="card card-custom">
        <div class="card-body">
            <div id="mapid"></div>
        </div>
    </div>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var map = L.map('mapid').setView([52.697446, -2.7310085], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 45,
        }).addTo(map);

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
            });
        }

        var waypoints = <?php echo json_encode($coordinates); ?>;
        console.log(waypoints);

        function onMarkerClick(id) {
            return function() {
                window.location.href = "../jobs/jobupdate.php?id=" + id;
            }
        }

        for (var i = 0; i < waypoints.length; i++) {
            var marker = L.marker([waypoints[i].lat, waypoints[i].lng]).addTo(map);
            console.log('Created marker:', marker);
            marker.on('click', onMarkerClick(waypoints[i].id));
        }
    </script>
</div>
</div>

<script>
    // Add this to your existing JS code
    document.getElementById('jobType').addEventListener('change', function() {
        document.getElementById('myForm').submit();
    });

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