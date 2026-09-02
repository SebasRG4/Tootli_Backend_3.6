import modal
from pathlib import Path

# ---------------------------------------------------------------------------
# Modal Volume para cachear pesos (se descarga una sola vez)
# ---------------------------------------------------------------------------
weights_volume = modal.Volume.from_name("model-weights-vol", create_if_missing=True)
WEIGHTS_DIR = Path("/weights")

# ---------------------------------------------------------------------------
# Imagen Docker base con PyTorch + CUDA + dependencias del sistema
# ---------------------------------------------------------------------------
base_image = (
    modal.Image.debian_slim(python_version="3.10")
    .apt_install(
        "git", "ffmpeg", "libsm6", "libxext6", "libgl1",
        "libglib2.0-0", "wget", "curl"
    )
    .pip_install(
        "torch==2.3.0",
        "torchvision==0.18.0",
        "torchaudio==2.3.0",
        extra_index_url="https://download.pytorch.org/whl/cu121"
    )
    # Clonar LivePortrait (repo correcto: KwaiVGI) e instalar sus dependencias
    .run_commands(
        "git clone https://github.com/KwaiVGI/LivePortrait /root/LivePortrait",
        "pip install -r /root/LivePortrait/requirements.txt",
    )
    # Instalar Wav2Lip (sin usar su requirements.txt — pide opencv==4.1.0.25 que ya no existe)
    .run_commands(
        "git clone https://github.com/Rudrabha/Wav2Lip /root/Wav2Lip",
        # Parchear audio.py: librosa>=0.8 cambió mel() a keyword-only args
        # Old: librosa.filters.mel(hp.sample_rate, hp.n_fft, ...)
        # New: librosa.filters.mel(sr=hp.sample_rate, n_fft=hp.n_fft, ...)
        "sed -i 's/librosa.filters.mel(hp.sample_rate, hp.n_fft/librosa.filters.mel(sr=hp.sample_rate, n_fft=hp.n_fft/g' /root/Wav2Lip/audio.py",
        # Instalar solo lo que Wav2Lip necesita en versiones disponibles.
        # numpy, opencv, pillow, tqdm ya vienen de LivePortrait.
        "pip install librosa>=0.8.0 face-alignment numba",
    )
    # Descargar el modelo de detección de caras de Wav2Lip (s3fd)
    .run_commands(
        "mkdir -p /root/Wav2Lip/face_detection/detection/sfd",
        "wget -q -O /root/Wav2Lip/face_detection/detection/sfd/s3fd.pth "
        "'https://www.adrianbulat.com/downloads/python-fan/s3fd-619a316812.pth'",
    )
)

app = modal.App("video-avatar-generator", image=base_image)

# ---------------------------------------------------------------------------
# Función de descarga de pesos (correr una sola vez, escribe al Volume)
# ---------------------------------------------------------------------------
@app.function(
    volumes={str(WEIGHTS_DIR): weights_volume},
    timeout=1800,  # 30 min para descargar todo
)
def download_models():
    """
    Descarga los pesos de LivePortrait y Wav2Lip al Volume persistente.
    Solo necesita correr una vez.
    """
    import subprocess

    liveportrait_dir = WEIGHTS_DIR / "liveportrait"
    wav2lip_dir = WEIGHTS_DIR / "wav2lip"

    # --- LivePortrait ---
    lp_marker = liveportrait_dir / "base_models" / "appearance_feature_extractor.pth"
    if not lp_marker.exists():
        print("Descargando pesos de LivePortrait desde KwaiVGI/LivePortrait...")
        liveportrait_dir.mkdir(parents=True, exist_ok=True)
        subprocess.run([
            "python", "-c",
            (
                "from huggingface_hub import snapshot_download; "
                "snapshot_download("
                "    repo_id='KwaiVGI/LivePortrait',"
                f"   local_dir='{liveportrait_dir}',"
                "    ignore_patterns=['*.md', '*.txt']"
                ")"
            )
        ], check=True)
        print("✓ Pesos de LivePortrait descargados.")
    else:
        print("✓ Pesos de LivePortrait ya existen en el Volume.")

    # --- Wav2Lip GAN ---
    wav2lip_model = wav2lip_dir / "wav2lip_gan.pth"
    if not wav2lip_model.exists():
        print("Descargando pesos de Wav2Lip GAN...")
        wav2lip_dir.mkdir(parents=True, exist_ok=True)

        # Intentar múltiples fuentes públicas en HuggingFace
        sources = [
            # Fuente 1: numz/wav2lip_studio (path correcto: Wav2lip con l minúscula)
            ("numz/wav2lip_studio", "Wav2lip/wav2lip_gan.pth"),
            # Fuente 2: Nekochu/Wav2Lip
            ("Nekochu/Wav2Lip", "wav2lip_gan.pth"),
            # Fuente 3: rippertnt/wav2lip
            ("rippertnt/wav2lip", "checkpoints/wav2lip_gan.pth"),
        ]

        downloaded = False
        for repo_id, filename in sources:
            print(f"  Intentando: {repo_id}/{filename}...")
            result = subprocess.run([
                "python", "-c",
                (
                    f"from huggingface_hub import hf_hub_download; "
                    f"path = hf_hub_download("
                    f"    repo_id='{repo_id}',"
                    f"    filename='{filename}',"
                    f"    local_dir='{wav2lip_dir}'"
                    f"); "
                    f"import shutil, pathlib; "
                    f"dest = pathlib.Path('{wav2lip_dir}') / 'wav2lip_gan.pth'; "
                    f"src = pathlib.Path(path); "
                    f"shutil.copy(src, dest) if src != dest else None; "
                    f"print('OK:', path)"
                )
            ], capture_output=True, text=True)
            if result.returncode == 0:
                print(f"  ✓ Descargado desde {repo_id}")
                downloaded = True
                break
            else:
                print(f"  ✗ Falló {repo_id}: {result.stderr.strip()[-200:]}")

        if not downloaded:
            raise RuntimeError("No se pudo descargar wav2lip_gan.pth desde ninguna fuente.")

        print("✓ Pesos de Wav2Lip descargados.")
    else:
        print("✓ Pesos de Wav2Lip ya existen en el Volume.")

    weights_volume.commit()
    print("✅ Todos los pesos están listos.")


