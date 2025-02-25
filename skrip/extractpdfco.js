const fs = require("fs");
const request = require("request");

const apiKey = "alesuperjuve@gmail.com_h5ePpR7WYTikVhCCzRVxXA3ROdyCsbCZbCvZ0rgtSOFy3250v4RkUS65biX0H2G0"; // Ganti dengan API key PDF.co

const path = require("path"); // Tambahkan ini di atas
const inputPdfPath = path.resolve(process.argv[2]); // Ubah ini


if (!inputPdfPath) {
    console.error("❌ Harap masukkan nama file PDF sebagai argumen!");
    process.exit(1);
}

// Upload file ke PDF.co
const uploadUrl = "https://api.pdf.co/v1/file/upload";
console.log("📂 File yang dikirim:", inputPdfPath);
console.log("📢 Apakah file ada?", fs.existsSync(inputPdfPath));
console.log("📢 Ukuran file:", fs.statSync(inputPdfPath).size, "bytes");

const uploadRequest = request.post({
    url: uploadUrl,
    headers: { "x-api-key": apiKey },
    formData: { 
        file: { 
            value: fs.createReadStream(inputPdfPath), 
            options: { filename: "document.pdf", contentType: "application/pdf" } 
        } 
    }
}, function (error, response, body) {
    if (error) {
        console.error("❌ Gagal mengunggah file:", error);
        return;
    }

    try {
        const uploadResult = JSON.parse(body);
        console.log("📢 Debugging Response:", uploadResult); // Debugging response

        if (!uploadResult.success) {
            console.error("❌ Gagal mengunggah file:", uploadResult.message);
            return;
        }

        console.log("✅ Berhasil mengunggah file!");
        console.log("📂 File URL:", uploadResult.url);

        // Ekstraksi tabel dari PDF
        extractTable(uploadResult.url);
    } catch (parseError) {
        console.error("❌ Kesalahan parsing respons:", parseError, body);
    }
});




// Fungsi untuk mengekstrak tabel dari file PDF yang sudah diunggah
function extractTable(uploadedFileUrl) {
    const extractUrl = "https://api.pdf.co/v1/pdf/convert/to/json";

    const extractRequest = request.post({
        url: extractUrl,
        headers: { "x-api-key": apiKey },
        json: {
            url: uploadedFileUrl,
            pages: "1-", // Semua halaman
            outputFormat: "json"
        }
    }, function (error, response, body) {
        if (error) {
            console.error("❌ Gagal mengekstrak PDF:", error);
            return;
        }

        if (!body.success) {
            console.error("❌ Gagal mengekstrak PDF:", body.message);
            return;
        }

        console.log("✅ Berhasil mengekstrak PDF!");
        console.log("📜 Hasil ekstraksi:", JSON.stringify(body.body, null, 4));

        // Simpan hasil ke file JSON
        fs.writeFileSync("output.json", JSON.stringify(body.body, null, 4));
        console.log("💾 Hasil telah disimpan ke output.json");
    });
}
