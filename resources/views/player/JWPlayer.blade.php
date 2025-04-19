
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $name }} - {{ $websiteName }}</title>
    <link rel="icon" type="image/png" href="{{ $logo }}">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4260107204900117"
     crossorigin="anonymous"></script>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-DBNDPWPKHJ"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-DBNDPWPKHJ');
    </script>
    <link rel="stylesheet" href="https://beymtv.com/css/main.css">
    {!! $popAdsCode !!}
    <style type="text/css" media="screen">
    .jw-icon-rewind {
  display: none !important;
}
    html,body{padding:0;margin:0;height:100%}#ykstream-player{width:100%!important;height:100%!important;overflow:hidden;background-color:#000}</style>
    <style type="text/css">body,html{font-family:Tahoma;height:100%;width:100%;padding:0;margin:0;background-color:#000}#video-container{position:fixed;top:0;left:0;width:100%;height:100%;overflow:hidden;z-index:10}#jwplayer{width:100%!important;height:100%!important}.play-button-outer{position:absolute;z-index:999997;width:6em;height:6em;background-color:gray;cursor:pointer}.play-button{margin:0 auto;top:25%;position:relative;width:0;height:0;border-style:solid;border-width:1.5em 0 1.5em 3em;border-color:transparent transparent transparent #000;opacity:.75}.play-button-outer:hover{background-color:#a9a9a9}.play-button-outer:hover .play-button{opacity:1}.swal-text,.swal-title{font-family:Arial,sans-serif}.loading{height:100vh;width:100%;display:flex;align-items:center;justify-content:center;background:hsl(220deg 29% 90% / 50%)}.pl{width:6em;height:6em}.pl_ring{animation:2s linear infinite ringA}.plring--a{stroke:#f42f25}.plring--b{animation-name:ringB;stroke:#f49725}.plring--c{animation-name:ringC;stroke:#255ff4}.pl_ring--d{animation-name:ringD;stroke:#f42582}@keyframes ringA{4%,from{stroke-dasharray:0 660;stroke-width:20;stroke-dashoffset:-330}12%{stroke-dasharray:60 600;stroke-width:30;stroke-dashoffset:-335}32%{stroke-dasharray:60 600;stroke-width:30;stroke-dashoffset:-595}40%,54%{stroke-dasharray:0 660;stroke-width:20;stroke-dashoffset:-660}62%{stroke-dasharray:60 600;stroke-width:30;stroke-dashoffset:-665}82%{stroke-dasharray:60 600;stroke-width:30;stroke-dashoffset:-925}90%,to{stroke-dasharray:0 660;stroke-width:20;stroke-dashoffset:-990}}@keyframes ringB{12%,from{stroke-dasharray:0 220;stroke-width:20;stroke-dashoffset:-110}20%{stroke-dasharray:20 200;stroke-width:30;stroke-dashoffset:-115}40%{stroke-dasharray:20 200;stroke-width:30;stroke-dashoffset:-195}48%,62%{stroke-dasharray:0 220;stroke-width:20;stroke-dashoffset:-220}70%{stroke-dasharray:20 200;stroke-width:30;stroke-dashoffset:-225}90%{stroke-dasharray:20 200;stroke-width:30;stroke-dashoffset:-305}98%,to{stroke-dasharray:0 220;stroke-width:20;stroke-dashoffset:-330}}@keyframes ringC{from{stroke-dasharray:0 440;stroke-width:20;stroke-dashoffset:0}8%{stroke-dasharray:40 400;stroke-width:30;stroke-dashoffset:-5}28%{stroke-dasharray:40 400;stroke-width:30;stroke-dashoffset:-175}36%,58%{stroke-dasharray:0 440;stroke-width:20;stroke-dashoffset:-220}66%{stroke-dasharray:40 400;stroke-width:30;stroke-dashoffset:-225}86%{stroke-dasharray:40 400;stroke-width:30;stroke-dashoffset:-395}94%,to{stroke-dasharray:0 440;stroke-width:20;stroke-dashoffset:-440}}@keyframes ringD{8%,from{stroke-dasharray:0 440;stroke-width:20;stroke-dashoffset:0}16%{stroke-dasharray:40 400;stroke-width:30;stroke-dashoffset:-5}36%{stroke-dasharray:40 400;stroke-width:30;stroke-dashoffset:-175}44%,50%{stroke-dasharray:0 440;stroke-width:20;stroke-dashoffset:-220}58%{stroke-dasharray:40 400;stroke-width:30;stroke-dashoffset:-225}78%{stroke-dasharray:40 400;stroke-width:30;stroke-dashoffset:-395}86%,to{stroke-dasharray:0 440;stroke-width:20;stroke-dashoffset:-440}}</style>
</head>
<body>
<div id="video-container">
    <div id="ykstream-player"></div>
</div>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script src="https://ssl.p.jwpcdn.com/player/v/8.36.2/jwplayer.js"></script>
<script type="text/javascript">jwplayer.key="cLGMn8T20tGvW+0eXPhq4NNmLB57TrscPjd1IyJF84o=";</script>
<script type="text/javascript">
    var player = jwplayer("ykstream-player");
    var config = {
        aspectratio: "16:9",
        //width: "100%",
        //height: "100%",
        autostart: false,
        preload: true,
        controls: @json($controls),
        primary: "html5",
        abouttext: "Streamify360",
        aboutlink: "",
        image: @json(asset('storage/'.$poster)),
        sources: [{"file": {!! json_encode($src, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},"label":"HD","type":"video/mp4"}],
        tracks: [{"file":"","kind":"thumbnails"}],
        logo: {
            file: "",
            link: @json($logo),
            position: "top-left",
        },
        captions: {
            color: "#FFFFFF",
            fontSize: "14",
            fontFamily: "Trebuchet MS, Sans Serif",
            backgroundColor: "",
        },
        skin: {
            name: "default"
        },
        advertising: {
            client: "googima",
            schedule: @json($vastlink),
        }
    };

    player.setup(config);
    player.addButton(
        @json($logo),
        "Streamify360",
        function () {
            var win = window.open(@json($websiteURL),"_blank");
            win.focus();
        },
        "Streamify360"
    );
    player.on('ready', function(){
        jwplayer().addButton(
            '<svg xmlns="http://www.w3.org/2000/svg" class="jw-svg-icon jw-svg-icon-rewind2" viewBox="0 0 240 240" focusable="false"><path d="m 25.993957,57.778 v 125.3 c 0.03604,2.63589 2.164107,4.76396 4.8,4.8 h 62.7 v -19.3 h -48.2 v -96.4 H 160.99396 v 19.3 c 0,5.3 3.6,7.2 8,4.3 l 41.8,-27.9 c 2.93574,-1.480087 4.13843,-5.04363 2.7,-8 -0.57502,-1.174985 -1.52502,-2.124979 -2.7,-2.7 l -41.8,-27.9 c -4.4,-2.9 -8,-1 -8,4.3 v 19.3 H 30.893957 c -2.689569,0.03972 -4.860275,2.210431 -4.9,4.9 z m 163.422413,73.04577 c -3.72072,-6.30626 -10.38421,-10.29683 -17.7,-10.6 -7.31579,0.30317 -13.97928,4.29374 -17.7,10.6 -8.60009,14.23525 -8.60009,32.06475 0,46.3 3.72072,6.30626 10.38421,10.29683 17.7,10.6 7.31579,-0.30317 13.97928,-4.29374 17.7,-10.6 8.60009,-14.23525 8.60009,-32.06475 0,-46.3 z m -17.7,47.2 c -7.8,0 -14.4,-11 -14.4,-24.1 0,-13.1 6.6,-24.1 14.4,-24.1 7.8,0 14.4,11 14.4,24.1 0,13.1 -6.5,24.1 -14.4,24.1 z m -47.77056,9.72863 v -51 l -4.8,4.8 -6.8,-6.8 13,-12.99999 c 3.02543,-3.03598 8.21053,-0.88605 8.2,3.4 v 62.69999 z"></path></svg>',
            "Forward 10 sec", 
            function() {
                jwplayer().seek( jwplayer().getPosition()+10 );
            },
            "ff11"
        );
        jwplayer().addButton(
            '<svg xmlns="http://www.w3.org/2000/svg" class="jw-svg-icon jw-svg-icon-rewind" viewBox="0 0 240 240" focusable="false"><path d="M113.2,131.078a21.589,21.589,0,0,0-17.7-10.6,21.589,21.589,0,0,0-17.7,10.6,44.769,44.769,0,0,0,0,46.3,21.589,21.589,0,0,0,17.7,10.6,21.589,21.589,0,0,0,17.7-10.6,44.769,44.769,0,0,0,0-46.3Zm-17.7,47.2c-7.8,0-14.4-11-14.4-24.1s6.6-24.1,14.4-24.1,14.4,11,14.4,24.1S103.4,178.278,95.5,178.278Zm-43.4,9.7v-51l-4.8,4.8-6.8-6.8,13-13a4.8,4.8,0,0,1,8.2,3.4v62.7l-9.6-.1Zm162-130.2v125.3a4.867,4.867,0,0,1-4.8,4.8H146.6v-19.3h48.2v-96.4H79.1v19.3c0,5.3-3.6,7.2-8,4.3l-41.8-27.9a6.013,6.013,0,0,1-2.7-8,5.887,5.887,0,0,1,2.7-2.7l41.8-27.9c4.4-2.9,8-1,8,4.3v19.3H209.2A4.974,4.974,0,0,1,214.1,57.778Z"></path></svg>',
            "Rewind 10 sec", 
            function() {
                var tt = jwplayer().getPosition()-10;
                if(tt<0)tt=0;
                jwplayer().seek( tt );
            },
            "ff00"
        );
    });

    player.on("audioTracks", function (event) {
        var tracks = player.getAudioTracks();
        if (tracks.length < 2) return;
        $('.jw-settings-topbar-buttons').mousedown(function () {
            $('#jw-settings-submenu-audioTracks').removeClass('jw-settings-submenu-active');
            $('.jw-submenu-audioTracks').attr('aria-expanded', 'false');
        });
        player.addButton("/images/dualy.svg", "Audio Track", function () {
            $('.jw-controls').toggleClass('jw-settings-open');
            $('.jw-settings-captions, .jw-settings-playbackRates').attr('aria-checked', 'false');
            if ($('.jw-controls').hasClass('jw-settings-open')) {
                $('.jw-submenu-audioTracks').attr('aria-checked', 'true');
                $('.jw-submenu-audioTracks').attr('aria-expanded', 'true');
                $('.jw-settings-submenu-quality').removeClass('jw-settings-submenu-active');
                $('.jw-settings-submenu-audioTracks').addClass('jw-settings-submenu-active');
            } else {
                $('.jw-submenu-audioTracks').attr('aria-checked', 'false');
                $('.jw-submenu-audioTracks').attr('aria-expanded', 'false');
                $('.jw-settings-submenu-audioTracks').removeClass('jw-settings-submenu-active');
            }
        }, "dualSound");
        player.on("audioTrackChanged", function (event) {
            localStorage.setItem('default_audio', event.tracks[event.currentTrack].name);
        });
        if (localStorage.getItem('default_audio')) {
            setTimeout("audio_set(localStorage.getItem('default_audio'));", 300);
        }
    });

    var current_audio;
    function audio_set(audio_name) {
        var tracks = player.getAudioTracks();
        if (tracks.length > 1) {
            for (i = 0; i < tracks.length; i++) {
                if (tracks[i].name == audio_name) {
                    if (i == current_audio) {
                        return;
                    }
                    current_audio = i;
                    player.setCurrentAudioTrack(i);
                }
            }
        }
    }
    player.on("setupError",function()
    {
        swal("Server Error!","Please contact us to fix it asap. Thank you!","error")
    }
    );
    player.on("error",function()
        {
        swal("Server Error!","Please contact us to fix it asap. Thank you!","error")
    }
    );
</script>
    
</body>
</html>
