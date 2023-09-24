<?php

$connection = new mysqli("localhost", "root", "", "studentweb");

// Parametreleri al
$sid = $_GET['sid'];
$fname = $_GET['ad'];
$lname = $_GET['soyad'];
$birthplace = $_GET['dyer'];
$birthdate = $_GET['dtarih'];

// SQL sorgusunu hazırla ve çalıştır
$stmt = $connection->prepare("UPDATE student SET fname=?, lname=?, birthplace=?, birthdate=? WHERE sid=?");
$stmt->bind_param("ssssi", $fname, $lname, $birthplace, $birthdate, $sid);

if ($stmt->execute()) {
    // Eğer güncelleme başarılıysa
    echo json_encode(['success' => true]);
} else {
    // Eğer güncelleme başarısızsa
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
?>
