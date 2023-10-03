import re
from docx import Document

def kelime_bul(docx_dosyasi):
    doc = Document(docx_dosyasi)
    sonuc = []

    # Tüm paragrafları dolaş
    for para in doc.paragraphs:
        # Her kelime için kontrol et
        for kelime in para.text.split():
            # Eğer kelime belirtilen özellikleri taşıyorsa sonuc listesine ekle
            if re.search(r'\w+\d$', kelime):
                sonuc.append(kelime)

    return sonuc

docx_dosyasi = "Akif_Bey.docx"
bulunan_kelimeler = kelime_bul(docx_dosyasi)
print(bulunan_kelimeler)
