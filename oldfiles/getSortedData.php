<?php
$connection = new mysqli("localhost", "root", "", "studentweb");

// Kullanıcının belirttiği sıralama türü ve yönü
$sortType = $_GET['sortType'] ?? 'sid';
$sortOrder = $_GET['sortOrder'] ?? 'ASC';

// Hangi sayfada olduğumuzu ve her sayfada kaç kayıt gösterilmesi gerektiğini belirt
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;

// SQL sorgusunda kullanılacak ofseti belirle
$offset = ($page - 1) * $limit;

// Toplam öğrenci sayısını alarak toplam sayfa sayısını hesapla
$totalStudentsQuery = $connection->query("SELECT COUNT(*) as total FROM student");
$totalStudents = $totalStudentsQuery->fetch_assoc()['total'];
$totalPages = ceil($totalStudents / $limit);

// Sıralama ve limitlendirme ile öğrenci verilerini çek
$query = "SELECT * FROM student ORDER BY $sortType $sortOrder LIMIT $offset, $limit";
$result = $connection->query($query);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data['students'][] = $row;
}

// Toplam sayfa sayısını da verilere ekle
$data['totalPages'] = $totalPages;

// JSON olarak sonucu döndür
echo json_encode($data);
?>
