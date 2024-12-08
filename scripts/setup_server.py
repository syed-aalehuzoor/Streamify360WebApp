import mysql.connector
from mysql.connector import Error
from dotenv import load_dotenv
import os
import logging
from argparse import ArgumentParser
from paramiko import SSHClient, AutoAddPolicy, RSAKey

script_dir = os.path.dirname(os.path.abspath(__file__))

# Set up logging
logging.basicConfig(filename=os.path.join(script_dir, 'output.log'), level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

# Argument parsing
parser = ArgumentParser(description="Script to configure a remote server.")
parser.add_argument('--id', type=str, required=True, help="Server Id")
args = parser.parse_args()
server_id = args.id

logging.info(f"Server Id: {server_id}")

# Load environment variables from .env file
load_dotenv(os.path.join(script_dir, '../.env'))

# Get database credentials from environment variables
db_host = os.getenv('DB_HOST')
db_user = os.getenv('DB_USERNAME')
db_password = os.getenv('DB_PASSWORD')
db_name = os.getenv('DB_DATABASE')

connection = None
cursor = None

try:
    # Connect to the MySQL database
    connection = mysql.connector.connect(
        host=db_host,
        user=db_user,
        password=db_password,
        database=db_name
    )

    if connection.is_connected():
        logging.info("Successfully connected to the database")

        # Create a cursor object
        cursor = connection.cursor(dictionary=True)

        # Fetch the server_instance using the server_id
        query = "SELECT ip, ssh_port, username, domain, status, type FROM servers WHERE id = %s"
        cursor.execute(query, (server_id,))
        result = cursor.fetchone()
        
        if result:
            ip = result['ip']
            ssh_port = result['ssh_port']
            username = result['username']
            domain = result['domain']
            status = result['status']
            type = result['type']

            client = SSHClient()
            client.set_missing_host_key_policy(AutoAddPolicy)
            
            try:
                # Update status to 'Connecting to Server'
                update_query = "UPDATE servers SET status = %s WHERE id = %s"
                cursor.execute(update_query, ('Connecting to Server', server_id))
                connection.commit()                
        
                key = RSAKey.from_private_key_file(filename=os.path.join(script_dir, 'id_rsa'))
                client.connect(hostname=ip, port=ssh_port, username=username, pkey=key, allow_agent=False, look_for_keys=False)
                try:
                    # List of commands to execute on the server
                    statuses = [
                        ('Updating and Upgrading System', 'sudo apt update && sudo apt upgrade -y'),
                    ]

                    if type == 'encoder':
                        statuses.extend([
                            ('Installing FFmpeg', 'sudo apt install ffmpeg -y'),
                            ('Installing pip','sudo apt install python3-pip -y'),
                            ('Installing YT Downloader','pip install yt-dlp'),
                            ('Installing GDrive Dowloader','pip install gdown'),
                            ('Creating Directory for Streams', 'sudo mkdir -p /var/www/html/streams/')
                        ])
                    elif type == 'storage':
                        statuses.extend([
                            ('Installing Nginx', 'sudo apt install -y nginx'),
                            ('Enabling Nginx', 'sudo systemctl enable nginx'),
                            ('Restarting Nginx', 'sudo systemctl restart nginx'),
                            ('Allowing Nginx through UFW', 'sudo ufw allow "Nginx Full"'),
                            ('Reloading UFW', 'sudo ufw reload'),
                            ('Configuring Nginx CORS', r'grep -q "add_header Access-Control-Allow-Origin" /etc/nginx/nginx.conf || sudo sed -i "/^http {/a \ \ \ \ \tadd_header Access-Control-Allow-Origin *;" /etc/nginx/nginx.conf'),
                            ('Restarting Nginx Service', 'sudo service nginx restart'),
                            ('Creating Directory for Streams', 'sudo mkdir -p /var/www/html/streams/')
                        ])

                    for status, command in statuses:
                        # Update database status
                        update_query = "UPDATE servers SET status = %s WHERE id = %s"
                        cursor.execute(update_query, (status, server_id))
                        connection.commit()

                        # Execute command on the server
                        logging.info(f"Executing command: {command}")
                        stdin, stdout, stderr = client.exec_command(command)
                        error = stderr.read().decode()
                        if error:
                            logging.error(f"Error executing command '{command}': {error}")
                        exit_status = stdout.channel.recv_exit_status()
                        if exit_status != 0:
                            logging.error(f"Command '{command}' failed with exit status {exit_status}")
                            raise Exception("Command execution error")
                    if type == 'encoder':
                        rsa_key_path = os.path.join(script_dir, 'id_rsa')
                        try:
                            with client.open_sftp() as sftp:
                                sftp.put(localpath=rsa_key_path, remotepath='/home/ubuntu/id_rsa')
                        except Exception as e:
                            logging.error(f"Configuration failed at here: {e}")

                    # Final status update
                    update_query = "UPDATE servers SET status = %s WHERE id = %s"
                    cursor.execute(update_query, ('live', server_id))
                    connection.commit()

                except Exception as e:
                    logging.error(f"Configuration failed: {e}")
                    update_query = "UPDATE servers SET status = %s WHERE id = %s"
                    cursor.execute(update_query, ('Configuration Failed', server_id))
                    connection.commit()
            except Exception as e:
                logging.error(f"Authentication failed: {e}")
                update_query = "UPDATE servers SET status = %s WHERE id = %s"
                cursor.execute(update_query, ('Authentication Failed', server_id))
                connection.commit()

            finally:
                client.close()
                logging.info("SSH connection closed")
                
        else:
            logging.warning(f"No server found with ID: {server_id}")

except Error as e:
    logging.error(f"Database error: {e}")

finally:
    if connection and connection.is_connected():
        cursor.close()
        connection.close()
        logging.info("MySQL connection is closed")
