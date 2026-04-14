<?php
require_once 'includes/connection.php';
if (!isLoggedIn() || !isAdmin()) { redirect('index.php'); }
$pageTitle = 'Manage Equipment';

$message = '';
$messageType = '';

// Handle DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Check if equipment has active rentals
    $check = $db->prepare("SELECT COUNT(*) as count FROM rentals WHERE equipment_id = ? AND status IN ('active','overdue')");
    $check->bind_param("i", $id);
    $check->execute();
    $hasRentals = $check->get_result()->fetch_assoc()['count'];
    
    if ($hasRentals > 0) {
        $message = 'Cannot delete equipment with active rentals.';
        $messageType = 'error';
    } else {
        $stmt = $db->prepare("DELETE FROM equipment WHERE equipment_id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = 'Equipment deleted successfully.';
            $messageType = 'success';
        }
    }
}

// Handle ADD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = sanitize($db, $_POST['name']);
    $category = sanitize($db, $_POST['category']);
    $serial = sanitize($db, $_POST['serial_number']);
    $condition = sanitize($db, $_POST['condition_status']);
    $quantity = intval($_POST['total_quantity']);
    $description = sanitize($db, $_POST['description']);

    $stmt = $db->prepare("INSERT INTO equipment (name, category, serial_number, condition_status, total_quantity, available_quantity, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiis", $name, $category, $serial, $condition, $quantity, $quantity, $description);
    
    if ($stmt->execute()) {
        $message = 'Equipment added successfully.';
        $messageType = 'success';
    } else {
        $message = 'Error: Serial number may already exist.';
        $messageType = 'error';
    }
}

// Handle EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = intval($_POST['equipment_id']);
    $name = sanitize($db, $_POST['name']);
    $category = sanitize($db, $_POST['category']);
    $serial = sanitize($db, $_POST['serial_number']);
    $condition = sanitize($db, $_POST['condition_status']);
    $newTotal = intval($_POST['total_quantity']);
    $description = sanitize($db, $_POST['description']);

    // Calculate available quantity adjustment
    $current = $db->prepare("SELECT total_quantity, available_quantity FROM equipment WHERE equipment_id = ?");
    $current->bind_param("i", $id);
    $current->execute();
    $curr = $current->get_result()->fetch_assoc();
    $rented = $curr['total_quantity'] - $curr['available_quantity'];
    $newAvailable = max(0, $newTotal - $rented);

    $stmt = $db->prepare("UPDATE equipment SET name=?, category=?, serial_number=?, condition_status=?, total_quantity=?, available_quantity=?, description=? WHERE equipment_id=?");
    $stmt->bind_param("ssssiisi", $name, $category, $serial, $condition, $newTotal, $newAvailable, $description, $id);
    
    if ($stmt->execute()) {
        $message = 'Equipment updated successfully.';
        $messageType = 'success';
    } else {
        $message = 'Error updating equipment.';
        $messageType = 'error';
    }
}

// Fetch all equipment
$equipment = $db->query("SELECT * FROM equipment ORDER BY name ASC");

// Check if editing
$editItem = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $db->prepare("SELECT * FROM equipment WHERE equipment_id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editItem = $stmt->get_result()->fetch_assoc();
}

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Equipment Management</h1>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<!-- Add / Edit Form -->
<div class="card">
    <h2><?php echo $editItem ? 'Edit Equipment' : 'Add New Equipment'; ?></h2>
    <form method="POST">
        <input type="hidden" name="action" value="<?php echo $editItem ? 'edit' : 'add'; ?>">
        <?php if ($editItem): ?>
            <input type="hidden" name="equipment_id" value="<?php echo $editItem['equipment_id']; ?>">
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label for="name">Equipment Name</label>
                <input type="text" id="name" name="name" required
                       value="<?php echo $editItem ? htmlspecialchars($editItem['name']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category" name="category" required
                       placeholder="e.g. Computing, Display, Accessories"
                       value="<?php echo $editItem ? htmlspecialchars($editItem['category']) : ''; ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="serial_number">Serial Number</label>
                <input type="text" id="serial_number" name="serial_number" required
                       value="<?php echo $editItem ? htmlspecialchars($editItem['serial_number']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="condition_status">Condition</label>
                <select id="condition_status" name="condition_status" required>
                    <?php foreach (['New', 'Good', 'Fair', 'Poor'] as $cond): ?>
                    <option value="<?php echo $cond; ?>" <?php echo ($editItem && $editItem['condition_status'] === $cond) ? 'selected' : ''; ?>>
                        <?php echo $cond; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="total_quantity">Total Quantity</label>
                <input type="number" id="total_quantity" name="total_quantity" min="1" required
                       value="<?php echo $editItem ? $editItem['total_quantity'] : '1'; ?>">
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <input type="text" id="description" name="description"
                       value="<?php echo $editItem ? htmlspecialchars($editItem['description']) : ''; ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-primary"><?php echo $editItem ? 'Update Equipment' : 'Add Equipment'; ?></button>
        <?php if ($editItem): ?>
            <a href="admin_equipment.php" class="btn btn-secondary">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<!-- Equipment List -->
<div class="card">
    <h2>Equipment Inventory (<?php echo $equipment->num_rows; ?> items)</h2>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Serial No.</th>
                    <th>Condition</th>
                    <th>Total</th>
                    <th>Available</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($equipment->num_rows > 0): ?>
                    <?php while ($item = $equipment->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($item['category']); ?></td>
                        <td><?php echo htmlspecialchars($item['serial_number']); ?></td>
                        <td><span class="badge badge-<?php echo strtolower($item['condition_status']); ?>"><?php echo $item['condition_status']; ?></span></td>
                        <td><?php echo $item['total_quantity']; ?></td>
                        <td><?php echo $item['available_quantity']; ?></td>
                        <td class="actions">
                            <a href="admin_equipment.php?edit=<?php echo $item['equipment_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="admin_equipment.php?delete=<?php echo $item['equipment_id']; ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Are you sure you want to delete this equipment?');">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center" style="padding:2rem; color:#6b7280;">No equipment found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
