<?php
ob_start();

session_start();
if (!isset($_SESSION['loggedin'])) {
    header('location: /index.php');
    exit;
}

include "../../includes/connectdb.php";
date_default_timezone_set('GMT');
$org_id = $_SESSION['org_id'];

//get bank details
$stmtBankDetails = $conn->prepare("SELECT bank_beneficiary, bank_sortCode, bank_accountNumber FROM organisations WHERE id = ?");
$stmtBankDetails->bind_param("i", $org_id);
$stmtBankDetails->execute();
$resultBankDetails = $stmtBankDetails->get_result();
$rowBankDetails = $resultBankDetails->fetch_assoc();
$bank_beneficiary = $rowBankDetails['bank_beneficiary'];
$bank_sortCode = $rowBankDetails['bank_sortCode'];
$bank_accountNumber = $rowBankDetails['bank_accountNumber'];

// Update bank details
if (isset($_POST['update_banking'])) {
    $new_beneficiary = $_POST['beneficiary_form'];
    $new_sort_code = $_POST['sort_code_form'];
    $new_account_number = $_POST['account_number_form'];

    $stmtUpdateBanking = $conn->prepare("UPDATE organisations SET bank_beneficiary = ?, bank_sortCode = ?, bank_accountNumber = ? WHERE id = ?");
    $stmtUpdateBanking->bind_param("sssi", $new_beneficiary, $new_sort_code, $new_account_number, $org_id);
    if ($stmtUpdateBanking->execute()) {
        echo '<div class="alert alert-success">
            <strong>Success!</strong> Bank details updated.
        </div>';
        header("refresh:1; url=bank_details.php");
    } else {
        echo '<div class="alert alert-danger">
            <strong>Error!</strong> Failed to update bank details.
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
        <div class="card card-custom">
        <div class="card-body">
        
        <div class="mb-3">
            <form action="" method="post">
                <div class="mb-3">
                    <label for="beneficiary_form" class="form-label">Beneficiary</label>
                    <input type="text" name="beneficiary_form" class="form-control" value="<?php echo $bank_beneficiary; ?>">
                </div>
                <div class="mb-3">
                    <label for="sort_code_form" class="form-label">Sort Code</label>
                    <input type="text" name="sort_code_form" class="form-control" value="<?php echo $bank_sortCode; ?>">
                </div>
                <div class="mb-3">
                    <label for="account_number_form" class="form-label">Account Number</label>
                    <input type="number" name="account_number_form" class="form-control" value="<?php echo $bank_accountNumber ?>">
                </div>

                

                <div class="mb-3">
                    <input type="submit" name="update_banking" value="Update" class="btn btn-primary">
                </div>
            </form>
        </div>
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
