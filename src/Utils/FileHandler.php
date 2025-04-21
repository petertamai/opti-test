<?php
/**
 * FileHandler.php
 * 
 * Utility class for handling file operations such as uploads, 
 * temporary file management, and file cleanup.
 * 
 * @author Piotr Tamulewicz <pt@petertam.pro>
 */

namespace ImgTasks\Utils;

class FileHandler {
    /**
     * Base directory for uploads
     * 
     * @var string
     */
    private $uploadsDir;
    
    /**
     * Time-to-live for temporary files in hours
     * 
     * @var int
     */
    private $tempFilesTtl;
    
    /**
     * Constructor
     * 
     * @param string $uploadsDir Base directory for uploads (relative to app root)
     * @param int $tempFilesTtl Time-to-live for temporary files in hours
     */
    public function __construct($uploadsDir = 'uploads', $tempFilesTtl = 24) {
        // Ensure uploads directory is absolute
        if (!str_starts_with($uploadsDir, '/')) {
            $uploadsDir = dirname(__DIR__, 2) . '/' . $uploadsDir;
        }
        
        $this->uploadsDir = rtrim($uploadsDir, '/');
        $this->tempFilesTtl = $tempFilesTtl;
        
        // Ensure uploads directory exists and is writable
        $this->ensureDirectoryExists($this->uploadsDir);
    }
    
    /**
     * Handle file upload from $_FILES
     * 
     * @param array $file File information from $_FILES
     * @return array Uploaded file information
     * @throws \Exception
     */
    public function handleUpload($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception($this->getUploadErrorMessage($file['error']));
        }
        
