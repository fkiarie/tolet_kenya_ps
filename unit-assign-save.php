<?php
require 'auth/auth_check.php';
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unit_id'], $_POST['tenant_id'])) {
    $unit_id = $_POST['unit_id'];
    $tenant_id = $_POST['tenant_id'];

    // Assign unit
    $stmt = $conn->prepare("UPDATE units SET tenant_id = ?, status = 'occupied' WHERE id = ?");
    $stmt->execute([$tenant_id, $unit_id]);

    header("Location: tenant-view.php?id=" . $tenant_id . "&success=Unit assigned successfully");
    exit;
} else {
    header("Location: tenants.php?error=Invalid request");
    exit;
}
