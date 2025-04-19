import json
from argparse import ArgumentParser
import os
import sys
import subprocess
from concurrent.futures import ThreadPoolExecutor
from shutil import rmtree, copy as copy_file
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
    {'name': '--thumbnail', 'kwargs': {'help': "Thumbnail file (optional)"}},
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
thumbnail_file = args.thumbnail
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

def get_video_height(input_file):
    """
    Uses ffprobe to get the height of the first video stream.
    """
    cmd = [
        "ffprobe", "-v", "error", "-select_streams", "v:0",
        "-show_entries", "stream=height", "-of", "csv=p=0", input_file
    ]
    try:
        result = subprocess.run(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE,
                                universal_newlines=True, check=True)
        height = int(result.stdout.strip())
        return height
    except Exception as e:
        print(f"Error getting video height: {e}")
        sys.exit(1)

def build_ffmpeg_command(input_file, qualities, output_dir):
    """
    Constructs an FFmpeg command with multi-threading enabled to transcode the input file into multiple HLS streams.
    Each quality branch scales and encodes the video while using additional threads to speed up processing.
    """
    import os

    # Encoding parameters for each quality level.
    params = {
        1080: {"height": 1080, "video_bitrate": "2000k", "audio_bitrate": "192k"},
        720:  {"height": 720,  "video_bitrate": "1200k", "audio_bitrate": "128k"},
        480:  {"height": 480,  "video_bitrate": "800k",  "audio_bitrate": "96k"}
    }
    
    num_streams = len(qualities)
    
    # Build the filter_complex graph:
    filter_complex = "[0:v]split={}".format(num_streams)
    for i in range(num_streams):
        filter_complex += "[v{}]".format(i+1)
    filter_complex += ";"
    
    for i, q in enumerate(qualities):
        height = q  # assuming q is the desired height for that quality level
        filter_complex += "[v{}]scale=-2:{}[v{}_scaled];".format(i+1, height, i+1)
    filter_complex = filter_complex.rstrip(";")
    
    # Adding multi-threading options: '-threads' for codec and '-filter_threads' for the filter graph.
    cmd = [
        "ffmpeg", "-y",
        "-i", input_file,
        "-filter_complex", filter_complex
    ]
    
    # For each quality level, map the corresponding scaled video branch and the original audio,
    # then encode using libx264 with multi-threading.
    for i, q in enumerate(qualities):
        p = params[q]
        seg_folder = os.path.join(output_dir, f"{q}p")
        os.makedirs(seg_folder, exist_ok=True)
        playlist = os.path.join(seg_folder, f"{q}p.m3u8")
        seg_pattern = os.path.join(seg_folder, f"%03d.html")
        cmd.extend([
            "-map", "[v{}_scaled]".format(i+1),
            "-map", "0:a",
            "-c:v", "h264_nvenc",
            "-preset", "fast",
            "-b:v", p["video_bitrate"],
            "-c:a", "copy",
            "-hls_time", "10",
            "-hls_playlist_type", "vod",
            "-hls_segment_filename", seg_pattern,
            playlist
        ])
    
    return cmd



def generate_master_manifest(output_dir, qualities):
    """
    Generates a master M3U8 manifest in the main output folder that references the variant playlists.
    """
    # Map quality to bandwidth (in bits per second) and resolution.
    # These are approximate values.
    quality_info = {
        1080: {"bandwidth": 2000000, "resolution": "1920x1080"},
        720:  {"bandwidth": 1200000, "resolution": "1280x720"},
        480:  {"bandwidth": 800000,  "resolution": "854x480"}
    }
    master_manifest = "#EXTM3U\n#EXT-X-VERSION:3\n"
    for q in qualities:
        info = quality_info[q]
        master_manifest += (
            f"#EXT-X-STREAM-INF:BANDWIDTH={info['bandwidth']},RESOLUTION={info['resolution']}\n"
            f"{q}p/{q}p.m3u8\n"
        )
    master_path = os.path.join(output_dir, "master.m3u8")
    with open(master_path, "w") as f:
        f.write(master_manifest)
    print(f"Master manifest generated at: {master_path}")

