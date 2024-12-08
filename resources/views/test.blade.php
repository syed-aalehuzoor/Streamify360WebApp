<!DOCTYPE html>
<html>
<head>
    <title>Chunked File Upload Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .progress {
            width: 100%;
            background-color: #f0f0f0;
            padding: 3px;
            border-radius: 3px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, .2);
        }
        .progress-bar {
            display: block;
            height: 22px;
            background-color: #659cef;
            border-radius: 3px;
            transition: width 500ms ease-in-out;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>File Upload Test</h2>
        <input type="file" id="fileInput">
        <div class="progress" style="display:none;">
            <span class="progress-bar" style="width: 0%;">0%</span>
        </div>
        <div id="status"></div>
    </div>
    <div id="errorbox">

    </div>

    <script>
        const CHUNK_SIZE = 1024 * 1024; // 1MB chunks
        
        document.getElementById('fileInput').addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            const progress = document.querySelector('.progress');
            const progressBar = document.querySelector('.progress-bar');
            const status = document.getElementById('status');
            progress.style.display = 'block';
            
            try {
                // Step 1: Initiate upload
                const initResponse = await fetch('{{ route('upload.initiate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        filename: file.name
                    })
                });
                console.log(initResponse);
                const { upload_id } = await initResponse.json();
                console.log(upload_id);
                
                // Step 2: Upload chunks
                const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
                
                for (let i = 0; i < totalChunks; i++) {
                    const chunk = file.slice(i * CHUNK_SIZE, (i + 1) * CHUNK_SIZE);
                    const formData = new FormData();
                    formData.append('file', chunk);
                    formData.append('upload_id', upload_id);
                    formData.append('chunkIndex', i);
                    
                    const chunkResponse = await fetch('{{ route('upload.chunk') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    });
                    const errorBox = document.getElementById('errorbox');
                    errorBox.innerHTML = await chunkResponse.text();
                  
                    // Update progress
                    const percentComplete = ((i + 1) / totalChunks) * 100;
                    progressBar.style.width = percentComplete + '%';
                    progressBar.textContent = Math.round(percentComplete) + '%';
                }
                
                // Step 3: Finalize upload
                const fileName = file.name;
                const fileExtension = fileName.split('.').pop();
                const finalizeResponse = await fetch('{{ route('upload.finalize') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        upload_id: upload_id,
                        totalChunks: totalChunks,
                        fileExtension: fileExtension
                    })
                });
                
                const result = await finalizeResponse.json();
                status.textContent = 'Upload completed! File path: ' + result.filepath;
                
            } catch (error) {
                status.textContent = 'Error: ' + error.message;
                console.error(error);
            }
        });
    </script>
</body>
</html>