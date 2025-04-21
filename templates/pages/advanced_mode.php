<?php
/**
 * Advanced Pipeline Mode View
 * 
 * Complex interface for creating custom processing pipelines with multiple operations.
 * Allows users to chain multiple processes in a specific order with fine-grained control.
 * 
 * @var string $mode Current application mode
 * @var \ImgTasks\Utils\SessionManager $sessionManager Session manager instance
 */

// Set page title
$pageTitle = "Advanced Pipeline - Custom Image Processing";
?>

<div class="max-w-5xl mx-auto">
    <!-- Mode Introduction -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-3">Advanced Pipeline Mode</h1>
        <p class="text-gray-300">Create custom processing pipelines by combining multiple operations in any order. Drag and drop to reorder steps in your workflow.</p>
    </div>
    
    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left Column: Upload and Pipeline -->
        <div>
            <!-- Upload Panel -->
            <div class="card bg-gray-800 rounded-xl shadow-md mb-6">
                <div class="mb-4">
                    <h2 class="text-xl font-semibold text-white mb-2">1. Upload Image</h2>
                    <p class="text-gray-400 text-sm">Start by uploading an image to process</p>
                </div>
                
                <?php 
                // Include the dropzone component (single file for pipeline)
                $dropzoneId = 'advanced-mode-dropzone';
                $uploadUrl = getenv('APP_BASE_PATH') . '/api/upload';
                $options = [
                    'maxFiles' => 1,
                    'dropzoneText' => 'Drag and drop an image here or click to browse'
                ];
                include dirname(__DIR__) . '/partials/dropzone_area.php'; 
                ?>
            </div>
            
            <!-- Pipeline Builder -->
            <?php 
            // Include the pipeline component
            $pipelineId = 'advanced-mode-pipeline';
            include dirname(__DIR__) . '/partials/pipeline_step.php'; 
            ?>

            <!-- Save Pipeline Button -->
            <div class="flex justify-between mt-6">
                <button id="save-pipeline-btn" class="bg-gray-700 hover:bg-gray-600 text-white py-2 px-4 rounded-lg flex items-center transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    Save Pipeline
                </button>
                
                <button id="load-pipeline-btn" class="bg-gray-700 hover:bg-gray-600 text-white py-2 px-4 rounded-lg flex items-center transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Load Pipeline
                </button>
            </div>
        </div>
        
        <!-- Right Column: Pipeline Information and Results -->
        <div>
            <!-- Pipeline Information Panel -->
            <div class="card bg-gray-800 rounded-xl shadow-md mb-6">
                <div class="mb-4">
                    <h2 class="text-xl font-semibold text-white mb-2">Pipeline Information</h2>
                    <p class="text-gray-400 text-sm">Summary of your current processing workflow</p>
                </div>
                
                <div id="pipeline-info-empty" class="p-4 bg-gray-700 rounded-lg text-center">
                    <p class="text-gray-300">No pipeline steps defined yet</p>
                    <p class="text-sm text-gray-400 mt-1">Add steps to your pipeline to see information here</p>
                </div>
                
                <div id="pipeline-info-content" class="hidden">
                    <div class="p-4 bg-gray-700 rounded-lg">
                        <ul id="pipeline-summary" class="space-y-2">
                            <!-- Summary items will be added dynamically -->
                        </ul>
                        
                        <div class="mt-4 pt-4 border-t border-gray-600">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-300">Total steps:</span>
                                <span id="pipeline-step-count" class="text-white font-medium">0</span>
                            </div>
                            <div class="flex justify-between text-sm mt-1">
                                <span class="text-gray-300">Estimated processing time:</span>
                                <span id="pipeline-est-time" class="text-white font-medium">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Processing Tips -->
                    <div class="mt-6">
                        <h3 class="text-lg font-medium text-white mb-2">Processing Tips</h3>
                        <ul class="text-sm text-gray-400 space-y-2 pl-6 list-disc">
                            <li>For best results, place <span class="text-accent">background removal</span> before optimization steps</li>
                            <li>Use multiple <span class="text-accent">optimize</span> steps with different settings for advanced control</li>
                            <li>Save your pipeline for future use with similar images</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Results Panel -->
            <?php 
            // Include the results component
            $resultsId = 'advanced-mode-results';
            $autoRefresh = false; 
            include dirname(__DIR__) . '/partials/results_area.php'; 
            ?>
        </div>
    </div>
