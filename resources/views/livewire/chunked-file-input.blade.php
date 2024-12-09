<div class="bg-white rounded-xl h-fit border p-6 m-6">
    <input type="file" id="file-{{ $filename }}" accept="{{ $acceptedTypes }}">
    <div id="file-{{ $filename }}-errorbox" class="text-red-600 text-sm"></div>
    <div id="file-{{ $filename }}-progress-container" class="mt-6 p-4 border border-gray-300 rounded-lg bg-white shadow-lg" style="display: none;">
        <div class="relative h-2 w-full bg-gray-200 rounded-full overflow-hidden">
            <div id="file-{{ $filename }}-progress-bar" class="absolute top-0 left-0 h-full bg-blue-500 transition-all duration-300" style="width: 0%;"></div>
        </div>
        <div class="flex justify-between items-center mt-3">
            <div class="text-sm text-gray-700 font-semibold animate-pulse">Uploading...</div>
            <div id="file-{{ $filename }}-percentage-text" class="text-sm text-gray-700 font-semibold animate-pulse"></div>
        </div>
    </div>

    <script>
        class FileUploader {
            constructor(fileInputId, maxChunkSize = 1024 * 1024 * 10) {
                this.fileInput = document.getElementById(fileInputId);
                this.MAX_CHUNK_SIZE = maxChunkSize;
                this.CHUNK_SIZE = maxChunkSize;
                this.eachChunkPercentage = 1;
                this.currentProgress = 0;

                this.errorBox = document.getElementById('file-{{ $filename }}-errorbox');
                this.progressBar = document.getElementById('file-{{ $filename }}-progress-bar');
                this.percentageText = document.getElementById('file-{{ $filename }}-percentage-text');
                this.progressContainer = document.getElementById('file-{{ $filename }}-progress-container');
                this.initializeEventListeners();
            }

            initializeEventListeners() {
                this.fileInput.addEventListener('change', async (e) => {
                    const file = e.target.files[0];
                    if (!file) return;
                    await this.handleFileUpload(file);
                });
            }

            async callAPI(apiEndpoint, method = 'POST', body = null, contentType = 'application/json', headers = {}) {
                headers['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
                if (contentType) headers['Content-Type'] = contentType;

                const response = await fetch(apiEndpoint, {
                    method,
                    headers,
                    body
                });

                if (!response.ok) {
                    /*const errorBox = document.getElementById('errorbox');
                    errorBox.innerHTML = await response.text();
                    throw new Error('API request failed');*/
                }
                return await response.json();
            }

            updateProgress(progress) {
                this.progressBar.style.width = `${progress}%`;
                this.percentageText.innerText = `${progress}%`;
            }

            async uploadChunk(formData) {
                const { success } = await this.callAPI(
                    '/api/upload-chunk',
                    'POST',
                    formData,
                    null
                );
                return success;
            }

            async handleFileUpload(file) {
                try {
                    // Calculate chunk size as 1% of file size, but cap at MAX_CHUNK_SIZE
                    this.CHUNK_SIZE = Math.min(Math.ceil(file.size / 100), this.MAX_CHUNK_SIZE);
                    
                    var success = false;
                    var upload_id = '';
                    // Initialize upload
                    ({ success, upload_id } = await this.callAPI('/api/initiate', 'POST'));
                    if (!success) throw new Error('Upload initiation failed');

                    this.progressContainer.style.display = 'block';
                    // Prepare chunks
                    const totalChunks = Math.ceil(file.size / this.CHUNK_SIZE);
                    this.eachChunkPercentage = 100 / totalChunks;
                    let uploadPromises = [];

                    let completedChunks = 0;
                    
                    // Upload chunks
                    for (let i = 0; i < totalChunks; i++) {
                        const chunk = file.slice(i * this.CHUNK_SIZE, (i + 1) * this.CHUNK_SIZE);
                        const formData = new FormData();
                        formData.append('file', chunk);
                        formData.append('upload_id', upload_id);
                        formData.append('chunkIndex', i);
                        uploadPromises.push(
                            this.uploadChunk(formData).then(() => {
                                completedChunks++;
                                const progress = Math.round((completedChunks / totalChunks) * 100);
                                this.updateProgress(progress);
                            })
                        );
                        if ((i + 1) % 5 === 0 || i === totalChunks - 1) {
                            await Promise.all(uploadPromises);
                            uploadPromises = [];
                        }
                    }

                    // Finalize upload
                    const uploadDetails = JSON.stringify({
                        upload_id: upload_id,
                        totalChunks: totalChunks,
                        fileExtension: file.name.split('.').pop()
                    });
                    ({ success } = await this.callAPI('/api/finalize', 'POST', uploadDetails));
                    if (!success) throw new Error('Upload initiation failed');
                    this.errorBox.innerText = 'Upload completed! Success: ' + success;

                } catch (error) {
                    this.errorBox.innerText = 'Error: ' + error.message;
                    this.progressContainer.style.display = 'none';
                }
            }
        }

        // Initialize uploader
        new FileUploader('file-{{ $filename }}');
    </script>
</div>