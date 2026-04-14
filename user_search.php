<?php
require_once 'includes/connection.php';
if (!isLoggedIn() || isAdmin()) { redirect('index.php'); }
$pageTitle = 'Search Equipment';

// Get search parameters
$search = isset($_GET['search']) ? sanitize($db, $_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize($db, $_GET['category']) : '';
$condition = isset($_GET['condition']) ? sanitize($db, $_GET['condition']) : '';

// Build query
$sql = "SELECT * FROM equipment WHERE available_quantity > 0";
$params = [];
$types = '';

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR description LIKE ? OR serial_number LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}
if (!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= 's';
}
if (!empty($condition)) {
    $sql .= " AND condition_status = ?";
    $params[] = $condition;
    $types .= 's';
}
$sql .= " ORDER BY name ASC";

$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$equipment = $stmt->get_result();

// Get all categories for filter dropdown
$categories = $db->query("SELECT DISTINCT category FROM equipment ORDER BY category ASC");

// Get user rental info
$userId = $_SESSION['user_id'];
$userInfo = $db->query("SELECT max_rentals FROM users WHERE user_id = $userId")->fetch_assoc();
$currentRentals = $db->query("SELECT COUNT(*) as count FROM rentals WHERE user_id = $userId AND status IN ('active','overdue')")->fetch_assoc()['count'];
$remainingSlots = max(0, $userInfo['max_rentals'] - $currentRentals);

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Search & Rent Equipment</h1>
    <span class="badge badge-<?php echo $remainingSlots > 0 ? 'active' : 'overdue'; ?>" style="font-size:0.95rem; padding:0.4rem 1rem;">
        <?php echo $remainingSlots; ?> rental slot(s) remaining
    </span>
</div>

<!-- Search Form -->
<div class="card">
    <form method="GET" action="user_search.php">
        <div class="search-bar">
            <input type="text" name="search" placeholder="Search by name, description, or serial number..." 
                   value="<?php echo htmlspecialchars($search); ?>">
            <select name="category">
                <option value="">All Categories</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                            <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <select name="condition">
                <option value="">Any Condition</option>
                <?php foreach (['New', 'Good', 'Fair', 'Poor'] as $cond): ?>
                    <option value="<?php echo $cond; ?>" <?php echo $condition === $cond ? 'selected' : ''; ?>>
                        <?php echo $cond; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="user_search.php" class="btn btn-secondary">Clear</a>
        </div>
    </form>
</div>

<?php if (isset($_GET['rented'])): ?>
    <div class="alert alert-success">&#10003; Equipment rented successfully! Due date has been set.</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
<?php endif; ?>

<!-- Equipment Grid -->
<div class="equipment-grid">
    <?php if ($equipment->num_rows > 0): ?>
        <?php while ($item = $equipment->fetch_assoc()): ?>
        <div class="equip-card">
            <div>
                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                <div class="meta">
                    <span><strong>Category:</strong> <?php echo htmlspecialchars($item['category']); ?></span>
                    <span><strong>Serial:</strong> <?php echo htmlspecialchars($item['serial_number']); ?></span>
                    <span><strong>Condition:</strong> 
                        <span class="badge badge-<?php echo strtolower($item['condition_status']); ?>">
                            <?php echo $item['condition_status']; ?>
                        </span>
                    </span>
                    <?php if ($item['description']): ?>
                        <span style="margin-top:0.3rem;"><?php echo htmlspecialchars($item['description']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <p class="availability in-stock">
                    <?php echo $item['available_quantity']; ?> of <?php echo $item['total_quantity']; ?> available
                </p>
                <?php if ($remainingSlots > 0): ?>
                    <form method="POST" action="user_rent.php" style="display:flex; gap:0.5rem; align-items:center;">
                        <input type="hidden" name="equipment_id" value="<?php echo $item['equipment_id']; ?>">
                        <select name="quantity" style="padding:0.4rem; border-radius:6px; border:1px solid #d1d5db; width:60px;">
                            <?php for ($i = 1; $i <= min($item['available_quantity'], $remainingSlots); $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                        <select name="duration" style="padding:0.4rem; border-radius:6px; border:1px solid #d1d5db;">
                            <option value="7">7 days</option>
                            <option value="14" selected>14 days</option>
                            <option value="30">30 days</option>
                        </select>
                        <button type="submit" class="btn btn-success btn-sm">Rent</button>
                    </form>
                <?php else: ?>
                    <p style="color:#dc2626; font-size:0.9rem; font-weight:500;">Rental limit reached</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="card" style="grid-column: 1/-1;">
            <p class="text-center" style="padding:2rem; color:#6b7280;">No equipment matches your search. Try different filters.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
