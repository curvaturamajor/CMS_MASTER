import re

with open("Akif_Bey.txt", "r", encoding="utf-8") as file:
    lines = file.readlines()

# Kelimenin başında veya sonunda sayı olabilecek, sonrasında virgül veya nokta bulunabilecek ve Türkçe karakterleri içeren regex pattern
pattern = r'(?:(?<=\s)|^)[0-9]*[ğüşıöçĞÜŞİÖÇa-zA-ZâîûîÂÎÛÎ]+[0-9]+[,.]?(?=\s|$)'

for line in lines:
    matches = re.findall(pattern, line)
    for match in matches:
        print(match)
