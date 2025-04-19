from azure.batch import models, BatchServiceClient
from azure.common.credentials import ServicePrincipalCredentials
from time import sleep
import os
import wget
import mysql.connector
from dotenv import load_dotenv
from azure.storage.blob import ContainerSasPermissions, BlobSasPermissions, generate_blob_sas, generate_container_sas, BlobServiceClient
import datetime
from paramiko import SSHClient, AutoAddPolicy, RSAKey
from urllib.parse import urlparse
import gdown
from logging import basicConfig, info as log_info, error as log_error, INFO, getLogger, WARNING
from pytubefix import YouTube
import subprocess

script_dir = os.path.dirname(os.path.abspath(__file__))
load_dotenv(os.path.join(script_dir, '../.env'))

basicConfig(filename=os.path.join(script_dir, 'video_process.logs'), level=INFO, format='%(asctime)s - %(levelname)s - %(message)s')

db_host = os.getenv('DB_HOST')
db_user = os.getenv('DB_USERNAME')
db_password = os.getenv('DB_PASSWORD')
db_database = os.getenv('DB_DATABASE')

allowed_threads = {
    'basic': os.getenv('BASIC_PLAN_THREADS'),
    'premium': os.getenv('PREMIUM_PLAN_THREADS'),
    'enterprise': os.getenv('ENTERPRISE_PLAN_THREADS')
}

STORAGE_ACCOUNT_NAME = os.getenv('AZURE_STORAGE_NAME')
STORAGE_ACCOUNT_KEY = os.getenv('AZURE_STORAGE_KEY')
CONTAINER_NAME = os.getenv('AZURE_STORAGE_CONTAINER')

APP_URL = os.getenv('APP_URL')

TENANT_ID = os.getenv('AZURE_TENANT_ID')
RESOURCE = "https://batch.core.windows.net/"
CLIENT_ID = os.getenv('AZURE_CLIENT_ID')
SECRET = os.getenv('AZURE_CLIENT_SECRET')
BATCH_ACCOUNT_URL = "https://hlsencoder.eastus.batch.azure.com"

credentials = ServicePrincipalCredentials(
    client_id=CLIENT_ID,
    secret=SECRET,
    tenant=TENANT_ID,
    resource=RESOURCE
)

batch_client = BatchServiceClient(credentials, batch_url=BATCH_ACCOUNT_URL)

