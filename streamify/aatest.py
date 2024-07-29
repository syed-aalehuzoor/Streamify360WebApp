import paramiko
from time import sleep
# Define the server and login details
hostname = '48.217.244.62'
port = 22
username = 'ubuntu'
password = 'Hurnara12345@'  # You can also use a private key instead of a password

# Local and remote file paths
local_path = './uploaded_files/ahmed.pdf'
remote_path = '/home/ubuntu/ahmed.pdf'

try:
    # Create an SSH client
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())

    # Connect to the server
    client.connect(hostname, port, username, password)

    with client.open_sftp() as sftp:
        # Upload the file
        sftp.put(local_path, remote_path)
        print(f'Successfully uploaded {local_path} to {remote_path}')
    
    stdin, stdout, stderr = client.exec_command(command='ping -c 10 google.com')

    # Continuously read from stdout and print the output in real-time
    while True:
        if stdout.channel.recv_ready():
            output = stdout.channel.recv(1024).decode()
            print(output, end='')  # Print without adding extra newline
        if stderr.channel.recv_stderr_ready():
            error = stderr.channel.recv_stderr(1024).decode()
            print(error, end='')  # Print without adding extra newline
        if stdout.channel.exit_status_ready():
            break  # Command execution is complete

        sleep(1)  # Sleep briefly to avoid busy-waiting

    client.close()

except Exception as e:
    print(f'An error occurred: {str(e)}')
