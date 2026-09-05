import sys, os, pty, select, subprocess, time

def run():
    master, slave = pty.openpty()
    ssh_cmd = ["ssh", "-p", "5225", "-o", "StrictHostKeyChecking=no", "sebastian-rivera@15.235.73.88"]
    proc = subprocess.Popen(ssh_cmd, stdin=slave, stdout=slave, stderr=slave, close_fds=True)
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
            
            if b"password:" in buffer.lower() and not password_sent:
                time.sleep(1.0)
                os.write(master, b"tHUhl8Ubl#iSafa!r*@h-\n")
                password_sent = True
                buffer = b""
                
            elif b"sebastian-rivera@" in buffer and password_sent and not sudo_sent:
                time.sleep(0.5)
                os.write(master, b"sudo -i\n")
                sudo_sent = True
                buffer = b""
                
            elif b"password" in buffer.lower() and sudo_sent and not sudo_pass_sent:
                time.sleep(1.0)
                os.write(master, b"tHUhl8Ubl#iSafa!r*@h-\n")
                sudo_pass_sent = True
                buffer = b""
                
            elif b"root@" in buffer and sudo_pass_sent and not commands_sent:
                time.sleep(0.5)
                # Find the backend directory and cat the .env file
                os.write(master, b"cat /var/www/html/.env | grep DB_\n")
                time.sleep(2.0)
                os.write(master, b"cat /var/www/tootli/.env | grep DB_\n")
                time.sleep(2.0)
                os.write(master, b"exit\n")
                os.write(master, b"exit\n")
                commands_sent = True
                buffer = b""
                
    os.close(master)

if __name__ == "__main__":
    run()
