<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'auth/auth_check.php';
require 'config/db.php';

// Ensure agent is logged in
$agent_id = $_SESSION['agent_id'] ?? null;
if (!$agent_id) {
    die("Unauthorized: Agent not found in session.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name        = $_POST['name'];
    $national_id = $_POST['national_id'];
    $phone       = $_POST['phone'];
    $email       = $_POST['email'];
    $next_of_kin = $_POST['next_of_kin'];
    $kin_contact = $_POST['kin_contact'];

    $id_photo = null;
    $passport_photo = null;
    $uploadDir = "uploads/";

    // ID photo
    if (!empty($_FILES['id_photo']['name'])) {
        $id_photo = time() . "_id_" . basename($_FILES['id_photo']['name']);
        move_uploaded_file($_FILES['id_photo']['tmp_name'], $uploadDir . $id_photo);
    }

    // Passport photo
    if (!empty($_FILES['passport_photo']['name'])) {
        $passport_photo = time() . "_passport_" . basename($_FILES['passport_photo']['name']);
        move_uploaded_file($_FILES['passport_photo']['tmp_name'], $uploadDir . $passport_photo);
    }

    // Insert tenant (linked to this agent)
    $sql = "INSERT INTO tenants (name, national_id, id_photo, phone, email, next_of_kin, kin_contact, passport_photo, agent_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $name,
        $national_id,
        $id_photo,
        $phone,
        $email,
        $next_of_kin,
        $kin_contact,
        $passport_photo,
        $agent_id
    ]);

    header("Location: tenants.php?success=added");
    exit;
}
?>
