import cv2
import json
import sys

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

def save_visual_boxes_as_placard_zones(image_path, output_json_path):
    image = load_image(image_path)
    visual_boxes = detect_visual_boxes(image)

    zones = [{"zone_box": box} for box in visual_boxes]

    with open(output_json_path, 'w', encoding='utf-8') as out:
        json.dump(zones, out, indent=2)

    print(f"{len(zones)} placard zones saved to {output_json_path}")

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python detect_visual_placard_zones_basic.py <image_path> <output_json_path>")
        sys.exit(1)

    save_visual_boxes_as_placard_zones(sys.argv[1], sys.argv[2])
