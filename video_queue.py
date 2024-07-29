import json
import os
from paramiko import SSHClient, AutoAddPolicy
from time import sleep
import re

video_settings = {
    "360p": "640x360", 
    "480p": "854x480", 
    "720p": "1280x720", 
    "1080p": "1920x1080"
}
bitrates = {
    "360p": "400k", 
    "480p": "700k", 
    "720p": "1200k", 
    "1080p": "2500k"
}
bandwidths = {
    "360p": "400000", 
    "480p": "700000", 
    "720p": "1200000", 
    "1080p": "2500000"
}

response_file = 'response.json'
queue_file = 'queue.json'

def respond(video, response_file=response_file):

    video_key = video['video_key']
    progress_file_path = f'{video_key}.txt'

    def progress(done, total):
        progress_percentage = (done / total) * 100
        with open(progress_file_path, 'w') as progress_file:
            progress_file.write(f'{progress_percentage:.2f}\n')
        print(f'Progress: {progress_percentage:.2f}%')

    try:
        domain_name = video['server']['domain']
        server_ip = video['server']['ip']
        server_username = video['server']['username']
        server_password = video['server']['password']
        video_name = f'{video['video_key']}.{video['video_ext']}'
        video_local_filepath = f'./uploaded_files/{video_name}'

        client = SSHClient()
        client.set_missing_host_key_policy(AutoAddPolicy())
        client.connect(hostname=server_ip, port=22, username=server_username, password=server_password)
        if os.path.exists(video_local_filepath):
            with client.open_sftp() as sftp:
                sftp.put(localpath=video_local_filepath, remotepath=f'/home/ubuntu/{video_name}', callback=progress)
                sftp.chdir('/var/www/html/streams/')
                sftp.mkdir(str(video_key))
        else:
            print('file not found')
        hls_video_dir = f'/var/www/html/streams/{video_key}/'
        domains = [f"player{i:02d}.{video['server']['domain']}" for i in range(1, 11)]
        
        cmd = f'ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 /home/ubuntu/{video_name}'
        stdin, stdout, stderr = client.exec_command(cmd)
        total_duration = float(stdout.readline())

        for quality in video['video_resolutions']:
            video_setting = video_settings[quality]
            bitrate = bitrates[quality]
            bandwidth = bandwidths[quality]
            quality_dir = f'{hls_video_dir}/{quality}'
            client.exec_command(f'mkdir -p {quality_dir}')
            command = f"ffmpeg -y -i /home/ubuntu/{video_name} -vf scale={video_setting} -map 0 -b:v {bitrate} -c:a aac -b:a 128k -crf 27 -preset veryfast -hls_time 10 -hls_list_size 0 -hls_segment_filename {quality_dir}/%03d.ts {quality_dir}/playlist.m3u8"
            stdin, stdout, stderr = client.exec_command(command=command)

            # Continuously read from stdout and print the output in real-time
            pattern = re.compile(r"time=(\d+:\d+:\d+\.\d+)")  # Regex pattern to capture the time
            while True:
                if stdout.channel.recv_ready():
                    output = stdout.channel.recv(1024).decode()
                    print(output, end='')  # Print without adding extra newline
                if stderr.channel.recv_stderr_ready():
                    line = stderr.readline()
                    if not line:
                        break
                    
                    match = pattern.search(line)
                    if match:
                        current_time = sum(x * float(t) for x, t in zip([3600, 60, 1, 0.01], match.group(1).split(':')))
                        progress = (current_time / total_duration) * 100
                        print(f'Rendering {quality}: {progress:.2f}%')

                    error = stderr.channel.recv_stderr(1024).decode()
                    print(error, end='')  # Print without adding extra newline
                if stdout.channel.exit_status_ready():
                    break  # Command execution is complete
                sleep(1)  # Sleep briefly to avoid busy-waiting
            with client.open_sftp() as sftp:
                ts_files = sftp.listdir_attr(quality_dir)
                for ts_file in ts_files:
                    if ts_file.filename.endswith('ts'):
                        ts_path = f'{quality_dir}/{ts_file.filename}'
                        html_path = ts_path.replace(".ts", ".html")
                        sftp.rename(oldpath=ts_path, newpath=html_path)
                m3u8_file = f'{hls_video_dir}/{quality}.m3u8'
                with sftp.open(filename=m3u8_file, mode='w+') as m3u8:
                    m3u8.write("#EXTM3U\n")
                    m3u8.write("#EXT-X-VERSION:3\n")
                    m3u8.write("#EXT-X-PLAYLIST-TYPE:VOD\n")
                    m3u8.write("#EXT-X-TARGETDURATION:10\n")

                    html_sequence = 0
                    for html_file in sorted(sftp.listdir(quality_dir)):
                        if html_file.endswith(".html"):
                            domain = domains[html_sequence % len(domains)]
                            m3u8.write(f"#EXTINF:10.000000,\n")
                            m3u8.write(f"https://{domain}/streams/{video_key}/{quality}/{html_file}\n")
                            html_sequence += 1

                    total_duration = html_sequence * 10
                    m3u8.write("#EXT-X-ENDLIST\n")
                    m3u8.write(f"#EXT-X-TOTALDURATION:{total_duration}\n")
        master_m3u8 = f'{hls_video_dir}/master.m3u8'
        with client.open_sftp() as sftp:
            with sftp.open(filename=master_m3u8, mode='w+') as master:
                master.write("#EXTM3U\n")
                for i, quality in enumerate(video['video_resolutions']):
                    video_setting = video_settings[quality]
                    bandwidth = bandwidths[quality]
                    master.write(f"#EXT-X-STREAM-INF:BANDWIDTH={bandwidth},RESOLUTION={video_setting}\n")
                    master.write(f"https://play.{domain_name}/streams/{video_key}/{quality}.m3u8\n")
                final_master_manifest_url = f"https://play.{domain_name}/streams/{video_key}/master.m3u8"
        response = {
            'success': True,
            'video': video,
            'master_manifest_url': final_master_manifest_url
        }
        os.remove(video_local_filepath)
    except Exception as e:
        response = {
            'success': False,
            'video': video,
            'master_manifest_url': None
        }
    try:
        if os.path.exists(response_file):
            with open(response_file, 'r') as file:
                responses = json.load(file)
        else:
            responses = []
        
        responses.append(response)
        
        with open(response_file, 'w') as file:
            json.dump(responses, file, indent=4)
    except (IOError, json.JSONDecodeError) as e:
        print(f"Error while responding: {e}")

def dequeue(queue_file=queue_file):
    try:
        if os.path.exists(queue_file):
            with open(queue_file, 'r+') as file:
                queue = json.load(file)
                if queue:
                    item = queue.pop(0)
                    file.seek(0)
                    file.truncate()
                    json.dump(queue, file, indent=4)
                    return item
                else:
                    return None
        else:
            return None
    except (IOError, json.JSONDecodeError) as e:
        print(f"Error while dequeuing: {e}")
        return None

while True:
    video = dequeue()
    if video:
        respond(video)
    sleep(5)
