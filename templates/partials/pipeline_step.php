<?php
/**
 * Pipeline step component
 * 
 * Template for a single step in the advanced pipeline mode.
 * Used with SortableJS to create a draggable, configurable processing pipeline.
 * 
 * @param string $pipelineId - Unique ID for the pipeline container
 */

// Default options
$pipelineId = $pipelineId ?? 'processing-pipeline';
?>

<div id="<?= $pipelineId ?>-container" class="mb-8">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold text-white">Processing Pipeline</h3>
        <div class="flex space-x-2">
            <button id="<?= $pipelineId ?>-add" class="bg-button hover:bg-blue-600 text-white text-sm py-1 px-3 rounded flex items-center transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Step
            </button>
            <button id="<?= $pipelineId ?>-clear" class="bg-gray-700 hover:bg-gray-600 text-white text-sm py-1 px-3 rounded flex items-center transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Clear All
            </button>
        </div>
    </div>

    <div id="<?= $pipelineId ?>-empty" class="bg-gray-700 rounded-xl p-6 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <p class="text-gray-300 mb-2">No pipeline steps defined</p>
        <p class="text-sm text-gray-400">Click 'Add Step' to start building your processing pipeline</p>
    </div>

    <div id="<?= $pipelineId ?>-steps" class="hidden">
        <div id="<?= $pipelineId ?>-list" class="space-y-2">
            <!-- Pipeline steps will be added here dynamically -->
        </div>
        
        <div class="mt-6 text-right">
            <button id="<?= $pipelineId ?>-run" class="bg-accent2 hover:bg-green-400 text-gray-800 py-2 px-4 rounded-lg font-medium flex items-center ml-auto transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Run Pipeline
            </button>
        </div>
    </div>
</div>

<!-- Step Template -->
<template id="<?= $pipelineId ?>-step-template">
    <div class="pipeline-step bg-gray-800 rounded-lg border border-gray-700 shadow-md">
        <div class="flex items-center p-3 cursor-move handle">
            <div class="step-number flex items-center justify-center w-6 h-6 rounded-full bg-button text-white text-sm font-bold mr-3">1</div>
            <div class="step-title font-medium text-white flex-grow"></div>
            <div class="flex space-x-1">
                <button class="step-toggle-options text-gray-300 hover:text-white p-1 rounded transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <button class="step-remove text-red-400 hover:text-red-500 p-1 rounded transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="step-options-container hidden px-4 pb-4 pt-1">
            <!-- Options will be dynamically generated based on step type -->
        </div>
    </div>
</template>

<!-- Optimization Options Template -->
<template id="<?= $pipelineId ?>-optimize-options-template">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="form-label text-gray-300">Quality</label>
            <div class="flex items-center">
                <input type="range" class="optimize-quality w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer" min="1" max="100" value="80">
                <span class="optimize-quality-value ml-2 text-gray-300 w-8 text-right">80%</span>
            </div>
        </div>
        
        <div>
            <label class="form-label text-gray-300">Format</label>
            <select class="optimize-format bg-gray-700 text-white w-full px-3 py-2 rounded-lg border border-gray-600 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <option value="auto">Auto (recommended)</option>
                <option value="jpg">JPG</option>
                <option value="png">PNG</option>
                <option value="webp">WebP</option>
            </select>
        </div>
        
        <div>
            <label class="form-label text-gray-300">Resize</label>
            <select class="optimize-resize bg-gray-700 text-white w-full px-3 py-2 rounded-lg border border-gray-600 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <option value="">No resizing</option>
                <option value="1920x1080">1920×1080 (FHD)</option>
                <option value="1280x720">1280×720 (HD)</option>
                <option value="800x600">800×600</option>
                <option value="custom">Custom size...</option>
            </select>
        </div>
        
        <div class="optimize-custom-resize hidden">
            <label class="form-label text-gray-300">Custom Size (WxH)</label>
            <input type="text" class="optimize-custom-resize-value bg-gray-700 text-white w-full px-3 py-2 rounded-lg border border-gray-600 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" placeholder="e.g., 640x480">
        </div>
        
        <div class="col-span-full">
            <label class="inline-flex items-center cursor-pointer">
                <input type="checkbox" class="optimize-strip-metadata form-checkbox bg-gray-700 border-gray-600 text-button rounded w-5 h-5" checked>
                <span class="ml-2 text-gray-300">Strip metadata (EXIF, etc.)</span>
            </label>
        </div>
    </div>
