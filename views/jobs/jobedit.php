<?php
ob_start();

session_start();
if (!isset($_SESSION['loggedin'])) {
    header('location: /index.html');
    exit;
}

include "../../includes/connectdb.php";
date_default_timezone_set('GMT');
$job_id = $_GET['id'];
$org_id = $_SESSION['org_id'];

// Get job details
$stmtJobData = $conn->prepare("SELECT * FROM job_org" . $org_id . " WHERE id = ?");
$stmtJobData->bind_param("i", $job_id);
$stmtJobData->execute();
$resultJobData = $stmtJobData->get_result();
$rowJobData = $resultJobData->fetch_assoc();

$house_num = $rowJobData["houseNumName"];
$street_name = $rowJobData["streetName"];
$price = $rowJobData["price"];
$frequency = $rowJobData["frequency"];
$payment_type_id = $rowJobData["paymentType_id"];
$info = $rowJobData["info"];

// Get payment types
$stmtPaymentTypes = $conn->prepare("SELECT id, paymentType FROM paymentType");
$stmtPaymentTypes->execute();
$resultPaymentTypes = $stmtPaymentTypes->get_result();

// Update job
if (isset($_POST['update_payment'])) {
    $new_house_num = $_POST['house_num_form'];
    $new_street = $_POST['street_form'];
    $new_price = $_POST['price_form'];
    $new_frequency = $_POST['frequency_form'];
    $new_payment_type = $_POST['payment_type'];
    $new_info = $_POST['info_form'];


        // Debugging: print the value of new_info
        error_log("New info: " . $new_info);
        // Set to NULL if info is empty
        if (empty($new_info)) {
            $new_info = NULL;
        }

    $stmtUpdateJob = $conn->prepare("UPDATE job_org" . $org_id . " SET houseNumName = ?, streetName = ?, price = ?, frequency = ?, paymentType_id = ?, info = ? WHERE id = ?");
    $stmtUpdateJob->bind_param("ssiiiss", $new_house_num, $new_street, $new_price, $new_frequency, $new_payment_type, $new_info, $job_id);
    if ($stmtUpdateJob->execute()) {
        echo '<div class="alert alert-success">
            <strong>Success!</strong> Job updated.
        </div>';
        header("location: jobupdate.php?id=$job_id");
    } else {
        echo '<div class="alert alert-danger">
            <strong>Error!</strong> Failed to update job.
        </div>';
    }
}

$conn->close();
ob_end_flush();
?>

<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Winda Ninjas</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> <!-- For hamburger menu -->
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <link href="/css/main.css" rel="stylesheet">

</head>

<body>
<div class="topnav">
        <a href="/views/dashboard.php" class="active">Winda Ninjas</a>
        <!-- Navigation links (hidden by default) -->
        <div id="myLinks">
            <a href="/views/jobs/jobs.php">All Jobs</a>
            <a href="/views/jobs/jobadd.php">Add Job</a>
            <a href="/views/manager/charts.php">Metrics</a>
            <a href="/views/manager/logout.php">Logout</a>
        </div>
        <!-- "Hamburger menu" / "Bar icon" to toggle the navigation links -->
        <a href="javascript:void(0);" class="icon" onclick="myFunction()">
            <i class="fa fa-bars"></i>
        </a>
    </div>
    <div class="container mt-5">
        <div class="card card-custom">
        <div class="card-body">
        
        <div class="mb-3">
            <form action="" method="post">
                <input type="hidden" name="id" value="<?php echo $job_id; ?>">
                <div class="mb-3">
                    <label for="house_num_form" class="form-label">House Number/Name</label>
                    <input type="text" name="house_num_form" class="form-control" value="<?php echo $house_num; ?>">
                </div>
                <div class="mb-3">
                    <label for="street_form" class="form-label">Street Name</label>
                    <input type="text" name="street_form" class="form-control" value="<?php echo $street_name; ?>">
                </div>
                <div class="mb-3">
                    <label for="price_form" class="form-label">Price</label>
                    <input type="number" name="price_form" class="form-control" value="<?php echo $price; ?>">
                </div>
                <div class="mb-3">
                    <label for="frequency_form" class="form-label">Frequency</label>
                    <input type="number" name="frequency_form" class="form-control" value="<?php echo $frequency; ?>">
                </div>
                <div class="mb-3">
                    <label for="payment_type" class="form-label">Payment Type</label>
                    <select class="form-select" id="payment_type" name="payment_type">
                        <?php
                        while ($rowPaymentType = $resultPaymentTypes->fetch_assoc()) {
                            $paymentTypeId = $rowPaymentType['id'];
                            $paymentType = $rowPaymentType['paymentType'];
                            $selected = ($paymentTypeId == $payment_type_id) ? 'selected' : '';
                            echo '<option value="' . $paymentTypeId . '" ' . $selected . '>' . $paymentType . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="info_form" class="form-label">Information</label>
                    <input type="text" name="info_form" class="form-control" value="<?php echo $info; ?>">
                </div>
                <div class="mb-3">
                    <input type="submit" name="update_payment" value="Update" class="btn btn-primary">
                </div>
            </form>
        </div>
        <button onClick="location.href = 'jobdelete.php?id=<?php echo $job_id ?>'; " class="btn btn-danger" type="button">Request Delete</button>
    </div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
<script>
    function myFunction() {
        var x = document.getElementById("myLinks");
        if (x.style.display === "block") {
            x.style.display = "none";
            document.getElementById("myLinks").style.display = "none";
        } else {
            x.style.display = "block";
            document.getElementById("myLinks").style.display = "block";
        }
    }
    </script>
</html>
