import re
import PyPDF2

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
for item in bulunan_kelimeler_sayilar:
    print(item)
