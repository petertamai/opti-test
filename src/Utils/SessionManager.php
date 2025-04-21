<?php
/**
 * SessionManager.php
 * 
 * Utility class for managing PHP sessions with additional features
 * like session expiration handling and namespace isolation.
 * 
 * @author Piotr Tamulewicz <pt@petertam.pro>
 */

namespace ImgTasks\Utils;

class SessionManager {
    /**
     * Application-specific session namespace
     * 
     * @var string
     */
    private $namespace;
    
    /**
     * Session lifetime in seconds
     * 
     * @var int
     */
    private $lifetime;
    
    /**
     * Session cookie path
     * 
     * @var string
     */
    private $cookiePath;
    
    /**
     * Whether to force HTTPS for the session cookie
     * 
     * @var bool
     */
    private $secureOnly;
    
    /**
     * Whether to make the session cookie accessible only via HTTP (not JavaScript)
     * 
     * @var bool
     */
    private $httpOnly;
    
    /**
     * SameSite cookie attribute
     * 
     * @var string
     */
    private $sameSite;
    
    /**
     * Constructor
     * 
     * @param string $namespace Application-specific session namespace
     * @param array $options Session options
     */
    public function __construct(
        $namespace = 'imgtasks',
        $options = []
    ) {
        $this->namespace = $namespace;
        
        // Set default options
        $defaultOptions = [
            'lifetime' => 7200, // 2 hours
            'cookie_path' => '/',
            'secure_only' => false,
            'http_only' => true,
            'same_site' => 'Lax'
        ];
        
        // Merge with provided options
        $options = array_merge($defaultOptions, $options);
        
        $this->lifetime = $options['lifetime'];
        $this->cookiePath = $options['cookie_path'];
        $this->secureOnly = $options['secure_only'];
        $this->httpOnly = $options['http_only'];
        $this->sameSite = $options['same_site'];
        
        // Start or resume session
        $this->startSession();
    }
    
    /**
     * Start or resume a session
     * 
     * @return bool True on success
     */
    public function startSession() {
        // Set session cookie parameters
        session_set_cookie_params([
            'lifetime' => $this->lifetime,
            'path' => $this->cookiePath,
            'secure' => $this->secureOnly,
            'httponly' => $this->httpOnly,
            'samesite' => $this->sameSite
        ]);
        
        // Start session if not already started
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        // Initialize namespace if not exists
        if (!isset($_SESSION[$this->namespace])) {
            $_SESSION[$this->namespace] = [];
        }
        
        // Check session expiration
        $this->checkSessionExpiration();
        
        return true;
    }
    
    /**
     * Set a session variable
     * 
     * @param string $key Session variable key
     * @param mixed $value Session variable value
     * @return void
     */
    public function set($key, $value) {
        $_SESSION[$this->namespace][$key] = $value;
        $this->updateLastActivity();
    }
    
    /**
     * Get a session variable
     * 
     * @param string $key Session variable key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Session variable value or default
     */
    public function get($key, $default = null) {
        return $_SESSION[$this->namespace][$key] ?? $default;
    }
    
    /**
     * Check if a session variable exists
     * 
     * @param string $key Session variable key
     * @return bool True if the key exists
     */
    public function has($key) {
        return isset($_SESSION[$this->namespace][$key]);
    }
    
    /**
     * Remove a session variable
     * 
     * @param string $key Session variable key
     * @return void
     */
    public function remove($key) {
        if (isset($_SESSION[$this->namespace][$key])) {
            unset($_SESSION[$this->namespace][$key]);
        }
    }
    
    /**
     * Clear all session variables in the namespace
     * 
     * @return void
     */
    public function clear() {
        $_SESSION[$this->namespace] = [];
        $this->updateLastActivity();
    }
    
    /**
     * Destroy the entire session
     * 
     * @return bool True on success
     */
    public function destroy() {
        // Clear session data
        $_SESSION = [];
        
        // Delete the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        // Destroy the session
        return session_destroy();
    }
    
    /**
     * Regenerate the session ID
     * 
     * @param bool $deleteOldSession Whether to delete the old session data
     * @return bool True on success
     */
    public function regenerateId($deleteOldSession = true) {
        return session_regenerate_id($deleteOldSession);
    }
    
    /**
     * Get all session data in the namespace
     * 
     * @return array Session data
     */
    public function getAll() {
        return $_SESSION[$this->namespace] ?? [];
    }
    
    /**
     * Update the last activity timestamp
     * 
     * @return void
     */
    private function updateLastActivity() {
        $_SESSION[$this->namespace]['_last_activity'] = time();
    }
    
    /**
     * Check if the session has expired
     * If expired, clear the session data
     * 
     * @return void
     */
    private function checkSessionExpiration() {
        $lastActivity = $_SESSION[$this->namespace]['_last_activity'] ?? null;
        
        if ($lastActivity !== null && (time() - $lastActivity) > $this->lifetime) {
            // Session has expired, clear it
            $this->clear();
            
            // Regenerate session ID for security
            $this->regenerateId(true);
        }
        
        // Update last activity timestamp
        $this->updateLastActivity();
    }
    
    /**
     * Set a flash message (available only for the next request)
     * 
     * @param string $key Flash message key
     * @param mixed $value Flash message value
     * @return void
     */
    public function setFlash($key, $value) {
        $_SESSION[$this->namespace]['_flash'][$key] = $value;
        $this->updateLastActivity();
    }
    
    /**
     * Get a flash message and remove it
     * 
     * @param string $key Flash message key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Flash message value or default
     */
    public function getFlash($key, $default = null) {
        $value = $_SESSION[$this->namespace]['_flash'][$key] ?? $default;
        
        // Remove the flash message
        if (isset($_SESSION[$this->namespace]['_flash'][$key])) {
            unset($_SESSION[$this->namespace]['_flash'][$key]);
        }
        
        return $value;
    }
    
    /**
     * Check if a flash message exists
     * 
     * @param string $key Flash message key
     * @return bool True if the key exists
     */
    public function hasFlash($key) {
        return isset($_SESSION[$this->namespace]['_flash'][$key]);
    }
    
    /**
     * Keep a flash message for another request
     * 
     * @param string $key Flash message key
     * @return void
     */
    public function keepFlash($key) {
        if (isset($_SESSION[$this->namespace]['_flash'][$key])) {
            $value = $_SESSION[$this->namespace]['_flash'][$key];
            $this->setFlash($key, $value);
        }
    }
    
    /**
     * Get all flash messages and remove them
     * 
     * @return array Flash messages
     */
    public function getAllFlash() {
        $flash = $_SESSION[$this->namespace]['_flash'] ?? [];
        
        // Remove all flash messages
        $_SESSION[$this->namespace]['_flash'] = [];
        
        return $flash;
    }
    
    /**
     * Get the session ID
     * 
     * @return string Session ID
     */
    public function getId() {
        return session_id();
    }
    
    /**
     * Get the session status
     * 
     * @return int Session status (PHP_SESSION_DISABLED, PHP_SESSION_NONE, or PHP_SESSION_ACTIVE)
     */
    public function getStatus() {
        return session_status();
    }
    
    /**
     * Check if the session is active
     * 
     * @return bool True if the session is active
     */
    public function isActive() {
        return session_status() === PHP_SESSION_ACTIVE;
    }
}