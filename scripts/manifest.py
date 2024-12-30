from argparse import ArgumentParser
from shutil import copy
parser = ArgumentParser(description="Video Encoding Script with optional subtitle and logo overlay.")
parser.add_argument('--key', required=True, help="Video Key")
parser.add_argument('--domain', required=True, help="Domain")
args = parser.parse_args()
video_key = args.key
domain_name = args.domain
import os

#resolutions = {"360p": "640x360", "480p": "854x480", "720p": "1280x720", "1080p": "1920x1080"}

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

streams_dir = '/var/www/html/streams/'
video_dir = os.path.join(streams_dir, video_key)

resolutions = {
    "360p": [640, 360], 
    "480p": [854, 480], 
    "720p": [1280, 720], 
    "1080p": [1920, 1080]
}

rendered_qualities = os.listdir(video_dir)
highest_quality = '1080p'
for resolution in resolutions:
    if resolution in rendered_qualities:
        highest_quality = resolution
qualities_to_copy = []
for resolution in resolutions:
    if resolution == highest_quality:
        break
    if resolution not in rendered_qualities:
        qualities_to_copy.append(resolution)

highest_quality_path = os.path.join(video_dir, highest_quality)
for quality in qualities_to_copy:
    quality_path = os.path.join(video_dir, quality)
    if not os.path.exists(quality_path):
        os.makedirs(quality_path)
    for filename in os.listdir(highest_quality_path):
        filepath = os.path.join(highest_quality_path, filename)
        if os.path.exists(filepath):
            copy(src=filepath, dst=quality_path)

rendered_qualities = [d for d in os.listdir(video_dir) if os.path.isdir(os.path.join(video_dir, d))]

master_m3u8_data = ''
master_m3u8_data += "#EXTM3U\n"
for quality in rendered_qualities:
    video_setting = f'{resolutions[quality][0]}x{resolutions[quality][1]}'
    bandwidth = bandwidths[quality]
    master_m3u8_data += f"#EXT-X-STREAM-INF:BANDWIDTH={bandwidth},RESOLUTION={video_setting}\n"
    master_m3u8_data += f"https://streambox.{domain_name}/streams/{video_key}/{quality}.m3u8\n"
    #master_m3u8.write(f"{quality}.m3u8\n")
    quality_m3u8_data = ''
    quality_m3u8_data += '#EXTM3U\n'
    quality_m3u8_data += '#EXT-X-VERSION:3\n'
    quality_m3u8_data += '#EXT-X-PLAYLIST-TYPE:VOD\n'
    quality_m3u8_data += '#EXT-X-TARGETDURATION:10\n'

    quality_dir = os.path.join(video_dir, quality)
    quality_segments = os.listdir(quality_dir)
    quality_segments.sort()
    player = '01'
    for segment in quality_segments:
        if segment.endswith('.ts'):
            old_path = os.path.join(quality_dir, segment)
            new_filename = os.path.splitext(segment)[0] + '.html'
            new_path = os.path.join(quality_dir, new_filename)
            os.rename(old_path, new_path)
            segment = new_filename

        quality_m3u8_data += '#EXTINF:10.000000,\n'
        quality_m3u8_data += f'https://player{player}.{domain_name}/streams/{video_key}/{quality}/{segment}\n'

        #quality_m3u8.write(f'{quality}/{segment}\n')
        player = str(int(player) + 1).zfill(2)
        if player == '11':
            player = '01'
    quality_m3u8_data += '#EXT-X-ENDLIST\n'
    quality_m3u8_data += '#EXT-X-TOTALDURATION:60'

    quality_manifest_path = os.path.join(video_dir, f'{quality}.m3u8')    
    with open(quality_manifest_path, 'w') as quality_m3u8:
        quality_m3u8.write(quality_m3u8_data)

master_m3u8_path = os.path.join(video_dir, "master.m3u8")
with open(master_m3u8_path, "w") as master_m3u8:
    master_m3u8.write(master_m3u8_data)