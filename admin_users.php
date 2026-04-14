<?php
require_once 'includes/connection.php';
if (!isLoggedIn() || !isAdmin()) { redirect('index.php'); }
$pageTitle = 'Manage Users';

$message = '';
$messageType = '';

// Handle DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Prevent deleting yourself
    if ($id === $_SESSION['user_id']) {
        $message = 'You cannot delete your own account.';
        $messageType = 'error';
    } else {
        // Check active rentals
        $check = $db->prepare("SELECT COUNT(*) as count FROM rentals WHERE user_id = ? AND status IN ('active','overdue')");
        $check->bind_param("i", $id);
        $check->execute();
        $hasRentals = $check->get_result()->fetch_assoc()['count'];

        if ($hasRentals > 0) {
            $message = 'Cannot delete user with active rentals.';
            $messageType = 'error';
        } else {
            $stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = 'User deleted successfully.';
                $messageType = 'success';
            }
        }
    }
}

// Handle ADD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $fullName = sanitize($db, $_POST['full_name']);
    $email = sanitize($db, $_POST['email']);
    $username = sanitize($db, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = sanitize($db, $_POST['role']);
    $maxRentals = intval($_POST['max_rentals']);

    $stmt = $db->prepare("INSERT INTO users (full_name, email, username, password, role, max_rentals) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $fullName, $email, $username, $password, $role, $maxRentals);
    
    if ($stmt->execute()) {
        $message = 'User added successfully.';
        $messageType = 'success';
    } else {
        $message = 'Error: Username or email may already exist.';
        $messageType = 'error';
    }
}

// Handle EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = intval($_POST['user_id']);
    $fullName = sanitize($db, $_POST['full_name']);
    $email = sanitize($db, $_POST['email']);
    $username = sanitize($db, $_POST['username']);
    $role = sanitize($db, $_POST['role']);
    $maxRentals = intval($_POST['max_rentals']);

    // Update with or without password
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET full_name=?, email=?, username=?, password=?, role=?, max_rentals=? WHERE user_id=?");
        $stmt->bind_param("sssssii", $fullName, $email, $username, $password, $role, $maxRentals, $id);
    } else {
        $stmt = $db->prepare("UPDATE users SET full_name=?, email=?, username=?, role=?, max_rentals=? WHERE user_id=?");
        $stmt->bind_param("ssssii", $fullName, $email, $username, $role, $maxRentals, $id);
    }
    
    if ($stmt->execute()) {
        $message = 'User updated successfully.';
        $messageType = 'success';
    } else {
        $message = 'Error updating user.';
        $messageType = 'error';
    }
}

// Fetch all users
$users = $db->query("SELECT u.*, (SELECT COUNT(*) FROM rentals r WHERE r.user_id = u.user_id AND r.status IN ('active','overdue')) AS active_rentals FROM users u ORDER BY u.full_name ASC");

// Check if editing
$editUser = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editUser = $stmt->get_result()->fetch_assoc();
}

include 'includes/header.php';
?>

<div class="page-header">
    <h1>User Management</h1>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<!-- Add / Edit Form -->
<div class="card">
    <h2><?php echo $editUser ? 'Edit User' : 'Add New User'; ?></h2>
    <form method="POST">
        <input type="hidden" name="action" value="<?php echo $editUser ? 'edit' : 'add'; ?>">
        <?php if ($editUser): ?>
            <input type="hidden" name="user_id" value="<?php echo $editUser['user_id']; ?>">
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" required
                       value="<?php echo $editUser ? htmlspecialchars($editUser['full_name']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo $editUser ? htmlspecialchars($editUser['email']) : ''; ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required
                       value="<?php echo $editUser ? htmlspecialchars($editUser['username']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">Password <?php echo $editUser ? '(leave blank to keep current)' : ''; ?></label>
                <input type="password" id="password" name="password" <?php echo $editUser ? '' : 'required'; ?>>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="user" <?php echo ($editUser && $editUser['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo ($editUser && $editUser['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label for="max_rentals">Max Rentals Allowed</label>
                <input type="number" id="max_rentals" name="max_rentals" min="1" max="99" required
                       value="<?php echo $editUser ? $editUser['max_rentals'] : '5'; ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-primary"><?php echo $editUser ? 'Update User' : 'Add User'; ?></button>
        <?php if ($editUser): ?>
            <a href="admin_users.php" class="btn btn-secondary">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<!-- Users List -->
<div class="card">
    <h2>All Users (<?php echo $users->num_rows; ?>)</h2>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Max Rentals</th>
                    <th>Active Rentals</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><span class="badge badge-<?php echo $user['role'] === 'admin' ? 'new' : 'good'; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                    <td><?php echo $user['max_rentals']; ?></td>
                    <td><?php echo $user['active_rentals']; ?></td>
                    <td class="actions">
                        <a href="admin_users.php?edit=<?php echo $user['user_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <?php if ($user['user_id'] !== $_SESSION['user_id']): ?>
                        <a href="admin_users.php?delete=<?php echo $user['user_id']; ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
