<!DOCTYPE html>
<html>

<head>
    <title>Test Map with Amazon Location Router</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>

<body>

    <h1>Test Map</h1>

    <!-- Create a container for the map and pass AWS credentials as data attributes -->
    <div id="map" style="height: 400px;" data-aws-region="<?php echo getenv('AWS_REGION'); ?>" data-aws-access-key-id="<?php echo getenv('AWS_ACCESS_KEY_ID'); ?>" data-aws-secret-access-key="<?php echo getenv('AWS_SECRET_ACCESS_KEY'); ?>"></div>
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

        // Initialize the map
        var map = L.map('map').setView([52.697605, -2.729776], 13);

        // Add a tile layer using OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Function to calculate route and add polylines to the map
        function calculateRoute(departure, destination, waypoints) {
            // Debugging: Log request parameters
            console.log('Request Params: Departure:', departure, ' Destination:', destination);

            // Swap the order of coordinates for AWS Location Service
            var awsDeparture = [departure[1], departure[0]];
            var awsWaypoints = waypoints.map(waypoint => [waypoint[1], waypoint[0]]); // This will work with any number of waypoints
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
                WaypointPositions: awsWaypoints,
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
                    L.marker(departure).addTo(map);
                    L.marker(destination).addTo(map);

                    // Loop through waypoints and add a marker for each
                    waypoints.forEach(function(waypoint) {
                        L.marker(waypoint).addTo(map);
                    });
                    // Add the route polyline
                    var routePolyline = L.polyline(route, {
                        color: 'blue',
                        weight: 5
                    }).addTo(map);
                }
            });
        }



        // Example of calling the calculateRoute function with two points
        var departure = [52.697605, -2.729776];
        var destination = [52.708287, -2.73956];
        var waypoints = [
            [52.70333, -2.73333],
            [52.70556, -2.73667],
        ]

        calculateRoute(departure, destination, waypoints);
    </script>

</body>

</html>