<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('location: /index.html');
    exit;
}
date_default_timezone_set('GMT');
include "../../includes/connectdb.php";
$org_id = $_SESSION['org_id'];
?>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> <!-- For hamburger menu -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="/css/main.css" rel="stylesheet">
    <style>
        /* Style the navigation menu */
        .topnav {
            overflow: hidden;
            background-color: #333;
            position: relative;
        }

        /* Hide the links inside the navigation menu (except for logo/home) */
        .topnav #myLinks {
            display: none;
        }

        /* Style navigation menu links */
        .topnav a {
            color: white;
            padding: 14px 16px;
            text-decoration: none;
            font-size: 17px;
            display: block;
        }

        /* Style the hamburger menu */
        .topnav a.icon {
            background: black;
            display: block;
            position: absolute;
            right: 0;
            top: 0;
        }

        /* Add a grey background color on mouse-over */
        .topnav a:hover {
            background-color: #ddd;
            color: black;
        }

        /* Style the active link (or home/logo) */
        .active {
            /* background-color: #04AA6D; */
            color: white;
        }
    </style>
</head>

<body>
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