<?php
/**
 * Simple Optimization Mode View
 * 
 * Focused interface for image optimization with common parameters.
 * Allows users to tune optimization settings and batch process multiple images.
 * 
 * @var string $mode Current application mode
 * @var \ImgTasks\Utils\SessionManager $sessionManager Session manager instance
 */

// Set page title
$pageTitle = "Simple Optimization - Image Compression";
?>

<div class="max-w-5xl mx-auto">
    <!-- Mode Introduction -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-3">Simple Optimization Mode</h1>
        <p class="text-gray-300">Optimize your images with fine-tuned compression settings. Upload multiple images for batch processing.</p>
    </div>
    
    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <!-- Upload Panel (spans 3 columns on large screens) -->
        <div class="lg:col-span-3">
            <div class="card bg-gray-800 rounded-xl shadow-md">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-white mb-2">1. Upload Images</h2>
                    <p class="text-gray-400 text-sm">Add up to 10 images for batch processing</p>
                </div>
                
                <?php 
                // Include the dropzone component with multi-file support
                $dropzoneId = 'simple-opt-dropzone';
                $uploadUrl = getenv('APP_BASE_PATH') . '/api/upload';
                $options = [
                    'maxFiles' => 10,
                    'dropzoneText' => 'Drag and drop images here or click to browse'
                ];
                include dirname(__DIR__) . '/partials/dropzone_area.php'; 
                ?>
            </div>
        </div>
        
        <!-- Settings Panel (spans 2 columns on large screens) -->
        <div class="lg:col-span-2">
            <div class="card bg-gray-800 rounded-xl shadow-md">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-white mb-2">2. Optimization Settings</h2>
                    <p class="text-gray-400 text-sm">Adjust parameters to balance quality and file size</p>
                </div>
                
                <form id="optimization-settings" class="space-y-6">
                    <!-- Quality Setting -->
                    <div>
                        <label for="quality" class="block text-white text-sm font-medium mb-2">
                            Quality: <span id="quality-value">80%</span>
                        </label>
                        <div class="flex items-center">
                            <span class="text-xs text-gray-400 mr-2">Low</span>
                            <input type="range" id="quality" name="quality" class="w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer" min="1" max="100" value="80">
                            <span class="text-xs text-gray-400 ml-2">High</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Lower quality = smaller file size</p>
                    </div>
                    
                    <!-- Format Selection -->
                    <div>
                        <label for="format" class="block text-white text-sm font-medium mb-2">Output Format</label>
                        <select id="format" name="format" class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 p-2 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <option value="auto">Auto (Recommended)</option>
                            <option value="jpg">JPG</option>
                            <option value="png">PNG</option>
                            <option value="webp">WebP</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-400">'Auto' selects the best format for each image</p>
                    </div>
                    
                    <!-- Resize Option -->
                    <div>
                        <label for="resize" class="block text-white text-sm font-medium mb-2">Resize Images</label>
                        <select id="resize" name="resize" class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 p-2 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <option value="">No resizing</option>
                            <option value="1920x1080">1920×1080 (FHD)</option>
                            <option value="1280x720">1280×720 (HD)</option>
                            <option value="800x600">800×600</option>
                            <option value="custom">Custom size...</option>
                        </select>
                    </div>
                    
                    <!-- Custom Resize (hidden by default) -->
                    <div id="custom-resize-container" class="hidden">
                        <label for="custom-resize" class="block text-white text-sm font-medium mb-2">Custom Size (WxH)</label>
                        <input type="text" id="custom-resize" name="custom-resize" placeholder="e.g., 640x480" class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 p-2 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <p class="mt-1 text-xs text-gray-400">Enter width and height in pixels (e.g., 640x480)</p>
                    </div>
                    
                    <!-- Metadata Option -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="strip-metadata" name="strip-metadata" class="form-checkbox bg-gray-700 border-gray-600 text-button rounded" checked>
                            <span class="ml-2 text-white text-sm">Strip metadata (EXIF, GPS, etc.)</span>
                        </label>
                        <p class="mt-1 text-xs text-gray-400 ml-6">Removes camera info, location data, and other metadata</p>
                    </div>
                    
                    <!-- Process Button -->
                    <div class="pt-2">
                        <button id="optimize-btn" type="button" class="w-full btn btn-success bg-accent2 hover:bg-green-400 text-gray-800 font-medium py-3 px-4 rounded-lg flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Optimize Images
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Results Area -->
    <?php 
    // Include the results component
    $resultsId = 'simple-opt-results';
    $autoRefresh = false; 
    include dirname(__DIR__) . '/partials/results_area.php'; 
    ?>
</div>

