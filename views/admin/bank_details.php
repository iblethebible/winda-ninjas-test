<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('location: /index.html');
    exit;
}

include "../../includes/connectdb.php";
$org_id = $_SESSION['org_id'];
$user = $_SESSION['name'];


// Fetching bank details if they exist
$sql = "SELECT bank_beneficiary, bank_sortCode, bank_accountNumber FROM organisations WHERE id = $org_id";
$result = $conn->query($sql);
$beneficiary = "";
$sort_code = "";
$account_number = "";
$bankDetailsAvailable = false;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $beneficiary = $row['bank_beneficiary'];
    $sort_code = $row['bank_sortCode'];
    $account_number = $row['bank_accountNumber'];
    $bankDetailsAvailable = true;  // Add this line
} else {
    $beneficiary = "No bank details";
    $sort_code = "No bank details";
    $account_number = "No bank details";
}



$conn->close();
?>

<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- For hamburger menu -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <script src="jquery-3.6.0.min.js"></script>
    <link href="/css/main.css" rel="stylesheet">
    <style>
    .card-custom {
        background-color: #f8f9fa;
        border-color: #343a40;
    }

    .card-custom h1,
    h2 {
        color: #343a40;
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


                <!-- Display job information -->
                <h1 class="mb-4">Bank Details</h1>
                
                <table class="table">
                    <tr>
                        <th>Beneficiary:</th>
                        <td><?php echo $beneficiary ?></td>
                    </tr>
                    <tr>
                        <th>Sort Code:</th>
                        <td><?php echo $sort_code ?></td>
                    </tr>
                    <tr>
                        <th>Account Number</th>
                        <td><?php echo $account_number?></td>
                    </tr>
                </table>
                
                <button onClick="location.href = 'bank_details_edit.php?id=<?php echo $org_id ?>';"
                    class="btn btn-primary">Edit/Add bank details</button>
                
                <h2 class="mt-4"><a href="/views/admin/admin_dashboard.php">Return</a></h2>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>

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