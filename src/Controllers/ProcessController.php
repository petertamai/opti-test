<?php
/**
 * ProcessController.php
 * 
 * Handles image processing requests for the ImgTasks application.
 * Coordinates between file uploads, processing services, and results display.
 * 
 * @author Piotr Tamulewicz <pt@petertam.pro>
 */

namespace ImgTasks\Controllers;

class ProcessController {
    /**
     * File handler instance
     * 
     * @var \ImgTasks\Utils\FileHandler
     */
    private $fileHandler;
    
    /**
     * Validator instance
     * 
     * @var \ImgTasks\Utils\Validator
     */
    private $validator;
    
    /**
     * Session manager instance
     * 
     * @var \ImgTasks\Utils\SessionManager
     */
    private $sessionManager;
    
    /**
     * Optimization service instance
     * 
     * @var \ImgTasks\Services\OptimizationService
     */
    private $optimizationService;
    
    /**
     * Replicate service instance
     * 
     * @var \ImgTasks\Services\ReplicateService
     */
    private $replicateService;
    
    /**
     * Constructor
     * 
     * @param \ImgTasks\Utils\FileHandler $fileHandler
     * @param \ImgTasks\Utils\Validator $validator
     * @param \ImgTasks\Utils\SessionManager $sessionManager
     * @param \ImgTasks\Services\OptimizationService $optimizationService
     * @param \ImgTasks\Services\ReplicateService $replicateService
     */
    public function __construct(
        $fileHandler,
        $validator,
        $sessionManager,
        $optimizationService,
        $replicateService
    ) {
        $this->fileHandler = $fileHandler;
        $this->validator = $validator;
        $this->sessionManager = $sessionManager;
        $this->optimizationService = $optimizationService;
        $this->replicateService = $replicateService;
    }
    
