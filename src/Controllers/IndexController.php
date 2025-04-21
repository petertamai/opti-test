<?php
/**
 * IndexController.php
 * 
 * Handles main page routing and mode switching for the ImgTasks application.
 * Responsible for rendering the appropriate view templates based on the requested mode.
 * 
 * @author Piotr Tamulewicz <pt@petertam.pro>
 */

namespace ImgTasks\Controllers;

class IndexController {
    /**
     * Default mode to display if none specified
     * 
     * @var string
     */
    private $defaultMode = 'basic';
    
    /**
     * Valid application modes
     * 
     * @var array
     */
    private $validModes = ['basic', 'simple', 'advanced'];
    
    /**
     * Session manager instance
     * 
     * @var \ImgTasks\Utils\SessionManager
     */
    private $sessionManager;
    
    /**
     * Constructor
     * 
     * @param \ImgTasks\Utils\SessionManager $sessionManager
     */
    public function __construct($sessionManager) {
        $this->sessionManager = $sessionManager;
    }
    
    /**
     * Handle the main application request
     * Determines which mode to display and renders the appropriate template
     * 
     * @return void
     */
    public function index() {
        // Get requested mode from URL, defaulting to basic mode
        $mode = isset($_GET['mode']) ? $_GET['mode'] : $this->defaultMode;
        
        // Validate mode
        if (!in_array($mode, $this->validModes)) {
            $mode = $this->defaultMode;
        }
        
        // Store current mode in session
        $this->sessionManager->set('current_mode', $mode);
        
        // Render the appropriate template based on mode
        switch ($mode) {
            case 'simple':
                $this->renderTemplate('pages/simple_opt_mode.php', ['mode' => $mode]);
                break;
            case 'advanced':
                $this->renderTemplate('pages/advanced_mode.php', ['mode' => $mode]);
                break;
            case 'basic':
            default:
                $this->renderTemplate('pages/basic_mode.php', ['mode' => $mode]);
                break;
        }
    }
    
    /**
     * Render error page
     * 
     * @param string $message Error message to display
     * @param int $code HTTP status code
     * @return void
     */
    public function error($message = 'An error occurred', $code = 500) {
        http_response_code($code);
        $this->renderTemplate('error.php', [
            'message' => $message,
            'code' => $code
        ]);
    }
    
    /**
     * Render a template with the given data
     * 
     * @param string $template Path to template file (relative to templates directory)
     * @param array $data Data to pass to the template
     * @return void
     */
    private function renderTemplate($template, $data = []) {
        // Extract data to make variables available in template scope
        if (!empty($data)) {
            extract($data);
        }
        
        // Include main layout template, which will include the specific content template
        $contentTemplate = $template;
        include dirname(__DIR__, 2) . '/templates/layout/main.php';
    }
}