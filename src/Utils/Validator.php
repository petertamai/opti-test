<?php
/**
 * Validator.php
 * 
 * Utility class for validating user input, particularly for file uploads
 * and processing options.
 * 
 * @author Piotr Tamulewicz <pt@petertam.pro>
 */

namespace ImgTasks\Utils;

class Validator {
    /**
     * Maximum allowed file size in bytes
     * 
     * @var int
     */
    private $maxFileSize;
    
    /**
     * Allowed MIME types for uploads
     * 
     * @var array
     */
    private $allowedMimeTypes;
    
    /**
     * Constructor
     * 
     * @param int $maxFileSizeMB Maximum file size in MB
     * @param array $allowedMimeTypes Array of allowed MIME types
     */
    public function __construct($maxFileSizeMB = 25, $allowedMimeTypes = null) {
        $this->maxFileSize = $maxFileSizeMB * 1024 * 1024; // Convert MB to bytes
        
        // Default allowed MIME types if none provided
        $this->allowedMimeTypes = $allowedMimeTypes ?? [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp'
        ];
    }
    
    /**
     * Validate a file upload
     * 
     * @param array $file File information from $_FILES
     * @return bool True if valid
     * @throws \Exception
     */
    public function validateUpload($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception($this->getUploadErrorMessage($file['error']));
        }
        
