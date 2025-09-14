<?php
require 'auth/auth_check.php';
require 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $national_id = $_POST['national_id'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $next_of_kin = $_POST['next_of_kin'];
    $kin_contact = $_POST['kin_contact'];

    // Fetch current tenant to check old photos
    $stmt = $conn->prepare("SELECT id_photo, passport_photo FROM tenants WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);

    $id_photo = $current['id_photo'];
    $passport_photo = $current['passport_photo'];

    $uploadDir = "uploads/";

    // Handle ID photo update
    if (!empty($_FILES['id_photo']['name'])) {
        // Delete old file if exists
        if ($id_photo && file_exists($uploadDir . $id_photo)) {
            unlink($uploadDir . $id_photo);
        }
        $id_photo = time() . "_id_" . basename($_FILES['id_photo']['name']);
        move_uploaded_file($_FILES['id_photo']['tmp_name'], $uploadDir . $id_photo);
    }

    // Handle passport photo update
    if (!empty($_FILES['passport_photo']['name'])) {
        if ($passport_photo && file_exists($uploadDir . $passport_photo)) {
            unlink($uploadDir . $passport_photo);
        }
        $passport_photo = time() . "_passport_" . basename($_FILES['passport_photo']['name']);
        move_uploaded_file($_FILES['passport_photo']['tmp_name'], $uploadDir . $passport_photo);
    }

    $sql = "UPDATE tenants 
            SET name = ?, national_id = ?, phone = ?, email = ?, 
                next_of_kin = ?, kin_contact = ?, id_photo = ?, passport_photo = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$name, $national_id, $phone, $email, $next_of_kin, $kin_contact, $id_photo, $passport_photo, $id]);

    header("Location: tenants.php?success=updated");
    exit;
}
?>
