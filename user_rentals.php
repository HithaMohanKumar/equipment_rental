<?php
require_once 'includes/connection.php';
if (!isLoggedIn() || isAdmin()) { redirect('index.php'); }
$pageTitle = 'My Rentals';

$userId = $_SESSION['user_id'];

// Update overdue
$db->query("UPDATE rentals SET status = 'overdue' WHERE status = 'active' AND due_date < NOW()");

// Filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where = "WHERE r.user_id = $userId";
if ($filter === 'active') $where .= " AND r.status = 'active'";
elseif ($filter === 'overdue') $where .= " AND r.status = 'overdue'";
elseif ($filter === 'returned') $where .= " AND r.status = 'returned'";

$rentals = $db->query("
    SELECT r.*, e.name AS equipment_name, e.category, e.serial_number
    FROM rentals r
    JOIN equipment e ON r.equipment_id = e.equipment_id
    $where
    ORDER BY r.rental_date DESC
");

include 'includes/header.php';
?>

<div class="page-header">
    <h1>My Rental History</h1>
    <a href="user_search.php" class="btn btn-primary">Rent More Equipment</a>
</div>

<?php if (isset($_GET['returned'])): ?>
    <div class="alert alert-success">&#10003; Equipment returned successfully!</div>
<?php endif; ?>

<div class="card">
    <div class="search-bar">
        <a href="user_rentals.php" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">All</a>
        <a href="user_rentals.php?filter=active" class="btn <?php echo $filter === 'active' ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">Active</a>
        <a href="user_rentals.php?filter=overdue" class="btn <?php echo $filter === 'overdue' ? 'btn-danger' : 'btn-secondary'; ?> btn-sm">Overdue</a>
        <a href="user_rentals.php?filter=returned" class="btn <?php echo $filter === 'returned' ? 'btn-success' : 'btn-secondary'; ?> btn-sm">Returned</a>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Equipment</th>
                    <th>Category</th>
                    <th>Qty</th>
                    <th>Rented On</th>
                    <th>Due Date</th>
                    <th>Returned</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rentals->num_rows > 0): ?>
                    <?php while ($r = $rentals->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($r['equipment_name']); ?></strong><br>
                            <small style="color:#6b7280;"><?php echo $r['serial_number']; ?></small></td>
                        <td><?php echo htmlspecialchars($r['category']); ?></td>
                        <td><?php echo $r['quantity']; ?></td>
                        <td><?php echo date('d M Y', strtotime($r['rental_date'])); ?></td>
                        <td><?php echo date('d M Y', strtotime($r['due_date'])); ?></td>
                        <td><?php echo $r['return_date'] ? date('d M Y', strtotime($r['return_date'])) : '-'; ?></td>
                        <td><span class="badge badge-<?php echo $r['status']; ?>"><?php echo ucfirst($r['status']); ?></span></td>
                        <td>
                            <?php if ($r['status'] !== 'returned'): ?>
                                <a href="user_return.php?id=<?php echo $r['rental_id']; ?>" 
                                   class="btn btn-success btn-sm"
                                   onclick="return confirm('Return this item?');">Return</a>
                            <?php else: ?>
                                <span style="color:#6b7280;">-</span>
                            <?php endif; ?>
                        </td>
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
