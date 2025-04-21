<?php
/**
 * Basic Mode View
 * 
 * Simple interface for quick image tasks with immediate processing.
 * Allows users to optimize images or remove backgrounds with minimal options.
 * 
 * @var string $mode Current application mode
 * @var \ImgTasks\Utils\SessionManager $sessionManager Session manager instance
 */

// Set page title
$pageTitle = "Basic Mode - Quick Image Processing";
?>

<div class="max-w-5xl mx-auto">
    <!-- Mode Introduction -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-3">Basic Mode</h1>
        <p class="text-gray-300">Quickly process your images with the most common operations. Just upload, select an operation, and process.</p>
    </div>
    
    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Upload Panel -->
        <div class="lg:col-span-2">
            <div class="card bg-gray-800 rounded-xl shadow-md">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-white mb-2">1. Upload Your Image</h2>
                    <p class="text-gray-400 text-sm">Supported formats: JPG, PNG, WebP, GIF</p>
                </div>
                
                <?php 
                // Include the dropzone component
                $dropzoneId = 'basic-mode-dropzone';
                $uploadUrl = getenv('APP_BASE_PATH') . '/api/upload';
                $options = [
                    'maxFiles' => 1,
                    'dropzoneText' => 'Drag and drop an image here or click to browse'
                ];
                include dirname(__DIR__) . '/partials/dropzone_area.php'; 
                ?>
            </div>
        </div>
        
        <!-- Operations Panel -->
        <div class="lg:col-span-1">
            <div class="card bg-gray-800 rounded-xl shadow-md">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-white mb-2">2. Choose Operation</h2>
                    <p class="text-gray-400 text-sm">Select one operation to perform</p>
                </div>
                
                <div id="basic-operations" class="space-y-4">
                    <!-- Optimize Operation -->
                    <div class="operation-option p-4 border border-gray-700 rounded-lg cursor-pointer hover:border-button hover:bg-gray-700 transition-colors duration-200">
                        <label class="flex items-start cursor-pointer">
                            <input type="radio" name="operation" value="optimize" class="mt-1 form-radio text-button" checked>
                            <div class="ml-3">
                                <span class="block font-medium text-white">Optimize Image</span>
                                <span class="block text-sm text-gray-400 mt-1">Reduce file size while maintaining quality</span>
                            </div>
                        </label>
                    </div>
                    
                    <!-- Remove Background Operation -->
                    <div class="operation-option p-4 border border-gray-700 rounded-lg cursor-pointer hover:border-button hover:bg-gray-700 transition-colors duration-200">
                        <label class="flex items-start cursor-pointer">
                            <input type="radio" name="operation" value="remove_background" class="mt-1 form-radio text-button">
                            <div class="ml-3">
                                <span class="block font-medium text-white">Remove Background</span>
                                <span class="block text-sm text-gray-400 mt-1">Create a transparent background (PNG)</span>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Process Button -->
                <div class="mt-6">
                    <button id="basic-process-btn" class="w-full btn btn-success bg-accent2 hover:bg-green-400 text-gray-800 font-medium py-2 px-4 rounded-lg flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Process Image
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Results Area -->
    <?php 
    // Include the results component
    $resultsId = 'basic-mode-results';
    $autoRefresh = false; 
    include dirname(__DIR__) . '/partials/results_area.php'; 
    ?>
</div>

<!-- Basic Mode JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const processButton = document.getElementById('basic-process-btn');
    const operationOptions = document.querySelectorAll('.operation-option');
    
    // Disable process button initially until an image is uploaded
    processButton.disabled = true;
    
    // Listen for file upload events
    document.addEventListener('imgTasks:fileUploaded', function(e) {
        processButton.disabled = false;
    });
    
    // Listen for file removal events
    document.addEventListener('imgTasks:fileRemoved', function(e) {
        // Check if there are any files left
        const dropzone = window.dropzones['basic-mode-dropzone'];
        if (!dropzone || dropzone.files.length === 0) {
            processButton.disabled = true;
        }
    });
    
    // Highlight selected operation
    operationOptions.forEach(option => {
        const radio = option.querySelector('input[type="radio"]');
        
        // Initial state
        if (radio.checked) {
            option.classList.add('border-button', 'bg-gray-700');
        }
        
        // Click handler
        option.addEventListener('click', function() {
            // Uncheck all radios
            document.querySelectorAll('input[name="operation"]').forEach(radio => {
                radio.checked = false;
            });
            
            // Check this radio
            radio.checked = true;
            
            // Update highlighting
            operationOptions.forEach(opt => {
                opt.classList.remove('border-button', 'bg-gray-700');
            });
            option.classList.add('border-button', 'bg-gray-700');
        });
    });
    
    // Process button click handler
    processButton.addEventListener('click', function() {
        // Check if an image is uploaded
        const dropzone = window.dropzones['basic-mode-dropzone'];
        if (!dropzone || dropzone.files.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'No Image',
                text: 'Please upload an image first',
                timer: 3000
            });
            return;
        }
        
        // Get selected operation
        const selectedOperation = document.querySelector('input[name="operation"]:checked').value;
        
        // Get the file data
        const file = dropzone.files[0];
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
            title: 'Processing Image',
            html: 'Please wait while we process your image...',
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
                operation: selectedOperation,
                fileId: file.imgTasksData.id,
                options: {} // Use default options in basic mode
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
                    text: 'Your image has been processed successfully',
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
                title: 'Processing Failed',
                text: error.message || 'An error occurred during processing',
                timer: 5000
            });
        });
    });
});
</script>