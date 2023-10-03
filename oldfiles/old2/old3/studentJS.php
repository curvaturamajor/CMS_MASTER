<!DOCTYPE html>
<html lang="en">
<head>
<style>
    #pagination a {
        margin-right: 5px; 
    }
</style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

<script>
let currentPage = 1;
const perPage = 5;
document.addEventListener('DOMContentLoaded', function () {
    // Sayfa yüklendiğinde öğrencileri çekmek için.
    fetchStudentsWithPagination(1);

    // Sıralama özelliği için tıklama dinleyicisi.
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

   // Genel bir XHR işleyici fonksiyonu
   function makeRequest(method, url, callback) {
        let xhr = new XMLHttpRequest();
        xhr.open(method, url, true);
        xhr.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                let response = JSON.parse(this.responseText);
                callback(response);
            }
        };
        xhr.send();
    }
    // Tablodaki Sil ve Güncelle butonları için tıklama dinleyicisi.
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
   // Tabloyu veriyle güncelleme fonksiyonu
    function updateTableWithData(data) {
        let tableBody = document.querySelector(".table tbody");
        tableBody.innerHTML = "";
        data.students.forEach(student => {
            let newRow = `<tr data-id="${student.sid}">
                            <td>${student.sid}</td>
                            <td>${student.fname}</td>
                            <td>${student.lname}</td>
                            <td>${student.birthplace}</td>
                            <td>${student.birthDate}</td>
                            <td><button onclick="sil(${student.sid})">Sil</button></td>
                            <td><button onclick="guncelle(${student.sid})">Güncelle</button></td>
                          </tr>`;
            tableBody.innerHTML += newRow;
        });
    }
    // Sakla butonuna basılmadıysa ve diğer butonlara basıldığında veya sıralama değiştirildiğinde Sakla'nın tekrar Güncelle'ye dönüşmesi için.
    document.addEventListener('click', function(e) {
        if (e.target && (e.target.innerText === "Sil" || e.target.innerText === "Oluştur")) {
            resetUpdateButtons();
        }
    });

    // Güncelleme butonlarını sıfırlamak için.
    document.querySelector('.table thead').addEventListener('click', function() {
        if(document.querySelector('.table thead')) {
            resetUpdateButtons();
        }
    });
});

</script>

<script>
function getStudentData() {
    return {
        fname: document.getElementById("new_fname").value,
        lname: document.getElementById("new_lname").value,
        birthplace: document.getElementById("new_birthplace").value,
        birthdate: document.getElementById("new_birthdate").value
    };
}

function addStudentToTable(student) {
    let table = document.querySelector(".table tbody");
    let newRow = table.insertRow();
    newRow.insertCell(0).innerText = student.sid;
    newRow.insertCell(1).innerText = student.fname;
    newRow.insertCell(2).innerText = student.lname;
    newRow.insertCell(3).innerText = student.birthplace;
    newRow.insertCell(4).innerText = student.birthdate;
    newRow.insertCell(5).innerHTML = `<button onclick="sil(${student.sid})">Sil</button>`;
    newRow.insertCell(6).innerHTML = `<button onclick="guncelle(${student.sid})">Güncelle</button>`;
}

function yeni() {
    let student = getStudentData();
    let url = `yeni.php?ad=${student.fname}&soyad=${student.lname}&dyer=${student.birthplace}&dtarih=${student.birthdate}`;

    makeRequest("GET", url, function(response) {
        if (response.success) {
            addStudentToTable({
                sid: response.sid,
                ...student
            });
        } else {
            alert("Error: " + response.error);
        }
    });
}

