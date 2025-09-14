<?php
require 'auth/auth_check.php';
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $building_id = $_POST['building_id'];
    $unit_number = $_POST['unit_number'];
    $floor = $_POST['floor'] ?? null;
    $type = $_POST['type'];
    $rent = $_POST['rent'];
    $status = $_POST['status'];
    $tenant_id = !empty($_POST['tenant_id']) ? $_POST['tenant_id'] : null;

    $stmt = $conn->prepare("INSERT INTO units 
        (building_id, unit_number, floor, type, rent, status, tenant_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([$building_id, $unit_number, $floor, $type, $rent, $status, $tenant_id]);

    header("Location: units.php?success=1");
    exit();
}