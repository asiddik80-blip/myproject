# =============================================
# detectBoxesTwoSteps.py
# ---------------------------------------------
# Alur utama:
# 1. Baca gambar input (JPEG)
# 2. Lakukan preprocessing visual:
#    - Grayscale
#    - CLAHE (peningkatan kontras)
#    - Adaptive Threshold (binary inverse)
#    - Morphological Closing
# 3. Simpan hasil preprocessing ke folder debug
# 4. Deteksi kontur dari hasil threshold
# 5. Konversi kontur ke bounding boxes
# 6. Filter box kecil yang tidak relevan
# 7. Output array box dalam format JSON
# =============================================

import cv2
import sys
import json
import os

# Ambil argumen
image_path = sys.argv[1]  # Path ke file JPEG
# sys.argv[2] = zone_path (tidak dipakai di sini)

# Baca gambar
img = cv2.imread(image_path)
if img is None:
    print("[]")
    sys.exit(0)

# Step 1: Grayscale
gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

# Step 2: CLAHE (peningkatan kontras lokal)
clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8,8))
enhanced = clahe.apply(gray)

# Step 3: Adaptive Threshold (binary inverse)
thresh = cv2.adaptiveThreshold(
    enhanced, 255,
    cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
    cv2.THRESH_BINARY_INV,
    11, 2
)

# Step 4: Morphological Closing (untuk menutup lubang kecil)
kernel = cv2.getStructuringElement(cv2.MORPH_RECT, (3, 3))
closed = cv2.morphologyEx(thresh, cv2.MORPH_CLOSE, kernel)

# Step 5: Simpan hasil preprocessing ke folder debug
script_dir = os.path.dirname(os.path.abspath(__file__))
debug_dir = os.path.abspath(os.path.join(script_dir, '..', 'preprocess_debug'))

# Ambil nama file tanpa ekstensi untuk digunakan sebagai prefix
basename = os.path.splitext(os.path.basename(image_path))[0]
debug_folder = os.path.join(os.path.dirname(__file__), "..", "preprocess_debug")
debug_folder = os.path.abspath(debug_folder)

# Pastikan folder debug ada
os.makedirs(debug_folder, exist_ok=True)

# Simpan gambar hasil preprocessing
cv2.imwrite(os.path.join(debug_folder, f"{basename}-1-gray.jpg"), gray)
cv2.imwrite(os.path.join(debug_folder, f"{basename}-2-enhanced.jpg"), enhanced)
cv2.imwrite(os.path.join(debug_folder, f"{basename}-3-thresh.jpg"), thresh)
cv2.imwrite(os.path.join(debug_folder, f"{basename}-4-closed.jpg"), closed)


# Step 6: Deteksi kontur
contours, _ = cv2.findContours(closed, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

# Step 7: Konversi ke bounding boxes
boxes = []
for cnt in contours:
    x, y, w, h = cv2.boundingRect(cnt)
    if w >= 30 and h >= 20:  # filter noise kotak kecil
        boxes.append({
            "x": int(x),
            "y": int(y),
            "width": int(w),
            "height": int(h)
        })

# Step 8: Output hasil dalam JSON
print(json.dumps(boxes))
