import json
from argparse import ArgumentParser
import os
import subprocess
from concurrent.futures import ThreadPoolExecutor, as_completed
from shutil import rmtree, make_archive

script_dir = os.path.dirname(os.path.abspath(__file__))

# Initialize argument parser
parser = ArgumentParser(description="Video Encoding Script with optional subtitle and logo overlay.")

# Required arguments
parser.add_argument('--key', required=True, help="Video Key")
parser.add_argument('--domain', required=True, help="Domain Name")
parser.add_argument('--video', required=True, help="Input video file")

# Optional arguments
parser.add_argument('--qualities', help="Qualities as a JSON String (e.g., '[\"360p\", \"480p\", \"720p\"]')")
parser.add_argument('--subtitle', help="Subtitle file (optional)")
parser.add_argument('--logo', help="Logo file to overlay (optional)")
parser.add_argument('--chunk_duration', type=int, default=10, help="Chunk duration in seconds")
parser.add_argument('--max_workers', type=int, default=4, help="Max number of threads for concurrent execution")

# Parse the arguments
args = parser.parse_args()

# Retrieve values from the parsed arguments
key = args.key
domain_name = args.domain
input_file = args.video
subtitle_file = args.subtitle
logo_file = args.logo
qualities = args.qualities
chunk_duration = args.chunk_duration
max_workers = args.max_workers

qualities = json.loads(qualities) if qualities and (isinstance(qualities, str) and not json.JSONDecodeError) else ["360p", "480p", "720p"]

domains = [f"player{i:02d}.{domain_name}" for i in range(1, 11)]