        // Check if file exists
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new \Exception('Invalid upload');
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new \Exception(
                sprintf('File too large. Maximum size is %s MB', $this->maxFileSize / (1024 * 1024))
            );
        }
        
        // Check MIME type
        $mimeType = $this->getMimeType($file['tmp_name']);
        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            throw new \Exception(
                sprintf('Unsupported file type: %s. Allowed types: %s', 
                    $mimeType, 
                    implode(', ', $this->allowedMimeTypes)
                )
            );
        }
        
        // Validate image dimensions (if applicable)
        if ($this->isImageMimeType($mimeType)) {
            $this->validateImageDimensions($file['tmp_name']);
        }
        
        return true;
    }
    
    /**
     * Validate optimization options
     * 
     * @param array $options Optimization options
     * @return array Validated and sanitized options
     * @throws \Exception
     */
    public function validateOptimizationOptions($options) {
        $validatedOptions = [];
        
        // Validate quality
        if (isset($options['quality'])) {
            $quality = filter_var($options['quality'], FILTER_VALIDATE_INT);
            if ($quality === false || $quality < 1 || $quality > 100) {
                throw new \Exception('Quality must be an integer between 1 and 100');
            }
            $validatedOptions['quality'] = $quality;
        }
        
        // Validate format
        if (isset($options['format'])) {
            $validFormats = ['auto', 'jpg', 'png', 'webp'];
            $format = strtolower(trim($options['format']));
            if (!in_array($format, $validFormats)) {
                throw new \Exception(
                    sprintf('Invalid format. Allowed formats: %s', implode(', ', $validFormats))
                );
            }
            $validatedOptions['format'] = $format;
        }
        
        // Validate resize
        if (isset($options['resize']) && $options['resize'] !== null) {
            if (!preg_match('/^\d+x\d+$/', $options['resize'])) {
                throw new \Exception('Resize must be in the format WIDTHxHEIGHT (e.g., 800x600)');
            }
            $validatedOptions['resize'] = $options['resize'];
        }
        
        // Validate boolean options
        foreach (['strip_metadata'] as $boolOption) {
            if (isset($options[$boolOption])) {
                $validatedOptions[$boolOption] = (bool) $options[$boolOption];
            }
        }
        
        return $validatedOptions;
    }
    
    /**
     * Validate background removal options
     * 
     * @param array $options Background removal options
     * @return array Validated and sanitized options
     * @throws \Exception
     */
    public function validateBackgroundRemovalOptions($options) {
        $validatedOptions = [];
        
        // Validate alpha_matting (boolean)
        if (isset($options['alpha_matting'])) {
            $validatedOptions['alpha_matting'] = (bool) $options['alpha_matting'];
        }
        
        // Validate alpha_matting_foreground_threshold
        if (isset($options['alpha_matting_foreground_threshold'])) {
            $threshold = filter_var($options['alpha_matting_foreground_threshold'], FILTER_VALIDATE_INT);
            if ($threshold === false || $threshold < 0 || $threshold > 255) {
                throw new \Exception('Foreground threshold must be an integer between 0 and 255');
            }
            $validatedOptions['alpha_matting_foreground_threshold'] = $threshold;
        }
        
        // Validate alpha_matting_background_threshold
        if (isset($options['alpha_matting_background_threshold'])) {
            $threshold = filter_var($options['alpha_matting_background_threshold'], FILTER_VALIDATE_INT);
            if ($threshold === false || $threshold < 0 || $threshold > 255) {
                throw new \Exception('Background threshold must be an integer between 0 and 255');
            }
            $validatedOptions['alpha_matting_background_threshold'] = $threshold;
        }
        
        // Validate alpha_matting_erode_size
        if (isset($options['alpha_matting_erode_size'])) {
            $size = filter_var($options['alpha_matting_erode_size'], FILTER_VALIDATE_INT);
            if ($size === false || $size < 0) {
                throw new \Exception('Erode size must be a positive integer');
            }
            $validatedOptions['alpha_matting_erode_size'] = $size;
        }
        
        return $validatedOptions;
    }
    
    /**
     * Validate pipeline steps
     * 
     * @param array $steps Pipeline steps
     * @return array Validated pipeline steps
     * @throws \Exception
     */
    public function validatePipelineSteps($steps) {
        if (!is_array($steps) || empty($steps)) {
            throw new \Exception('Pipeline must contain at least one step');
        }
        
        $validatedSteps = [];
        $allowedStepTypes = ['optimize', 'remove_background'];
        
        foreach ($steps as $index => $step) {
            if (!isset($step['type'])) {
                throw new \Exception(sprintf('Step %d is missing a type', $index + 1));
            }
            
            if (!in_array($step['type'], $allowedStepTypes)) {
                throw new \Exception(sprintf(
                    'Invalid step type: %s. Allowed types: %s',
                    $step['type'],
                    implode(', ', $allowedStepTypes)
                ));
            }
            
            $validatedStep = [
                'type' => $step['type'],
                'options' => []
            ];
            
            // Validate step options based on type
            if (isset($step['options']) && is_array($step['options'])) {
                switch ($step['type']) {
                    case 'optimize':
                        $validatedStep['options'] = $this->validateOptimizationOptions($step['options']);
                        break;
                    case 'remove_background':
                        $validatedStep['options'] = $this->validateBackgroundRemovalOptions($step['options']);
                        break;
                }
            }
            
            $validatedSteps[] = $validatedStep;
        }
        
        return $validatedSteps;
    }
    
    /**
     * Get MIME type of a file
     * 
     * @param string $filepath Path to the file
     * @return string MIME type
     */
    private function getMimeType($filepath) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filepath);
        finfo_close($finfo);
        
        return $mimeType;
    }
    
    /**
     * Check if a MIME type is an image
     * 
     * @param string $mimeType MIME type to check
     * @return bool True if the MIME type is an image
     */
    private function isImageMimeType($mimeType) {
        return strpos($mimeType, 'image/') === 0;
    }
    
    /**
     * Validate image dimensions
     * 
     * @param string $filepath Path to the image file
     * @param int $minWidth Minimum width in pixels
     * @param int $minHeight Minimum height in pixels
     * @param int $maxWidth Maximum width in pixels
     * @param int $maxHeight Maximum height in pixels
     * @return bool True if valid
     * @throws \Exception
     */
    private function validateImageDimensions(
        $filepath, 
        $minWidth = 10, 
        $minHeight = 10, 
        $maxWidth = 10000, 
        $maxHeight = 10000
    ) {
        $imageInfo = getimagesize($filepath);
        
        if (!$imageInfo) {
            throw new \Exception('Invalid image file');
        }
        
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        if ($width < $minWidth || $height < $minHeight) {
            throw new \Exception(
                sprintf('Image too small. Minimum dimensions are %dx%d pixels', $minWidth, $minHeight)
            );
        }
        
        if ($width > $maxWidth || $height > $maxHeight) {
            throw new \Exception(
                sprintf('Image too large. Maximum dimensions are %dx%d pixels', $maxWidth, $maxHeight)
            );
        }
        
        return true;
    }
    
    /**
     * Get human-readable upload error message
     * 
     * @param int $errorCode Error code from $_FILES['error']
     * @return string Error message
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload';
            default:
                return 'Unknown upload error';
        }
    }
}