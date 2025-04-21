<?php
/**
 * Results area component
 * 
 * Displays processed images and provides download options
 * 
 * @param string $resultsId - Unique ID for this results area
 * @param bool $autoRefresh - Whether to automatically refresh results
 * @param int $refreshInterval - Refresh interval in milliseconds
 */

// Default options
$resultsId = $resultsId ?? 'processing-results';
$autoRefresh = $autoRefresh ?? false;
$refreshInterval = $refreshInterval ?? 5000; // 5 seconds
?>

<div id="<?= $resultsId ?>" class="mt-8">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold text-white">Processing Results</h3>
        <button id="<?= $resultsId ?>-refresh" class="bg-gray-700 hover:bg-gray-600 text-white text-sm py-1 px-3 rounded flex items-center transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
        </button>
    </div>

    <div id="<?= $resultsId ?>-loading" class="hidden p-6 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-button mb-2"></div>
        <p class="text-gray-300">Loading results...</p>
    </div>

    <div id="<?= $resultsId ?>-empty" class="bg-gray-700 rounded-xl p-6 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p class="text-gray-300 mb-2">No processed images yet</p>
        <p class="text-sm text-gray-400">Processed images will appear here after you run operations</p>
    </div>

    <div id="<?= $resultsId ?>-container" class="hidden">
        <div id="<?= $resultsId ?>-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <!-- Results will be loaded here dynamically -->
        </div>
    </div>
</div>

<!-- Result item template -->
<template id="<?= $resultsId ?>-item-template">
    <div class="result-item bg-gray-800 rounded-xl overflow-hidden shadow-md transition-transform duration-200 hover:scale-[1.02]">
        <div class="relative h-40 bg-gray-900">
            <img class="result-image w-full h-full object-contain" src="" alt="Processed image">
            <div class="absolute top-2 right-2">
                <span class="result-operation inline-block bg-button text-white text-xs px-2 py-1 rounded-full"></span>
            </div>
        </div>
        <div class="p-4">
            <div class="flex justify-between items-start mb-2">
                <div class="max-w-[70%]">
                    <h4 class="result-name text-white font-medium text-sm truncate"></h4>
                    <p class="result-time text-gray-400 text-xs"></p>
                </div>
                <div class="result-size text-right">
                    <span class="text-accent2 text-xs font-semibold"></span>
                </div>
            </div>
            <div class="flex justify-between mt-3">
                <a href="#" class="result-download bg-button hover:bg-blue-600 text-white text-xs py-1 px-3 rounded flex items-center transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download
                </a>
                <button class="result-delete bg-red-600 hover:bg-red-700 text-white text-xs py-1 px-3 rounded flex items-center transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete
                </button>
            </div>
        </div>
    </div>
</template>

