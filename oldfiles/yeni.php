<?php

$connection = new mysqli("localhost", "root", "", "studentweb");

$fname = $_GET['ad'];
$lname = $_GET['soyad'];
$birthplace = $_GET['dyer'];
$birthdate = $_GET['dtarih'];

$stmt = $connection->prepare("INSERT INTO student (fname, lname, birthplace, birthdate) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $fname, $lname, $birthplace, $birthdate);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'sid' => $connection->insert_id]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
$stmt->close();
?>
