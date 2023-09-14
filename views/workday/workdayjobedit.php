<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: /index.html');
    exit;
}

include "../../includes/connectdb.php";
$org_id = $_SESSION['org_id'];

date_default_timezone_set('GMT');
if (isset($_GET['history_id'])) {
    $history_id = $_GET['history_id'];

    $sqlJobs = "SELECT job_org" . $org_id . ".houseNumName, job_org" . $org_id . ".streetName, job_history_org" . $org_id . ".price, job_history_org" . $org_id . ".paid, job_history_org" . $org_id . ".dateDone, job_history_org" . $org_id . ".id as history_id, job_history_org" . $org_id . ".payment_type_id
                FROM job_history_org" . $org_id . "
                INNER JOIN job_org" . $org_id . " ON job_history_org" . $org_id . ".job_id = job_org" . $org_id . ".id
                WHERE job_history_org" . $org_id . ".id = ?
                LIMIT 1";

    $stmtJobs = $conn->prepare($sqlJobs);
    $stmtJobs->bind_param("i", $history_id);
    $stmtJobs->execute();
    $resultJobs = $stmtJobs->get_result();

    if ($resultJobs->num_rows > 0) {
        $job = $resultJobs->fetch_assoc();
    } else {
        echo "No job found with that ID";
        exit;
    }
} else {
    echo "No job ID provided";
    exit;
}

// Fetch payment types from the paymentType table
$sqlPaymentTypes = "SELECT id, paymentType FROM paymentType";
$resultPaymentTypes = $conn->query($sqlPaymentTypes);
$paymentTypes = array();
while ($row = $resultPaymentTypes->fetch_assoc()) {
    $paymentTypes[$row['id']] = $row['paymentType'];
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $price = $_POST['price'];
    $paid = isset($_POST['paid']) ? 1 : 0;
    $payment_type_id = $_POST['payment_type'];

    // Update job history record with new values
    $sqlUpdateJob = "UPDATE job_history_org" . $org_id . " SET price = ?, paid = ?, payment_type_id = ? WHERE id = ?";
    $stmtUpdateJob = $conn->prepare($sqlUpdateJob);
    $stmtUpdateJob->bind_param("iiii", $price, $paid, $payment_type_id, $history_id);

    if ($stmtUpdateJob->execute()) {
        $dateDone = date('Y-m-d', strtotime($_POST['dateDone']));
        header("Location: workday.php?date={$dateDone}");
        exit;
    } else {
        echo '<div class="alert alert-danger">
                <strong>Error!</strong> Job update failed.
              </div>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <link href="/css/main.css" rel="stylesheet">
    <style>
        /* Custom CSS for larger inputs and buttons */
        input[type="number"],
        input[type="submit"],
        button {
            font-size: 1.5rem; /* Increase font size */
            padding: 0.75rem; /* Increase padding */
            line-height: 1.5; /* Adjust line height */
        }
        /* Increase checkbox size */
        input[type="checkbox"] {
            transform: scale(2.5);
        }
    </style>
</head>
<body>
    <div class="d-grid gap-3">
        <button onclick="location.href = '../dashboard.php';" class="btn btn-primary" type="button">Dashboard</button>
    </div>

    <div class="container">
        <h1><?php echo $job["houseNumName"] . " " . $job["streetName"] ?><br><?php echo $job["dateDone"] ?></h1>
        <div class="row">
            <div class="col-3"></div>
            <form action="" method="post" class="col-6">
                <input type="hidden" name="dateDone" value="<?php echo $job['dateDone']; ?>">
                <input type="hidden" id="history_id" name="history_id" value="<?php echo $job['history_id']; ?>">

                <div class="mb-3">
                    <label for="price" class="form-label" style="font-size: 24px; font-weight: bold;">Price (£):</label>
                    <input type="number" class="form-control" id="price" name="price" value="<?php echo $job['price']; ?>" min="0">
                </div>
                <div class="mb-3">
                    <label for="paid" class="form-label" style="font-size: 24px; font-weight: bold;">Paid?</label>
                    <input type="checkbox" id="paid" name="paid" value="1" <?php echo ($job['paid'] ? 'checked' : ''); ?>><br>
                </div>
                <div class="mb-3">
                    <label for="payment_type" class="form-label" style="font-size: 24px; font-weight: bold;">Payment Type:</label>
                    <select name="payment_type" id="payment_type" class="form-control" style="font-size: 1.5rem;">
                        <?php
                        // Display payment types in the dropdown
                        foreach ($paymentTypes as $paymentTypeId => $paymentType) {
                            $selected = ($paymentTypeId == $job['payment_type_id']) ? 'selected' : '';
                            echo '<option value="' . $paymentTypeId . '" ' . $selected . '>' . $paymentType . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <button type="submit" style="font-size: 1.5rem; padding: 0.75rem; height: 20px;">Update Job</button>
                </div>
                <br>
                <br>
                <br>
                <div class="mb-3">
                    <button type="submit" formaction="undoworkdayjob.php" style="background-color: red; color: white;" onclick="return confirm('Are you sure you want to undo the job?');">Undo Job</button>
                </div>
            </form>
            <div class="col-3"></div>
        </div>
    </div>
</body>
</html>
