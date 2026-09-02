import os
from google import genai
from pydantic import BaseModel
from typing import List

# It will use GEMINI_API_KEY from environment variables automatically if set
# otherwise you can initialize with api_key="YOUR_KEY"
client = genai.Client()

class VideoScene(BaseModel):
    scene_number: int
    duration_seconds: float
    visual_type: str # "AVATAR" | "B_ROLL_FOOD" | "B_ROLL_APP"
    b_roll_keyword: str
    voiceover: str
    onscreen_text: str

class AdScript(BaseModel):
    business: str # "TOOTLI" | "CASA_LAS_FUENTES"
    hook_angle: str
    scenes: List[VideoScene]

def generate_ad_script(business: str, objective: str) -> AdScript:
    prompt = f"""
    Eres un estratega de anuncios UGC para redes sociales (TikTok/Reels).
    Negocio: {business}
    Objetivo: {objective}
    
    Genera un guion de 15 a 20 segundos optimizado para alta retención (hook fuerte en los primeros 3s).
    Alterna entre tomas del AVATAR hablando y tomas de B-ROLL (pantalla de la app o comida).
    """
    
    response = client.models.generate_content(
        model='gemini-2.5-flash',
        contents=prompt,
        config={
            'response_mime_type': 'application/json',
            'response_schema': AdScript,
        },
    )
    return response.parsed

if __name__ == "__main__":
    print("Testing Gemini Script Generation...")
    try:
        script = generate_ad_script("TOOTLI", "Promoción de fin de semana tacos 2x1")
        print("\nGenerated Script Object:")
        print(script.model_dump_json(indent=2))
    except Exception as e:
        print(f"Error generating script: {e}")
