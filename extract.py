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

# Matikan log INFO, biar Laravel cuma dapat JSON
logging.getLogger().setLevel(logging.ERROR)

# Pastikan skrip menerima dua argumen
if len(sys.argv) < 3:
    print(json.dumps({"error": "Usage: python extract.py <pdf_path> <credentials_path>"}))
    sys.stdout.flush()
    sys.exit(1)

pdf_path = sys.argv[1]
credentials_path = sys.argv[2]

if not os.path.exists(pdf_path):
    print(json.dumps({"error": "File PDF tidak ditemukan"}))
    sys.stdout.flush()
    sys.exit(1)

if not os.path.exists(credentials_path):
    print(json.dumps({"error": "File credentials tidak ditemukan"}))
    sys.stdout.flush()
    sys.exit(1)

try:
    with open(credentials_path, 'r') as cred_file:
        credentials_data = json.load(cred_file)
except Exception as e:
    print(json.dumps({"error": f"Gagal membaca credentials: {str(e)}"}))
    sys.stdout.flush()
    sys.exit(1)

client_id = credentials_data.get("client_credentials", {}).get("client_id")
client_secret = credentials_data.get("client_credentials", {}).get("client_secret")

if not client_id or not client_secret:
    print(json.dumps({"error": "Client ID atau Client Secret tidak valid"}))
    sys.stdout.flush()
    sys.exit(1)

try:
    with open(pdf_path, 'rb') as file:
        input_stream = file.read()

    credentials = ServicePrincipalCredentials(client_id=client_id, client_secret=client_secret)
    pdf_services = PDFServices(credentials=credentials)

    input_asset = pdf_services.upload(input_stream=input_stream, mime_type=PDFServicesMediaType.PDF)

    extract_pdf_params = ExtractPDFParams(elements_to_extract=[ExtractElementType.TEXT])
    extract_pdf_job = ExtractPDFJob(input_asset=input_asset, extract_pdf_params=extract_pdf_params)

    location = pdf_services.submit(extract_pdf_job)
    pdf_services_response = pdf_services.get_job_result(location, ExtractPDFResult)

    result_asset: CloudAsset = pdf_services_response.get_result().get_resource()
    stream_asset: StreamAsset = pdf_services.get_content(result_asset)

    output_dir = os.path.join(os.getcwd(), "storage", "app", "pdfs")
    output_file_path = os.path.join(output_dir, f"extract_{datetime.now().strftime('%Y%m%d_%H%M%S')}.zip")

    os.makedirs(output_dir, exist_ok=True)

    with open(output_file_path, "wb") as file:
        file.write(stream_asset.get_input_stream())

    with zipfile.ZipFile(output_file_path, 'r') as archive:
        json_entry = archive.open('structuredData.json')
        json_data = json_entry.read()
        data = json.loads(json_data)

    extracted_texts = [element["Text"] for element in data.get("elements", []) if "Text" in element]

    print(json.dumps({"texts": extracted_texts}))
    sys.stdout.flush()

except (ServiceApiException, ServiceUsageException, SdkException) as e:
    print(json.dumps({"error": f"Adobe API Error: {str(e)}"}))
    sys.stdout.flush()
    sys.exit(1)
