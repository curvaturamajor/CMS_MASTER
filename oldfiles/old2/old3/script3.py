import re
import PyPDF2
import xml.etree.ElementTree as ET
def kelime_sayi_sonrasi_bulucu_pdf(dosya_ismi):
    bulunan_ifadeler = []

    # Satır başındaki sayı, sonrasındaki kelimeleri ve ':' ayrımcısını içeren düzenli ifade
    pattern = re.compile(r'^(\d+)\s+([^\n\d:]+):\s+([^\n\d]+)', re.MULTILINE)

    with open(dosya_ismi, 'rb') as file:
        reader = PyPDF2.PdfReader(file)

        for page_num in range(len(reader.pages)):
            page = reader.pages[page_num]
            text = page.extract_text()
            for match in pattern.findall(text):
                sayi, kelimeler_1, kelimeler_2 = match

                # Eğer sayının yanında kelimeler varsa ve bu kelimeler başka bir sayı değilse, bu satırı listeye ekleyelim.
                if kelimeler_1.strip() and not kelimeler_1.strip().isdigit() and kelimeler_2.strip() and not kelimeler_2.strip().isdigit():
                    bulunan_ifadeler.append(f"{sayi} {kelimeler_1.strip()}: {kelimeler_2.strip()}")

    return bulunan_ifadeler

dosya_ismi = "Akif_Bey.pdf"
bulunan_kelimeler = kelime_sayi_sonrasi_bulucu_pdf(dosya_ismi)

def kelime_sayi_bulucu_pdf(dosya_ismi):
    bulunan_kelimeler = []

    # 'kelime2', 'çemö23', 'Kâmil9453' gibi ifadeleri (Türkçe karakterlerle) bulmak için düzenli ifade
    pattern = re.compile(r'\b[a-zA-ZçöğüşıâîûÇÖĞÜŞİÂÎÛ]+?\d+\b')

    with open(dosya_ismi, 'rb') as file:
        reader = PyPDF2.PdfReader(file)

        for page_num in range(len(reader.pages)):
            page = reader.pages[page_num]
            text = page.extract_text()
            for match in pattern.findall(text):
                bulunan_kelimeler.append(match)

    return bulunan_kelimeler

dosya_ismi = "Akif_Bey.pdf"
bulunan_kelimeler_sayilar = kelime_sayi_bulucu_pdf(dosya_ismi)



def xml_duzenle(xml_dosya_ismi, pdf_dosya_ismi):
    # İlk scriptten gelen fonksiyonları kullanarak bilgileri alalım.
    bulunan_kelimeler = kelime_sayi_sonrasi_bulucu_pdf(pdf_dosya_ismi)
    bulunan_kelimeler_sayilar = kelime_sayi_bulucu_pdf(pdf_dosya_ismi)

    # Kelime ve sayıları eşleştiren bir sözlük oluşturalım.
    kelime_sozluk = {}
    for item in bulunan_kelimeler:
        sayi, kelimeler_1, kelimeler_2 = item.split(":")[0].split()
        kelime_sozluk[kelimeler_1.strip() + sayi] = kelimeler_2.split(",")[0].strip()

    # XML dosyasını okuyalım.
    agac = ET.parse(xml_dosya_ismi)
    kok = agac.getroot()

    # Her bir metni kontrol edip dönüştürelim.
    for elem in kok.iter():
        if elem.text:
            for kelime_sayi in bulunan_kelimeler_sayilar:
                if kelime_sayi in elem.text:
                    kelime = re.sub(r'\d+', '', kelime_sayi)
                    degisim = f"({kelime}) {kelime_sozluk[kelime_sayi]}"
                    elem.text = elem.text.replace(kelime_sayi, degisim)

    # Değişiklikleri kaydedelim.
    agac.write('guncellenmis_Akif_Bey.xml', encoding='utf-8', xml_declaration=True)

xml_duzenle("Akif_Bey.xml", "Akif_Bey.pdf")
