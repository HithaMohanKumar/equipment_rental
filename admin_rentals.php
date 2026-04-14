<?php
require_once 'includes/connection.php';
if (!isLoggedIn() || !isAdmin()) { redirect('index.php'); }
$pageTitle = 'All Rentals';

// Update overdue statuses
$db->query("UPDATE rentals SET status = 'overdue' WHERE status = 'active' AND due_date < NOW()");

// Filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where = '';
if ($filter === 'active') $where = "WHERE r.status = 'active'";
elseif ($filter === 'overdue') $where = "WHERE r.status = 'overdue'";
elseif ($filter === 'returned') $where = "WHERE r.status = 'returned'";

$rentals = $db->query("
    SELECT r.*, u.full_name, u.username, e.name AS equipment_name, e.serial_number
    FROM rentals r
    JOIN users u ON r.user_id = u.user_id
    JOIN equipment e ON r.equipment_id = e.equipment_id
    $where
    ORDER BY r.rental_date DESC
");

include 'includes/header.php';
?>

<div class="page-header">
    <h1>All Rentals</h1>
</div>

<div class="card">
    <div class="search-bar">
        <a href="admin_rentals.php" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">All</a>
        <a href="admin_rentals.php?filter=active" class="btn <?php echo $filter === 'active' ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">Active</a>
        <a href="admin_rentals.php?filter=overdue" class="btn <?php echo $filter === 'overdue' ? 'btn-danger' : 'btn-secondary'; ?> btn-sm">Overdue</a>
        <a href="admin_rentals.php?filter=returned" class="btn <?php echo $filter === 'returned' ? 'btn-success' : 'btn-secondary'; ?> btn-sm">Returned</a>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Equipment</th>
                    <th>Qty</th>
                    <th>Rented On</th>
                    <th>Due Date</th>
                    <th>Returned</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rentals->num_rows > 0): ?>
                    <?php while ($r = $rentals->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $r['rental_id']; ?></td>
                        <td><?php echo htmlspecialchars($r['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($r['equipment_name']); ?><br><small style="color:#6b7280;"><?php echo $r['serial_number']; ?></small></td>
                        <td><?php echo $r['quantity']; ?></td>
                        <td><?php echo date('d M Y', strtotime($r['rental_date'])); ?></td>
                        <td><?php echo date('d M Y', strtotime($r['due_date'])); ?></td>
                        <td><?php echo $r['return_date'] ? date('d M Y', strtotime($r['return_date'])) : '-'; ?></td>
                        <td><span class="badge badge-<?php echo $r['status']; ?>"><?php echo ucfirst($r['status']); ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center" style="padding:2rem; color:#6b7280;">No rentals found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