        // Generate unique ID and paths
        $id = uniqid('file_');
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $id . '.' . $extension;
        $filepath = $this->uploadsDir . '/' . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new \Exception('Failed to move uploaded file');
        }
        
        // Generate thumbnail for images
        $thumbnailPath = null;
        if ($this->isImage($filepath)) {
            $thumbnailPath = $this->generateThumbnail($filepath);
        }
        
        // Return file information
        return [
            'id' => $id,
            'name' => $file['name'],
            'path' => $filepath,
            'size' => $file['size'],
            'type' => $file['type'],
            'extension' => $extension,
            'thumbnail' => $thumbnailPath,
            'uploaded_at' => time()
        ];
    }
    
    /**
     * Generate a thumbnail for an image
     * 
     * @param string $imagePath Path to the original image
     * @param int $maxWidth Maximum thumbnail width
     * @param int $maxHeight Maximum thumbnail height
     * @return string|null Path to the thumbnail, or null on failure
     */
    public function generateThumbnail($imagePath, $maxWidth = 200, $maxHeight = 200) {
        // Check if file exists and is an image
        if (!file_exists($imagePath) || !$this->isImage($imagePath)) {
            return null;
        }
        
        // Get image type and create source image
        $imageInfo = getimagesize($imagePath);
        $imageType = $imageInfo[2];
        $sourceImage = $this->createImageFromFile($imagePath, $imageType);
        
        if (!$sourceImage) {
            return null;
        }
        
        // Calculate new dimensions
        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        
        $ratio = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
        $newWidth = round($sourceWidth * $ratio);
        $newHeight = round($sourceHeight * $ratio);
        
        // Create thumbnail image
        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG images
        if ($imageType === IMAGETYPE_PNG) {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
            imagefilledrectangle($thumbnail, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Resize the image
        imagecopyresampled(
            $thumbnail, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight, $sourceWidth, $sourceHeight
        );
        
        // Generate thumbnail filename
        $thumbnailPath = $this->getThumbnailPath($imagePath);
        
        // Save thumbnail
        $this->saveImage($thumbnail, $thumbnailPath, $imageType);
        
        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($thumbnail);
        
        return $thumbnailPath;
    }
    
    /**
     * Get a temporary file path for downloaded or generated files
     * 
     * @param string $extension File extension (without dot)
     * @return string Path to the temporary file
     */
    public function getTempFilePath($extension = 'jpg') {
        $extension = strtolower($extension);
        $extension = ltrim($extension, '.');
        $filename = uniqid('temp_') . '.' . $extension;
        $filepath = $this->uploadsDir . '/' . $filename;
        
        return $filepath;
    }
    
    /**
     * Delete a file
     * 
     * @param string $filepath Path to the file to delete
     * @return bool True if the file was deleted or didn't exist, false on failure
     */
    public function deleteFile($filepath) {
        if (!file_exists($filepath)) {
            return true;
        }
        
        return unlink($filepath);
    }
    
    /**
     * Clean up old temporary files
     * 
     * @return int Number of files deleted
     */
    public function cleanupTempFiles() {
        $count = 0;
        $threshold = time() - ($this->tempFilesTtl * 3600);
        
        // Scan the uploads directory
        $files = scandir($this->uploadsDir);
        
        foreach ($files as $file) {
            // Skip directories and hidden files
            if ($file === '.' || $file === '..' || $file[0] === '.') {
                continue;
            }
            
            $filepath = $this->uploadsDir . '/' . $file;
            
            // Skip if not a file
            if (!is_file($filepath)) {
                continue;
            }
            
            // Check file modification time
            if (filemtime($filepath) < $threshold) {
                if (unlink($filepath)) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Check if a file is an image
     * 
     * @param string $filepath Path to the file
     * @return bool True if the file is an image
     */
    public function isImage($filepath) {
        if (!file_exists($filepath)) {
            return false;
        }
        
        $imageInfo = @getimagesize($filepath);
        
        if (!$imageInfo) {
            return false;
        }
        
        $validTypes = [
            IMAGETYPE_JPEG,
            IMAGETYPE_PNG,
            IMAGETYPE_GIF,
            IMAGETYPE_WEBP
        ];
        
        return in_array($imageInfo[2], $validTypes);
    }
    
    /**
     * Ensure a directory exists and is writable
     * 
     * @param string $dir Directory path
     * @return bool True if the directory exists and is writable
     * @throws \Exception
     */
    private function ensureDirectoryExists($dir) {
        // Create directory if it doesn't exist
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new \Exception("Failed to create directory: {$dir}");
            }
        }
        
        // Check if directory is writable
        if (!is_writable($dir)) {
            throw new \Exception("Directory is not writable: {$dir}");
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
    
    /**
     * Create an image resource from a file
     * 
     * @param string $filepath Path to the image file
     * @param int $imageType Image type constant (IMAGETYPE_*)
     * @return resource|false Image resource or false on failure
     */
    private function createImageFromFile($filepath, $imageType) {
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($filepath);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($filepath);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($filepath);
            case IMAGETYPE_WEBP:
                if (function_exists('imagecreatefromwebp')) {
                    return imagecreatefromwebp($filepath);
                }
                return false;
            default:
                return false;
        }
    }
    
    /**
     * Save an image resource to a file
     * 
     * @param resource $image Image resource
     * @param string $filepath Path to save the image
     * @param int $imageType Image type constant (IMAGETYPE_*)
     * @return bool True on success, false on failure
     */
    private function saveImage($image, $filepath, $imageType) {
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                return imagejpeg($image, $filepath, 85);
            case IMAGETYPE_PNG:
                return imagepng($image, $filepath, 6);
            case IMAGETYPE_GIF:
                return imagegif($image, $filepath);
            case IMAGETYPE_WEBP:
                if (function_exists('imagewebp')) {
                    return imagewebp($image, $filepath, 85);
                }
                return false;
            default:
                return false;
        }
    }
    
    /**
     * Get a path for a thumbnail based on the original image path
     * 
     * @param string $imagePath Path to the original image
     * @return string Path for the thumbnail
     */
    private function getThumbnailPath($imagePath) {
        $pathInfo = pathinfo($imagePath);
        return $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
    }
    
    /**
     * Get file MIME type
     * 
     * @param string $filepath Path to the file
     * @return string MIME type or empty string on failure
     */
    public function getMimeType($filepath) {
        if (!file_exists($filepath)) {
            return '';
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filepath);
        finfo_close($finfo);
        
        return $mimeType;
    }

    /**
     * Compatibility function for PHP < 8.0
     * 
     * @param string $haystack String to search in
     * @param string $needle String to search for
     * @return bool True if $haystack starts with $needle
     */
    private function str_starts_with($haystack, $needle) {
        if (function_exists('str_starts_with')) {
            return str_starts_with($haystack, $needle);
        }
        return strpos($haystack, $needle) === 0;
    }
}