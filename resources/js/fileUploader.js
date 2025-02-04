class FileUploader {
    constructor(fileName, containerId, acceptedTypes = '*', maxChunkSize = 1024 * 1024 * 10, onFinalizeCallback = null) {
        if (!fileName || !containerId) {
            throw new Error('fileName and containerId are required');
        }

        this.fileInputId = `filename-${fileName}`;
        this.containerId = containerId;
        this.maxChunkSize = maxChunkSize;
        this.currentChunkSize = maxChunkSize;
        this.acceptedTypes = acceptedTypes;
        this.onFinalizeCallback = onFinalizeCallback;
        this.isUploading = false;

        this.createUIElements();
        this.initializeEventListeners();
    }

    createUIElements() {
        const wrapper = document.getElementById(this.containerId);
        if (!wrapper) {
            throw new Error(`Container with id "${this.containerId}" not found`);
        }
        wrapper.classList.add('w-full', 'flex' ,'flex-col', 'items-center', 'border', 'border-gray-300', 'rounded-xl');
        // Create file input
        this.fileInput = document.createElement('input');
        this.fileInput.type = 'file';
        this.fileInput.id = this.fileInputId;
        this.fileInput.accept = this.acceptedTypes;
        this.fileInput.classList.add('w-full', 'px-6', 'p-8');
        wrapper.appendChild(this.fileInput);

        // Create error display
        this.errorBox = document.createElement('div');
        this.errorBox.id = `${this.fileInputId}-errorbox`;
        this.errorBox.className = 'text-red-600 text-sm';
        wrapper.appendChild(this.errorBox);

        // Create progress container
        this.progressContainer = this.createProgressContainer();
        wrapper.appendChild(this.progressContainer);
    }

    createProgressContainer() {
        const container = document.createElement('div');
        container.id = `${this.fileInputId}-progress-container`;
        container.className = 'p-6 my-6 mx-8 w-full border border-gray-300 rounded-lg bg-white shadow-lg';
        container.style.display = 'none';

        const progressBarWrapper = document.createElement('div');
        progressBarWrapper.className = 'relative h-2 w-full bg-gray-200 rounded-full overflow-hidden';

        this.progressBar = document.createElement('div');
        this.progressBar.id = `${this.fileInputId}-progress-bar`;
        this.progressBar.className = 'absolute top-0 left-0 h-full bg-blue-500 transition-all duration-300';
        this.progressBar.style.width = '0%';
        progressBarWrapper.appendChild(this.progressBar);

        container.appendChild(progressBarWrapper);

        const progressTextContainer = document.createElement('div');
        progressTextContainer.className = 'flex justify-between items-center mt-3';

        const uploadingText = document.createElement('div');
        uploadingText.className = 'text-sm text-gray-700 font-semibold animate-pulse';
        uploadingText.innerText = 'Uploading...';
        progressTextContainer.appendChild(uploadingText);

        this.percentageText = document.createElement('div');
        this.percentageText.id = `${this.fileInputId}-percentage-text`;
        this.percentageText.className = 'text-sm text-gray-700 font-semibold animate-pulse';
        progressTextContainer.appendChild(this.percentageText);

        container.appendChild(progressTextContainer);
        return container;
    }

    initializeEventListeners() {
        this.fileInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;
            if (this.isUploading) {
                this.showError('An upload is already in progress');
                return;
            }
            await this.handleFileUpload(file);
        });
    }

    async callAPI(apiEndpoint, method = 'POST', body = null, contentType = 'application/json', headers = {}) {
        try {
            headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content;
            if (contentType) headers['Content-Type'] = contentType;

            const response = await fetch(apiEndpoint, {
                method,
                headers,
                body
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            throw new Error(`API request failed: ${error.message}`);
        }
    }

    updateProgress(progress) {
        const clampedProgress = Math.min(Math.max(progress, 0), 100);
        this.progressBar.style.width = `${clampedProgress}%`;
        this.percentageText.innerText = `${clampedProgress}%`;
    }

    showError(message) {
        this.errorBox.innerText = `Error: ${message}`;
        this.progressContainer.style.display = 'none';
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
        this.isUploading = true;
        this.errorBox.innerText = '';
        
        try {
            // Calculate optimal chunk size based on file size
            this.currentChunkSize = Math.min(Math.ceil(file.size / 100), this.maxChunkSize);
            
            // Initialize upload
            const { success, upload_id } = await this.callAPI('/api/initiate', 'POST');
            if (!success) throw new Error('Upload initiation failed');

            // Setup progress tracking
            this.progressContainer.style.display = 'block';
            const totalChunks = Math.ceil(file.size / this.currentChunkSize);
            let uploadPromises = [];
            let completedChunks = 0;
            
            // Upload chunks
            for (let i = 0; i < totalChunks; i++) {
                const chunk = file.slice(i * this.currentChunkSize, (i + 1) * this.currentChunkSize);
                const formData = new FormData();
                formData.append('file', chunk);
                formData.append('upload_id', upload_id);
                formData.append('chunkIndex', i);
                
                uploadPromises.push(
                    this.uploadChunk(formData).then(() => {
                        completedChunks++;
                        this.updateProgress(Math.round((completedChunks / totalChunks) * 100));
                    })
                );

                // Process chunks in batches of 5
                if ((i + 1) % 5 === 0 || i === totalChunks - 1) {
                    await Promise.all(uploadPromises);
                    uploadPromises = [];
                }
            }

            // Finalize upload
            const uploadDetails = JSON.stringify({
                upload_id,
                totalChunks,
                fileExtension: file.name.split('.').pop()
            });
            const { success: finalizeSuccess } = await this.callAPI('/api/finalize', 'POST', uploadDetails);
            if (!finalizeSuccess) throw new Error('Upload finalization failed');

            this.errorBox.innerText = 'Upload completed successfully!';
            if (this.onFinalizeCallback) {
                this.onFinalizeCallback(upload_id);
            }

        } catch (error) {
            this.showError(error.message);
        } finally {
            this.isUploading = false;
        }
    }
}
export default FileUploader;