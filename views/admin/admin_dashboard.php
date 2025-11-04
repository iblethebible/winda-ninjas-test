<?php
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

$job_table_name = "job_org" . $org_id;

// Define getCount function
function getCount($conn, $sql)
{
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'];
}

$sql = "SELECT COUNT(*) AS total FROM $job_table_name";
$total_jobs = getCount($conn, $sql);

$sql = "SELECT COUNT(*) AS total FROM users WHERE org_id = $org_id AND role_id = 2";
$total_workers = getCount($conn, $sql);

// Fetching monthly values for jobs
$monthly_values = [];
for ($i = 1; $i <= 12; $i++) {
    $sql = "SELECT SUM(price) AS total FROM job_history_org" . $org_id . " WHERE MONTH(dateDone) = $i AND YEAR(dateDone) = YEAR(CURDATE())";
    $monthly_values[$i] = getCount($conn, $sql);
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
                <a href="/views/manager/dieselcalc.php">Diesel Calculator</a>
                <a href="bank_details.php">Bank Details</a>
                <a href="/views/manager/addzone.php">Zones</a>
                <a href="/views/manager/charts.php">Metrics</a>
                <a href="/views/manager/changepassword.php">Change Password</a>
                <a href="/views/manager/logout.php">Logout</a>
            </div>
            <!-- "Hamburger menu" / "Bar icon" to toggle the navigation links -->
            <a href="javascript:void(0);" class="icon" onclick="myFunction()">
                <i class="fa fa-bars"></i>
            </a>
        </div>
        <div class="container mt-5">
            <h1>Admin Dashboard</h1>
            <div class="row">
                <!-- HTML canvas element -->
                <canvas id="lineChartValue" width="400" height="200"></canvas>

                <!-- JavaScript for Chart.js -->
                <script>
                    var ctxValue = document.getElementById('lineChartValue').getContext('2d');
                    var monthly_values = <?php echo json_encode(array_values($monthly_values)); ?>;
                    var myLineChartValue = new Chart(ctxValue, {
                        type: 'line',
                        data: {
                            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                            datasets: [{
                                label: 'Income per Month (£)',
                                data: monthly_values,
                                borderColor: 'rgba(255, 99, 132, 1)',
                                backgroundColor: 'rgba(0, 0, 0, 0)'
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value, index, values) {
                                            return '£' + value;
                                        }
                                    }
                                }
                            },
                            tooltips: {
                                callbacks: {
                                    label: function(tooltipItem, data) {
                                        var label = data.datasets[tooltipItem.datasetIndex].label || '';
                                        if (label) {
                                            label += ': £';
                                        }
                                        label += Math.round(tooltipItem.yLabel * 100) / 100;
                                        return label;
                                    }
                                }
                            }
                        }
                    });
                </script>

            </div>
            <div class="row">
                <div class="col-md-6 col-sm-6">
                    <div class="card mb-4">
                        <div class="card-body cards">
                            <h5 class="card-title"><b>Total Jobs</b></h5>
                            <p class="card-text"><?php echo $total_jobs; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6">
                    <div class="card mb-4">
                        <div class="card-body cards">
                            <h5 class="card-title"><b>Workers</b></h5>
                            <p class="card-text"><?php echo $total_workers; ?></p>
                        </div>
                    </div>
                </div>


            </div>

            <script src="https://unpkg.com/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js"></script>

            <script>
                /* Toggle between showing and hiding the navigation menu links when the user clicks on the hamburger menu / bar icon */
                function myFunction() {
                    var x = document.getElementById("myLinks");
                    if (x.style.display === "block") {
                        x.style.display = "none";
                    } else {
                        x.style.display = "block";
                    }
                }
            </script>
            <?php include '../../includes/footer.php'; ?>
    </container>
</body>
<!-- At the end of your body tag -->



</html>