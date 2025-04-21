<?php
/**
 * Dropzone area component
 * 
 * Reusable upload area using Dropzone.js
 * 
 * @param string $dropzoneId - Unique ID for this dropzone instance
 * @param string $uploadUrl - URL to handle file uploads
 * @param array $options - Additional dropzone options
 */

// Default options
$options = $options ?? [];
$dropzoneId = $dropzoneId ?? 'dropzone-upload';
$uploadUrl = $uploadUrl ?? getenv('APP_BASE_PATH') . '/api/upload';
$maxFiles = $options['maxFiles'] ?? 10;
$maxFileSize = $options['maxFileSize'] ?? ((int)getenv('MAX_UPLOAD_SIZE_MB') ?: 25);
$acceptedFiles = $options['acceptedFiles'] ?? 'image/jpeg,image/png,image/gif,image/webp';
$dropzoneText = $options['dropzoneText'] ?? 'Drag and drop images here or click to browse';
?>

<div class="mb-6">
    <div id="<?= $dropzoneId ?>" class="dropzone rounded-xl border-2 border-dashed border-gray-500 p-8 text-center cursor-pointer hover:border-button transition-colors duration-200 bg-gray-700 bg-opacity-50">
        <div class="dz-message">
            <div class="text-accent text-2xl mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p><?= $dropzoneText ?></p>
            </div>
            <p class="text-sm text-gray-400 mb-2">Maximum file size: <?= $maxFileSize ?>MB</p>
            <p class="text-sm text-gray-400">Supported formats: JPG, PNG, GIF, WebP</p>
        </div>
    </div>
    
    <div id="<?= $dropzoneId ?>-preview" class="hidden mt-4 p-4 bg-gray-700 rounded-lg">
        <h3 class="text-lg font-semibold mb-2 text-white">Uploaded Files</h3>
        <div class="dropzone-previews flex flex-wrap gap-4"></div>
    </div>
</div>

<!-- Dropzone initialization for this instance -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load Dropzone.js if not already loaded
    if (typeof Dropzone === 'undefined') {
        const script = document.createElement('script');
        script.src = '<?= getenv('APP_BASE_PATH') ?>/js/vendor/dropzone.min.js';
        script.onload = initDropzone;
        document.head.appendChild(script);
    } else {
        initDropzone();
    }
    
    function initDropzone() {
        // Configure Dropzone
        Dropzone.autoDiscover = false;
        
        const myDropzone = new Dropzone("#<?= $dropzoneId ?>", {
            url: "<?= $uploadUrl ?>",
            paramName: "file",
            maxFilesize: <?= $maxFileSize ?>,
            maxFiles: <?= $maxFiles ?>,
            acceptedFiles: "<?= $acceptedFiles ?>",
            addRemoveLinks: true,
            dictRemoveFile: "Remove",
            previewsContainer: "#<?= $dropzoneId ?>-preview .dropzone-previews",
            
            init: function() {
                this.on("addedfile", function(file) {
                    document.getElementById("<?= $dropzoneId ?>-preview").classList.remove("hidden");
                });
                
                this.on("removedfile", function(file) {
                    if (this.files.length === 0) {
                        document.getElementById("<?= $dropzoneId ?>-preview").classList.add("hidden");
                    }
                    
                    // Trigger file removed event that can be caught by other scripts
                    const event = new CustomEvent('imgTasks:fileRemoved', {
                        detail: { file: file }
                    });
                    document.dispatchEvent(event);
                });
                
                this.on("success", function(file, response) {
                    try {
                        const data = typeof response === 'string' ? JSON.parse(response) : response;
                        
                        if (data.success) {
                            // Store the file ID and other data in the file object
                            file.imgTasksData = data.file;
                            
                            // Trigger file uploaded event that can be caught by other scripts
                            const event = new CustomEvent('imgTasks:fileUploaded', {
                                detail: { 
                                    file: file, 
                                    response: data 
                                }
                            });
                            document.dispatchEvent(event);
                        } else {
                            this.removeFile(file);
                            Swal.fire({
                                icon: 'error',
                                title: 'Upload Failed',
                                text: data.error || 'Unknown error occurred',
                                timer: 3000
                            });
                        }
                    } catch (error) {
                        console.error('Error parsing response:', error);
                        this.removeFile(file);
                        Swal.fire({
                            icon: 'error',
                            title: 'Upload Failed',
                            text: 'Invalid server response',
                            timer: 3000
                        });
                    }
                });
                
                this.on("error", function(file, errorMessage) {
                    this.removeFile(file);
                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Failed',
                        text: errorMessage,
                        timer: 3000
                    });
                });
                
                // Expose dropzone instance to window for external access if needed
                window.dropzones = window.dropzones || {};
                window.dropzones["<?= $dropzoneId ?>"] = this;
            }
        });
        
        // Add custom classes to preview elements for Tailwind styling
        myDropzone.options.previewTemplate = `
            <div class="dz-preview dz-file-preview bg-gray-800 rounded-lg p-3 shadow-md">
                <div class="dz-image">
                    <img data-dz-thumbnail class="rounded-md w-32 h-32 object-cover" />
                </div>
                <div class="dz-details mt-2">
                    <div class="dz-filename"><span data-dz-name class="text-sm text-white truncate block max-w-[140px]"></span></div>
                    <div class="dz-size mt-1"><span data-dz-size class="text-xs text-gray-400"></span></div>
                </div>
                <div class="dz-progress mt-2 h-2 rounded-full bg-gray-600 overflow-hidden">
                    <span class="dz-upload bg-button rounded-full h-full block w-0" data-dz-uploadprogress></span>
                </div>
                <div class="dz-success-mark hidden">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="dz-error-mark hidden">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div class="dz-error-message hidden"><span data-dz-errormessage class="text-xs text-red-500"></span></div>
                <div class="dz-controls mt-2">
                    <button data-dz-remove class="btn-remove text-xs bg-red-600 hover:bg-red-700 text-white py-1 px-2 rounded transition-colors duration-200">
                        Remove
                    </button>
                </div>
            </div>
        `;
    }
});
</script>