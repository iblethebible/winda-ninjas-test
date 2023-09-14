<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('location: /index.html');
    exit;
}

include "../../includes/connectdb.php"; 
$org_id = $_SESSION['org_id'];
$user = $_SESSION['name'];

// Get the job ID from the URL parameter
$job_id = $_GET['id'];

// Retrieve job information
$sql = "SELECT houseNumName, streetName FROM job_org" . $org_id . " WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the query executed successfully
if (!$result) {
    echo "Error: " . $conn->error;
    exit;
}

// Check if the job exists
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $houseNumName = $row['houseNumName'];
    $streetName = $row['streetName'];
} else {
    echo "Job not found.";
    exit;
}

// Retrieve customer information (if exists)
$customerSql = "SELECT id, forename, surname, email, phoneNumber FROM customer_org" . $org_id . " WHERE id IN (SELECT cust_id FROM job_org" . $org_id . " WHERE id = ?)";
$stmtCustomer = $conn->prepare($customerSql);
$stmtCustomer->bind_param("i", $job_id);
$stmtCustomer->execute();
$customerResult = $stmtCustomer->get_result();

// Check if the customer exists
if ($customerResult->num_rows > 0) {
    $customerRow = $customerResult->fetch_assoc();
    $customerID = $customerRow['id'];
    $forename = $customerRow['forename'];
    $surname = $customerRow['surname'];
    $email = $customerRow['email'];
    $phoneNumber = $customerRow['phoneNumber'];
}

$conn->close();
?>

<html>
<head>
    <meta charset="utf-8">
    <script type="text/javascript" src="/js/removeCompleteButtons.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <script src="jquery-3.6.0.min.js"></script>
    <link href="/css/main.css" rel="stylesheet">
    <style>
        .card-custom {
            background-color: #f8f9fa; 
            border-color: #343a40;
        }
        .card-custom h1, h2 {
            color: #343a40;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="card card-custom">
            <div class="card-body">


                <!-- Display job information -->
                <h1 class="mb-4"><?php echo $houseNumName . " " . $streetName ?></h1>

                <!-- Display customer information if available -->
                <?php if (isset($forename) && isset($surname)) : ?>
                    <h2 class="mb-3">Customer Information</h2>
                    <table class="table">
                        <tr>
                            <th>Forename:</th>
                            <td><?php echo $forename ?></td>
                        </tr>
                        <tr>
                            <th>Surname:</th>
                            <td><?php echo $surname ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?php echo $email ?></td>
                        </tr>
                        <tr>
                            <th>Phone Number:</th>
                            <td><a href="tel:<?php echo $phoneNumber ?>"><?php echo $phoneNumber ?></a></td>
                        </tr>
                    </table>
                    <button onClick="location.href = 'customeredit.php?id=<?php echo $customerID ?>';" class="btn btn-primary">Edit Customer</button>
                <?php else : ?>
                    <h2>No Customer Information</h2>
                    <button onClick="location.href = 'customeradd.php?id=<?php echo $job_id ?>';" class="btn btn-primary">Add Customer</button>
                <?php endif; ?>
                <h2 class="mt-4"><a href="/views/jobs/jobupdate.php?id=<?php echo $job_id ?>">Return</a></h2>
            </div>
        </div>
    </div>
</body>
</html>
