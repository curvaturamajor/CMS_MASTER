import re
import PyPDF2

def kelime_sayi_sonrasi_bulucu_pdf(dosya_ismi):
    bulunan_ifadeler = []

    # Satır başındaki sayı ve sonrasındaki kelimeleri (noktalama işaretleri dahil) bulmak için düzenli ifade
    pattern = re.compile(r'^(\d+)\s+([^\n\d]+)', re.MULTILINE)

    with open(dosya_ismi, 'rb') as file:
        reader = PyPDF2.PdfReader(file)

        for page_num in range(len(reader.pages)):
            page = reader.pages[page_num]
            text = page.extract_text()
            for match in pattern.findall(text):
                sayi, kelimeler = match

                # Eğer sayının yanında kelimeler varsa ve bu kelimeler başka bir sayı değilse, bu satırı listeye ekleyelim.
                if kelimeler.strip() and not kelimeler.strip().isdigit():
                    bulunan_ifadeler.append(f"{sayi} {kelimeler.strip()}")  # strip() metoduyla baştaki ve sondaki fazla boşlukları kaldırıyoruz.

    return bulunan_ifadeler

dosya_ismi = "Akif_Bey.pdf"
bulunan_kelimeler = kelime_sayi_sonrasi_bulucu_pdf(dosya_ismi)
for item in bulunan_kelimeler:
    print(item)
