<?php
/**
 * ReplicateService.php
 * 
 * Service for interacting with the Replicate API for AI-based image processing,
 * particularly focused on background removal functionality.
 * 
 * @author Piotr Tamulewicz <pt@petertam.pro>
 */

namespace ImgTasks\Services;

class ReplicateService {
    /**
     * Base URL for the Replicate API
     * 
     * @var string
     */
    private $apiBaseUrl = 'https://api.replicate.com/v1';
    
    /**
     * API token for authentication
     * 
     * @var string
     */
    private $apiToken;
    
    /**
     * File handler instance
     * 
     * @var \ImgTasks\Utils\FileHandler
     */
    private $fileHandler;
    
    /**
     * Model ID for background removal
     * 
     * @var string
     */
    private $backgroundRemovalModel = 'ilkerc/rembg:535fd87bc8a2afd91b382a2a16125d09a107f97b521de6d7a19571764dd757a5';
    
    /**
     * Constructor
     * 
     * @param string $apiToken API token for Replicate
     * @param \ImgTasks\Utils\FileHandler $fileHandler File handler instance
     */
    public function __construct($apiToken, $fileHandler) {
        $this->apiToken = $apiToken;
        $this->fileHandler = $fileHandler;
    }
    
    /**
     * Remove the background from an image
     * 
     * @param string $imagePath Path to the image file
     * @param array $options Additional options
     * @return array Result information
     * @throws \Exception
     */
    public function removeBackground($imagePath, $options = []) {
        try {
            // Check if file exists
            if (!file_exists($imagePath)) {
                throw new \Exception("Image file not found: {$imagePath}");
            }
            
            // Encode image as base64
            $base64Image = $this->encodeImageToBase64($imagePath);
            
            // Create prediction
            $predictionId = $this->createPrediction($base64Image, $options);
            
            // Wait for prediction to complete
            $result = $this->waitForPrediction($predictionId);
            
            // Check prediction status
            if ($result['status'] !== 'succeeded') {
                $error = isset($result['error']) ? $result['error'] : 'Unknown error';
                throw new \Exception("Background removal failed: {$error}");
            }
            
            // Get the output URL
            if (!isset($result['output']) || !is_string($result['output'])) {
                throw new \Exception("Invalid output from background removal model");
            }
            
            $outputUrl = $result['output'];
            
            // Download the processed image
            $processedImagePath = $this->downloadProcessedImage($outputUrl);
            
            // Return result information
            return [
                'path' => $processedImagePath,
                'size' => filesize($processedImagePath),
                'original_size' => filesize($imagePath)
            ];
        } catch (\Exception $e) {
            // Log error
            error_log("ReplicateService Error: " . $e->getMessage());
            
            // Re-throw the exception
            throw $e;
        }
    }
    
    /**
     * Create a prediction on Replicate
     * 
     * @param string $base64Image Base64 encoded image
     * @param array $options Additional options
     * @return string Prediction ID
     * @throws \Exception
     */
    private function createPrediction($base64Image, $options = []) {
        // Default options
        $defaultOptions = [
            'alpha_matting' => false,
            'alpha_matting_foreground_threshold' => 240,
            'alpha_matting_background_threshold' => 10,
            'alpha_matting_erode_size' => 10
        ];
        
        // Merge defaults with provided options
        $options = array_merge($defaultOptions, $options);
        
        // Prepare the request payload
        $payload = [
            'version' => $this->backgroundRemovalModel,
            'input' => array_merge(
                ['image' => "data:image/jpeg;base64,{$base64Image}"],
                $options
            )
        ];
        
        // Initialize cURL session
        $curl = curl_init();
        
        // Set cURL options
        curl_setopt_array($curl, [
            CURLOPT_URL => "{$this->apiBaseUrl}/predictions",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Authorization: Token ' . $this->apiToken,
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 30
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
        
        // Parse the response
        $result = json_decode($response, true);
        
        if (!$result || !isset($result['id'])) {
            throw new \Exception("Failed to create prediction");
        }
        
        return $result['id'];
    }
    
    /**
     * Wait for a prediction to complete
     * 
     * @param string $predictionId Prediction ID
     * @param int $maxAttempts Maximum number of polling attempts
     * @param int $interval Polling interval in seconds
     * @return array Prediction result
     * @throws \Exception
     */
    private function waitForPrediction($predictionId, $maxAttempts = 30, $interval = 2) {
        $attempts = 0;
        
        while ($attempts < $maxAttempts) {
            // Get prediction status
            $result = $this->getPrediction($predictionId);
            
            // Check if prediction is complete
            if (in_array($result['status'], ['succeeded', 'failed', 'canceled'])) {
                return $result;
            }
            
            // Wait before polling again
            sleep($interval);
            $attempts++;
        }
        
        throw new \Exception("Prediction timed out");
    }
    
    /**
     * Get a prediction's status and results
     * 
     * @param string $predictionId Prediction ID
     * @return array Prediction information
     * @throws \Exception
     */
    private function getPrediction($predictionId) {
        // Initialize cURL session
        $curl = curl_init();
        
        // Set cURL options
        curl_setopt_array($curl, [
            CURLOPT_URL => "{$this->apiBaseUrl}/predictions/{$predictionId}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Token ' . $this->apiToken,
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 10
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
        
        // Parse the response
        $result = json_decode($response, true);
        
        if (!$result) {
            throw new \Exception("Failed to get prediction status");
        }
        
        return $result;
    }
    
    /**
     * Encode an image to base64
     * 
     * @param string $imagePath Path to the image file
     * @return string Base64 encoded image
     * @throws \Exception
     */
    private function encodeImageToBase64($imagePath) {
        // Read the image file
        $imageData = file_get_contents($imagePath);
        
        if ($imageData === false) {
            throw new \Exception("Failed to read image file");
        }
        
        // Encode to base64
        return base64_encode($imageData);
    }
    
    /**
     * Download a processed image from Replicate
     * 
     * @param string $url URL of the processed image
     * @return string Path to the downloaded image
     * @throws \Exception
     */
    private function downloadProcessedImage($url) {
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
            throw new \Exception("Failed to download processed image: {$error}");
        }
        
        if ($statusCode >= 400) {
            throw new \Exception("Failed to download processed image: HTTP error {$statusCode}");
        }
        
        return $tempPath;
    }
}