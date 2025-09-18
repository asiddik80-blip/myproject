
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

def extract_anchors(ocr_data):
    anchors = []
    for word in ocr_data.get("words", []):
        if re.match(r"^ITEM-\d{3}$", word.get("text", "")):
            bbox = word.get("bbox", {})
            anchors.append({
                "text": word["text"],
                "x": bbox.get("left", 0),
                "y": bbox.get("top", 0),
                "width": bbox.get("width", 0),
                "height": bbox.get("height", 0),
            })
    return anchors

def match_visual_boxes_to_anchors(anchors, visual_boxes):
    matched_zones = []

    for anchor in anchors:
        anchor_center_x = anchor["x"] + anchor["width"] // 2
        anchor_y = anchor["y"]

        candidates = []
        for vb in visual_boxes:
            vb_bottom_y = vb["y"] + vb["height"]
            vb_center_x = vb["x"] + vb["width"] // 2
            # Relaxed matching thresholds
            if vb_bottom_y < anchor_y + 100 and abs(vb_center_x - anchor_center_x) < 600:
                candidates.append(vb)

        if candidates:
            candidates.sort(key=lambda vb: abs((vb["y"] + vb["height"]) - anchor_y))
            best_match = candidates[0]
            matched_zones.append({
                "anchor": anchor["text"],
                "zone_box": best_match
            })
        else:
            print(f"⚠️ No visual box found for anchor {anchor['text']} at ({anchor['x']},{anchor['y']})")

    return matched_zones

def main(image_path, ocr_json_path, output_json_path):
    image = load_image(image_path)
    visual_boxes = detect_visual_boxes(image)

    with open(ocr_json_path, "r", encoding="utf-8") as f:
        ocr_data = json.load(f)

    anchors = extract_anchors(ocr_data)
    filtered_zones = match_visual_boxes_to_anchors(anchors, visual_boxes)

    with open(output_json_path, "w", encoding="utf-8") as out:
        json.dump(filtered_zones, out, indent=2, ensure_ascii=False)

    print(f"Filtered visual placard zones saved to {output_json_path}")

if __name__ == "__main__":
    if len(sys.argv) != 4:
        print("Usage: python detect_visual_placard_zones_filtered.py <image_path> <ocr_json_path> <output_json_path>")
        sys.exit(1)

    main(sys.argv[1], sys.argv[2], sys.argv[3])
