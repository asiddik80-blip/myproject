import sys
import logging
import os
import zipfile
import json
from datetime import datetime

from adobe.pdfservices.operation.auth.service_principal_credentials import ServicePrincipalCredentials
from adobe.pdfservices.operation.exception.exceptions import ServiceApiException, ServiceUsageException, SdkException
from adobe.pdfservices.operation.pdf_services_media_type import PDFServicesMediaType
from adobe.pdfservices.operation.io.cloud_asset import CloudAsset
from adobe.pdfservices.operation.io.stream_asset import StreamAsset
from adobe.pdfservices.operation.pdf_services import PDFServices
from adobe.pdfservices.operation.pdfjobs.jobs.extract_pdf_job import ExtractPDFJob
from adobe.pdfservices.operation.pdfjobs.params.extract_pdf.extract_element_type import ExtractElementType
from adobe.pdfservices.operation.pdfjobs.params.extract_pdf.extract_pdf_params import ExtractPDFParams
from adobe.pdfservices.operation.pdfjobs.result.extract_pdf_result import ExtractPDFResult

# Logging
logging.basicConfig(level=logging.INFO)

# Pastikan skrip menerima dua argumen: path file PDF dan credentials
if len(sys.argv) < 3:
    print("Usage: python extract.py <pdf_path> <credentials_path>")
    sys.exit(1)

pdf_path = sys.argv[1]  # Path PDF dari Laravel
credentials_path = sys.argv[2]  # Path credentials dari Laravel

# Pastikan file PDF tersedia
if not os.path.exists(pdf_path):
    print(json.dumps({"error": "File PDF tidak ditemukan"}))
    sys.exit(1)

# Pastikan file credentials tersedia
if not os.path.exists(credentials_path):
    print(json.dumps({"error": "File credentials tidak ditemukan"}))
    sys.exit(1)

# Baca kredensial Adobe dari file JSON
try:
    with open(credentials_path, 'r') as cred_file:
        credentials_data = json.load(cred_file)
except Exception as e:
    print(json.dumps({"error": f"Gagal membaca credentials: {str(e)}"}))
    sys.exit(1)

# Ambil client_id dan client_secret dari credentials
client_id = credentials_data.get("client_credentials", {}).get("client_id")
client_secret = credentials_data.get("client_credentials", {}).get("client_secret")

if not client_id or not client_secret:
    print(json.dumps({"error": "Client ID atau Client Secret tidak valid dalam credentials"}))
    sys.exit(1)

try:
    # Buka file PDF dari Laravel
    with open(pdf_path, 'rb') as file:
        input_stream = file.read()

    # Buat kredensial Adobe API
    credentials = ServicePrincipalCredentials(client_id=client_id, client_secret=client_secret)

    # Buat instance PDF Services
    pdf_services = PDFServices(credentials=credentials)

    # Upload file PDF ke Adobe API
    input_asset = pdf_services.upload(input_stream=input_stream, mime_type=PDFServicesMediaType.PDF)

    # Buat parameter ekstraksi teks
    extract_pdf_params = ExtractPDFParams(elements_to_extract=[ExtractElementType.TEXT])

    # Buat job ekstraksi
    extract_pdf_job = ExtractPDFJob(input_asset=input_asset, extract_pdf_params=extract_pdf_params)

    # Submit job ke Adobe API dan dapatkan hasilnya
    location = pdf_services.submit(extract_pdf_job)
    pdf_services_response = pdf_services.get_job_result(location, ExtractPDFResult)

    # Ambil hasil ekstraksi sebagai asset
    result_asset: CloudAsset = pdf_services_response.get_result().get_resource()
    stream_asset: StreamAsset = pdf_services.get_content(result_asset)

    # Simpan hasil dalam ZIP
    output_dir = "C:/xampp/htdocs/coba/storage/app/pdfs"
    output_file_path = f"{output_dir}/extract_{datetime.now().strftime('%Y%m%d_%H%M%S')}.zip"

    # Pastikan folder output ada
    if not os.path.exists(output_dir):
        os.makedirs(output_dir)

    # Simpan file hasil ekstraksi
    with open(output_file_path, "wb") as file:
        file.write(stream_asset.get_input_stream())


    # Buka ZIP dan baca JSON hasil ekstraksi
    with zipfile.ZipFile(output_file_path, 'r') as archive:
        json_entry = archive.open('structuredData.json')
        json_data = json_entry.read()
        data = json.loads(json_data)

    # Ekstrak teks dari elemen
    extracted_texts = []
    for element in data.get("elements", []):
        if "Text" in element:
            extracted_texts.append(element["Text"])

    # Cetak hasil dalam format JSON untuk dikembalikan ke Laravel
    print(json.dumps({"texts": extracted_texts}, indent=4))

except (ServiceApiException, ServiceUsageException, SdkException) as e:
    logging.exception(f'Exception encountered while executing operation: {e}')
    print(json.dumps({"error": f"Adobe API Error: {str(e)}"}))
    sys.exit(1)
