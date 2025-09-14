<?php
require 'auth/auth_check.php';
require 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $agent_id = $_SESSION['user_id'];
    $name = $_POST['name'];
    $national_id = $_POST['national_id'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $next_of_kin = $_POST['next_of_kin'];   // ✅ match DB
    $kin_contact = $_POST['kin_contact'];   // ✅ match DB
    $bank_details = $_POST['bank_details'];

    // Uploads folder
    $uploadDir = __DIR__ . "/uploads/";

    // Handle ID Photo
    $id_photo = null;
    if (!empty($_FILES['id_photo']['name'])) {
        $idFileName = time() . "_id_" . basename($_FILES['id_photo']['name']);
        $idTarget = $uploadDir . $idFileName;
        if (move_uploaded_file($_FILES['id_photo']['tmp_name'], $idTarget)) {
            $id_photo = "uploads/" . $idFileName; // save relative path
        }
    }

    // Handle Passport Photo
    $passport_photo = null;
    if (!empty($_FILES['passport_photo']['name'])) {
        $passportFileName = time() . "_passport_" . basename($_FILES['passport_photo']['name']);
        $passportTarget = $uploadDir . $passportFileName;
        if (move_uploaded_file($_FILES['passport_photo']['tmp_name'], $passportTarget)) {
            $passport_photo = "uploads/" . $passportFileName;
        }
    }

    // Insert into DB
    $sql = "INSERT INTO landlords 
            (agent_id, name, national_id, id_photo, phone, email, next_of_kin, kin_contact, passport_photo, bank_details) 
            VALUES 
            (:agent_id, :name, :national_id, :id_photo, :phone, :email, :next_of_kin, :kin_contact, :passport_photo, :bank_details)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':agent_id' => $agent_id,
        ':name' => $name,
        ':national_id' => $national_id,
        ':id_photo' => $id_photo,
        ':phone' => $phone,
        ':email' => $email,
        ':next_of_kin' => $next_of_kin,
        ':kin_contact' => $kin_contact,
        ':passport_photo' => $passport_photo,
        ':bank_details' => $bank_details
    ]);

    header("Location: landlords.php?success=1");
    exit;
}