<!-- Results area JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const resultsId = '<?= $resultsId ?>';
    const resultsContainer = document.getElementById(`${resultsId}-container`);
    const resultsGrid = document.getElementById(`${resultsId}-grid`);
    const resultsEmpty = document.getElementById(`${resultsId}-empty`);
    const resultsLoading = document.getElementById(`${resultsId}-loading`);
    const refreshButton = document.getElementById(`${resultsId}-refresh`);
    const template = document.getElementById(`${resultsId}-item-template`);
    
    let refreshTimeout = null;
    let isLoading = false;
    
    // Set up auto-refresh if enabled
    const autoRefresh = <?= $autoRefresh ? 'true' : 'false' ?>;
    const refreshInterval = <?= $refreshInterval ?>;
    
    if (autoRefresh) {
        scheduleRefresh();
    }
    
    // Load results on page load
    loadResults();
    
    // Refresh button click handler
    refreshButton.addEventListener('click', function() {
        loadResults();
    });
    
    // Listen for processing completion events
    document.addEventListener('imgTasks:processingComplete', function(e) {
        loadResults();
    });
    
    // Function to load results from the server
    function loadResults() {
        if (isLoading) return;
        isLoading = true;
        
        // Clear any pending auto-refresh
        if (refreshTimeout) {
            clearTimeout(refreshTimeout);
            refreshTimeout = null;
        }
        
        // Show loading indicator
        resultsEmpty.classList.add('hidden');
        resultsContainer.classList.add('hidden');
        resultsLoading.classList.remove('hidden');
        
        // Make API request
        fetch(`<?= getenv('APP_BASE_PATH') ?>/api/results`)
            .then(response => response.json())
            .then(data => {
                // Clear existing results
                resultsGrid.innerHTML = '';
                
                if (data.success && data.results && data.results.length > 0) {
                    // Render results
                    data.results.forEach(result => {
                        const resultElement = createResultElement(result);
                        resultsGrid.appendChild(resultElement);
                    });
                    
                    // Show results container, hide empty state
                    resultsContainer.classList.remove('hidden');
                    resultsEmpty.classList.add('hidden');
                } else {
                    // Show empty state, hide results container
                    resultsContainer.classList.add('hidden');
                    resultsEmpty.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error loading results:', error);
                // Show empty state and error
                resultsContainer.classList.add('hidden');
                resultsEmpty.classList.remove('hidden');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load processing results',
                    timer: 3000
                });
            })
            .finally(() => {
                // Hide loading indicator
                resultsLoading.classList.add('hidden');
                isLoading = false;
                
                // Schedule next auto-refresh if enabled
                if (autoRefresh) {
                    scheduleRefresh();
                }
            });
    }
    
    // Function to create a result element from the template
    function createResultElement(result) {
        // Clone the template
        const resultElement = template.content.cloneNode(true).firstElementChild;
        
        // Set result data
        const image = resultElement.querySelector('.result-image');
        image.src = `<?= getenv('APP_BASE_PATH') ?>/${result.path}`;
        image.alt = result.name;
        
        // Set operation badge
        const operationBadge = resultElement.querySelector('.result-operation');
        operationBadge.textContent = formatOperationName(result.operation);
        
        // Set name and time
        resultElement.querySelector('.result-name').textContent = result.name;
        resultElement.querySelector('.result-time').textContent = formatTimestamp(result.timestamp);
        
        // Set size
        const sizeElement = resultElement.querySelector('.result-size');
        if (result.size) {
            const formattedSize = formatFileSize(result.size);
            sizeElement.textContent = formattedSize;
            
            // Add original size comparison if available
            if (result.original_size) {
                const originalSize = formatFileSize(result.original_size);
                const savingsPercent = calculateSavingsPercent(result.original_size, result.size);
                
                if (savingsPercent > 0) {
                    const savingsSpan = document.createElement('span');
                    savingsSpan.className = 'block text-green-400 text-xs mt-1';
                    savingsSpan.textContent = `-${savingsPercent}%`;
                    sizeElement.appendChild(savingsSpan);
                }
            }
        }
        
        // Set download link
        const downloadLink = resultElement.querySelector('.result-download');
        downloadLink.href = `<?= getenv('APP_BASE_PATH') ?>/${result.path}`;
        downloadLink.setAttribute('download', result.name);
        
        // Set delete button
        const deleteButton = resultElement.querySelector('.result-delete');
        deleteButton.addEventListener('click', function() {
            deleteResult(result.id);
        });
        
        return resultElement;
    }
    
    // Function to format operation name for display
    function formatOperationName(operation) {
        switch (operation) {
            case 'optimize':
                return 'Optimized';
            case 'remove_background':
                return 'No BG';
            case 'pipeline':
                return 'Pipeline';
            default:
                return operation.charAt(0).toUpperCase() + operation.slice(1);
        }
    }
    
    // Function to format timestamp
    function formatTimestamp(timestamp) {
        if (!timestamp) return '';
        
        const date = new Date(timestamp * 1000);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    
    // Function to format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }
    
    // Calculate savings percentage
    function calculateSavingsPercent(originalSize, newSize) {
        if (!originalSize || !newSize || originalSize <= 0) return 0;
        
        const savings = ((originalSize - newSize) / originalSize) * 100;
        return Math.round(savings);
    }
    
    // Function to delete a result
    function deleteResult(resultId) {
        if (!resultId) return;
        
        // Ask for confirmation
        Swal.fire({
            title: 'Delete Result',
            text: 'Are you sure you want to delete this processed image?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Send delete request
                fetch(`<?= getenv('APP_BASE_PATH') ?>/api/delete-result`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ resultId: resultId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload results
                        loadResults();
                        
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'The result has been deleted.',
                            timer: 2000
                        });
                    } else {
                        throw new Error(data.error || 'Failed to delete result');
                    }
                })
                .catch(error => {
                    console.error('Error deleting result:', error);
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to delete result',
                        timer: 3000
                    });
                });
            }
        });
    }
    
    // Function to schedule auto-refresh
    function scheduleRefresh() {
        if (refreshTimeout) {
            clearTimeout(refreshTimeout);
        }
        
        refreshTimeout = setTimeout(() => {
            loadResults();
        }, refreshInterval);
    }
});
</script>