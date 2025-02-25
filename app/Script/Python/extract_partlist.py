import sys
import json
import pandas as pd
import camelot
import fitz  # PyMuPDF untuk ekstraksi teks

def extract_table(pdf_path):
    """
    Ekstrak tabel dari halaman pertama PDF menggunakan Camelot.
    """
    tables = camelot.read_pdf(pdf_path, pages='1')

    if tables.n == 0:
        return {"error": "No tables found"}

    # Ambil data tabel pertama
    df = tables[0].df  

    # Menggunakan baris pertama sebagai header
    df.columns = df.iloc[0]
    df = df[1:]

    # Bersihkan data (hapus spasi ekstra)
    df = df.applymap(lambda x: x.strip() if isinstance(x, str) else x)

    # Konversi ke list of dicts agar lebih rapi
    table_data = df.to_dict(orient="records")

    return table_data

def extract_metadata(pdf_path):
    """
    Ekstrak metadata dari teks di halaman pertama PDF.
    """
    doc = fitz.open(pdf_path)
    text = doc[0].get_text("text")  # Ambil teks dari halaman pertama

    metadata = {
        "placard_no": "N/A",
        "rev_ltr": "N/A",
        "sheet": "N/A"
    }

    # Pindai teks untuk menemukan metadata
    for line in text.split("\n"):
        if "PLACARD / MARKING DRAWING NO." in line:
            metadata["placard_no"] = line.split(":")[-1].strip()
        elif "REV. LTR" in line:
            metadata["rev_ltr"] = line.split(":")[-1].strip()
        elif "SHEET" in line:
            metadata["sheet"] = line.split(":")[-1].strip()

    return metadata

def main():
    """
    Jalankan ekstraksi tabel dan metadata dari PDF yang diberikan sebagai argumen.
    """
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No PDF file provided"}))
        return

    pdf_path = sys.argv[1]

    # Ekstrak data dari tabel
    table_data = extract_table(pdf_path)

    # Ekstrak metadata dari teks di luar tabel
    metadata = extract_metadata(pdf_path)

    # Gabungkan hasil ekstraksi
    result = {
        "file_name": pdf_path.split("/")[-1],
        "main_table": table_data,
        "placard_info": {
            "PLACARD / MARKING DRAWING NO.": metadata["placard_no"],
            "REV. LTR": metadata["rev_ltr"],
            "SHEET": metadata["sheet"]
        }
    }

    # Cetak hasil dalam format JSON agar bisa dibaca oleh Laravel
    print(json.dumps(result, ensure_ascii=False))

if __name__ == "__main__":
    main()
