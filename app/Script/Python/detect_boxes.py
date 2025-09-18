#!/usr/bin/env python3

import sys
import json
import cv2

def is_overlapping(box, zone):
    return (
        box['x'] < zone['x'] + zone['width'] and
        box['x'] + box['width'] > zone['x'] and
        box['y'] < zone['y'] + zone['height'] and
        box['y'] + box['height'] > zone['y']
    )

def detect_visual_boxes(image_path, zone_json_path=None):
    image = cv2.imread(image_path)
    if image is None:
        return []

    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    _, thresh = cv2.threshold(gray, 150, 255, cv2.THRESH_BINARY_INV)
    kernel = cv2.getStructuringElement(cv2.MORPH_RECT, (5, 5))
    dilated = cv2.dilate(thresh, kernel, iterations=1)
    contours, _ = cv2.findContours(dilated, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

    all_boxes = []
    for cnt in contours:
        x, y, w, h = cv2.boundingRect(cnt)
        if w < 30 or h < 30:
            continue
        all_boxes.append({"x": x, "y": y, "width": w, "height": h})

    if zone_json_path is None:
        return all_boxes

    # Baca zona dan ambil hanya zona bertipe "paper"
    try:
        with open(zone_json_path, 'r') as f:
            zones = json.load(f)
    except Exception as e:
        print(json.dumps({"error": f"Gagal membaca zona: {str(e)}"}))
        sys.exit(1)

    paper_zones = [z for z in zones if z.get('type') == 'paper']

    def inside_any_paper_zone(box):
        return any(is_overlapping(box, paper) for paper in paper_zones)

    filtered_boxes = [box for box in all_boxes if inside_any_paper_zone(box)]
    return filtered_boxes

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Image path not provided"}))
        sys.exit(1)

    image_path = sys.argv[1]
    zone_path = sys.argv[2] if len(sys.argv) > 2 else None

    boxes = detect_visual_boxes(image_path, zone_path)
    print(json.dumps(boxes))
