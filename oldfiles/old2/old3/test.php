<?php
initializeVariables();
switch($operation) {
    case 'sil':
        printHead();
        dbConnection();
        sil($_GET['sid']);
        initializeVariables();
        listele($page, $column, $order);
        printFooter();
        break;
    case 'ekleForm':
        printHead();
        ekleForm();
        printFooter();
        break;
    case 'ekle':
        printHead();
        echo "Ekleme case bloğu calisti!!!! ";
        dbConnection();
        ekle($_GET['fname'], $_GET['lname'], $_GET['birthplace'], $_GET['birthDate']);
        initializeVariables();
        listele($page, $column, $order);
        printFooter();
        break;
    case 'guncele':
        printHead();
        dbConnection();
        guncelle($_GET['sid'], $_GET['fname'], $_GET['lname'], $_GET['birthplace'], $_GET['birthDate']);
        initializeVariables();
        listele($page, $column, $order);
        printFooter();
        break;
    case 'degistir':
        printHead();
        dbConnection();
        degistir($_GET['sid']);
        printFooter();
        break;
    default:
        dbConnection();
        printHead();
        initializeVariables();
        listele($page, $column, $order);
        printFooter();
}

function printHead() {
    echo "head calisti!";
    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">';
    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>';
    echo '</head>';
    echo '<body>';
}

function printFooter() {
    echo '</body>';
    echo '</html>';
}

function initializeVariables() {
    global $operation, $column, $order, $page;
    $operation = isset($_GET['op']) ? $_GET['op'] : '';
    $column = isset($_GET['column']) ? $_GET['column'] : 'fname';
    $order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
}


function dbConnection() {
    global $connection;  // Bu satırı ekledik. $connection değişkenini global olarak işaretledik.
    $connection = new mysqli("localhost", "root", "", "studentweb");
    if ($connection->connect_error) {
        die("Veritabanına bağlantı kurulamadı: " . $connection->connect_error);
    }
}

function sil($sid) {
    global $connection, $page;
    $delete_query = "DELETE FROM student WHERE sid = $sid";
    $connection->query($delete_query);
}

function ekle($fname, $lname, $birthplace, $birthDate) {
    echo "ekle fonksiyonuna giriş yapıldı.<br>";
    global $connection;
    $stmt = $connection->prepare("INSERT INTO student (fname, lname, birthplace, birthdate) VALUES (?, ?, ?, ?)");
    $bind = $stmt->bind_param("ssss", $fname, $lname, $birthplace, $birthDate);

    if (!$bind) {
        die("Parameter binding failed: " . $stmt->error);
    }

    if (!$stmt->execute()) {
        die("Execution failed: " . $stmt->error);
    }

    $stmt->close();
}


function guncelle($sid, $fname, $lname, $birthplace, $birthDate) {
    global $connection;
    $update_query = "UPDATE student SET fname='$fname', lname='$lname', birthplace='$birthplace', birthDate='$birthDate' WHERE sid = $sid";
    $connection->query($update_query);
}


function degistir($sid) {
    global $connection, $page, $column, $order;
    $select_query = "SELECT * FROM student WHERE sid = $sid";
    $student = $connection->query($select_query)->fetch_assoc();

    echo "<form action='' method='get'>";
    echo "<input type='hidden' name='op' value='guncele'>";  
    echo "<input type='hidden' name='page' value='$page'>";
    echo "<input type='hidden' name='column' value='$column'>";
    echo "<input type='hidden' name='order' value='$order'>";
    echo "<input type='hidden' name='sid' value='" . $student['sid'] . "'>";
    echo "Ad: <input type='text' name='fname' value='" . $student['fname'] . "'><br>";
    echo "Soyad: <input type='text' name='lname' value='" . $student['lname'] . "'><br>";
    echo "Doğum Yeri: <input type='text' name='birthplace' value='" . $student['birthplace'] . "'><br>";
    echo "Doğum Tarihi: <input type='date' name='birthDate' value='" . $student['birthDate'] . "'><br>";
    echo "<input type='submit' value='Güncelle'>"; // name özelliğini kaldırıldı
    echo "</form>";
    
    
}

