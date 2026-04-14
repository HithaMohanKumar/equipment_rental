<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Equipment Rental System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <a href="index.php">&#128736; EquipRent</a>
        </div>
        <div class="nav-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin_dashboard.php">Dashboard</a>
                    <a href="admin_equipment.php">Equipment</a>
                    <a href="admin_users.php">Users</a>
                    <a href="admin_rentals.php">All Rentals</a>
                <?php else: ?>
                    <a href="user_dashboard.php">Dashboard</a>
                    <a href="user_search.php">Search Equipment</a>
                    <a href="user_rentals.php">My Rentals</a>
                <?php endif; ?>
                <span class="nav-user">Hello, <?php echo htmlspecialchars($_SESSION['full_name']); ?> (<?php echo $_SESSION['role']; ?>)</span>
                <a href="logout.php" class="btn-logout">Logout</a>
            <?php else: ?>
                <a href="index.php">Login</a>
            <?php endif; ?>
        </div>
    </nav>
    <main class="container">
