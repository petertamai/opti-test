<?php
/**
 * OptimizationService.php
 * 
 * Service for interacting with the custom image optimization API.
 * Handles sending images for optimization and processing the results.
 * 
 * @author Piotr Tamulewicz <pt@petertam.pro>
 */

namespace ImgTasks\Services;

class OptimizationService {
    /**
     * Base URL for the optimization API
     * 
     * @var string
     */
    private $apiBaseUrl;
    
    /**
     * API key for authentication
     * 
     * @var string
     */
    private $apiKey;
    
    /**
     * File handler instance
     * 
     * @var \ImgTasks\Utils\FileHandler
     */
    private $fileHandler;
    
    /**
     * Constructor
     * 
     * @param string $apiBaseUrl Base URL for the optimization API
     * @param string $apiKey API key for authentication
     * @param \ImgTasks\Utils\FileHandler $fileHandler File handler instance
     */
    public function __construct($apiBaseUrl, $apiKey, $fileHandler) {
        $this->apiBaseUrl = rtrim($apiBaseUrl, '/');
        $this->apiKey = $apiKey;
        $this->fileHandler = $fileHandler;
    }
    
    /**
     * Optimize an image with the specified options
     * 
     * @param string $imagePath Path to the image file
     * @param array $options Optimization options
     * @return array Result information
     * @throws \Exception
     */
    public function optimize($imagePath, $options = []) {
        // Default optimization options
        $defaultOptions = [
            'quality' => 80,
            'format' => 'auto', // auto, jpg, png, webp
            'resize' => null, // null or dimensions like '800x600'
            'strip_metadata' => true
        ];
        
        // Merge defaults with provided options
        $options = array_merge($defaultOptions, $options);
        
        try {
            // Prepare API endpoint
            $endpoint = "{$this->apiBaseUrl}/optimize";
            
            // Check if file exists
            if (!file_exists($imagePath)) {
                throw new \Exception("Image file not found: {$imagePath}");
            }
            
            // Validate image
            $this->validateImage($imagePath);
            
            // Prepare optimization request
            $result = $this->sendOptimizationRequest($endpoint, $imagePath, $options);
            
            // Handle API response
            if (!isset($result['success']) || $result['success'] !== true) {
                $errorMessage = isset($result['error']) ? $result['error'] : 'Unknown optimization error';
                throw new \Exception("Optimization failed: {$errorMessage}");
            }
            
            // Download the optimized image
            $optimizedImageUrl = $result['optimized_url'];
            $optimizedImagePath = $this->downloadOptimizedImage($optimizedImageUrl);
            
            // Return result information
            return [
                'path' => $optimizedImagePath,
                'size' => filesize($optimizedImagePath),
                'original_size' => filesize($imagePath),
                'savings_percent' => isset($result['savings_percent']) ? $result['savings_percent'] : null,
                'format' => isset($result['format']) ? $result['format'] : null
            ];
        } catch (\Exception $e) {
            // Log error
            error_log("OptimizationService Error: " . $e->getMessage());
            
            // Re-throw the exception
            throw $e;
        }
    }
    
    /**
     * Send an optimization request to the API
     * 
     * @param string $endpoint API endpoint URL
     * @param string $imagePath Path to the image file
     * @param array $options Optimization options
     * @return array API response
     * @throws \Exception
     */
    private function sendOptimizationRequest($endpoint, $imagePath, $options) {
        // Initialize cURL session
        $curl = curl_init();
        
        // Prepare file for upload
        $file = new \CURLFile($imagePath);
        
        // Prepare POST data
        $postData = array_merge([
            'api_key' => $this->apiKey,
            'image' => $file
        ], $options);
        
        // Set cURL options
        curl_setopt_array($curl, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
        ]);
        
        // Execute the request
        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        
        // Close cURL session
        curl_close($curl);
        
        // Handle request errors
        if ($error) {
            throw new \Exception("API request failed: {$error}");
        }
        
        if ($statusCode >= 400) {
            throw new \Exception("API request failed with status code {$statusCode}");
        }
        
        // Parse and return the response
        $result = json_decode($response, true);
        
        if (!$result) {
            throw new \Exception("Failed to parse API response");
        }
        
        return $result;
    }
    
    /**
     * Download an optimized image from the API
     * 
     * @param string $url URL of the optimized image
     * @return string Path to the downloaded image
     * @throws \Exception
     */
    private function downloadOptimizedImage($url) {
        // Create a temporary file path
        $tempPath = $this->fileHandler->getTempFilePath();
        
        // Initialize cURL session
        $curl = curl_init();
        
        // Open file for writing
        $fp = fopen($tempPath, 'w+');
        
        if (!$fp) {
            throw new \Exception("Failed to create temporary file for download");
        }
        
        // Set cURL options
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_FILE => $fp,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
        ]);
        
        // Execute the request
        curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        
        // Close file and cURL session
        fclose($fp);
        curl_close($curl);
        
        // Handle download errors
        if ($error) {
            throw new \Exception("Failed to download optimized image: {$error}");
        }
        
        if ($statusCode >= 400) {
            throw new \Exception("Failed to download optimized image: HTTP error {$statusCode}");
        }
        
        return $tempPath;
    }
    
    /**
     * Validate an image file
     * 
     * @param string $imagePath Path to the image file
     * @return bool True if valid
     * @throws \Exception
     */
    private function validateImage($imagePath) {
        // Get image information
        $imageInfo = getimagesize($imagePath);
        
        if (!$imageInfo) {
            throw new \Exception("Invalid image file");
        }
        
        // Check image type
        $validTypes = [
            IMAGETYPE_JPEG,
            IMAGETYPE_PNG,
            IMAGETYPE_GIF,
            IMAGETYPE_WEBP
        ];
        
        if (!in_array($imageInfo[2], $validTypes)) {
            throw new \Exception("Unsupported image format");
        }
        
        return true;
    }
}