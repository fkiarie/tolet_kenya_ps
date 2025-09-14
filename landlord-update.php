<?php
require 'auth/auth_check.php';
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $national_id = $_POST['national_id'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $next_of_kin = $_POST['next_of_kin'];
    $kin_contact = $_POST['kin_contact'];
    $bank_details = $_POST['bank_details'];

    // Handle file uploads
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $id_photo = $_FILES['id_photo']['name'] 
        ? $uploadDir . time() . "_id_" . basename($_FILES['id_photo']['name']) 
        : null;
    $passport_photo = $_FILES['passport_photo']['name'] 
        ? $uploadDir . time() . "_passport_" . basename($_FILES['passport_photo']['name']) 
        : null;

    if ($id_photo) move_uploaded_file($_FILES['id_photo']['tmp_name'], $id_photo);
    if ($passport_photo) move_uploaded_file($_FILES['passport_photo']['tmp_name'], $passport_photo);

    // Build SQL dynamically to only update uploaded fields if present
    $sql = "UPDATE landlords SET name=?, national_id=?, phone=?, email=?, next_of_kin=?, kin_contact=?, bank_details=?";
    $params = [$name, $national_id, $phone, $email, $next_of_kin, $kin_contact, $bank_details];

    if ($id_photo) {
        $sql .= ", id_photo=?";
        $params[] = $id_photo;
    }
    if ($passport_photo) {
        $sql .= ", passport_photo=?";
        $params[] = $passport_photo;
    }

    $sql .= " WHERE id=?";
    $params[] = $id;

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    header("Location: landlords.php?success=updated");
    exit;
}
?>
