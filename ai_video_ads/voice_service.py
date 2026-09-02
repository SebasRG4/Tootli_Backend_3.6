import os
import requests
from pathlib import Path

# Set your ElevenLabs API Key via environment variable ELEVENLABS_API_KEY
ELEVENLABS_API_KEY = os.getenv("ELEVENLABS_API_KEY", "")

def generate_voiceover(text: str, voice_id: str = "21m00Tcm4TlvDq8ikWAM", output_filename: str = "voiceover.mp3") -> str:
    """
    Generates a voiceover from text using ElevenLabs API.
    voice_id defaults to a pre-made voice (Rachel). You can change this to any valid Voice ID.
    Returns the path to the saved audio file.
    """
    if not ELEVENLABS_API_KEY:
        print("Warning: ELEVENLABS_API_KEY environment variable is not set. Cannot generate real audio.")
        return ""
        
    url = f"https://api.elevenlabs.io/v1/text-to-speech/{voice_id}"
    
    headers = {
        "Accept": "audio/mpeg",
        "Content-Type": "application/json",
        "xi-api-key": ELEVENLABS_API_KEY
    }
    
    data = {
        "text": text,
        "model_id": "eleven_multilingual_v2",
        "voice_settings": {
            "stability": 0.5,
            "similarity_boost": 0.75
        }
    }
    
    response = requests.post(url, json=data, headers=headers)
    
    if response.status_code != 200:
        raise Exception(f"Error from ElevenLabs API: {response.status_code} - {response.text}")
        
    with open(output_filename, "wb") as f:
        for chunk in response.iter_content(chunk_size=1024):
            if chunk:
                f.write(chunk)
                
    return output_filename

if __name__ == "__main__":
    print("Testing ElevenLabs Voice Generation...")
    try:
        sample_text = "¡Hola! ¿Tienes hambre y flojera de cocinar? Pide en Tootli hoy mismo con envío gratis."
        output_file = generate_voiceover(sample_text, output_filename="test_voice.mp3")
        if output_file:
            print(f"Éxito: Audio generado en {output_file}")
    except Exception as e:
        print(f"Error generating voice: {e}")
