<?php
// Add CORS headers to allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require '/var/www/html/vendor/autoload.php';

use Aws\LocationService\LocationServiceClient;

$locationClient = new LocationServiceClient([
    'region'  => 'eu-west-2',
    'version' => 'latest',
    'key' => getenv('AWS_ACCESS_KEY_ID'),
    'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
]);

if (isset($_GET['address'])) {
    $address = $_GET['address'];


    $geocoding_result = $locationClient->searchPlaceIndexForText([
        'IndexName' => 'WindowCoord',
        'Text' => $address,
    ]);

    if (count($geocoding_result['Results']) > 0) {
        // take the first result
        $first_result = $geocoding_result['Results'][0];
        $latitude = $first_result['Place']['Geometry']['Point'][1];
        $longitude = $first_result['Place']['Geometry']['Point'][0];

        // Return the latitude and longitude as JSON
        echo json_encode(array('status' => 'OK', 'latitude' => $latitude, 'longitude' => $longitude));
    } else {
        // Return an error message if geocoding failed
        echo json_encode(array('status' => 'error', 'message' => 'Geocoding failed.'));
    }
} else {
    // Return an error message if the address parameter is not provided
    echo json_encode(array('status' => 'error', 'message' => 'Address not provided.'));
}
?>
