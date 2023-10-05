<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['loggedin'])) {
  header('location: /index.html');
  exit;
}
include "../../includes/connectdb.php";
date_default_timezone_set('GMT');
$user = $_SESSION['name'];
$org_id = $_SESSION['org_id'];
$zone_id = $_SESSION['zone_id'];
$role_id = $_SESSION['role_id'];

$org_id = $_SESSION['org_id'];

$job_table_name = "job_org" . $org_id;

// Original SQL query
$sql = "SELECT zone_org{$org_id}.area AS zone, paymentType.paymentType AS paymentType, COUNT(*) AS count
        FROM job_org{$org_id}
        JOIN zone_org{$org_id} ON job_org{$org_id}.zone_id = zone_org{$org_id}.id
        JOIN paymentType ON job_org{$org_id}.paymentType_id = paymentType.id
        GROUP BY zone_org{$org_id}.area, paymentType.paymentType;";

$result = $conn->query($sql);

$data = array();
while ($row = $result->fetch_assoc()) {
    $zone = $row['zone'];
    $paymentType = $row['paymentType'];
    $count = $row['count'];

    if (!isset($data[$zone])) {
        $data[$zone] = array();
    }
    $data[$zone][$paymentType] = $count;
}


// sql for average monthly valu
$sql2 = "SELECT 
            zone_org{$org_id}.id as 'Zone', 
            zone_org{$org_id}.area as 'Area',
            SUM(job_org{$org_id}.price / job_org{$org_id}.frequency * 4) as 'avg_monthly_value'
         FROM 
            job_org{$org_id}
         JOIN 
            zone_org{$org_id}
         ON 
            job_org{$org_id}.zone_id = zone_org{$org_id}.id 
         GROUP BY 
            zone_org{$org_id}.id, zone_org{$org_id}.area";

if ($stmt = $conn->prepare($sql2)) {
  $stmt->execute();
  $result2 = $stmt->get_result();

  $data2 = array(); // Initialize to empty array
  while ($row = $result2->fetch_assoc()) {
      $zone = $row['Area'];
      $avg_monthly_value = $row['avg_monthly_value'];
      $data2[$zone] = $avg_monthly_value;
  }
  $stmt->close();
} else {
  echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- For hamburger menu -->
    <style>
    .cards {
        border: 2px solid #007bff;
        border-radius: 5px;
        background-color: #e1eded;
    }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <container>
        <!-- Top Navigation Menu -->
        <div class="topnav">
            <a href="/views/dashboard.php" class="active">Winda Ninjas</a>
            <!-- Navigation links (hidden by default) -->
            <div id="myLinks">
                <a href="/views/jobs/jobs.php">All Jobs</a>
                <a href="/views/jobs/jobadd.php">Add Job</a>
                <a href="/views/manager/addzone.php">Add Zone</a>
                <a href="/views/manager/charts.php">Metrics</a>
                <a href="/views/manager/changepassword.php">Change Password</a>
                <a href="/views/admin/admin_dashboard.php">Admin Dashboard</a>
                <a href="/views/manager/logout.php">Logout</a>
            </div>
            <!-- "Hamburger menu" / "Bar icon" to toggle the navigation links -->
            <a href="javascript:void(0);" class="icon" onclick="myFunction()">
                <i class="fa fa-bars"></i>
            </a>
        </div>
        <div class="container mt-5">
    <h1>Admin Dashboard</h1>
    <!-- <div class="row d-flex align-items-stretch"> <!-- Add d-flex align-items-stretch here -->
        <div class="col-md-6">
            <div class="card mb-4 card-same-height">
                <div class="card-body cards">
                    <h5 class="card-title">Total Jobs</h5>
                    <p class="card-text">
                        <?php
                        $sql = "SELECT COUNT(*) AS total FROM job_org" . $org_id . "";
                        $result = mysqli_query($conn, $sql);
                        $row = mysqli_fetch_assoc($result);
                        echo $row['total'];
                        ?>
                    </p>
                </div>
            </div>
        </div> 
        <div class="col-md-6">
            <div class="card mb-4 card-same-height">
                <div class="card-body cards">
                    <h5 class="card-title">Average Monthly value</h5>
                    <canvas id="monthlyAverageChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>



    </container>
    <script>
    console.log("JavaScript is working");
    var data2 = <?php 
  $encodedData = json_encode($data2);
  if (json_last_error() === JSON_ERROR_NONE) {
      echo $encodedData;
  } else {
      echo "\"JSON encoding error: " . json_last_error_msg() . "\"";
  }
?>;
    console.log(typeof data2);
    console.log(data2);

    if (data2 !== null && typeof data2 === 'object') {
        var labels2 = Object.keys(data2);
        var datasetData2 = labels2.map(label => data2[label]);

        var dtx2 = document.getElementById('monthlyAverageChart').getContext('2d');
        new Chart(dtx2, {
            type: 'bar',
            data: {
                labels: labels2,
                datasets: [{
                    label: 'Average Monthly Value',
                    data: datasetData2,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    } else {
        console.log("data2 is null or not an object");
    }
    </script>

</body>

</html>