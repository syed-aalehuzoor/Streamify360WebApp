import json
from argparse import ArgumentParser
import os
import sys
import subprocess
from concurrent.futures import ThreadPoolExecutor
from shutil import rmtree
from logging import basicConfig, info as log_info, INFO

script_dir = os.path.dirname(os.path.abspath(__file__))
basicConfig(filename=os.path.join(script_dir, 'video_process.logs'), level=INFO)

parser = ArgumentParser(description="Video Encoding Script with optional subtitle and logo overlay.")

arguments = [
    {'name': '--key', 'kwargs': {'required': True, 'help': "Video Key"}},
    {'name': '--domain', 'kwargs': {'required': True, 'help': "Domain Name"}},
    {'name': '--serverip', 'kwargs': {'required': True, 'help': "Server IP"}},
    {'name': '--video', 'kwargs': {'required': True, 'help': "Input video file"}},
    # Optional arguments
    {'name': '--subtitle', 'kwargs': {'help': "Subtitle file (optional)"}},
    {'name': '--logo', 'kwargs': {'help': "Logo file to overlay (optional)"}},
    {'name': '--chunk_duration', 'kwargs': {'type': int, 'default': 10, 'help': "Chunk duration in seconds"}},
    {'name': '--max_workers', 'kwargs': {'type': int, 'default': 4, 'help': "Max number of threads for concurrent execution"}},
]
for arg in arguments:
    parser.add_argument(arg['name'], **arg['kwargs'])
args = parser.parse_args()
key = args.key
domain_name = args.domain
server_ip = args.serverip
input_file = args.video
input_file = os.path.join(script_dir, input_file)
subtitle_file = args.subtitle
logo_file = args.logo
chunk_duration = args.chunk_duration
max_workers = args.max_workers

resolutions = {
    "360p": [640, 360], 
    "480p": [854, 480], 
    "720p": [1280, 720], 
    "1080p": [1920, 1080]
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

def run_command(command):
    command_string = " ".join(command) if isinstance(command, list) else command
    log_info(f'Video ID: {key}, Running Command: {command_string}')
    result = subprocess.run(command, check=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
    log_info(f'Video ID: {key}, Completed Command: {command_string}')

def get_resolution_key(input_file):
    result = subprocess.run(["ffprobe", "-v", "error", "-select_streams", "v:0", "-show_entries", "stream=width,height", "-of", "json", input_file], stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
    video_width, video_height = (json.loads(result.stdout).get('streams', [{}])[0].get('width', 0), json.loads(result.stdout).get('streams', [{}])[0].get('height', 0))
    return next(k for k, (w, h) in resolutions.items() if video_width <= w and video_height <= h)

try:

    if logo_file or subtitle_file:
        log_info('\n------------------------------')
        log_info(f'\nHardcoding Logo/Subtitle on Video.')
        log_info('\n------------------------------')
        hardcoded_input_file = os.path.join(script_dir, f'HardCoded-{key}.mp4')
        if logo_file and os.path.isfile(logo_file) and subtitle_file and os.path.isfile(subtitle_file):
            command = f'ffmpeg -threads {max_workers} -i "{input_file}" -i "{logo_file}" -filter_complex "[1:v][0:v]scale2ref=h=ow/mdar:w=iw[logo][video];[video][logo]overlay=format=auto,ass={subtitle_file}" -c:v libx264 -preset fast -c:a aac -b:a 128k -movflags +faststart "{hardcoded_input_file}"'
        elif logo_file and os.path.isfile(logo_file):
            command = f'ffmpeg -threads {max_workers} -i "{input_file}" -i "{logo_file}" -filter_complex "[1:v][0:v]scale2ref=h=ow/mdar:w=iw[logo][video];[video][logo]overlay=format=auto" -c:v libx264 -preset fast -c:a aac -b:a 128k -movflags +faststart "{hardcoded_input_file}"'
        elif subtitle_file and os.path.isfile(subtitle_file):
            command = f'ffmpeg -threads {max_workers} -i "{input_file}" -filter_complex "ass={subtitle_file}" -c:v libx264 -preset fast -c:a aac -b:a 128k -movflags +faststart "{hardcoded_input_file}"'
        run_command(command=command)
        if input_file and os.path.exists(input_file):
            os.remove(input_file)
        input_file = hardcoded_input_file

    highest_quality = get_resolution_key(input_file=input_file)
    log_info(
        '\n------------------------------'
        f'\nCopying the Input Quality({highest_quality}) to HLS Format.'
        '\n------------------------------'
    )
    video_dir = os.path.join(script_dir, key)
    os.makedirs(video_dir, exist_ok=True)
    bitrate = bitrates[highest_quality]
    quality_dir = os.path.join(video_dir, highest_quality)
    os.makedirs(quality_dir, exist_ok=True)

    command = ["ffmpeg", "-y", "-i", input_file, "-b:v", bitrate, "-c", "copy", "-f", "segment", "-segment_time", str(chunk_duration), os.path.join(quality_dir, "%04d.ts")]
    run_command(command=command)

    key_path = os.path.join(script_dir, 'streamify360.pem')
    remote_path=f'/var/www/html/streams/'

    command = ["scp", "-i", key_path,"-o", "StrictHostKeyChecking=no", "-r", video_dir, f"ubuntu@{server_ip}:{remote_path}"]
    run_command(command=command)
    
    command = ["ssh", "-i", key_path, f"ubuntu@{server_ip}", f'python3 /home/ubuntu/manifest.py --key {key} --domain {domain_name}']
    run_command(command=command)

    remote_video_dir = os.path.join(remote_path, key)
    command = ["ssh", "-i", key_path, f"ubuntu@{server_ip}", f'sudo chmod -R 755 {remote_video_dir}']
    
    run_command(command=command)
    
    log_info(
        '\n------------------------------'
        f'\nEncoding to all lower Quality than input quality({highest_quality}).'
        '\n------------------------------'
    )

    with ThreadPoolExecutor(max_workers=4) as worker:
        for quality in resolutions:
            resolution = resolutions[quality]
            bitrate = bitrates[quality]
            quality_dir = os.path.join(video_dir, quality)
            os.makedirs(quality_dir, exist_ok=True)
            command = ["ffmpeg", '-y', "-i", input_file, "-c:v", "libx264", "-vf", f'scale={resolution[0]}:{resolution[1]}', "-b:v", bitrate, "-f", "segment", "-segment_time", str(chunk_duration), os.path.join(quality_dir, "%04d.ts")]
            worker.submit(run_command, command)

            if quality == highest_quality:
                break
        worker.shutdown(wait=True)
        
    for quality in os.listdir(video_dir):
        if quality == highest_quality:
            break
        quality_dir = os.path.join(video_dir, quality)
        command = ["scp", "-i", key_path, "-r", quality_dir, f"ubuntu@{server_ip}:{remote_video_dir}"]
        run_command(command=command)

    command = ["ssh", "-i", key_path, f"ubuntu@{server_ip}", f'sudo chmod -R 755 {remote_video_dir}']
    run_command(command=command)

    if os.path.exists(video_dir):
        rmtree(video_dir)
    if os.path.exists(input_file):
        os.remove(input_file)
    if subtitle_file and os.path.exists(subtitle_file):
        os.remove(subtitle_file)
    if logo_file and os.path.exists(logo_file):
        os.remove(logo_file)

except subprocess.CalledProcessError as e:
    log_info(
        f"\nVideo Processing Failed:\n"
        f"-------------------------\n"
        f"Error Output : {e.stderr}\n"
        f"Unexpected Error: {e}\n"
    )
    sys.exit(1)