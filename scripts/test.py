#!/usr/bin/env python3
import subprocess
import argparse
import sys
import os

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
    Constructs an FFmpeg command to transcode the input file into multiple HLS streams.
    Each output stream is scaled and encoded using GPU acceleration (h264_nvenc) with the specified bitrate.
    The HLS variant playlists are stored in the main output folder, while the segments are stored in a subfolder per quality.
    """
    # Define parameters for each quality level with new bitrates.
    params = {
        1080: {"height": 1080, "video_bitrate": "2000k", "audio_bitrate": "192k"},
        720:  {"height": 720,  "video_bitrate": "1200k", "audio_bitrate": "128k"},
        480:  {"height": 480,  "video_bitrate": "800k",  "audio_bitrate": "96k"}
    }
    
    num_streams = len(qualities)
    # Build the filter_complex string to split and scale the video stream.
    filter_complex = f"[0:v]split={num_streams}"
    for i in range(num_streams):
        filter_complex += f"[v{i+1}]"
    filter_complex += ";"
    
    for i, q in enumerate(qualities):
        # Scale video to the target height (width is calculated to preserve aspect ratio)
        filter_complex += f"[v{i+1}]scale=-2:{q}[v{i+1}out];"
    filter_complex = filter_complex.rstrip(";")
    
    # Build the base ffmpeg command.
    cmd = ["ffmpeg", "-y", "-i", input_file, "-filter_complex", filter_complex]
    
    # Append output options for each quality stream.
    for i, q in enumerate(qualities):
        p = params[q]
        # The variant playlist will be stored in the main output folder.
        output_playlist = os.path.join(output_dir, f"{q}p.m3u8")
        # The segments will be stored in a subfolder named "{quality}p".
        segment_folder = os.path.join(output_dir, f"{q}p")
        segment_pattern = os.path.join(segment_folder, f"{q}p_%03d.html")
        cmd.extend([
            "-map", f"[v{i+1}out]", "-map", "0:a",
            "-c:v", "h264_nvenc",
            "-preset", "fast",
            "-b:v", p["video_bitrate"],
            "-c:a", "aac",
            "-b:a", p["audio_bitrate"],
            "-hls_time", "4",
            "-hls_playlist_type", "vod",
            "-hls_segment_filename", segment_pattern,
            output_playlist
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
            f"{q}p.m3u8\n"
        )
    master_path = os.path.join(output_dir, "master.m3u8")
    with open(master_path, "w") as f:
        f.write(master_manifest)
    print(f"Master manifest generated at: {master_path}")

def main():
    parser = argparse.ArgumentParser(
        description="Transcode MP4 to HLS with multiple quality levels using GPU acceleration and store output in /var/www/html/streams/<video_key>"
    )
    parser.add_argument("input", help="Input MP4 file")
    parser.add_argument("--video-key", help="Video key used for making output folder under /var/www/html/streams/")
    args = parser.parse_args()
    
    # Determine video key: either from command-line or prompt.
    if args.video_key:
        video_key = args.video_key
    else:
        video_key = input("Enter video key: ").strip()
        if not video_key:
            print("Video key cannot be empty.")
            sys.exit(1)
    
    # Build the main output directory based on the video key.
    output_dir = os.path.join("/var/www/html/streams", video_key)
    if not os.path.exists(output_dir):
        os.makedirs(output_dir)
    
    input_file = args.input
    height = get_video_height(input_file)
    print(f"Input video height: {height}")
    
    # Determine quality levels based on the input video height.
    if height >= 1080:
        qualities = [1080, 720, 480]
    elif height >= 720:
        qualities = [720, 480]
    else:
        qualities = [480]
    
    print(f"Transcoding to qualities: {qualities}")
    print(f"Main output will be stored in: {output_dir}")
    
    # Create subdirectories for quality segments.
    for q in qualities:
        quality_folder = os.path.join(output_dir, f"{q}p")
        if not os.path.exists(quality_folder):
            os.makedirs(quality_folder)
    
    ffmpeg_cmd = build_ffmpeg_command(input_file, qualities, output_dir)
    
    print("Running ffmpeg command:")
    print(" ".join(ffmpeg_cmd))
    
    # Run the ffmpeg command.
    subprocess.run(ffmpeg_cmd)
    
    # Generate the master manifest referencing all variant playlists.
    generate_master_manifest(output_dir, qualities)

if __name__ == "__main__":
    main()