    /**
     * Handle file upload
     * Processes the uploaded file and saves it to the temporary uploads directory
     * 
     * @return void
     */
    public function upload() {
        // Check if a file was uploaded
        if (empty($_FILES['file'])) {
            $this->jsonResponse(['success' => false, 'error' => 'No file uploaded'], 400);
            return;
        }
        
        try {
            // Validate upload
            $this->validator->validateUpload($_FILES['file']);
            
            // Handle file upload
            $uploadedFile = $this->fileHandler->handleUpload($_FILES['file']);
            
            // Store file info in session
            $sessionFiles = $this->sessionManager->get('uploaded_files', []);
            $sessionFiles[] = $uploadedFile;
            $this->sessionManager->set('uploaded_files', $sessionFiles);
            
            // Return success response with file details
            $this->jsonResponse([
                'success' => true, 
                'file' => [
                    'id' => $uploadedFile['id'],
                    'name' => $uploadedFile['name'],
                    'path' => $uploadedFile['path'],
                    'size' => $uploadedFile['size'],
                    'type' => $uploadedFile['type'],
                    'thumbnail' => $uploadedFile['thumbnail']
                ]
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Process an image using the requested operation
     * Handles different processing operations based on the request
     * 
     * @return void
     */
    public function process() {
        // Parse input from POST or JSON request body
        $input = $this->getRequestInput();
        
        // Validate required parameters
        if (!isset($input['operation']) || !isset($input['fileId'])) {
            $this->jsonResponse(['success' => false, 'error' => 'Missing required parameters'], 400);
            return;
        }
        
        try {
            // Get file info from session
            $fileId = $input['fileId'];
            $file = $this->findFileById($fileId);
            
            if (!$file) {
                $this->jsonResponse(['success' => false, 'error' => 'File not found'], 404);
                return;
            }
            
            // Process based on operation type
            $result = null;
            switch ($input['operation']) {
                case 'optimize':
                    $options = isset($input['options']) ? $input['options'] : [];
                    $result = $this->optimizationService->optimize($file['path'], $options);
                    break;
                    
                case 'remove_background':
                    $result = $this->replicateService->removeBackground($file['path']);
                    break;
                    
                case 'pipeline':
                    if (!isset($input['steps']) || !is_array($input['steps'])) {
                        $this->jsonResponse(['success' => false, 'error' => 'Pipeline steps required'], 400);
                        return;
                    }
                    $result = $this->processPipeline($file['path'], $input['steps']);
                    break;
                    
                default:
                    $this->jsonResponse(['success' => false, 'error' => 'Unsupported operation'], 400);
                    return;
            }
            
            // Check if processing was successful
            if (!$result || !isset($result['path'])) {
                $this->jsonResponse(['success' => false, 'error' => 'Processing failed'], 500);
                return;
            }
            
            // Create result info
            $resultInfo = [
                'id' => uniqid('result_'),
                'originalId' => $fileId,
                'name' => pathinfo($result['path'], PATHINFO_BASENAME),
                'path' => $result['path'],
                'operation' => $input['operation'],
                'timestamp' => time(),
                'thumbnail' => $this->fileHandler->generateThumbnail($result['path'])
            ];
            
            // Save result info to session
            $results = $this->sessionManager->get('processing_results', []);
            $results[] = $resultInfo;
            $this->sessionManager->set('processing_results', $results);
            
            // Return success response with result details
            $this->jsonResponse([
                'success' => true,
                'result' => $resultInfo
            ]);
        } catch (\Exception $e) {
            // Log error
            error_log('Processing error: ' . $e->getMessage());
            
            $this->jsonResponse([
                'success' => false, 
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get results of processing for the current session
     * 
     * @return void
     */
    public function getResults() {
        $results = $this->sessionManager->get('processing_results', []);
        $this->jsonResponse(['success' => true, 'results' => $results]);
    }
    
    /**
     * Delete a processed result file
     * 
     * @return void
     */
    public function deleteResult() {
        $input = $this->getRequestInput();
        
        if (!isset($input['resultId'])) {
            $this->jsonResponse(['success' => false, 'error' => 'Result ID required'], 400);
            return;
        }
        
        try {
            $resultId = $input['resultId'];
            $results = $this->sessionManager->get('processing_results', []);
            
            // Find the result by ID
            $resultIndex = null;
            foreach ($results as $index => $result) {
                if ($result['id'] === $resultId) {
                    $resultIndex = $index;
                    break;
                }
            }
            
            if ($resultIndex === null) {
                $this->jsonResponse(['success' => false, 'error' => 'Result not found'], 404);
                return;
            }
            
            // Delete the file
            $result = $results[$resultIndex];
            $this->fileHandler->deleteFile($result['path']);
            
            // If there's a thumbnail, delete it too
            if (isset($result['thumbnail'])) {
                $this->fileHandler->deleteFile($result['thumbnail']);
            }
            
            // Remove from session
            array_splice($results, $resultIndex, 1);
            $this->sessionManager->set('processing_results', $results);
            
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Process a pipeline of operations on a file
     * 
     * @param string $filePath Path to input file
     * @param array $steps Pipeline steps with operations and parameters
     * @return array Result information
     * @throws \Exception
     */
    private function processPipeline($filePath, $steps) {
        $currentPath = $filePath;
        $finalResult = null;
        
        foreach ($steps as $step) {
            if (!isset($step['type'])) {
                throw new \Exception('Each pipeline step must have a type');
            }
            
            $options = isset($step['options']) ? $step['options'] : [];
            
            switch ($step['type']) {
                case 'optimize':
                    $result = $this->optimizationService->optimize($currentPath, $options);
                    break;
                    
                case 'remove_background':
                    $result = $this->replicateService->removeBackground($currentPath);
                    break;
                    
                default:
                    throw new \Exception("Unsupported pipeline step type: {$step['type']}");
            }
            
            if (!$result || !isset($result['path'])) {
                throw new \Exception("Failed to process pipeline step: {$step['type']}");
            }
            
            // Update current path for next step (except for the last step)
            $currentPath = $result['path'];
            $finalResult = $result;
        }
        
        return $finalResult;
    }
    
    /**
     * Find a file by its ID in the session
     * 
     * @param string $fileId ID of the file to find
     * @return array|null File information or null if not found
     */
    private function findFileById($fileId) {
        $files = $this->sessionManager->get('uploaded_files', []);
        
        foreach ($files as $file) {
            if ($file['id'] === $fileId) {
                return $file;
            }
        }
        
        return null;
    }
    
    /**
     * Get the request input data from either POST or JSON request body
     * 
     * @return array The request input data
     */
    private function getRequestInput() {
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            return $input ?: [];
        }
        
        return $_POST;
    }
    
    /**
     * Send a JSON response
     * 
     * @param array $data Response data
     * @param int $status HTTP status code
     * @return void
     */
    private function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}