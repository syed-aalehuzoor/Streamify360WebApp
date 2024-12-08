from pytubefix import YouTube


yt = YouTube('https://www.youtube.com/watch?v=jlOti1Z2IoM', use_oauth=True)
ys = yt.streams.get_highest_resolution()

print(ys.download(output_path='/www/wwwroot/dev.streamify360.com/scripts', filename='test3'))