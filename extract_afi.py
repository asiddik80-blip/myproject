import os
import zipfile
import json
from adobe.pdfservices.operation.auth.service_principal_credentials import ServicePrincipalCredentials
from adobe.pdfservices.operation.exception.exceptions import ServiceApiException, ServiceUsageException, SdkException
from adobe.pdfservices.operation.io.stream_asset import StreamAsset
from adobe.pdfservices.operation.io.cloud_asset import CloudAsset
from adobe.pdfservices.operation.pdf_services import PDFServices
from adobe.pdfservices.operation.pdf_services_media_type import PDFServicesMediaType
from adobe.pdfservices.operation.pdfjobs.jobs.extract_pdf_job import ExtractPDFJob
from adobe.pdfservices.operation.pdfjobs.params.extract_pdf.extract_element_type import ExtractElementType
from adobe.pdfservices.operation.pdfjobs.params.extract_pdf.extract_pdf_params import ExtractPDFParams
from adobe.pdfservices.operation.pdfjobs.result.extract_pdf_result import ExtractPDFResult

# Tentukan path kredensial secara langsung
pdf_credentials_path = "C:/xampp/htdocs/coba/storage/adobe/pdfservices-api-credentials.json"

# Cek apakah file kredensial ada
if not os.path.isfile(pdf_credentials_path):
    print(f"File kredensial tidak ditemukan di: {pdf_credentials_path}")
    exit(1)

# Membaca file kredensial
with open(pdf_credentials_path, 'r') as f:
    credentials_data = json.load(f)

# Inisialisasi kredensial dari data JSON
credentials = ServicePrincipalCredentials(
    client_id=credentials_data['client_credentials']['client_id'],
    client_secret=credentials_data['client_credentials']['client_secret']
    
)


# Inisialisasi PDFServices
pdf_services = PDFServices(credentials=credentials)

# Nama file input dan output
input_pdf_path = "C:/xampp/htdocs/coba/storage/app/public/1740237413-PAGE ONE REV L.pdf"
output_zip_path = "./ExtractTextInfoFromPDF.zip"

# Pastikan file output ZIP lama dihapus jika sudah ada
if os.path.isfile(output_zip_path):
    os.remove(output_zip_path)

# Membuka file PDF sebagai input
with open(input_pdf_path, "rb") as pdf_file:
    input_stream = StreamAsset(pdf_file, mime_type="application/pdf")

# Upload file PDF ke Adobe PDF Services
input_asset = pdf_services.upload(input_stream=input_stream, mime_type=PDFServicesMediaType.PDF)

# Menentukan parameter untuk ekstraksi teks
extract_pdf_params = ExtractPDFParams(
    elements_to_extract=[ExtractElementType.TEXT],  # Hanya mengekstrak teks
)

# Membuat job untuk ekstraksi PDF
extract_pdf_job = ExtractPDFJob(input_asset=input_asset, extract_pdf_params=extract_pdf_params)

try:
    # Submit job dan mendapatkan hasilnya
    location = pdf_services.submit(extract_pdf_job)
    pdf_services_response = pdf_services.get_job_result(location, ExtractPDFResult)

    # Mendapatkan hasil ekstraksi
    result_asset: CloudAsset = pdf_services_response.get_result().get_resource()
    stream_asset: StreamAsset = pdf_services.get_content(result_asset)

    # Menyimpan hasil ekstraksi sebagai ZIP
    with open(output_zip_path, "wb") as file:
        file.write(stream_asset.get_input_stream())

    # Membuka ZIP dan membaca file structuredData.json
    with zipfile.ZipFile(output_zip_path, 'r') as archive:
        with archive.open('structuredData.json') as jsonentry:
            jsondata = jsonentry.read()
            data = json.loads(jsondata)

    # Mencetak teks dari elemen yang merupakan H1
    for element in data["elements"]:
        if element["Path"].endswith("/H1"):
            print(element["Text"])

except (ServiceApiException, ServiceUsageException, SdkException) as e:
    print(f"Terjadi kesalahan saat mengekstrak PDF: {e}")
