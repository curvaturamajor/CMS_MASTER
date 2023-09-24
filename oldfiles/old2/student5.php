<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">  <title>Document</title>
</head>
<body>

<?php

$connection = new mysqli("localhost", "root", "", "studentweb");

$records_per_page = 5;
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start_record = ($current_page - 1) * $records_per_page;

$total_records_query = "SELECT COUNT(*) as total_records FROM student";
$total_records_result = $connection->query($total_records_query);
$total_records = $total_records_result->fetch_assoc()['total_records'];
$total_pages = ceil($total_records / $records_per_page);


$allowed_columns = ['sid', 'fname', 'lname', 'birthplace', 'birthDate'];
$sort_column = (isset($_GET['column']) && in_array($_GET['column'], $allowed_columns)) ? $_GET['column'] : 'fname';
$sort_order = (isset($_GET['order']) && $_GET['order'] === 'DESC') ? 'DESC' : 'ASC';

$toggle_order = ($sort_order === 'ASC') ? 'DESC' : 'ASC';

// Adding a new student
if(isset($_POST['submit'])) {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $birthplace = $_POST['birthplace'];
    $birthdate = $_POST['birthdate'];

    $stmt = $connection->prepare("INSERT INTO student (fname, lname, birthplace, birthdate) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $fname, $lname, $birthplace, $birthdate);
    $stmt->execute();
    $stmt->close();
}

// If 'Yeni' link is clicked
if(isset($_GET['op']) && $_GET['op'] == 'yeni') {
    echo "<form action='?page=$current_page' method='post'>";
    echo "Ad: <input type='text' name='fname'><br>";
    echo "Soyad: <input type='text' name='lname'><br>";
    echo "Doğum Yeri: <input type='text' name='birthplace'><br>";
    echo "Doğum Tarihi: <input type='date' name='birthdate'><br>";
    echo "<input type='submit' name='submit' value='Ekle'>";
    echo "</form>";
}

// Öğrenciyi silme
if(isset($_GET['op']) && $_GET['op'] == 'sil' && isset($_GET['sid'])) {
    $sid = intval($_GET['sid']);
    $delete_query = "DELETE FROM student WHERE sid = $sid";
    $connection->query($delete_query);
    header("Location: ?page=$current_page"); // Refresh the page after deletion
}

// Öğrenci bilgilerini güncelleme
if(isset($_POST['submit']) && $_POST['submit'] == 'Güncelle') {
    $sid = intval($_POST['sid']);
    $fname = $connection->real_escape_string($_POST['fname']);
    $lname = $connection->real_escape_string($_POST['lname']);
    $birthplace = $connection->real_escape_string($_POST['birthplace']);
    $birthdate = $connection->real_escape_string($_POST['birthdate']);

    $update_query = "UPDATE student SET fname='$fname', lname='$lname', birthplace='$birthplace', birthDate='$birthdate' WHERE sid = $sid";
    $connection->query($update_query);
    header("Location: ?page=$current_page"); // Refresh the page after updating
}

$query = "SELECT * FROM student ORDER BY $sort_column $sort_order LIMIT $start_record, $records_per_page";
$result = $connection->query($query);

echo "<table  class='table table-striped'>";
echo "<thead>";
echo "<tr>";
echo "<th><a href='?column=sid&order=$toggle_order&page=$current_page'>No</a></th>";
echo "<th><a href='?column=fname&order=$toggle_order&page=$current_page'>Ad</a></th>";
echo "<th><a href='?column=lname&order=$toggle_order&page=$current_page'>Soyad</a></th>";
echo "<th><a href='?column=birthplace&order=$toggle_order&page=$current_page'>Doğum Yeri</a></th>";
echo "<th><a href='?column=birthDate&order=$toggle_order&page=$current_page'>Doğum Tarihi</a></th>";
echo "<th><a href='?op=yeni&page=$current_page'>Yeni</a></th>";
echo "<th></th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['sid'] . "</td>";
    echo "<td>" . $row['fname'] . "</td>";
    echo "<td>" . $row['lname'] . "</td>";
    echo "<td>" . $row['birthplace'] . "</td>";
    echo "<td>" . $row['birthDate'] . "</td>";
    echo "<td><a href='?op=sil&sid=" . $row['sid'] . "&page=$current_page'>sil</a></td>";
    echo "<td><a href='?op=guncele&sid=" . $row['sid'] . "&page=$current_page'>Güncelle</a></td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";

// 'Güncelle' linkine tıklandığında formun görüntülenmesi
if(isset($_GET['op']) && $_GET['op'] == 'guncele' && isset($_GET['sid'])) {
    $sid = intval($_GET['sid']);
    $select_query = "SELECT * FROM student WHERE sid = $sid";
    $student = $connection->query($select_query)->fetch_assoc();

    echo "<form action='?page=$current_page' method='post'>";
    echo "<input type='hidden' name='sid' value='" . $student['sid'] . "'>";
    echo "Ad: <input type='text' name='fname' value='" . $student['fname'] . "'><br>";
    echo "Soyad: <input type='text' name='lname' value='" . $student['lname'] . "'><br>";
    echo "Doğum Yeri: <input type='text' name='birthplace' value='" . $student['birthplace'] . "'><br>";
    echo "Doğum Tarihi: <input type='date' name='birthdate' value='" . $student['birthDate'] . "'><br>";
    echo "<input type='submit' name='submit' value='Güncelle'>";
    echo "</form>";
}


?>

<!-- Sayfalama için linkler -->
<a href="?page=1"><<</a> <!-- İlk Sayfa -->
<?php if($current_page > 1): ?>
<a href="?page=<?php echo ($current_page - 1); ?>"><</a> <!-- Önceki Sayfa -->
<?php endif; ?>

<?php 
// Mevcut sayfanın 3 sayfa gerisinden başlayarak 3 sayfa sonrasına kadar olan sayfa numaralarını ekleyin
for($i = max(1, $current_page - 3); $i <= min($total_pages, $current_page + 3); $i++): ?>
    <a href="?page=<?php echo $i; ?>" <?php echo ($i == $current_page) ? 'style="font-weight: bold;"' : ''; ?>><?php echo $i; ?></a>
<?php endfor; ?>

<?php if($current_page < $total_pages): ?>
<a href="?page=<?php echo ($current_page + 1); ?>">></a> <!-- Sonraki Sayfa -->
<?php endif; ?>
<a href="?page=<?php echo $total_pages; ?>">>></a> <!-- Son Sayfa -->