</template>

<!-- Background Removal Options Template -->
<template id="<?= $pipelineId ?>-remove-bg-options-template">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-full">
            <label class="inline-flex items-center cursor-pointer">
                <input type="checkbox" class="remove-bg-alpha-matting form-checkbox bg-gray-700 border-gray-600 text-button rounded w-5 h-5">
                <span class="ml-2 text-gray-300">Use alpha matting (better edges for hair/fur)</span>
            </label>
        </div>
        
        <div class="alpha-matting-options hidden">
            <div class="mb-4">
                <label class="form-label text-gray-300">Foreground Threshold</label>
                <div class="flex items-center">
                    <input type="range" class="remove-bg-fg-threshold w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer" min="0" max="255" value="240">
                    <span class="remove-bg-fg-threshold-value ml-2 text-gray-300 w-8 text-right">240</span>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label text-gray-300">Background Threshold</label>
                <div class="flex items-center">
                    <input type="range" class="remove-bg-bg-threshold w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer" min="0" max="255" value="10">
                    <span class="remove-bg-bg-threshold-value ml-2 text-gray-300 w-8 text-right">10</span>
                </div>
            </div>
            
            <div>
                <label class="form-label text-gray-300">Erode Size</label>
                <div class="flex items-center">
                    <input type="range" class="remove-bg-erode-size w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer" min="0" max="30" value="10">
                    <span class="remove-bg-erode-size-value ml-2 text-gray-300 w-8 text-right">10</span>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Pipeline JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pipelineId = '<?= $pipelineId ?>';
    const container = document.getElementById(`${pipelineId}-container`);
    if (!container) return;
    
    const addButton = document.getElementById(`${pipelineId}-add`);
    const clearButton = document.getElementById(`${pipelineId}-clear`);
    const runButton = document.getElementById(`${pipelineId}-run`);
    const emptyState = document.getElementById(`${pipelineId}-empty`);
    const stepsContainer = document.getElementById(`${pipelineId}-steps`);
    const stepsList = document.getElementById(`${pipelineId}-list`);
    
    const stepTemplate = document.getElementById(`${pipelineId}-step-template`);
    const optimizeOptionsTemplate = document.getElementById(`${pipelineId}-optimize-options-template`);
    const removeBgOptionsTemplate = document.getElementById(`${pipelineId}-remove-bg-options-template`);
    
    let stepCounter = 0;
    let sortableInstance = null;
    
    // Initialize SortableJS
    initSortable();
    
    // Add event listeners
    addButton.addEventListener('click', showAddStepDialog);
    clearButton.addEventListener('click', clearPipeline);
    runButton.addEventListener('click', runPipeline);
    
    // Initialize pipeline
    updatePipelineVisibility();
    
    // Listen for file uploaded events
    document.addEventListener('imgTasks:fileUploaded', function(e) {
        // Enable the pipeline if it's empty
        if (getStepCount() === 0) {
            showAddStepDialog();
        }
    });
    
    // Initialize SortableJS for drag and drop reordering
    function initSortable() {
        if (typeof Sortable === 'undefined') {
            // Load Sortable.js if not available
            const script = document.createElement('script');
            script.src = '<?= getenv('APP_BASE_PATH') ?>/js/vendor/Sortable.min.js';
            script.onload = function() {
                createSortableInstance();
            };
            document.head.appendChild(script);
        } else {
            createSortableInstance();
        }
    }
    
    // Create SortableJS instance
    function createSortableInstance() {
        sortableInstance = new Sortable(stepsList, {
            animation: 150,
            handle: '.handle',
            ghostClass: 'bg-gray-700',
            onEnd: function() {
                updateStepNumbers();
            }
        });
    }
    
    // Show dialog to add a new step
    function showAddStepDialog() {
        Swal.fire({
            title: 'Add Processing Step',
            html: `
                <div class="text-left">
                    <p class="mb-4">Select the type of processing step to add to your pipeline:</p>
                    <div class="space-y-2">
                        <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-100">
                            <input type="radio" name="stepType" value="optimize" class="mr-3" checked>
                            <div>
                                <span class="font-medium block">Image Optimization</span>
                                <span class="text-sm text-gray-600">Reduce file size, convert format, resize</span>
                            </div>
                        </label>
                        <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-100">
                            <input type="radio" name="stepType" value="remove_background" class="mr-3">
                            <div>
                                <span class="font-medium block">Background Removal</span>
                                <span class="text-sm text-gray-600">Automatically remove the image background</span>
                            </div>
                        </label>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Add Step',
            confirmButtonColor: '#397BFB',
            customClass: {
                container: 'swal-wide'
            },
            preConfirm: () => {
                const radioButtons = document.getElementsByName('stepType');
                for (const radioButton of radioButtons) {
                    if (radioButton.checked) {
                        return radioButton.value;
                    }
                }
                return 'optimize'; // Default
            }
        }).then((result) => {
            if (result.isConfirmed) {
                addPipelineStep(result.value);
            }
        });
    }
    
    // Add a new pipeline step
    function addPipelineStep(type) {
        // Increment step counter
        stepCounter++;
        
        // Clone step template
        const step = stepTemplate.content.cloneNode(true).firstElementChild;
        step.dataset.type = type;
        step.dataset.stepId = `step-${stepCounter}`;
        
        // Set step title based on type
        const stepTitle = step.querySelector('.step-title');
        if (type === 'optimize') {
            stepTitle.textContent = 'Optimize Image';
        } else if (type === 'remove_background') {
            stepTitle.textContent = 'Remove Background';
        }
        
        // Set step options
        const optionsContainer = step.querySelector('.step-options-container');
        if (type === 'optimize') {
            const options = optimizeOptionsTemplate.content.cloneNode(true);
            optionsContainer.appendChild(options);
            
            // Add event listener for custom resize option
            const resizeSelect = optionsContainer.querySelector('.optimize-resize');
            const customResizeContainer = optionsContainer.querySelector('.optimize-custom-resize');
            
            resizeSelect.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customResizeContainer.classList.remove('hidden');
                } else {
                    customResizeContainer.classList.add('hidden');
                }
            });
            
            // Add event listener for quality slider
            const qualitySlider = optionsContainer.querySelector('.optimize-quality');
            const qualityValue = optionsContainer.querySelector('.optimize-quality-value');
            
            qualitySlider.addEventListener('input', function() {
                qualityValue.textContent = `${this.value}%`;
            });
            
        } else if (type === 'remove_background') {
            const options = removeBgOptionsTemplate.content.cloneNode(true);
            optionsContainer.appendChild(options);
            
            // Add event listener for alpha matting checkbox
            const alphaMattingCheckbox = optionsContainer.querySelector('.remove-bg-alpha-matting');
            const alphaMattingOptions = optionsContainer.querySelector('.alpha-matting-options');
            
            alphaMattingCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    alphaMattingOptions.classList.remove('hidden');
                } else {
                    alphaMattingOptions.classList.add('hidden');
                }
            });
            
            // Add event listeners for threshold sliders
            const fgThresholdSlider = optionsContainer.querySelector('.remove-bg-fg-threshold');
            const fgThresholdValue = optionsContainer.querySelector('.remove-bg-fg-threshold-value');
            
            fgThresholdSlider.addEventListener('input', function() {
                fgThresholdValue.textContent = this.value;
            });
            
            const bgThresholdSlider = optionsContainer.querySelector('.remove-bg-bg-threshold');
            const bgThresholdValue = optionsContainer.querySelector('.remove-bg-bg-threshold-value');
            
            bgThresholdSlider.addEventListener('input', function() {
                bgThresholdValue.textContent = this.value;
            });
            
            const erodeSizeSlider = optionsContainer.querySelector('.remove-bg-erode-size');
            const erodeSizeValue = optionsContainer.querySelector('.remove-bg-erode-size-value');
            
            erodeSizeSlider.addEventListener('input', function() {
                erodeSizeValue.textContent = this.value;
            });
        }
        
        // Add event listeners for toggle and remove buttons
        const toggleButton = step.querySelector('.step-toggle-options');
        toggleButton.addEventListener('click', function() {
            optionsContainer.classList.toggle('hidden');
            
            // Change icon based on state
            const icon = this.querySelector('svg');
            if (optionsContainer.classList.contains('hidden')) {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />';
            } else {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />';
            }
        });
        
        const removeButton = step.querySelector('.step-remove');
        removeButton.addEventListener('click', function() {
            step.remove();
            updateStepNumbers();
            updatePipelineVisibility();
        });
        
        // Add step to pipeline
        stepsList.appendChild(step);
        
        // Update step numbers and visibility
        updateStepNumbers();
        updatePipelineVisibility();
    }
    
    // Update step numbers
    function updateStepNumbers() {
        const steps = Array.from(stepsList.children);
        steps.forEach((step, index) => {
            const numberElement = step.querySelector('.step-number');
            numberElement.textContent = index + 1;
        });
    }
    
    // Get the number of steps in the pipeline
    function getStepCount() {
        return stepsList.children.length;
    }
    
    // Update pipeline visibility based on step count
    function updatePipelineVisibility() {
        if (getStepCount() > 0) {
            emptyState.classList.add('hidden');
            stepsContainer.classList.remove('hidden');
        } else {
            emptyState.classList.remove('hidden');
            stepsContainer.classList.add('hidden');
        }
    }
    
    // Clear all pipeline steps
    function clearPipeline() {
        Swal.fire({
            title: 'Clear Pipeline',
            text: 'Are you sure you want to remove all steps from the pipeline?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, clear it',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6'
        }).then((result) => {
            if (result.isConfirmed) {
                stepsList.innerHTML = '';
                updatePipelineVisibility();
            }
        });
    }
    
    // Run the pipeline on the selected file
    function runPipeline() {
        // Check if there are uploaded files
        if (!window.dropzones || Object.keys(window.dropzones).length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'No Files',
                text: 'Please upload at least one image to process',
                timer: 3000
            });
            return;
        }
        
        // Check if dropzone has files
        let hasFiles = false;
        let selectedFile = null;
        
        for (const dropzoneId in window.dropzones) {
            const dropzone = window.dropzones[dropzoneId];
            if (dropzone.files.length > 0) {
                hasFiles = true;
                
                // If multiple files, ask user to select one
                if (dropzone.files.length > 1) {
                    showFileSelectionDialog(dropzone.files);
                    return;
                } else {
                    selectedFile = dropzone.files[0];
                }
                
                break;
            }
        }
        
        if (!hasFiles) {
            Swal.fire({
                icon: 'error',
                title: 'No Files',
                text: 'Please upload at least one image to process',
                timer: 3000
            });
            return;
        }
        
        // If we have a selected file, process it
        if (selectedFile) {
            processPipeline(selectedFile);
        }
    }
    
    // Show dialog to select a file when multiple are uploaded
    function showFileSelectionDialog(files) {
        let fileOptions = '';
        
        files.forEach((file, index) => {
            fileOptions += `
                <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-100 mb-2">
                    <input type="radio" name="fileSelection" value="${index}" class="mr-3" ${index === 0 ? 'checked' : ''}>
                    <div class="flex items-center">
                        <img src="${file.dataURL || URL.createObjectURL(file)}" class="w-12 h-12 object-cover rounded mr-3">
                        <div>
                            <span class="font-medium block">${file.name}</span>
                            <span class="text-sm text-gray-600">${formatFileSize(file.size)}</span>
                        </div>
                    </div>
                </label>
            `;
        });
        
        Swal.fire({
            title: 'Select Image to Process',
            html: `
                <div class="text-left">
                    <p class="mb-4">Select which image to process through the pipeline:</p>
                    <div class="space-y-2">
                        ${fileOptions}
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Process Image',
            confirmButtonColor: '#397BFB',
            customClass: {
                container: 'swal-wide'
            },
            preConfirm: () => {
                const radioButtons = document.getElementsByName('fileSelection');
                for (const radioButton of radioButtons) {
                    if (radioButton.checked) {
                        return parseInt(radioButton.value);
                    }
                }
                return 0; // Default to first file
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const selectedFile = files[result.value];
                processPipeline(selectedFile);
            }
        });
    }
    
    // Format file size for display
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }
    
    // Process a file through the pipeline
    function processPipeline(file) {
        // Get pipeline steps
        const steps = Array.from(stepsList.children);
        if (steps.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Empty Pipeline',
                text: 'Please add at least one processing step to the pipeline',
                timer: 3000
            });
            return;
        }
        
        // Check if file has the required data
        if (!file.imgTasksData || !file.imgTasksData.id) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid File',
                text: 'The selected file does not have the required metadata',
                timer: 3000
            });
            return;
        }
        
        // Prepare pipeline steps data
        const pipelineSteps = steps.map(step => {
            const stepType = step.dataset.type;
            const optionsContainer = step.querySelector('.step-options-container');
            let options = {};
            
            if (stepType === 'optimize') {
                const qualitySlider = optionsContainer.querySelector('.optimize-quality');
                const formatSelect = optionsContainer.querySelector('.optimize-format');
                const resizeSelect = optionsContainer.querySelector('.optimize-resize');
                const stripMetadata = optionsContainer.querySelector('.optimize-strip-metadata');
                
                options.quality = parseInt(qualitySlider.value);
                options.format = formatSelect.value;
                
                if (resizeSelect.value === 'custom') {
                    const customResizeValue = optionsContainer.querySelector('.optimize-custom-resize-value');
                    options.resize = customResizeValue.value;
                } else if (resizeSelect.value) {
                    options.resize = resizeSelect.value;
                }
                
                options.strip_metadata = stripMetadata.checked;
                
            } else if (stepType === 'remove_background') {
                const alphaMattingCheckbox = optionsContainer.querySelector('.remove-bg-alpha-matting');
                
                options.alpha_matting = alphaMattingCheckbox.checked;
                
                if (options.alpha_matting) {
                    const fgThreshold = optionsContainer.querySelector('.remove-bg-fg-threshold');
                    const bgThreshold = optionsContainer.querySelector('.remove-bg-bg-threshold');
                    const erodeSize = optionsContainer.querySelector('.remove-bg-erode-size');
                    
                    options.alpha_matting_foreground_threshold = parseInt(fgThreshold.value);
                    options.alpha_matting_background_threshold = parseInt(bgThreshold.value);
                    options.alpha_matting_erode_size = parseInt(erodeSize.value);
                }
            }
            
            return {
                type: stepType,
                options: options
            };
        });
        
        // Show processing dialog
        const processingDialog = Swal.fire({
            title: 'Processing Image',
            html: `
                <div class="text-center">
                    <div class="progress-bar-container inline-block w-24 h-24 mb-4" id="pipeline-progress-bar"></div>
                    <p id="pipeline-progress-text">Initializing pipeline...</p>
                </div>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });
        
        // Initialize progress bar
        let progressBar = null;
        
        processingDialog.then(() => {
            const progressBarContainer = document.getElementById('pipeline-progress-bar');
            if (progressBarContainer && typeof ProgressBar !== 'undefined') {
                progressBar = new ProgressBar.Circle(progressBarContainer, {
                    strokeWidth: 6,
                    easing: 'easeInOut',
                    duration: 1400,
                    color: '#397BFB',
                    trailColor: '#eee',
                    trailWidth: 1,
                    svgStyle: null
                });
                
                progressBar.set(0.05); // Start with a small progress indication
            }
        });
        
        // Send process request to the server
        fetch(`<?= getenv('APP_BASE_PATH') ?>/api/process`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                operation: 'pipeline',
                fileId: file.imgTasksData.id,
                steps: pipelineSteps
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update progress bar to completion
                if (progressBar) {
                    progressBar.animate(1.0);
                }
                
                document.getElementById('pipeline-progress-text').textContent = 'Processing complete!';
                
                // Close dialog after a short delay
                setTimeout(() => {
                    Swal.close();
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Processing Complete',
                        text: 'The image has been successfully processed',
                        timer: 2000
                    });
                    
                    // Trigger event for results area to refresh
                    const event = new CustomEvent('imgTasks:processingComplete', {
                        detail: { 
                            result: data.result 
                        }
                    });
                    document.dispatchEvent(event);
                }, 1000);
            } else {
                throw new Error(data.error || 'Unknown processing error');
            }
        })
        .catch(error => {
            console.error('Pipeline processing error:', error);
            
            // Update progress bar to error state
            if (progressBar) {
                progressBar.set(0);
                progressBar.path.setAttribute('stroke', '#e74c3c');
            }
            
            document.getElementById('pipeline-progress-text').textContent = 'Processing failed!';
            
            // Close dialog after a short delay
            setTimeout(() => {
                Swal.close();
                
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Processing Failed',
                    text: error.message || 'An error occurred during processing',
                    timer: 5000
                });
            }, 1000);
        });
    }
    
    // Load saved pipeline from session storage if available
    function loadSavedPipeline() {
        try {
            const savedPipeline = sessionStorage.getItem(`${pipelineId}-data`);
            if (savedPipeline) {
                const pipelineData = JSON.parse(savedPipeline);
                if (Array.isArray(pipelineData) && pipelineData.length > 0) {
                    pipelineData.forEach(step => {
                        addPipelineStep(step.type, step.options);
                    });
                }
            }
        } catch (error) {
            console.error('Error loading saved pipeline:', error);
        }
    }
    
    // Save pipeline to session storage
    function savePipeline() {
        try {
            const steps = Array.from(stepsList.children);
            const pipelineData = steps.map(step => {
                const stepType = step.dataset.type;
                const optionsContainer = step.querySelector('.step-options-container');
                let options = {};
                
                if (stepType === 'optimize') {
                    const qualitySlider = optionsContainer.querySelector('.optimize-quality');
                    const formatSelect = optionsContainer.querySelector('.optimize-format');
                    const resizeSelect = optionsContainer.querySelector('.optimize-resize');
                    const stripMetadata = optionsContainer.querySelector('.optimize-strip-metadata');
                    
                    options.quality = parseInt(qualitySlider.value);
                    options.format = formatSelect.value;
                    
                    if (resizeSelect.value === 'custom') {
                        const customResizeValue = optionsContainer.querySelector('.optimize-custom-resize-value');
                        options.resize = customResizeValue.value;
                    } else if (resizeSelect.value) {
                        options.resize = resizeSelect.value;
                    }
                    
                    options.strip_metadata = stripMetadata.checked;
                } else if (stepType === 'remove_background') {
                    const alphaMattingCheckbox = optionsContainer.querySelector('.remove-bg-alpha-matting');
                    
                    options.alpha_matting = alphaMattingCheckbox.checked;
                    
                    if (options.alpha_matting) {
                        const fgThreshold = optionsContainer.querySelector('.remove-bg-fg-threshold');
                        const bgThreshold = optionsContainer.querySelector('.remove-bg-bg-threshold');
                        const erodeSize = optionsContainer.querySelector('.remove-bg-erode-size');
                        
                        options.alpha_matting_foreground_threshold = parseInt(fgThreshold.value);
                        options.alpha_matting_background_threshold = parseInt(bgThreshold.value);
                        options.alpha_matting_erode_size = parseInt(erodeSize.value);
                    }
                }
                
                return {
                    type: stepType,
                    options: options
                };
            });
            
            sessionStorage.setItem(`${pipelineId}-data`, JSON.stringify(pipelineData));
        } catch (error) {
            console.error('Error saving pipeline:', error);
        }
    }
    
    // Save pipeline when changes are made
    function setupPipelineSaving() {
        // Save when steps are added, removed, or reordered
        const observer = new MutationObserver(savePipeline);
        observer.observe(stepsList, { childList: true, subtree: true });
        
        // Save when option values change (using event delegation)
        stepsList.addEventListener('change', savePipeline);
        stepsList.addEventListener('input', savePipeline);
    }
    
    // Load saved pipeline on init
    loadSavedPipeline();
    
    // Setup pipeline saving
    setupPipelineSaving();
});
</script>