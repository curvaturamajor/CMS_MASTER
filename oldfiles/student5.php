<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>Document</title>
</head>
<body>
<br><br>

<?php
$connection = new mysqli("localhost", "root", "", "studentweb");
$operation = isset($_GET['is']) ? $_GET['is'] : '';

switch ($operation) {
    case 'sil':
        sil($_GET['sid']);
        listele();
        break;
    case 'ekleForm':  // Bu satırı ekleyin
        ekleForm();   // ve bu satırı
        break;
    case 'ekle':
        ekle($_GET['fname'], $_GET['lname'], $_GET['birthplace'], $_GET['birthDate']);
        listele();
        break;
    case 'guncelle':
        guncelle($_GET['sid'], $_GET['fname'], $_GET['lname'], $_GET['birthplace'], $_GET['birthDate']);
        listele();
        break;
    case 'guncele':
        form($_GET['sid']);
        break;
    default:
        listele();
        break;
}

function sil($sid) {
    global $connection;
    $delete_query = "DELETE FROM student WHERE sid = $sid";
    $connection->query($delete_query);
}

function ekle($fname, $lname, $birthplace, $birthDate) {
    global $connection;
    $stmt = $connection->prepare("INSERT INTO student (fname, lname, birthplace, birthDate) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $fname, $lname, $birthplace, $birthDate);
    $stmt->execute();
    $stmt->close();

    // Ekleme işlemi tamamlandıktan sonra ana sayfaya yönlendirme
    header("Location: student5.php");
    exit;
}

function ekleForm() {
    echo "<form action='?is=ekle' method='get'>";
    echo "Ad: <input type='text' name='fname'><br>";
    echo "Soyad: <input type='text' name='lname'><br>";
    echo "Doğum Yeri: <input type='text' name='birthplace'><br>";
    echo "Doğum Tarihi: <input type='date' name='birthDate'><br>";
    echo "<input type='submit' value='Ekle'>";
    echo "</form>";
}

function guncelle($sid, $fname, $lname, $birthplace, $birthDate) {
    global $connection;
    
    $fname = $connection->real_escape_string($fname);
    $lname = $connection->real_escape_string($lname);
    $birthplace = $connection->real_escape_string($birthplace);
    $birthDate = $connection->real_escape_string($birthDate);

    $update_query = "UPDATE student SET fname='$fname', lname='$lname', birthplace='$birthplace', birthDate='$birthDate' WHERE sid = $sid";
    if ($connection->affected_rows == 0) {
        echo "Hiçbir satır güncellenmedi.";
    }
    // SQL sorgusunu ekrana yazdır
    echo $update_query;
    
    if ($connection->query($update_query) === FALSE) {
        // Olası bir MySQL hatasını ekrana yazdır
        echo "<br>Güncelleme Hatası: " . $connection->error;
    }
}



function form($sid) {
    global $connection;
    $select_query = "SELECT * FROM student WHERE sid = $sid";
    $student = $connection->query($select_query)->fetch_assoc();

    echo "<form action='?is=guncelle' method='get'>";
    echo "<input type='hidden' name='sid' value='" . $student['sid'] . "'>";
    echo "Ad: <input type='text' name='fname' value='" . $student['fname'] . "'><br>";
    echo "Soyad: <input type='text' name='lname' value='" . $student['lname'] . "'><br>";
    echo "Doğum Yeri: <input type='text' name='birthplace' value='" . $student['birthplace'] . "'><br>";
    echo "Doğum Tarihi: <input type='date' name='birthDate' value='" . $student['birthDate'] . "'><br>";
    echo "<input type='submit' name='submit' value='Güncelle'>";
    echo "</form>";
}
function listele() {
    global $connection;

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

    $base_link = "?column=$sort_column&order=$sort_order";

    

    $query = "SELECT * FROM student ORDER BY $sort_column $sort_order LIMIT $start_record, $records_per_page";
    $result = $connection->query($query);

    echo "<table class='table table-striped'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th><a href='?column=sid&order=$toggle_order&page=$current_page'>No</a></th>";
    echo "<th><a href='?column=fname&order=$toggle_order&page=$current_page'>Ad</a></th>";
    echo "<th><a href='?column=lname&order=$toggle_order&page=$current_page'>Soyad</a></th>";
    echo "<th><a href='?column=birthplace&order=$toggle_order&page=$current_page'>Doğum Yeri</a></th>";
    echo "<th><a href='?column=birthDate&order=$toggle_order&page=$current_page'>Doğum Tarihi</a></th>";
    echo "<th><a href='?is=ekleForm'>Yeni</a></th>";  // 'ekleForm' olarak değiştirildi
    echo "<th></th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['sid'] . "</td>";
        echo "<td>" . $row['fname'] . "</td>";
        echo "<td>" . $row['lname'] . "</td>";
        echo "<td>" . $row['birthplace'] . "</td>";
        echo "<td>" . $row['birthDate'] . "</td>";
        echo "<td><a href='?is=sil&sid=" . $row['sid'] . "&page=$current_page'>sil</a></td>";
        echo "<td><a href='?is=guncele&sid=" . $row['sid'] . "&page=$current_page'>Güncelle</a></td>";
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";

// Sayfalama için linkler
echo "<a href='" . $base_link . "&page=1'><<</a> ";  // İlk Sayfa
if ($current_page > 1) {
    echo "<a href='" . $base_link . "&page=" . ($current_page - 1) . "'><</a> ";  // Önceki Sayfa
}
for ($i = max(1, $current_page - 3); $i <= min($total_pages, $current_page + 3); $i++) {
    echo "<a href='" . $base_link . "&page=$i' " . ($i == $current_page ? 'style="font-weight: bold;"' : '') . ">$i</a> ";
}
if ($current_page < $total_pages) {
    echo "<a href='" . $base_link . "&page=" . ($current_page + 1) . "'>></a> ";  // Sonraki Sayfa
}
echo "<a href='" . $base_link . "&page=$total_pages'>>></a>";  // Son Sayfa
}

?>

</body>
</html>