def run_command(command, key = '<VideoID>'):
    command_string = " ".join(command) if isinstance(command, list) else command
    log_info(f'Video ID: {key}, Running Command: {command_string}')
    result = subprocess.run(command, check=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
    log_info(f'Video ID: {key}, Completed Command: {command_string}')

def is_youtube_url(url):
    parsed_url = urlparse(url)
    return 'youtube.com' in parsed_url.netloc or 'youtu.be' in parsed_url.netloc

def is_google_drive_url(url):
    return 'drive.google.com' in url

def is_direct_mp4_url(url):
    return urlparse(url).scheme in ('http', 'https') and url.lower().endswith('.mp4')

def upload_to_blob(local_filepath, blob_name):
    blob_service_client = BlobServiceClient(account_url=f"https://{STORAGE_ACCOUNT_NAME}.blob.core.windows.net/", credential=STORAGE_ACCOUNT_KEY)
    container_client = blob_service_client.get_container_client(CONTAINER_NAME)
    blob_client = container_client.get_blob_client(blob=blob_name)
    with open(local_filepath, 'rb') as data:   
        blob_client.upload_blob(data=data)
    if os.path.exists(local_filepath):
        os.remove(local_filepath)

def download_from_youtube(video_key, url):
    extension = 'mp4'
    video_filename = f'{video_key}.{extension}'
    yt = YouTube(url, use_oauth=True)
    ys = yt.streams.get_highest_resolution()
    local_filepath = ys.download(output_path='/www/wwwroot/dev.streamify360.com/scripts', filename=video_filename)
    blob_name = f'videos/{video_filename}'
    print(f'Downloaded File: {local_filepath}')
    upload_to_blob(local_filepath=local_filepath, blob_name=blob_name)
    return blob_name

def download_from_drive(video_key, url):
    try:
        log_info(f'Downloading Video From Drive for Video: {video_key}')
        filename = gdown.download(url=url, quiet=True, fuzzy=True)
        local_filepath = os.path.join(script_dir, filename)
        blob_name = f'videos/{video_key}.mp4'
        upload_to_blob(local_filepath=local_filepath, blob_name=blob_name)
        return blob_name
    except:
        return None

def is_blob_exists(blob_name):
    blob_service_client = BlobServiceClient(account_url=f"https://{STORAGE_ACCOUNT_NAME}.blob.core.windows.net/", credential=STORAGE_ACCOUNT_KEY)
    container_client = blob_service_client.get_container_client(CONTAINER_NAME)
    blob_client = container_client.get_blob_client(blob=blob_name)
    return blob_client.exists()

def blob_size(blob_name):
    blob_service_client = BlobServiceClient(account_url=f"https://{STORAGE_ACCOUNT_NAME}.blob.core.windows.net/", credential=STORAGE_ACCOUNT_KEY)
    container_client = blob_service_client.get_container_client(CONTAINER_NAME)
    blob_client = container_client.get_blob_client(blob=blob_name)
    properties = blob_client.get_blob_properties()
    return properties.size

def download_Direct_mp4(video_key, url):
    try:
        log_info(f'Downloading Mp4 Video for Video: {video_key}')
        output_path = os.path.join(script_dir, f'temps')
        input_file = wget.download(url=url, out=output_path)
        local_filepath = os.path.join(output_path, input_file)
        blob_name = f'videos/{video_key}.mp4'
        upload_to_blob(local_filepath=local_filepath, blob_name=blob_name)
        return blob_name
    except:
        return None

def create_sas_url(blob_name=None, container_name=None, expire_after: int = 24):
    start_time = datetime.datetime.now(datetime.timezone.utc)
    expiry_time = start_time + datetime.timedelta(hours=expire_after)
    if blob_name:
        sas_token = generate_blob_sas(
            account_name=STORAGE_ACCOUNT_NAME,
            container_name=CONTAINER_NAME,
            blob_name=blob_name,
            account_key=STORAGE_ACCOUNT_KEY,
            permission=BlobSasPermissions(read=True),
            expiry=expiry_time,
            start=start_time
        )
        return f"https://{STORAGE_ACCOUNT_NAME}.blob.core.windows.net/{CONTAINER_NAME}/{blob_name}?{sas_token}"

    elif container_name:
        
        sas_token = generate_container_sas(
            account_name=STORAGE_ACCOUNT_NAME,
            container_name=CONTAINER_NAME,
            account_key=STORAGE_ACCOUNT_KEY,
            permission=ContainerSasPermissions(write=True),
            expiry=expiry_time,
            start=start_time
        )
        return f"https://{STORAGE_ACCOUNT_NAME}.blob.core.windows.net/{CONTAINER_NAME}?{sas_token}"
    else:
         return None
    
def get_file_extension(file_url):
    """
    Helper function to get the file extension from the URL.
    """
    return os.path.splitext(file_url)[1]  # Get the file extension from the URL

def download_video(video_key):
    connection = mysql.connector.connect(host=db_host, user=db_user, password=db_password, database=db_database)
    cursor = connection.cursor(dictionary=True)    
    cursor.execute("SELECT * FROM videos WHERE id = %s", (video_key,))
    video = cursor.fetchone()
    if not video:
        raise Exception(f"Video with ID {video_key} not found")
    
    url = video['video_url']
    if is_youtube_url(url):
        blob_name = download_from_youtube(video_key, url)
    elif is_google_drive_url(url):
        blob_name = download_from_drive(video_key, url)
    elif is_direct_mp4_url(url):
        blob_name = download_Direct_mp4(video_key, url)
    elif is_blob_exists(url):
        blob_name = url
    else:
        raise Exception("Invalid Video URL")
    
    if is_blob_exists(blob_name=blob_name):
        update_query = """
            UPDATE videos 
            SET is_blob_file = 1, video_url = %s 
            WHERE id = %s
        """
        cursor.execute(update_query, (blob_name, video_key))
        connection.commit()

def post_task(video_key):
    try:
        expected_processing_time = 60
        post_task_connection = mysql.connector.connect(host=db_host, user=db_user, password=db_password, database=db_database)
        post_task_cursor = post_task_connection.cursor(dictionary=True)
        post_task_cursor.execute("SELECT * FROM videos WHERE id = %s", (video_key,))
        video = post_task_cursor.fetchone()
        if not video:
            raise Exception(f'Video with ID: {video_key} not found')

        post_task_cursor.execute("SELECT * FROM servers WHERE id = %s", [video['serverid']])
        server = post_task_cursor.fetchone()
        if not server:
            raise Exception(f'Server with ID: {video["serverid"]} not found')
        
        update_query = "UPDATE videos SET status = 'Processing' WHERE id = %s"
        post_task_cursor.execute(update_query, (video_key,))
        post_task_connection.commit()
        print(f'Posting the task for Video: {video_key}')

        # Extract data from the video record
        userplan_query = "SELECT userplan FROM users WHERE id = %s"
        post_task_cursor.execute(userplan_query, (video['userid'],))
        user = post_task_cursor.fetchone()
        userplan = user['userplan']
        threads = allowed_threads[userplan]
        
        # Create resource files list dynamically
        resource_files = [
            models.ResourceFile(
                    http_url="https://hlsencoder.blob.core.windows.net/scripts/run.py?sp=r&st=2024-11-12T13:51:25Z&se=2025-11-10T21:51:25Z&spr=https&sv=2022-11-02&sr=b&sig=nLtpVN%2FitaWF6hreT3nFI4NW%2FJ0ttlEpvf5P4CbOPTU%3D",
                    file_path=f'run.py'
            ),
            models.ResourceFile(
                    http_url='https://hlsencoder.blob.core.windows.net/scripts/streamify360.pem?sp=r&st=2024-11-12T13:54:24Z&se=2025-11-10T21:54:24Z&spr=https&sv=2022-11-02&sr=b&sig=3T7YjhC7wHRyOLCDweFhQAtd4EMT7zUpbaQMn2Yspxs%3D',
                    file_path=f'streamify360.pem', file_mode= '0400'
            )
        ]
        if is_youtube_url(video['video_url']):
            blob_name = download_from_youtube(video_key=video_key, url=video['video_url'])
        elif is_google_drive_url(video['video_url']):
            blob_name = download_from_drive(video_key=video_key, url=video['video_url'])
        elif is_direct_mp4_url(video['video_url']):
            blob_name = download_Direct_mp4(video_key=video_key, url=video['video_url'])
        elif is_blob_exists(video['video_url']):
            blob_name = video['video_url']
        else:
            raise Exception('Invalid Video URL')
        
        expected_processing_time = blob_size(blob_name) / ( 1024 * 1024 * 50 )
        extension = get_file_extension(blob_name)
        video_filename = f"video-{video_key}{extension}"

        resource_files.append(
            models.ResourceFile(
                http_url=create_sas_url(blob_name=blob_name),
                file_path=video_filename
            )
        )

        command = f"python3 run.py --key {video_key} --domain {server['domain']} --serverip {server['ip']} --max_workers {threads} --video {video_filename}"

        thumbnail_url = video['thumbnail_url']
        if thumbnail_url:
            thumbnail_ext = get_file_extension(thumbnail_url)
            thumbnail_filename = f"thumbnail-{video_key}{thumbnail_ext}"
            resource_files.append(models.ResourceFile(
                http_url=create_sas_url(blob_name=video['thumbnail_url']),
                file_path=thumbnail_filename
            ))
            command += f' --thumbnail {thumbnail_filename}'
            new_manifest_url = f"https://streambox.streamify360.net/streams/{video_key}/{thumbnail_filename}"
            update_query = "UPDATE videos SET thumbnail_url = %s WHERE id = %s"
            post_task_cursor.execute(update_query, (new_manifest_url, video_key))

        logo_url = video['logo_url']
        if logo_url:
            logo_ext = get_file_extension(logo_url)
            logo_filename = f"logo-{video_key}{logo_ext}"
            resource_files.append(models.ResourceFile(
                http_url=create_sas_url(blob_name=video['logo_url']),
                file_path=logo_filename
            ))
            command += f' --logo {logo_filename}'

        subtitle_url = video['subtitle_url']
        if subtitle_url:
            subtitle_ext = get_file_extension(subtitle_url)
            subtitle_filename = f"subtitle-{video_key}{subtitle_ext}"    
            resource_files.append(models.ResourceFile(
                http_url=create_sas_url(blob_name=video['subtitle_url']),
                file_path=subtitle_filename
            ))
            command += f' --subtitle {subtitle_filename}'

        if logo_url or subtitle_url:
            expected_processing_time = expected_processing_time * 10

        # Create the task with dynamically generated file names
        batch_client.task.add(
            job_id='job1',
            task=models.TaskAddParameter(
                id=video_key,
                command_line=command,  # Single string with command line
                resource_files=resource_files,
                constraints=models.TaskConstraints(retention_time=datetime.timedelta(minutes=60))
            )
        )

        expected_processing_time = max(expected_processing_time, 5)

        sleep(expected_processing_time)
        update_query = f"UPDATE videos SET manifest_url = 'https://streambox.streamify360.net/streams/{video_key}/master.m3u8' WHERE id = %s"
        post_task_cursor.execute(update_query, (video_key,))
        update_query = "UPDATE videos SET status = 'live' WHERE id = %s"
        post_task_cursor.execute(update_query, (video_key,))
        post_task_connection.commit()
        log_info(f'Posted task to process Video: {video_key}')

    except Exception as e:
        log_error(f'Failed to post Task for Video: {video_key}\nError: {e}')
        update_query = "UPDATE videos SET status = 'Failed' WHERE id = %s"
        post_task_cursor.execute(update_query, (video_key,))
        post_task_connection.commit()


def delete_from_storage(server, video_id):
    ssh_client = SSHClient()
    ssh_client.set_missing_host_key_policy(AutoAddPolicy)
    pkey = RSAKey.from_private_key_file(os.path.join(script_dir, 'streamify360.pem'))
    ssh_client.connect(server['ip'], port=server['ssh_port'], username=server['username'], pkey=pkey, allow_agent=False, look_for_keys=False)
    ssh_client.exec_command(f'rm -rf /var/www/html/streams/{video_id}')
    ssh_client.close()

def delete_task(video_id):
    try:
        batch_client.task.delete(job_id='job1', task_id=video_id)
    except:
        pass

def delete_video_by_id(video_id):
    delete_task(video_id)
    delete_connection = mysql.connector.connect(host=db_host, user=db_user, password=db_password, database=db_database)
    delete_cursor = delete_connection.cursor(dictionary=True)
    delete_cursor.execute("SELECT * FROM videos WHERE id = %s", (video_id,))
    video = delete_cursor.fetchone()
    delete_cursor.execute("SELECT * FROM servers WHERE id = %s", [video['serverid']])
    server = delete_cursor.fetchone()
    delete_cursor.execute("DELETE FROM videos WHERE id = %s", (video_id,))
    delete_connection.commit()
    delete_from_storage(server, video_id)
    log_info(f'Deleted Video: {video_id}')

def delete_draft_videos(videos):
    delete_expired_draft_videos_connection = mysql.connector.connect(host=db_host, user=db_user, password=db_password, database=db_database)

    delete_expired_draft_videos_cursor = delete_expired_draft_videos_connection.cursor(dictionary=True)
    for video in videos:
        log_info(f"Deleting Expired Draft Video {video['id']}")
        delete_video_query = "DELETE FROM videos WHERE id = %s"
        delete_expired_draft_videos_cursor.execute(delete_video_query, (video['id'],))
        delete_expired_draft_videos_connection.commit()  # Commit the transaction

def delete_expired_videos():
    deleted_video_query = "SELECT id, serverid FROM videos WHERE status = 'Deleted'"
    drafted_video_query = "SELECT id FROM videos WHERE status = 'Draft' AND created_at < NOW() - INTERVAL 1 DAY;"
    delete_connection = mysql.connector.connect(host=db_host, user=db_user, password=db_password, database=db_database)
    delete_cursor = delete_connection.cursor(dictionary=True)
    delete_cursor.execute(deleted_video_query)
    deleted_videos = delete_cursor.fetchall()
    delete_cursor.execute(drafted_video_query)
    drafted_videos = delete_cursor.fetchall()
    delete_draft_videos(drafted_videos)
    for video in deleted_videos:
        delete_video_by_id(video['id'])    
