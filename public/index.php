<?php
/**
 * Front Controller / Entry Point
 * 
 * This is the main entry point for the ImgTasks application.
 * All requests go through this file, which initializes the application,
 * handles routing, and dispatches to appropriate controllers.
 * 
 * @author Piotr Tamulewicz <pt@petertam.pro>
 */

// Define application root path
define('APP_ROOT', dirname(__DIR__));

// Load Composer autoloader (if you use it)
// require_once APP_ROOT . '/vendor/autoload.php';

// Manual autoload function for classes
spl_autoload_register(function ($className) {
    // Convert namespace to file path
    $className = str_replace('ImgTasks\\', '', $className);
    $className = str_replace('\\', '/', $className);
    
    $file = APP_ROOT . '/src/' . $className . '.php';
    
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});

// Load environment variables from .env file
$envPath = APP_ROOT . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse key=value
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if (strpos($value, '"') === 0 || strpos($value, "'") === 0) {
                $value = trim($value, '\'"');
            }
            
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Set error reporting based on environment
$appDebug = getenv('APP_DEBUG') === 'true';
if ($appDebug) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

// Initialize utility classes
$fileHandler = new \ImgTasks\Utils\FileHandler(
    getenv('TEMP_UPLOADS_DIR') ?: 'uploads',
    (int) (getenv('TEMP_FILES_TTL_HOURS') ?: 24)
);

$validator = new \ImgTasks\Utils\Validator(
    (int) (getenv('MAX_UPLOAD_SIZE_MB') ?: 25)
);

$sessionOptions = [
    'lifetime' => (int) (getenv('SESSION_LIFETIME_MINUTES') ?: 120) * 60,
    'cookie_path' => getenv('SESSION_COOKIE_PATH') ?: '/opti2',
    'secure_only' => getenv('SESSION_COOKIE_SECURE') === 'true',
    'http_only' => getenv('SESSION_COOKIE_HTTPONLY') !== 'false',
    'same_site' => getenv('SESSION_COOKIE_SAMESITE') ?: 'Lax'
];

$sessionManager = new \ImgTasks\Utils\SessionManager('imgtasks', $sessionOptions);

// Initialize API services
$optimizationService = new \ImgTasks\Services\OptimizationService(
    getenv('IMAGE_OPTIMIZATION_SERVICE_BASE_URL'),
    getenv('IMAGE_OPTIMIZATION_API_KEY'),
    $fileHandler
);

$replicateService = new \ImgTasks\Services\ReplicateService(
    getenv('REPLICATE_API_TOKEN'),
    $fileHandler
);

// Initialize controllers
$indexController = new \ImgTasks\Controllers\IndexController($sessionManager);
$processController = new \ImgTasks\Controllers\ProcessController(
    $fileHandler,
    $validator,
    $sessionManager,
    $optimizationService,
    $replicateService
);

// Basic routing
$route = $_GET['route'] ?? '';
$route = trim($route, '/');

// Handle AJAX requests for processing
if (strpos($route, 'api/') === 0) {
    // API routes
    $apiRoute = substr($route, 4); // Remove 'api/' prefix
    
    switch ($apiRoute) {
        case 'upload':
            $processController->upload();
            break;
            
        case 'process':
            $processController->process();
            break;
            
        case 'results':
            $processController->getResults();
            break;
            
        case 'delete-result':
            $processController->deleteResult();
            break;
            
        default:
            // API endpoint not found
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'API endpoint not found']);
            exit;
    }
} else {
    // Web routes
    switch ($route) {
        default:
            // Default: show main page with mode switching
            $indexController->index();
            break;
    }
}