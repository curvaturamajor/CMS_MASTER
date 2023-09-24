<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>Öğrenci Yönetimi</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let headers = document.querySelectorAll('.sortable');
    headers.forEach(header => {
        header.addEventListener('click', function () {
            let currentSort = header.getAttribute('data-sort');
            let sortOrder = 'ASC';
            if (header.getAttribute('data-order') === 'ASC') {
                sortOrder = 'DESC';
            }
            header.setAttribute('data-order', sortOrder);
            getSortedData(currentSort, sortOrder);
        });
    });
    document.querySelector('.table tbody').addEventListener('click', function(e) {
        if (e.target && e.target.nodeName === "BUTTON") {
            const sid = e.target.closest('tr').getAttribute('data-id');
            if (e.target.innerText === "Güncelle") {
                guncelle(sid);
            } else if (e.target.innerText === "Sil") {
                sil(sid);
            }
        }
    });
});
</script>

<script>
function yeni() {
    let fname = document.getElementById("new_fname").value;
    let lname = document.getElementById("new_lname").value;
    let birthplace = document.getElementById("new_birthplace").value;
    let birthdate = document.getElementById("new_birthdate").value;

    let xhr = new XMLHttpRequest();
    xhr.open("GET", `yeni.php?ad=${fname}&soyad=${lname}&dyer=${birthplace}&dtarih=${birthdate}`, true);
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            let response = JSON.parse(this.responseText);
            if (response.success) {
                let table = document.querySelector(".table tbody");
                let newRow = table.insertRow();
                newRow.insertCell(0).innerText = response.sid;
                newRow.insertCell(1).innerText = fname;
                newRow.insertCell(2).innerText = lname;
                newRow.insertCell(3).innerText = birthplace;
                newRow.insertCell(4).innerText = birthdate;
                newRow.insertCell(5).innerHTML = `<button onclick="sil(${response.sid})">Sil</button>`;
                newRow.insertCell(6).innerHTML = `<button onclick="guncelle(${response.sid})">Güncelle</button>`;
            } else {
                alert("Error: " + response.error);
            }
        }
    };
    xhr.send();
}


    function sil(sid) {
        let xhr = new XMLHttpRequest();
        xhr.open("GET", `sil.php?no=${sid}`, true);
        xhr.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                let response = JSON.parse(this.responseText);
                if (response.success) {
                    let table = document.querySelector(".table tbody");
                    table.deleteRow(sid - 1);
                } else {
                    alert("Error: " + response.error);
                }
            }
        };
        xhr.send();
    }

    function guncelle(sid) {
    // Diğer açık olan satırları kapat
    document.querySelectorAll("tr[data-editing='true']").forEach(row => {
        let cells = row.querySelectorAll("td");
        for (let i = 1; i <= 4; i++) {
            let input = cells[i].querySelector("input");
            if (input) {  // Input varsa değerini alıp hücreye yaz
                cells[i].innerText = input.value;
            }
        }

        let updateBtn = row.querySelector("button[onclick^='sakla']");
        if (updateBtn) {  // Eğer Sakla butonu varsa, Güncelle'ye çevir
            updateBtn.innerText = "Güncelle";
            updateBtn.onclick = function() {
                guncelle(sid);
            };
        }
        row.removeAttribute("data-editing");
    });

    // Şimdi bu satırı düzenleniyor olarak işaretle
    let row = document.querySelector(`tr[data-id="${sid}"]`);
    row.setAttribute("data-editing", "true");
    let cells = row.querySelectorAll("td");

    // Hücreleri düzenlenebilir kutulara dönüştür
    for (let i = 1; i <= 4; i++) { 
        let cellValue = cells[i].innerText;
        cells[i].innerHTML = `<input type="text" value="${cellValue}" />`;
    }

    // Butonları güncelle
    let updateBtn = row.querySelector("button[onclick^='guncelle']");
    updateBtn.innerText = "Sakla";
    updateBtn.onclick = function() {
        sakla(sid);
    };
}

document.querySelector('.table thead').addEventListener('click', function() {
    if(document.querySelector('.table thead')) {
        resetUpdateButtons();
    }
});

function sakla(sid) {
    let row = document.querySelector(`tr[data-id="${sid}"]`);
    let inputs = row.querySelectorAll("input");

    let fname = inputs[0].value;
    let lname = inputs[1].value;
    let birthplace = inputs[2].value;
    let birthdate = inputs[3].value;

    let xhr = new XMLHttpRequest();
    xhr.open("GET", `guncelle.php?sid=${sid}&ad=${fname}&soyad=${lname}&dyer=${birthplace}&dtarih=${birthdate}`, true);
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            let response = JSON.parse(this.responseText);
            if (response.success) {
                for(let i=0; i < inputs.length; i++) {
                    inputs[i].parentElement.innerText = inputs[i].value;
                }

                let buttons = row.querySelectorAll("button");
                buttons.forEach(button => {
                    if (button.innerText.trim() === "Sakla") {
                        button.innerText = "Güncelle";
                        button.setAttribute("onclick", `guncelle(${sid})`);
                    }
                });
            } else {
                console.error("Hata: " + response.error);
            }
        }
    };
    xhr.send();
}




