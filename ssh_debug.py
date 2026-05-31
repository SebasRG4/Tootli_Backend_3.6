import sys
import os
import pty
import select
import subprocess
import time

def debug_ssh():
    master, slave = pty.openpty()
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
    
    buffer = b""
    while proc.poll() is None:
        r, w, x = select.select([master], [], [], 2.0)
        if master in r:
            try:
                data = os.read(master, 1024)
            except OSError:
                break
            if not data:
                break
            buffer += data
            print(f"[RAW READ] {data}")
            
            if b"password" in data.lower():
                print("[DEBUG] Found password prompt! Sending password...")
                os.write(master, b"tHUhl8Ubl#iSafa!r*@h-\n")
                
    print(f"[FINAL BUFFER] {buffer}")
    os.close(master)

if __name__ == "__main__":
    debug_ssh()
