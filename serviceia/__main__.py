import os
import subprocess
import sys

def main():
    # Define paths relative to this script
    # This script is in /back3.6/serviceia/__main__.py
    # We want to run /back3.6/Tootli_AI
    
    current_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    service_dir = os.path.join(current_dir, 'Tootli_AI')
    venv_python = os.path.join(service_dir, 'venv', 'bin', 'python')

    if not os.path.exists(service_dir):
        print(f"❌ Error: Cannot find Tootli_AI directory at {service_dir}")
        sys.exit(1)

    if not os.path.exists(venv_python):
        print(f"❌ Error: Cannot find virtual environment at {venv_python}")
        print("Please run setup first.")
        sys.exit(1)

    print(f"🤖 Starting Tootli AI Service...")
    print(f"📍 Location: {service_dir}")
    print(f"🚀 Service will be enabled at: http://127.0.0.1:8000")
    print("Press Ctrl+C to stop.")
    print("-" * 50)

    # Change working directory so uvicorn finds main.py
    os.chdir(service_dir)

    try:
        # We replace the current process with the uvicorn process
        # This means the terminal becomes the server log
        cmd = [venv_python, "-m", "uvicorn", "main:app", "--reload", "--port", "8000"]
        subprocess.run(cmd)
    except KeyboardInterrupt:
        print("\n🛑 Service stopped by user.")
    except Exception as e:
        print(f"\n❌ Error running service: {e}")

if __name__ == "__main__":
    main()
