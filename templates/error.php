<?php
/**
 * Error Page Template
 * 
 * Generic error page displayed when an exception occurs or an error is triggered.
 * This template provides user-friendly error information while limiting technical details.
 * 
 * @var int $code HTTP status code (default: 500)
 * @var string $message Error message to display (default: "An error occurred")
 * @var string $description Optional additional description or instructions
 * @var array $debug Optional debug information (only shown if APP_DEBUG is true)
 */

// Default values
$code = $code ?? 500;
$message = $message ?? 'An error occurred';
$description = $description ?? 'We encountered an unexpected issue while processing your request.';
$debug = $debug ?? [];

// Determine error type title based on code
$errorTitle = 'Error';
if ($code >= 400 && $code < 500) {
    $errorTitle = 'Request Error';
} elseif ($code >= 500) {
    $errorTitle = 'Server Error';
}

// Set page title
$pageTitle = "Error $code - $errorTitle";

// Set app debug mode from environment
$appDebug = getenv('APP_DEBUG') === 'true';
?>

<div class="max-w-2xl mx-auto py-10 px-4">
    <div class="bg-gray-800 rounded-xl overflow-hidden shadow-lg">
        <!-- Error Header -->
        <div class="bg-red-600 px-6 py-4">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h1 class="text-2xl font-bold text-white"><?= htmlspecialchars($errorTitle) ?> (<?= $code ?>)</h1>
            </div>
        </div>
        
        <!-- Error Content -->
        <div class="px-6 py-8">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-white mb-3"><?= htmlspecialchars($message) ?></h2>
                <p class="text-gray-300"><?= htmlspecialchars($description) ?></p>
            </div>
            
            <!-- Suggestions -->
            <div class="mt-8">
                <h3 class="text-lg font-medium text-white mb-3">What you can try:</h3>
                <ul class="list-disc pl-5 text-gray-300 space-y-2">
                    <li>Refresh the page and try again</li>
                    <li>Return to the <a href="<?= getenv('APP_BASE_PATH') ?>/" class="text-button hover:underline">home page</a></li>
                    <li>Make sure your internet connection is stable</li>
                    <li>If the issue persists, try again later</li>
                </ul>
            </div>
            
            <!-- Debug Information (only shown in debug mode) -->
            <?php if ($appDebug && !empty($debug)): ?>
                <div class="mt-8 pt-6 border-t border-gray-700">
                    <h3 class="text-lg font-medium text-white mb-3">Debug Information</h3>
                    <div class="bg-gray-900 rounded-lg p-4 font-mono text-xs text-gray-300 overflow-x-auto">
                        <pre><?= htmlspecialchars(print_r($debug, true)) ?></pre>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Error Footer -->
        <div class="px-6 py-4 bg-gray-900 border-t border-gray-700">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-400">Error ID: <?= substr(md5(uniqid() . $code . $message), 0, 8) ?></span>
                <a href="<?= getenv('APP_BASE_PATH') ?>/" class="inline-flex items-center text-button hover:text-blue-400 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-7-7v14" />
                    </svg>
                    Return to Home
                </a>
            </div>
        </div>
    </div>
    
    <!-- Additional Debugging Controls (only shown in debug mode) -->
    <?php if ($appDebug): ?>
        <div class="mt-6 text-center">
            <a href="javascript:history.back()" class="inline-block bg-gray-700 hover:bg-gray-600 text-white rounded-lg px-4 py-2 transition-colors duration-200">
                <span class="inline-flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Go Back
                </span>
            </a>
        </div>
    <?php endif; ?>
</div>