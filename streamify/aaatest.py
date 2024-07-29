command = [
            "ffmpeg", "-y", "-i", f"/home/ubuntu/video_name",
            "-vf", f"scale=video_setting",
            "-map", "0", "-b:v", '{bitrate}', "-c:a", "aac", "-b:a", "128k", "-crf", "27", "-preset", "veryfast",
            "-hls_time", "10", "-hls_list_size", "0",
            "-hls_segment_filename", "quality_dir/%03d.ts",
            "playlist.m3u8"
        ]
command = ' '.join(command)
print(command)