// Önceden belirttiğimiz `makeRequest` fonksiyonu:
function makeRequest(method, url, callback) {
    let xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            let response = JSON.parse(this.responseText);
            callback(response);
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
                // Öğrenciyi başarılı bir şekilde sildikten sonra listenin yeniden yüklenmesi
                fetchStudentsWithPagination(currentPage);
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
    if (e.target && (e.target.innerText === "Sil" || e.target.innerText === "Oluştur")) {
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


function getSortedData(sortType, sortOrder) {
    let currentSort = document.querySelector('.sortable[data-order]');
    currentSort = currentSort ? currentSort.getAttribute('data-sort') : 'sid';

    let order = document.querySelector('.sortable[data-order]');
    order = order ? order.getAttribute('data-order') : 'ASC';

    let headers = document.querySelectorAll('.sortable');
    headers.forEach(header => {
        header.removeAttribute('data-order'); // Önceki sıralama bilgisini temizle
    });

    let currentHeader = document.querySelector(`.sortable[data-sort="${sortType}"]`);
    currentHeader.setAttribute('data-order', sortOrder); // Yeni sıralama bilgisini belirt

    let xhr = new XMLHttpRequest();
    xhr.open('GET', `getSortedData.php?sortType=${currentSort}&sortOrder=${order}&page=${currentPage}&limit=${perPage}`, true);
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 400) {
            let data = JSON.parse(xhr.responseText);
            let tableBody = document.querySelector(".table tbody");
            tableBody.innerHTML = "";
            data.students.forEach(student => {
                let newRow = `<tr data-id="${student.sid}">
                                <td>${student.sid}</td>
                                <td>${student.fname}</td>
                                <td>${student.lname}</td>
                                <td>${student.birthplace}</td>
                                <td>${student.birthDate}</td>
                                <td><button onclick="sil(${student.sid})">Sil</button></td>
                                <td><button onclick="guncelle(${student.sid})">Güncelle</button></td>
                              </tr>`;
                tableBody.innerHTML += newRow;
            });

            updatePaginationLinks(data.totalPages, 1);
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

<script>
function fetchStudentsWithPagination(page) {
    currentPage = page; // Sayfa numarasını güncelle

    let currentSortElement = document.querySelector('.sortable[data-order]');
    let currentSort = currentSortElement ? currentSortElement.getAttribute('data-sort') : 'sid';
    let sortOrder = currentSortElement ? currentSortElement.getAttribute('data-order') : 'ASC';

    let xhr = new XMLHttpRequest();
    xhr.open('GET', `getSortedData.php?page=${page}&limit=${perPage}&sortType=${currentSort}&sortOrder=${sortOrder}`, true);
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 400) {
            let data = JSON.parse(xhr.responseText);
            let tableBody = document.querySelector(".table tbody");
            tableBody.innerHTML = "";
            data.students.forEach(student => {
                let newRow = `<tr data-id="${student.sid}">
                                <td>${student.sid}</td>
                                <td>${student.fname}</td>
                                <td>${student.lname}</td>
                                <td>${student.birthplace}</td>
                                <td>${student.birthDate}</td>
                                <td><button onclick="sil(${student.sid})">Sil</button></td>
                                <td><button onclick="guncelle(${student.sid})">Güncelle</button></td>
                              </tr>`;
                tableBody.innerHTML += newRow;
            });

            updatePaginationLinks(data.totalPages, page);
        }
    };
    xhr.send();
}

function updatePaginationLinks(totalPages, activePage) {
    // Önceki sayfa numaralarını temizleyelim
    let oldPaginationDiv = document.getElementById('pagination');
    if (oldPaginationDiv) {
        oldPaginationDiv.remove();
    }

    let paginationDiv = document.createElement('div');
    paginationDiv.id = 'pagination';
    
    let startPage = Math.max(1, activePage - 3);
    let endPage = Math.min(totalPages, activePage + 3);

    function createPageLink(text, page) {
        let a = document.createElement('a');
        a.href = "#";
        a.innerText = text;

        a.addEventListener('click', function(e) {
            e.preventDefault();
            fetchStudentsWithPagination(page);
        });

        paginationDiv.appendChild(a);
    }

    if (activePage !== 1) {
        createPageLink('<<', 1);
        createPageLink('<', activePage - 1);
    }

    for (let i = startPage; i <= endPage; i++) {
        if (i === activePage) {
            let span = document.createElement('span');
            span.className = 'active';
            span.innerText = i;
            paginationDiv.appendChild(span);
        } else {
            createPageLink(i.toString(), i);
        }
    }

    if (activePage !== totalPages) {
        createPageLink('>', activePage + 1);
        createPageLink('>>', totalPages);
    }

    document.body.appendChild(paginationDiv);
}

</script>
</html>