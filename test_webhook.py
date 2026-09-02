import requests

with open('ai_video_ads/test_avatar.jpg', 'rb') as img, open('ai_video_ads/test_audio.wav', 'rb') as aud:
    files = {
        'image': ('avatar.jpg', img, 'image/jpeg'),
        'audio': ('voice.wav', aud, 'audio/wav')
    }
    print("Enviando petición a Modal...")
    response = requests.post("https://sebasrg4--video-avatar-generator-generate-web.modal.run", files=files)
    print(f"Status: {response.status_code}")
    if response.status_code != 200:
        print(f"Error: {response.text}")
    else:
        print("Success!")
