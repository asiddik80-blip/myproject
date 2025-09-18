#!/usr/bin/env python3

import sys
import json
import cv2

def detect_and_draw_boxes(image_path, output_path):
    image = cv2.imread(image_path)
    if image is None:
        print(json.dumps({"error": f"Cannot read image: {image_path}"}), file=sys.stderr)
        return []

    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    _, thresh = cv2.threshold(gray, 150, 255, cv2.THRESH_BINARY_INV)
    kernel = cv2.getStructuringElement(cv2.MORPH_RECT, (5, 5))
    dilated = cv2.dilate(thresh, kernel, iterations=1)
    contours, _ = cv2.findContours(dilated, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

    boxes = []
    for cnt in contours:
        x, y, w, h = cv2.boundingRect(cnt)
        if w < 30 or h < 30:
            continue
        boxes.append({"x": x, "y": y, "width": w, "height": h})
        cv2.rectangle(image, (x, y), (x + w, y + h), (0, 0, 255), 2)

    cv2.imwrite(output_path, image)
    return boxes

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print(json.dumps({"error": "Image path or output path missing"}), file=sys.stderr)
        sys.exit(1)

    input_path = sys.argv[1]
    output_path = sys.argv[2]
    detected = detect_and_draw_boxes(input_path, output_path)
    print(json.dumps(detected))
