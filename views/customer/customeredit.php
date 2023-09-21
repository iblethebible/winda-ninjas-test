<?php
ob_start();
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('location: /index.html');
    exit;
}
include "../../includes/connectdb.php"; 
$org_id = $_SESSION['org_id'];

// Get the customer ID from the URL parameter
$customer_id = $_GET['id'];

// Retrieve customer information based on the customer ID
$sql = "SELECT * FROM customer_org" . $org_id . " WHERE id = ?";
$stmtCustomer = $conn->prepare($sql);
$stmtCustomer->bind_param("i", $customer_id);
$stmtCustomer->execute();
$result = $stmtCustomer->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $forename = $row['forename'];
    $surname = $row['surname'];
    $email = $row['email'];
    $phoneNumber = $row['phoneNumber'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the form submission
    
    // Retrieve form data
    $forename = $_POST['forename'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $phoneNumber = $_POST['phoneNumber'];

    // Update the customer record
    $updateSql = "UPDATE customer_org" . $org_id . " SET forename = ?, surname = ?, email = ?, phoneNumber = ? WHERE id = ?";
    $stmtUpdate = $conn->prepare($updateSql);
    $stmtUpdate->bind_param("ssssi", $forename, $surname, $email, $phoneNumber, $customer_id);
    if ($stmtUpdate->execute()) {
        echo '<div class="alert alert-success">
            <strong>Success!</strong> Customer record updated.
        </div>';
        
        // Get the job ID associated with the customer
        $jobSql = "SELECT id FROM job_org" . $org_id . " WHERE cust_id = ?";
        $stmtJob = $conn->prepare($jobSql);
        $stmtJob->bind_param("i", $customer_id);
        $stmtJob->execute();
        $jobResult = $stmtJob->get_result();
        if ($jobResult->num_rows > 0) {
            $jobRow = $jobResult->fetch_assoc();
            $job_id = $jobRow['id'];
            
            // Redirect back to customer.php with the job ID
            header("Refresh: 1; URL=customer.php?id=$job_id");
            exit;
        } else {
            echo "Error: Job not found for the customer.";
        }
    } else {
        echo "Error updating customer record: " . $conn->error;
    }
}

$conn->close();
ob_end_flush();
?>

<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> <!-- For hamburger menu -->
    <title>Edit Customer</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/main.css" rel="stylesheet">
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
    <div class="container">
        
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <h1>Edit Customer</h1>
                <form method="POST">
                    <div class="mb-3">
                        <label for="forename" class="form-label">Forename</label>
                        <input type="text" class="form-control" id="forename" name="forename" value="<?php echo $forename ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="surname" class="form-label">Surname</label>
                        <input type="text" class="form-control" id="surname" name="surname" value="<?php echo $surname ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $email ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="phoneNumber" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" value="<?php echo $phoneNumber ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <input type="submit" class="btn btn-primary" value="Update Customer">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

</body>
<script>
    function myFunction() {
        var x = document.getElementById("myLinks");
        if (x.style.display === "block") { // If the navigation menu is displayed
            x.style.display = "none"; // Hide the navigation menu
        } else {
            x.style.display = "block"; // Display the navigation menu
        }
    }
    </script>
</html>
