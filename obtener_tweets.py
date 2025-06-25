import requests
import json
from datetime import datetime, timedelta

# Configuración
bearer_token = ""
query = "videojuegos"
max_results = 30
start_time = (datetime.utcnow() - timedelta(days=3)).isoformat("T") + "Z"

# URL de la API
url = "https://api.twitter.com/2/tweets/search/recent"
params = {
    "query": query,
    "max_results": max_results,
    "start_time": start_time,
    "tweet.fields": "created_at",
    "expansions": "author_id",
    "user.fields": "username"
}
headers = {
    "Authorization": f"Bearer {bearer_token}"
}

# Solicitud a la API
response = requests.get(url, headers=headers, params=params)

# Validación
if response.status_code == 200:
    data = response.json()
    tweets = data.get("data", [])
    users = {u["id"]: u["username"] for u in data.get("includes", {}).get("users", [])}

    # Guardar en archivo
    with open("tweets.json", "w", encoding="utf-8") as f:
        json.dump({"tweets": tweets, "users": users}, f, ensure_ascii=False, indent=4)

    print("✅ Archivo tweets.json creado con éxito con", len(tweets), "tweets.")
else:
    print("❌ Error en la solicitud:", response.status_code)
    print(response.text)