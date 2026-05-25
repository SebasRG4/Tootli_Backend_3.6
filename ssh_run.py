import sys
import os
import pty
import select
import subprocess
import time

def run_ssh_command(commands):
    # Forzar la asignación de pty
    master, slave = pty.openpty()
    
    # Iniciar el proceso ssh
    ssh_cmd = [
        "ssh", 
        "-p", "5225", 
        "-o", "StrictHostKeyChecking=no", 
        "-o", "PubkeyAuthentication=no",
        "-o", "PreferredAuthentications=password",
        "sebastian-rivera@15.235.73.88"
    ]
    
    proc = subprocess.Popen(
        ssh_cmd, 
        stdin=slave, 
        stdout=slave, 
        stderr=slave, 
        close_fds=True
    )
    
    os.close(slave)
    
    password_sent = False
    sudo_sent = False
    sudo_pass_sent = False
    commands_sent = False
    
    buffer = b""
    
    while proc.poll() is None:
        r, w, x = select.select([master], [], [], 1.0)
        if master in r:
            try:
                data = os.read(master, 1024)
            except OSError:
                break
            if not data:
                break
                
            buffer += data
            sys.stdout.buffer.write(data)
            sys.stdout.flush()
            
            # Buscar prompts de contraseña
            if b"password:" in buffer.lower() and not password_sent:
                time.sleep(1.0) # Esperar a que el prompt esté completamente listo
                os.write(master, b"tHUhl8Ubl#iSafa!r*@h-\r")
                password_sent = True
                print("\n[DEBUG] Contraseña enviada.")
                buffer = b""
                
            elif b"sebastian-rivera@" in buffer and password_sent and not sudo_sent:
                time.sleep(0.5)
                os.write(master, b"sudo -i\r")
                sudo_sent = True
                print("\n[DEBUG] Comando sudo -i enviado.")
                buffer = b""
                
            elif b"password" in buffer.lower() and sudo_sent and not sudo_pass_sent:
                time.sleep(1.0) # Esperar al prompt de sudo
                os.write(master, b"tHUhl8Ubl#iSafa!r*@h-\r")
                sudo_pass_sent = True
                print("\n[DEBUG] Contraseña de sudo enviada.")
                buffer = b""
                
            elif b"root@" in buffer and sudo_pass_sent and not commands_sent:
                time.sleep(0.5)
                # Ya somos root! Correr los comandos solicitados
                for cmd in commands:
                    os.write(master, f"{cmd}\r".encode())
                    time.sleep(0.5)
                os.write(master, b"exit\r") # Salir de root
                os.write(master, b"exit\r") # Salir de ssh
                commands_sent = True
                print("\n[DEBUG] Comandos ejecutados.")
                buffer = b""
                
    os.close(master)

if __name__ == "__main__":
    # Comandos de prueba iniciales para investigar el servidor
    cmds = [
        "pwd",
        "ls -la",
        "docker ps",
        "find / -name 'scrape_justo.py' 2>/dev/null || find / -name 'artisan' 2>/dev/null"
    ]
    run_ssh_command(cmds)
