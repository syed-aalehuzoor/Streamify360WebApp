<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <title>Document</title>
</head>
<body>
    <h1>
        {{ $city }}
    </h1>
    <h2>
        {{ $regionName }}
    </h2>
    <h3>
        {{ $country }}
    </h3>
    <h1>
        {{ $operatingSystem }}
    </h1>
    <h1>
        {{ $deviceType }}
    </h1>
    <h1>Check HLS Support</h1>
    <p id="hls-status">Checking HLS support...</p>
    <script>
        // Check if HLS is supported
        const hlsStatusElement = document.getElementById("hls-status");
        
        if (Hls.isSupported()) {
            hlsStatusElement.textContent = "HLS is supported in this browser!";
            hlsStatusElement.style.color = "green";
        } else {
            hlsStatusElement.textContent = "HLS is NOT supported in this browser.";
            hlsStatusElement.style.color = "red";
        }
    </script>
</body>
</html>