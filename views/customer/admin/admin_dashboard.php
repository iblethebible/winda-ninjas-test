<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- For hamburger menu -->


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <meta charset="UTF-8">

    <title>Admin Dashboard</title>
    <link href="/css/main.css" rel="stylesheet">
</head>

<body>
    <div class="container fluid">
        <div class="row">


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
                    <a href="/views/admin/admin_dashboard.php">Admin Dashboard</a>
                    <a href="/views/manager/logout.php">Logout</a>
                </div>
                <!-- "Hamburger menu" / "Bar icon" to toggle the navigation links -->
                <a href="javascript:void(0);" class="icon" onclick="myFunction()">
                    <i class="fa fa-bars"></i>
                </a>

            </div>
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                Products
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            <div class="admin-dashboard">
                <header>
                    <h1>Admin Mode</h1>
                </header>
                <div class="container mt-5">
                </div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-body cards">
                            <a href="/admin/dashboard.php">Dashboard</a>
                            <a href="/admin/logout.php">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
            <main class="admin-main">
                <section class="admin-section" id="job-management">
                    <h2>Job Management</h2>
                    <ul>
                        <li><a href="/admin/jobs/all_jobs.php">All Jobs</a></li>
                        <li><a href="/admin/jobs/add_job.php">Add Job</a></li>
                    </ul>
                </section>

                <section class="admin-section" id="zone-management">
                    <h2>Zone Management</h2>
                    <ul>
                        <li><a href="/admin/zones/all_zones.php">All Zones</a></li>
                        <li><a href="/admin/zones/add_zone.php">Add Zone</a></li>
                    </ul>
                </section>

                <section class="admin-section" id="invoice-management">
                    <h2>Invoice Management</h2>
                    <ul>
                        <li><a href="/views/invoicing/invoices.php">All Invoices</a></li>
                        <li><a href="/admin/invoices/create_invoice.php">Create Invoice</a></li>
                    </ul>
                </section>

                <section class="admin-section" id="metrics">
                    <h2>Metrics</h2>
                    <ul>
                        <li><a href="/admin/metrics/charts.php">View Charts</a></li>
                    </ul>
                </section>

                <section class="admin-section" id="user-management">
                    <h2>User Management</h2>
                    <ul>
                        <li><a href="/admin/users/all_users.php">All Users</a></li>
                        <li><a href="/admin/users/add_user.php">Add User</a></li>
                        <li><a href="/admin/users/change_password.php">Change Password</a></li>
                    </ul>
                </section>
            </main>
        </div>
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