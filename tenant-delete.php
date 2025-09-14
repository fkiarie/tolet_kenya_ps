<?php
require 'auth/auth_check.php';
require 'config/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch tenant photos before deleting
    $stmt = $conn->prepare("SELECT id_photo, passport_photo FROM tenants WHERE id = ?");
    $stmt->execute([$id]);
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($tenant) {
        $uploadDir = "uploads/";

        // Delete photos if exist
        if ($tenant['id_photo'] && file_exists($uploadDir . $tenant['id_photo'])) {
            unlink($uploadDir . $tenant['id_photo']);
        }
        if ($tenant['passport_photo'] && file_exists($uploadDir . $tenant['passport_photo'])) {
            unlink($uploadDir . $tenant['passport_photo']);
        }

        // Delete tenant record
        $stmt = $conn->prepare("DELETE FROM tenants WHERE id = ?");
        $stmt->execute([$id]);
    }

    header("Location: tenants.php?success=deleted");
    exit;
}
?>
