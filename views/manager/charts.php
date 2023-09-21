<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('location: /index.html');
    exit;
}

include "../../includes/connectdb.php";
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

// New SQL query for the new chart
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

$result2 = $conn->query($sql2);

$data2 = array();
while ($row = $result2->fetch_assoc()) {
    $zone = $row['Area'];
    $avg_monthly_value = $row['avg_monthly_value'];

    $data2[$zone] = $avg_monthly_value;
}



?>

<html>

<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> <!-- For hamburger menu -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="/css/main.css" rel="stylesheet">

</head>

<body>
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
            </a>
            </a>
            <a href="/views/manager/logout.php">Logout</a>
        </div>
        <!-- "Hamburger menu" / "Bar icon" to toggle the navigation links -->
        <a href="javascript:void(0);" class="icon" onclick="myFunction()">
            <i class="fa fa-bars"></i>
        </a>
    </div>
    <div class="container mt-5">
        <div class="row">
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card body">
                        <h5 class="card-title"><b>Payment Type Distribution</b></h5>
                        <canvas id="dyChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card body">
                        <h5 class="card-title"><b>Average Monthly Value</b></h5>
                        <canvas id="myNewChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card body">
                        <h5 class="card-title"><b>Value Currently Available NOW by zone</b></h5>
                        <?php

                        $sql3 = "SELECT zone_id, SUM(price) as total_value FROM job_org" . $org_id . " WHERE dateNextDue <= CURDATE() GROUP BY zone_id";


                        $result3 = $conn->query($sql3);

                        if ($result3 === FALSE) {
                            die($conn->error);
                        }


                        echo "<table>";
                        echo "<tr><th>Zone</th><th>Total Value</th></tr>";

                        if ($result3->num_rows > 0) {
                            while ($row = $result3->fetch_assoc()) {
                                $zone_id = $row['zone_id'];
                                $total_value = $row['total_value'];

                                // Now, we need to fetch the zone name for the given zone_id
                                $zone_sql = "SELECT area FROM zone_org" . $org_id . " WHERE id = " . $zone_id;
                                $zone_result = $conn->query($zone_sql);

                                if ($zone_result->num_rows > 0) {
                                    $zone_row = $zone_result->fetch_assoc();
                                    $zone_name = $zone_row['area'];
                                } else {
                                    $zone_name = "Unknown zone";
                                }

                                echo "<tr><td>" . $zone_name . "</td><td>£" . $total_value . "</td></tr>";
                            }
                        } else {
                            echo "No results found";
                        }

                        echo "</table>";

                        ?>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <script>
        var data = <?php echo json_encode($data); ?>;
        var labels = Object.keys(data);
        var datasets = [];
        var uniquePaymentTypes = [...new Set([].concat(...labels.map(label => Object.keys(data[label]))))];
        var datasetData = uniquePaymentTypes.map(paymentType => {
            return labels.map(label => data[label][paymentType] || 0);
        });
        uniquePaymentTypes.forEach((paymentType, index) => {
            datasets.push({
                label: paymentType,
                data: datasetData[index],
                backgroundColor: index % 2 === 0 ? 'rgba(255, 99, 132, 0.2)' : 'rgba(54, 162, 235, 0.2)',
                borderColor: index % 2 === 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            });
        });
        var dtx = document.getElementById('dyChart').getContext('2d');
        new Chart(dtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                scales: {
                    x: {
                        beginAtZero: true,
                        stacked: true
                    },
                    y: {
                        beginAtZero: true,
                        stacked: true
                    }
                }
            }
        });

        var data2 = <?php echo json_encode($data2); ?>;
        var labels2 = Object.keys(data2);
        var datasetData2 = labels2.map(label => data2[label]);

        var dtx2 = document.getElementById('myNewChart').getContext('2d');
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
    </script>
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
</body>

</html>