// Sakla butonuna basılmadıysa ve diğer butonlara basıldığında veya sıralama değiştirildiğinde Sakla'nın tekrar Güncelle'ye dönüşmesi için
document.addEventListener('click', function(e) {
    if (e.target && (e.target.innerText === "Sil" || e.target.innerText === "Oluştur" || e.target.innerText === "Ara")) {
        resetUpdateButtons();
    }
});

function resetUpdateButtons() {
    document.querySelectorAll("button[onclick^='sakla']").forEach(button => {
        let sid = button.closest('tr').getAttribute('data-id');
        button.innerText = "Güncelle";
        button.onclick = function() {
            guncelle(sid);
        };
    });
}

document.addEventListener('DOMContentLoaded', function () {
    let sortType = '';  // hangi sütuna göre sıralama yapılacağını belirtir
    let sortOrder = 'ASC'; // sıralamanın yönü (ASC veya DESC)

    let headers = document.querySelectorAll('.sortable');
    headers.forEach(header => {
        header.addEventListener('click', function () {
            let currentSort = header.getAttribute('data-sort');
            if (sortType === currentSort) {
                sortOrder = sortOrder === 'ASC' ? 'DESC' : 'ASC';
            } else {
                sortType = currentSort;
                sortOrder = 'ASC';
            }
            getSortedData(sortType, sortOrder);
        });
    });
});

function getSortedData(sortType, sortOrder) {
    // Bu fonksiyon AJAX ile veritabanından sıralı veriyi çeker ve tabloyu günceller
    // Örnek olarak aşağıdaki şekilde yapabilirsiniz:

    let xhr = new XMLHttpRequest();
    xhr.open("GET", `getSortedData.php?sortType=${sortType}&sortOrder=${sortOrder}`, true);
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            // Bu örnekte veriyi tekrar oluşturmak için basit bir yöntem kullanılmıştır. 
            let data = JSON.parse(this.responseText);
            let table = document.querySelector(".table tbody");
            table.innerHTML = '';
            data.forEach(row => {
                let newRow = table.insertRow();
                newRow.setAttribute('data-id', row.sid);
                newRow.insertCell(0).innerText = row.sid;
                newRow.insertCell(1).innerText = row.fname;
                newRow.insertCell(2).innerText = row.lname;
                newRow.insertCell(3).innerText = row.birthplace;
                newRow.insertCell(4).innerText = row.birthDate;
                newRow.insertCell(5).innerHTML = `<button onclick="sil(${row.sid})">Sil</button>`;
                newRow.insertCell(6).innerHTML = `<button onclick="guncelle(${row.sid})">Güncelle</button>`;
            });
        }
    };
    xhr.send();
}
</script>
</head>

<body>
<?php
$connection = new mysqli("localhost", "root", "", "studentweb");
$query = "SELECT * FROM student";
$result = $connection->query($query);
?>

    <br><br>

 <table class="table table-striped">
        <thead>
            <tr>
                <th class="sortable" data-sort="sid">No</th>
                <th class="sortable" data-sort="fname">Ad</th>
                <th class="sortable" data-sort="lname">Soyad</th>
                <th class="sortable" data-sort="birthplace">Doğum Yeri</th>
                <th class="sortable" data-sort="birthDate">Doğum Tarihi</th>
                <th> </th>
                <th> </th>
            </tr>
            <tr>
            <!-- Yeni öğrenci ekleme hücreleri -->
            <td></td>
            <td><input type="text" id="new_fname"></td>
            <td><input type="text" id="new_lname"></td>
            <td><input type="text" id="new_birthplace"></td>
            <td><input type="date" id="new_birthdate"></td>
            <td><button onclick="yeni()">Oluştur</button></td>
            <td></td>
        </tr>
        <tr>
            <!-- Öğrenci arama hücreleri -->
            <td></td>
            <td><input type="text" id="search_fname"></td>
            <td><input type="text" id="search_lname"></td>
            <td><input type="text" id="search_birthplace"></td>
            <td><input type="date" id="search_birthdate"></td>
            <td><button onclick="ara()">Ara</button></td>
            <td></td>
        </tr>
    </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr data-id="<?php echo $row['sid']; ?>">
                <td><?php echo $row['sid']; ?></td>
                <td><?php echo $row['fname']; ?></td>
                <td><?php echo $row['lname']; ?></td>
                <td><?php echo $row['birthplace']; ?></td>
                <td><?php echo $row['birthDate']; ?></td>
                <td><button onclick="sil(<?php echo $row['sid']; ?>)">Sil</button> </td>
                <td><button onclick="guncelle(<?php echo $row['sid']; ?>)">Güncelle</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>


</body>
</html>