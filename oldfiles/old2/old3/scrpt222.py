import re
import PyPDF2

def kelime_sayi_bulucu_pdf(dosya_ismi):
    bulunan_kelimeler = []

    # 'Çürüksu’nun45', 'Hicab486' gibi ifadeleri (Türkçe karakterlerle) bulmak için düzenli ifade
    pattern = re.compile(
        r'(?<!\w)[a-zA-ZçöğüşıâîûÇÖĞÜŞİÂÎÛğĞüÜöÖçÇşŞıİ’’]+?\d+[a-zA-ZçöğüşıâîûÇÖĞÜŞİÂÎÛğĞüÜöÖçÇşŞıİ’’]*[\.,\n]*(?!\w)')

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