<!-- Simple Optimization Mode JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const optimizeButton = document.getElementById('optimize-btn');
    const qualitySlider = document.getElementById('quality');
    const qualityValue = document.getElementById('quality-value');
    const resizeSelect = document.getElementById('resize');
    const customResizeContainer = document.getElementById('custom-resize-container');
    
    // Disable optimize button initially until an image is uploaded
    optimizeButton.disabled = true;
    
    // Listen for file upload events
    document.addEventListener('imgTasks:fileUploaded', function(e) {
        optimizeButton.disabled = false;
    });
    
    // Listen for file removal events
    document.addEventListener('imgTasks:fileRemoved', function(e) {
        // Check if there are any files left
        const dropzone = window.dropzones['simple-opt-dropzone'];
        if (!dropzone || dropzone.files.length === 0) {
            optimizeButton.disabled = true;
        }
    });
    
    // Update quality value display when slider is moved
    qualitySlider.addEventListener('input', function() {
        qualityValue.textContent = `${this.value}%`;
    });
    
    // Show/hide custom resize field based on selection
    resizeSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customResizeContainer.classList.remove('hidden');
        } else {
            customResizeContainer.classList.add('hidden');
        }
    });
    
    // Process button click handler
    optimizeButton.addEventListener('click', function() {
        // Check if images are uploaded
        const dropzone = window.dropzones['simple-opt-dropzone'];
        if (!dropzone || dropzone.files.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'No Images',
                text: 'Please upload at least one image first',
                timer: 3000
            });
            return;
        }
        
        // Collect optimization options
        const options = {
            quality: parseInt(qualitySlider.value),
            format: document.getElementById('format').value,
            strip_metadata: document.getElementById('strip-metadata').checked
        };
        
        // Add resize option if selected
        if (resizeSelect.value) {
            if (resizeSelect.value === 'custom') {
                const customResize = document.getElementById('custom-resize').value;
                if (customResize && /^\d+x\d+$/.test(customResize)) {
                    options.resize = customResize;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Size',
                        text: 'Please enter a valid size format (e.g., 640x480)',
                        timer: 3000
                    });
                    return;
                }
            } else {
                options.resize = resizeSelect.value;
            }
        }
        
        // Process single or batch depending on number of files
        if (dropzone.files.length === 1) {
            processSingleImage(dropzone.files[0], options);
        } else {
            processBatchImages(dropzone.files, options);
        }
    });
    
    // Process a single image
    function processSingleImage(file, options) {
        // Check if the file has valid data
        if (!file.imgTasksData || !file.imgTasksData.id) {
            Swal.fire({
                icon: 'error',
                title: 'Upload Error',
                text: 'The image was not properly uploaded. Please try again.',
                timer: 3000
            });
            return;
        }
        
        // Show processing spinner
        Swal.fire({
            title: 'Optimizing Image',
            html: 'Please wait while we optimize your image...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Send processing request
        fetch(`<?= getenv('APP_BASE_PATH') ?>/api/process`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                operation: 'optimize',
                fileId: file.imgTasksData.id,
                options: options
            })
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            
            if (data.success) {
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Your image has been optimized successfully',
                    timer: 2000
                });
                
                // Trigger event for results area to refresh
                const event = new CustomEvent('imgTasks:processingComplete', {
                    detail: { 
                        result: data.result 
                    }
                });
                document.dispatchEvent(event);
            } else {
                throw new Error(data.error || 'Unknown processing error');
            }
        })
        .catch(error => {
            console.error('Processing error:', error);
            
            Swal.fire({
                icon: 'error',
                title: 'Optimization Failed',
                text: error.message || 'An error occurred during optimization',
                timer: 5000
            });
        });
    }
    
    // Process multiple images in batch
    function processBatchImages(files, options) {
        // Show batch processing dialog
        Swal.fire({
            title: 'Batch Optimization',
            html: `
                <div class="text-center">
                    <p class="mb-4">Optimizing ${files.length} images...</p>
                    <div class="progress-bar-container mx-auto h-4 bg-gray-300 rounded-full overflow-hidden mb-4">
                        <div id="batch-progress-bar" class="h-full bg-blue-600 rounded-full" style="width: 0%"></div>
                    </div>
                    <p id="batch-progress-text">Starting... (0/${files.length})</p>
                </div>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });
        
        // Process files sequentially
        let completedCount = 0;
        let successCount = 0;
        let errorCount = 0;
        let results = [];
        
        // Process files one by one
        const processNextFile = (index) => {
            if (index >= files.length) {
                // All files processed
                setTimeout(() => {
                    Swal.close();
                    
                    // Show completion message
                    Swal.fire({
                        icon: successCount > 0 ? 'success' : 'error',
                        title: 'Batch Processing Complete',
                        html: `
                            <p>Successfully optimized: ${successCount} of ${files.length}</p>
                            ${errorCount > 0 ? `<p class="text-red-500">Failed: ${errorCount}</p>` : ''}
                        `,
                        timer: 3000
                    });
                    
                    // Refresh results if any successful
                    if (successCount > 0) {
                        const event = new CustomEvent('imgTasks:processingComplete');
                        document.dispatchEvent(event);
                    }
                }, 500);
                return;
            }
            
            const file = files[index];
            
            // Check if file has valid data
            if (!file.imgTasksData || !file.imgTasksData.id) {
                // Skip invalid file
                completedCount++;
                errorCount++;
                updateBatchProgress(completedCount, files.length);
                processNextFile(index + 1);
                return;
            }
            
            // Process the current file
            fetch(`<?= getenv('APP_BASE_PATH') ?>/api/process`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    operation: 'optimize',
                    fileId: file.imgTasksData.id,
                    options: options
                })
            })
            .then(response => response.json())
            .then(data => {
                completedCount++;
                
                if (data.success) {
                    successCount++;
                    results.push(data.result);
                } else {
                    errorCount++;
                    console.error(`Error processing ${file.name}: ${data.error}`);
                }
            })
            .catch(error => {
                completedCount++;
                errorCount++;
                console.error(`Error processing ${file.name}: ${error.message}`);
            })
            .finally(() => {
                // Update progress
                updateBatchProgress(completedCount, files.length);
                
                // Process next file
                processNextFile(index + 1);
            });
        };
        
        // Update the batch progress UI
        function updateBatchProgress(completed, total) {
            const progressBar = document.getElementById('batch-progress-bar');
            const progressText = document.getElementById('batch-progress-text');
            
            const percentage = Math.round((completed / total) * 100);
            progressBar.style.width = `${percentage}%`;
            progressText.textContent = `Processing... (${completed}/${total})`;
        }
        
        // Start processing the first file
        processNextFile(0);
    }
});
</script>