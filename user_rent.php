<?php
require_once 'includes/connection.php';
if (!isLoggedIn() || isAdmin()) { redirect('index.php'); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('user_search.php'); }

$userId = $_SESSION['user_id'];
$equipmentId = intval($_POST['equipment_id']);
$quantity = intval($_POST['quantity']);
$duration = intval($_POST['duration']);

// Validate duration
if (!in_array($duration, [7, 14, 30])) {
    redirect('user_search.php?error=Invalid rental duration.');
}

// Check user rental limit
$userInfo = $db->query("SELECT max_rentals FROM users WHERE user_id = $userId")->fetch_assoc();
$currentRentals = $db->query("SELECT COUNT(*) as count FROM rentals WHERE user_id = $userId AND status IN ('active','overdue')")->fetch_assoc()['count'];

if ($currentRentals + $quantity > $userInfo['max_rentals']) {
    redirect('user_search.php?error=You have reached your rental limit.');
}

// Check equipment availability
$equip = $db->query("SELECT * FROM equipment WHERE equipment_id = $equipmentId")->fetch_assoc();
if (!$equip || $equip['available_quantity'] < $quantity) {
    redirect('user_search.php?error=Not enough stock available.');
}

// Calculate due date
$dueDate = date('Y-m-d H:i:s', strtotime("+$duration days"));

// Begin transaction
$db->begin_transaction();

try {
    // Create rental record
    $stmt = $db->prepare("INSERT INTO rentals (user_id, equipment_id, quantity, due_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $userId, $equipmentId, $quantity, $dueDate);
    $stmt->execute();

    // Reduce available quantity
    $stmt2 = $db->prepare("UPDATE equipment SET available_quantity = available_quantity - ? WHERE equipment_id = ?");
    $stmt2->bind_param("ii", $quantity, $equipmentId);
    $stmt2->execute();

    $db->commit();
    redirect('user_search.php?rented=1');
} catch (Exception $e) {
    $db->rollback();
    redirect('user_search.php?error=An error occurred. Please try again.');
}
?>
