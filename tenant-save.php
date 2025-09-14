<?php
require 'auth/auth_check.php';
require 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $national_id = $_POST['national_id'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $next_of_kin = $_POST['next_of_kin'];
    $kin_contact = $_POST['kin_contact'];

    $id_photo = null;
    $passport_photo = null;

    $uploadDir = "uploads/";

    if (!empty($_FILES['id_photo']['name'])) {
        $id_photo = time() . "_id_" . basename($_FILES['id_photo']['name']);
        move_uploaded_file($_FILES['id_photo']['tmp_name'], $uploadDir . $id_photo);
    }

    if (!empty($_FILES['passport_photo']['name'])) {
        $passport_photo = time() . "_passport_" . basename($_FILES['passport_photo']['name']);
        move_uploaded_file($_FILES['passport_photo']['tmp_name'], $uploadDir . $passport_photo);
    }

    $sql = "INSERT INTO tenants (name, national_id, id_photo, phone, email, next_of_kin, kin_contact, passport_photo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$name, $national_id, $id_photo, $phone, $email, $next_of_kin, $kin_contact, $passport_photo]);

    header("Location: tenants.php?success=added");
    exit;
}
?>
