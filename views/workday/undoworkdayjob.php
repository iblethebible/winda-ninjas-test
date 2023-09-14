<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: /index.html');
    exit;
}
date_default_timezone_set('GMT');
include "../../includes/connectdb.php"; 
$org_id = $_SESSION['org_id'];

if (isset($_POST['history_id'])) {
    $history_id = $_POST['history_id'];

    // Fetch the dateDone from the job history before deleting it
    $sqlFetchDateDone = "SELECT dateDone FROM job_history_org" . $org_id . " WHERE id = ?";
    $stmtFetchDateDone = $conn->prepare($sqlFetchDateDone);
    if ($stmtFetchDateDone === FALSE) {
        die($conn->error);
    }
    $stmtFetchDateDone->bind_param("i", $history_id);
    $stmtFetchDateDone->execute();
    $resultFetchDateDone = $stmtFetchDateDone->get_result();

    if ($resultFetchDateDone->num_rows > 0) {
        $rowFetchDateDone = $resultFetchDateDone->fetch_assoc();
        $dateDone = $rowFetchDateDone['dateDone'];
    } else {
        echo "No job found with that ID";
        exit;
    }

    // check if there are previous jobs
    $sqlPreviousJobs = "SELECT job_id, COUNT(*) as count 
                        FROM job_history_org" . $org_id . "
                        WHERE id < ? GROUP BY job_id"; 

    $stmtPreviousJobs = $conn->prepare($sqlPreviousJobs);
    if ($stmtPreviousJobs === FALSE) {
        die($conn->error);
    }
    $stmtPreviousJobs->bind_param("i", $history_id);
    $stmtPreviousJobs->execute();
    $resultPreviousJobs = $stmtPreviousJobs->get_result();

    if ($resultPreviousJobs->num_rows > 0) {
        $previousJobs = $resultPreviousJobs->fetch_assoc();
        $job_id = $previousJobs['job_id'];

        if ($previousJobs['count'] > 0) {
            // get the max id and corresponding job_id
            $sqlMaxIdAndJobId = "SELECT job_id, MAX(id) AS max_id FROM job_history_org" . $org_id . " WHERE job_id = ? GROUP BY job_id";
            $stmtMaxIdAndJobId = $conn->prepare($sqlMaxIdAndJobId);
            if ($stmtMaxIdAndJobId === FALSE) {
                die($conn->error);
            }
            $stmtMaxIdAndJobId->bind_param("i", $job_id);
            $stmtMaxIdAndJobId->execute();
            $resultMaxIdAndJobId = $stmtMaxIdAndJobId->get_result();

            if ($resultMaxIdAndJobId->num_rows > 0) {
                $rowMaxIdAndJobId = $resultMaxIdAndJobId->fetch_assoc();
                $max_id = $rowMaxIdAndJobId['max_id'];
            } else {
                echo "No job found with that ID";
                exit;
            }

            // undo the job by rolling back the date last completed to the previous date
            $sqlRollback = "UPDATE job_org" . $org_id . "
                            SET dateLastDone = (SELECT dateDone FROM job_history_org" . $org_id . " WHERE id = ?),
                                dateNextDue = DATE_ADD((SELECT dateDone FROM job_history_org" . $org_id . " WHERE id = ?), INTERVAL job_org" . $org_id . ".frequency WEEK)
                            WHERE id = ?";

            $stmtRollback = $conn->prepare($sqlRollback);
            if ($stmtRollback === FALSE) {
                die($conn->error);
            }
            $stmtRollback->bind_param("iii", $max_id, $max_id, $job_id);
            $stmtRollback->execute();
        } else {
            // no previous jobs, just set dateLastDone and dateNextDue to NULL
            $sqlRollback = "UPDATE job_org" . $org_id . "
                            SET dateLastDone = NULL,
                                dateNextDue = NULL
                            WHERE id = ?";

            $stmtRollback = $conn->prepare($sqlRollback);
            if ($stmtRollback === FALSE) {
                die($conn->error);
            }
            $stmtRollback->bind_param("i", $job_id);
            $stmtRollback->execute();
        }
    } 

    // delete the record from the job_history table
    $sqlDelete = "DELETE FROM job_history_org" . $org_id . " WHERE id = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    if ($stmtDelete === FALSE) {
        die($conn->error);
    }
    $stmtDelete->bind_param("i", $history_id);
    $stmtDelete->execute();

    // redirect to the previous page
    header("location: workday.php?date={$dateDone}");
    exit;
} else {
    echo "No job history ID provided";
    exit;
}
?>
