<?php
require 'auth/auth_check.php';
require 'config/db.php';

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("DELETE FROM units WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}

header("Location: units.php?success=deleted");
exit();
