@extends('layouts.player')

@push('meta')
<title>{{ $name }} - {{ $websiteName }}</title>
<link rel="icon" type="image/png" href="{{ $logo }}">
@endpush

@section('content')
    <div id="video_player"></div>
@if ( $popAdsCode )
{!! $popAdsCode !!}
@endif

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script src="https://ssl.p.jwpcdn.com/player/v/8.13.0/jwplayer.js"></script>
<script type="text/javascript">jwplayer.key="cLGMn8T20tGvW+0eXPhq4NNmLB57TrscPjd1IyJF84o=";</script>
<script type="text/javascript">
    var player = jwplayer("video_player");
    var config = {
        width: "100%",
        height: "100%",
        aspectratio: "16:9",
        autostart: false,
        controls: @json($controls),
        primary: "html5",
        abouttext: "Streamify360",
        aboutlink: "",
        image: "https://destro.tvlogy.to/_l-CX4tkLnNAm0GNNVz6FOBkAq2n5KcW1Nug3xcr8MANg0XvP-iTCZJiNvNaGxveIkvbu3eTrzt077Osaavw/Sf6dPtWkIMYFV6rD77Bz5sBsxsewYI_JcYCU4e-LmJs/preview.jpg",
        sources: [{"file": @json($src),"label":"HD","type":"hls"}],
        tracks: [{"file":"","kind":"thumbnails"}],
        logo: {
            file: "",
            link: "",
            position: "top-left",
        },
        captions: {
            color: "#FFFFFF",
            fontSize: "14",
            fontFamily: "Trebuchet MS, Sans Serif",
            backgroundColor: "",
        },
        advertising: {
            client: "googima",
            schedule: ""
        }
    };
    player.setup(config);
</script>
<script src="https://vidtower.pro/res/amodal.js" type="text/javascript"></script>
<script>
    let playerState = {
        srcURL: @json($src),
        vastURL: @json($vastlink),
        playing: false,
        duration: 0,
        buffered: 0,
        currentTime: 0,
        muted: @json($muted),
        volume: @json($volume),
        fullscreen: false,
        pictureInPicture: false,
        currentResolution: '360p',
        availableResolutions: [],
        currentPlaybackSpeed: @json($playbackSpeed),
        playbackSpeeds: @json($customPlaybackSpeeds),
        started: false,
        mouseMoveTimeout: null,
        throttleTimeout: false,
        menuActive: @json($menuActive),
        menuActiveText: @json($menuActiveText),
        menuBackground: @json($menuBackground),
        menuText: @json($menuText)
    };
</script>
@endsection

