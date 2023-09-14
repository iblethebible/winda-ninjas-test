<?php
require '/var/www/html/vendor/autoload.php';

use Aws\LocationService\LocationServiceClient;

include "../../includes/connectdb.php";

$locationClient = new LocationServiceClient([
    'region'  => 'eu-west-2',
    'version' => 'latest',
    'key' => getenv('AWS_ACCESS_KEY_ID'),
    'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
]);

$sql = "SELECT id, houseNumName, streetName FROM job_org1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    $address = $row['houseNumName'] . ' ' . $row['streetName'];

    $geocoding_result = $locationClient->searchPlaceIndexForText([
        'IndexName' => 'WindowCoord',
        'Text' => $address,
        'BiasPosition' => [-2.74, 52.71], // longitude, latitude for Shrewsbury
    ]);

    
    if (count($geocoding_result['Results']) > 0) {
        // take the first result
        $first_result = $geocoding_result['Results'][0];
        $latitude = $first_result['Place']['Geometry']['Point'][1];
        $longitude = $first_result['Place']['Geometry']['Point'][0];

        // prepare an SQL statement to update the row in the database
        $update_sql = "UPDATE job_org1 SET latitude = ?, longitude = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ddi", $latitude, $longitude, $row['id']);
        $stmt->execute();
    }
  }

} else {
  echo "0 results";
}

$conn->close();
?>
