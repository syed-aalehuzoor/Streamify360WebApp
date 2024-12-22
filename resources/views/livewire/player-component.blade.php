<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>{{ $video->name }} - Streamify360</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('storage/brands/streamify360.png') }}">
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script src="https://ssl.p.jwpcdn.com/player/v/8.13.0/jwplayer.js"></script>
    {!! $settings->pop_ads_code !!}
    <script type="text/javascript">
        jwplayer.key = "fsEKyI5f7mNCCzmTSj7cHPQwradnhMGhL8VxSsVPRMs=";
    </script>
</head>
<body style="margin: 0%;">
    <div id="video_player"></div>
    <script type="text/javascript">
        var player = jwplayer("video_player");
        var config = {
            width: "{!! $settings->responsive ? '100%' : ($settings->player_width ? $settings->player_width : '100%') !!}",
            height: "{!! $settings->responsive ? '100%' : ($settings->player_height ? $settings->player_height : '100%') !!}",
            aspectratio: "16:9",
            autostart: {{ $settings->autoplay ? 'true,mute: true' : 'false' }},
            controls: {{ $settings->show_controls ? 'true' : 'false' }},
            primary: "html5",
            abouttext: "Streamify360",
            aboutlink: "https://streamify360.com",
            image: "{{ $video->thumbnail_url }}",
            @if ($settings->show_playback_speed)
                playbackRateControls: [0.5, 1, 1.5, 2],        
            @endif
            sources: [{"file":"{{ $video->manifest_url }}","label":"HD","type":"application/x-mpegURL"}],
            tracks: [{"file":"","kind":"thumbnails"}],
            logo: {
                file: "",
                link: "",
                position: "top-left",
            },
            captions: {
                color: "#FFFFFF", // Default caption color
                fontSize: "14",
                fontFamily: "Trebuchet MS, Sans Serif",
                backgroundColor: "",
            },
            advertising: {
                client: "googima",
                schedule: "{{ $settings->vast_link }}"
            },
            skin: {
                controlbar: {
                    background: "{{ $settings->controlbar_background_color ?? 'rgba(0, 0, 0, 0.7)'}}", // Control bar background color
                    icons: "{{ $settings->controlbar_icons_color ?? 'rgba(255, 255, 255, 0.8)'}}", // Icon color when inactive
                    iconsActive: "{{ $settings->controlbar_icons_active_color ?? '#FFFFFF'}}", // Icon color when active
                    text: "{{ $settings->controlbar_text_color ?? '#FFFFFF'}}" // Control bar text color
                },
                menus: {
                    background: "{{ $settings->menu_background_color ?? '#333333'}}", // Menu background color
                    text: "{{ $settings->menu_text_color ?? 'rgba(255, 255, 255, 0.8)'}}", // Inactive text color in menus
                    textActive: "{{ $settings->menu_text_active_color ?? '#FFFFFF'}}" // Active text color in menus
                },
                timeslider: {
                    progress: "{{ $settings->timeslider_progress_color ?? '#F2F2F2'}}", // Progress bar color
                    rail: "{{ $settings->timeslider_rail_color ?? 'rgba(255, 255, 255, 0.3)'}}" // Rail color of the time slider
                },
                tooltips: {
                    background: "{{ $settings->tooltip_background_color ?? '#000000'}}", // Tooltip background color
                    text: "{{ $settings->tooltip_text_color ?? '#FFFFFF'}}" // Tooltip text color
                }
            }
        };
        player.setup(config);
        player.on('ready', function ()
        {
            jwplayer().addButton('<svg xmlns="http://www.w3.org/2000/svg" class="jw-svg-icon jw-svg-icon-rewind2" viewBox="0 0 240 240" focusable="false"><path d="m 25.993957,57.778 v 125.3 c 0.03604,2.63589 2.164107,4.76396 4.8,4.8 h 62.7 v -19.3 h -48.2 v -96.4 H 160.99396 v 19.3 c 0,5.3 3.6,7.2 8,4.3 l 41.8,-27.9 c 2.93574,-1.480087 4.13843,-5.04363 2.7,-8 -0.57502,-1.174985 -1.52502,-2.124979 -2.7,-2.7 l -41.8,-27.9 c -4.4,-2.9 -8,-1 -8,4.3 v 19.3 H 30.893957 c -2.689569,0.03972 -4.860275,2.210431 -4.9,4.9 z m 163.422413,73.04577 c -3.72072,-6.30626 -10.38421,-10.29683 -17.7,-10.6 -7.31579,0.30317 -13.97928,4.29374 -17.7,10.6 -8.60009,14.23525 -8.60009,32.06475 0,46.3 3.72072,6.30626 10.38421,10.29683 17.7,10.6 7.31579,-0.30317 13.97928,-4.29374 17.7,-10.6 8.60009,-14.23525 8.60009,-32.06475 0,-46.3 z m -17.7,47.2 c -7.8,0 -14.4,-11 -14.4,-24.1 0,-13.1 6.6,-24.1 14.4,-24.1 7.8,0 14.4,11 14.4,24.1 0,13.1 -6.5,24.1 -14.4,24.1 z m -47.77056,9.72863 v -51 l -4.8,4.8 -6.8,-6.8 13,-12.99999 c 3.02543,-3.03598 8.21053,-0.88605 8.2,3.4 v 62.69999 z"></path></svg>', 
            "Forward 10 sec", function ()
            {
                jwplayer().seek(jwplayer().getPosition() + 10)
            },
            "ff11");
            jwplayer().addButton('<svg xmlns="http://www.w3.org/2000/svg" class="jw-svg-icon jw-svg-icon-rewind" viewBox="0 0 240 240" focusable="false"><path d="M113.2,131.078a21.589,21.589,0,0,0-17.7-10.6,21.589,21.589,0,0,0-17.7,10.6,44.769,44.769,0,0,0,0,46.3,21.589,21.589,0,0,0,17.7,10.6,21.589,21.589,0,0,0,17.7-10.6,44.769,44.769,0,0,0,0-46.3Zm-17.7,47.2c-7.8,0-14.4-11-14.4-24.1s6.6-24.1,14.4-24.1,14.4,11,14.4,24.1S103.4,178.278,95.5,178.278Zm-43.4,9.7v-51l-4.8,4.8-6.8-6.8,13-13a4.8,4.8,0,0,1,8.2,3.4v62.7l-9.6-.1Zm162-130.2v125.3a4.867,4.867,0,0,1-4.8,4.8H146.6v-19.3h48.2v-96.4H79.1v19.3c0,5.3-3.6,7.2-8,4.3l-41.8-27.9a6.013,6.013,0,0,1-2.7-8,5.887,5.887,0,0,1,2.7-2.7l41.8-27.9c4.4-2.9,8-1,8,4.3v19.3H209.2A4.974,4.974,0,0,1,214.1,57.778Z"></path></svg>', 
            "Rewind 10 sec", function ()
            {
                var tt = jwplayer().getPosition() - 10;
                if (tt < 0) {
                    tt = 0;
                }
                jwplayer().seek(tt)
            },
            "ff00");
            $("div.jw-icon-rewind").hide()
        });
        // Set default playback speed after setup
        player.on('ready', function() {
            player.setPlaybackRate({{ str_replace('x', '', $settings->playback_speed) }}); // Set the desired default playback speed
            player.setVolume({{ $settings->volume_level }}); // Set the default volume to 50%
            player.addButton(
                "{{ $settings->logo_url }}",
                "Streamify360",
                function () {
                    var win = window.open("{{ $settings->website_url }}", "_blank");
                    win.focus();
                },
                "Streamify360"
            );
            @if ($settings->social_sharing_enabled) // Adjust the condition as per your settings structure
                player.addButton(
                    "{{ asset('storage/brands/fbshare.png') }}",
                    "Share on Facebook",
                    function () {
                        var win = window.open("https://www.facebook.com/sharer/sharer.php?u={{ $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] }}", "_blank");
                        win.focus();
                    },
                    "Facebook"
                );
            @endif

        });
    </script>
    <script src="res/amodal.js" type="text/javascript"></script>
</body>
</html>
