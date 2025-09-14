<?php
require 'auth/auth_check.php';
require 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $building_id = $_POST['building_id'];
    $unit_number = $_POST['unit_number'];
    $floor = $_POST['floor'];
    $type = $_POST['type'];
    $rent = $_POST['rent'];
    $status = $_POST['status'];

    $sql = "UPDATE units 
            SET building_id = ?, unit_number = ?, floor = ?, type = ?, rent = ?, status = ?
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$building_id, $unit_number, $floor, $type, $rent, $status, $id]);

    header("Location: units.php?success=updated");
    exit;
}
?>
