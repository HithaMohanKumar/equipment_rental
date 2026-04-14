<?php
require_once 'includes/connection.php';
if (!isLoggedIn() || isAdmin()) { redirect('index.php'); }

$rentalId = intval($_GET['id']);
$userId = $_SESSION['user_id'];

// Verify this rental belongs to the current user and is active/overdue
$stmt = $db->prepare("SELECT * FROM rentals WHERE rental_id = ? AND user_id = ? AND status IN ('active', 'overdue')");
$stmt->bind_param("ii", $rentalId, $userId);
$stmt->execute();
$rental = $stmt->get_result()->fetch_assoc();

if (!$rental) {
    redirect('user_dashboard.php');
}

// Begin transaction
$db->begin_transaction();

try {
    // Update rental record
    $returnDate = date('Y-m-d H:i:s');
    $stmt = $db->prepare("UPDATE rentals SET status = 'returned', return_date = ? WHERE rental_id = ?");
    $stmt->bind_param("si", $returnDate, $rentalId);
    $stmt->execute();

    // Increase available quantity
    $stmt2 = $db->prepare("UPDATE equipment SET available_quantity = available_quantity + ? WHERE equipment_id = ?");
    $stmt2->bind_param("ii", $rental['quantity'], $rental['equipment_id']);
    $stmt2->execute();

    $db->commit();
    redirect('user_dashboard.php?returned=1');
} catch (Exception $e) {
    $db->rollback();
    redirect('user_dashboard.php');
}
?>
