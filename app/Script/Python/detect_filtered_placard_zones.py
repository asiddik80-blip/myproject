import cv2
import json
import sys
import re

def load_image(image_path):
    image = cv2.imread(image_path)
    if image is None:
        raise ValueError(f"Image not found: {image_path}")
    return image

def detect_visual_boxes(image):
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    _, thresh = cv2.threshold(gray, 150, 255, cv2.THRESH_BINARY_INV)
    kernel = cv2.getStructuringElement(cv2.MORPH_RECT, (5, 5))
    dilated = cv2.dilate(thresh, kernel, iterations=1)
    contours, _ = cv2.findContours(dilated, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

    boxes = []
    for cnt in contours:
        x, y, w, h = cv2.boundingRect(cnt)
        if w >= 30 and h >= 30:
            boxes.append({"x": x, "y": y, "width": w, "height": h})
    return boxes

def extract_words_in_box(box, words):
    x0, y0 = box["x"], box["y"]
    x1, y1 = x0 + box["width"], y0 + box["height"]
    words_in_zone = []
    for word in words:
        bbox = word.get("bbox", {})
        wx = bbox.get("left")
        wy = bbox.get("top")
        ww = bbox.get("width")
        wh = bbox.get("height")
        if wx is None or wy is None or ww is None or wh is None:
            continue
        if wx >= x0 and wy >= y0 and (wx + ww) <= x1 and (wy + wh) <= y1:
            words_in_zone.append(word)
    return words_in_zone

def group_words_into_lines(words):
    lines = []
    if not words:
        return lines

    # Sort by Y (top), then X (left)
    sorted_words = sorted(words, key=lambda w: (w["bbox"]["top"], w["bbox"]["left"]))
    line = []
    current_y = sorted_words[0]["bbox"]["top"]

    for word in sorted_words:
        y = word["bbox"]["top"]
        if abs(y - current_y) > 15 and line:
            lines.append(" ".join([w["text"] for w in line]))
            line = []
        line.append(word)
        current_y = y
    if line:
        lines.append(" ".join([w["text"] for w in line]))

    return lines

def is_anchor(word):
    return bool(re.match(r'^ITEM-\\d{3}$', word.get("text", "")))

def extract_filtered_placard_zones(image_path, ocr_json_path, output_json_path):
    image = load_image(image_path)
    visual_boxes = detect_visual_boxes(image)

    with open(ocr_json_path, 'r', encoding='utf-8') as f:
        ocr_data = json.load(f)

    words = ocr_data.get("words", [])

    # Temukan semua anchor
    anchors = []
    for word in words:
        if is_anchor(word):
            bbox = word.get("bbox", {})
            anchors.append({
                "text": word["text"],
                "x": bbox.get("left", 0),
                "y": bbox.get("top", 0),
                "width": bbox.get("width", 0),
                "height": bbox.get("height", 0)
            })

    placard_zones = []

    for anchor in anchors:
        anchor_center_x = anchor["x"] + anchor["width"] // 2
        anchor_y = anchor["y"]

        # Cari kotak visual yang berada di atas anchor (dan dekat horizontal)
        candidates = [
            vb for vb in visual_boxes
            if (vb["y"] + vb["height"] < anchor_y - 10) and
               (abs((vb["x"] + vb["width"] // 2) - anchor_center_x) < max(200, anchor["width"] * 1.5))
        ]

        if candidates:
            candidates.sort(key=lambda box: anchor_y - (box["y"] + box["height"]))
            best_box = candidates[0]

            # Ambil kata-kata dalam kotak dan kelompokkan jadi baris
            zone_words = extract_words_in_box(best_box, words)
            body_lines = group_words_into_lines(zone_words)

            placard_zones.append({
                "anchor": anchor["text"],
                "zone_box": best_box,
                "body_lines": body_lines
            })

    with open(output_json_path, 'w', encoding='utf-8') as out:
        json.dump(placard_zones, out, indent=2, ensure_ascii=False)

    print(f"Filtered Placard Zones with body_lines saved to {output_json_path}")

if __name__ == "__main__":
    if len(sys.argv) != 4:
        print("Usage: python detect_filtered_placard_zones.py <image_path> <ocr_json_path> <output_json_path>")
        sys.exit(1)

    extract_filtered_placard_zones(sys.argv[1], sys.argv[2], sys.argv[3])
