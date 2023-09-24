<?php

$connection = new mysqli("localhost", "root", "", "studentweb");

// Parametre olarak gelen öğrenci ID'sini al
$sid = $_GET['no'];

// SQL sorgusunu hazırla ve çalıştır
$stmt = $connection->prepare("DELETE FROM student WHERE sid=?");
$stmt->bind_param("i", $sid);

if ($stmt->execute()) {
    // Eğer silme işlemi başarılıysa
    echo json_encode(['success' => true]);
} else {
    // Eğer silme işlemi başarısızsa
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
?>
