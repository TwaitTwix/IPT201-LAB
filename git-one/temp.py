import requests
city = "Manila"
url = f"https://wttr.in/{city}?format=j1"
response = requests.get(url)
data = response.json()
print("City:", city)
print("temperature:",
      data["current_condition"][0]["temp_C"],
      "C")