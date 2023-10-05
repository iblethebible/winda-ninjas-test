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
                <a href="#">Bank Details</a>
                <a href="/views/manager/addzone.php">Add Zone</a>
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
        <?php
        $timescales = [
            'daily' => [
                'label' => 'Daily',
                'interval' => '-5 days',
                'dateInterval' => 'P1D',
                'format' => 'D, d M'
            ],
            'weekly' => [
                'label' => 'Weekly',
                'interval' => '-5 weeks',
                'dateInterval' => 'P7D',
                'format' => 'W'
            ],
            'monthly' => [
                'label' => 'Monthly',
                'interval' => '-5 months',
                'dateInterval' => 'P1M',
                'format' => 'M'
            ]
        ];

        foreach ($timescales as $timescale => $data) {
            $interval = $data['interval'];
            $format = $data['format'];

            $startDate = date('Y-m-d', strtotime($interval));
            $endDate = date('Y-m-d');

            $period = new DatePeriod(
                new DateTime($startDate),
                new DateInterval($data['dateInterval']),
                new DateTime($endDate)
            );

            $sqlTotalWork = "SELECT price, dateDone
                             FROM job_history_org" . $org_id . "
                             WHERE dateDone BETWEEN ? AND ?
                             ORDER BY dateDone ASC";

            $sqlEarnings = "SELECT price, dateDone
                            FROM job_history_org" . $org_id . "
                            WHERE paid = 1 AND dateDone BETWEEN ? AND ?
                            ORDER BY dateDone ASC";

            $stmtTotalWork = $conn->prepare($sqlTotalWork);
            $stmtTotalWork->bind_param("ss", $startDate, $endDate);
            $stmtTotalWork->execute();
            $resultTotalWork = $stmtTotalWork->get_result();

            $stmtEarnings = $conn->prepare($sqlEarnings);
            $stmtEarnings->bind_param("ss", $startDate, $endDate);
            $stmtEarnings->execute();
            $resultEarnings = $stmtEarnings->get_result();

            $totalWork = [];
            $earnings = [];

            // Initialize all days with 0
            foreach ($period as $date) {
                $key = $date->format($format);
                $totalWork[$key] = 0;
                $earnings[$key] = 0;
            }

            while ($row = $resultTotalWork->fetch_assoc()) {
                $date = new DateTime($row['dateDone']);
                $key = $date->format($format);
                $totalWork[$key] += $row['price'];
            }

            while ($row = $resultEarnings->fetch_assoc()) {
                $date = new DateTime($row['dateDone']);
                $key = $date->format($format);
                $earnings[$key] += $row['price'];
            }

            $labels = array_keys($totalWork);
            $totalWorkData = array_values($totalWork);
            $earningsData = array_values($earnings);
        ?>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $data['label']; ?> £ Worked/Collected</h5>
                        <canvas id="workChart<?php echo ucfirst($timescale); ?>"></canvas>
                    </div>
                </div>
            </div>


            <script>
                const ctx<?php echo ucfirst($timescale); ?> = document.getElementById('workChart<?php echo ucfirst($timescale); ?>').getContext('2d');
                new Chart(ctx<?php echo ucfirst($timescale); ?>, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode($labels); ?>,
                        datasets: [{
                                label: 'Total Work',
                                data: <?php echo json_encode($totalWorkData); ?>,
                                backgroundColor: 'rgba(0, 123, 255, 0.5)',
                                borderColor: 'rgba(0, 123, 255, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Collected',
                                data: <?php echo json_encode($earningsData); ?>,
                                backgroundColor: 'rgba(220, 53, 69, 0.5)',
                                borderColor: 'rgba(220, 53, 69, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            },
                            x: {
                                barPercentage: 1,
                                categoryPercentage: 0.5
                            }
                        },
                        plugins: {
                            tooltip: {
                                enabled: true,
                                displayColors: false,
                                callbacks: {
                                    label: function(context) {
                                        var label = context.dataset.label || '';

                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += new Intl.NumberFormat('en-US', {
                                                style: 'currency',
                                                currency: 'GBP'
                                            }).format(context.parsed.y);
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            </script>
        <?php } ?>
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
    </div>

</body>

</html>