resolutions = {
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


video_dir = os.path.join(script_dir, key)
os.makedirs(video_dir, exist_ok=True)

# Step 1: Split video into chunks based on duration
chunks_dir = os.path.join(video_dir, 'chunks')
os.makedirs(chunks_dir, exist_ok=True)

# Function to run FFmpeg command asynchronously
def run_ffmpeg_command(command):
    result = subprocess.run(command, capture_output=True, text=True)
    if result.returncode != 0:
        print(f"FFmpeg error: {result.stderr}")
    return result

# Step 1: Split the video into chunks based on the desired duration
chunk_duration = 10  # Duration of each chunk in seconds
chunks_dir = os.path.join(video_dir, 'chunks')
os.makedirs(chunks_dir, exist_ok=True)

# Split video into chunks using FFmpeg
split_command = [
    "ffmpeg", "-i", input_file,
    "-c", "copy", "-f", "segment",
    "-segment_time", str(chunk_duration),
    os.path.join(chunks_dir, "%04d.mp4")
]
subprocess.run(split_command)

# List all the generated chunks
chunks = sorted([f for f in os.listdir(chunks_dir) if f.endswith(".mp4")])

with ThreadPoolExecutor(max_workers=max_workers) as executor:
    future_to_command = {}

    for chunk in chunks:
        chunk_path = os.path.join(chunks_dir, chunk)

        for quality in qualities:
            video_setting = resolutions[quality]
            bitrate = bitrates[quality]
            quality_dir = os.path.join(video_dir, quality)
            os.makedirs(quality_dir, exist_ok=True)

            chunk_output_path = os.path.join(quality_dir, f"{chunk.split('.')[0]}.ts")
            playlist_path = os.path.join(quality_dir, f"{chunk.split('.')[0]}.m3u8")

            # Create FFmpeg command for each chunk and quality
            if logo_file and os.path.isfile(logo_file) and subtitle_file and os.path.isfile(subtitle_file):
                command = [
                    "ffmpeg", "-y", "-copyts", "-i", chunk_path,
                    "-i", logo_file,
                    "-filter_complex", f"[1:v]scale=w=iw:h=ih[logo];[0:v][logo]overlay=format=auto,ass='{subtitle_file}',scale={video_setting}",
                    "-map", "0", "-b:v", bitrate, "-c:a", "aac", "-b:a", "128k", "-crf", "27", "-preset", "veryfast",
                    "-hls_flags", "single_file",
                    "-hls_time", str(chunk_duration), "-hls_list_size", "0",
                    "-hls_segment_filename", chunk_output_path,
                    playlist_path
                ]
            elif logo_file and os.path.isfile(logo_file):
                command = [
                    "ffmpeg", "-y", "-copyts", "-i", chunk_path,
                    "-i", logo_file,
                    "-filter_complex", f"[1:v]scale=w=iw:h=ih[logo];[0:v][logo]overlay=format=auto,scale={video_setting}",
                    "-map", "0", "-b:v", bitrate, "-c:a", "aac", "-b:a", "128k", "-crf", "27", "-preset", "veryfast",
                    "-hls_time", str(chunk_duration), "-hls_list_size", "0",
                    "-hls_flags", "single_file",
                    "-hls_segment_filename", chunk_output_path,
                    playlist_path
                ]
            elif subtitle_file and os.path.isfile(subtitle_file):
                command = [
                    "ffmpeg", "-y", "-copyts", "-i", chunk_path,
                    "-vf", f"ass='{subtitle_file}',scale={video_setting}",
                    "-map", "0", "-b:v", bitrate, "-c:a", "aac", "-b:a", "128k", "-crf", "27", "-preset", "veryfast",
                    "-hls_time", str(chunk_duration), "-hls_list_size", "0",
                    "-hls_flags", "single_file",
                    "-hls_segment_filename", chunk_output_path,
                    playlist_path
                ]
            else:
                command = [
                    "ffmpeg", "-y", "-copyts", "-i", chunk_path,
                    "-vf", f"scale={video_setting}",
                    "-map", "0", "-b:v", bitrate, "-c:a", "aac", "-b:a", "128k", "-crf", "27", "-preset", "veryfast",
                    "-hls_time", str(chunk_duration), "-hls_list_size", "0",
                    "-hls_flags", "single_file",
                    "-hls_segment_filename", chunk_output_path,
                    playlist_path
                ]

            # Submit the command for parallel execution
            future = executor.submit(run_ffmpeg_command, command)
            future_to_command[future] = command

    # Wait for all futures to complete
    for future in as_completed(future_to_command):
        command = future_to_command[future]
        try:
            future.result()  # Will raise an exception if FFmpeg fails
            print(f"Completed: {' '.join(command)}")
        except Exception as exc:
            print(f"Command failed: {' '.join(command)}. Error: {exc}")

# Step 3: Concatenate the chunked .m3u8 files into a single .m3u8 file for each quality
# Step 3: Create playlist (M3U8) for each quality
for quality in qualities:
    quality_dir = os.path.join(video_dir, quality)
    for file in os.listdir(quality_dir):
        if file.endswith('.ts'):
            ts_path = os.path.join(quality_dir, file)
            html_path = ts_path.replace(".ts", ".html")
            os.rename(ts_path, html_path)
        elif file.endswith('.m3u8'):
            os.remove(os.path.join(quality_dir, file))

    # Create a new m3u8 file that lists all chunks for this quality
    quality_manifest_path = os.path.join(video_dir, f"{quality}.m3u8")
    with open(quality_manifest_path, "w") as m3u8:
        m3u8.write("#EXTM3U\n")
        m3u8.write("#EXT-X-VERSION:3\n")
        m3u8.write("#EXT-X-PLAYLIST-TYPE:VOD\n")
        m3u8.write("#EXT-X-TARGETDURATION:10\n")

        html_sequence = 0
        for html_file in sorted(os.listdir(quality_dir)):
            if html_file.endswith(".html"):
                domain = domains[html_sequence % len(domains)]
                m3u8.write(f"#EXTINF:10.000000,\n")
                m3u8.write(f"https://{domain}/streams/{key}/{quality}/{html_file}\n")
                html_sequence += 1

        total_duration = html_sequence * 10
        m3u8.write("#EXT-X-ENDLIST\n")
        m3u8.write(f"#EXT-X-TOTALDURATION:{total_duration}\n")

# Step 4: Create master playlist
master_m3u8 = os.path.join(video_dir, "master.m3u8")
with open(master_m3u8, "w") as master:
    master.write("#EXTM3U\n")
    for quality in qualities:
        video_setting = resolutions[quality]
        bandwidth = bandwidths[quality]
        master.write(f"#EXT-X-STREAM-INF:BANDWIDTH={bandwidth},RESOLUTION={video_setting}\n")
        master.write(f"https://streambox.{domain_name}/streams/{key}/{quality}.m3u8\n")

if os.path.exists(chunks_dir):
    rmtree(chunks_dir)
make_archive(video_dir, 'zip', video_dir)
if os.path.exists(video_dir):
    rmtree(video_dir)
if os.path.exists(input_file):
    os.remove(input_file)
if subtitle_file and os.path.exists(subtitle_file):
    os.remove(subtitle_file)
if logo_file and os.path.exists(logo_file):
   os.remove(logo_file)