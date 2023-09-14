<?php
require '/var/www/html/vendor/autoload.php';
use Aws\LocationService\LocationServiceClient;

function calculateRoute($departure, $destination) {
    $client = new LocationServiceClient([
        'region' => 'eu-west-2',
        'version' => 'latest',
        'credentials' => [
            'key' => getenv('AWS_ACCESS_KEY_ID'),
            'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
        ]
    ]);

    try {
        $result = $client->calculateRoute([
            'CalculatorName' => 'Window_Routing',
            'DeparturePosition' => $departure,
            'DestinationPosition' => $destination,
        ]);

        return $result;
    } catch (AwsException $e) {
        // output error message if fails
        error_log($e->getMessage());
        return null;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assuming you have form input fields named 'departure' and 'destination'
    $departure = array_map('floatval', explode(',', $_POST['departure'])); // This will split the input string into an array and convert to floats
    $destination = array_map('floatval', explode(',', $_POST['destination'])); // This too

    $route = calculateRoute($departure, $destination);

    if ($route === null) {
        echo "Failed to calculate route";
    } else {
        // Use the returned $route object
        echo "<pre>";
        print_r($route);
        echo "</pre>";
    }
}
?>

<!DOCTYPE html>
<html>
<body>

<h2>Test Route Calculation</h2>

<form method="post" action="maps.php">
  <label for="departure">Departure (longitude,latitude):</label><br>
  <input type="text" id="departure" name="departure"><br>
  <label for="destination">Destination (longitude,latitude):</label><br>
  <input type="text" id="destination" name="destination"><br>
  <input type="submit" value="Calculate Route">
</form>


</body>
</html>