function ekleForm() {
    global $page, $column, $order;

    echo "<form action='' method='get'>"; // Burada 'action'ı boş bırakarak formun kendi sayfasına göndermesini sağlıyoruz.
    echo "<input type='hidden' name='op' value='ekle'>";  // Burada op parametresini "ekle" olarak ekledik.
    echo "<input type='hidden' name='page' value='$page'>";
    echo "<input type='hidden' name='column' value='$column'>";
    echo "<input type='hidden' name='order' value='$order'>";
    echo "Ad: <input type='text' name='fname'><br>";
    echo "Soyad: <input type='text' name='lname'><br>";
    echo "Doğum Yeri: <input type='text' name='birthplace'><br>";
    echo "Doğum Tarihi: <input type='date' name='birthDate'><br>";
    echo "<input type='submit' value='Ekle'>"; // name özelliğini kaldırdık ve value değerini "Ekle" yaptık
    echo "</form>";
}


function listele($page, $column, $order) {
    global $connection;

    $records_per_page = 5;
    $start_record = ($page - 1) * $records_per_page;

    $total_records_query = "SELECT COUNT(*) as total_records FROM student";
    $total_records_result = $connection->query($total_records_query);
    $total_records = $total_records_result->fetch_assoc()['total_records'];
    $total_pages = ceil($total_records / $records_per_page);
    
    $toggle_order = ($order === 'ASC') ? 'DESC' : 'ASC';

    $query = "SELECT * FROM student ORDER BY $column $order LIMIT $start_record, $records_per_page";
    $result = $connection->query($query);
    echo "<table  class='table table-striped'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th><a href='?column=sid&order=$toggle_order&page=$page'>No</a></th>";
    echo "<th><a href='?column=fname&order=$toggle_order&page=$page'>Ad</a></th>";
    echo "<th><a href='?column=lname&order=$toggle_order&page=$page'>Soyad</a></th>";
    echo "<th><a href='?column=birthplace&order=$toggle_order&page=$page'>Doğum Yeri</a></th>";
    echo "<th><a href='?column=birthDate&order=$toggle_order&page=$page'>Doğum Tarihi</a></th>";
    echo "<th><a href='?op=ekleForm&page=$page'>Yeni</a></th>";
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
        echo "<td><a href='?op=sil&sid=" . $row['sid'] . "&page=$page'>sil</a></td>";
        echo "<td><a href='?op=degistir&sid=" . $row['sid'] . "&page=$page'>Güncelle</a></td>";
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
    sayfalama($page, $column, $order);  // Sayfalama fonksiyonunu burada çağırıyoruz
}

function sayfalama($page, $column, $order) {
    global $connection;

    $records_per_page = 5;
    $total_records_query = "SELECT COUNT(*) as total_records FROM student";
    $total_records_result = $connection->query($total_records_query);
    $total_records = $total_records_result->fetch_assoc()['total_records'];
    $total_pages = ceil($total_records / $records_per_page);
    $toggle_order = ($order === 'ASC') ? 'DESC' : 'ASC';

    // Sayfalama için linkler
    echo "<a href='?column=$column&order=$order&page=1'><<</a>";

    if ($page > 1) {
        echo "<a href='?column=$column&order=$order&page=" . ($page - 1) . "'><</a>";
    }

    for ($i = max(1, $page - 3); $i <= min($total_pages, $page + 3); $i++) {
        $boldStyle = ($i == $page) ? 'style="font-weight: bold;"' : '';
        echo "<a href='?column=$column&order=$order&page=$i' $boldStyle>$i</a>";
    }

    if ($page < $total_pages) {
        echo "<a href='?column=$column&order=$order&page=" . ($page + 1) . "'>></a>";
    }

    echo "<a href='?column=$column&order=$order&page=$total_pages'>>>></a>";
}


?>
