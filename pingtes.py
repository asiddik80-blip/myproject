import requests

url = "https://pdf-services-ue1.adobe.io"
try:
    response = requests.get(url)
    print(f"Status Code: {response.status_code}")
except requests.exceptions.RequestException as e:
    print(f"Error: {e}")