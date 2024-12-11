<div class="bg-white rounded-xl h-fit border p-6 m-6">

    <div id="testdiv">
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        new FileUploader('{{ $filename }}', 'testdiv', '.mp4', 1024 * 1024 * 10, (uploadId) => {
            @this.set('uploadId', uploadId, true);
            console.log('Upload finalized successfully!', uploadId);
        });
    });
    </script>
</div>