# ---------------------------------------------------------------------------
# Función principal de inferencia
# ---------------------------------------------------------------------------
@app.function(
    gpu="L4",
    timeout=600,
    volumes={str(WEIGHTS_DIR): weights_volume},
)
def generate_talking_avatar(
    source_image_bytes: bytes,
    audio_bytes: bytes,
    driving_video_bytes: bytes = None,
) -> bytes:
    """
    Pipeline de dos etapas:
      1. LivePortrait: anima el rostro estático usando un video de driving
      2. Wav2Lip: sincroniza los labios con el audio proporcionado

    Args:
        source_image_bytes: Imagen JPG del avatar (debe contener un rostro humano)
        audio_bytes: Audio WAV para lip sync
        driving_video_bytes: (Opcional) Video de referencia de movimiento.
                             Si es None, usa el video de ejemplo incluido en LivePortrait.

    Returns:
        bytes del video MP4 final
    """
    import subprocess
    import shutil

    tmp = Path("/tmp/avatar_pipeline")
    tmp.mkdir(exist_ok=True)

    source_img = tmp / "source.jpg"
    audio_wav = tmp / "audio.wav"
    driving_video = tmp / "driving.mp4"
    final_out = tmp / "final_output.mp4"

    # Escribir inputs
    source_img.write_bytes(source_image_bytes)
    audio_wav.write_bytes(audio_bytes)

    # Video de driving: usar el proporcionado o el ejemplo del repo
    if driving_video_bytes:
        driving_video.write_bytes(driving_video_bytes)
    else:
        example_dirs = [
            Path("/root/LivePortrait/assets/examples/driving/d0.mp4"),
            Path("/root/LivePortrait/assets/examples/driving/d14.mp4"),
        ]
        found = next((p for p in example_dirs if p.exists()), None)
        if not found:
            candidates = list(Path("/root/LivePortrait/assets/examples/driving").glob("*.mp4"))
            found = candidates[0] if candidates else None
        if not found:
            raise FileNotFoundError(
                "No se encontró video de driving. Proporciona uno o verifica el repo de LivePortrait."
            )
        shutil.copy(found, driving_video)
        print(f"Usando video de driving de ejemplo: {found.name}")

    # Configurar symlink de pesos para que LivePortrait los encuentre
    lp_weights_link = Path("/root/LivePortrait/pretrained_weights")
    if lp_weights_link.is_symlink():
        lp_weights_link.unlink()
    elif lp_weights_link.exists():
        shutil.rmtree(lp_weights_link)
    lp_weights_link.symlink_to(WEIGHTS_DIR / "liveportrait")

    # ------------------------------------------------------------------
    # ETAPA 1: LivePortrait — face reenactment
    # ------------------------------------------------------------------
    print("▶ Etapa 1/2: LivePortrait face reenactment...")
    lp_out_dir = tmp / "lp_output"
    lp_out_dir.mkdir(exist_ok=True)

    lp_cmd = [
        "python", "/root/LivePortrait/inference.py",
        "-s", str(source_img),
        "-d", str(driving_video),
        "-o", str(lp_out_dir),
    ]
    result = subprocess.run(lp_cmd, capture_output=True, text=True, cwd="/root/LivePortrait")
    if result.returncode != 0:
        print("=== STDOUT LivePortrait ===\n", result.stdout[-3000:])
        print("=== STDERR LivePortrait ===\n", result.stderr[-3000:])
        raise RuntimeError(f"LivePortrait falló:\n{result.stderr[-3000:]}")

    # Buscar el MP4 generado por LivePortrait.
    # LivePortrait produce:
    #   - source--driving.mp4        ← el video animado (lo que queremos)
    #   - source--driving_concat.mp4 ← concatenación side-by-side (descartar)
    output_videos = [
        v for v in lp_out_dir.rglob("*.mp4")
        if "_concat" not in v.name
    ]
    if not output_videos:
        # Intentar también en tmp raíz por si LivePortrait cambió su output path
        output_videos = [
            v for v in tmp.glob("*.mp4")
            if "_concat" not in v.name and v.name != "driving.mp4" and v.name != "final_output.mp4"
        ]
    if not output_videos:
        print("STDOUT:", result.stdout[-2000:])
        raise FileNotFoundError(f"LivePortrait no produjo un MP4. STDERR: {result.stderr[-2000:]}")

    liveportrait_out = sorted(output_videos, key=lambda p: p.stat().st_size, reverse=True)[0]
    print(f"✓ LivePortrait output: {liveportrait_out.name} ({liveportrait_out.stat().st_size / 1024:.1f} KB)")

    # ------------------------------------------------------------------
    # ETAPA 2: Wav2Lip — lip sync con audio
    # ------------------------------------------------------------------
    print("▶ Etapa 2/2: Wav2Lip lip sync...")

    wav2lip_checkpoint = WEIGHTS_DIR / "wav2lip" / "wav2lip_gan.pth"
    wav2lip_cmd = [
        "python", "/root/Wav2Lip/inference.py",
        "--checkpoint_path", str(wav2lip_checkpoint),
        "--face", str(liveportrait_out),
        "--audio", str(audio_wav),
        "--outfile", str(final_out),
        "--nosmooth",
    ]
    result = subprocess.run(wav2lip_cmd, capture_output=True, text=True, cwd="/root/Wav2Lip")
    if result.returncode != 0:
        print("=== STDOUT Wav2Lip ===\n", result.stdout[-3000:])
        print("=== STDERR Wav2Lip ===\n", result.stderr[-3000:])
        raise RuntimeError(f"Wav2Lip falló:\n{result.stderr[-3000:]}")

    if not final_out.exists():
        raise FileNotFoundError(f"Wav2Lip no produjo el video final. STDERR: {result.stderr[-2000:]}")

    size_kb = final_out.stat().st_size / 1024
    print(f"✅ Pipeline completo. Video final: {final_out.name} ({size_kb:.1f} KB)")
    return final_out.read_bytes()

# ---------------------------------------------------------------------------
# API REST (Webhook) para interactuar desde Laravel
# ---------------------------------------------------------------------------
from fastapi import Request, Response
from fastapi.responses import JSONResponse

@app.function(
    gpu="L4",
    timeout=600,
    volumes={str(WEIGHTS_DIR): weights_volume},
)
@modal.web_endpoint(method="POST")
async def generate_web(request: Request):
    """
    Endpoint HTTP que recibe 'image' y 'audio' via multipart/form-data.
    Retorna el MP4 directamente.
    """
    try:
        form = await request.form()
        if "image" not in form or "audio" not in form:
            return JSONResponse({"error": "Missing 'image' or 'audio' in form data"}, status_code=400)
            
        image_bytes = await form["image"].read()
        audio_bytes = await form["audio"].read()
        
        # Llamar a la función principal localmente dentro del mismo worker
        video_bytes = generate_talking_avatar.local(
            source_image_bytes=image_bytes,
            audio_bytes=audio_bytes,
            driving_video_bytes=None
        )
        
        return Response(content=video_bytes, media_type="video/mp4")
    except Exception as e:
        import traceback
        return JSONResponse({
            "error": str(e),
            "traceback": traceback.format_exc()
        }, status_code=500)