</div>

<!-- Saved Pipelines Modal Template -->
<template id="saved-pipelines-template">
    <div class="text-left">
        <p class="mb-4">Select a pipeline to load:</p>
        <div id="saved-pipelines-list" class="max-h-60 overflow-y-auto space-y-3 mb-4">
            <!-- Saved pipelines will be listed here -->
            <div class="text-center text-gray-500 py-6">
                No saved pipelines found
            </div>
        </div>
        <div class="text-xs text-gray-500 mt-2">
            Note: Loading a pipeline will replace your current pipeline steps
        </div>
    </div>
</template>

<!-- Advanced Mode JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const savePipelineBtn = document.getElementById('save-pipeline-btn');
    const loadPipelineBtn = document.getElementById('load-pipeline-btn');
    const pipelineInfoEmpty = document.getElementById('pipeline-info-empty');
    const pipelineInfoContent = document.getElementById('pipeline-info-content');
    const pipelineSummary = document.getElementById('pipeline-summary');
    const pipelineStepCount = document.getElementById('pipeline-step-count');
    const pipelineEstTime = document.getElementById('pipeline-est-time');
    
    // Reference to the pipeline list element (from pipeline_step.php)
    const pipelineList = document.getElementById('advanced-mode-pipeline-list');
    
    // Initialize pipeline information
    updatePipelineInfo();
    
    // Set up MutationObserver to watch for changes to the pipeline
    const observer = new MutationObserver(updatePipelineInfo);
    observer.observe(pipelineList, { childList: true, subtree: true });
    
    // Save Pipeline button click handler
    savePipelineBtn.addEventListener('click', savePipeline);
    
    // Load Pipeline button click handler
    loadPipelineBtn.addEventListener('click', showLoadPipelineDialog);
    
    // Update the pipeline information panel
    function updatePipelineInfo() {
        const steps = pipelineList ? Array.from(pipelineList.children) : [];
        
        // Update step count
        pipelineStepCount.textContent = steps.length;
        
        // Show/hide appropriate info panels
        if (steps.length === 0) {
            pipelineInfoEmpty.classList.remove('hidden');
            pipelineInfoContent.classList.add('hidden');
            return;
        } else {
            pipelineInfoEmpty.classList.add('hidden');
            pipelineInfoContent.classList.remove('hidden');
        }
        
        // Clear existing summary
        pipelineSummary.innerHTML = '';
        
        // Add each step to the summary
        steps.forEach((step, index) => {
            const stepType = step.dataset.type;
            const stepTitle = step.querySelector('.step-title').textContent;
            
            // Create summary item
            const listItem = document.createElement('li');
            listItem.className = 'flex items-center';
            
            // Step number indicator
            const numberSpan = document.createElement('span');
            numberSpan.className = 'flex items-center justify-center w-5 h-5 rounded-full bg-gray-600 text-white text-xs font-bold mr-2';
            numberSpan.textContent = index + 1;
            
            // Step description
            const descSpan = document.createElement('span');
            descSpan.className = 'text-gray-300';
            
            // Add specific details based on step type
            if (stepType === 'optimize') {
                const qualitySlider = step.querySelector('.optimize-quality');
                const formatSelect = step.querySelector('.optimize-format');
                const quality = qualitySlider ? qualitySlider.value : '80';
                const format = formatSelect ? formatSelect.value : 'auto';
                
                descSpan.textContent = `${stepTitle} (${quality}% quality, ${format.toUpperCase()})`;
            } else if (stepType === 'remove_background') {
                const alphaMattingCheckbox = step.querySelector('.remove-bg-alpha-matting');
                const alphaMatting = alphaMattingCheckbox && alphaMattingCheckbox.checked ? 'with alpha matting' : '';
                
                descSpan.textContent = `${stepTitle} ${alphaMatting}`;
            } else {
                descSpan.textContent = stepTitle;
            }
            
            listItem.appendChild(numberSpan);
            listItem.appendChild(descSpan);
            pipelineSummary.appendChild(listItem);
        });
        
        // Update estimated processing time
        let estTimeInSeconds = steps.length * 3; // Base estimate: 3 seconds per step
        
        // Add extra time for more complex operations
        steps.forEach(step => {
            const stepType = step.dataset.type;
            if (stepType === 'remove_background') {
                estTimeInSeconds += 10; // Background removal takes longer
                
                const alphaMattingCheckbox = step.querySelector('.remove-bg-alpha-matting');
                if (alphaMattingCheckbox && alphaMattingCheckbox.checked) {
                    estTimeInSeconds += 5; // Alpha matting adds extra time
                }
            }
        });
        
        // Format the time estimate
        if (estTimeInSeconds < 60) {
            pipelineEstTime.textContent = `~${estTimeInSeconds} seconds`;
        } else {
            const minutes = Math.floor(estTimeInSeconds / 60);
            const seconds = estTimeInSeconds % 60;
            pipelineEstTime.textContent = `~${minutes} min ${seconds} sec`;
        }
    }
    
    // Save the current pipeline
    function savePipeline() {
        // Get pipeline steps
        const steps = pipelineList ? Array.from(pipelineList.children) : [];
        
        if (steps.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Empty Pipeline',
                text: 'Please add at least one step to your pipeline before saving',
                timer: 3000
            });
            return;
        }
        
        // Prompt for pipeline name
        Swal.fire({
            title: 'Save Pipeline',
            input: 'text',
            inputLabel: 'Pipeline Name',
            inputPlaceholder: 'Enter a name for this pipeline',
            showCancelButton: true,
            inputValidator: (value) => {
                if (!value) {
                    return 'Please enter a name for your pipeline';
                }
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const pipelineName = result.value;
                
                // Extract pipeline data
                const pipelineData = getPipelineData();
                
                // Get existing saved pipelines
                let savedPipelines = getSavedPipelines();
                
                // Add new pipeline
                savedPipelines[pipelineName] = {
                    name: pipelineName,
                    steps: pipelineData,
                    created: new Date().toISOString()
                };
                
                // Save to localStorage
                localStorage.setItem('imgTasks_savedPipelines', JSON.stringify(savedPipelines));
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Pipeline Saved',
                    text: `Your pipeline "${pipelineName}" has been saved`,
                    timer: 2000
                });
            }
        });
    }
    
    // Get the current pipeline data
    function getPipelineData() {
        const steps = pipelineList ? Array.from(pipelineList.children) : [];
        
        return steps.map(step => {
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
    }
    
    // Get saved pipelines from localStorage
    function getSavedPipelines() {
        try {
            const savedData = localStorage.getItem('imgTasks_savedPipelines');
            return savedData ? JSON.parse(savedData) : {};
        } catch (error) {
            console.error('Error loading saved pipelines:', error);
            return {};
        }
    }
    
    // Show dialog to load a saved pipeline
    function showLoadPipelineDialog() {
        // Get saved pipelines
        const savedPipelines = getSavedPipelines();
        const pipelineCount = Object.keys(savedPipelines).length;
        
        if (pipelineCount === 0) {
            Swal.fire({
                icon: 'info',
                title: 'No Saved Pipelines',
                text: 'You don\'t have any saved pipelines yet',
                timer: 3000
            });
            return;
        }
        
        // Create template for the dialog
        const template = document.getElementById('saved-pipelines-template');
        const dialogContent = template.content.cloneNode(true);
        
        // Get the pipelines list container
        const pipelinesList = dialogContent.getElementById('saved-pipelines-list');
        
        // Clear the default content
        pipelinesList.innerHTML = '';
        
        // Add each saved pipeline to the list
        Object.values(savedPipelines).forEach(pipeline => {
            const pipelineItem = document.createElement('div');
            pipelineItem.className = 'saved-pipeline-item p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-100';
            pipelineItem.dataset.name = pipeline.name;
            
            // Pipeline name and metadata
            const createdDate = new Date(pipeline.created);
            const stepCount = pipeline.steps.length;
            
            pipelineItem.innerHTML = `
                <div class="flex justify-between items-center">
                    <div>
                        <span class="font-medium block">${pipeline.name}</span>
                        <span class="text-xs text-gray-600">
                            ${stepCount} step${stepCount !== 1 ? 's' : ''} · Created ${createdDate.toLocaleDateString()}
                        </span>
                    </div>
                    <button class="delete-pipeline-btn text-red-500 p-1 hover:text-red-700" data-name="${pipeline.name}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            `;
            
            pipelinesList.appendChild(pipelineItem);
        });
        
        // Show the dialog
        Swal.fire({
            title: 'Load Pipeline',
            html: dialogContent,
            showCancelButton: true,
            confirmButtonText: 'Load Selected',
            confirmButtonColor: '#397BFB',
            cancelButtonText: 'Cancel',
            didOpen: () => {
                // Add click handler for pipeline items
                const pipelineItems = document.querySelectorAll('.saved-pipeline-item');
                let selectedPipeline = null;
                
                pipelineItems.forEach(item => {
                    item.addEventListener('click', function(e) {
                        // Ignore clicks on the delete button
                        if (e.target.closest('.delete-pipeline-btn')) {
                            return;
                        }
                        
                        // Clear previous selection
                        pipelineItems.forEach(pi => pi.classList.remove('bg-blue-50', 'border-blue-300'));
                        
                        // Mark this item as selected
                        this.classList.add('bg-blue-50', 'border-blue-300');
                        selectedPipeline = this.dataset.name;
                    });
                });
                
                // Add click handler for delete buttons
                document.querySelectorAll('.delete-pipeline-btn').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const pipelineName = this.dataset.name;
                        
                        Swal.fire({
                            title: 'Delete Pipeline',
                            text: `Are you sure you want to delete "${pipelineName}"?`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, delete it',
                            confirmButtonColor: '#d33',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Delete the pipeline
                                const savedPipelines = getSavedPipelines();
                                delete savedPipelines[pipelineName];
                                localStorage.setItem('imgTasks_savedPipelines', JSON.stringify(savedPipelines));
                                
                                // Close and reopen the dialog to refresh
                                Swal.close();
                                setTimeout(showLoadPipelineDialog, 300);
                            }
                        });
                    });
                });
            },
            preConfirm: () => {
                // Get selected pipeline
                const selectedItem = document.querySelector('.saved-pipeline-item.bg-blue-50');
                
                if (!selectedItem) {
                    Swal.showValidationMessage('Please select a pipeline');
                    return false;
                }
                
                return selectedItem.dataset.name;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const pipelineName = result.value;
                const savedPipelines = getSavedPipelines();
                
                if (savedPipelines[pipelineName]) {
                    loadPipeline(savedPipelines[pipelineName]);
                }
            }
        });
    }
    
    // Load a pipeline into the UI
    function loadPipeline(pipeline) {
        // Reference to external functions defined in pipeline_step.php
        const addPipelineStep = window.addPipelineStep || window[`addPipelineStep_${pipelineId}`];
        const clearPipeline = window.clearPipeline || window[`clearPipeline_${pipelineId}`];
        
        if (!addPipelineStep || !clearPipeline) {
            console.error('Pipeline functions not found');
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Could not load pipeline. Please try refreshing the page.',
                timer: 3000
            });
            return;
        }
        
        // Clear existing pipeline
        clearPipeline();
        
        // Add each step from the saved pipeline
        pipeline.steps.forEach(step => {
            addPipelineStep(step.type, step.options);
        });
        
        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Pipeline Loaded',
            text: `Successfully loaded pipeline "${pipeline.name}"`,
            timer: 2000
        });
    }
});
</script>