def run_command(command):
    command_string = " ".join(command) if isinstance(command, list) else command
    log_info(f'Video ID: {key}, Running Command: {command_string}')
    result = subprocess.run(command, check=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
    log_info(f'Video ID: {key}, Completed Command: {command_string}')

def get_resolution_key(input_file):
    result = subprocess.run(["ffprobe", "-v", "error", "-select_streams", "v:0", "-show_entries", "stream=width,height", "-of", "json", input_file], stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
    video_width, video_height = (json.loads(result.stdout).get('streams', [{}])[0].get('width', 0), json.loads(result.stdout).get('streams', [{}])[0].get('height', 0))
    return next(k for k, (w, h) in resolutions.items() if video_width <= w and video_height <= h)

def get_video_duration(input_file):
    result = subprocess.run(
        ["ffprobe", "-v", "error", "-show_entries", "format=duration", "-of", "json", input_file],
        stdout=subprocess.PIPE,
        stderr=subprocess.PIPE,
        text=True
    )
    duration = json.loads(result.stdout).get('format', {}).get('duration', 0)
    return float(duration)

def get_video_width(input_file):
    """
    Uses ffprobe to get the width (horizontal resolution) of the input video.
    """
    cmd = [
        "ffprobe",
        "-v", "error",
        "-select_streams", "v:0",
        "-show_entries", "stream=width",
        "-of", "csv=p=0",
        input_file
    ]
    try:
        result = subprocess.run(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True, check=True)
        width = int(result.stdout.strip())
        return width
    except Exception as e:
        print(f"Error detecting video height: {e}")
        exit(1)

try:
    if logo_file or subtitle_file:
        log_info('\n------------------------------')
        log_info(f'\nHardcoding Logo/Subtitle on Video.')
        log_info('\n------------------------------')
        hardcoded_input_file = os.path.join(script_dir, f'HardCoded-{key}.mp4')
        if logo_file and os.path.isfile(logo_file) and subtitle_file and os.path.isfile(subtitle_file):
            command = [
                "ffmpeg",
                "-threads", str(max_workers),
                "-i", input_file,
                "-i", logo_file,
                "-filter_complex", f"[1:v][0:v]scale2ref=h=ow/mdar:w=iw[logo][video];[video][logo]overlay=format=auto,ass={subtitle_file}",
                "-c:v", "libx264",
                "-preset", "fast",
                "-c:a", "aac",
                "-b:a", "128k",
                "-movflags", "+faststart",
                hardcoded_input_file
            ]
        elif logo_file and os.path.isfile(logo_file):
            command = [
                "ffmpeg",
                "-threads", str(max_workers),
                "-i", input_file,
                "-i", logo_file,
                "-filter_complex", "[1:v][0:v]scale2ref=h=ow/mdar:w=iw[logo][video];[video][logo]overlay=format=auto",
                "-c:v", "libx264",
                "-preset", "fast",
                "-c:a", "aac",
                "-b:a", "128k",
                "-movflags", "+faststart",
                hardcoded_input_file
            ]
        elif subtitle_file and os.path.isfile(subtitle_file):
            command = [
                "ffmpeg",
                "-threads", str(max_workers),
                "-i", input_file,
                "-filter_complex", f"ass={subtitle_file}",
                "-c:v", "libx264",
                "-preset", "fast",
                "-c:a", "aac",
                "-b:a", "128k",
                "-movflags", "+faststart",
                hardcoded_input_file
            ]

        run_command(command=command)
        if input_file and os.path.exists(input_file):
            os.remove(input_file)
        input_file = hardcoded_input_file

    video_dir = os.path.join(script_dir, key)
    os.makedirs(video_dir, exist_ok=True)

    key_path = os.path.join(script_dir, 'streamify360.pem')
    remote_path=f'/var/www/html/streams/'

    mkdir_command = [
        "ssh",
        "-i", key_path,
        "-o", "StrictHostKeyChecking=no",
        f"ubuntu@{server_ip}",
        f"mkdir -p /var/www/html/streams/{key}"
    ]
    run_command(command=mkdir_command)

    remote_video_dir = os.path.join(remote_path, key)
    command = ["ssh", "-i", key_path, f"ubuntu@{server_ip}", f'sudo chmod -R 755 {remote_video_dir}']    
    run_command(command=command)

    height = get_video_height(input_file)

    if height >= 1080:
        qualities = [1080, 720, 480]
    elif height >= 720:
        qualities = [720, 480]
    else:
        qualities = [480]

    for q in qualities:
        quality_folder = os.path.join(video_dir, f"{q}p")
        if not os.path.exists(quality_folder):
            os.makedirs(quality_folder)
    
    ffmpeg_cmd = build_ffmpeg_command(input_file, qualities, video_dir)
    
    print("Running ffmpeg command:")
    print(" ".join(ffmpeg_cmd))
    
    # Run the ffmpeg command.
    subprocess.run(ffmpeg_cmd)
    generate_master_manifest(video_dir, qualities)

    files = os.listdir(video_dir)
    remote_path=f'/var/www/html/streams/{key}/'
    for file in files:
        local_file_path = os.path.join(video_dir, file)
        command = ["scp", "-i", key_path,"-o", "StrictHostKeyChecking=no", "-r", local_file_path, f"ubuntu@{server_ip}:{remote_path}"]
        run_command(command=command)

except subprocess.CalledProcessError as e:
    log_info(
        f"\nVideo Processing Failed:\n"
        f"-------------------------\n"
        f"Error Output : {e.stderr}\n"
        f"Unexpected Error: {e}\n"
    )
    sys.exit(1)