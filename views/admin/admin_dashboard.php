<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <!-- Add your CSS links here -->
</head>
<body>
    <div class="admin-dashboard">
        <header>
            <h1>Winda Ninjas Admin Dashboard</h1>
        </header>
        
        <nav class="admin-navigation">
            <ul>
                <li><a href="/admin/dashboard.php">Dashboard</a></li>
                <li><a href="/admin/logout.php">Logout</a></li>
            </ul>
        </nav>
        
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
</body>
</html>
