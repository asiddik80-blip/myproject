# ============================================
# detect_red_mask.py
# --------------------------------------------
# Deteksi area merah + teks di dalamnya
# ============================================

import cv2
import sys
import json
import os
import numpy as np
import pytesseract
from collections import defaultdict

# 🔹 Validasi argumen
if len(sys.argv) < 2:
    print("[]")
    sys.exit(0)

image_path = sys.argv[1]
img = cv2.imread(image_path)

if img is None:
    print("[]")
    sys.exit(0)

# 🔹 Step 1: Konversi ke HSV
hsv = cv2.cvtColor(img, cv2.COLOR_BGR2HSV)

# 🔹 Step 2: Masking merah (HSV)
lower_red1 = np.array([0, 100, 100])
upper_red1 = np.array([10, 255, 255])
lower_red2 = np.array([160, 100, 100])
upper_red2 = np.array([179, 255, 255])

mask1 = cv2.inRange(hsv, lower_red1, upper_red1)
mask2 = cv2.inRange(hsv, lower_red2, upper_red2)
red_mask = cv2.bitwise_or(mask1, mask2)

# 🔹 Step 3: Morphological closing
kernel = cv2.getStructuringElement(cv2.MORPH_RECT, (3, 3))
closed = cv2.morphologyEx(red_mask, cv2.MORPH_CLOSE, kernel)

# 🔹 Step 4: Deteksi kontur merah
contours, _ = cv2.findContours(closed, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

# 🔹 Step 5: Proses setiap kontur
boxes = []
for cnt in contours:
    x, y, w, h = cv2.boundingRect(cnt)
    if w < 10 or h < 10:
        continue

    cropped = img[y:y + h, x:x + w]

    try:
        # Gunakan Tesseract untuk deteksi teks per baris
        tsv = pytesseract.image_to_data(cropped, config='--psm 6', output_type=pytesseract.Output.DICT)
        lines_dict = defaultdict(list)

        n = len(tsv['level'])
        for i in range(n):
            text = tsv['text'][i].strip()
            conf = int(tsv['conf'][i])
            line_num = tsv['line_num'][i]
            if text != '' and conf > 30:
                lines_dict[line_num].append(text)

        lines_text = [' '.join(words) for words in lines_dict.values() if words]

    except Exception as e:
        lines_text = []

    boxes.append({
        "x": int(x),
        "y": int(y),
        "width": int(w),
        "height": int(h),
        "lines": lines_text
    })

# 🔹 Step 6: Simpan debug images
script_dir = os.path.dirname(os.path.abspath(__file__))
debug_dir = os.path.abspath(os.path.join(script_dir, '..', 'preprocess_debug'))
os.makedirs(debug_dir, exist_ok=True)
basename = os.path.splitext(os.path.basename(image_path))[0]

cv2.imwrite(os.path.join(debug_dir, f"{basename}-red_mask.jpg"), red_mask)
cv2.imwrite(os.path.join(debug_dir, f"{basename}-red_closed.jpg"), closed)

img_debug = img.copy()
for box in boxes:
    x, y, w, h = box['x'], box['y'], box['width'], box['height']
    cv2.rectangle(img_debug, (x, y), (x + w, y + h), (0, 0, 255), 2)

cv2.imwrite(os.path.join(debug_dir, f"{basename}-red_boxes.jpg"), img_debug)

# 🔹 Step 7: Output JSON
print(json.dumps(boxes))
