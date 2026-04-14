<?php
require_once 'includes/connection.php';
if (!isLoggedIn() || isAdmin()) { redirect('index.php'); }
$pageTitle = 'User Dashboard';

$userId = $_SESSION['user_id'];

// Update overdue
$db->query("UPDATE rentals SET status = 'overdue' WHERE status = 'active' AND due_date < NOW()");

// Stats
$activeRentals = $db->query("SELECT COUNT(*) as count FROM rentals WHERE user_id = $userId AND status = 'active'")->fetch_assoc()['count'];
$overdueRentals = $db->query("SELECT COUNT(*) as count FROM rentals WHERE user_id = $userId AND status = 'overdue'")->fetch_assoc()['count'];
$totalReturned = $db->query("SELECT COUNT(*) as count FROM rentals WHERE user_id = $userId AND status = 'returned'")->fetch_assoc()['count'];

// Max rentals for user
$maxRentals = $db->query("SELECT max_rentals FROM users WHERE user_id = $userId")->fetch_assoc()['max_rentals'];
$currentRentals = $activeRentals + $overdueRentals;
$remainingSlots = max(0, $maxRentals - $currentRentals);

// Current active rentals
$myRentals = $db->query("
    SELECT r.*, e.name AS equipment_name, e.category, e.serial_number
    FROM rentals r
    JOIN equipment e ON r.equipment_id = e.equipment_id
    WHERE r.user_id = $userId AND r.status IN ('active', 'overdue')
    ORDER BY r.due_date ASC
");

include 'includes/header.php';
?>

<?php if (isset($_GET['returned'])): ?>
    <div class="alert alert-success">&#10003; Equipment returned successfully!</div>
<?php endif; ?>

<div class="page-header">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h1>
    <a href="user_search.php" class="btn btn-primary">Search & Rent Equipment</a>
</div>

<div class="dashboard-grid">
    <div class="stat-card">
        <h3><?php echo $activeRentals; ?></h3>
        <p>Active Rentals</p>
    </div>
    <div class="stat-card red">
        <h3><?php echo $overdueRentals; ?></h3>
        <p>Overdue</p>
    </div>
    <div class="stat-card green">
        <h3><?php echo $totalReturned; ?></h3>
        <p>Total Returned</p>
    </div>
    <div class="stat-card purple">
        <h3><?php echo $remainingSlots; ?> / <?php echo $maxRentals; ?></h3>
        <p>Rental Slots Available</p>
    </div>
</div>

<?php if ($overdueRentals > 0): ?>
    <div class="alert alert-error">&#9888; You have <?php echo $overdueRentals; ?> overdue rental(s). Please return them as soon as possible.</div>
<?php endif; ?>

<div class="card">
    <h2>My Current Rentals</h2>
    <?php if ($myRentals->num_rows > 0): ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Equipment</th>
                    <th>Category</th>
                    <th>Serial No.</th>
                    <th>Rented On</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($r = $myRentals->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($r['equipment_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($r['category']); ?></td>
                    <td><?php echo htmlspecialchars($r['serial_number']); ?></td>
                    <td><?php echo date('d M Y', strtotime($r['rental_date'])); ?></td>
                    <td><?php echo date('d M Y', strtotime($r['due_date'])); ?></td>
                    <td><span class="badge badge-<?php echo $r['status']; ?>"><?php echo ucfirst($r['status']); ?></span></td>
                    <td>
                        <a href="user_return.php?id=<?php echo $r['rental_id']; ?>" 
                           class="btn btn-success btn-sm"
                           onclick="return confirm('Return this item?');">Return</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p style="color:#6b7280; text-align:center; padding:2rem;">You have no active rentals. <a href="user_search.php">Browse equipment</a> to get started.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
