import os
import sys
import requests
import modal
from modal_worker import app, generate_talking_avatar, download_models


def download_test_assets():
    """Descarga imagen con rostro humano y audio WAV para prueba."""
    img_path = "test_avatar.jpg"
    audio_path = "test_audio.wav"

    headers = {"User-Agent": "Mozilla/5.0"}

    if not os.path.exists(img_path):
        print("Descargando imagen de prueba (rostro humano)...")
        # Imagen pública de Creative Commons con rostro humano claro
        response = requests.get(
            "https://upload.wikimedia.org/wikipedia/commons/thumb/1/14/Gatto_europeo4.jpg/440px-Gatto_europeo4.jpg",
            headers=headers,
            timeout=30,
        )
        # Usar una imagen de prueba de Unsplash (rostro frontal)
        response = requests.get(
            "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&auto=format&fit=crop",
            headers=headers,
            timeout=30,
        )
        response.raise_for_status()
        with open(img_path, "wb") as f:
            f.write(response.content)
        print(f"  ✓ Imagen guardada: {img_path} ({len(response.content) / 1024:.1f} KB)")

    if not os.path.exists(audio_path):
        print("Generando audio WAV de prueba...")
        import wave
        import struct
        import math

        sample_rate = 22050
        duration = 3  # segundos
        frequency = 220  # Hz (nota LA baja)

        with wave.open(audio_path, "w") as wav_file:
            wav_file.setnchannels(1)       # mono
            wav_file.setsampwidth(2)       # 16-bit
            wav_file.setframerate(sample_rate)
            num_samples = sample_rate * duration
            for i in range(num_samples):
                # Onda senoidal suave (simula voz humana básica)
                sample = int(16000 * math.sin(2 * math.pi * frequency * i / sample_rate))
                wav_file.writeframes(struct.pack("<h", sample))

        print(f"  ✓ Audio generado: {audio_path} ({os.path.getsize(audio_path) / 1024:.1f} KB)")

    return img_path, audio_path


def main():
    print("=" * 60)
    print("  Prueba del Pipeline: LivePortrait + Wav2Lip en Modal")
    print("=" * 60)

    # 1. Descargar assets de prueba
    img_path, audio_path = download_test_assets()

    with open(img_path, "rb") as f:
        img_bytes = f.read()

    with open(audio_path, "rb") as f:
        audio_bytes = f.read()

    print("\nConectando a Modal...")

    try:
        with modal.enable_output():
            with app.run():
                # Paso 1: Asegurarse de que los pesos estén descargados
                print("\n[1/2] Verificando/descargando pesos del modelo...")
                download_models.remote()

                # Paso 2: Correr el pipeline de inferencia
                print("\n[2/2] Ejecutando pipeline LivePortrait → Wav2Lip...")
                output_video_bytes = generate_talking_avatar.remote(
                    source_image_bytes=img_bytes,
                    audio_bytes=audio_bytes,
                    driving_video_bytes=None,  # Usar video de ejemplo del repo
                )

        output_path = "test_output.mp4"
        with open(output_path, "wb") as f:
            f.write(output_video_bytes)

        size_kb = len(output_video_bytes) / 1024
        print(f"\n✅ ¡Éxito! Video guardado en '{output_path}' ({size_kb:.1f} KB)")

    except Exception as e:
        print(f"\n❌ Error durante la ejecución en Modal: {e}", file=sys.stderr)
        raise


if __name__ == "__main__":
    main()

