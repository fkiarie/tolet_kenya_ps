<?php
require 'auth/auth_check.php';
require 'config/db.php';

if (!isset($_GET['id'])) {
    die("Missing landlord ID.");
}

$id = $_GET['id'];

// Delete landlord
$stmt = $conn->prepare("DELETE FROM landlords WHERE id = ?");
$stmt->execute([$id]);

header("Location: landlords.php?success=deleted");
exit;
?>
