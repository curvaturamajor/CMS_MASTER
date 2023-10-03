import re
import PyPDF2
from PyPDF2.pdf import PageObject

def kelime_sayi_sonrasi_bulucu_pdf(dosya_ismi):
    # [ilk betiğin kodları]
    # ...

def kelime_sayi_bulucu_pdf(dosya_ismi):
    # [ikinci betiğin kodları]
    # ...

def pdf_guncelle(dosya_ismi, degisiklikler):
    with open(dosya_ismi, 'rb') as file:
        reader = PyPDF2.PdfReader(file)
        writer = PyPDF2.PdfWriter()

        for page_num in range(len(reader.pages)):
            page = reader.pages[page_num]
            text = page.extract_text()

            for eski, yeni in degisiklikler.items():
                text = text.replace(eski, yeni)

            # Sayfanın içeriğini güncelle
            packet = io.BytesIO()
            packet.write(text.encode('utf-8'))
            packet.seek(0)
            new_pdf = PyPDF2.PdfReader(packet)

            page.merge_page(new_pdf.pages[0])
            writer.add_page(page)

        with open("Guncellenmis_Akif_Bey.pdf", "wb") as output_file_handle:
            writer.write(output_file_handle)

dosya_ismi = "Akif_Bey.pdf"

# İlk ve ikinci betikten çıktıları al
ilk_cikti = kelime_sayi_sonrasi_bulucu_pdf(dosya_ismi)
ikinci_cikti = kelime_sayi_bulucu_pdf(dosya_ismi)

# Değişiklikler için bir sözlük oluştur
degisiklikler = {}
for item in ilk_cikti:
    sayi, kelimeler = item.split(" ", 1)
    for ikinci_item in ikinci_cikti:
        if sayi in ikinci_item:
            degisiklikler[ikinci_item] = kelimeler

pdf_guncelle(dosya_ismi, degisiklikler)
