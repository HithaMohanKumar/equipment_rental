<?php
require_once 'includes/connection.php';
if (!isLoggedIn() || !isAdmin()) { redirect('index.php'); }
$pageTitle = 'Admin Dashboard';

// Fetch statistics
$totalEquipment = $db->query("SELECT COUNT(*) as count FROM equipment")->fetch_assoc()['count'];
$totalUsers = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'];
$activeRentals = $db->query("SELECT COUNT(*) as count FROM rentals WHERE status = 'active'")->fetch_assoc()['count'];
$overdueRentals = $db->query("SELECT COUNT(*) as count FROM rentals WHERE status = 'active' AND due_date < NOW()")->fetch_assoc()['count'];

// Update overdue statuses
$db->query("UPDATE rentals SET status = 'overdue' WHERE status = 'active' AND due_date < NOW()");
$overdueRentals = $db->query("SELECT COUNT(*) as count FROM rentals WHERE status = 'overdue'")->fetch_assoc()['count'];

// Recent rentals
$recentRentals = $db->query("
    SELECT r.*, u.full_name, e.name AS equipment_name 
    FROM rentals r 
    JOIN users u ON r.user_id = u.user_id 
    JOIN equipment e ON r.equipment_id = e.equipment_id 
    ORDER BY r.rental_date DESC LIMIT 5
");

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Admin Dashboard</h1>
    <p style="color:#6b7280;">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
</div>

<div class="dashboard-grid">
    <div class="stat-card">
        <h3><?php echo $totalEquipment; ?></h3>
        <p>Total Equipment</p>
    </div>
    <div class="stat-card green">
        <h3><?php echo $totalUsers; ?></h3>
        <p>Registered Users</p>
    </div>
    <div class="stat-card orange">
        <h3><?php echo $activeRentals; ?></h3>
        <p>Active Rentals</p>
    </div>
    <div class="stat-card red">
        <h3><?php echo $overdueRentals; ?></h3>
        <p>Overdue Rentals</p>
    </div>
</div>

<div class="card">
    <h2>Recent Rental Activity</h2>
    <?php if ($recentRentals->num_rows > 0): ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Equipment</th>
                    <th>Rented On</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($rental = $recentRentals->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($rental['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($rental['equipment_name']); ?></td>
                    <td><?php echo date('d M Y', strtotime($rental['rental_date'])); ?></td>
                    <td><?php echo date('d M Y', strtotime($rental['due_date'])); ?></td>
                    <td>
                        <?php
                        $statusClass = $rental['status'];
                        if ($rental['status'] === 'active' && strtotime($rental['due_date']) < time()) {
                            $statusClass = 'overdue';
                        }
                        ?>
                        <span class="badge badge-<?php echo $statusClass; ?>"><?php echo ucfirst($statusClass); ?></span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p style="color:#6b7280; text-align:center; padding:2rem;">No rental activity yet.</p>
    <?php endif; ?>
</div>

<div class="dashboard-grid" style="grid-template-columns: 1fr 1fr;">
    <div class="card text-center">
        <h2>Equipment Management</h2>
        <p class="mb-2" style="color:#6b7280;">Add, edit, or remove equipment from inventory.</p>
        <a href="admin_equipment.php" class="btn btn-primary">Manage Equipment</a>
    </div>
    <div class="card text-center">
        <h2>User Management</h2>
        <p class="mb-2" style="color:#6b7280;">Add, edit, or remove user accounts.</p>
        <a href="admin_users.php" class="btn btn-success">Manage Users</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
